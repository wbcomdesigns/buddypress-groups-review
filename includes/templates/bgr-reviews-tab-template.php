<?php
/**
 * BGR Group Review tab content.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/templates
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
global $bp, $post, $wp;
global $bgr;
$bgr_current_user     = wp_get_current_user();
$member_id            = $bgr_current_user->ID;
$reviews_per_page     = $bgr['reviews_per_page'];
$review_rating_fields = $bgr['review_rating_fields'];
$review_label         = $bgr['review_label'];
$bgr_paged            = 1;
$current_page         = max( 1, get_query_var( 'paged', 1 ) ); // Ensures default value is 1.

if ( 'reviews' !== basename( home_url( $wp->request ) ) ) {
	$bgr_paged = basename( home_url( $wp->request ) );
}

// Handle sorting parameter (sanitized input, no state change).
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Sorting is display-only, no data modification.
$sort_by      = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'newest';
$bgr_order    = 'DESC';
$bgr_order_by = 'date';

if ( 'oldest' === $sort_by ) {
	$bgr_order = 'ASC';
}

// For rating-based sorting, we need to get all reviews and sort in PHP.
$sort_by_rating = in_array( $sort_by, array( 'highest', 'lowest' ), true );

if ( $sort_by_rating ) {
	// Get all reviews first for rating-based sorting.
	$args = array(
		'post_type'      => 'review',
		'post_status'    => 'publish',
		'category'       => 'group',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'     => 'linked_group',
				'value'   => bp_get_group_id(),
				'compare' => '=',
			),
		),
	);

	$all_reviews = get_posts( $args );

	// Calculate average rating for each review.
	$reviews_with_ratings = array();
	foreach ( $all_reviews as $review_post ) {
		$review_ratings = get_post_meta( $review_post->ID, 'review_star_rating', true );
		$avg_rating     = 0;
		if ( ! empty( $review_ratings ) && is_array( $review_ratings ) ) {
			$avg_rating = array_sum( $review_ratings ) / count( $review_ratings );
		}
		$reviews_with_ratings[] = array(
			'post'   => $review_post,
			'rating' => $avg_rating,
		);
	}

	// Sort by rating.
	usort(
		$reviews_with_ratings,
		function ( $a, $b ) use ( $sort_by ) {
			if ( 'highest' === $sort_by ) {
				return $b['rating'] <=> $a['rating'];
			}
			return $a['rating'] <=> $b['rating'];
		}
	);

	// Manual pagination.
	$total_reviews = count( $reviews_with_ratings );
	$offset        = ( $current_page - 1 ) * $reviews_per_page;
	$paged_reviews = array_slice( $reviews_with_ratings, $offset, $reviews_per_page );

	// Create a mock query object for template compatibility.
	$reviews                 = new stdClass();
	$reviews->posts          = array_column( $paged_reviews, 'post' );
	$reviews->post_count     = count( $paged_reviews );
	$reviews->found_posts    = $total_reviews;
	$reviews->max_num_pages  = ceil( $total_reviews / $reviews_per_page );
	$reviews->current_post   = -1;
	$reviews->in_the_loop    = false;
	$reviews->is_rating_sort = true;
} else {
	$args = array(
		'post_type'      => 'review',
		'post_status'    => 'publish',
		'category'       => 'group',
		'posts_per_page' => $reviews_per_page,
		'paged'          => $current_page,
		'orderby'        => $bgr_order_by,
		'order'          => $bgr_order,
		'meta_query'     => array(
			array(
				'key'     => 'linked_group',
				'value'   => bp_get_group_id(),
				'compare' => '=',
			),
		),
	);

	$reviews = new WP_Query( $args );
}
?>
<div class="bgr-group-reviews-block">

	<div class="group-reviews">
		<?php
		// Check for reviews - handle both WP_Query and mock stdClass for rating sorting.
		$has_reviews_to_display = $sort_by_rating ? ! empty( $reviews->posts ) : $reviews->have_posts();
		?>
		<?php if ( $has_reviews_to_display ) : ?>
		<div class="bgr-reviews-sorting">
			<label for="bgr-sort-reviews"><?php esc_html_e( 'Sort by:', 'bp-group-reviews' ); ?></label>
			<select id="bgr-sort-reviews" onchange="window.location.href=this.value;">
				<?php
				$base_url = remove_query_arg( array( 'sort', 'paged' ) );
				$options  = array(
					'newest'  => __( 'Newest First', 'bp-group-reviews' ),
					'oldest'  => __( 'Oldest First', 'bp-group-reviews' ),
					'highest' => __( 'Highest Rated', 'bp-group-reviews' ),
					'lowest'  => __( 'Lowest Rated', 'bp-group-reviews' ),
				);
				foreach ( $options as $value => $label ) :
					$url      = add_query_arg( 'sort', $value, $base_url );
					$selected = ( $sort_by === $value ) ? 'selected' : '';
					?>
					<option value="<?php echo esc_url( $url ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endif; ?>
		<div id="group-reviews-list">
			<div id="request-review-list" class="item-list">
				<?php
				// Check if we have posts (works for both WP_Query and rating-sorted array).
				$has_reviews = $sort_by_rating ? ! empty( $reviews->posts ) : $reviews->have_posts();

				if ( $has_reviews ) {
					// Get posts array for iteration.
					$review_posts = $sort_by_rating ? $reviews->posts : $reviews->posts;

					foreach ( $review_posts as $review_post ) :
						// Set up post data.
						if ( ! $sort_by_rating ) {
							$reviews->the_post();
							$current_review = $post;
						} else {
							$current_review = $review_post;
							setup_postdata( $current_review );
						}

						$author      = $current_review->post_author;
						$review_id   = $current_review->ID;
						$trimcontent = $current_review->post_content;
						?>
							<div class="bgr-row item-list group-request-list">
								<div class="bgr-group-profiles">
									<?php bp_displayed_user_avatar( array( 'item_id' => $author ) ); ?>
								</div>
								<div class="bgr-group-content">
									<div class="reviewer">
										<b><?php echo wp_kses_post( bp_core_get_userlink( $author ) ); ?></b>
									</div>

									<div class="item-description">
										<div class="review-description">
											<?php
											// Build group URL safely for both BuddyPress and BuddyBoss.
											if ( function_exists( 'bp_get_group_url' ) ) {
												$review_url = bp_get_group_url() . sanitize_title( bp_group_review_tab_name() ) . '/view/' . $review_id;
											} else {
												// Fallback for older versions.
												$review_url = bp_get_group_permalink() . sanitize_title( bp_group_review_tab_name() ) . '/view/' . $review_id;
											}
											if ( ! empty( $trimcontent ) ) {
												$len = strlen( $trimcontent );
												if ( $len > 150 ) {
													$shortexcerpt = substr( $trimcontent, 0, 150 );
													echo wp_kses_post( $shortexcerpt );
													?>
														<a href="<?php echo esc_url( $review_url ); ?>"><i><b><?php esc_html_e( 'read more...', 'bp-group-reviews' ); ?></b></i></a>
													<?php
												} else {
													echo wp_kses_post( $trimcontent );
												}
											}
											?>
												<div class="review-ratings">
													<?php do_action( 'bgr_display_ratings', $review_id ); ?>
												</div>
										</div>
									</div>
								</div>
								<div class="bgr-col-3">
									<?php if ( groups_is_user_admin( $member_id, bp_get_group_id() ) ) : ?>
										<div class='remove-review generic-button'>
											<a href="javascript:void(0);" class='remove-review-button button'> <?php esc_html_e( 'Delete', 'bp-group-reviews' ); ?> </a>
											<input type="hidden" name="remove_review_id" value="<?php echo esc_attr( $review_id ); ?>">
										</div>
									<?php endif; ?>
								</div>

								<div class="clear"></div>
							</div>

						<?php
					endforeach;
					$total_pages = $reviews->max_num_pages;
					if ( $total_pages > 1 ) {
						// Build base URL with sort parameter preserved.
						$pagination_base = get_pagenum_link( 1 );
						if ( 'newest' !== $sort_by ) {
							$pagination_base = add_query_arg( 'sort', $sort_by, $pagination_base );
						}
						$format = ( strpos( $pagination_base, '?' ) !== false ) ? '&paged=%#%' : '?paged=%#%';
						?>
							<div class="review-pagination">
								<?php
								echo wp_kses_post(
									paginate_links(
										array(
											'base'      => $pagination_base . '%_%',
											'format'    => $format,
											'current'   => $current_page,
											'total'     => $total_pages,
											'prev_text' => esc_html__( 'prev', 'bp-group-reviews' ),
											'next_text' => esc_html__( 'next', 'bp-group-reviews' ),
										)
									)
								);
								?>

							</div>
							<?php
					}
					wp_reset_postdata();

				} else {

					$bp_template_option = bp_get_option( '_bp_theme_package_id' );
					if ( 'nouveau' === $bp_template_option ) {
						?>
					<div id="message" class="info bp-feedback bp-messages bp-template-notice">
						<span class="bp-icon" aria-hidden="true"></span>
					<?php } else { ?>
						<div id="message" class="info">
						<?php
					}
					/* translators: %1$s is replaced with review_label */
						echo '<p>' . sprintf( esc_html__( 'No %1$s found yet.', 'bp-group-reviews' ), esc_html( strtolower( $review_label ) ) ) . '</p>';
					?>
					</div>
					<?php } ?>
			</div>
		</div>
	</div>
</div>
