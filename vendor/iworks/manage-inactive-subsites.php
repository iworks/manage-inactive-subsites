<?php
/*

Copyright 2016 Marcin Pietrzak (marcin@iworks.pl)

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

if ( !defined( 'WPINC' ) ) {
    die;
}

if ( class_exists( 'IworksManageInactiveSubsites' ) ) {
    return;
}

/**
 * Summary.
 *
 * Description.
 *
 * @since 1.0.0
 */
class IworksManageInactiveSubsites {

    /**
     * Plugin base name
     *
     * @since 1.0.0
     * @access private
     * @var string $base plugin base name
     */
    private $base_name;

    /**
     * Plugin version.
     *
     * @since 1.0.0
     * @access private
     * @var string $version plugin version
     */
    private $version = 'trunk';

    public function __construct() {
        /**
         * static settings
         */
        $this->plugin_basename = plugin_basename( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/manage-inactive-subsites.php';

        /**
         * actions
         */
        add_action( 'admin_init', array( $this,'deactivate_plugin_for_non_network_install' ) );
        add_action( 'network_admin_menu', array( $this, 'add_network_settings_submenu' ) );
        add_action( 'admin_menu', array( $this, 'add_network_settings_submenu' ) );
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since 1.0.0
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    public function add_network_settings_submenu() {
        add_submenu_page(
            'settings.php',
            __('Manage Inactive Subsites', 'manage-inactive-subsites'),
            __('Manage Inactive Subsites', 'manage-inactive-subsites'),
            'manage_network_options',
            'manage-inactive-subsites-settings',
            array($this, 'plugin_settings_page')
        );

    }

    /**
     * Manage Inactive Subsites settings page
     *
     * This function produce plugin settings page, which allow to setup plugin
     * behaviors.
     *
     * @since 1.0.0
     *
     */
    public function plugin_settings_page() {
        echo '<div class="wrap">';
        printf( '<h1>%s</h1>', __( 'Manage Inactive Subsites', 'manage-inactive-subsites' ) );
        echo '</div>';
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since 1.0.0
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    public function deactivate_plugin_for_non_network_install() {
        if ( is_network_admin() ) {
            /**
             * delete from main site, to avoid problems
             */
            if ( is_main_site() ) {
                delete_option('manage-inactive-subsites-deactivate');
            }
            return;
        }
        if ( 'deactivate' != get_option( 'manage-inactive-subsites-deactivate', '' ) ) {
            return;
        }
        add_action( 'admin_notices', array( $this, 'deactivate_plugin_for_non_network_install_admin_notice' ) );
        deactivate_plugins( $this->plugin_basename );
        delete_option('manage-inactive-subsites-deactivate');
    }

    /**
     * Summary.
     *
     * Description.
     *
     * @since 1.0.0
     * @access (for functions: only use if private)
     *
     * @see Function/method/class relied on
     * @link URL
     * @global type $varname Description.
     * @global type $varname Description.
     *
     * @param type $var Description.
     * @param type $var Optional. Description.
     * @return type Description.
     */
    public function deactivate_plugin_for_non_network_install_admin_notice() {
        printf(
            '<div class="notice notice-info"><p>%s</p></div>',
            __('<b>Manage Inactive Subsites</b> can be only instaled as network activation.', 'manage-inactive-subsites')
        );
    }

}
