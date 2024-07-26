<?php
/*
 Plugin Name:       Logobot Wordpress Plugin
 Description:       Il chatbot evoluto di Logotel
 Version:           0.1.0
 Author:            Logotel Spa
 Author URI:        https://www.logotel.it
 Text Domain:       logobot-wp
 */

function logobot_wp_setup() {

    register_activation_hook(__FILE__, 'logobot_wp_activate');

    //call register settings function
	add_action( 'admin_init', 'logobot_wp_register_plugin_settings' );

    // aggiungo la voce di menu della pagina di configurazione del plugin
    add_action('admin_menu', 'logobot_wp_add_menu_page');

    //call init function
    add_action('init', 'logobot_wp_init_plugin');
}

function logobot_wp_activate() {
    add_option('logobot_wp_is_active', '0');
    add_option('logobot_wp_is_initialized', '0');
    add_option('logobot_wp_bot_name', 'Logobot');
}

function logobot_wp_init_plugin() {
    // inizializzo il plugin solo se non è mai stato fatto in precedenza
    if (get_option('logobot_wp_is_active') == 1 && get_option('logobot_wp_is_initialized') == 0) {
        logobot_wp_init_bot();
    }
}

function logobot_wp_init_bot() {
    //TODO
}

function logobot_wp_add_menu_page() {
    add_menu_page(
        'Impostazioni del Logobot',
        'Logobot',
        'manage_options',
        'logobot-wp',
        'logobot_wp_load_settings_page',
        'dashicons-format-status',
        65
    );
}

function logobot_wp_register_plugin_settings() {
    register_setting( 'logobot_wp_plugin_settings_group', 'logobot_wp_license_key' );
    register_setting( 'logobot_wp_plugin_settings_group', 'logobot_wp_is_active', 'logobot_wp_is_active_sanitizer');
    register_setting( 'logobot_wp_plugin_settings_group', 'logobot_wp_bot_name' );
}

function logobot_wp_is_active_sanitizer($input) {
    return $input == 1 ? 1 : 0;
}

function logobot_wp_load_settings_page() {
    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    ?>
        <div class="wrap">
            <h1>Impostazioni del Logobot</h1>
            <p>Impostazioni di base per consentire l'attivazione del logobot</p>
            <form method="post" action="options.php">
                <?php settings_fields( 'logobot_wp_plugin_settings_group' ); ?>
                <?php do_settings_sections( 'logobot_wp_plugin_settings_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">License Key</th>
                        <td>
                            <input type="text" name="logobot_wp_license_key" value="<?php echo esc_attr( get_option('logobot_wp_license_key') ); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Bot name</th>
                        <td>
                            <input type="text" name="logobot_wp_bot_name" value="<?php echo esc_attr( get_option('logobot_wp_bot_name') ); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Attivo</th>
                        <td>
                            <input type="checkbox" name="logobot_wp_is_active" value="1" <?php checked(1, get_option('logobot_wp_is_active'), true); ?>/>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
}

logobot_wp_setup();