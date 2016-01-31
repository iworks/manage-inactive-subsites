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

if ( class_exists( 'IworksManageInactiveSubsitesCron' ) ) {
    return;
}

require_once( dirname( dirname( __FILE__ ) ) . '/manage-inactive-subsites.php' );

/**
 * Manage Inactive Subsites Cron
 *
 * This class contain WP-Cron functions of this plugin.
 *
 * @since 1.0.0
 */
class IworksManageInactiveSubsitesCron extends IworksManageInactiveSubsites {

    public function __construct() {
        parent::__construct();
        add_action( 'manage_inactive_subsites_cron_hourly', array( $this, 'wp_cron_hourly' ) );
        /**
         * debug! delete line under, befor publish
         */
        add_action( 'manage_inactive_subsites_cron', array( $this, 'wp_cron_hourly' ) );
    }

    public function wp_cron_hourly() {
        $settings = $this->get_settings();
        $limit = 1;
        $where = '';
        switch( $settings['action'] ) {
        case 'deactivate':
            $where = 'AND deleted = 0 ';
            $limit = 10;
            break;
        case 'archive':
            $where = 'AND archived = 0 ';
            $limit = 10;
            break;
        }

        global $wpdb;

        $sql = 'SELECT blog_id, site_id FROM %s ';
        $sql .= 'WHERE last_updated < DATE_SUB( NOW(), INTERVAL %d %s ) ';
        $sql .= $where;
        if ( is_main_site() ) {
            $sql .= sprintf( 'AND blog_id <> %d ', get_current_blog_id() );
        }
        $sql .= 'ORDER BY last_updated DESC ';
        $sql .= 'limit %%d';
        $sql = sprintf( $sql, $wpdb->blogs, $settings['interval_size'], strtoupper( $settings['interval_type'] ) );
        $sql = $wpdb->prepare( $sql, $limit );

        update_option( 'mis_cron_sql', $sql );
        update_option( 'mis_cron', date('c') );
        update_option( 'mis_cron1', $this->get_settings() );
        d($this->get_settings());die;
    }

}
