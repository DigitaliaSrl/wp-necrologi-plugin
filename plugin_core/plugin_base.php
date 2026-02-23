<?php

namespace Digitalia;

// Evita l'accesso diretto al file
if (!defined('ABSPATH')) { exit; }

function get_plugin_page_url($name) {

    if (PluginBase::$CURRENT_ACTIVE_PLUGIN) {
        $plug_slug = str_replace(' ','-',str_replace('_','-',strtolower(PluginBase::$CURRENT_ACTIVE_PLUGIN)));
        return admin_url('admin.php?page='.$plug_slug.'-'.$name);
    }

    return '';

}

function get_plugin_asset_url($name) {

    if (PluginBase::$CURRENT_ACTIVE_PLUGIN) {
        $plug_name = PluginBase::$CURRENT_ACTIVE_PLUGIN;
        $dir = plugin_dir_url($plug_name).$plug_name;
        return $dir.'/assets/'.$name;
    }

    return '';

}

function dg_ajax_plugin_base() {
    
    $azione = sanitize_text_field($_POST['dgplugin_action']);

    if (isset(PluginBase::$ACTIONS[$azione])) {

        $res = PluginBase::$ACTIONS[$azione]($_POST);
        wp_send_json_success($res);

    }
    
}
add_action('wp_ajax_dg_ajax_plugin_base', __NAMESPACE__ . '\\dg_ajax_plugin_base');
add_action('wp_ajax_nopriv_dg_ajax_plugin_base', __NAMESPACE__ . '\\dg_ajax_plugin_base');


class PluginBase {

    private $slug;
    private $plugin_name;
    private $PAGINE=[];
    private $dbMan=null;

    private $plug_dir = '';
    private static $PLUGDATA = [];
    public static  $ACTIONS=[];
    private $plugin_icon = 'dashicons-admin-home';

    private static $INST = null;


    private static $GLOBAL_OBJ_VARS=[];
    public static $CURRENT_ACTIVE_PLUGIN='';

    static function get_instance() {
        return self::$INST;
    }


    function __construct($plugin_name, $main_plugin_file, $icona='dashicons-admin-home') {

        $plugin_dir = dirname($main_plugin_file);

        $this->plug_dir    = $plugin_dir;
        $this->plugin_name = $plugin_name;
        $this->plugin_icon = $icona;

        $this->slug  = sanitize_title($plugin_name);
        $this->dbMan = new DbOpzioniPlugin($this->slug);

        $main_class = $this;
        register_activation_hook($main_plugin_file, function () {
            
            if (!$this->is_installed()) {
                $this->dbMan->create_table_if_not_exists();
                $this->before_install();
            }
            $this->before_activation();
            
        });
        
        //register_uninstall_hook(__FILE__, [$this,'after_uninstall']);

        $plug_fold_name = basename($this->plug_dir);
        
        if ( is_admin() ) {

            add_action('admin_enqueue_scripts', function () {
                wp_enqueue_media();
            });

            add_action('admin_menu', function () {

                $nome_plugin = $this->plugin_name;
                $plug_slug   = $this->slug;

                $callback    = [$this, 'mostra_pagina_plugin'];
                AdminMenuManager::CreaMenuPrincipale($nome_plugin,$plug_slug,$callback,$this->plugin_icon);

                $pagine = $this->create_menu_pages();

                if ($pagine && is_array($pagine)) {

                    foreach ($pagine as $pag) {

                        $pg_slug = sanitize_title($pag);

                        $this->PAGINE[$pg_slug] = $pag;

                        AdminMenuManager::CreaSottoPaginaMenu($nome_plugin, $plug_slug, $pag, $pg_slug, $callback);

                    }

                }
                
            });
            
            add_action('admin_enqueue_scripts', function ($hook) use ($plug_fold_name) {
                // Carica solo per le pagine del plugin
                if (strpos($hook, $this->slug) !== false) {
                    wp_enqueue_style($this->slug.'-backend',  plugin_dir_url('') . '/' . $plug_fold_name . '/scripts/backend.css');
                    wp_enqueue_script($this->slug.'-backend', plugin_dir_url('') . '/' . $plug_fold_name . '/scripts/backend.js', array('jquery'), null, true);
                }
            });
        
        }

        add_action('wp_enqueue_scripts', function () use ($plug_fold_name) {
            wp_enqueue_style($this->slug.'-frontend',  plugin_dir_url('') . '/' . $plug_fold_name . '/scripts/frontend.css');
            wp_enqueue_script($this->slug.'-frontend', plugin_dir_url('') . '/' . $plug_fold_name . '/scripts/frontend.js', array('jquery'), null, true);
        });

        add_action('wp_head', function () { ?>
            <script>
                (function ($) {

                    window.DgPlugin = new function () {

                        let plug_vars = <?php echo json_encode(self::$GLOBAL_OBJ_VARS); ?>;

                        /*this.img_url  = '< ?php echo self::GetImgUrl(''); ? >';*/
                        this.site_url = '<?php echo get_site_url(); ?>';
                        let ajax_url  = '<?php echo admin_url('admin-ajax.php'); ?>';

                        this.get_var = function (vname) { return plug_vars[vname]; }

                        this.ajax = function (action_required, callback, data_c) {

                            if (!callback) { return; }

                            let dati_call = {
                                action: 'dg_ajax_plugin_base',
                                dgplugin_action: action_required
                            };
                            
                            if (data_c) { for (let i in data_c) { dati_call[i] = data_c[i]; } }

                            $.ajax({
                                type: 'POST',
                                url:  ajax_url,
                                data: dati_call,
                                success: function (response) { if (!response.success) { console.log("NO SUCCESS"); } if (callback) { callback(response.data); } },
                                error: function (error) { console.error('Error:', error); }
                            });

                        };

                    };

                })(jQuery);
            </script>
        <?php });
        
    }

