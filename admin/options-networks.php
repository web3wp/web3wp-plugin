<?php
/**
 * Web3WP Network Options Page.
 *
 * @package Web3WP
 */

namespace Web3WP\Admin;

use function Web3WP\get_plugin_options;
use function Web3WP\get_networks;

if ( ! \is_admin() ) {
	return;
}

const PLUGIN_NETWORKS_KEY = \Web3WP\PLUGIN_NETWORKS_KEY;

add_action( 'admin_menu', __NAMESPACE__ . '\add_network_settings_page' );
add_action( 'admin_init', __NAMESPACE__ . '\network_settings_page_init' );

/**
 * Adds a new menu page for network options.
 *
 * @return void
 */
function add_network_settings_page() {
	global $web3wp_network_settings_page;
	$web3wp_network_settings_page = add_submenu_page(
		'web3wp',
		__( 'Networks', 'web3wp' ), // page_title.
		__( 'Networks', 'web3wp' ), // menu_title.
		'manage_options', // capability.
		'web3wp_networks', // menu_slug.
		__NAMESPACE__ . '\create_network_settings_page'
	);
}

/**
 * Show list table view of networkd when not editing.
 *
 * @param array $networks List of networks to display.
 * @return void
 */
function render_display_mode( $networks ) {
	?>
		<p><?php echo esc_html__( 'Available Networks', 'web3wp' ); ?></p>
		<!-- <select name="networks">
		<?php

		foreach ( $networks as $nw_slug => $network ) {
			printf(
				'<option value="' . esc_attr( $nw_slug ) . '">' . esc_html( $network['name'] ) . ( $network['locked'] ? ' - &#x1f512;' : '' ) . '</option>'
			);
		}

		?>
		</select> -->

		<table class="wp-list-table widefat fixed striped" cellspacing="0" aria-describedby="">
			<thead>
				<tr>
					<th class="name"><?php esc_html_e( 'Network', 'web3wp' ); ?></th>
					<th class="name" width="80rem"><?php esc_html_e( 'Slug', 'web3wp' ); ?></th>
					<th class="name" width="30%"><?php esc_html_e( 'RPC', 'web3wp' ); ?></th>
					<th class="name" width="80rem"><?php esc_html_e( 'Chain ID', 'web3wp' ); ?></th>
					<th class="name" width="80rem"><?php esc_html_e( 'Symbol', 'web3wp' ); ?></th>
					<th class="name"><?php esc_html_e( 'Explorer', 'web3wp' ); ?></th>
					<th class="action" width=""></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $networks ) ) {
					foreach ( $networks as $nw_slug => $network ) {
						$name     = $network['name'];
						$edit_url = add_query_arg(
							array(
								'action' => 'edit',
								'slug'   => $nw_slug,
							)
						);
						?>
						<tr>
							<!-- name -->
							<th class="name" width="">
								<?php
								if ( ! $network['locked'] ) {
									?>
									<a href="<?php echo esc_url( $edit_url ); ?>" class="row-title"><?php } ?>
									<strong><?php echo esc_html( $name ); ?></strong>
								<?php
								if ( ! $network['locked'] ) {
									?>
									</a><?php } ?>
							</th>
							<!-- slug -->
							<td class="name" width="80rem"><?php echo esc_html( $nw_slug ); ?></td>
							<!-- rpc -->
							<td class="name" width=""><a href="<?php echo esc_url_raw( $network['rpc_url'] ); ?>"><?php echo esc_url( $network['rpc_url'] ); ?></a></td>
							<!-- chain -->
							<td class="name" width=""><?php echo esc_html( $network['chain_id'] ); ?></td>
							<!-- symbol -->
							<td class="name" width=""><?php echo esc_html( $network['symbol'] ); ?></td>
							<!-- explorer -->
							<td class="name" width=""><?php echo esc_html( $network['explorer_url'] ); ?></td>
							<td class="action">
								<?php if ( ! $network['locked'] ) { ?>
									<form method="post" action="admin.php?page=web3wp_networks" 
										<?php // translators: positional argument is the network slug. ?>
										onsubmit="return confirm('<?php echo sprintf( esc_html__( 'Are you sure you want to delete network: %s', 'web3wp' ), esc_attr( $nw_slug ) ); ?>');"
									>
										<input type="hidden" name="option_page" value="web3wp_networks_group" />
										<input type="hidden" name="action" value="delete" />
										<input type="hidden" name="slug" value="<?php echo esc_attr( $nw_slug ); ?>" />
										<?php wp_nonce_field( 'delete', '_wpnonce', esc_url( add_query_arg( 'action', 'delete' ) ), true ); ?>
										<a class="button button-primary alignright" style="margin:0 10px;" aria-label="Edit Network Settings" href="<?php echo esc_url_raw( $edit_url ); ?>"><?php echo esc_html_e( 'Edit', 'web3wp' ); ?></a>&nbsp;
										<input type="submit" name="submit" id="submit" class="button button-secondary alignright" value="<?php echo esc_html_e( 'Delete', 'web3wp' ); ?>">
									</form>
								<?php } ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<form method="post" action="admin.php?page=web3wp_networks">
			<input type="hidden" name="option_page" value="web3wp_networks_group" />
			<input type="hidden" name="action" value="add" />
			<?php wp_nonce_field( 'add', '_wpnonce', esc_url( add_query_arg( 'action', 'add' ) ), true ); ?>
			<input type="submit" style="margin: 10px 0;" name="submit" id="submit" class="button button-primary alignleft" value="<?php esc_html_e( 'Add Network', 'web3wp' ); ?>">
		</form>
	<?php
}

/**
 * Show Settings API UI if in edit mode.
 *
 * @return void
 */
function render_edit_mode() {
	?>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
				<?php
				settings_fields( 'web3wp_networks_group' );
				do_settings_sections( 'web3wp-networks' );
				?>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
					<a href="<?php echo esc_url_raw( remove_query_arg( array( 'action', 'slug' ) ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Back to Networks', 'web3wp' ); ?></a>
				</p>
		</form>

	<?php
}

/**
 * Renders the settings page.
 *
 * @return void
 */
function create_network_settings_page() {

	$networks = get_networks();
	$action   = sanitize_text_field( filter_input( INPUT_GET, 'action' ) );
	?>
		<div class="wrap">
			<h2><?php echo esc_html__( 'Networks', 'web3wp' ); ?></h2>

			<?php
			if ( empty( $action ) || 'display' === $action ) {
				render_display_mode( $networks );
			}
			if ( 'edit' === $action || 'create' === $action ) {
				render_edit_mode();
			}

			?>
		</div>
		<?php
}

/**
 * Handle delete network action.
 *
 * @return void
 */
function handle_network_delete() {
	$action = wp_unslash( filter_input( INPUT_POST, 'action' ) );
	$nonce  = wp_unslash( filter_input( INPUT_POST, '_wpnonce' ) );
	$slug   = wp_unslash( filter_input( INPUT_POST, 'slug' ) );

	if ( 'delete' === $action && wp_verify_nonce( $nonce, $action ) ) {
		delete_network( $slug );
		add_action(
			'admin_notices',
			function() {
				?>
			<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Network deleted.', 'web3wp' ); ?></p>
			</div>
				<?php
			}
		);
	}
}

/**
 * Handle the submission of a new network.
 *
 * @return void
 */
function handle_network_add() {

	$action = wp_unslash( filter_input( INPUT_POST, 'action' ) );
	$nonce  = wp_unslash( filter_input( INPUT_POST, '_wpnonce' ) );

	if ( 'add' === $action && wp_verify_nonce( $nonce, $action ) ) {
		add_network();
		add_action(
			'admin_notices',
			function() {
				?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Network added.', 'web3wp' ); ?></p>
			</div>
				<?php
			}
		);
	}
}

/**
 * Return only networks that don't have `locked` set to true.
 *
 * @param array $networks All networks.
 * @return array
 */
function remove_locked_networks( $networks ) {
	return array_filter(
		$networks,
		function( $network ) {
			return ! $network['locked'];
		}
	);
}

/**
 * Given a list of networks, remove the one identified by slug.
 *
 * @param array  $networks List of networks.
 * @param string $slug Slug of network to remove.
 * @return array
 */
function filter_out_network_slug( $networks, $slug ) {
	$out = array_filter(
		$networks,
		function( $network ) use ( $slug ) {
			return $network !== $slug;
		},
		ARRAY_FILTER_USE_KEY
	);

	return isset( $out ) && ! empty( $out ) ? $out : array();
}

/**
 * Delete a network from the list by slug.
 *
 * @param string $slug Network slug.
 * @return void
 */
function delete_network( $slug ) {
	$networks = get_networks();
	$networks = remove_locked_networks( $networks );
	$networks = filter_out_network_slug( $networks, $slug );
	update_option( PLUGIN_NETWORKS_KEY, maybe_serialize( $networks ), false );
}

/**
 * Add a new network to list of networks.
 *
 * @param array $network Optional. Prefill values.
 * @return void
 */
function add_network( $network = array() ) {

	$network = (array) $network;

	$networks                    = get_networks();
	$total_networks              = count( $networks );
	$networks                    = remove_locked_networks( $networks );
	$date                        = new \DateTime();
	$id                          = $date->getTimestamp();
	$default                     = array();
	$default[ 'network-' . $id ] = array(
		'name'         => 'Network ' . $id,
		'rpc_url'      => '',
		'chain_id'     => 0,
		'symbol'       => '',
		'explorer_url' => '',
		'locked'       => false,
		'type'         => 'evm',
	);

	$network = array_merge(
		$default,
		$network
	);

	$networks = array_merge(
		$network,
		$networks
	);
	update_option( PLUGIN_NETWORKS_KEY, maybe_serialize( $networks ), false );
}

/**
 * Initialise Settings API.
 *
 * @return void
 */
function network_settings_page_init() {
	register_setting(
		'web3wp_networks_group', // option_group.
		PLUGIN_NETWORKS_KEY, // option_name.
		__NAMESPACE__ . '\sanitize_network_settings'  // sanitize_callback.
	);

	add_settings_section(
		'web3wp_network_default_section', // id.
		// __( 'Network Settings', 'web3wp' ), // title.
		'',
		false,
		'web3wp-networks' // page.
	);

	$networks = get_networks();
	$slug     = wp_unslash( filter_input( INPUT_GET, 'slug' ) );

	if ( ! empty( $slug ) && isset( $networks[ $slug ] ) ) {
		// slug.
		add_settings_field(
			'network_setting_slug',
			__( 'Network slug', 'web3wp' ),
			__NAMESPACE__ . '\network_setting_slug_cb',
			'web3wp-networks',
			'web3wp_network_default_section',
			array(
				'network' => $networks[ $slug ],
				'slug'    => $slug,
			)
		);
		// name.
		add_settings_field(
			'network_setting_name',
			__( 'Name', 'web3wp' ),
			__NAMESPACE__ . '\network_setting_name_cb',
			'web3wp-networks',
			'web3wp_network_default_section',
			array(
				'network' => $networks[ $slug ],
				'slug'    => $slug,
			)
		);
		// rpc_url.
		add_settings_field(
			'network_setting_rpc',
			__( 'RPC URL', 'web3wp' ),
			__NAMESPACE__ . '\network_setting_rpc_cb',
			'web3wp-networks',
			'web3wp_network_default_section',
			array(
				'network' => $networks[ $slug ],
				'slug'    => $slug,
			)
		);
		// chain_id.
		add_settings_field(
			'network_setting_chain',
			__( 'Chain Id', 'web3wp' ),
			__NAMESPACE__ . '\network_setting_chain_cb',
			'web3wp-networks',
			'web3wp_network_default_section',
			array(
				'network' => $networks[ $slug ],
				'slug'    => $slug,
			)
		);
		// symbol.
		add_settings_field(
			'network_setting_symbol',
			__( 'Symbol', 'web3wp' ),
			__NAMESPACE__ . '\network_setting_symbol_cb',
			'web3wp-networks',
			'web3wp_network_default_section',
			array(
				'network' => $networks[ $slug ],
				'slug'    => $slug,
			)
		);
		// explorer_url.
		add_settings_field(
			'network_setting_explorer',
			__( 'Name', 'web3wp' ),
			__NAMESPACE__ . '\network_setting_explorer_cb',
			'web3wp-networks',
			'web3wp_network_default_section',
			array(
				'network' => $networks[ $slug ],
				'slug'    => $slug,
			)
		);
	}

	handle_network_delete();
	handle_network_add();
}

/**
 * Render slug.
 *
 * @param array $params Network paramaters.
 *
 * @return void
 */
function network_setting_slug_cb( $params ) {
	$network  = $params['network'];
	$slug     = $params['slug'];
	$disabled = $network['locked'] ? 'disabled' : '';
	printf(
		'<input type="hidden" name="%s[%s][pre_slug]" value="%s">',
		esc_attr( PLUGIN_NETWORKS_KEY ),
		esc_attr( $slug ),
		esc_attr( $slug )
	);
	printf(
		'<input type="text" name="%s[%s][slug]" value="%s" %s><br /><span class="description">%s</span>',
		esc_attr( PLUGIN_NETWORKS_KEY ),
		esc_attr( $slug ),
		esc_attr( $slug ),
		esc_attr( $disabled ),
		esc_html__( 'A shortname used internally to identify the network (no spaces or symbols, except dashes).', 'web3wp' )
	);
}

/**
 * Render slug.
 *
 * @param array $params Network paramaters.
 *
 * @return void
 */
function network_setting_name_cb( $params ) {
	$network  = $params['network'];
	$slug     = $params['slug'];
	$disabled = $network['locked'] ? 'disabled' : '';
	printf(
		'<input type="text" name="%s[%s][name]" value="%s" %s><br /><span class="description">%s</span>',
		esc_attr( PLUGIN_NETWORKS_KEY ),
		esc_attr( $slug ),
		esc_attr( $network['name'] ),
		esc_attr( $disabled ),
		esc_html__( 'A shortname used internally to identify the network (no spaces or symbols, except dashes).', 'web3wp' )
	);
}

/**
 * Render slug.
 *
 * @param array $params Network paramaters.
 *
 * @return void
 */
function network_setting_rpc_cb( $params ) {
	$network  = $params['network'];
	$slug     = $params['slug'];
	$disabled = $network['locked'] ? 'disabled' : '';
	printf(
		'<input type="text" name="%s[%s][rpc_url]" value="%s" %s><br /><span class="description">%s</span>',
		esc_attr( PLUGIN_NETWORKS_KEY ),
		esc_attr( $slug ),
		esc_attr( $network['rpc_url'] ),
		esc_attr( $disabled ),
		esc_html__( 'A shortname used internally to identify the network (no spaces or symbols, except dashes).', 'web3wp' )
	);
}

/**
 * Render slug.
 *
 * @param array $params Network paramaters.
 *
 * @return void
 */
function network_setting_chain_cb( $params ) {
	$network  = $params['network'];
	$slug     = $params['slug'];
	$disabled = $network['locked'] ? 'disabled' : '';
	printf(
		'<input type="text" name="%s[%s][chain_id]" value="%s" %s><br /><span class="description">%s</span>',
		esc_attr( PLUGIN_NETWORKS_KEY ),
		esc_attr( $slug ),
		esc_attr( $network['chain_id'] ),
		esc_attr( $disabled ),
		esc_html__( 'A shortname used internally to identify the network (no spaces or symbols, except dashes).', 'web3wp' )
	);
}

/**
 * Render slug.
 *
 * @param array $params Network paramaters.
 *
 * @return void
 */
function network_setting_symbol_cb( $params ) {
	$network  = $params['network'];
	$slug     = $params['slug'];
	$disabled = $network['locked'] ? 'disabled' : '';
	printf(
		'<input type="text" name="%s[%s][symbol]" value="%s" %s><br /><span class="description">%s</span>',
		esc_attr( PLUGIN_NETWORKS_KEY ),
		esc_attr( $slug ),
		esc_attr( $network['symbol'] ),
		esc_attr( $disabled ),
		esc_html__( 'A shortname used internally to identify the network (no spaces or symbols, except dashes).', 'web3wp' )
	);
}


/**
 * Render slug.
 *
 * @param array $params Network paramaters.
 *
 * @return void
 */
function network_setting_explorer_cb( $params ) {
	$network  = $params['network'];
	$slug     = $params['slug'];
	$disabled = $network['locked'] ? 'disabled' : '';
	printf(
		'<input type="text" name="%s[%s][explorer_url]" value="%s" %s><br /><span class="description">%s</span>',
		esc_attr( PLUGIN_NETWORKS_KEY ),
		esc_attr( $slug ),
		esc_attr( $network['explorer_url'] ),
		esc_attr( $disabled ),
		esc_html__( 'A shortname used internally to identify the network (no spaces or symbols, except dashes).', 'web3wp' )
	);
}

/**
 * Sanitize plugin settings on save.
 *
 * @param array $input The input array.
 * @return array
 */
function sanitize_network_settings( $input ) {

	// This happens because add_network() also triggers
	// the sanitization function.
	if ( empty( $input ) || ! is_array( $input ) ) {
		$networks = maybe_unserialize( $input );
		return $networks;
	}

	$networks         = remove_locked_networks( get_networks() );
	$input_keys       = array_keys( $input );
	$network_index    = array_shift( $input_keys );
	$modified_network = array_pop( $input );
	$slug             = $modified_network['slug'];
	$pre_slug         = $modified_network['pre_slug'];

	unset( $modified_network['slug'] );
	unset( $modified_network['pre_slug'] );

	// If the slug changed, drop the previous settings.
	if ( $pre_slug !== $slug ) {
		$pre_network = isset( $networks[ $pre_slug ] ) ? $networks[ $pre_slug ] : false;
		if ( $pre_network ) {
			unset( $networks[ $pre_slug ] );
			$networks[ $slug ] = $pre_network;
		}
	}

	// Update some settings.
	$networks[ $slug ]['name']         = sanitize_text_field( $modified_network['name'] );
	$networks[ $slug ]['rpc_url']      = esc_url_raw( $modified_network['rpc_url'] );
	$networks[ $slug ]['chain_id']     = (int) $modified_network['chain_id'];
	$networks[ $slug ]['symbol']       = sanitize_text_field( $modified_network['symbol'] );
	$networks[ $slug ]['explorer_url'] = esc_url_raw( $modified_network['explorer_url'] );

	return $networks;
}
