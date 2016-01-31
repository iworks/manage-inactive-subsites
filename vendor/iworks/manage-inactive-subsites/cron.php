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
    }

    /**
     * WP Cron plugin function
     *
     * @since 1.0.0
     *
     * @global wpdb  $wpdb WordPress database abstraction object.
     */
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
        /**
         * select sites
         */
        $sql = 'SELECT blog_id FROM %s ';
        $sql .= 'WHERE last_updated < DATE_SUB( NOW(), INTERVAL %d %s ) ';
        $sql .= $where;
        if ( is_main_site() ) {
            $sql .= sprintf( 'AND blog_id <> %d ', get_current_blog_id() );
        }
        $sql .= 'ORDER BY last_updated DESC ';
        $sql .= 'limit %%d';
        $sql = sprintf( $sql, $wpdb->blogs, $settings['interval_size'], strtoupper( $settings['interval_type'] ) );

        /**
         * get expired sites
         */
        $sql = $wpdb->prepare( $sql, $limit );
        $results = $wpdb->get_results( $sql );

        if ( count( $results ) < 1 ) {
            return;
        }

        /**
         * include required files
         */
        if ( 'archive' == $settings['action'] ) {
            include_once( ABSPATH . WPINC . '/ms-blogs.php' );
        } else {
            include_once( ABSPATH . 'wp-admin/includes/ms.php' );
        }

        /**
         * update
         */
        foreach( $results as $row ) {
            update_option( 'mis_cron_query_'.$row->blog_id, $settings['action'] );
            switch( $settings['action'] ) {
            case 'deactivate':
                wpmu_delete_blog( $row->blog_id, false );
                break;
            case 'archive':
                update_blog_status( $row->blog_id, 'archived', 1 );
                break;
            case 'delete':
                wpmu_delete_blog( $row->blog_id, true );
                break;
            }
        }
    }

}
