<?php
/**
 * BGR Single Group Review tab content.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/templates
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $bgr;
$review_rating_fields = $bgr['review_rating_fields'];
$url                  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
$review_id            = basename( $url );
$review               = get_post( $review_id );
$review_title         = $review->post_title;
$author               = $review->post_author;
$author_details       = get_userdata( $author );
?>
<div class="bgr-single-review">
	<div class="bgr-row item-list group-request-list">
			<div class="bgr-col-2">
				<?php bp_displayed_user_avatar( array( 'item_id' => $author ) ); ?>
			</div>
			<div class="bgr-col-10">
				<div class="reviewer">
					<b>
						<?php echo wp_kses_post( bp_core_get_userlink( $author ) ); ?>
					</b>
				</div>

				<div class="item-description">
					<div class="review-description">
						<div class="bgr-col-12">
							<?php echo esc_html( $review->post_content ); ?>
						</div>
						<?php
							$review_ratings = get_post_meta( $review_id, 'review_star_rating', false );
							do_action( 'bgr_display_ratings', $review_id );
						?>
					</div>
				</div>
			</div>
	</div>
</div>
