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
 * TASKS TO DONE:
 *
 * Factor in how to manage expiring of sites on first configuration, or when
 * changing the settings.
 *
 * All this will be based off the last_updated timestamp in the blogs table,
 * and remember this could be potentially running on a large multisite install
 * like edublogs.org with millions of blogs! Scalable code is important here.
 *
 */

/**
 * Manage Inactive Subsites
 *
 * This abstract class contains common settings and sanitization methods for
 * two nested class: Admin and Cron.
 *
 * @since 1.0.0
 */
abstract class IworksManageInactiveSubsites {

    /**
     * Plugin base name
     *
     * @since 1.0.0
     * @access protected
     * @var string $base plugin base name
     */
    protected $base_name;

    /**
     * Plugin version.
     *
     * @since 1.0.0
     * @access protected
     * @var string $version plugin version
     */
    protected $default_settings = array();

    /**
     * Plugin version.
     *
     * @since 1.0.0
     * @access protected
     * @var string $version plugin version
     */
    protected $version = 'trunk';

    public function __construct() {
        /**
         * static settings
         */
        $this->plugin_basename = plugin_basename( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/manage-inactive-subsites.php';

        /**
         * default settings
         */
        $this->default_settings = array(
            'interval_type' => array(
                'type'        => 'radio',
                'label'       => __( 'Interval Type', 'manage-inactive-subsites' ),
                'value'       => 'week',
                'callback'    => array( $this, 'render_interval_type' ),
                'description' => __( 'You can choose what time range you want to use.', 'manage-inactive-subsites' ),
                'options'     => array(
                    'day'   => array(
                        'label' => __( 'Day', 'manage-inactive-subsites' ),
                    ),
                    'week'  => array(
                        'label' => __( 'Week', 'manage-inactive-subsites' ),
                    ),
                    'month' => array(
                        'label' => __( 'Month', 'manage-inactive-subsites' ),
                    ),
                    'quarter'  => array(
                        'label' => __( 'Quarter', 'manage-inactive-subsites' ),
                    ),
                    'year'  => array(
                        'label' => __( 'Year', 'manage-inactive-subsites' ),
                    ),
                ),
            ),
            'interval_size' => array(
                'type'        => 'number',
                'label'       => __( 'Interval Size', 'manage-inactive-subsites' ),
                'value'       => 6,
                'callback'    => array( $this, 'render_interval_size' ),
            ),
            'action'        => array(
                'type'        => 'radio',
                'label'       => __( 'Action', 'manage-inactive-subsites' ),
                'value'       => 'archive',
                'callback'    => array( $this, 'render_action' ),
                'description' => __( 'Select action which should be taken, when last site update is older then you selected above', 'manage-inactive-subsites' ),
                'options'           => array(
                    'archive'    => array(
                        'label'       => __( 'Archive', 'manage-inactive-subsites' ),
                        'description' => __( 'Archived site will be unavailable with message "This site has been archived or suspended.".', 'manage-inactive-subsites' ),
                    ),
                    'deactivate' => array(
                        'label'       => __( 'Deactivate', 'manage-inactive-subsites' ),
                        'description' => __( '', 'manage-inactive-subsites' ),
                        'description' => __( 'Deactivated site will be unavailable with message "This site has been archived or suspended.".', 'manage-inactive-subsites' ),
                    ),
                    'delete'     => array(
                        'label'       => __( 'Delete', 'manage-inactive-subsites' ),
                        'description' => __( 'Use with extreme caution because once a site is deleted it can\'t be recovered.', 'manage-inactive-subsites' ),
                    ),
                ),
            ),
        );

        /**
         * actions
         */
        add_action( 'wp_ajax_mis_hide_admin_notification', array( $this, 'save_hide_admin_notification' ) );
    }

    public function save_hide_admin_notification() {
        if (
            isset( $_REQUEST['uid'] )
            && isset( $_REQUEST['nonce'] )
            && wp_verify_nonce(
                $_REQUEST['nonce'],
                sprintf( '%s_uid_%d', 'manage-inactive-subsites-current-configuration', $_REQUEST['uid'] )
            )
        ) {
            update_user_option( $_REQUEST['uid'], 'mis_hide_admin_notification', 1 );
            echo 'ok';
        }
        die;
    }

    public function admin_enqueue_scripts() {
        wp_enqueue_script(
            'manage-inactive-subsites-admin',
            plugins_url( '/scripts/manage-inactive-subsites-admin.js', $this->plugin_basename ),
            array( 'jquery' ),
            $this->version,
            true
        );
    }

    /**
     * Helper to show notices.
     *
     * @since 1.0.0
     * @access protected
     *
     * @param string $notice Notice to display.
     * @param string $class CSS class to add.
     */
    protected function print_notice( $notice, $class = '', $nonce_action = false ) {
        if ( empty( $notice ) ) {
            return;
        }
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }
        if ( get_user_option( 'mis_hide_admin_notification', get_current_user_id() ) ) {
            return;
        }
        $nonce_field = '';
        if ( ! empty( $nonce_action ) ) {
            $nonce_field = wp_nonce_field(
                sprintf( '%s_uid_%d', 'manage-inactive-subsites-current-configuration', get_current_user_id() ),
                'manage_inactive_subsites_nonce'
            );
            $class .= ' is-dismissible';
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        }
        printf(
            '<div class="notice %s manage-inactive-subsites-notice">%s%s</div>',
            ! empty( $class )? esc_attr( $class ):'',
            wpautop( $notice ),
            $nonce_field
        );
    }

