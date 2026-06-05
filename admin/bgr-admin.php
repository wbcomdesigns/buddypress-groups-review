<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- Legacy file name.
/**
 * Plugin admin service class.
 *
 * As of 3.9.0 the admin UI was migrated from the legacy Wbcom tab
 * wrapper (admin/wbcom/) to the card-panel pattern. This class no
 * longer renders the settings page or the WB Plugins menu — that is
 * owned by BGR_Admin_Panel (includes/admin/class-bgr-admin.php).
 *
 * What this class STILL owns (retained, non-wrapper logic):
 *   - The `review` custom post type + `review_category` taxonomy.
 *   - Adding the review capabilities to roles.
 *   - The pending-review count badge on the Reviews menu item.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BGR_Admin' ) ) {

	/**
	 * Admin service class — CPT, taxonomy, caps, and review-count badge.
	 *
	 * @since 1.0.0
	 */
	class BGR_Admin {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'bp_group_review_get_review_count' ) );

			$post_types = get_post_types();
			if ( ! in_array( 'review', $post_types, true ) ) {
				add_action( 'init', array( $this, 'bp_group_review_cpt' ) );
				add_action( 'init', array( $this, 'bp_group_review_taxonomy_cpt' ) );
			}

			add_action( 'init', array( $this, 'bp_group_review_add_capabilities_to_roles' ) );
		}

		/**
		 * Register the `review` custom post type.
		 *
		 * @since 1.0.0
		 */
		public function bp_group_review_cpt() {
			$labels = array(
				'name'               => esc_html__( 'Reviews', 'bp-group-reviews' ),
				'singular_name'      => esc_html__( 'Review', 'bp-group-reviews' ),
				'menu_name'          => esc_html__( 'Reviews', 'bp-group-reviews' ),
				'name_admin_bar'     => esc_html__( 'Reviews', 'bp-group-reviews' ),
				'add_new'            => esc_html__( 'Add New Review', 'bp-group-reviews' ),
				'add_new_item'       => esc_html__( 'Add New Review', 'bp-group-reviews' ),
				'new_item'           => esc_html__( 'New Review', 'bp-group-reviews' ),
				'view_item'          => esc_html__( 'View Reviews', 'bp-group-reviews' ),
				'all_items'          => esc_html__( 'All Reviews', 'bp-group-reviews' ),
				'search_items'       => esc_html__( 'Search Reviews', 'bp-group-reviews' ),
				'parent_item_colon'  => esc_html__( 'Parent Review', 'bp-group-reviews' ),
				'not_found'          => esc_html__( 'No Review Found', 'bp-group-reviews' ),
				'not_found_in_trash' => esc_html__( 'No Review Found In Trash', 'bp-group-reviews' ),
			);
			$args   = array(
				'labels'             => $labels,
				'public'             => false,
				'menu_icon'          => 'dashicons-testimonial',
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array(
					'slug'       => 'review',
					'with_front' => false,
				),
				'capability_type'    => 'post',
				'capabilities'       => array(
					'edit_post'          => 'edit_review',
					'read_post'          => 'read_review',
					'delete_post'        => 'delete_review',
					'edit_posts'         => 'edit_reviews',
					'edit_others_posts'  => 'edit_others_reviews',
					'publish_posts'      => 'publish_reviews',
					'read_private_posts' => 'read_private_reviews',
					'create_posts'       => 'do_not_allow',
				),
				'map_meta_cap'       => true,
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
			);
			register_post_type( 'review', $args );
		}

		/**
		 * Register the `review_category` taxonomy.
		 *
		 * @since 1.0.0
		 */
		public function bp_group_review_taxonomy_cpt() {
			$category_labels = array(
				'name'              => esc_html_x( 'Reviews Category', 'taxonomy general name', 'bp-group-reviews' ),
				'singular_name'     => esc_html_x( 'Review Category', 'taxonomy singular name', 'bp-group-reviews' ),
				'search_items'      => esc_html__( 'Search Categories', 'bp-group-reviews' ),
				'all_items'         => esc_html__( 'All Categories', 'bp-group-reviews' ),
				'parent_item'       => esc_html__( 'Parent Category', 'bp-group-reviews' ),
				'parent_item_colon' => esc_html__( 'Parent Category:', 'bp-group-reviews' ),
				'edit_item'         => esc_html__( 'Edit Category', 'bp-group-reviews' ),
				'update_item'       => esc_html__( 'Update Category', 'bp-group-reviews' ),
				'add_new_item'      => esc_html__( 'Add Category', 'bp-group-reviews' ),
				'new_item_name'     => esc_html__( 'New Category Name', 'bp-group-reviews' ),
				'menu_name'         => esc_html__( 'Category', 'bp-group-reviews' ),
			);
			$category_args   = array(
				'hierarchical'      => true,
				'labels'            => $category_labels,
				'show_ui'           => false,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'review_category' ),
			);
			register_taxonomy( 'review_category', array( 'review' ), $category_args );
		}

		/**
		 * Append a pending-review count badge to the Reviews menu item.
		 *
		 * @return void
		 */
		public function bp_group_review_get_review_count() {
			global $bgr, $menu; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Modifying admin menu count badge.
			if ( isset( $bgr['auto_approve_reviews'] ) && 'yes' !== $bgr['auto_approve_reviews'] ) {

				foreach ( $menu as $each_menu ) {
					if ( 'edit.php?post_type=review' === $each_menu[2] ) {
						$count = wp_count_posts( 'review' );
						if ( $count ) {
							$count = $count->draft;

							$key = $this->bp_group_review_recursive_array_search( 'edit.php?post_type=review', $menu );

							if ( ! $key ) {
								return;
							}

							$menu[ $key ][0] .= sprintf( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Appending count badge to admin menu.
								'<span class="awaiting-mod update-plugins count-%1$s"><span class="plugin-count">%1$s</span></span>',
								$count
							);
						}
					}
				}
			}
		}

		/**
		 * Recursive search helper for the menu array.
		 *
		 * @param string $needle   Value to find.
		 * @param array  $haystack Haystack.
		 * @return int|false Array key, or false.
		 */
		public function bp_group_review_recursive_array_search( $needle, $haystack ) {
			foreach ( $haystack as $key => $value ) {
				$current_key = $key;
				if (
					$needle === $value
					|| ( is_array( $value )
					&& false !== $this->bp_group_review_recursive_array_search( $needle, $value ) )
				) {
					return $current_key;
				}
			}
			return false;
		}

		/**
		 * Add custom review capabilities to roles.
		 *
		 * Administrator by default; other roles can be added via the
		 * 'bgr_add_roles_capability' filter.
		 *
		 * @since 1.0.0
		 */
		public function bp_group_review_add_capabilities_to_roles() {
			$roles = apply_filters( 'bgr_add_roles_capability', array( 'administrator' ) );
			foreach ( $roles as $role_name ) {
				$role = get_role( $role_name );
				if ( $role ) {
					$role->add_cap( 'edit_review' );
					$role->add_cap( 'edit_reviews' );
					$role->add_cap( 'edit_others_reviews' );
					$role->add_cap( 'publish_reviews' );
					$role->add_cap( 'read_private_reviews' );
					$role->add_cap( 'delete_review' );
					$role->add_cap( 'delete_reviews' );
					$role->add_cap( 'delete_others_reviews' );
					$role->add_cap( 'delete_published_reviews' );
				}
			}
		}
	}
	new BGR_Admin();

	// New card-panel admin (menu + enqueue + render). Replaces the
	// legacy Wbcom tab wrapper as of 3.9.0.
	require_once BGR_PLUGIN_PATH . 'includes/admin/class-bgr-admin-panel.php';
	$bgr_admin_panel = new BGR_Admin_Panel();
	$bgr_admin_panel->register();
}
