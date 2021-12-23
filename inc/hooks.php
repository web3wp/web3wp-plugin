<?php
/**
 * WordPress hooks.
 *
 * @package Web3WP
 */

namespace Web3WP;

$options = \Web3WP\get_plugin_options();

// Remove profile password fields. Maybe.
if ( $options['disable_password_fields'] ) {
	add_filter( 'show_password_fields', '__return_false', 10 );
}

// Remove profile application password fields. Maybe.
if ( $options['disable_application_passwords'] ) {
	add_filter( 'wp_is_application_passwords_available_for_user', '__return_false', 10 );
}

/**
 * Enqueue and prepare scripts.
 *
 * @return void
 */
function enqueue_scripts() {
	// Wallet connect features.
	wp_enqueue_script( 'ethers-js', plugins_url() . '/web3wp-plugin/assets/ethers-5.1.umd.min.js', array(), '20211130', true );
	wp_enqueue_script( 'web3wp-js', plugins_url() . '/web3wp-plugin/assets/connect.js', array( 'ethers-js' ), '20211130', true );

	// The `wp_rest` nonce will be handled by the REST API.
	$nonce = wp_create_nonce( 'wp_rest' );

	// Get the user object. Avoiding roles, etc, for security.
	$unmodified_user = wp_get_current_user();
	$current_user    = $unmodified_user;
	if ( ! is_wp_error( $current_user ) ) {
		$wallet       = get_user_meta( $current_user->ID, 'wallet_address', true );
		$current_user = array(
			'ID'             => $current_user->ID,
			'display_name'   => 0 === $current_user->ID ? '' : $current_user->data->display_name,
			'wallet_address' => $wallet,
		);
	}

	// Allows for overriding additional user properties.
	$current_user = apply_filters( 'web3wp_current_user', $current_user, $unmodified_user );

	// translators: Place the nonce where required.
	$signing_message     = sprintf( __( "Click 'Sign' to sign in with one time sign-in code: %s", 'web3wp' ), $nonce );
	$web3wp_connect_vars = array(
		'nonce'          => $nonce,
		'signingMessage' => apply_filters( 'web3wp_signing_message', $signing_message, $nonce ),
		'baseUrl'        => rest_url( 'web3wp/' ),
		'user'           => $current_user,
	);
	wp_add_inline_script( 'web3wp-js', 'const web3wp_connect = ' . wp_json_encode( $web3wp_connect_vars ) );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

function add_common_networks( $networks ) {

	$common = (array) $networks;

	// Ethereum.
	$common = array_merge(
		array(
			'ethereum' => array(
				'name'         => 'Ethereum Mainnet',
				'rpc_url'      => 'https://mainnet.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
				'chain_id'     => 1,
				'symbol'       => 'ETH',
				'explorer_url' => 'https://etherscan.io',
				'locked'       => true,
				'type'         => 'evm',
			),
			'rinkeby'  => array(
				'name'         => 'Ethereum (Rinkeby Testnet)',
				'rpc_url'      => 'https://rinkeby.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
				'chain_id'     => 4,
				'symbol'       => 'ETH',
				'explorer_url' => 'https://rinkeby.etherscan.io',
				'locked'       => true,
				'type'         => 'evm',
			),
		),
		$common
	);

	// Polygon.
	$common = array_merge(
		array(
			'polygon' => array(
				'name'         => 'Polygon Mainnet',
				'rpc_url'      => 'https://rpc-mainnet.maticvigil.com',
				'chain_id'     => 137,
				'symbol'       => 'MATIC',
				'explorer_url' => 'https://polygonscan.com/',
				'locked'       => true,
				'type'         => 'evm',
			),
			'mumbai'  => array(
				'name'         => 'Polygon (Mumbai Testnet)',
				'rpc_url'      => 'https://rpc-mumbai.maticvigil.com',
				'chain_id'     => 80001,
				'symbol'       => 'MATIC',
				'explorer_url' => 'https://mumbai.polygonscan.com',
				'locked'       => true,
				'type'         => 'evm',
			),
		),
		$common
	);

	return $common;
}
add_filter( 'web3wp_networks', __NAMESPACE__ . '\add_common_networks', 1, 1 );
