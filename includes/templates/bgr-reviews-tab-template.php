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
$current_user         = wp_get_current_user();
$member_id            = $current_user->ID;
$reviews_per_page     = $bgr['reviews_per_page'];
$review_rating_fields = $bgr['review_rating_fields'];
$review_label         = $bgr['review_label'];
$paged                = 1;


if ( 'reviews' !== basename( home_url( $wp->request ) ) ) {
	$paged = basename( home_url( $wp->request ) );
}

$args    = array(
	'post_type'      => 'review',
	'post_status'    => 'publish',
	'category'       => 'group',
	'posts_per_page' => $reviews_per_page,
	'paged'          => $paged,
	'meta_query'     => array(
		array(
			'key'     => 'linked_group',
			'value'   => bp_get_group_id(),
			'compare' => '=',
		),
	),
);
$reviews = new WP_Query( $args );
?>
<div class="bgr-group-reviews-block">
	<!-- <div class="select-wrap">
		<select id="bp-group-reviews-filter-by">
			<option value="latest"><?php //esc_html_e( 'Latest', 'bp-group-reviews' ); ?></option>
			<option value="highest"><?php //esc_html_e( 'Highest', 'bp-group-reviews' ); ?></option>
			<option value="lowest"><?php //esc_html_e( 'Lowest', 'bp-group-reviews' ); ?></option>
		</select>
	</div> -->
	<div class="group-reviews">
		<div id="group-reviews-list">
			<div id="request-review-list" class="item-list">
				<?php
				if ( $reviews->have_posts() ) {
					while ( $reviews->have_posts() ) :
						$reviews->the_post();
						?>
							<div class="bgr-row item-list group-request-list">
								<div class="bgr-group-profiles">
									<?php
									$author = $reviews->post->post_author;
									bp_displayed_user_avatar( array( 'item_id' => $author ) );
									?>
								</div>
								<div class="bgr-group-content">
									<div class="reviewer">
										<b><?php echo esc_url( bp_core_get_userlink( $author ) ); ?></b>
									</div>

									<div class="item-description">
										<div class="review-description">
											<?php
												$trimcontent = get_the_content();
												$url         = bp_get_group_url() . sanitize_title( bgr_group_review_tab_name() ) . '\/view/' . get_the_id();
											if ( ! empty( $trimcontent ) ) {
												$len = strlen( $trimcontent );
												if ( $len > 150 ) {
													$shortexcerpt = substr( $trimcontent, 0, 150 );
													echo wp_kses_post( $shortexcerpt );
													?>
														<a href="<?php echo esc_url( $url ); ?>"><i><b><?php esc_html_e( 'read more...', 'bp-group-reviews' ); ?></b></i></a>
													<?php
												} else {
													echo wp_kses_post( $trimcontent );
												}
											}
											?>
												<div class="review-ratings">
													<?php do_action( 'bgr_display_ratings', $post->ID ); ?>
												</div>
										</div>
									</div>
								</div>
								<div class="bgr-col-2">
									<?php if ( groups_is_user_admin( $member_id, bp_get_group_id() ) ) : ?>
										<div class='remove-review generic-button'>
											<a class='remove-review-button'> <?php esc_html_e( 'Delete', 'bp-group-reviews' ); ?> </a>
											<input type="hidden" name="remove_review_id" value="<?php echo esc_attr( $post->ID ); ?>">
										</div>
									<?php endif; ?>
								</div>

								<div class="clear"></div>
							</div>

						<?php
					endwhile;
					$total_pages = $reviews->max_num_pages;
					if ( $total_pages > 1 ) {

						?>
							<div class="review-pagination">
								<?php
								echo wp_kses_post(
									paginate_links(
										array(
											'base'      => get_pagenum_link( 1 ) . '%_%',
											'format'    => 'page/%#%',
											'current'   => $paged,
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
					if ( 'nouveau' == $bp_template_option ) {
						?>
					<div id="message" class="info bp-feedback bp-messages bp-template-notice">
						<span class="bp-icon" aria-hidden="true"></span>
					<?php } else { ?>
						<div id="message" class="info">
						<?php
					}
					/* translators: %1$s is replaced with review_label */
						echo '<p>' . sprintf( esc_html__( 'Unfortunately, there are no %1$s were found.', 'bp-group-reviews' ), esc_html( strtolower( $review_label ) ) ) . '</p>';
					?>
					</div>
					<?php } ?>
			</div>
		</div>
	</div>
</div>
