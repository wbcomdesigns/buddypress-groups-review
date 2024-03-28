<?php
/**
 * BGR Group Review widget.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/widgets
 */

/**
 * BGR Group Review widget.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/widget
 */
class bgr_review_widget extends WP_Widget {

	/** Constructor
	 *
	 *  @since   1.0.0
	 *  @author  Wbcom Designs
	 */
	public function __construct() {
		$widget_ops  = array(
			'classname'   => 'widget_bp_group_review_tab',
			'description' => esc_html__( 'Display Group Reviews.', 'bp-group-reviews' ),
		);
		$control_ops = array(
			'width'  => 200,
			'height' => 350,
		);
		parent::__construct( 'bgr_group_review_widget', esc_html__( 'BP Group Review Widget', 'bp-group-reviews' ), $widget_ops, $control_ops );
	}

	/**
	 * Form display for widget in admin panel.
	 *
	 * @since   1.0.0
	 * @author  Wbcom Designs
	 * @param array $instance Widget instance.
	 */
	public function form( $instance ) {
		if ( empty( $instance ) ) {
			$instance = wp_parse_args(
				(array) $instance,
				array(
					'widget_title' => '',
					'listing_cat'  => 'topreviewed',
					'post_num'     => '5',
					'thumbnail'    => 'show',
				)
			);
		}
		extract( $instance );
		?>
		<div class="bgr_review_listing_form">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'widget_title' ) ); ?>"><?php esc_html_e( 'Title', 'bp-group-reviews' ); ?>:</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'widget_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widget_title' ) ); ?>" type="text" value="<?php echo esc_attr( $widget_title ); ?>" />
			</p>
			<h4><?php esc_html_e( 'Select Listing Type', 'bp-group-reviews' ); ?></h4>
			<div class="bgr_review_select_listing_type">
				<label class="alignleft" for="listing_toprated">
					<input type="radio" class="" id="<?php echo esc_attr( $this->get_field_id( 'listing_cat' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'listing_cat' ) ); ?>" value="<?php echo 'toprated'; ?>" 
					<?php
					if ( 'toprated' == $listing_cat ) {
						echo 'checked="checked"';
					}
					?>
						/>
						<?php esc_html_e( 'Top Rated', 'bp-group-reviews' ); ?>
				</label>
				<label class="alignleft" for="listing_topreviewed">
					<input type="radio" class="" id="<?php echo esc_attr( $this->get_field_id( 'listing_cat' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'listing_cat' ) ); ?>" value="<?php echo 'topreviewed'; ?>" 
					<?php
					if ( 'topreviewed' == $listing_cat ) {
						echo 'checked="checked"';
					}
					?>
					/>
					<?php esc_html_e( 'Most Reviewed', 'bp-group-reviews' ); ?>
				</label>
			</div>
			<div class="clear"></div>
			<div class="bgr_review_options">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>"><?php esc_html_e( 'Number of reviews to show', 'bp-group-reviews' ); ?>:
						<input id="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_num' ) ); ?>" type="number" min="1" step="1" value="<?php echo esc_attr( $post_num ); ?>" />
					</label>
				</p>

				<p class="bgr_review_thumbnail">
					<label for="<?php echo esc_attr( $this->get_field_id( 'thumbnail' ) ); ?>"><?php esc_html_e( 'Thumbnail', 'bp-group-reviews' ); ?>:</label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'thumbnail' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumbnail' ) ); ?>" style="margin-left: 12px;">
						<option value="<?php echo esc_attr( 'hide' ); ?>" <?php selected( $thumbnail, 'hide', true ); ?>><?php esc_html_e( 'Hide', 'bp-group-reviews' ); ?></option>
						<option value="<?php echo esc_attr( 'show' ); ?>" <?php selected( $thumbnail, 'show', true ); ?>><?php esc_html_e( 'Show', 'bp-group-reviews' ); ?></option>
					</select>
				</p>

				<div class="clear"></div>
			</div>
		</div><!-- .bgr_review_listing_form -->
		<?php
	}

	/**
	 * Update Widget data
	 *
	 * @param  array $new_instance New Instance.
	 * @param  array $old_instance Old Instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['widget_title'] = wp_strip_all_tags( $new_instance['widget_title'] );
		$instance['listing_cat']  = $new_instance['listing_cat'];
		$instance['post_num']     = $new_instance['post_num'];
		$instance['thumbnail']    = $new_instance['thumbnail'];
		return $instance;
	}

	/**
	 * Display Widget content
	 *
	 * @param  array $args Arguments.
	 * @param  mixed $instance Instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		global $bp;
		$post;
		global $bgr;
		$review_rating_fields = $bgr['review_rating_fields'];
		$review_label         = $bgr['review_label'];

		extract( $instance, EXTR_SKIP );
		$widget_title       = apply_filters( 'widget_title', $widget_title );
		$widget_listing_cat = apply_filters( 'listing_cat', $listing_cat );
		$widget_post_num    = apply_filters( 'post_num', $post_num );
		$widget_thumbnail   = apply_filters( 'thumbnail', $thumbnail );

		$custom_args         = array(
			'post_type'      => 'review',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'category'       => 'review_category',
			'meta_key'       => 'linked_group',
		);
		$reviews             = get_posts( $custom_args );
		$review_groups       = array();
		$review_groups_count = array();
		$single_review_count = 0;

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
						$rev_avg                       = $total_review / $single_review_count;
						$single_rev_avg[ $review->ID ] = array( $linked_group[0] => $rev_avg );
					}
				}

				// Review count.
				if ( ! empty( $linked_group[0] ) ) {
					if ( array_key_exists( $linked_group[0], $review_groups_count ) ) {
						$value                                   = $review_groups_count[ $linked_group[0] ];
						$review_groups_count[ $linked_group[0] ] = (int) $value + 1;
					} else {
						$review_groups_count[ $linked_group[0] ] = 1;
					}
				}
			}
			/*** Sort Group ids according reviews & ratings */
			if ( ! empty( $single_rev_avg ) ) {
				$review_groups = array();
				$rev_arr       = array();
				foreach ( $single_rev_avg as $single_avg ) {
					foreach ( $single_avg as $gid => $avg ) {
						if ( array_key_exists( $gid, $review_groups ) ) {
							$rev_arr = $review_groups[ $gid ];
							array_push( $rev_arr, $avg );
							$review_groups[ $gid ] = $rev_arr;
						} else {
							$review_groups[ $gid ] = array( $avg );
						}
					}
				}
				arsort( $review_groups_count );
				/**** Count Average */
				if ( ! empty( $review_groups ) ) {
					foreach ( $review_groups as $key => $val ) {
						$count = count( $val );
						if ( $count > 1 ) {
							$sum                   = array_sum( array_values( $val ) );
							$review_groups[ $key ] = $sum / $count;
						} else {
							$review_groups[ $key ] = $val[0];
						}
					}
					arsort( $review_groups );
				}
			}
		}

		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $widget_title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $widget_title ) . wp_kses_post( $args['after_title'] );
		}

		/*** Select Listing Type */
		if ( 'toprated' == $widget_listing_cat ) {
			$group_args = $review_groups;
		} elseif ( 'topreviewed' == $widget_listing_cat ) {
			$group_args = $review_groups_count;
		} else {
			$group_args = '';
		}

		$post_num_counter = 0;
		if ( ! empty( $group_args ) ) {
			$gids = array_keys( $group_args );
			echo '<ul class="bupr-group-main">';
			foreach ( $gids as $gid ) {
				if ( $post_num_counter < $widget_post_num ) :
					$group          = groups_get_group( array( 'group_id' => $gid ) );
					$group_url      = bp_get_group_url( $group );
					$avatar_options = array(
						'item_id'    => $gid,
						'object'     => 'group',
						'type'       => 'full',
						'avatar_dir' => 'group-avatars',
						'alt'        => 'Group avatar',
						'class'      => 'sidebar_review_avatar avatar',
					);
					if ( $group->id !== 0 ) {
						?>
						<li class="bupr-group">
							<?php if ( 'show' == $widget_thumbnail ) { ?>
								<div class="bupr-img-widget">
									<a href="<?php echo esc_url( $group_url ); ?>"><?php echo wp_kses_post( bp_core_fetch_avatar( $avatar_options ) ); ?></a>
								</div>
							<?php } ?>
							<div class="bupr-content-widget">
								<div class="bupr-group-title"><a href="<?php echo esc_url( $group_url ); ?>"><?php echo esc_html( $group->name ); ?></a></div>
								<div class="bupr-group-rating">										
									<?php
									if ( ! empty( $review_groups ) ) {
										if ( array_key_exists( $gid, $review_groups ) ) {
											do_action( 'bgr_display_widget_average_ratings', $review_groups[ $gid ] );
											?>
											<div class="buddypress-groups-reviews-stars" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
												<span itemprop="ratingValue"  content="<?php echo esc_attr( $review_groups[ $gid ] ); ?>"></span>
												<span itemprop="bestRating"   content="5"></span>

												<span itemprop="reviewCount"  content="<?php echo esc_attr( $review_groups_count[ $gid ] ); ?>"></span>
												<span itemprop="itemReviewed" content="Person"></span>
												<span itemprop="name" content="<?php echo esc_attr( $group->name ); ?>"></span>
												<span itemprop="url" content="<?php echo esc_attr( $group_url ); ?>"></span>
											</div>
											<?php
										}
									}
									?>
								</div>
								<div class="bupr-meta">
									<?php
									esc_html_e( 'Total', 'bp-group-reviews' );
									echo ' ' . esc_html( $review_label ) . ' : ' . esc_html( $review_groups_count[ $gid ] );
									?>
								</div>
							<?php } ?>	
						</div>
					</li>						
					<?php
				endif;
				$post_num_counter++;
			}
			echo '</ul>';
		} else {
			if ( 'toprated' == $widget_listing_cat ) {
				?>
				<div id="message" class="info">
					<?php
					esc_html_e( 'No Rating has been given to any group yet!', 'bp-group-reviews' );
					?>
				</div>
			<?php } else { ?>
				<div id="message" class="info">
					<?php
					/* translators: %1$s is replaced with review_label */
					echo sprintf( esc_html__( 'No %1$s has been given to any group yet!', 'bp-group-reviews' ), esc_html( $review_label ) );
					?>
				</div>
				<?php
			}
		}
		echo wp_kses_post( $args['after_widget'] );
	}

}

/** Register Group Review Widget
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bgr_review_register_widget() {
	register_widget( 'bgr_review_widget' );
}

add_action( 'widgets_init', 'bgr_review_register_widget' );
