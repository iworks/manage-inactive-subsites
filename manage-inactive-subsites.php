<?php
/*
Plugin Name: Manage Inactive Subsites
Plugin URI: http://iworks.pl/manage-inactive-subsites
Description: Allow automate handle status of inactive site in MultiSite installation.
Version: 1.0.0
Network: true
Text Domain: manage-inactive-subsites
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2019 Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * load only for admin
 */

if ( is_admin() ) {
	/**
	 * i18n
	 */
	function manage_inactive_subsites_load_plugin_textdomain() {
		load_plugin_textdomain( 'manage-inactive-subsites', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}
	add_action( 'plugins_loaded', 'manage_inactive_subsites_load_plugin_textdomain' );
	/**
	 * require: IworksManageInactiveSubsitesAdmin Class
	 */
	if ( ! class_exists( 'IworksManageInactiveSubsitesAdmin' ) ) {
		require_once dirname( __FILE__ ) . '/vendor/iworks/manage-inactive-subsites/admin.php';
		new IworksManageInactiveSubsitesAdmin();
	}
}

/**
 * require: IworksManageInactiveSubsitesCron Class
 */
if ( ! class_exists( 'IworksManageInactiveSubsitesCron' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/iworks/manage-inactive-subsites/cron.php';
	new IworksManageInactiveSubsitesCron();
}

/**
 * Activate plugin hook.
 *
 * @since 1.0.0
 */
function iworks_manage_inactive_subsites_activate() {
	if ( is_admin() ) {
		add_option( 'manage-inactive-subsites-deactivate', 'deactivate', null, 'no' );
	}
	wp_schedule_event( time(), 'hourly', 'manage_inactive_subsites_cron_hourly' );
}

/**
 * Deactivate plugin hook.
 *
 * Deactivate function clear wp-cron jobs of this plugin and clear site
 * options.
 *
 * @since 1.0.0
 */
function iworks_manage_inactive_subsites_deactivate() {
	delete_site_option( 'manage_inactive_subsites_interval_type' );
	delete_site_option( 'manage_inactive_subsites_interval_size' );
	delete_site_option( 'manage_inactive_subsites_action' );
	wp_clear_scheduled_hook( 'manage_inactive_subsites_cron_hourly' );
}

/**
 * install & uninstall
 */
register_activation_hook( __FILE__, 'iworks_manage_inactive_subsites_activate' );
register_deactivation_hook( __FILE__, 'iworks_manage_inactive_subsites_deactivate' );

