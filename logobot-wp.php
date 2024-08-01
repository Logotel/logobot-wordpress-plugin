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
define('LOGOBOT_WP_IS_ACTIVE', 'logobot_wp_is_active');
define('LOGOBOT_WP_BOT_NAME', 'logobot_wp_bot_name');
define('LOGOBOT_WP_LICENSE_KEY', 'logobot_wp_license_key');
define('LOGOBOT_WP_CLIENT_URL', 'logobot_wp_client_url');
define('LOGOBOT_WP_PLUGIN_SETTINGS_GROUP', 'logobot_wp_plugin_settings_group');
define('LOGOBOT_WP_DOMAIN', 'logobot-wp');
logobot_wp_start();

function logobot_wp_start() {

    register_activation_hook(__FILE__, 'logobot_wp_activate');
    register_uninstall_hook(__FILE__, 'logobot_wp_uninstall');

    add_action('plugins_loaded', 'logobot_wp_load_textdomain');

    // register plugin settings
	add_action( 'admin_init', 'logobot_wp_register_plugin_settings' );

    // add admin menu item to settings page
    add_action('admin_menu', 'logobot_wp_add_menu_page');

    
    if (get_option(LOGOBOT_WP_IS_ACTIVE,0) == '1') {
        add_action( 'enqueue_block_editor_assets', 'logobot_wp_enqueue_block_editor_assets' );
        add_action('init', 'logobot_wp_init_plugin');
    }
}

function logobot_wp_load_textdomain() {
    load_plugin_textdomain(
        LOGOBOT_WP_DOMAIN, 
        false, 
        basename(dirname(__FILE__)) . '/languages/'
    );
}

function logobot_wp_activate() {
    add_option(LOGOBOT_WP_IS_ACTIVE, '0');
    add_option(LOGOBOT_WP_BOT_NAME, 'Logobot');
}

function logobot_wp_uninstall() {
    delete_option(LOGOBOT_WP_IS_ACTIVE);
    delete_option(LOGOBOT_WP_BOT_NAME);
    delete_option(LOGOBOT_WP_LICENSE_KEY);
    delete_option(LOGOBOT_WP_CLIENT_URL);
}

