<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- Legacy file name.
/**
 * BGR Group Review rating.
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
 * BGR Group Review rating.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/widgets
 */
class Bgr_Single_Group_Rating_Widget extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
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
	 * Most reviews one instance lists; keeps the review read bounded.
	 *
	 * @since 3.9.2
	 */
	const MAX_ITEMS = 20;

	/**
	 * Form display for widget in admin panel.
	 *
	 * @since 1.0.0
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		$instance       = wp_parse_args(
			(array) $instance,
			array(
				'post_num'       => '5',
				'rating_default' => 'latest',
			)
		);
		$title          = Bgr_Review_Widget::get_title( $instance );
		$post_num       = $instance['post_num'];
		$rating_default = $instance['rating_default'];
		?>
		<div class="bgr_review_listing_form">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'bp-group-reviews' ); ?>:</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>"><?php esc_html_e( 'Number of reviews to show', 'bp-group-reviews' ); ?>:
					<input id="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_num' ) ); ?>" type="number" min="1" max="<?php echo esc_attr( (string) self::MAX_ITEMS ); ?>" step="1" value="<?php echo esc_attr( $post_num ); ?>" />
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
	 * @since 1.0.0
	 * @param array $new_instance New Instance.
	 * @param array $old_instance Old Instance.
	 * @return array Updated instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = wp_strip_all_tags( $new_instance['title'] ?? '' );
		$instance['post_num']       = min( self::MAX_ITEMS, max( 1, absint( $new_instance['post_num'] ?? 5 ) ) );
		$instance['rating_default'] = in_array( $new_instance['rating_default'] ?? '', array( 'highest', 'lowest' ), true ) ? $new_instance['rating_default'] : 'latest';
		unset( $instance['widget_title'] );
		return $instance;
	}

	/**
	 * Display Widget content.
	 *
	 * Contextual: shows the ratings of the group being viewed, so it renders
	 * only on single group pages (place it in the group sidebar area).
	 *
	 * @since 1.0.0
	 * @param array $args     Widget arguments.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$group_id = (int) bp_get_current_group_id();
		if ( ! $group_id || ! bgr_can_view_group_reviews( $group_id ) ) {
			return;
		}
		$widget_title          = Bgr_Review_Widget::get_title( $instance );
		if ( '' === $widget_title ) {
			/* translators: %s: Group name. */
			$widget_title = sprintf( __( "%s's Ratings", 'bp-group-reviews' ), bp_get_current_group_name() );
		}
		$widget_title          = apply_filters( 'widget_title', $widget_title, $instance, $this->id_base );
		$widget_post_num       = min( self::MAX_ITEMS, max( 1, absint( apply_filters( 'post_num', $instance['post_num'] ?? 5 ) ) ) );
		$widget_rating_default = apply_filters( 'rating_default', $instance['rating_default'] ?? 'latest' );

		// Cached per-review rating index (review_id => average, newest first)
		// replaces the previous unbounded get_posts( -1 ) + N+1 meta scan.
		$stats          = bgr_get_group_review_stats( $group_id );
		$single_rev_avg = array_filter( $stats['index'] );

		if ( 'highest' === $widget_rating_default ) {
			arsort( $single_rev_avg, SORT_NUMERIC );
		} elseif ( 'lowest' === $widget_rating_default ) {
			asort( $single_rev_avg, SORT_NUMERIC );
		}

		// Bound the working set to the widget's own display limit.
		$single_rev_avg = array_slice( $single_rev_avg, 0, $widget_post_num, true );

		$final_review_obj = array();
		if ( ! empty( $single_rev_avg ) ) {
			$review_posts = get_posts(
				array(
					'post_type'      => 'review',
					'post_status'    => 'publish',
					'posts_per_page' => count( $single_rev_avg ),
					'post__in'       => array_keys( $single_rev_avg ),
					'orderby'        => 'post__in',
				)
			);
			foreach ( $review_posts as $review_post ) {
				$final_review_obj[ $review_post->ID ] = $review_post;
			}
		}
		$rows = self::render_rows( $single_rev_avg, $final_review_obj, $widget_post_num );

		echo wp_kses_post( $args['before_widget'] );
		if ( '' !== $widget_title ) {
			echo wp_kses_post( $args['before_title'] . $widget_title . $args['after_title'] );
		}

		if ( '' !== $rows ) {
			$filters = array(
				'latest'  => __( 'Latest', 'bp-group-reviews' ),
				'highest' => __( 'Highest', 'bp-group-reviews' ),
				'lowest'  => __( 'Lowest', 'bp-group-reviews' ),
			);
			?>
			<div class="bgr-widget-filters" id="bp-group-rating-list-options" role="group" aria-label="<?php esc_attr_e( 'Sort ratings', 'bp-group-reviews' ); ?>">
				<?php foreach ( $filters as $filter => $label ) : ?>
					<button type="button" class="bgr-widget-filter<?php echo $filter === $widget_rating_default ? ' selected' : ''; ?>" data-filter="<?php echo esc_attr( $filter ); ?>" aria-pressed="<?php echo $filter === $widget_rating_default ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</div>
			<ul class="bgr-widget-list" id="bp-group-rating" aria-live="polite"><?php echo $rows; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render_rows(). ?></ul>
			<input type="hidden" value="<?php echo esc_attr( (string) $widget_post_num ); ?>" class="group-rating-limit">
			<?php
		} else {
			echo '<p class="bgr-widget-empty">' . esc_html( Bgr_Review_Widget::empty_text() ) . '</p>';
		}
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Reviewer rows for the widget and its sort AJAX: 50px avatar, name, stars.
	 *
	 * @since 3.9.2
	 *
	 * @param array $averages Review ID => average rating, in display order.
	 * @param array $reviews  Review ID => WP_Post.
	 * @param int   $limit    Most rows to return.
	 * @return string Escaped `<li>` markup, '' when there is nothing to show.
	 */
	public static function render_rows( $averages, $reviews, $limit ) {
		$html = '';
		foreach ( $averages as $review_id => $average ) {
			if ( $limit < 1 ) {
				break;
			}
			if ( empty( $reviews[ $review_id ] ) ) {
				continue;
			}
			--$limit;
			$author = (int) $reviews[ $review_id ]->post_author;
			/* translators: %s: average rating, e.g. "4.5". */
			$stars_label = sprintf( __( 'Rated %s out of 5', 'bp-group-reviews' ), (string) round( (float) $average, 2 ) );
			ob_start();
			do_action( 'bgr_display_widget_average_ratings', $average );
			$stars = (string) ob_get_clean();

			$html .= '<li class="bgr-widget-row">'
				. '<span class="bgr-widget-avatar">' . get_avatar( $author, 50, '', '', array( 'class' => 'sidebar_review_avatar' ) ) . '</span>'
				. '<div class="bgr-widget-body">'
				. '<span class="bgr-widget-name">' . wp_kses_post( bp_core_get_userlink( $author ) ) . '</span>'
				. '<span class="bgr-widget-stars" role="img" aria-label="' . esc_attr( $stars_label ) . '">' . $stars . '</span>'
				. '</div></li>';
		}
		return $html;
	}
}

/**
 * Register Group Review Widget.
 *
 * @since 1.0.0
 */
function bp_group_review_register_rating_widget() {
	register_widget( 'Bgr_Single_Group_Rating_Widget' );
}

add_action( 'widgets_init', 'bp_group_review_register_rating_widget' );
