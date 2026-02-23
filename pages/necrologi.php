<?php

use function Digitalia\get_plugin_page_url;

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

                $dati_funerale = $funerale->luogo.' (<b>'.$funerale->data.'</b> '.$funerale->ora_da.')';
                $dati_feretro  = $feretro->luogo.' (<b>'.$feretro->data.'</b> '.$feretro->ora_da.')';
                $dati_rosario  = $rosario->luogo.' (<b>'.$rosario->data.'</b> '.$rosario->ora_da.')';

                $azioni = '<a target="_blank" href="'.$portale_url.'">edita sul portale</a>';
                
                $num_cordo = count($cer->cordogli->email) + count($cer->cordogli->pdf) + count($cer->cordogli->whatsapp);

                if ($num_cordo) {
                    $azioni .= ' | <a href="'.$cordogli_url.$cer->slug.'">vedi i cordogli</a>';
                }
                
                $thumb = PortaleFunebre_API::GetImgUrl($cer->thumbnail);

                $img = '<img src="'.$thumb.'" style="width: 40px"/>';

                echo '<tr><td>'.$img.'</td><td><b>'.$cer->nome_defunto.'</b></td><td>'.$dati_funerale.'</td><td>'.$dati_funerale.'</td><td>'.$dati_funerale.'</td><td>'.$azioni.'</td></tr>';


            }

        ?>
    </tbody>
    <tfoot><tr><th style="width: 40px"> </th><th>Nome defunto</th><th>funerale</th><th>rosario</th><th>feretro</th><th>Azione</th></tr></tfoot>
</table>

<?php

if (count($cerimonie) < 1) {
    echo '<p>Non ci sono cerimonie caricate al momento</p>';
}