function logobot_wp_init_plugin() {
    // start php session if not yet started
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
    $license_key = get_option( LOGOBOT_WP_LICENSE_KEY);
    $client_url = get_option( LOGOBOT_WP_CLIENT_URL);
    $bot_name = get_option( LOGOBOT_WP_BOT_NAME);
    $logobotWrapperId = isset($attributes['wrapperId']) ? $attributes['wrapperId'] : 'logobot-wrapper';
    $jwt = LogobotHelper::generateJWT($private_key_path,$license_key, $sessionId);

    if (empty($jwt)) {
        return;
    }
    
    ob_start();
    ?>
        <div class="logobot-wrapper" id="<?php echo esc_attr($logobotWrapperId); ?>" ></div>
        <script type="module" crossorigin src="<?php echo esc_attr($client_url); ?>/chatbot.js" onload="initLogobot()"></script>
        <script>
            function initLogobot() {

                const config = {
                    targetDiv: <?php echo wp_json_encode($logobotWrapperId); ?>,
                    userJwt: <?php echo wp_json_encode($jwt); ?>,
                    licenseKey: <?php echo wp_json_encode($license_key); ?>,
                    bot: <?php echo wp_json_encode($bot_name); ?>,
                    defaultOpen: true,
                    name: '<?php echo esc_js(__('Visitatore', LOGOBOT_WP_DOMAIN)); ?>',
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
    $output = ob_get_clean();
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
        __('Logobot settings', LOGOBOT_WP_DOMAIN),
        'Logobot',
        'manage_options',
        'logobot-wp',
        'logobot_wp_load_settings_page',
        'dashicons-format-status',
        65
    );
}

function logobot_wp_register_plugin_settings() {
    register_setting( LOGOBOT_WP_PLUGIN_SETTINGS_GROUP, LOGOBOT_WP_LICENSE_KEY );
    register_setting( LOGOBOT_WP_PLUGIN_SETTINGS_GROUP, LOGOBOT_WP_IS_ACTIVE, 'logobot_wp_is_active_sanitizer');
    register_setting( LOGOBOT_WP_PLUGIN_SETTINGS_GROUP, LOGOBOT_WP_BOT_NAME, 'logobot_wp_bot_name_sanitizer' );
    register_setting( LOGOBOT_WP_PLUGIN_SETTINGS_GROUP, LOGOBOT_WP_CLIENT_URL );
}

function logobot_wp_is_active_sanitizer($input) {
    return $input == 1 ? 1 : 0;
}

function logobot_wp_bot_name_sanitizer($input) {
    return $input ? $input : 'Logobot';
}

function logobot_wp_load_settings_page() {
    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', LOGOBOT_WP_DOMAIN ) );
	}
    ?>
        <div class="wrap">
            <h1><?php _e('Logobot settings', LOGOBOT_WP_DOMAIN); ?></h1>
            <p><?php _e('Configurations to enable Logobot activation', LOGOBOT_WP_DOMAIN); ?></p>
            <form method="post" action="options.php">
                <?php settings_fields( LOGOBOT_WP_PLUGIN_SETTINGS_GROUP ); ?>
                <?php do_settings_sections( LOGOBOT_WP_PLUGIN_SETTINGS_GROUP ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Private Key', LOGOBOT_WP_DOMAIN); ?>*</th>
                        <td>
                            <p>
                                <?php 
                                    _e( 
                                        '<em>Load your valid <strong>privatekey.pem</strong> file into wp-content/uploads/logobot-wp folder</em>', 
                                        LOGOBOT_WP_DOMAIN
                                    );
                                ?>
                            </p>
                            <p>
                                <?php
                                    if (!file_exists(logobot_wp_get_upload_dir(). 'privatekey.pem')) {
                                        echo '<strong style="color:red;">'. __('Private Key non trovata', LOGOBOT_WP_DOMAIN) . '</strong>';
                                    } else {
                                        echo '<strong style="color:green;">' . __('Private Key presente', LOGOBOT_WP_DOMAIN) . '</strong>';
                                    }
                                ?>
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('License', LOGOBOT_WP_DOMAIN); ?>*</th>
                        <td>
                            <input required type="text" name="<?php echo esc_attr(LOGOBOT_WP_LICENSE_KEY); ?>" value="<?php echo esc_attr( get_option(LOGOBOT_WP_LICENSE_KEY) ); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Environment', LOGOBOT_WP_DOMAIN); ?>*</th>
                        <td>
                            <select name="<?php echo esc_attr(LOGOBOT_WP_CLIENT_URL); ?>" required>
                                <option <?php if (get_option(LOGOBOT_WP_CLIENT_URL) == 'https://client-staging.chatbot.logotel.cloud') {echo "selected='selected'";} ?> value="https://client-staging.chatbot.logotel.cloud">Staging</option>
                                <option <?php if (get_option(LOGOBOT_WP_CLIENT_URL) == 'https://client.chatbot.logotel.cloud') {echo "selected='selected'";} ?> value="https://client.chatbot.logotel.cloud">Production</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Bot name', LOGOBOT_WP_DOMAIN); ?></th>
                        <td>
                            <input type="text" name="<?php echo esc_attr(LOGOBOT_WP_BOT_NAME); ?>" value="<?php echo esc_attr( get_option(LOGOBOT_WP_BOT_NAME) ); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Enable', LOGOBOT_WP_DOMAIN); ?></th>
                        <td>
                            <input type="checkbox" name="<?php echo esc_attr(LOGOBOT_WP_IS_ACTIVE); ?>" value="1" <?php checked(1, get_option(LOGOBOT_WP_IS_ACTIVE), true); ?>/>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
}