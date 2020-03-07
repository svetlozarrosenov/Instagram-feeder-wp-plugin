<?php
namespace App\Instagram;

class Feeder {
	private static $authorizationCodeLink;

	private static $instagramAppID;
	private static $instagramAppSecret;
	private static $authorizationCode;
	private static $instagramAppAccessToken;
	private static $instagramAppUserID;

	public function __construct() {
		$adminURI = admin_url();

		$this->updateOption( $_POST );

		$this->getApiCredentials();

		$clientID = self::$instagramAppID;

		self::$authorizationCodeLink = "https://www.instagram.com/oauth/authorize?app_id={$clientID}&redirect_uri={$adminURI}&scope=user_profile,user_media&response_type=code";


		if ( ! empty( $_GET['code'] ) ) {
			wp_redirect(admin_url("admin.php?page=instagram-feeds&AuthorizationCode={$_GET['code']}&pullInstagramFeeds=1"));
		}
	}

	public function updateOption( $args ) {
		if ( ! empty( $args['InstagramAppID'] ) ) {
			add_option( 'crb_instagram_app_id', $args['InstagramAppID'] );
			update_option( 'crb_instagram_app_id', $args['InstagramAppID'] );
		}

		if ( ! empty( $args['InstagramAppSecret'] ) ) {
			add_option( 'crb_instagram_app_secret', $args['InstagramAppSecret'] );
			update_option( 'crb_instagram_app_secret', $args['InstagramAppSecret'] );
		}

		if ( ! empty( $args['AuthorizationCode'] ) ) {
			add_option( 'crb_authorization_code', $args['AuthorizationCode'] );
			update_option( 'crb_authorization_code', $args['AuthorizationCode'] );
		}

		if ( ! empty( $args['access_token'] ) ) {
			add_option( 'crb_instagram_access_token', $args['access_token'] );
			update_option( 'crb_instagram_access_token', $args['access_token'] );
		}

		if ( ! empty( $args['user_id'] ) ) {
			add_option( 'crb_instagram_user_id', $args['user_id'] );
			update_option( 'crb_instagram_user_id', $args['user_id'] );
		}
	}

	public function getApiCredentials() {
		self::$instagramAppID = get_option('crb_instagram_app_id');
		self::$instagramAppSecret = get_option('crb_instagram_app_secret');
		self::$authorizationCode = get_option('crb_authorization_code');
		self::$instagramAppAccessToken = get_option('crb_instagram_access_token');
		self::$instagramAppUserID = get_option('crb_instagram_user_id');

		return [
			'AppID' => self::$instagramAppID,
			'AppSecret' => self::$instagramAppSecret,
			'AppAuthorizationCode' => self::$authorizationCode,
			'AppAccessToken' => self::$instagramAppAccessToken,
			'AppUserID' => self::$instagramAppUserID,
		];
	}

	public static function getAuthorizationLink() {
		return self::$authorizationCodeLink;
	}

	public function deleteFeeds() {
		$allposts= get_posts( array('post_type'=>'crb_instagram','numberposts'=>-1) );
		foreach ($allposts as $eachpost) {
			wp_delete_post( $eachpost->ID, true );
		}
	}

	public function insertFeeds( $feeds ) {
		foreach ( $feeds['data'] as $feed ) {
			$postID = wp_insert_post( array(
					'post_type' => 'crb_instagram',
					'post_status' => 'publish',
					'post_title' => $feed['username'],
				)
			);

			add_post_meta( $postID, '_crb_instagram_image', $feed['media_url'] );
			
			if ( $feed['caption'] ) {
				add_post_meta( $postID, '_crb_instagram_caption', $feed['caption'] );
			}
		}
	}

	public function refreshFeeds() {
		if ( empty( $_GET['AuthorizationCode'] ) ) {
				return;
		}

		$this->updateOption( $_GET );

		$credentials = Feeder::getApiCredentials();

        $params = array(
            'endpoint_url' => 'https://api.instagram.com/oauth/access_token',
            'type'         => 'POST',
            'url_params'   => array(
                'app_id'       => $credentials['AppID'],
                'app_secret'   => $credentials['AppSecret'],
                'grant_type'   => 'authorization_code',
                'redirect_uri' => admin_url(),
                'code'         => $credentials['AppAuthorizationCode'],
            )
        );

        $response = $this->makeApiCall( $params );

        $this->updateOption( $response );

        $credentials = Feeder::getApiCredentials();

        $params = array(
            'endpoint_url' => 'https://graph.instagram.com//me/media',
            'type'         => 'GET',
            'url_params'   => array(
                'fields'       => 'id,media_type,media_url,username,timestamp,caption,comments',
                'access_token' => $credentials['AppAccessToken'],
            )
        );

        $instagramFeeds = $this->makeApiCall( $params );

        $this->deleteFeeds();

        $this->insertFeeds( $instagramFeeds );
	}

	function makeApiCall( $params ) {
        $ch = curl_init();

        $endpoint = $params[ 'endpoint_url' ];

        if ( $params[ 'type' ] === 'POST' ) { // post request
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params[ 'url_params' ] ) );
            curl_setopt( $ch, CURLOPT_POST, 1 );
        } elseif ( 'GET' == $params['type'] ) { // get request
            //add params to endpoint
            $endpoint .= '?' . http_build_query( $params[ 'url_params' ] );
        }

        // general curl options
        curl_setopt( $ch, CURLOPT_URL, $endpoint );

        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $response = curl_exec( $ch );

        curl_close( $ch );

        $responseArray = json_decode( $response, true );

        return $responseArray;
    }
}
