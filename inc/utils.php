<?php
/**
 * Web3WP Options Page.
 *
 * @package Web3WP
 */

namespace Web3WP;

const PLUGIN_OPTIONS_KEY = 'web3wp_options';

/**
 * Utility function to get plugin options.
 *
 * @return mixed
 */
function get_plugin_options() {
	$options = get_option( PLUGIN_OPTIONS_KEY );
	$options = $options ? $options : array();
	return array_merge(
		array(
			'disable_password_fields'       => 1,
			'disable_application_passwords' => 1,
		),
		$options
	);
}
