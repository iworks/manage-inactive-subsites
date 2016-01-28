<?php
/*
Plugin Name: Manage Inactive Subsites
Plugin URI: http://manage-inactive-subsites.iworks.pl/
Description: Allow automate handle status of inactive site in MultiSite installation.
Version: trunk
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

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

/**
 * require: IworksManage Inactive Subsites Class
 */
if ( !class_exists( 'IworksManageInactiveSubsites' ) ) {
    require_once( $vendor . '/iworks/manage_inactive_subsites.php' );
}
/**
 * i18n
 */
load_plugin_textdomain( 'manage-inactive-subsites', false, dirname( dirname( plugin_basename( __FILE__) ) ).'/languages' );


/**
 * Summary.
 *
 * Description.
 *
 * @since x.x.x
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
function iworks_manage_inactive_subsites_activate()
{
}

/**
 * Summary.
 *
 * Description.
 *
 * @since x.x.x
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
function iworks_manage_inactive_subsites_deactivate()
{
}

$iworks_manage_inactive_subsites = new IworksManageInactiveSubsites();

/**
 * install & uninstall
 */
register_activation_hook  ( __FILE__, 'iworks_manage_inactive_subsites_activate'   );
register_deactivation_hook( __FILE__, 'iworks_manage_inactive_subsites_deactivate' );
