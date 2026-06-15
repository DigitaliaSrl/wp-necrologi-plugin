<?php /*
Plugin Name: Portale Funebre Necrologi
Plugin URI: http://www.portalefunebre.com
Description: Gestione dei necrologi sul tuo sito.
Version: 1.0
Author: Digitalia Srl
Author URI: https://digitalia.srl
License: GPL2
*/

if (!defined('ABSPATH')) { exit; }

define('PORTALE_FUNEBRE_NECROLOGI_API_INCLUDED', true);

require_once('plugin_core/plugin_load.php');

if (!class_exists('PortaleFunebreNecrologi_API')) {
    require_once plugin_dir_path(__FILE__) . 'inc/funebreapi/PortaleFunebreNecrologi_API.php';
}

add_action( 'portale_funebre_necrologi_head_left', function () {
    $link = PortaleFunebreNecrologi_API::GetLoginPath();
    echo '<p class="titolo-portale">Accedi al portale</p><a href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer" class="button">Accedi ora</a>';

});

add_action('template_redirect', function () {

    if (isset($_SERVER['REQUEST_URI'])) {

        $request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        $url_string  = (trim(wp_parse_url($request_uri, PHP_URL_PATH), '/'));

        $url_routes = explode('/',$url_string);
        // Controlla se la route è "portalefunebre"
        if (isset($url_routes[1]) &&  $url_routes[1] === 'ofadmin') {
            // Reindirizza alla URL desiderata
            wp_safe_redirect('https://www.portalefunebre.com/login');
            exit;
        }

    }

}, 1);


class PortaleFunebreNecrologi extends PortaleFunebreNecrologi\PluginBase {
    static $inInst=null;
    private static $IMPOSTAZIONI=null;
    function __construct() {
        parent::__construct('Portale Funebre Necrologi', __FILE__, 'dashicons-admin-home', ['gestione-necrologi']);

        self::$IMPOSTAZIONI = $this->get_opzione('impostazioni');

        $this->create_shortcode('gestione_necrologi_lista');
        $this->create_shortcode('gestione_necrologi_singolo');
        $this->create_shortcode('gestione_necrologi_slider');

        if (!isset(self::$IMPOSTAZIONI['api_key']) || !self::$IMPOSTAZIONI['api_key']|| !self::$IMPOSTAZIONI['client_id']) { return; }
        
        PortaleFunebreNecrologi_API::Config([
            'END_POINT' => 'https://portalefunebre.com',
            'CLIENT_ID' => self::$IMPOSTAZIONI['client_id'],
            'API_KEY'   => self::$IMPOSTAZIONI['api_key'],
        ]);

        self::$inInst = $this;

        $slug_singolo = (isset(self::$IMPOSTAZIONI['slug_singolo'])) ? self::$IMPOSTAZIONI['slug_singolo'] : 'necrologio';

        add_action('init', function() use ($slug_singolo) {
            add_rewrite_rule(
                '^pf-share/([^/]+)/?$',
                'index.php?pf_share_slug=$matches[1]',
                'top'
            );
            add_rewrite_rule('^'.$slug_singolo.'/([^/]+)?', 'index.php?necro_slug=$matches[1]', 'top');
        });
        add_filter('query_vars', function($vars) {
            $vars[] = 'pf_share_slug';
            $vars[] = 'necro_slug';
            return $vars;
        });

        if (PortaleFunebreNecrologi::IsConfigurato()) {
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
                    $wp_query->is_404  = false;

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
            add_action('template_redirect', function () {

                $slug = get_query_var('pf_share_slug');
                if (!$slug) return;

                remove_all_actions('wpseo_head');

                $api  = new PortaleFunebreNecrologi_API();
                $data = $api->TrovaNecrologioSingolo($slug);

                if (!$data || (empty($data))) {
                    wp_die('Not found');
                }

                $agenzia = (isset($data->agenzia)) ? $data->agenzia : (object)['ragione_sociale' => 'Onoranze Funebri', 'partita_iva'=> ''];

                $thumb = $data->thumbnail;
                $title = esc_attr($data->nome_defunto).' - '.$agenzia->ragione_sociale;
                $desc  = wp_strip_all_tags($data->testo);
                $img   = PortaleFunebreNecrologi_API::GetImgUrl('').$thumb;

                if (!$desc) {
                    $desc = 'E\' mancato/a all\'affetto dei suoi cari '.$data->nome_defunto;
                }
                $url      = home_url('/pf-share/'.$slug);
                $real_url = esc_url(home_url('/'.self::$IMPOSTAZIONI['slug_singolo'].'/'.$slug));

                ?>
                <!DOCTYPE html>
                <html>
                    <head>

                        <title><?php echo esc_attr($title); ?></title>

                        <!-- Open Graph / Facebook -->
                        <meta property="og:title" content="<?php echo esc_attr($title); ?>" />
                        <meta property="og:description" content="<?php echo esc_attr($desc); ?>" />
                        <meta property="og:image" content="<?php echo esc_url($img); ?>" />
                        <meta property="og:image:secure_url" content="<?php echo esc_url($img); ?>" />
                        <meta property="og:image:width" content="640" />
                        <meta property="og:image:height" content="780" />
                        <meta property="og:type"  content="article" />
                        <meta property="og:url" content="<?php echo esc_url($url); ?>" />

                        <!-- X (Twitter) -->
                        <meta property="twitter:card" content="<?php echo esc_attr($title); ?>" />
                        <meta property="twitter:url" content="<?php echo esc_url($real_url); ?>" />
                        <meta property="twitter:title" content="<?php echo esc_attr($title); ?>" />
                        <meta property="twitter:description" content="<?php echo esc_attr($desc); ?>" />
                        <meta property="twitter:image" content="<?php echo esc_url($img); ?>" />

                        <meta name="msapplication-TileImage" content="<?php echo esc_url($img); ?>">
                        <meta name="robots" content="noindex, nofollow">
                        <meta http-equiv="refresh" content="0;url=<?php echo esc_url($real_url); ?>">


                    </head>
                    <body style="opacity: 0">
                        <h1><?php echo esc_attr($title); ?></h1>
                        <p><?php echo esc_attr($desc); ?></p>
                        <img src="<?php echo esc_url($img); ?>"/>
                        <p><a href="<?php echo esc_url($real_url); ?>">Vai al necrologio</a></p>
                    </body>
                </html>
                <?php
                exit;
            });
        }
            

    }

