<?php
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
			add_action( 'bgr_display_ratings', array( $this, 'bgr_select_rating_type' ) );
			add_action( 'bgr_display_widget_average_ratings', array( $this, 'bgr_widget_average_ratings' ) );
			add_action( 'bgr_display_group_average_ratings', array( $this, 'bgr_group_average_ratings' ) );
		}

		/**
		 *  Actions performed for rating display in Review , Manage Review & Single Review Page
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 *
		 * @param int $post_id Review ID.
		 */
		public function bgr_select_rating_type( $post_id ) {			
			global $bgr;
			$review_rating_fields = $bgr['review_rating_fields'];			
			$review_ratings       = get_post_meta( $post_id, 'review_star_rating', false );			
			foreach( $review_ratings as $review_rating ){
				foreach ($review_rating as $key => $value) {
					 if (is_int($key)) {
					$stars_on  = $value;
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
			$this->bgr_display_star_rating( $review_rating_fields, $review_ratings );
		}

		/**
		 *  Actions performed for rating display type : Star
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 *
		 * @param array $review_rating_fields Review rating fields.
		 * @param array $review_ratings Review rating.
		 */
		public function bgr_display_star_rating( $review_rating_fields, $review_ratings ) {
			if ( ! empty( $review_rating_fields ) && ! empty( $review_ratings[0] ) ) :				
				foreach ( $review_rating_fields as $review_field ) {
					if ( array_key_exists( $review_field, $review_ratings[0] ) ) {
						?>
						<div class="multi-review">
							<div class="bgr-col-6">
								<?php echo esc_html( $review_field ) . ' : '; ?>
							</div>
							<div class="bgr-col-6">
							<?php
								/*** Ratings *****/
								$stars_on  = $review_ratings[0][ $review_field ];
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
				endif;
		}

		/**
		 *  Actions performed for rating display in  Widgets
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 *
		 * @param Array $review_groups Revie Groups.
		 */
		public function bgr_widget_average_ratings( $review_groups ) {
			global $bgr;
			$group_avg_rating = $review_groups;
			$remaining        = $group_avg_rating - (int) $group_avg_rating;

			if ( $remaining > 0 ) {
					$stars_on   = intval( $group_avg_rating );
					$stars_half = 1;
					$stars_off  = 5 - ( $stars_on + $stars_half );
			} else {
					$stars_on   = $group_avg_rating;
					$stars_off  = 5 - $group_avg_rating;
					$stars_half = 0;
			}

			$this->bgr_average_star_rating( $stars_on, $stars_half, $stars_off );

			$group_avg_rating = round( $group_avg_rating, 2 );
			echo '</div><div class="bupr-meta">';
			esc_html_e( 'Rating', 'bp-group-reviews' );
			echo ' : (' . esc_html( $group_avg_rating ) . ')';
		}

		/**
		 *  Actions performed for rating display in  Group Header
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 *
		 * @param string $avg_rating Average Rating.
		 */
		public function bgr_group_average_ratings( $avg_rating ) {
			global $bgr;
			$type = gettype( $avg_rating );
			$var  = (int) $avg_rating - $avg_rating;
			if ( 0 == $var ) {
					$stars_on   = $avg_rating;
					$stars_off  = 5 - $avg_rating;
					$stars_half = 0;
			} else {
					$stars_on   = intval( $avg_rating );
					$stars_half = 1;
					$stars_off  = 5 - ( $stars_on + $stars_half );
			}

			$this->bgr_average_star_rating( $stars_on, $stars_half, $stars_off );
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
		public function bgr_average_star_rating( $stars_on, $stars_half, $stars_off ) {
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
