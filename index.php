<?php
/*
Plugin Name: Gestione Necrologi
Plugin URI: http://www.portalefunebre.com
Description: Gestione dei necrologi sul tuo sito.
Version: 1.0
Author: Digitalia Srl
Author URI: https://digitalia.srl
License: GPL2
*/

if (!defined('ABSPATH')) { exit; }

define('PORTALE_FUNEBRE_API_INCLUDED', true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!PORTALE_FUNEBRE_API_INCLUDED) {
    
    $fakeApiPath = 'C:\www\FunebreIntegrator';

    require_once($fakeApiPath.'\PortaleFunebre_API.php');
    require_once($fakeApiPath.'\config.php');

}

require_once('plugin_core/plugin_load.php');

add_action( 'gestione_necrologi_head_left', function () {
    $link = PortaleFunebre_API::GetLoginPath();
    echo '<p class="titolo-portale">Accedi al portale</p><a href="'.$link.'" target="__blank" class="button">Accedi ora</a>';

});

add_action('template_redirect', function () {

    $url_string = (trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

    $url_routes = explode('/',$url_string);
    // Controlla se la route è "portalefunebre"
    if (isset($url_routes[1]) &&  $url_routes[1] === 'portalefunebre') {
        // Reindirizza alla URL desiderata
        wp_redirect('https://www.portalefunebre.com/login');
        exit;
    }

}, 1);


class GestioneNecrologi extends Digitalia\PluginBase {
    static $inInst=null;
    private static $IMPOSTAZIONI=null;
    function __construct() {
        parent::__construct('Gestione Necrologi',__FILE__);

        self::$IMPOSTAZIONI = $this->get_opzione('impostazioni');

        $this->create_shortcode('gestione_necrologi_lista');
        $this->create_shortcode('gestione_necrologi_singolo');
        $this->create_shortcode('gestione_necrologi_slider');

        /*
        
            'CLIENT_ID'  => 'eUQxZ0hUUEdOTUtHWE5wRksrYkU0QT09',
            'API_KEY'    => '108409c1c3797f0570ee07d242f86c49a0a9be32',
        */

        if (!isset(self::$IMPOSTAZIONI['api_key']) || !self::$IMPOSTAZIONI['api_key']|| !self::$IMPOSTAZIONI['client_id']) { return; }
        
        PortaleFunebre_API::Config([
            'END_POINT'  => 'https://portalefunebre.com',
            'CLIENT_ID'  => self::$IMPOSTAZIONI['client_id'],
            'API_KEY'    => self::$IMPOSTAZIONI['api_key'],
        ]);

        self::$inInst = $this;

        $slug_singolo = (isset(self::$IMPOSTAZIONI['slug_singolo'])) ? self::$IMPOSTAZIONI['slug_singolo'] : 'necrologio';

        add_action('init', function() use ($slug_singolo) {
            add_rewrite_rule('^'.$slug_singolo.'/([^/]+)?', 'index.php?necro_slug=$matches[1]', 'top');
            flush_rewrite_rules(  );
        });
        add_filter('query_vars', function($vars) {
            $vars[] = 'necro_slug';
            return $vars;
        });
        add_action('template_redirect', function() use ($slug_singolo)  {
            
            $slug = get_query_var('necro_slug');
            if (!$slug) { return; }

            $post = get_page_by_path($slug_singolo, OBJECT, 'page'); // oppure 'post' se cerchi tra i post

            if ($post) {
                setup_postdata($post);
                // imposta globali per far credere a WP che siamo su quella pagina
                global $wp_query;
                $wp_query->post    = $post;
                $wp_query->posts   = [$post];
                $wp_query->is_page = true;
                $wp_query->is_singular = true;
                $wp_query->is_home = false;
                $wp_query->is_404 = false;

                $template = get_single_template();

                if (!$template) { $template = get_page_template(); }

                if ($template) {
                    include(get_single_template());
                    exit;
                }
                
            } else {
                // fallback se non trova nulla
                wp_die('Pagina non trovata', 'Errore 404', ['response' => 404]);
            }
        });

    }

    static function GetImpostazioni() {
        return self::$IMPOSTAZIONI;
    }

    function create_menu_pages() {

        return [
            'Necrologi',
            'Cordogli',
            'Impostazioni'
        ];

    }

}

GestioneNecrologi::add_ajax_call('get_necrologio_singolo', function ($var) {
    if (!GestioneNecrologi::$inInst) { return; }
    $slug = $var['slug'];
    $api = new PortaleFunebre_API();
    return $api->TrovaNecrologioSingolo($slug);
});
GestioneNecrologi::add_ajax_call('get_lista_necrologi', function () {
    if (!GestioneNecrologi::$inInst) { return; }
    $api = new PortaleFunebre_API();
    return $api->TrovaTuttiNecrologi();
});
GestioneNecrologi::add_ajax_call('get_anteprima_necrologi', function () {
    if (!GestioneNecrologi::$inInst) { return; }
    $api = new PortaleFunebre_API();
    return $api->TrovaTuttiNecrologi(10);
});
GestioneNecrologi::add_ajax_call('invia_cordoglio_api', function ($var) {
    if (!GestioneNecrologi::$inInst) { return; }
    $api = new PortaleFunebre_API();
    return $api->InviaCordoglio($var);
});

new GestioneNecrologi;


$necro_settings_array = GestioneNecrologi::GetImpostazioni()->toArray();
unset($necro_settings_array['api_key']);
unset($necro_settings_array['client_id']);

Digitalia\PluginBase::add_js_variables([
    'necro_settings' => $necro_settings_array,
    'necro_img_url'  => PortaleFunebre_API::GetImgUrl(''),
    'necrologio_url' => get_site_url().'/'.GestioneNecrologi::GetImpostazioni()['slug_singolo']
]);

function trova_num_cols_necro_lista() {
    $num_cols = GestioneNecrologi::GetImpostazioni()['num_cols'];
    $out = [];
    for ($i=0; $i<$num_cols; $i++) { array_push($out, '1fr'); }
    return implode(' ',$out);
}

Digitalia\PluginBase::add_css_variables([
    'necro-colore-box'    => (GestioneNecrologi::$inInst) ? GestioneNecrologi::GetImpostazioni()['colore_box'] : '',
    'necro-colore-page'   => (GestioneNecrologi::$inInst) ? GestioneNecrologi::GetImpostazioni()['colore_page'] : '#f9eee4',
    'necro-colore-testo'  => (GestioneNecrologi::$inInst) ? GestioneNecrologi::GetImpostazioni()['colore_testo'] : '#000',
    //'necro-num-cols'      => (GestioneNecrologi::$inInst) ? trova_num_cols_necro_lista() : '1fr 1fr 1fr 1fr 1fr',
]);
