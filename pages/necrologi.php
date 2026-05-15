<?php

use function Digitalia\get_plugin_page_url;

if (!defined('ABSPATH')) { exit; }

$api = new PortaleFunebre_API();

$cerimonie = $api->TrovaTuttiNecrologi(true);

$portale_url  = PortaleFunebre_API::GetEndPoint().'/area-riservata';
$cordogli_url = get_plugin_page_url('cordogli').'&defunto=';

?>

<table class="wp-list-table tabella-iscritti widefat fixed striped necrologi">
    <thead><tr><th style="width: 40px"> </th><th>Nome defunto</th><th>funerale</th><th>rosario</th><th>feretro</th><th>Azione</th></tr></thead>
    <tbody>
        <?php

            foreach ($cerimonie as $cer) {

                $funerale = $cer->funerale;
                $feretro  = $cer->chiusura_feretro;
                $rosario  = $cer->rosario;

                $dati_funerale = esc_html($funerale->luogo) . ' (<b>' . esc_html($funerale->data) . '</b> ' . esc_html($funerale->ora_da) . ')';
                $dati_feretro  = esc_html($feretro->luogo) . ' (<b>' . esc_html($feretro->data) . '</b> ' . esc_html($feretro->ora_da) . ')';
                $dati_rosario  = esc_html($rosario->luogo) . ' (<b>' . esc_html($rosario->data) . '</b> ' . esc_html($rosario->ora_da) . ')';

                $azioni = '<a target="_blank" href="' . esc_url($portale_url) . '">edita sul portale</a>';
                
                $num_cordo = count($cer->cordogli->email) + count($cer->cordogli->pdf) + count($cer->cordogli->whatsapp);

                if ($num_cordo) {
                    $azioni .= ' | <a href="' . esc_url($cordogli_url . $cer->slug) . '">vedi i cordogli</a>';
                }
                
                $thumb = PortaleFunebre_API::GetImgUrl($cer->thumbnail);

                $img = '<img src="' . esc_url($thumb) . '" style="width: 40px"/>';

                echo '<tr><td>' . wp_kses_post($img) . '</td><td><b>' . esc_html($cer->nome_defunto) . '</b></td><td>' . wp_kses_post($dati_funerale) . '</td><td>' . wp_kses_post($dati_funerale) . '</td><td>' . wp_kses_post($dati_funerale) . '</td><td>' . wp_kses_post($azioni) . '</td></tr>';


            }

        ?>
    </tbody>
    <tfoot><tr><th style="width: 40px"> </th><th>Nome defunto</th><th>funerale</th><th>rosario</th><th>feretro</th><th>Azione</th></tr></tfoot>
</table>

<?php

if (count($cerimonie) < 1) {
    echo '<p>Non ci sono cerimonie caricate al momento</p>';
}