    static function GetImpostazioni() {
        return self::$IMPOSTAZIONI;
    }

    static function IsConfigurato() {
        $set = self::GetImpostazioni();
        if (!$set) { return false; }
        if (!isset($set['api_key']) || !isset($set['client_id'])) { return false; } 
        return $set['api_key'] && $set['client_id'];
    }

    function create_menu_pages() {

        if (self::IsConfigurato()) {
            return [
                'Necrologi',
                'Cordogli',
                'Impostazioni'
            ];
        }

        return ['Impostazioni'];

    }

}

PortaleFunebreNecrologi::add_ajax_call('get_necrologio_singolo', function ($var) {
    if (!PortaleFunebreNecrologi::$inInst) { return; }
    $slug = isset($var['slug']) ? sanitize_title($var['slug']) : '';
    $api = new PortaleFunebreNecrologi_API();
    return $api->TrovaNecrologioSingolo($slug);
});
PortaleFunebreNecrologi::add_ajax_call('get_lista_necrologi', function () {
    if (!PortaleFunebreNecrologi::$inInst) { return; }
    $api = new PortaleFunebreNecrologi_API();
    return $api->TrovaTuttiNecrologi();
});
PortaleFunebreNecrologi::add_ajax_call('get_anteprima_necrologi', function () {
    if (!PortaleFunebreNecrologi::$inInst) { return; }
    $api = new PortaleFunebreNecrologi_API();
    return $api->TrovaTuttiNecrologi(10);
});
PortaleFunebreNecrologi::add_ajax_call('invia_cordoglio_api', function ($var) {
    if (!PortaleFunebreNecrologi::$inInst) { return; }
    $api = new PortaleFunebreNecrologi_API();
    return $api->InviaCordoglio($var);
});

new PortaleFunebreNecrologi;


$necro_settings_array = PortaleFunebreNecrologi::GetImpostazioni()->toArray();
unset($necro_settings_array['api_key']);
unset($necro_settings_array['client_id']);

PortaleFunebreNecrologi\PluginBase::add_js_variables([
    'necro_settings' => $necro_settings_array,
    'necro_img_url'  => PortaleFunebreNecrologi_API::GetImgUrl(''),
    'necrologio_url' => get_site_url().'/'.PortaleFunebreNecrologi::GetImpostazioni()['slug_singolo']
]);

function portale_funebre_necrologi_num_cols() {
    $num_cols = PortaleFunebreNecrologi::GetImpostazioni()['num_cols'];
    $out = [];
    for ($i=0; $i<$num_cols; $i++) { array_push($out, '1fr'); }
    return implode(' ',$out);
}

PortaleFunebreNecrologi\PluginBase::add_css_variables([
    'necro-colore-box'    => (PortaleFunebreNecrologi::$inInst) ? PortaleFunebreNecrologi::GetImpostazioni()['colore_box'] : '',
    'necro-colore-page'   => (PortaleFunebreNecrologi::$inInst) ? PortaleFunebreNecrologi::GetImpostazioni()['colore_page'] : '#f9eee4',
    'necro-colore-testo'  => (PortaleFunebreNecrologi::$inInst) ? PortaleFunebreNecrologi::GetImpostazioni()['colore_testo'] : '#000',
]);