    private function hasCall($call_name) {
        if (!isset(self::$WP_CALLS[__FILE__])) { return false; }
        return self::$WP_CALLS[__FILE__] == $call_name;
    }

    protected function is_installed() { return $this->dbMan->isPluginInstalled(); }
    
    public function get_opzione(string $opzione) {

        $db  = $this->dbMan;
        $res = $db->get_entry($opzione);

        if ($res) { return new DbOpzionePlugin($this->dbMan, $res); }

        return new DbOpzionePlugin($this->dbMan, [
            'nome'      => $opzione,
            'dati'      => '{}',
            'creazione' => '',
            'modifica'  => ''
        ]);

    }

    static function add_js_variables($variabili) { foreach ($variabili as $name => $data) { self::$GLOBAL_OBJ_VARS[$name] = $data; } }

    static function add_css_variables($variabili) {
        \add_action( 'wp_head', function () use ($variabili) {
            $custom_css = '';
            foreach ($variabili as $k => $val) { $custom_css .= "--dg-$k: $val;\n"; }            
            echo "<style>:root {\n$custom_css\n}</style>";
        } );
    }

    static function add_ajax_call($nome_call, $fun) { self::$ACTIONS[$nome_call] = $fun; }

    static function get_author() { return self::$PLUGDATA['Author']; }
    static function get_author_url() { return self::$PLUGDATA['AuthorURI']; }
    static function get_version() { return self::$PLUGDATA['Version']; }
    static function get_description() { return self::$PLUGDATA['Description']; }
    static function get_plugin_name() { return self::$PLUGDATA['Name']; }

    public function mostra_pagina_plugin () {

        $screen = get_current_screen();
        $screen_id = ($screen) ? $screen->id : '';

        $overview_page  = 'toplevel_page_'.$this->slug.'-overview';
        $page_found = trim(str_replace('_page_','',str_replace($this->slug,'',$screen_id)),'-');

        if ($screen_id == $overview_page || $page_found == 'overview') {

            self::$INST = $this;
            self::$CURRENT_ACTIVE_PLUGIN = basename($this->plug_dir);
            
            self::$PLUGDATA = \get_plugin_data($this->plug_dir.'/index.php');

            AdminMenuManager::IncludiPaginaPlugin($this->plug_dir.'/plugin_home.php',$this->plugin_name,'Overview');
            
        } else if (isset($this->PAGINE[$page_found])) {
            
            self::$INST = $this;
            self::$CURRENT_ACTIVE_PLUGIN = basename($this->plug_dir);

            self::$PLUGDATA = \get_plugin_data($this->plug_dir.'/index.php');

            AdminMenuManager::IncludiPaginaPlugin($this->plug_dir.'/pages/'.$page_found.'.php',$this->plugin_name,$this->PAGINE[$page_found]);
        }

    }

    public function before_install() { }
    public function after_uninstall() { }
    public function before_activation() { }
    public function create_menu_pages() { }

    public function create_shortcode($short_string, array $params=[]) {

        self::$INST = $this;

        self::$CURRENT_ACTIVE_PLUGIN = basename($this->plug_dir);

        $shorts_folder = $this->plug_dir . '/shorts/';

        add_shortcode($short_string, function($atts) use ($short_string, $shorts_folder) {

            $shortcode_file = $shorts_folder . $short_string . '.php';
    
            if (file_exists($shortcode_file)) {
                
                if (function_exists($short_string)) {
                    return call_user_func($short_string, shortcode_atts(array(), $atts));
                } else {
                    ob_start();
                    include_once($shortcode_file);
                    return ob_get_clean();
                }

            } else {
                return '<p>Errore: Il file <strong>' . $shortcode_file . '.php</strong> non esiste.</p>';
            }

        });

    }

}