<?php
/**
 * Web3WP Options Page.
 *
 * @package Web3WP
 */

namespace Web3WP\Admin;

use function Web3WP\get_plugin_options;

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
		__( 'Web3 WP', 'web3wp' ), // page_title.
		__( 'Web3 WP', 'web3wp' ), // menu_title.
		'manage_options', // capability.
		'web3wp', // menu_slug.
		__NAMESPACE__ . '\create_admin_page',
		'dashicons-id'
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
}

/**
 * Sanitize plugin settings on save.
 *
 * @param array $input The input array.
 * @return array
 */
function sanitize( $input ) {
	$sanitary_values = array();

	$sanitary_values['login_trigger_class']   = isset( $input['login_trigger_class'] ) && ! empty( $input['login_trigger_class'] ) ? sanitize_text_field( wp_unslash( $input['login_trigger_class'] ) ) : 'connect-wallet-link';
	$sanitary_values['login_trigger_auto'] = falsey_truthy( 'login_trigger_auto', $input );

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
