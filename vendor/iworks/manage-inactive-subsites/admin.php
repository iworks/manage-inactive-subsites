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

if ( class_exists( 'IworksManageInactiveSubsitesAdmin' ) ) {
    return;
}

require_once( dirname( dirname( __FILE__ ) ) . '/manage-inactive-subsites.php' );

/**
 * Manage Inactive Subsites Admin
 *
 * This class contain network admin part of plugin.
 *
 * @since 1.0.0
 */
class IworksManageInactiveSubsitesAdmin extends IworksManageInactiveSubsites {

    public function __construct() {
        parent::__construct();

        /**
         * actions
         */
        add_action( 'admin_init', array( $this, 'register_setting' ) );
        add_action( 'admin_init', array( $this, 'deactivate_plugin_for_non_network_install' ) );
        add_action( 'admin_init', array( $this, 'add_notice_with_instruction' ) );
        add_action( 'network_admin_menu', array( $this, 'add_network_settings_submenu' ) );
        add_action( 'admin_menu', array( $this, 'add_network_settings_submenu' ) );
    }

    /**
     * Add submenu to Network Settings menu
     *
     * @since 1.0.0
     */
    public function add_network_settings_submenu() {
        $page = add_submenu_page(
            'settings.php',
            __( 'Manage Inactive Subsites', 'manage-inactive-subsites' ),
            __( 'Manage Inactive Subsites', 'manage-inactive-subsites' ),
            'manage_network_options',
            'manage-inactive-subsites-settings',
            array( $this, 'plugin_settings_page' )
        );
        /** This action is documented in wp-admin/admin.php */
        add_action( sprintf( 'load-%s', $page ), array( $this, 'add_contextual_help' ) );
    }

