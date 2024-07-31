<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('logobot_wp_is_active');
delete_option('logobot_wp_is_initialized');
delete_option('logobot_wp_bot_name');
delete_option('logobot_wp_license_key');
delete_option('logobot_wp_client_url');
delete_option('logobot_wp_private_key');