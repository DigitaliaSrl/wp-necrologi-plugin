<?php 

// Evita l'accesso diretto al file
if (!defined('ABSPATH')) { exit; }

if (!function_exists('portale_funebre_necrologi_plugin_loader')) {

    function portale_funebre_necrologi_plugin_loader() {

        require_once('plugin_db_manager.php');
        require_once('plugin_base.php');
        require_once('admin_menu_manager.php');
        require_once('plugin_render.php');


    }

    portale_funebre_necrologi_plugin_loader();

}
