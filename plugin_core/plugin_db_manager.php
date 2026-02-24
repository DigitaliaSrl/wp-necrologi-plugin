<?php


namespace Digitalia;

// Evita l'accesso diretto al file
if (!defined('ABSPATH')) { exit; }

// Classe per gestire la tabella nel database
class DbOpzioniPlugin {

    private $table_name;

    // Metodo costruttore della classe
    public function __construct($table_slug) {
        global $wpdb;
        $this->table_name = $wpdb->prefix . str_replace('-','_',$table_slug) . '_opzioni';
    }

    public function isPluginInstalled() {
        global $wpdb;
        if ($wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") != $this->table_name) {
            return false;
        }
        return true;
    }

    // Metodo privato per creare la tabella se non esiste
    public function create_table_if_not_exists() {

        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS ".$this->table_name." (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nome varchar(255) NOT NULL,
            dati longtext NOT NULL,
            creazione datetime NOT NULL,
            modifica datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        \dbDelta($sql);

    }

    // Metodo per inserire una nuova entry nella tabella
    public function insert_entry($nome, $dati) {

        global $wpdb;

        $creazione = current_time('mysql');
        $modifica = $creazione;

        $wpdb->insert(
            $this->table_name,
            [
                'nome'      => $nome,
                'dati'      => json_encode($dati),
                'creazione' => $creazione,
                'modifica'  => $modifica
            ],
            ['%s', '%s', '%s', '%s']
        );

    }

    // Metodo per leggere un'entry dalla tabella per nome
    public function get_entry($nome) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->table_name WHERE nome = %s", $nome), ARRAY_A);
    }

    // Metodo per aggiornare un'entry nella tabella per nome
    public function update_entry($nome, array $dati) {

        global $wpdb;

        $modifica = current_time('mysql');

        $wpdb->update(
            $this->table_name,
            [
                'dati'     => json_encode($dati),
                'modifica' => $modifica
            ],
            ['nome' => $nome],
            ['%s', '%s'],
            ['%s']
        );

    }

}

class DbOpzionePlugin implements \ArrayAccess {

    private $DB;

    private $nome,$dati=[],$creazione,$modifica;

    private $ID;

    private $crea_nuovo=false;

    function __construct(DbOpzioniPlugin $dbman, $opt) {
        $this->DB         = $dbman;
        $this->ID         = (isset($opt['id'])) ? $opt['id'] : null;
        $this->nome       = $opt['nome'];
        $this->dati       = json_decode($opt['dati'],true);
        $this->creazione  = $opt['creazione'];
        $this->modifica   = $opt['modifica'];
        $this->crea_nuovo = ($this->ID) ? false : true;
    }

    public function ToArray() { return $this->dati; }

    // Implementazione di ArrayAccess
    public function offsetSet(mixed $offset, mixed $value): void { $this->dati[$offset] = $value; }

    public function offsetExists(mixed $offset): bool { return (isset($this->dati[$offset])) ? true : false; }

    public function offsetUnset(mixed $offset): void { unset($this->dati[$offset]); }

    public function offsetGet(mixed $offset): mixed { return (isset($this->dati[$offset])) ? $this->dati[$offset] : null; }

    function salva() {

        if ($this->crea_nuovo) {
            $this->DB->insert_entry($this->nome, $this->dati);
            $this->crea_nuovo = false;
            return;
        }

        $this->DB->update_entry($this->nome, $this->dati);

    }

}
