<?php
/**
* @package Instagram Feeder
*/
/*
Plugin Name: Instagram Feeder 
Description: This plugin helps you to get instagram feeds from your app.
Version 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

class InstagramFeeder {
	private $user_id;
	private $access_token;

	public function __construct() {
		add_action( 'wp_loaded', function() {
			require 'vendor/autoload.php';

			$adminPage = new App\Instagram\AdminPage();
			
			$adminPage->createPostType();

			$feeder = new App\Instagram\Feeder();
			
			$feeder->refreshFeeds();

		} );
	}

	public function activate() {

	}

    public function registerPostMeta() {
		Container::make( 'post_meta', __( 'Custom Data', 'crb' ) )
			->where( 'post_type', '=', 'crb_instagram' )
			->add_fields( array(
				Field::make( 'text', 'crb_instagram_image', __(' Image', 'crb' ) ),
				Field::make( 'text', 'crb_instagram_caption', __( 'Caption', 'crb' ) ),
			) );
	}

	public function deactivate() {

	}
}

$InstagramFeeder = new InstagramFeeder();

add_action( 'carbon_fields_register_fields', array($InstagramFeeder, 'registerPostMeta') );

register_activation_hook( __FILE__, array( $InstagramFeeder, 'activate' ) );

register_deactivation_hook( __FILE__, array( $InstagramFeeder, 'deactivate' ) );