    /**
     * Add Contextual Help to Settings Page.
     *
     * @since 1.0.0
     */
    public function add_contextual_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'       => 'manage-inactive-subsites',
            'title'    => __( 'Manage Inactive Subsites', 'manage-inactive-subsites' ),
            'content'  => $this->get_contextual_help_contant(),
            'This is where I would provide tabbed help to the user on how everything in my admin panel works. Formatted HTML works fine in here too'
        ));
        $screen->set_help_sidebar(
            '<p><strong>' . __( 'For more information:', 'manage-inactive-subsites' ) . '</strong></p>' .
            '<p><a href="https://codex.wordpress.org/Network_Admin_Sites_Screen" target="_blank">' . __( 'Documentation on Site Management', 'manage-inactive-subsites' ) . '</a></p>'.
            '<p><a href="http://premium.wpmudev.org/manuals/wpmu-manual-2/working-with-sites/" target="_blank">' . __( 'Working with WordPress Sites in the Network Admin Dashboard', 'manage-inactive-subsites' ) . '</a></p>'
        );
    }

    /**
     * Content of contextual help.
     *
     * @since 1.0.0
     * @access private
     */
    private function get_contextual_help_contant() {
        $content = '';
        /**
         * Interval Type
         */
        $content .= sprintf( '<h3>%s</h3>', __( 'Interval Type', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'Interval type defines time range to easy management. You can choose day, week, month, quarter or year.', 'manage-inactive-subsites' ) );
        /**
         * Interval Size
         */
        $content .= sprintf( '<h3>%s</h3>', __( 'Interval Size', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'Interval size allow you to choose how many days, weeks, months, quarter or years must pass after we take selected action on blogs which pass this condition.', 'manage-inactive-subsites' ) );

        /**
         * Action
         */
        $content .= sprintf( '<h3>%s</h3>', __( 'Action', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'Action allow to choose what happend with site, when last update is older than you defined before.', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<h4>%s</h4>', __( 'Avaialable options', 'manage-inactive-subsites' ) );
        $content .= '<dl>';

        /**
         * Action: Archive
         */
        $content .= sprintf( '<dd><strong>%s</strong></dd>', __( 'Archive', 'manage-inactive-subsites' ) );
        $content .= '<dd>';
        $content .= sprintf( '<p>%s</p>', __( 'Marks a site as being archived so it\'s not accessible by users.', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'Archived sites can be unarchived.', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'Some people use this option to test the waters before deleting old and unused sites.', 'manage-inactive-subsites' ) );
        $content .= '</dd>';

        /**
         * Action: Deactivate
         */
        $content .= sprintf( '<dd><strong>%s</strong></dd>', __( 'Deactivate', 'manage-inactive-subsites' ) );
        $content .= '<dd>';
        $content .= sprintf( '<p>%s</p>', __( 'Reverses the activation step users go through when they signup for a site.', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'Deactivated sites can be reactivated without much fuss.', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'There\'s really not much use to deactivate on a typical WordPress Multisite.', 'manage-inactive-subsites' ) );
        $content .= '</dd>';

        /**
         * Action: Delete
         */
        $content .= sprintf( '<dd><strong>%s</strong></dd>', __( 'Delete', 'manage-inactive-subsites' ) );
        $content .= '<dd>';
        $content .= sprintf( '<p>%s</p>', __( 'Deletes the site entirely.', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'Use with extreme caution because once a site is deleted it can\'t be recovered.', 'manage-inactive-subsites' ) );
        $content .= sprintf( '<p>%s</p>', __( 'The better option in most circumstances is to archive a site rather than delete it.', 'manage-inactive-subsites' ) );
        $content .= '</dd>';
        $content .= '</dl>';

        /**
         * return
         */
        return $content;
    }

    /**
     * Manage Inactive Subsites settings page
     *
     * This function produce plugin settings page, which allow to setup plugin
     * behaviors.
     *
     * @since 1.0.0
     */
    public function plugin_settings_page() {
        $this->plugin_settings_update();
        $screen = get_current_screen();
        echo '<div class="wrap">';
        printf( '<h1>%s</h1>', __( 'Manage Inactive Subsites', 'manage-inactive-subsites' ) );
        printf( '<p>%s</p>', __( 'You can manage when and what we do with sites with no activity.', 'manage-inactive-subsites' ) );
        printf( '<form method="post" action="%s?page=manage-inactive-subsites-settings">', esc_attr( $screen->parent_file ) );
        wp_nonce_field( 'save-manage-inactive-subsites-configuration', 'manage_inactive_subsites_nonce' );

        settings_fields( 'manage-inactive-subsites-settings' );
        do_settings_sections( 'manage-inactive-subsites-settings' );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    /**
     * Table with plugin options
     *
     * This function produce only table withi plugin options.
     *
     * @since 1.0.0
     * @access private
     */
    private function render_single_field_by_key($key) {
        $settings = $this->get_settings();
        $data = $this->default_settings[$key];
        $option_name = $this->get_option_name( $key );
        /**
         * add field description
         */
        if ( isset( $data['description'] ) ) {
            printf ( '<p>%s</p>', $data['description'] );
        }
        switch ( $data['type'] ) {
        case 'radio':
            printf( '<fieldset><legend class="screen-reader-text"><span>%s</span></legend>', $data['label'] );
            if ( isset( $data['options'] ) && ! empty( $data['options'] ) ) {
                echo '<fieldset>';
                foreach( $data['options'] as $radio_value => $radio_configuration) {
                    printf(
                        '<label title="%s"><input type="radio" name="%s" value="%s" %s> %s</label>',
                        esc_attr( $radio_configuration['label'] ),
                        esc_attr( $option_name ),
                        esc_attr( $radio_value ),
                        ( $settings[$key] == $radio_value )? 'checked="checked"':'',
                        $radio_configuration['label']
                    );
                    /**
                     * add option description
                     */
                    if ( isset( $radio_configuration['description'] ) ) {
                        printf ( '<p class="description">%s</p>', $radio_configuration['description'] );
                    } else {
                        echo '<br />';
                    }
                }
                echo '</fieldset>';
            } else {
                _e( 'Corrupted or empty default data, please contact with plugin support.', 'manage-inactive-subsites');
            }
            break;
        case 'number':
            printf (
                '<input name="%s" type="number" min="1" value="%s" class="small-text">',
                esc_attr( $option_name ),
                esc_attr( $settings[$key] )
            );
            break;
        default:
            _e( 'Wrong option type, please contact with plugin support.', 'manage-inactive-subsites');
        }
        echo '<br />';
    }

    /**
     * Update settings.
     *
     * This function update and sanitize plugin settings. All settings are
     * saved as site_options.
     *
     * @since 1.0.0
     * @access private
     */
    private function plugin_settings_update()
    {
        if ( empty( $_POST ) || ! check_admin_referer( 'save-manage-inactive-subsites-configuration', 'manage_inactive_subsites_nonce' ) ) {
            return;
        }
        foreach ( array_keys( $this->default_settings ) as $key ) {
            $option_name = $this->get_option_name( $key );
            if ( !isset( $_POST[$option_name] ) || empty( $_POST[$option_name] ) ) {
                continue;
            }
            update_site_option( $option_name, $_POST[$option_name] );
        }
        $this->print_notice(
            sprintf( '<b>%s</b>', __( 'Settings saved.', 'manage-inactive-subsites' ) ),
            'updated'
        );
        /**
         * reset cron
         */
        wp_clear_scheduled_hook( 'manage_inactive_subsites_cron_hourly' );
        wp_schedule_event( time(), 'hourly', 'manage_inactive_subsites_cron_hourly' );
    }

    /**
     * Deactivate plugin for non-network installation
     *
     * When user try to activate this plugin from subsite plugn page, then
     * plugin is deactivated with messagem that this usage is not allowed.
     *
     * @since 1.0.0
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
     * Add warning notice after auto-deactivate.
     *
     * @since 1.0.0
     */
    public function add_notice_with_instruction() {
        $settings = $this->get_settings();
        $notice = sprintf(
            __( 'Current configuration: after %d %s inactive sites will be %s.', 'manage-inactive-subsites' ),
            $settings['interval_size'],
            $settings['interval_type'],
            $settings['action']
        );
        $this->print_notice( $notice, 'updated', 'manage-inactive-subsites-current-configuration' );
    }

    /**
     * Add warning notice after auto-deactivate.
     *
     * @since 1.0.0
     */
    public function deactivate_plugin_for_non_network_install_admin_notice() {
        $notice = '';
        if ( is_multisite() ) {
            $notice = __('<b>Manage Inactive Subsites</b> can be only installed as network activation.', 'manage-inactive-subsites');
            if ( current_user_can( 'manage_network_plugins' ) ) {
                $notice .= PHP_EOL.PHP_EOL;
                $notice .= sprintf(
                    __('Please go to <a href="%s">Network Admin Plugins</a> page and activate <b>Manage Inactive Subsites</b>.', 'manage-inactive-subsites'),
                    esc_url( add_query_arg( 'plugin_status', 'inactive', network_admin_url( 'plugins.php' ) ) )
                );
            }
        } else {
            $notice = __('<b>Manage Inactive Subsites</b> can be only installed only in Multisite WordPress installations.', 'manage-inactive-subsites');
        }
        $this->print_notice( $notice, 'notice-info');
    }

    /**
     * Register plugin settings.
     *
     * Function register all plugin settings and add filter to sanitization
     * options data before saving it to database.
     *
     * @since 1.0.0
     */
    public function register_setting() {
        foreach ( $this->default_settings as $key => $data ) {
            $option_name = $this->get_option_name( $key );
            add_settings_section(
                $option_name,
                $data['label'],
                $data['callback'],
                'manage-inactive-subsites-settings'
            );
            register_setting( 'manage-inactive-subsites-settings', $option_name );
            /**
             * add sanitization
             */
            add_filter(
                sprintf( 'pre_update_site_option_%s', $option_name ),
                array( $this, sprintf( 'sanitize_%s', $key ) )
            );
        }
    }

    /**
     * Render interval_type field
     *
     * @since 1.0.0
     *
     */
    public function render_interval_type() {
        $this->render_single_field_by_key( 'interval_type' );
    }

    /**
     * Render interval_size field
     *
     * @since 1.0.0
     *
     */
    public function render_interval_size() {
        $this->render_single_field_by_key( 'interval_size' );
    }

    /**
     * Render action field
     *
     * @since 1.0.0
     *
     */
    public function render_action() {
        $this->render_single_field_by_key( 'action' );
    }

}
