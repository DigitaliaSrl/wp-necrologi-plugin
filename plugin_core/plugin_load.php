<?php

// Evita l'accesso diretto al file
if (!defined('ABSPATH')) { exit; }

if (!function_exists('digitalia_plugin_loader')) {

    function digitalia_plugin_loader() {

        require_once('plugin_db_manager.php');
        require_once('plugin_base.php');
        require_once('admin_menu_manager.php');
        require_once('plugin_render.php');

        if (PORTALE_FUNEBRE_API_INCLUDED) {
            require_once(plugin_dir_path(__FILE__).'../inc/funebreapi/PortaleFunebre_API.php');
        }

    }

    digitalia_plugin_loader();

}
