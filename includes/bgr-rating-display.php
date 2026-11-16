<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- Legacy file name.
/**
 * Class to display rating.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class to serve Rating Display.
 */

if ( ! class_exists( 'BGR_Rating_Display' ) ) {

	/**
	 * Class to display rating.
	 *
	 * @link       https://wbcomdesigns.com/
	 * @since      1.0.0
	 *
	 * @package    BuddyPress_Group_Review
	 * @subpackage BuddyPress_Group_Review/includes
	 */
	class BGR_Rating_Display {

		/**
		 * Constructor for Group Rating Display
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function __construct() {
			add_action( 'bgr_display_ratings', array( $this, 'bp_group_review_select_rating_type' ) );
			add_action( 'bgr_display_widget_average_ratings', array( $this, 'bp_group_review_average_stars' ) );
			add_action( 'bgr_display_group_average_ratings', array( $this, 'bp_group_review_average_stars' ) );
		}

		/**
		 *  Actions performed for rating display in Review , Manage Review & Single Review Page
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 *
		 * @param int $post_id Review ID.
		 */
		public function bp_group_review_select_rating_type( $post_id ) {
			global $bgr;
			$review_rating_fields = $bgr['review_rating_fields'];
			$review_ratings       = get_post_meta( $post_id, 'review_star_rating', false );

			// Ensure review_ratings is an array before iterating.
			if ( ! empty( $review_ratings ) && is_array( $review_ratings ) ) {
				foreach ( $review_ratings as $review_rating ) {
					// Ensure each review_rating is an array before iterating.
					if ( ! is_array( $review_rating ) ) {
						continue;
					}
					foreach ( $review_rating as $key => $value ) {
						if ( is_int( $key ) ) {
							$stars_on  = absint( $value );
							$stars_off = 5 - $stars_on;
							for ( $i = 1; $i <= $stars_on; $i++ ) {
								?>
								<span class="fas fa-star stars bgr-star-rate"></span>
								<?php
							}
							for ( $i = 1; $i <= $stars_off; $i++ ) {
								?>
								<span class="far fa-star stars bgr-star-rate"></span>
								<?php
							}
						}
					}
				}
			}
			$this->bp_group_review_display_star_rating( $review_rating_fields, $review_ratings );
		}

		/**
		 *  Actions performed for rating display type : Star
		 *
		 *  Display all criteria ratings from the review itself, including archived/legacy criteria.
		 *  This ensures historical reviews display correctly even when criteria have changed.
		 *
		 *  @since   1.0.0
		 *  @since   3.7.0 Updated to display all review criteria regardless of current active status.
		 *  @author  Wbcom Designs
		 *
		 * @param array $review_rating_fields Review rating fields (global or group-level active criteria).
		 * @param array $review_ratings Review rating data from post meta.
		 */
		public function bp_group_review_display_star_rating( $review_rating_fields, $review_ratings ) {
			// Ensure $review_ratings[0] is an array.
			if ( empty( $review_ratings[0] ) || ! is_array( $review_ratings[0] ) ) {
				return;
			}

			$actual_review_ratings = $review_ratings[0];

			// Get currently active criteria for comparison (to identify archived ones).
			global $bgr;
			$active_criteria = ! empty( $bgr['active_rating_fields'] ) ? $bgr['active_rating_fields'] : array();

			// Display all criteria that the review actually has.
			foreach ( $actual_review_ratings as $review_field => $rating_value ) {
				// Skip non-string keys (legacy numeric arrays).
				if ( is_int( $review_field ) ) {
					continue;
				}

				// Check if this criterion is currently active.
				$is_archived = ! in_array( $review_field, $active_criteria, true );
				?>
				<div class="multi-review <?php echo $is_archived ? 'bgr-archived-criteria' : ''; ?>">
					<div class="bgr-col-6">
						<?php
						echo esc_html( bp_group_review_get_criteria_label( $review_field ) ) . ' : ';
						if ( $is_archived ) {
							?>
							<span class="bgr-legacy-indicator" title="<?php esc_attr_e( 'This criterion is no longer active', 'bp-group-reviews' ); ?>">
								<small>(<?php esc_html_e( 'legacy', 'bp-group-reviews' ); ?>)</small>
							</span>
							<?php
						}
						?>
					</div>
					<div class="bgr-col-6">
					<?php
						$stars_on  = absint( $rating_value );
						$stars_off = 5 - $stars_on;
					for ( $i = 1; $i <= $stars_on; $i++ ) {
						?>
							<span class="fas fa-star stars bgr-star-rate"></span>
							<?php
					}
					for ( $i = 1; $i <= $stars_off; $i++ ) {
						?>
							<span class="far fa-star stars bgr-star-rate"></span>
							<?php
					}
					?>
					</div>
				</div>
				<?php
			}
		}

		/**
		 * Print the stars for an average rating (0-5); a whole rating never gets a half star.
		 *
		 * Stars only. The caller owns the wrapper and any text label, so the
		 * markup is balanced wherever the stars are used.
		 *
		 * @since 3.9.2
		 *
		 * @param float $avg_rating Average rating.
		 */
		public function bp_group_review_average_stars( $avg_rating ) {
			$avg_rating = (float) $avg_rating;
			$stars_on   = (int) $avg_rating;
			$stars_half = ( $avg_rating - $stars_on ) > 0 ? 1 : 0;
			$this->bp_group_review_average_star_rating( $stars_on, $stars_half, 5 - $stars_on - $stars_half );
		}

		/**
		 *  Actions performed for Widget rating display type : Star
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 *
		 * @param int $stars_on Stars on review.
		 * @param int $stars_half Stars half on review.
		 * @param int $stars_off Stars off on review.
		 */
		public function bp_group_review_average_star_rating( $stars_on, $stars_half, $stars_off ) {
			for ( $i = 1; $i <= $stars_on; $i++ ) {
				?>
					<span class="fas fa-star stars bgr-star-rate"></span>
					<?php
			}

			for ( $i = 1; $i <= $stars_half; $i++ ) {
				?>
					<span class="fas fa-star-half-alt stars bgr-star-rate"></span>
					<?php
			}

			for ( $i = 1; $i <= $stars_off; $i++ ) {
				?>
					<span class="far fa-star stars bgr-star-rate"></span>
					<?php
			}
		}
	}
	new BGR_Rating_Display();
}
