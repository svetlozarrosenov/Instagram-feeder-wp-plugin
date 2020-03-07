<?php
namespace App\Instagram;

class AdminPage {
	private $pluginDir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'instagram-feeder' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;

	public function __construct() {
		add_action( 'admin_menu', function() {
			add_menu_page( 'Instagram Settings', 'Instagram Settings', 'administrator', 'instagram-feeds', function() {
				$this->render('instagram-feeds-view');
			} );
		} );
	}

	public function render( $view, $context = [] ) {
		extract( $context );

		require $this->pluginDir . $view . '.php';
	}

	public function createPostType() {
		register_post_type( 'crb_instagram', array(
			'labels' => array(
				'name' => __( 'Instagram Feeds', 'crb' ),
				'singular_name' => __( 'Instagram Feed', 'crb' ),
				'add_new' => __( 'Add New', 'crb' ),
				'add_new_item' => __( 'Add new Instagram Feed', 'crb' ),
				'view_item' => __( 'View Instagram Feed', 'crb' ),
				'edit_item' => __( 'Edit Instagram Feed', 'crb' ),
				'new_item' => __( 'New Instagram Feed', 'crb' ),
				'view_item' => __( 'View Instagram Feed', 'crb' ),
				'search_items' => __( 'Search Instagram Feeds', 'crb' ),
				'not_found' =>  __( 'No Instagram Feeds found', 'crb' ),
				'not_found_in_trash' => __( 'No Instagram Feeds found in trash', 'crb' ),
			),
			'public' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'_edit_link' => 'post.php?post=%d',
			'rewrite' => array(
				'slug' => 'instagram',
				'with_front' => false,
			),
			'query_var' => true,
			'menu_icon' => 'dashicons-products',
			'supports' => array( 'title', 'editor', 'page-attributes', 'thumbnail' ),
		) );
	}
}

