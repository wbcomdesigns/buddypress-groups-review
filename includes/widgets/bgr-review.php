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
	 * Most groups one instance lists; keeps the per-row group reads bounded.
	 *
	 * @since 3.9.2
	 */
	const MAX_ITEMS = 20;

	/**
	 * Widget title from the standard `title` key, falling back to the legacy
	 * `widget_title` key so widgets saved before 3.9.2 keep their title.
	 *
	 * @since 3.9.2
	 *
	 * @param array $instance Widget instance.
	 * @return string
	 */
	public static function get_title( $instance ) {
		return (string) ( $instance['title'] ?? $instance['widget_title'] ?? '' );
	}

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
				'listing_cat' => 'topreviewed',
				'post_num'    => '5',
				'thumbnail'   => 'show',
			)
		);

		$title       = self::get_title( $instance );
		$listing_cat = $instance['listing_cat'];
		$post_num    = $instance['post_num'];
		$thumbnail   = $instance['thumbnail'];
		?>
		<div class="bgr_review_listing_form">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'bp-group-reviews' ); ?>:</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<fieldset class="bgr_review_select_listing_type">
				<legend><?php esc_html_e( 'Select Listing Type', 'bp-group-reviews' ); ?></legend>
				<label class="alignleft">
					<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'listing_cat' ) ); ?>" value="toprated" <?php checked( $listing_cat, 'toprated' ); ?> />
					<?php esc_html_e( 'Top Rated', 'bp-group-reviews' ); ?>
				</label>
				<label class="alignleft">
					<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'listing_cat' ) ); ?>" value="topreviewed" <?php checked( $listing_cat, 'topreviewed' ); ?> />
					<?php esc_html_e( 'Most Reviewed', 'bp-group-reviews' ); ?>
				</label>
			</fieldset>
			<div class="clear"></div>
			<div class="bgr_review_options">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>"><?php esc_html_e( 'Number of reviews to show', 'bp-group-reviews' ); ?>:
						<input id="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_num' ) ); ?>" type="number" min="1" max="<?php echo esc_attr( (string) self::MAX_ITEMS ); ?>" step="1" value="<?php echo esc_attr( $post_num ); ?>" />
					</label>
				</p>

				<p class="bgr_review_thumbnail">
					<label for="<?php echo esc_attr( $this->get_field_id( 'thumbnail' ) ); ?>"><?php esc_html_e( 'Thumbnail', 'bp-group-reviews' ); ?>:</label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'thumbnail' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumbnail' ) ); ?>">
						<option value="hide" <?php selected( $thumbnail, 'hide' ); ?>><?php esc_html_e( 'Hide', 'bp-group-reviews' ); ?></option>
						<option value="show" <?php selected( $thumbnail, 'show' ); ?>><?php esc_html_e( 'Show', 'bp-group-reviews' ); ?></option>
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
		$instance                = $old_instance;
		$instance['title']       = wp_strip_all_tags( $new_instance['title'] ?? '' );
		$instance['listing_cat'] = 'toprated' === ( $new_instance['listing_cat'] ?? '' ) ? 'toprated' : 'topreviewed';
		$instance['post_num']    = min( self::MAX_ITEMS, max( 1, absint( $new_instance['post_num'] ?? 5 ) ) );
		$instance['thumbnail']   = 'hide' === ( $new_instance['thumbnail'] ?? '' ) ? 'hide' : 'show';
		unset( $instance['widget_title'] );
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

		$widget_title       = apply_filters( 'bgr_widget_title', self::get_title( $instance ) );
		$widget_title       = apply_filters( 'widget_title', $widget_title, $instance, $this->id_base );
		$widget_listing_cat = apply_filters( 'bgr_listing_cat', $instance['listing_cat'] ?? 'topreviewed' );
		$widget_post_num    = min( self::MAX_ITEMS, max( 1, absint( apply_filters( 'bgr_post_num', $instance['post_num'] ?? 5 ) ) ) );
		$widget_thumbnail   = apply_filters( 'bgr_thumbnail', $instance['thumbnail'] ?? 'show' );

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

		// Select Listing Type.
		$group_args = 'toprated' === $widget_listing_cat ? $review_groups : $review_groups_count;

		$rows  = '';
		$shown = 0;
		foreach ( array_keys( $group_args ) as $gid ) {
			if ( $shown >= $widget_post_num ) {
				break;
			}
			$group = groups_get_group( (int) $gid );
			// Private and hidden groups' ratings are shown only to their members and site moderators.
			if ( empty( $group->id ) || ! bgr_can_view_group_reviews( $group ) ) {
				continue;
			}
			++$shown;
			$rows .= $this->render_row( $group, (float) ( $review_groups[ $gid ] ?? 0 ), (int) $review_groups_count[ $gid ], $review_label, 'show' === $widget_thumbnail );
		}

		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $widget_title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $widget_title ) . wp_kses_post( $args['after_title'] );
		}

		if ( '' !== $rows ) {
			echo '<ul class="bgr-widget-list">' . $rows . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render_row().
		} else {
			echo '<p class="bgr-widget-empty">' . esc_html(
				'toprated' === $widget_listing_cat
					? __( 'No ratings have been given to any groups yet.', 'bp-group-reviews' )
					/* translators: %1$s: Review label. */
					: sprintf( __( 'No %1$s has been given to any group yet!', 'bp-group-reviews' ), $review_label )
			) . '</p>';
		}
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * One group row: 50px avatar, name, stars and review count.
	 *
	 * @since 3.9.2
	 *
	 * @param BP_Groups_Group $group          Group.
	 * @param float           $average        Average rating (0 when unrated).
	 * @param int             $count          Number of reviews.
	 * @param string          $review_label   Plural review label.
	 * @param bool            $show_thumbnail Whether to print the avatar.
	 * @return string Escaped row markup.
	 */
	private function render_row( $group, $average, $count, $review_label, $show_thumbnail ) {
		$group_url = bgr_get_group_url( $group );
		/* translators: %s: average rating, e.g. "4.5". */
		$stars_label = sprintf( __( 'Rated %s out of 5', 'bp-group-reviews' ), (string) round( $average, 2 ) );
		ob_start();
		?>
		<li class="bgr-widget-row">
			<?php if ( $show_thumbnail ) : ?>
				<a class="bgr-widget-avatar" href="<?php echo esc_url( $group_url ); ?>" tabindex="-1" aria-hidden="true">
					<?php
					echo wp_kses_post(
						bp_core_fetch_avatar(
							array(
								'item_id'    => $group->id,
								'object'     => 'group',
								'type'       => 'full',
								'width'      => 50,
								'height'     => 50,
								'avatar_dir' => 'group-avatars',
								'alt'        => '',
								'class'      => 'sidebar_review_avatar avatar',
							)
						)
					);
					?>
				</a>
			<?php endif; ?>
			<div class="bgr-widget-body">
				<a class="bgr-widget-name" href="<?php echo esc_url( $group_url ); ?>"><?php echo esc_html( $group->name ); ?></a>
				<?php if ( $average > 0 ) : ?>
					<span class="bgr-widget-stars" role="img" aria-label="<?php echo esc_attr( $stars_label ); ?>">
						<?php do_action( 'bgr_display_widget_average_ratings', $average ); ?>
					</span>
				<?php endif; ?>
				<span class="bgr-widget-meta">
					<?php
					/* translators: %1$s: number of reviews, %2$s: review label (e.g. "Reviews"). */
					printf( esc_html__( '%1$s %2$s', 'bp-group-reviews' ), esc_html( number_format_i18n( $count ) ), esc_html( $review_label ) );
					?>
				</span>
			</div>
		</li>
		<?php
		return (string) ob_get_clean();
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
