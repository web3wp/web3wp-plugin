<?php

namespace Web3WP;

use WP_REST_Request;
use WP_REST_Server;
use WP_User_Query;
use \Web3WP\Crypto\EcRecover;
use WP_REST_Response;

/**
 * Initialize the Web3WP APIs.
 *
 * @return void
 */
function rest_api_init()
{
    register_rest_route(
        'web3wp',
        '/login/',
        array(
            'methods'             => WP_REST_Server::CREATABLE, // POST.
            'callback'            => __NAMESPACE__ . '\login_post_request',
            'permission_callback' => '__return_true',
        )
    );

    register_rest_route(
        'web3wp',
        '/logout/',
        array(
            'methods'             => WP_REST_Server::CREATABLE, // POST.
            'callback'            => __NAMESPACE__ . '\logout_post_request',
            'permission_callback' => '__return_true',
        )
    );
}
add_action('rest_api_init', __NAMESPACE__ . '\rest_api_init');


/**
 * Handle the login POST request.
 *
 * @param \WP_REST_Request $request Request object.
 * @return void
 */
function login_post_request( \WP_REST_Request $request ) {
	// REST API has already verified the nonce for us, no need to do it again.
	$nonce = $request->get_header( 'X-WP-Nonce' );

	// Determine the wallet address.
	$signingMessage = sprintf( __( "Click 'Sign' to sign in with one time sign-in code: %s", 'wallet_connect' ), $nonce );
	$message        = json_encode( apply_filters( 'wallet-connect-signing-message', $signingMessage, $nonce ) );

	$signature      = $request->get_header( 'X-Signed-Message' );
	$wallet_address = EcRecover::personalEcRecover( $message, $signature );

	// Get user and login...
	$user_query = new WP_User_Query(
		array(
			'meta_key'   => 'wallet_address',
			'meta_value' => $wallet_address,
			'number'     => 1,
		)
	);
	if ( ! empty( $user_query->get_results() ) ) {
		$user = $user_query->get_results()[0];
	} else {

		// Disable email sending...
		add_filter( 'send_email_change_email', '__return_false', 10 );

		// Insert new user...
		$user_id = wp_insert_user(
			wp_unslash(
				array(
					'user_login' => $wallet_address,
					// Email hack to avoid using emails on login.
					'user_email' => 'user@' . $wallet_address . '.email',
				)
			)
		);

		if ( is_wp_error( ( $user_id ) ) ) {
			$user = null;
		} else {
			$user = get_user_by( 'ID', $user_id );
		}

		if ( $user ) {
			update_user_meta( $user_id, 'wallet_address', $wallet_address );
		}
	}

	// If we have a valid user then log them in!
	if ( ! is_wp_error( $user ) ) {
		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID ); // Set the current user detail
		wp_set_auth_cookie( $user->ID ); // Set auth details in cookie
	}

	$output = array(
        'message' => __('User logged in.','web3wp'),
		'user' => $user,
	);

	return new WP_REST_Response(
        $output,
        200,
    );
}

/**
 * Handle the logout POST request.
 *
 * @param \WP_REST_Request $request Request object.
 * @return void
 */
function logout_post_request( \WP_REST_Request $request ) {
	// REST API has already verified the nonce for us, no need to do it again.
	$nonce = $request->get_header( 'X-WP-Nonce' );
    wp_clear_auth_cookie();

	$output = array(
		'message' => __('User logged out.','web3wp'),
	);

	return new WP_REST_Response(
        $output,
        200,
    );
}
