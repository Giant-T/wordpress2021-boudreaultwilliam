<?php

require_once "../../../wp-load.php";
global $wpdb;

// les variables de session seront initialisées à true seulement si tous les tests sont réussis
$_SESSION['william_suppression_item_reussie'] = false; 
$_SESSION['william_nonce_lien_valide'] = false;

if ( isset( $_GET['_wpnonce'] ) ) {
    if ( isset( $_GET['id'] ) ) {
        $id = $_GET['id'];
 
        if (wp_verify_nonce( $_GET['_wpnonce'], "supprimer_item_$id" ) ) {
            $_SESSION['william_nonce_lien_valide'] = true;
        }
    }
}

if ($_SESSION['william_nonce_lien_valide']) {
    $table_items = $wpdb->prefix . 'william_livre';
    if ( $wpdb->delete($table_items, [ 'id' => $id ] ) ) {
        $_SESSION['william_suppression_item_reussie'] = true;
    } else {
        william_log_debug( $wpdb->last_error );
    }
}

$url_retour = admin_url( "admin.php?page=william_gestion" );

wp_redirect( $url_retour );