<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Group Course settings tab
 * The class_exists() check is recommended, to prevent problems during upgrade
 * or when the Groups component is disabled
 */

if ( class_exists( 'BP_Group_Extension' ) ) :

	class Group_Reviews_Management_Extn extends BP_Group_Extension {

		function __construct() {

			global $bp;
			global $bgr;
			$enabled                    = true;
			$add_review_nav             = false;
			$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
			$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
			if ( ! empty( $bgr_admin_display_settings ) ) {
				$review_label     = $bgr_admin_display_settings['manage_review_label'];
				$add_review_label = $bgr_admin_display_settings['review_label'];
				if ( empty( $review_label ) ) {
					$review_label = 'Reviews ';
				}
				if ( empty( $add_review_label ) ) {
					$add_review_label = 'Review';
				}
			} else {
				$review_label     = 'Reviews ';
				$add_review_label = 'Review';
			}
			if ( ! empty( $bgr_admin_general_settings ) ) {
				$exclude_groups = $bgr_admin_general_settings['exclude_groups'];
				if ( ! empty( $exclude_groups ) ) {
					if ( in_array( bp_get_current_group_id(), $exclude_groups ) ) {
						$enabled = false;
					}
				}
			}

			if ( ! groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) || ! is_user_logged_in() ) {
				$add_review_nav = true;
			}

			if ( ! empty( $bgr_admin_general_settings ) ) {
				if ( ! empty( $exclude_groups ) ) {
					if ( ! in_array( bp_get_current_group_id(), $exclude_groups ) ) {
						$args = array(
							'slug'              => 'add-review',
							'nav_item_position' => 99,
							'nav_item_name'     => sprintf( __( 'Add %s', 'bp-group-reviews' ), $add_review_label ),
							'enable_nav_item'   => $add_review_nav,
							'screens'           => array(
								'edit' => array(
									'name'    => $review_label,
									'slug'    => 'manage-reviews',
									'enabled' => $enabled,
								),
							),
							'show_tab'          => false,

						);
						parent::init( $args );
					}
				} else {
					$args = array(
						'slug'              => 'add-review',
						'nav_item_position' => 99,
						'nav_item_name'     => sprintf( __( 'Add %s', 'bp-group-reviews' ), $add_review_label ),
						'enable_nav_item'   => $add_review_nav,
						'screens'           => array(
							'edit' => array(
								'name'    => $review_label,
								'slug'    => 'manage-reviews',
								'enabled' => $enabled,
							),
						),
						'show_tab'          => false,

					);
					parent::init( $args );
				}
			} else {
				$args = array(
					'slug'              => 'add-review',
					'nav_item_position' => 99,
					'nav_item_name'     => sprintf( __( 'Add %s', 'bp-group-reviews' ), $add_review_label ),
					'enable_nav_item'   => $add_review_nav,
					'screens'           => array(
						'edit' => array(
							'name'    => $review_label,
							'slug'    => 'manage-reviews',
							'enabled' => $enabled,
						),
					),
					'show_tab'          => false,

				);
						parent::init( $args );
			}
		}

		public function display( $group_id = null ) { ?>
			<div class="bgr-bp-success">
				<?php
				$bp_template_option = bp_get_option('_bp_theme_package_id');
				if( 'nouveau' == $bp_template_option ) { ?>
					<div id="message" class="success bp-feedback bp-messages bp-template-notice">
						<span class="bp-icon" aria-hidden="true"></span>
				<?php } else { ?>		
					<div id="message" class="success bgr-bp-success">
				<?php } ?>		
					<p><?php _e( 'Your Response added. This will be published when group admin approved it.', 'bp-group-reviews' ); ?></p>
				</div>
			</div>
			<div class="bgr-group-review-no-popup-add-block">
				<?php do_shortcode( '[add_group_review_form]' ); ?>
			</div>
		<?php
		}

		/*
		*  Display all posted reviews that not checked by group admins.
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/

		function edit_screen( $group_id = null ) {
			global $bp, $wpdb, $post;
			// Admin Settings
			$bgr_admin_settings         = get_option( 'bgr_admin_general_settings' );
			$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
			if ( ! empty( $bgr_admin_settings ) ) {
				$reviews_per_page = $bgr_admin_settings['reviews_per_page'];
				if ( empty( $reviews_per_page ) ) {
					$reviews_per_page = -1;
				}
			} else {
				$reviews_per_page = -1;
			}

			if ( ! empty( $bgr_admin_display_settings ) ) {
				$review_label = $bgr_admin_display_settings['review_label'];
				if ( empty( $review_label ) ) {
					$review_label = 'Reviews ';
				}
			} else {
				$review_label = 'Reviews ';
			}
			$paged   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$args    = array(
				'post_type'      => 'review',
				'post_status'    => 'draft',
				'posts_per_page' => $reviews_per_page,
				'paged'          => $paged,
				'category'       => 'group',
				'meta_query'     => array(
					array(
						'key'     => 'linked_group',
						'value'   => $bp->groups->current_group->id,
						'compare' => '=',
					),
				),
			);
			$reviews = new WP_Query( $args );

			?>
			<div id="request-review-list" class="item-list reviews-item-list">
				<?php
				if ( $reviews->have_posts() ) :
					while ( $reviews->have_posts() ) :
						$reviews->the_post();
					?>
					<div class="bgr-row item-list group-request-list">
						<div class="bgr-col-2">
								<?php
								$author = $reviews->post->post_author;
								bp_displayed_user_avatar( array( 'item_id' => $author ) );
									?>
							</div>
							<div class="bgr-col-8">
								<div class="item-title">
									<?php _e( bp_core_get_userlink( $author ), 'bp-group-reviews' ); ?>
								</div>
								<div class="item-description">
									<div class="review-description">
										<div class="review-excerpt bgr-col-12">
											<b> <?php _e( 'Short Description ', 'bp-group-reviews' ); ?>: </b>
											<?php
											$trimcontent = get_the_content();
											if ( ! empty( $trimcontent ) ) {
												$len = strlen( $trimcontent );
												if ( $len > 150 ) {
													$shortexcerpt = substr( $trimcontent, 0, 150 );
													echo $shortexcerpt;
												} else {
													echo $trimcontent;
												}
											}
											?>
										</div>
										<div class="review-full-description bgr-col-12">
											<div class="bgr-col-12">
											<b>
													<?php _e( 'Full Description', 'bp-group-reviews' ); ?> :
											</b>
												<?php the_content(); ?>
											</div>
											<?php do_action( 'bgr_display_ratings', $post->ID ); ?>
										</div>
										<a class="expand-review-des"><?php _e( 'View More..', 'bp-group-reviews' ); ?> </a>
									</div>
								</div>
							</div>
							<div class="bgr-col-2">
								<div class='bgr-col-12 accept-review generic-button'>
									<a class='accept-button' data-group-type='<?php _e( $bp->groups->current_group->id, 'bp-group-reviews' ); ?>'> <?php _e( 'Accept', 'bp-group-reviews' ); ?> </a><input type="hidden" name="accept_review_id" value="<?php _e( $post->ID, 'bp-group-reviews' ); ?>">
								</div>
								<div class='bgr-col-12 deny-review generic-button'>
									<a class='deny-button' data-group-type='<?php _e( $bp->groups->current_group->id, 'bp-group-reviews' ); ?>'> <?php _e( 'Deny', 'bp-group-reviews' ); ?> </a><input type="hidden" name="deny_review_id" value="<?php _e( $post->ID, 'bp-group-reviews' ); ?>">
								</div>
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
							$current_page = max( 1, get_query_var( 'paged' ) );
							echo paginate_links(
								array(
									'base'      => get_pagenum_link( 1 ) . '%_%',
									'format'    => 'page/%#%',
									'current'   => $current_page,
									'total'     => $total_pages,
									'prev_text' => __( 'prev', 'bp-group-reviews' ),
									'next_text' => __( 'next', 'bp-group-reviews' ),
								)
							);
									?>
						</div>
						<?php
					}
					wp_reset_postdata();
				else :
					$bp_template_option = bp_get_option('_bp_theme_package_id');
					if( 'nouveau' == $bp_template_option ) { ?>
					<div id="message" class="info bp-feedback bp-messages bp-template-notice">
						<span class="bp-icon" aria-hidden="true"></span>
					<?php } else { ?>
						<div id="message" class="info">
					<?php } ?>
						<p><?php _e( "Sorry, no $review_label were found.", 'bp-group-reviews' ); ?></p>
					</div>
			<?php endif;
		}
	}
	bp_register_group_extension( 'Group_Reviews_Management_Extn' );



endif;
