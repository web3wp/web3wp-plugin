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
	 * Login trigger settings.
	 */
	add_settings_section(
		'web3wp_login_section', // id.
		__( 'Login Settings', 'web3wp' ), // title.
		null, // callback.
		'web3wp-admin' // page.
	);

	add_settings_field(
		'login_trigger_class',
		__( 'CSS trigger class', 'web3wp' ),
		__NAMESPACE__ . '\login_trigger_class_callback',
		'web3wp-admin',
		'web3wp_login_section'
	);

	add_settings_field(
		'login_trigger_auto',
		__( 'Connect wallet on load', 'web3wp' ),
		__NAMESPACE__ . '\login_trigger_auto_callback',
		'web3wp-admin',
		'web3wp_login_section'
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

	/*
	 * Network settings.
	 */
	add_settings_section(
		'web3wp_network_config_section', // id.
		__( 'Network Configuration', 'web3wp' ), // title.
		null, // callback.
		'web3wp-admin' // page.
	);

	add_settings_field(
		'web3wp_default_network',
		__( 'Default Network', 'web3wp' ),
		__NAMESPACE__ . '\web3wp_default_network_callback',
		'web3wp-admin',
		'web3wp_network_config_section'
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

	$sanitary_values['login_trigger_class'] = isset( $input['login_trigger_class'] ) && ! empty( $input['login_trigger_class'] ) ? sanitize_text_field( wp_unslash( $input['login_trigger_class'] ) ) : 'connect-wallet-link';
	$sanitary_values['login_trigger_auto']  = falsey_truthy( 'login_trigger_auto', $input );

	$sanitary_values['disable_password_fields']       = falsey_truthy( 'disable_password_fields', $input );
	$sanitary_values['disable_application_passwords'] = falsey_truthy( 'disable_application_passwords', $input );

	$sanitary_values['default_network'] = isset( $input['default_network'] ) && ! empty( $input['default_network'] ) ? sanitize_text_field( wp_unslash( $input['default_network'] ) ) : 'ethereum';

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
 * Render field.
 *
 * @return void
 */
function login_trigger_class_callback() {
	$plugin_options = get_plugin_options();
	printf(
		'<input type="text" name="%s[login_trigger_class]" id="login_trigger_class" value="%s" placeholder="%s"><br><span class="description">%s</span>',
		esc_attr( PLUGIN_OPTIONS_KEY ),
		isset( $plugin_options['login_trigger_class'] ) ? esc_attr( $plugin_options['login_trigger_class'] ) : '',
		esc_attr( 'connect-wallet-link' ),
		esc_html__( 'CSS class that will trigger wallet connect.', 'web3wp' )
	);
}

/**
 * Plugin psuedo autoloader.
 *
 * @return void
 */
function login_trigger_auto_callback() {
	$plugin_options = get_plugin_options();
	printf(
		'<input type="checkbox" name="%s[login_trigger_auto]" id="login_trigger_auto" %s><span class="description">%s</span>',
		esc_attr( PLUGIN_OPTIONS_KEY ),
		checked( 1, isset( $plugin_options['login_trigger_auto'] ) ? (bool) $plugin_options['login_trigger_auto'] : false, false ),
		esc_html__( 'Trigger immediately when a user visits.', 'web3wp' )
	);
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

/**
 * Render default network field.
 */
function web3wp_default_network_callback() {

	$plugin_options   = get_plugin_options();
	$networks         = get_networks();
	$selected_network = isset( $plugin_options['default_network'] ) ? $plugin_options['default_network'] : '';

	printf(
		'<select name="%s[default_network]" id="default_network">',
		esc_attr( PLUGIN_OPTIONS_KEY ),
	);

	foreach ( $networks as $nw_slug => $network ) {
		$select = selected( $nw_slug, $selected_network );
		printf(
			'<option %s value="' . esc_attr( $nw_slug ) . '">' . esc_html( $network['name'] ) . ( $network['locked'] ? ' - &#x1f512;' : '' ) . '</option>',
			esc_attr( $select )
		);
	}

	esc_html_e( '</select>' );
}
