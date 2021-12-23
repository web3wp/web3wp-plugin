<?php
/**
 * Web3WP Options Page.
 *
 * @package Web3WP
 */

namespace Web3WP\Admin;

use function Web3WP\get_plugin_options;
use function Web3WP\get_networks;

if ( ! \is_admin() ) {
	return;
}

const PLUGIN_OPTIONS_KEY = \Web3WP\PLUGIN_OPTIONS_KEY;

add_action( 'admin_menu', __NAMESPACE__ . '\add_plugin_page' );
add_action( 'admin_init', __NAMESPACE__ . '\page_init' );

/**
 * Adds a new menu page for plugin options.
 *
 * @return void
 */
function add_plugin_page() {

	add_menu_page(
		'', // Overwritten.
		__( 'Web3 WP', 'web3wp' ), // Root menu title.
		'manage_options',
		'web3wp',
		__NAMESPACE__ . '\create_admin_page',
		"data:image/svg+xml,%3Csvg clip-rule='evenodd' fill-rule='evenodd' stroke-linejoin='round' stroke-miterlimit='2' viewBox='0 0 400 500' width='34' height='20' xml:space='preserve' xmlns='http://www.w3.org/2000/svg'%3E%3Cg transform='matrix(5 0 0 5 -1665 -805.1)'%3E%3Cg transform='translate(-18 -57.98)'%3E%3Cpath d='m402 278.09h0.018c0.347 6.588 5.807 11.831 12.482 11.831s12.135-5.243 12.482-11.831h0.018v-52.587c0-3.587 2.913-6.5 6.5-6.5s6.5 2.913 6.5 6.5v52.668c0 13.66-11.09 24.75-24.75 24.75-8.062 0-15.229-3.863-19.75-9.839-4.521 5.976-11.688 9.839-19.75 9.839-13.66 0-24.75-11.09-24.75-24.75v-52.668c0-3.587 2.913-6.5 6.5-6.5s6.5 2.913 6.5 6.5v52.587h0.018c0.347 6.588 5.807 11.831 12.482 11.831s12.135-5.243 12.482-11.831h0.018v-52.257c0-3.77 2.913-6.83 6.5-6.83s6.5 3.06 6.5 6.83v52.257z' fill='rgba(255,255,255,1.0)'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"
	);

	// Duplication hack to remove root item as a sub item.
	add_submenu_page(
		'web3wp',
		'', // Overwritten.
		__( 'Settings', 'web3wp' ), // menu_title.
		'manage_options',
		'web3wp', // Duplicate root slug.
		__NAMESPACE__ . '\create_admin_page', // Duplicate root function.
	);
}

/**
 * Renders the settings page.
 *
 * @return void
 */
function create_admin_page() {
	?>
		<div class="wrap">
			<h2><?php echo esc_html__( 'Web3 WP', 'web3wp' ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'web3wp_option_group' );
				do_settings_sections( 'web3wp-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
}

/**
 * Register settings and fields before they can be rendered.
 *
 * @return void
 */
function page_init() {
	register_setting(
		'web3wp_option_group', // option_group.
		PLUGIN_OPTIONS_KEY, // option_name.
		__NAMESPACE__ . '\sanitize'  // sanitize_callback.
	);

	/*
	 * E-mail and Password settings.
	 */
	add_settings_section(
		'web3wp_password_section', // id.
		__( 'Password Settings', 'web3wp' ), // title.
		__NAMESPACE__ . '\settings_section_info', // callback.
		'web3wp-admin' // page.
	);

	add_settings_field(
		'disable_password_fields',
		__( 'Disable password fields', 'web3wp' ),
		__NAMESPACE__ . '\disable_password_fields_callback',
		'web3wp-admin',
		'web3wp_password_section'
	);

	add_settings_field(
		'disable_application_passwords',
		__( 'Disable application passwords', 'web3wp' ),
		__NAMESPACE__ . '\disable_application_passwords_callback',
		'web3wp-admin',
		'web3wp_password_section'
	);
}

/**
 * Sanitize plugin settings on save.
 *
 * @param array $input The input array.
 * @return array
 */
function sanitize( $input ) {
	$sanitary_values = array();

	$sanitary_values['disable_password_fields']       = falsey_truthy( 'disable_password_fields', $input );
	$sanitary_values['disable_application_passwords'] = falsey_truthy( 'disable_application_passwords', $input );

	return $sanitary_values;
}

/**
 * Returns real true/false values.
 *
 * @param [type] $key   Key to search on the $input.
 * @param [type] $input Input to search.
 * @return bool
 */
function falsey_truthy( $key, $input ) {
	if ( ! isset( $input[ $key ] ) ) {
		return 0;
	}
	return in_array( $input[ $key ], array( 'on', 'yes', 1, true ), true );
}

/**
 * Show info under credentials settings title.
 *
 * @return void
 */
function settings_section_info() {
	?>
	<p><?php echo esc_html__( 'The following options changes the behaviour of password fields on the user profile.', 'web3wp' ); ?></p>
	<?php
}

/**
 * Render field.
 *
 * @return void
 */
function disable_password_fields_callback() {
	$plugin_options = get_plugin_options();
	printf(
		'<input type="checkbox" name="%s[disable_password_fields]" id="disable_password_fields" %s><span class="description">%s</span>',
		esc_attr( PLUGIN_OPTIONS_KEY ),
		checked( 1, isset( $plugin_options['disable_password_fields'] ) ? (bool) $plugin_options['disable_password_fields'] : false, false ),
		esc_html__( '(recommended) This prevents the change password fields on the user profile. Keep this if you want backup logins, but its not ideal.', 'web3wp' )
	);
}

/**
 * Render field.
 *
 * @return void
 */
function disable_application_passwords_callback() {
	$plugin_options = get_plugin_options();
	printf(
		'<input type="checkbox" name="%s[disable_application_passwords]" id="disable_application_passwords" %s><span class="description">%s</span>',
		esc_attr( PLUGIN_OPTIONS_KEY ),
		checked( 1, isset( $plugin_options['disable_application_passwords'] ) ? (bool) $plugin_options['disable_application_passwords'] : false, false ),
		esc_html__( 'Prevent application passwords for users. If your site is not exposing APIs for users, then click this checkbox.', 'web3wp' )
	);
}
