<?php

namespace Digitalia;

// Evita l'accesso diretto al file
if (!defined('ABSPATH')) { exit; }

abstract class AdminMenuManager {

    static function CreaMenuPrincipale($nome_plugin, $slug, $call, $icona='dashicons-admin-home',$cap='manage_options') {
             
        add_menu_page(
            $nome_plugin.' - Overview', // Titolo della pagina
            $nome_plugin, // Titolo del menu
            $cap, // Capacità necessaria
            $slug.'-overview', // Slug della pagina
            $call, // Funzione di callback
            $icona, // Icona del menu
            1 // Posizione nel menu
        );

        // Aggiungi la voce di menu principale come voce di sottomenu ma con un titolo diverso
        add_submenu_page(
            $slug.'-overview', // Slug del menu genitore
            $nome_plugin.' - '.'Overview', // Titolo della pagina
            'Overview', // Titolo del menu
            $cap, // Capacità necessaria
            $slug.'-overview', // Slug della pagina
            $call, // Funzione di callback
        );

    }

    static function CreaSottoPaginaMenu($nome_plugin, $plug_slug, $pgName, $pg_slug, $callb, $cap='manage_options') {
        
        add_submenu_page(
            $plug_slug.'-overview', // Slug del menu genitore
            $nome_plugin.' - '.$pgName, // Titolo della pagina
            $pgName, // Titolo del menu
            'manage_options', // Capacità necessaria
            $plug_slug.'-'.$pg_slug, // Slug della pagina
            $callb, // Funzione di callback
        );

    }

    static function IncludiPaginaPlugin($file, $nomePlugin, $nomePagina) {

        echo '<div class="wrap digitalia-wp-plugin"><div class="testata-plugin"><div class="left">';
        echo '<h1>'.PluginBase::get_plugin_name().': '.$nomePagina.'</h1><p style="margin-top: 0px;">v'.PluginBase::get_version().' di <i>'.PluginBase::get_author().'</i></p></div>';
        echo '<div class="right">';
        do_action('gestione_necrologi_head_left');
        echo '</div></div><hr>';
        include_once($file);
        echo '</div>';
    }
    
}