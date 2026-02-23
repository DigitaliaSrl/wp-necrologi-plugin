<?php

if (!defined('PORTALE_FUNEBRE_API_INCLUDED')) {
    die('');
}

class PortaleFunebre_API {

    private static $END_POINT='',$API_KEY='',$CLIENT_ID='';

    private $urlParams = '';

    private static $inited=false;

    private $token=null;

    function __construct() {

        /*$res = self::portale_call('api/authorize',[
            'api_key'   => self::$API_KEY,
            'client_id' => self::$CLIENT_ID
        ]);*/
        
        $this->urlParams = '?api_key='.self::$API_KEY.'&client_id='.self::$CLIENT_ID;
        
        /*$res = json_decode($res, true);

        $this->token = $res['token'];*/

    }

    public static function GetEndPoint() { return self::$END_POINT; }

    public static function GetAsseturl($asset_name = '', $type='') {

        if ($type == 'img') {
            return self::$END_POINT.'/upload/media/'.$asset_name;
        }

        return '-';

    }

    public static function GetImgUrl($image_name = '') {
        return self::GetAsseturl($image_name, 'img');
    }

    public static function GetLoginPath() { return self::$END_POINT.'/login/'; }
    
    public function TrovaStatistiche() {

        $res2 = self::portale_call('api/user_stats'.$this->urlParams,['token' => $this->token]);

        return json_decode($res2);
        
    }

    public function InviaCordoglio(array $dati) {
        $dati['token'] = $this->token;
        $res2 = self::portale_call('api/add_cordoglio'.$this->urlParams,$dati);
        return json_decode($res2);
    }

    
    public function TrovaNecrologioSingolo(string $slug, $full=false) {

        $res2 = self::portale_call('api/get_necrolog'.$this->urlParams,['slug' => $slug,'get_full' => $full]);

        return json_decode($res2);
        
    }

    public function TrovaTuttiNecrologi($full=false, int $limite=0) {

        $res2 = self::portale_call('api/necrologies_list'.$this->urlParams,[ 'get_full' => $full, 'limit' => $limite ]);

        return json_decode($res2);

    }

    static function Config() {

        foreach (func_get_arg(0) as $key => $val) {
            self::$$key = $val;
        }

        self::$inited = true;

    }

    private static function portale_call($entry_point,array $data) {
        
        $url = self::$END_POINT.'/'.$entry_point;
        
        try {

            $postdata = http_build_query($data);
            // Inizializza cURL
            $ch = curl_init($url);

            // Configura cURL
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded'
            ));

            // Esegui la richiesta e cattura la risposta
            $result = curl_exec($ch);

            // Controllo del risultato
            if ($result === FALSE) {
                // Gestione dell'errore

                throw new Exception("Api call not valid");

            }

            return $result;

        } catch (Exception $e) {
            // Stampa il risultato
            return[ 'error' => $e->GetMessage() ];

        }
        
    }

}