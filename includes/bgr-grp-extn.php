<?php
/**
 * BP Group Extension.
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
 * Group Course settings tab
 * The class_exists() check is recommended, to prevent problems during upgrade
 * or when the Groups component is disabled
 */

if ( class_exists( 'BP_Group_Extension' ) ) :
	/**
	 * BP Group Extension.
	 *
	 * @link       https://wbcomdesigns.com/
	 * @since      1.0.0
	 *
	 * @package    BuddyPress_Group_Review
	 * @subpackage BuddyPress_Group_Review/includes
	 */
	class Group_Reviews_Management_Extn extends BP_Group_Extension {

		/**
		 * Group Extension init.
		 *
		 * @param  array $args Arguments.
		 * @return void
		 */
		public function __construct( $args = array() ) {
			global $bp;
			$url 		= isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
			$parsed_url = parse_url( $url );
			$path       = isset( $parsed_url['path'] ) ? trim( $parsed_url['path'], '/' ) : '';
			$parts      = explode( '/', $path );
			$group_slug = end( $parts );
			$group_id   = BP_Groups_Group::group_exists( $group_slug ); // get current group id.
			if( empty( $group_id ) ){
				$group_id = bp_get_current_group_id();
			}
			$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
			$exclude_groups             = isset( $bgr_admin_general_settings['exclude_groups'] ) ? $bgr_admin_general_settings['exclude_groups'] : array();
			$enabled                    = ! in_array( bp_get_current_group_id(), $exclude_groups, true );
			$add_review_label           = $this->get_add_review_label();
			$add_review_nav             = $this->should_show_add_review_nav();			
			
			// "Add Review" tab will not display on the current group when below condition are matched.
			if ( ! empty( $exclude_groups ) ) {
				if ( in_array( $group_id, $exclude_groups ) ) {
					if ( ! groups_is_user_admin( bp_loggedin_user_id(), $group_id ) ){
						return;
					}
				}
			}
			$args = array(
				'slug'              => 'add-' . bgr_group_review_tab_slug(),
				'nav_item_position' => 200,
				'name'              => sprintf( __( 'Add %s', 'bp-group-reviews' ), $add_review_label ),
				'enable_nav_item'   => $add_review_nav,
				'screens'           => array(
					'edit' => array(
						'name'    => sprintf( __( '%s Management', 'bp-group-reviews' ), $add_review_label ),
						'slug'    => bgr_group_review_tab_slug() . '-management',
						'enabled' => $enabled,
					),
				),
				'show_tab'          => $enabled,
			);

			parent::init( $args );
		}

		private function get_add_review_label() {
			$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
			return ! empty( $bgr_admin_display_settings ) ? bgr_group_add_review_tab_name() : esc_html__( 'Review', 'bp-group-reviews' );
		}

		private function should_show_add_review_nav() {
			 return ! ( groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) && is_user_logged_in() );
		}

		/**
		 * Dispay group review form.
		 *
		 * @param  int $group_id Group ID.
		 * @return void
		 */
		public function display( $group_id = null ) {
			?>
			<div class="bgr-bp-success">
				<?php
				$bp_template_option = bp_get_option( '_bp_theme_package_id' );
				if ( 'nouveau' == $bp_template_option ) {
					?>
					<div id="message" class="success bp-feedback bp-messages bp-template-notice">
						<span class="bp-icon" aria-hidden="true"></span>
				<?php } else { ?>
					<div id="message" class="success bgr-bp-success">
				<?php } ?>
					<p><?php esc_html_e( 'Your Response added. This will be published when group admin has approved it.', 'bp-group-reviews' ); ?></p>
				</div>
			</div>
			<div class="bgr-group-review-no-popup-add-block">
				<?php echo do_shortcode( '[add_group_review_form]' ); ?>
			</div>
			<?php
		}

		/**
		 * Display all posted reviews that not checked by group admins.
		 *
		 * @param  int $group_id Group ID.
		 * @return void
		 */
		public function edit_screen( $group_id = null ) {
				global $bp, $wpdb, $post;
				// Admin Settings.
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
					$review_label = esc_html__( 'Reviews', 'bp-group-reviews' );
				}
			} else {
				$review_label = esc_html__( 'Reviews', 'bp-group-reviews' );
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
										<?php echo wp_kses_post( bp_core_get_userlink( $author ) ); ?>
									</div>
									<div class="item-description">
										<div class="review-description">
											<div class="review-excerpt bgr-col-12">
												<b> <?php esc_html_e( 'Short Description ', 'bp-group-reviews' ); ?>: </b>
												<?php
												$trimcontent = get_the_content();
												if ( ! empty( $trimcontent ) ) {
													$len = strlen( $trimcontent );
													if ( $len > 150 ) {
														$shortexcerpt = substr( $trimcontent, 0, 150 );
														echo wp_kses_post( $shortexcerpt );
													} else {
														echo wp_kses_post( $trimcontent );
													}
												}
												?>
											</div>
											<div class="review-full-description bgr-col-12">
												<div class="bgr-col-12">
												<b>
														<?php esc_html_e( 'Full Description', 'bp-group-reviews' ); ?> :
												</b>
													<?php the_content(); ?>
												</div>
												<?php do_action( 'bgr_display_ratings', $post->ID ); ?>
											</div>
											<a class="expand-review-des"><?php esc_html_e( 'View More..', 'bp-group-reviews' ); ?> </a>
										</div>
									</div>
								</div>
								<div class="bgr-col-2">
									<div class='bgr-col-12 accept-review generic-button'>
										<a class='accept-button' data-group-type='<?php echo esc_attr( $bp->groups->current_group->id ); ?>'> <?php esc_html_e( 'Accept', 'bp-group-reviews' ); ?> </a><input type="hidden" name="accept_review_id" value="<?php echo esc_attr( $post->ID ); ?>">
									</div>
									<div class='bgr-col-12 deny-review generic-button'>
										<a class='deny-button' data-group-type='<?php echo esc_attr( $bp->groups->current_group->id ); ?>'> <?php esc_html_e( 'Deny', 'bp-group-reviews' ); ?> </a><input type="hidden" name="deny_review_id" value="<?php echo esc_attr( $post->ID ); ?>">
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
								echo wp_kses_post(
									paginate_links(
										array(
											'base'      => get_pagenum_link( 1 ) . '%_%',
											'format'    => 'page/%#%',
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
					else :
						$bp_template_option = bp_get_option( '_bp_theme_package_id' );
						if ( 'nouveau' == $bp_template_option ) {
							?>
						<div id="message" class="info bp-feedback bp-messages bp-template-notice">
							<span class="bp-icon" aria-hidden="true"></span>
						<?php } else { ?>
							<div id="message" class="info">
						<?php } ?>
							<?php /* translators: %s is replaced with review_label */ ?>
							<p><?php echo sprintf( esc_html__( 'Sorry, no %s were found.', 'bp-group-reviews' ), esc_html( $review_label ) ); ?></p>
						</div>
						<?php
				endif;
					?>
				</div>
				<?php
		}
	}
	bp_register_group_extension( 'Group_Reviews_Management_Extn' );



endif;
