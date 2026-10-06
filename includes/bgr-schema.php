<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- Legacy file name.
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

			$group_id = bp_get_current_group_id();
			if ( ! $group_id ) {
				return;
			}

			// Aggregate rating comes from the shared cached stats (no unbounded scan).
			$stats = bgr_get_group_review_stats( $group_id );

			if ( empty( $stats['total_reviews'] ) ) {
				return;
			}

			/**
			 * Filter how many individual reviews are embedded in the JSON-LD output.
			 *
			 * The aggregateRating always covers ALL reviews (from the cached
			 * stats); this only bounds the per-review `review` entries.
			 *
			 * @since 3.9.0
			 *
			 * @param int $limit    Maximum individual reviews in schema. Default 10.
			 * @param int $group_id The group ID.
			 */
			$schema_reviews_limit = (int) apply_filters( 'bgr_schema_reviews_limit', 10, $group_id );

			// Most recent published reviews for the per-review schema entries (bounded).
			$args = array(
				'post_type'      => 'review',
				'post_status'    => 'publish',
				'posts_per_page' => $schema_reviews_limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Bounded query, runs once per group page.
				'meta_query'     => array(
					array(
						'key'     => 'linked_group',
						'value'   => $group_id,
						'compare' => '=',
					),
				),
			);

			$reviews = get_posts( $args );

			// Get group object safely to avoid BuddyBoss template errors.
			$group = groups_get_group( $group_id );
			if ( ! $group ) {
				return;
			}

			$group_name        = bp_get_group_name( $group );
			$group_description = bp_get_group_description( $group );

			// Build group URL safely without relying on template globals.
			if ( function_exists( 'bp_get_group_url' ) ) {
				// BuddyPress 12.0+ URL function.
				$group_link = bp_get_group_url( $group );
			} else {
				// Fallback: build URL manually to avoid template dependency.
				$group_link = bp_get_groups_directory_permalink() . $group->slug . '/';
			}

			// Aggregate rating from the cached stats (covers all reviews).
			$total_reviews  = (int) $stats['total_reviews'];
			$ratings_count  = (int) $stats['ratings_count'];
			$average_rating = round( (float) $stats['average'], 1 );

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
						++$review_count;
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
	}

	new BGR_Schema();
}
