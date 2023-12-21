<?php
/**
 * BGR Group Review rating.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/widgets
 */

/**
 * BGR Group Review rating.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/widgets
 */
class bgr_single_group_rating_widget extends WP_Widget {

	/** Constructor
	 *
	 *  @since   1.0.0
	 *  @author  Wbcom Designs
	 */
	public function __construct() {
		$widget_ops  = array(
			'classname'   => 'widget_bp_group_review_tab buddypress',
			'description' => esc_html__( 'Display displayed group ratings.', 'bp-group-reviews' ),
		);
		$control_ops = array(
			'width'  => 200,
			'height' => 350,
		);
		parent::__construct( 'bgr_group_rating_widget', esc_html__( 'BP Displayed Group Rating Widget', 'bp-group-reviews' ), $widget_ops, $control_ops );
	}

	/**
	 * Form display for widget in admin panel.
	 *
	 *  @since   1.0.0
	 *  @author  Wbcom Designs
	 * @param  array $instance Instance.
	 */
	public function form( $instance ) {
		if ( empty( $instance ) ) {
			$instance = wp_parse_args(
				(array) $instance,
				array(
					'widget_title'   => '',
					'post_num'       => '5',
					'rating_default' => 'latest',
				)
			);
		}
		extract( $instance );
		$rating_default = esc_attr( $instance['rating_default'] );
		?>
		<div class="bgr_review_listing_form">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'widget_title' ) ); ?>"><?php esc_html_e( 'Title', 'bp-group-reviews' ); ?>:</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'widget_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widget_title' ) ); ?>" type="text" value="<?php echo esc_attr( $widget_title ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>"><?php esc_html_e( 'Number of reviews to show', 'bp-group-reviews' ); ?>:
					<input id="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_num' ) ); ?>" type="number" min="1" step="1" value="<?php echo esc_attr( $post_num ); ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'rating_default' ) ); ?>"><?php esc_html_e( 'Default ratings to show:', 'bp-group-reviews' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'rating_default' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'rating_default' ) ); ?>">
					<option value="latest" <?php selected( $rating_default, 'latest' ); ?>><?php esc_html_e( 'Latest', 'bp-group-reviews' ); ?></option>
					<option value="highest" <?php selected( $rating_default, 'highest' ); ?>><?php esc_html_e( 'Highest', 'bp-group-reviews' ); ?></option>
					<option value="lowest"  <?php selected( $rating_default, 'lowest' ); ?>><?php esc_html_e( 'Lowest', 'bp-group-reviews' ); ?></option>
				</select>
			</p>
		</div><!-- .bgr_review_listing_form -->
		<?php
	}

	/**
	 * Update Widget data.
	 *
	 *  @since   1.0.0
	 *  @author  Wbcom Designs
	 * @param  array $new_instance New Instance.
	 * @param  array $old_instance Old Instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['widget_title']   = wp_strip_all_tags($new_instance['widget_title']);
		$instance['post_num']       = $new_instance['post_num'];
		$instance['rating_default'] = $new_instance['rating_default'];
		return $instance;
	}

	/**
	 * Display Widget content.
	 *
	 *  @since   1.0.0
	 *  @author  Wbcom Designs
	 *
	 * @param  array $args Instance.
	 * @param  array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		global $bp, $post;
		global $bgr;
		if ( ! bp_get_current_group_id() ) {
			return;
		}
		$review_rating_fields = $bgr['review_rating_fields'];
		$review_label         = $bgr['review_label'];

		extract( $instance, EXTR_SKIP );
		$widget_title          = apply_filters( 'widget_title', $widget_title );
		$widget_post_num       = apply_filters( 'post_num', $post_num );
		$widget_rating_default = apply_filters( 'rating_default', $rating_default );

		$custom_args         = array(
			'post_type'      => 'review',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'category'       => 'review_category',
			'meta_key'       => 'linked_group',
			'meta_value'     => bp_get_current_group_id(),
		);
		$reviews             = get_posts( $custom_args );
		$single_review_count = 0;
		$final_review_obj    = array();
		if ( ! empty( $reviews ) ) {
			$single_rev_avg = array();
			foreach ( $reviews as $review ) {
				$linked_group   = get_post_meta( $review->ID, 'linked_group', false );
				$review_ratings = get_post_meta( $review->ID, 'review_star_rating', false );
				if ( ! empty( $review_ratings ) && ! empty( $review_rating_fields ) ) {
					$rev_rating_array    = $review_ratings[0];
					$total_review        = 0;
					$single_review_count = 0;
					foreach ( $review_rating_fields as $rating_field ) {
						if ( array_key_exists( $rating_field, $rev_rating_array ) ) {
							$total_review += $rev_rating_array[ $rating_field ];
							$single_review_count++;
						}
					}
					if ( ! empty( $single_review_count ) ) {
						$rev_avg                         = $total_review / $single_review_count;
						$single_rev_avg[ $review->ID ]   = $rev_avg;
						$final_review_obj[ $review->ID ] = $review;
					}
				}
			}
		}
		echo wp_kses_post( $args['before_widget'] );
		if ( empty( $widget_title ) ) {
			$group      = groups_get_group( array( 'group_id' => bp_get_current_group_id() ) );
			$group_name = $group->name;
			/* translators: %s is replaced with group_name */
			$widget_title = sprintf( __( "%s's Ratings", 'bp-group-reviews' ), $group_name );
		}
		echo wp_kses_post( $args['before_title'] . $widget_title . $args['after_title'] );

		$bgr_user_count = 0;
		if ( ! empty( $single_rev_avg ) ) {
			if ( 'highest' == $widget_rating_default ) {
				arsort( $single_rev_avg );
			} elseif ( 'lowest' == $widget_rating_default ) {
				asort( $single_rev_avg );
			} else {
				$single_rev_avg = $single_rev_avg;
			}
			?>
			<div class="item-options" id="bp-group-rating-list-options">
				<a href="#" attr-val="latest" id="member_latest_reviews"
				<?php
				if ( $rating_default == 'latest' ) :
					?>
					class="selected"<?php endif; ?>><?php esc_html_e( 'Latest', 'bp-group-reviews' ); ?></a>
				| <a href="#" attr-val="highest" id="member_good_reviews"
				<?php
				if ( $rating_default == 'highest' ) :
					?>
					class="selected"<?php endif; ?>><?php esc_html_e( 'Highest', 'bp-group-reviews' ); ?></a>
				| <a href="#" attr-val="lowest" id="member_bad_reviews"
				<?php
				if ( $rating_default == 'lowest' ) :
					?>
					class="selected"<?php endif; ?>><?php esc_html_e( 'Lowest', 'bp-group-reviews' ); ?></a>
			</div>
			<?php
			echo '<ul class="item-list" id="bp-group-rating">';
			foreach ( $single_rev_avg as $bgrKey => $bgrValue ) {
				if ( $bgr_user_count == $widget_post_num ) {
					break;
				} else {
					echo '<li class="vcard"><div class="item-avatar">';
					echo get_avatar( $final_review_obj[ $bgrKey ]->post_author, 65 );
					echo '</div>';
					echo '<div class="item">';

					$members_profile = bp_core_get_userlink( $final_review_obj[ $bgrKey ]->post_author );
					echo '<div class="item-title fn">';
					echo wp_kses_post( $members_profile );
					echo '</div>';

					$bgr_avg_rating = $bgrValue;
					$stars_on       = $stars_off = $stars_half = '';
					$remaining      = $bgr_avg_rating - (int) $bgr_avg_rating;
					if ( $remaining > 0 ) {
						$stars_on       = intval( $bgr_avg_rating );
						$stars_half     = 1;
						$bgr_half_squar = 1;
						$stars_off      = 5 - ( $stars_on + $stars_half );
					} else {
						$stars_on   = $bgr_avg_rating;
						$stars_off  = 5 - $bgr_avg_rating;
						$stars_half = 0;
					}
					echo '<div class="item-meta">';
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

					echo '</div>';

					$bgr_avg_rating = round( $bgr_avg_rating, 2 );
					echo '<span class="bgr-meta">';
					/* translators: %1$s is replaced with $bgr_avg_rating */
					echo sprintf( esc_html__( 'Rating : ( %1$s )', 'bp-group-reviews' ), esc_html( $bgr_avg_rating ) );
					echo '</span>';
					echo '</div></li>';

				}

				$bgr_user_count++;
			}
			echo '</ul>';
		} else {
			?>
			<div id="message" class="info">
			<?php
			esc_html_e( 'No Rating has been given by any member yet!', 'bp-group-reviews' );
			?>
			</div>
			<?php
		}
		?>
		<input type="hidden" value="<?php echo esc_attr( $widget_post_num ); ?>" class="group-rating-limit">
		<?php
		echo wp_kses_post( $args['after_widget'] );
	}

}

/** Register Group Review Widget
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bgr_group_rating_register_widget() {
	register_widget( 'bgr_single_group_rating_widget' );
}

add_action( 'widgets_init', 'bgr_group_rating_register_widget' );
