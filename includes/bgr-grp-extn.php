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
			global $bgr;
			$enabled                    = true;
			$add_review_nav             = false;
			$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
			$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );

			if ( ! empty( $bgr_admin_display_settings ) ) {
				$review_label     = bp_group_review_tab_name();
				$add_review_label = bp_group_review_add_review_tab_name();
			} else {
				$review_label     = esc_html__( 'Reviews', 'bp-group-reviews' );
				$add_review_label = esc_html__( 'Review', 'bp-group-reviews' );
			}

			// Check if current group is excluded from reviews.
			$current_group_id  = absint( bp_get_current_group_id() );
			$is_group_excluded = false;
			$exclude_groups    = array();

			if ( ! empty( $bgr_admin_general_settings ) && isset( $bgr_admin_general_settings['exclude_groups'] ) ) {
				$exclude_groups = array_map( 'absint', (array) $bgr_admin_general_settings['exclude_groups'] );
				if ( ! empty( $exclude_groups ) && in_array( $current_group_id, $exclude_groups, true ) ) {
					$enabled           = false;
					$is_group_excluded = true;
				}
			}

			// Only proceed with tab creation if group is not excluded.
			if ( ! $is_group_excluded ) {
				if ( ! empty( $bgr_admin_general_settings ) ) {
					$args = array(
						'slug'              => 'manage-' . bp_group_review_tab_slug(),
						'nav_item_position' => 200,
						/* translators: %s is replaced with add_review_label */
						'name'              => sprintf( __( 'Manage %s', 'bp-group-reviews' ), $add_review_label ),
						'enable_nav_item'   => $add_review_nav,
						'screens'           => array(
							'admin' => array(
								'name'    => 'Manage ' . $review_label,
								'slug'    => 'manage-' . bp_group_review_tab_slug(),
								'enabled' => $enabled,
							),
						),
						'show_tab'          => false,

					);
					parent::init( $args );
				} else {
					$args = array(
						'slug'              => 'manage-' . bp_group_review_tab_slug(),
						'nav_item_position' => 200,
						/* translators: %s is replaced with add_review_label */
						'name'              => sprintf( __( 'Manage %s', 'bp-group-reviews' ), $add_review_label ),
						'enable_nav_item'   => $add_review_nav,
						'screens'           => array(
							'edit' => array(
								'name'    => 'Manage ' . $review_label,
								'slug'    => 'manage-' . bp_group_review_tab_slug(),
								'enabled' => $enabled,
							),
						),
						'show_tab'          => false,
					);
					parent::init( $args );
				}
			}
			add_filter( 'query_vars', array( $this, 'add_custom_pagination_query_vars' ) );
		}

		/**
		 * Display all posted reviews that not checked by group admins.
		 *
		 * @param  int $group_id Group ID.
		 * @return void
		 */
		public function edit_screen( $group_id = null ) {
			global $bp, $wpdb, $post, $wp_query;
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
						<div class="bgr-col-2 bgr-post-author">
								<?php
								$author = $reviews->post->post_author;
								bp_displayed_user_avatar( array( 'item_id' => $author ) );
								?>
							</div>
							<div class="bgr-col-7">
								<div class="item-title">
									<?php echo wp_kses_post( bp_core_get_userlink( $author ) ); ?>
								</div>
								<div class="item-description">
									<div class="review-description">
										<?php
										$trimcontent = get_the_content();
										if ( ! empty( $trimcontent ) ) :
											?>
										<div class="review-excerpt bgr-col-12">
											<b> <?php esc_html_e( 'Short Description ', 'bp-group-reviews' ); ?>: </b>
											<?php
												$len = strlen( $trimcontent );
											if ( $len > 150 ) {
												$shortexcerpt = substr( $trimcontent, 0, 150 );
												echo wp_kses_post( $shortexcerpt );
											} else {
												echo wp_kses_post( $trimcontent );
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
										<?php else : ?>
										<div class="review-ratings-only bgr-col-12">
											<?php do_action( 'bgr_display_ratings', $post->ID ); ?>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<div class="bgr-col-3 bgr-generic-button">
								<div class='accept-review generic-button'>
									<a class='accept-button button' data-group-type='<?php echo esc_attr( $bp->groups->current_group->id ); ?>'> <?php esc_html_e( 'Accept', 'bp-group-reviews' ); ?> </a><input type="hidden" name="accept_review_id" value="<?php echo esc_attr( $post->ID ); ?>">
								</div>
								<div class='deny-review generic-button'>
									<a class='deny-button button' data-group-type='<?php echo esc_attr( $bp->groups->current_group->id ); ?>'> <?php esc_html_e( 'Deny', 'bp-group-reviews' ); ?> </a><input type="hidden" name="deny_review_id" value="<?php echo esc_attr( $post->ID ); ?>">
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
							$current_page = max( 1, get_query_var( 'paged', 1 ) ); // Ensures default value is 1
							$format       = '?paged=%#%';
							$pagination   = paginate_links(
								array(
									'base'      => esc_url( get_pagenum_link( 1 ) ) . '%_%',
									'format'    => $format, // Make sure this matches your permalink structure
									'current'   => $current_page,
									'total'     => $reviews->max_num_pages, // Use the correct WP_Query object
									'prev_text' => esc_html__( 'Prev', 'bp-group-reviews' ),
									'next_text' => esc_html__( 'Next', 'bp-group-reviews' ),
								)
							);

							if ( $pagination ) {
								echo wp_kses_post( '<div class="review-pagination">' . $pagination . '</div>' );
							}
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
						<p><?php printf( esc_html__( 'Sorry, no %s were found.', 'bp-group-reviews' ), esc_html( $review_label ) ); ?></p>
					</div>
					<?php
			endif;
				echo '</div>';
				echo '<input type="submit" name="save" style="display:none;" id="bp-group-edit-manage-revieww-submit">';
		}

		public function add_custom_pagination_query_vars( $vars ) {
			$vars[] = 'paged'; // Ensure WordPress recognizes it
			$vars[] = 'upage'; // Add BuddyPress pagination var if needed
			return $vars;
		}
	}
	bp_register_group_extension( 'Group_Reviews_Management_Extn' );

	/**
	 * Group Criteria Settings Extension.
	 *
	 * Allows group admins to customize review criteria for their group.
	 *
	 * @since 3.7.0
	 */
	class Group_Reviews_Criteria_Extn extends BP_Group_Extension {

		/**
		 * Constructor.
		 *
		 * @param array $args Arguments.
		 */
		public function __construct( $args = array() ) {
			global $bgr;

			$enabled                    = true;
			$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );

			// Check if group-level criteria feature is enabled by site admin.
			$enable_group_criteria = isset( $bgr_admin_general_settings['enable_group_criteria'] ) ? $bgr_admin_general_settings['enable_group_criteria'] : 'no';
			if ( 'yes' !== $enable_group_criteria ) {
				return; // Feature not enabled, don't register this extension.
			}

			// Check if current group is excluded from reviews.
			$current_group_id  = absint( bp_get_current_group_id() );
			$is_group_excluded = false;

			if ( ! empty( $bgr_admin_general_settings ) && isset( $bgr_admin_general_settings['exclude_groups'] ) ) {
				$exclude_groups = array_map( 'absint', (array) $bgr_admin_general_settings['exclude_groups'] );
				if ( ! empty( $exclude_groups ) && in_array( $current_group_id, $exclude_groups, true ) ) {
					$enabled           = false;
					$is_group_excluded = true;
				}
			}

			if ( ! $is_group_excluded ) {
				$args = array(
					'slug'              => 'review-criteria',
					'nav_item_position' => 201,
					'name'              => __( 'Review Criteria', 'bp-group-reviews' ),
					'enable_nav_item'   => false,
					'screens'           => array(
						'admin' => array(
							'name'    => __( 'Review Criteria', 'bp-group-reviews' ),
							'slug'    => 'review-criteria',
							'enabled' => $enabled,
						),
					),
					'show_tab'          => false,
				);
				parent::init( $args );
			}
		}

		/**
		 * Display the criteria settings screen.
		 *
		 * @param int $group_id Group ID.
		 */
		public function admin_screen( $group_id = null ) {
			$this->edit_screen( $group_id );
		}

		/**
		 * Display the criteria settings screen.
		 *
		 * @param int $group_id Group ID.
		 */
		public function edit_screen( $group_id = null ) {
			// Load the template.
			$template_path = BGR_PLUGIN_PATH . 'includes/templates/bgr-group-criteria-settings.php';
			if ( file_exists( $template_path ) ) {
				include $template_path;
			}
			// Hidden submit button to satisfy BP Group Extension requirements.
			echo '<input type="submit" name="save" style="display:none;" id="bp-group-edit-review-criteria-submit">';
		}

		/**
		 * Save the criteria settings.
		 *
		 * @param int $group_id Group ID.
		 */
		public function admin_screen_save( $group_id = null ) {
			// Settings are saved via AJAX, so nothing needed here.
		}

		/**
		 * Save the criteria settings.
		 *
		 * @param int $group_id Group ID.
		 */
		public function edit_screen_save( $group_id = null ) {
			// Settings are saved via AJAX, so nothing needed here.
		}
	}
	// Only register the criteria extension if the feature is enabled by site admin.
	$bgr_criteria_settings     = get_option( 'bgr_admin_general_settings' );
	$enable_criteria_extension = isset( $bgr_criteria_settings['enable_group_criteria'] ) ? $bgr_criteria_settings['enable_group_criteria'] : 'no';
	if ( 'yes' === $enable_criteria_extension ) {
		bp_register_group_extension( 'Group_Reviews_Criteria_Extn' );
	}

endif;
