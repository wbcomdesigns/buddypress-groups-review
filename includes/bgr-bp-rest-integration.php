<?php
/**
 * BuddyPress REST API Integration for Group Reviews.
 *
 * Extends BuddyPress Groups REST API with review data.
 *
 * @link       https://wbcomdesigns.com/
 * @since      3.5.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BGR_BP_REST_Integration' ) ) {

	/**
	 * BuddyPress REST API Integration.
	 *
	 * Adds review data to BuddyPress Groups endpoints.
	 *
	 * @since 3.5.0
	 */
	class BGR_BP_REST_Integration {

		/**
		 * Constructor.
		 *
		 * @since 3.5.0
		 */
		public function __construct() {
			// Add reviews data to BuddyPress Groups endpoint.
			add_filter( 'bp_rest_groups_get_item_schema', array( $this, 'add_review_fields_to_schema' ) );
			add_filter( 'rest_prepare_buddypress_group', array( $this, 'add_review_data_to_response' ), 10, 3 );

			// Register custom endpoints under buddypress namespace.
			add_action( 'rest_api_init', array( $this, 'register_bp_review_routes' ) );
		}

		/**
		 * Add review fields to BuddyPress groups schema.
		 *
		 * @param array $schema BuddyPress group schema.
		 * @return array Modified schema.
		 */
		public function add_review_fields_to_schema( $schema ) {
			$schema['properties']['reviews'] = array(
				'description' => __( 'Group review statistics.', 'bp-group-reviews' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'average_rating' => array(
						'description' => __( 'Average rating across all criteria.', 'bp-group-reviews' ),
						'type'        => 'number',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'total_reviews'  => array(
						'description' => __( 'Total number of published reviews.', 'bp-group-reviews' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'ratings'        => array(
						'description' => __( 'Average ratings by criteria.', 'bp-group-reviews' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'can_review'     => array(
						'description' => __( 'Whether the current user can review this group.', 'bp-group-reviews' ),
						'type'        => 'boolean',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			);

			return $schema;
		}

		/**
		 * Add review data to BuddyPress group response.
		 *
		 * @param WP_REST_Response $response Response object.
		 * @param WP_REST_Request  $request  Request object.
		 * @param BP_Groups_Group  $group    Group object.
		 * @return WP_REST_Response Modified response.
		 */
		public function add_review_data_to_response( $response, $request, $group ) {
			global $bgr;

			$group_id = $group->id;

			// Get review statistics.
			$review_stats = $this->get_group_review_stats( $group_id );

			// Check if current user can review.
			$can_review = false;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();

				// Cannot review if user is group admin.
				if ( function_exists( 'groups_is_user_admin' ) && ! groups_is_user_admin( $user_id, $group_id ) ) {
					// Check if group is excluded.
					$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
					$exclude_groups             = isset( $bgr_admin_general_settings['exclude_groups'] ) ? array_map( 'absint', (array) $bgr_admin_general_settings['exclude_groups'] ) : array();

					if ( ! in_array( absint( $group_id ), $exclude_groups, true ) ) {
						// Check if multiple reviews allowed or user hasn't reviewed yet.
						$multi_reviews = isset( $bgr_admin_general_settings['multi_reviews'] ) ? $bgr_admin_general_settings['multi_reviews'] : 'no';

						if ( 'yes' === $multi_reviews ) {
							$can_review = true;
						} else {
							// Check if user has already reviewed.
							$existing_reviews = get_posts(
								array(
									'post_type'   => 'review',
									'post_status' => array( 'draft', 'publish' ),
									'author'      => $user_id,
									'meta_query'  => array(
										array(
											'key'     => 'linked_group',
											'value'   => $group_id,
											'compare' => '=',
										),
									),
								)
							);

							$can_review = empty( $existing_reviews );
						}
					}
				}
			}

			// Add review data to response.
			$data            = $response->get_data();
			$data['reviews'] = array(
				'average_rating' => $review_stats['average'],
				'total_reviews'  => $review_stats['total_reviews'],
				'ratings'        => $review_stats['ratings'],
				'can_review'     => $can_review,
			);
			$response->data  = $data;

			return $response;
		}

		/**
		 * Get review statistics for a group.
		 *
		 * @param int $group_id Group ID.
		 * @return array Review statistics.
		 */
		protected function get_group_review_stats( $group_id ) {
			global $bgr;

			$args = array(
				'post_type'      => 'review',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'linked_group',
						'value'   => $group_id,
						'compare' => '=',
					),
				),
			);

			$reviews              = get_posts( $args );
			$total_reviews        = count( $reviews );
			$review_rating_fields = isset( $bgr['review_rating_fields'] ) ? $bgr['review_rating_fields'] : array();

			if ( empty( $reviews ) ) {
				return array(
					'average'       => 0,
					'total_reviews' => 0,
					'ratings'       => array(),
				);
			}

			$total_ratings   = 0;
			$ratings_count   = 0;
			$criteria_totals = array();

			foreach ( $reviews as $review ) {
				$review_ratings = get_post_meta( $review->ID, 'review_star_rating', true );

				if ( ! empty( $review_ratings ) && is_array( $review_ratings ) ) {
					foreach ( $review_ratings as $field => $rating ) {
						if ( ! isset( $criteria_totals[ $field ] ) ) {
							$criteria_totals[ $field ] = array(
								'total' => 0,
								'count' => 0,
							);
						}
						$criteria_totals[ $field ]['total'] += $rating;
						++$criteria_totals[ $field ]['count'];
						$total_ratings += $rating;
						++$ratings_count;
					}
				}
			}

			$average_rating = $ratings_count > 0 ? round( $total_ratings / $ratings_count, 2 ) : 0;

			$criteria_averages = array();
			foreach ( $criteria_totals as $field => $data ) {
				$criteria_averages[ $field ] = $data['count'] > 0 ? round( $data['total'] / $data['count'], 2 ) : 0;
			}

			return array(
				'average'       => $average_rating,
				'total_reviews' => $total_reviews,
				'ratings'       => $criteria_averages,
			);
		}

		/**
		 * Register review routes under buddypress namespace.
		 *
		 * @since 3.5.0
		 */
		public function register_bp_review_routes() {
			// Reviews sub-resource for groups.
			register_rest_route(
				'buddypress/v1',
				'/groups/(?P<group_id>\d+)/reviews',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_group_reviews' ),
						'permission_callback' => '__return_true',
						'args'                => array(
							'group_id' => array(
								'required'          => true,
								'validate_callback' => function ( $param ) {
									return is_numeric( $param );
								},
								'sanitize_callback' => 'absint',
							),
							'page'     => array(
								'default'           => 1,
								'sanitize_callback' => 'absint',
							),
							'per_page' => array(
								'default'           => 10,
								'sanitize_callback' => 'absint',
							),
						),
					),
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'create_group_review' ),
						'permission_callback' => 'is_user_logged_in',
						'args'                => array(
							'group_id'    => array(
								'required'          => true,
								'validate_callback' => function ( $param ) {
									return is_numeric( $param );
								},
								'sanitize_callback' => 'absint',
							),
							'subject'     => array(
								'required'          => true,
								'sanitize_callback' => 'sanitize_text_field',
							),
							'description' => array(
								'required'          => true,
								'sanitize_callback' => 'sanitize_textarea_field',
							),
							'ratings'     => array(
								'required'          => false,
								'validate_callback' => function ( $param ) {
									return is_array( $param );
								},
							),
						),
					),
					'schema' => array( $this, 'get_review_schema' ),
				)
			);

			// Single review endpoint.
			register_rest_route(
				'buddypress/v1',
				'/groups/(?P<group_id>\d+)/reviews/(?P<review_id>\d+)',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_single_review' ),
						'permission_callback' => '__return_true',
						'args'                => array(
							'group_id'  => array(
								'required'          => true,
								'validate_callback' => function ( $param ) {
									return is_numeric( $param );
								},
								'sanitize_callback' => 'absint',
							),
							'review_id' => array(
								'required'          => true,
								'validate_callback' => function ( $param ) {
									return is_numeric( $param );
								},
								'sanitize_callback' => 'absint',
							),
						),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_group_review' ),
						'permission_callback' => array( $this, 'update_review_permissions_check' ),
						'args'                => array(
							'group_id'    => array(
								'required'          => true,
								'sanitize_callback' => 'absint',
							),
							'review_id'   => array(
								'required'          => true,
								'sanitize_callback' => 'absint',
							),
							'subject'     => array(
								'sanitize_callback' => 'sanitize_text_field',
							),
							'description' => array(
								'sanitize_callback' => 'sanitize_textarea_field',
							),
							'ratings'     => array(
								'validate_callback' => function ( $param ) {
									return is_array( $param );
								},
							),
						),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'callback'            => array( $this, 'delete_group_review' ),
						'permission_callback' => array( $this, 'delete_review_permissions_check' ),
						'args'                => array(
							'group_id'  => array(
								'required'          => true,
								'sanitize_callback' => 'absint',
							),
							'review_id' => array(
								'required'          => true,
								'sanitize_callback' => 'absint',
							),
						),
					),
					'schema' => array( $this, 'get_review_schema' ),
				)
			);
		}

		/**
		 * Get reviews for a group.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response|WP_Error
		 */
		public function get_group_reviews( $request ) {
			$group_id = $request->get_param( 'group_id' );
			$page     = $request->get_param( 'page' );
			$per_page = $request->get_param( 'per_page' );

			$args = array(
				'post_type'      => 'review',
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'meta_query'     => array(
					array(
						'key'     => 'linked_group',
						'value'   => $group_id,
						'compare' => '=',
					),
				),
			);

			$reviews_query = new WP_Query( $args );
			$reviews       = array();

			foreach ( $reviews_query->posts as $review ) {
				$reviews[] = $this->prepare_review_for_response( $review, $group_id );
			}

			$response = rest_ensure_response( $reviews );

			$response->header( 'X-WP-Total', $reviews_query->found_posts );
			$response->header( 'X-WP-TotalPages', $reviews_query->max_num_pages );

			return $response;
		}

		/**
		 * Create a review for a group.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response|WP_Error
		 */
		public function create_group_review( $request ) {
			global $bgr;

			$group_id    = $request->get_param( 'group_id' );
			$subject     = $request->get_param( 'subject' );
			$description = $request->get_param( 'description' );
			$ratings     = $request->get_param( 'ratings' );
			$user_id     = get_current_user_id();

			// Check if group exists.
			$group = groups_get_group( $group_id );
			if ( empty( $group->id ) ) {
				return new WP_Error( 'rest_invalid_group', __( 'Invalid group ID.', 'bp-group-reviews' ), array( 'status' => 404 ) );
			}

			// Check if user is group admin (can't review own group).
			if ( function_exists( 'groups_is_user_admin' ) && groups_is_user_admin( $user_id, $group_id ) ) {
				return new WP_Error( 'rest_cannot_review', __( 'Group admins cannot review their own group.', 'bp-group-reviews' ), array( 'status' => 403 ) );
			}

			// Check if user is a member of the group.
			if ( function_exists( 'groups_is_user_member' ) && ! groups_is_user_member( $user_id, $group_id ) ) {
				return new WP_Error( 'rest_not_member', __( 'You must be a member of this group to submit a review.', 'bp-group-reviews' ), array( 'status' => 403 ) );
			}

			// Check if group is excluded.
			$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
			$exclude_groups             = isset( $bgr_admin_general_settings['exclude_groups'] ) ? array_map( 'absint', (array) $bgr_admin_general_settings['exclude_groups'] ) : array();

			if ( in_array( absint( $group_id ), $exclude_groups, true ) ) {
				return new WP_Error( 'rest_group_excluded', __( 'Reviews are disabled for this group.', 'bp-group-reviews' ), array( 'status' => 403 ) );
			}

			// Check for multiple reviews.
			$multi_reviews = isset( $bgr_admin_general_settings['multi_reviews'] ) ? $bgr_admin_general_settings['multi_reviews'] : 'no';

			if ( 'no' === $multi_reviews ) {
				$existing_reviews = get_posts(
					array(
						'post_type'   => 'review',
						'post_status' => array( 'draft', 'publish' ),
						'author'      => $user_id,
						'meta_query'  => array(
							array(
								'key'     => 'linked_group',
								'value'   => $group_id,
								'compare' => '=',
							),
						),
					)
				);

				if ( ! empty( $existing_reviews ) ) {
					return new WP_Error( 'rest_already_reviewed', __( 'You have already reviewed this group.', 'bp-group-reviews' ), array( 'status' => 403 ) );
				}
			}

			// Determine post status based on auto-approve setting.
			$auto_approve = isset( $bgr_admin_general_settings['auto_approve_reviews'] ) && 'yes' === $bgr_admin_general_settings['auto_approve_reviews'];
			$post_status  = $auto_approve ? 'publish' : 'draft';

			// Create the review post.
			$review_id = wp_insert_post(
				array(
					'post_title'   => sanitize_text_field( $subject ),
					'post_content' => sanitize_textarea_field( $description ),
					'post_type'    => 'review',
					'post_status'  => $post_status,
					'post_author'  => $user_id,
				)
			);

			if ( is_wp_error( $review_id ) ) {
				return $review_id;
			}

			// Save review meta.
			update_post_meta( $review_id, 'linked_group', $group_id );

			if ( ! empty( $ratings ) && is_array( $ratings ) ) {
				$sanitized_ratings = array();
				foreach ( $ratings as $key => $value ) {
					$sanitized_key                       = sanitize_text_field( $key );
					$sanitized_value                     = min( 5, max( 1, absint( $value ) ) );
					$sanitized_ratings[ $sanitized_key ] = $sanitized_value;
				}
				update_post_meta( $review_id, 'review_star_rating', $sanitized_ratings );
			}

			$review = get_post( $review_id );

			return rest_ensure_response( $this->prepare_review_for_response( $review, $group_id ) );
		}

		/**
		 * Get a single review.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response|WP_Error
		 */
		public function get_single_review( $request ) {
			$review_id = $request->get_param( 'review_id' );
			$group_id  = $request->get_param( 'group_id' );

			$review = get_post( $review_id );

			if ( ! $review || 'review' !== $review->post_type ) {
				return new WP_Error( 'rest_review_invalid', __( 'Invalid review ID.', 'bp-group-reviews' ), array( 'status' => 404 ) );
			}

			// Verify review belongs to the specified group.
			$linked_group = get_post_meta( $review_id, 'linked_group', true );
			if ( absint( $linked_group ) !== absint( $group_id ) ) {
				return new WP_Error( 'rest_review_invalid', __( 'Review does not belong to this group.', 'bp-group-reviews' ), array( 'status' => 404 ) );
			}

			return rest_ensure_response( $this->prepare_review_for_response( $review, $group_id ) );
		}

		/**
		 * Update a group review.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response|WP_Error
		 */
		public function update_group_review( $request ) {
			$review_id   = $request->get_param( 'review_id' );
			$group_id    = $request->get_param( 'group_id' );
			$subject     = $request->get_param( 'subject' );
			$description = $request->get_param( 'description' );
			$ratings     = $request->get_param( 'ratings' );

			$review = get_post( $review_id );

			if ( ! $review || 'review' !== $review->post_type ) {
				return new WP_Error( 'rest_review_invalid', __( 'Invalid review ID.', 'bp-group-reviews' ), array( 'status' => 404 ) );
			}

			$update_args = array( 'ID' => $review_id );

			if ( ! empty( $subject ) ) {
				$update_args['post_title'] = sanitize_text_field( $subject );
			}

			if ( ! empty( $description ) ) {
				$update_args['post_content'] = sanitize_textarea_field( $description );
			}

			$result = wp_update_post( $update_args );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			if ( ! empty( $ratings ) && is_array( $ratings ) ) {
				$sanitized_ratings = array();
				foreach ( $ratings as $key => $value ) {
					$sanitized_key                       = sanitize_text_field( $key );
					$sanitized_value                     = min( 5, max( 1, absint( $value ) ) );
					$sanitized_ratings[ $sanitized_key ] = $sanitized_value;
				}
				update_post_meta( $review_id, 'review_star_rating', $sanitized_ratings );
			}

			$review = get_post( $review_id );

			return rest_ensure_response( $this->prepare_review_for_response( $review, $group_id ) );
		}

		/**
		 * Delete a group review.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response|WP_Error
		 */
		public function delete_group_review( $request ) {
			$review_id = $request->get_param( 'review_id' );

			$review = get_post( $review_id );

			if ( ! $review || 'review' !== $review->post_type ) {
				return new WP_Error( 'rest_review_invalid', __( 'Invalid review ID.', 'bp-group-reviews' ), array( 'status' => 404 ) );
			}

			$result = wp_delete_post( $review_id, true );

			if ( ! $result ) {
				return new WP_Error( 'rest_cannot_delete', __( 'The review could not be deleted.', 'bp-group-reviews' ), array( 'status' => 500 ) );
			}

			return rest_ensure_response(
				array(
					'deleted' => true,
					'id'      => $review_id,
				)
			);
		}

		/**
		 * Prepare review data for REST response.
		 *
		 * @param WP_Post $review   Review post object.
		 * @param int     $group_id Group ID.
		 * @return array Prepared review data.
		 */
		protected function prepare_review_for_response( $review, $group_id ) {
			$author  = get_userdata( $review->post_author );
			$ratings = get_post_meta( $review->ID, 'review_star_rating', true );

			return array(
				'id'          => $review->ID,
				'group_id'    => absint( $group_id ),
				'subject'     => $review->post_title,
				'description' => $review->post_content,
				'status'      => $review->post_status,
				'author'      => array(
					'id'          => $review->post_author,
					'name'        => $author ? $author->display_name : '',
					'avatar_urls' => array(
						'thumb' => function_exists( 'bp_core_fetch_avatar' ) ? bp_core_fetch_avatar(
							array(
								'item_id' => $review->post_author,
								'type'    => 'thumb',
								'html'    => false,
							)
						) : get_avatar_url( $review->post_author, array( 'size' => 50 ) ),
						'full'  => function_exists( 'bp_core_fetch_avatar' ) ? bp_core_fetch_avatar(
							array(
								'item_id' => $review->post_author,
								'type'    => 'full',
								'html'    => false,
							)
						) : get_avatar_url( $review->post_author, array( 'size' => 150 ) ),
					),
				),
				'ratings'     => ! empty( $ratings ) ? $ratings : array(),
				'created'     => mysql_to_rfc3339( $review->post_date ),
				'modified'    => mysql_to_rfc3339( $review->post_modified ),
			);
		}

		/**
		 * Permission check for updating a review.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return bool|WP_Error
		 */
		public function update_review_permissions_check( $request ) {
			$review_id = $request->get_param( 'review_id' );
			$review    = get_post( $review_id );

			if ( ! $review || 'review' !== $review->post_type ) {
				return new WP_Error( 'rest_review_invalid', __( 'Invalid review ID.', 'bp-group-reviews' ), array( 'status' => 404 ) );
			}

			if ( ! current_user_can( 'edit_review', $review_id ) ) {
				return new WP_Error( 'rest_cannot_edit', __( 'You do not have permission to edit this review.', 'bp-group-reviews' ), array( 'status' => 403 ) );
			}

			return true;
		}

		/**
		 * Permission check for deleting a review.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return bool|WP_Error
		 */
		public function delete_review_permissions_check( $request ) {
			$review_id = $request->get_param( 'review_id' );
			$review    = get_post( $review_id );

			if ( ! $review || 'review' !== $review->post_type ) {
				return new WP_Error( 'rest_review_invalid', __( 'Invalid review ID.', 'bp-group-reviews' ), array( 'status' => 404 ) );
			}

			if ( ! current_user_can( 'delete_review', $review_id ) ) {
				return new WP_Error( 'rest_cannot_delete', __( 'You do not have permission to delete this review.', 'bp-group-reviews' ), array( 'status' => 403 ) );
			}

			return true;
		}

		/**
		 * Get review schema.
		 *
		 * @return array Review schema.
		 */
		public function get_review_schema() {
			return array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'group-review',
				'type'       => 'object',
				'properties' => array(
					'id'          => array(
						'description' => __( 'Review ID.', 'bp-group-reviews' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'group_id'    => array(
						'description' => __( 'Group ID.', 'bp-group-reviews' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'required'    => true,
					),
					'subject'     => array(
						'description' => __( 'Review title.', 'bp-group-reviews' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'required'    => true,
					),
					'description' => array(
						'description' => __( 'Review content.', 'bp-group-reviews' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'required'    => true,
					),
					'status'      => array(
						'description' => __( 'Review status.', 'bp-group-reviews' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'author'      => array(
						'description' => __( 'Review author.', 'bp-group-reviews' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'ratings'     => array(
						'description' => __( 'Star ratings by criteria.', 'bp-group-reviews' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
					),
					'created'     => array(
						'description' => __( 'Creation date.', 'bp-group-reviews' ),
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'modified'    => array(
						'description' => __( 'Last modified date.', 'bp-group-reviews' ),
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			);
		}
	}

	new BGR_BP_REST_Integration();
}
