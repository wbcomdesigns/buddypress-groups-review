<?php
/**
 * Schema.org Structured Data for Group Reviews.
 *
 * @link       https://wbcomdesigns.com/
 * @since      3.5.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BGR_Schema' ) ) {

	/**
	 * Class for adding Schema.org structured data to reviews.
	 *
	 * @since 3.5.0
	 */
	class BGR_Schema {

		/**
		 * Constructor.
		 *
		 * @since 3.5.0
		 */
		public function __construct() {
			add_action( 'wp_head', array( $this, 'add_group_review_schema' ), 5 );
		}

		/**
		 * Add JSON-LD schema markup for group reviews.
		 *
		 * @since 3.5.0
		 */
		public function add_group_review_schema() {
			if ( ! function_exists( 'bp_is_group' ) || ! bp_is_group() ) {
				return;
			}

			global $bgr;

			$group_id = bp_get_current_group_id();
			if ( ! $group_id ) {
				return;
			}

			// Get all published reviews for this group.
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

			$reviews = get_posts( $args );

			if ( empty( $reviews ) ) {
				return;
			}

			// Get group object safely to avoid BuddyBoss template errors.
			$group = groups_get_group( $group_id );
			if ( ! $group ) {
				return;
			}

			$group_name        = bp_get_group_name( $group );
			$group_description = bp_get_group_description( $group );

			// Build group URL safely without relying on template globals.
			if ( function_exists( 'bp_get_group_url' ) ) {
				// BuddyPress 12.0+
				$group_link = bp_get_group_url( $group );
			} else {
				// Fallback: build URL manually to avoid template dependency.
				$group_link = bp_get_groups_directory_permalink() . $group->slug . '/';
			}

			// Calculate aggregate rating.
			$total_reviews        = count( $reviews );
			$review_rating_fields = isset( $bgr['review_rating_fields'] ) ? $bgr['review_rating_fields'] : array();
			$total_ratings        = 0;
			$ratings_count        = 0;

			$review_schemas = array();

			foreach ( $reviews as $review ) {
				$review_ratings = get_post_meta( $review->ID, 'review_star_rating', true );
				$author_id      = $review->post_author;
				$author_info    = get_userdata( $author_id );

				if ( ! empty( $review_ratings ) && is_array( $review_ratings ) ) {
					$review_total = 0;
					$review_count = 0;

					foreach ( $review_ratings as $rating ) {
						$review_total += $rating;
						$review_count++;
						$total_ratings += $rating;
						$ratings_count++;
					}

					$review_average = $review_count > 0 ? round( $review_total / $review_count, 1 ) : 0;

					// Individual review schema.
					$review_schemas[] = array(
						'@type'         => 'Review',
						'author'        => array(
							'@type' => 'Person',
							'name'  => $author_info ? $author_info->display_name : __( 'Anonymous', 'bp-group-reviews' ),
						),
						'datePublished' => get_the_date( 'c', $review->ID ),
						'reviewBody'    => wp_strip_all_tags( $review->post_content ),
						'name'          => $review->post_title,
						'reviewRating'  => array(
							'@type'       => 'Rating',
							'ratingValue' => $review_average,
							'bestRating'  => '5',
							'worstRating' => '1',
						),
					);
				}
			}

			$average_rating = $ratings_count > 0 ? round( $total_ratings / $ratings_count, 1 ) : 0;

			// Build organization/group schema.
			$schema = array(
				'@context'        => 'https://schema.org',
				'@type'           => 'Organization',
				'name'            => $group_name,
				'description'     => wp_strip_all_tags( $group_description ),
				'url'             => $group_link,
				'aggregateRating' => array(
					'@type'       => 'AggregateRating',
					'ratingValue' => $average_rating,
					'bestRating'  => '5',
					'worstRating' => '1',
					'ratingCount' => $ratings_count,
					'reviewCount' => $total_reviews,
				),
			);

			// Add individual reviews to schema.
			if ( ! empty( $review_schemas ) ) {
				$schema['review'] = $review_schemas;
			}

			// Output JSON-LD.
			echo '<script type="application/ld+json">';
			echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
			echo '</script>' . "\n";
		}

		/**
		 * Get schema markup for a single review (for use in templates).
		 *
		 * @since 3.5.0
		 * @param int $review_id Review post ID.
		 * @return string HTML with microdata.
		 */
		public static function get_review_microdata( $review_id ) {
			$review         = get_post( $review_id );
			$review_ratings = get_post_meta( $review_id, 'review_star_rating', true );
			$author_id      = $review->post_author;
			$author_info    = get_userdata( $author_id );

			if ( empty( $review_ratings ) || ! is_array( $review_ratings ) ) {
				return '';
			}

			$review_total = 0;
			$review_count = 0;

			foreach ( $review_ratings as $rating ) {
				$review_total += $rating;
				$review_count++;
			}

			$review_average = $review_count > 0 ? round( $review_total / $review_count, 1 ) : 0;

			$output = '<div itemscope itemtype="https://schema.org/Review" style="display:none;">';
			$output .= '<span itemprop="name">' . esc_html( $review->post_title ) . '</span>';
			$output .= '<span itemprop="author" itemscope itemtype="https://schema.org/Person">';
			$output .= '<span itemprop="name">' . esc_html( $author_info ? $author_info->display_name : __( 'Anonymous', 'bp-group-reviews' ) ) . '</span>';
			$output .= '</span>';
			$output .= '<span itemprop="reviewBody">' . esc_html( wp_strip_all_tags( $review->post_content ) ) . '</span>';
			$output .= '<span itemprop="datePublished" content="' . esc_attr( get_the_date( 'c', $review_id ) ) . '">' . esc_html( get_the_date( '', $review_id ) ) . '</span>';
			$output .= '<div itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">';
			$output .= '<span itemprop="ratingValue">' . esc_html( $review_average ) . '</span>';
			$output .= '<span itemprop="bestRating">5</span>';
			$output .= '<span itemprop="worstRating">1</span>';
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}
	}

	new BGR_Schema();
}
