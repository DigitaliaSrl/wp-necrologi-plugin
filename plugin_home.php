<?php

use function Digitalia\get_plugin_asset_url as get_asset_url;
use function Digitalia\get_plugin_page_url;

if (!defined('ABSPATH')) { exit; }

$img_url = get_asset_url('digitalia_bg.jpg');

$plugin_instance = Digitalia\PluginBase::get_instance();

$api = new PortaleFunebre_API();

$stats = $api->TrovaStatistiche();

$lista_necrologi = get_plugin_page_url('necrologi');
$lista_cordogli  = get_plugin_page_url('cordogli');
$impostazioni    = get_plugin_page_url('impostazioni');

?>
<div class="welcome-panel">

    <div class="welcome-panel-content">

        <div class="welcome-panel-header">

            <div class="welcome-panel-header-image">
                <img src="<?php echo $img_url; ?>"/>
            </div>
            <h2><?php echo Digitalia\PluginBase::get_plugin_name(); ?></h2>
            <p><?php echo Digitalia\PluginBase::get_description(); ?></p>
        </div>
        <?php  if (!GestioneNecrologi::IsConfigurato()) { ?>
            <div class="welcome-panel-container">
                <div class="pf-banner-container">
                    <div class="pf-banner-content">
                        <div class="pf-banner-text">
                            <span class="pf-badge">Configurazione Necessaria</span>
                            <h2>Porta la tua Agenzia Online con un Click</h2>
                            <p>Collega il tuo plugin a <strong>PortaleFunebre.com</strong> per sbloccare la sincronizzazione automatica dei necrologi, la gestione dei fiori e i messaggi di cordoglio in tempo reale.</p>
                        </div>
                        <div class="pf-banner-action">
                            <a href="https://www.portalefunebre.com" target="_blank" class="pf-button">
                                Attiva Tutte le Funzionalità
                                <span class="pf-icon">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <?php } else { ?>
            
            <div class="welcome-panel-column-container">
                
                <div class="welcome-panel-column">
                    <span class="dashicons dashicons-edit"></span>
                    <div class="welcome-panel-column-content">
                        <h3>Crea e gestisci i tuoi necrologi</h3>
                        <p>Per creare, modificare e eliminare i necrologi, accedi al portale amministrativo. i tuoi necrologi saranno subito visibili all'interno del tuo sito</p>
                        <h4>(hai inserito <?php echo $stats->numero_necrologi; ?> necrologi)</h4>
                        <a href="<?php echo $lista_necrologi; ?>">Visulizzali adesso</a>
                    </div>
                </div>

                <div class="welcome-panel-column">
                    <span class="dashicons dashicons-excerpt-view"></span>
                    <div class="welcome-panel-column-content">
                        <h3>Visualizza i necrologi sul tuo sito, e raccogli i cordogli</h3>
                        <p>Il nostro portale funebre si integra direttamente al tuo sito, grazie a questo apposito plugin potrai visualizzarli e iniziare subito a raccogliere i cordogli</p>  
                        <h4>(hai ricevuto <?php echo $stats->numero_cordogli; ?> cordogli)</h4>
                        <a href="<?php echo $lista_cordogli; ?>">Mostra i cordogli</a>
                    </div>
                </div>

                <div class="welcome-panel-column">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <div class="welcome-panel-column-content">
                        <h3>Cambia l'aspetto e gli stili di visualizzazione</h3>
                        <p>Grazie a questo plugin potrai modificare il modo di visualizzare i necrologi e cambiare alcuni aspetti visivi</p>
                        <a href="<?php echo $impostazioni; ?>">Modifica impostazioni</a>
                    </div>
                </div>

            </div>

        <?php } ?>
    </div>

</div>

<h2>Shortcodes:</h2>
<p><code>[gestione_necrologi_lista]</code>Lista dei necrologi</p>
<p><code>[gestione_necrologi_singolo]</code>Pagina Necrologio singolo</p>
<p><code>[gestione_necrologi_slider]</code>Slider di preview dei necrologi</p>

<p>Usa questi shortcode per includere gli elmenti dove desideri, posiziona gestione_necrologi_singolo all'interno della pagina del necrologio singolo</p>
