<?php

namespace Web3WP;

$options = \Web3WP\get_plugin_options();

// Remove profile password fields. Maybe.
if ( $options['disable_password_fields']) {
    add_filter( 'show_password_fields', '__return_false', 10 );
}

// Remove profile application password fields. Maybe.
if ( $options['disable_application_passwords']) {
    add_filter( 'wp_is_application_passwords_available_for_user', '__return_false', 10 );
}

/**
 * Enqueue and prepare scripts.
 *
 * @return void
 */
function enqueue_scripts() {
	// Wallet connect features.
	wp_enqueue_script( 'ethers-js', plugins_url() . '/web3wp-plugin/assets/ethers-5.1.umd.min.js', array(), false, true );
	wp_enqueue_script( 'web3wp-js', plugins_url() . '/web3wp-plugin/assets/connect.js', array( 'ethers-js' ), false, true );

	// The `wp_rest` nonce will be handled by the REST API.
	$nonce = wp_create_nonce( 'wp_rest' );

	// Get the user object. Avoiding roles, etc, for security.
    $unmodified_user = wp_get_current_user();
	$current_user = $unmodified_user;
	if ( ! is_wp_error( $current_user ) ) {
		$wallet       = get_user_meta( $current_user->ID, 'wallet_address', true );
		$current_user = array(
			'ID'             => $current_user->ID,
			'display_name'   => $current_user->ID === 0 ? '' : $current_user->data->display_name,
			'wallet_address' => $wallet,
		);
	}

    // Allows for overriding additional user properties.
    $current_user = apply_filters( 'web3wp-current-user', $current_user, $unmodified_user );

	$signingMessage      = sprintf( __( "Click 'Sign' to sign in with one time sign-in code: %s", 'web3wp' ), $nonce );
	$web3wp_connect_vars = array(
		'nonce'          => $nonce,
		'signingMessage' => apply_filters( 'web3wp-signing-message', $signingMessage, $nonce ),
		'loginUrl'       => rest_url( 'web3wp/login' ),
		'user'           => $current_user,
	);
	wp_add_inline_script( 'web3wp-js', 'const web3wp_connect = ' . wp_json_encode( $web3wp_connect_vars ) );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );