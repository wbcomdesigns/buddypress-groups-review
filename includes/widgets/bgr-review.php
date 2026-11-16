<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- Legacy file name.
/**
 * BGR Group Review widget.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/widgets
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed -- Legacy widget registration.

/**
 * BGR Group Review widget.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/widget
 */
class Bgr_Review_Widget extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 * @param array $instance Widget instance.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'widget_title' => '',
				'listing_cat'  => 'topreviewed',
				'post_num'     => '5',
				'thumbnail'    => 'show',
			)
		);

		// Use direct array access instead of extract() for security.
		$widget_title = $instance['widget_title'];
		$listing_cat  = $instance['listing_cat'];
		$post_num     = $instance['post_num'];
		$thumbnail    = $instance['thumbnail'];
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
					if ( 'toprated' === $listing_cat ) {
						echo 'checked="checked"';
					}
					?>
						/>
						<?php esc_html_e( 'Top Rated', 'bp-group-reviews' ); ?>
				</label>
				<label class="alignleft" for="listing_topreviewed">
					<input type="radio" class="" id="<?php echo esc_attr( $this->get_field_id( 'listing_cat' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'listing_cat' ) ); ?>" value="<?php echo 'topreviewed'; ?>"
					<?php
					if ( 'topreviewed' === $listing_cat ) {
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
	 * Update Widget data.
	 *
	 * @param array $new_instance New Instance.
	 * @param array $old_instance Old Instance.
	 * @return array Updated instance.
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
	 * Display Widget content.
	 *
	 * @param array $args     Arguments.
	 * @param mixed $instance Instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		global $bgr;
		$review_label = $bgr['review_label'];

		// Use direct array access instead of extract() for security.
		$widget_title       = apply_filters( 'bgr_widget_title', $instance['widget_title'] );
		$widget_listing_cat = apply_filters( 'bgr_listing_cat', $instance['listing_cat'] );
		$widget_post_num    = apply_filters( 'bgr_post_num', $instance['post_num'] );
		$widget_thumbnail   = apply_filters( 'bgr_thumbnail', $instance['thumbnail'] );

		// Rank every reviewed group from the cached per-group stats, so the widget
		// matches the group header's rating and never loads reviews itself. The
		// ranking is shared and cleared by invalidate_group_review_cache().
		$ranking = get_transient( 'bgr_widget_group_ranking' );
		if ( ! is_array( $ranking ) ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Cached in the transient above.
			$group_ids = $wpdb->get_col(
				"SELECT DISTINCT pm.meta_value FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'linked_group'
				WHERE p.post_type = 'review' AND p.post_status = 'publish'"
			);
			$ranking   = array();
			// ponytail: one cached stats read per reviewed group on a cold cache; fine to a few thousand groups.
			foreach ( $group_ids as $group_id ) {
				$stats = bgr_get_group_review_stats( $group_id );
				if ( $stats['total_reviews'] ) {
					$ranking[ (int) $group_id ] = array(
						'average' => $stats['average'],
						'count'   => $stats['total_reviews'],
					);
				}
			}
			set_transient( 'bgr_widget_group_ranking', $ranking, DAY_IN_SECONDS );
		}

		$review_groups       = array_filter( wp_list_pluck( $ranking, 'average' ) );
		$review_groups_count = wp_list_pluck( $ranking, 'count' );
		arsort( $review_groups );
		arsort( $review_groups_count );

		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $widget_title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $widget_title ) . wp_kses_post( $args['after_title'] );
		}

		// Select Listing Type.
		if ( 'toprated' === $widget_listing_cat ) {
			$group_args = $review_groups;
		} elseif ( 'topreviewed' === $widget_listing_cat ) {
			$group_args = $review_groups_count;
		} else {
			$group_args = '';
		}

		$post_num_counter = 0;
		if ( ! empty( $group_args ) ) {
			$gids = array_keys( $group_args );
			echo '<ul class="bupr-group-main">';
			foreach ( $gids as $gid ) {
				if ( $post_num_counter >= $widget_post_num ) {
					break;
				}
				$group = groups_get_group( array( 'group_id' => $gid ) );
				// Private and hidden groups' ratings are shown only to their members and site moderators.
				if ( empty( $group->id ) || ! bgr_can_view_group_reviews( $group ) ) {
					continue;
				}
				++$post_num_counter;
				// Build group URL safely for both BuddyPress and BuddyBoss.
				if ( function_exists( 'bp_get_group_url' ) ) {
					$group_url = bp_get_group_url( $group );
				} else {
					// Fallback for older versions - build URL manually.
					$group_url = bp_get_groups_directory_permalink() . $group->slug . '/';
				}
				$avatar_options = array(
					'item_id'    => $gid,
					'object'     => 'group',
					'type'       => 'full',
					'avatar_dir' => 'group-avatars',
					'alt'        => esc_attr__( 'Group avatar', 'bp-group-reviews' ),
					'class'      => 'sidebar_review_avatar avatar',
				);
				?>
					<li class="bupr-group">
						<?php if ( 'show' === $widget_thumbnail ) { ?>
							<div class="bupr-img-widget">
								<a href="<?php echo esc_url( $group_url ); ?>"><?php echo wp_kses_post( bp_core_fetch_avatar( $avatar_options ) ); ?></a>
							</div>
						<?php } ?>
						<div class="bupr-content-widget">
							<div class="bupr-group-title"><a href="<?php echo esc_url( $group_url ); ?>"><?php echo esc_html( $group->name ); ?></a></div>
							<?php if ( ! empty( $review_groups[ $gid ] ) ) : ?>
								<div class="bupr-group-rating"><?php do_action( 'bgr_display_widget_average_ratings', $review_groups[ $gid ] ); ?></div>
								<div class="bupr-meta">
									<?php
									/* translators: %s: average star rating value, e.g. "4.5". */
									printf( esc_html__( 'Rating : (%s)', 'bp-group-reviews' ), esc_html( round( (float) $review_groups[ $gid ], 2 ) ) );
									?>
								</div>
							<?php endif; ?>
							<div class="bupr-meta">
								<?php
								/* translators: %1$s: review label (e.g. "Reviews"), %2$s: number of reviews. */
								printf( esc_html__( 'Total %1$s : %2$s', 'bp-group-reviews' ), esc_html( $review_label ), esc_html( $review_groups_count[ $gid ] ) );
								?>
							</div>
						</div>
					</li>
				<?php
			}
			echo '</ul>';
		} elseif ( 'toprated' === $widget_listing_cat ) {
			?>
				<div id="message" class="info">
					<?php
					esc_html_e( 'No ratings have been given to any groups yet.', 'bp-group-reviews' );
					?>
				</div>
			<?php } else { ?>
				<div id="message" class="info">
					<?php
					/* translators: %1$s: Review label. */
					printf( esc_html__( 'No %1$s has been given to any group yet!', 'bp-group-reviews' ), esc_html( $review_label ) );
					?>
				</div>
				<?php

			}
			echo wp_kses_post( $args['after_widget'] );
	}
}

/**
 * Register Group Review Widget.
 *
 * @since 1.0.0
 */
function bp_group_review_register_widget() {
	register_widget( 'Bgr_Review_Widget' );
}

add_action( 'widgets_init', 'bp_group_review_register_widget' );