    /**
     * Get option name.
     *
     * @since 1.0.0
     * @access protected
     *
     * @param string $key internal option key
     * @return string option name.
     */
    protected function get_option_name( $key ) {
        $key = preg_replace( '/[^a-z^0-9]\+/', '_', $key );
        return sprintf( 'manage_inactive_subsites_%s', $key );
    }

    /**
     * Get current settings.
     *
     * Function get current settings and validate values.
     *
     * @since 1.0.0
     *
     * @return array Associative array with options keys and values.
     */
    public function get_settings() {
        $settings = array();
        foreach ( $this->default_settings as $key => $data ) {
            $option_name = $this->get_option_name( $key );
            $v = get_site_option( $option_name );
            if ( empty( $v ) ) {
                $v = $this->get_default_setting_by_key( $key );
            }
            /**
             * sanitize
             */
            switch( $key ) {
            case 'interval_type':
                $settings[ $key ] = $this->sanitize_interval_type( $v );
                break;
            case 'interval_size':
                $settings[ $key ] = $this->sanitize_interval_size( $v );
                break;
            case 'action':
                $settings[ $key ] = $this->sanitize_action( $v );
                break;
            default:
                $settings[$key] = $v;
            }
        }
        /**
         * Sanitize
         */
        return $settings;
    }

    /**
     * Sanitize radio types.
     *
     * @since 1.0.0
     * @access private
     *
     * @param string $key option key
     * @param mixed $value value to sanitize
     *
     * @return string interval type value
     */
    private function sanitize_radio_by_key( $key, $value = null ) {
        if (
            ! empty( $value )
            && is_string( $value )
            && isset( $this->default_settings[ $key ] )
            && isset( $this->default_settings[ $key ]['options'] )
            && is_array( $this->default_settings[ $key ]['options'] )
        ) {
            $value = strtolower( $value );
            if ( array_key_exists( $value, $this->default_settings[ $key ]['options'] ) ) {
                return $value;
            }
        }
        return $this->get_default_setting_by_key( $key );
    }

    /**
     * Sanitize interval type.
     *
     * @since 1.0.0
     *
     * @param mixed $value value to sanitize
     * @return string interval type value
     */
    public function sanitize_interval_type( $value ) {
        return $this->sanitize_radio_by_key( 'interval_type', $value );
    }

    /**
     * Sanitize interval size.
     *
     * @since 1.0.0
     *
     * @param mixed $value value to sanitize
     * @return integer interval size value
     */
    public function sanitize_interval_size( $value ) {
        $value = intval( $value );
        if ( $value > 0 ) {
            return $value;
        }
        return $this->get_default_setting_by_key( 'interval_size' );
    }

    /**
     * Sanitize action.
     *
     * @since 1.0.0
     *
     * @param mixed $value value to sanitize
     * @return string action value
     */
    public function sanitize_action( $value ) {
        return $this->sanitize_radio_by_key( 'action', $value );
    }

    /**
     * Get default value.
     *
     * Function returns default value for option key.
     *
     * @since 1.0.0
     * @access protected
     *
     * @param string $key option key
     *
     * @return mixed return defult value or null if default value is not
     * defined
     */
    protected function get_default_setting_by_key( $key ) {
        if (
            isset( $this->default_settings[ $key ] )
            && isset( $this->default_settings[ $key ]['value'] )
        ) {
            return $this->default_settings[ $key ]['value'];
        }
        return null;
    }

}
