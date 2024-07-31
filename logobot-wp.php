<?php
/*
 Plugin Name:       Logobot Wordpress Plugin
 Description:       Il chatbot evoluto di Logotel
 Version:           0.1.0
 Author:            Logotel Spa
 Author URI:        https://www.logotel.it
 Text Domain:       logobot-wp
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once __DIR__ . '/vendor/autoload.php';
use Logotel\LogobotWp\Helper\LogobotHelper;
logobot_wp_start();

function logobot_wp_start() {

    register_activation_hook(__FILE__, 'logobot_wp_activate');

    // register plugin settings
	add_action( 'admin_init', 'logobot_wp_register_plugin_settings' );

    // add admin menu item to settings page
    add_action('admin_menu', 'logobot_wp_add_menu_page');

    
    if (get_option('logobot_wp_is_active',0) == '1') {
        add_action( 'enqueue_block_editor_assets', 'logobot_wp_enqueue_block_editor_assets' );
        add_action('init', 'logobot_wp_init_plugin');
    }
}

function logobot_wp_activate() {
    add_option('logobot_wp_is_active', '0');
    add_option('logobot_wp_bot_name', 'Logobot');
}

function logobot_wp_init_plugin() {
    if (!session_id()) {
        session_start();
    }
    logobot_wp_register_block();
}

function logobot_wp_get_upload_dir() {
    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['basedir'];
    return $upload_path . '/logobot-wp/';
}

function logobot_wp_render_block($attributes) {
    $sessionId = session_id();
    $private_key_path = logobot_wp_get_upload_dir() . 'privatekey.pem';
    $license_key = get_option( 'logobot_wp_license_key');
    $client_url = get_option( 'logobot_wp_client_url');
    $bot_name = get_option( 'logobot_wp_bot_name');
    $logobotWrapperId = isset($attributes['wrapperId']) ? $attributes['wrapperId'] : 'logobot-wrapper';
    $jwt = LogobotHelper::generateJWT($private_key_path,$license_key, $sessionId);

    if (empty($jwt)) {
        //return;
    }
    
    ob_start(); // Avvia la cattura dell’output
    ?>
        <div class="logobot-wrapper" id="<?php echo esc_attr($logobotWrapperId); ?>" ></div>
        <script type="module" crossorigin src="<?php echo esc_attr($client_url); ?>/chatbot.js" onload="initLogobot()"></script>
        <script>
            function initLogobot() {

                // cancella il threadkey in localStorage al caricamento della pagina
                localStorage.setItem('logobot_threadkey', '');

                const config = {
                    targetDiv: <?php echo wp_json_encode($logobotWrapperId); ?>,
                    userJwt: <?php echo wp_json_encode($jwt); ?>,
                    licenseKey: <?php echo wp_json_encode($license_key); ?>,
                    bot: <?php echo wp_json_encode($bot_name); ?>,
                    defaultOpen: true,
                    name: 'Visitatore',
                    themeConfig: {
                        direction: 'ltr',
                        paletteMode: 'light',
                        colorPreset: '#000000',
                        contrast: 'low',
                        responsiveFontSizes: true
                    }
                };
                Logobot.init(config);
            }
        </script>
    <?php
    $output = ob_get_clean(); // Recupera l'output e pulisce il buffer
    return $output;
}

function logobot_wp_register_block() {
    register_block_type( 'logobot-wp/logobot-block', array(
        'render_callback' => 'logobot_wp_render_block',
    ) );
}

function logobot_wp_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'logobot-block-editor', 
        plugins_url( 'logobot-block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' )
    );
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
    register_setting( 'logobot_wp_plugin_settings_group', 'logobot_wp_bot_name', 'logobot_wp_bot_name_sanitizer' );
    register_setting( 'logobot_wp_plugin_settings_group', 'logobot_wp_client_url' );
}

function logobot_wp_is_active_sanitizer($input) {
    return $input == 1 ? 1 : 0;
}

function logobot_wp_bot_name_sanitizer($input) {
    return $input ? $input : 'Logobot';
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
                        <th scope="row">Private Key*</th>
                        <td>
                            <p><em>Load your valid <strong>privatekey.pem</strong> file into wp-content/uploads/logobot-wp folder</em></p>
                            <p>
                                <?php
                                    $upload_path = wp_upload_dir();
                                    if (!file_exists(logobot_wp_get_upload_dir(). 'privatekey.pem')) {
                                        echo '<strong style="color:red;">Private Key non trovato</strong>';
                                    } else {
                                        echo '<strong style="color:green;">Private Key presente</strong>';
                                    }
                                ?>
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">License*</th>
                        <td>
                            <input required type="text" name="logobot_wp_license_key" value="<?php echo esc_attr( get_option('logobot_wp_license_key') ); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Logobot Client*</th>
                        <td>
                            <select name="logobot_wp_client_url" required>
                                <option <?php if (get_option('logobot_wp_client_url') == 'https://client-staging.chatbot.logotel.cloud') {echo "selected='selected'";} ?> value="https://client-staging.chatbot.logotel.cloud">Staging</option>
                                <option <?php if (get_option('logobot_wp_client_url') == 'https://client.chatbot.logotel.cloud') {echo "selected='selected'";} ?> value="https://client.chatbot.logotel.cloud">Production</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Bot name</th>
                        <td>
                            <input type="text" name="logobot_wp_bot_name" value="<?php echo esc_attr( get_option('logobot_wp_bot_name') ); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Attiva</th>
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