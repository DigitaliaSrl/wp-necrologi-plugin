<?php

use function Digitalia\get_plugin_page_url;

$api = new PortaleFunebre_API();

$cordogli_url = get_plugin_page_url('cordogli').'&defunto=';

$defunto_slug = (isset($_GET['defunto'])) ? $_GET['defunto'] : '';

if ($defunto_slug) {
    
    $necrologio = $api->TrovaNecrologioSingolo($defunto_slug, true);

    echo '<h1>CORDOGLI PER: '.$necrologio->nome_defunto.'</h1><hr>';

    $cordogli = $necrologio->cordogli;

    $is_attivo = function ($tp) use ($cordogli) { return $cordogli->attivi->$tp; };
    
    foreach ($cordogli as $tipo_cordoglio => $dati) {

        if ($tipo_cordoglio == 'attivi' || $tipo_cordoglio == 'whatsapp') { continue; }

        if (!$is_attivo($tipo_cordoglio)) { continue; }

        $num_cordogli = count($dati);

        echo '<h2>'.strtoupper($tipo_cordoglio).' ('.$num_cordogli.')</h2>';

        if ($num_cordogli < 1) { echo '-- nessun corodglio ricevuto al momento'; }

        ?>
        <table class="wp-list-table tabella-iscritti widefat fixed striped necrologi">
            <thead><tr><th>Nome e cognome</th><th>email</th><th>telefono</th><!--th>Cordogli whatsapp</th--><th>Messaggio</th></tr></thead>
            <tbody>


        <?php

            foreach ($dati as $cord) {

                $nome_cognome = $cord->nome.' '.$cord->cognome;
                $mail         = $cord->email;
                $telefono     = $cord->telefono;
                $mex          = $cord->messaggio;


                echo '<tr><td><b>'.$nome_cognome.'</b></td><td><b>'.$mail.'</b></td><td>'.$telefono.'</td><!--td>'.$telefono.'</td--><td>'.$mex.'</td></tr>';


            }

        ?>
            </tbody>
            <tfoot><tr><th>Nome e cognome</th><th>email</th><th>telefono</th><!--th>Cordogli whatsapp</th--><th>Messaggio</th></tr></tfoot>
        </table>
    
        <?php

    }

} else {
    
    $cerimonie = $api->TrovaTuttiNecrologi(true);

?>

    <table class="wp-list-table tabella-iscritti widefat fixed striped necrologi">
        <thead><tr><th style="width: 40px"> </th><th>Nome defunto</th><th>Cordogli MAIL</th><!--th>Cordogli whatsapp</th--><th>Cordogli PDF</th><th>Azione</th></tr></thead>
        <tbody>
            <?php

                foreach ($cerimonie as $cer) {

                    $num_email    = count($cer->cordogli->email);
                    $num_whatsapp = count($cer->cordogli->whatsapp);
                    $num_pdf      = count($cer->cordogli->pdf);

                    $totali = $num_email + $num_whatsapp + $num_pdf;

                    if ($totali < 1) { continue; }

                    $azioni = '<a href="'.$cordogli_url.$cer->slug.'">vedi</a>';

                    $thumb = PortaleFunebre_API::GetImgUrl($cer->thumbnail);

                    $img = '<img src="'.$thumb.'" style="width: 40px"/>';

                    echo '<tr><td>'.$img.'</td><td><b>'.$cer->nome_defunto.'</b> ('.$totali.' cordogli totali)</td><td>'.$num_email.'</td><!--td>'.$num_whatsapp.'</td--><td>'.$num_pdf.'</td><td>'.$azioni.'</td></tr>';


                }

            ?>
        </tbody>
        <tfoot><tr><th style="width: 40px"> </th><th>Nome defunto</th><th>Cordogli MAIL</th><!--th>Cordogli whatsapp</th--><th>Cordogli PDF</th><th>Azione</th></tr></tfoot>
    </table>

    <?php

    if (count($cerimonie) < 1) {
        echo '<p>Non ci sono ancora cordogli caricati per questo utente</p>';
    }
 ?>
    
    
     
<?php }
