<?php
/**
 * Web3WP Plugin.
 *
 * @package   Web3WP
 * @copyright Copyright(c) 2021, Rheinard Korf
 * @licence https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPLv3)
 */

/**
 * Plugin Name: Web3WP
 * Plugin URI: https://github.com/web3wp/web3wp-plugin
 * Description: Authenticate users using a Web3 wallet, like MetaMask.
 * Version: 0.1.0
 * Author: Rheinard Korf
 * Author URI: https://rheinardkorf.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: web3wp
 */

require_once __DIR__ . '/lib/index.php';
require_once __DIR__ . '/inc/utils.php';
require_once __DIR__ . '/admin/options.php';
require_once __DIR__ . '/inc/hooks.php';
require_once __DIR__ . '/inc/login-api.php';
