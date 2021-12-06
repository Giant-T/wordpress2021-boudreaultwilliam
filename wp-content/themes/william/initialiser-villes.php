<?php
require_once "../../../wp-load.php";

if ( isset( $_GET['id_province'] ) && is_numeric( $_GET['id_province'] ) ) {
    global $wpdb;
    $reponse = '';
    $id_province = intval($_GET['id_province']);
    $table_ville = $wpdb->prefix . 'william_ville'; 
    $requete = $wpdb->prepare( "SELECT id, nom FROM $table_ville WHERE province = %d ORDER BY nom ASC;", $id_province );
    $resultat = $wpdb->get_results( $requete );
    $erreur = $wpdb->last_error;
    if ( $erreur == "" ) {
        if ( $wpdb->num_rows > 0 ) {
            $valeurs = [];
            foreach( $resultat as $enreg ) {
                array_push( $valeurs, array(  'id' => $enreg->id, 'nom' => $enreg->nom) );
            }
            $reponse = json_encode($valeurs);
        }
        else {
            $reponse = json_encode( array( array( 'id' => '', 'nom' => __( 'Aucune donné dans cette province.', 'william' ) ) ) );
        }
    }
    else {
        william_log_debug( $wpdb->last_error );
        $reponse = json_encode( array( array( 'id' => '', 'nom' => __( 'Accès au données impossible.', 'william' ) ) ) );
    }
    echo $reponse;
}
else {
    http_response_code(418);
}