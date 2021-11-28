<?php
// chargement des fonctionnalités WordPress nécessaires au traitement
require_once "../../../wp-load.php";
global $wpdb;

$_SESSION['william_mise_a_jour_item_reussie'] = false;
$_SESSION['william_erreur_mise_a_jour_item'] = '';
$_SESSION['william_nonce_formulaire_valide'] = false;
$_POST = stripslashes_deep($_POST);

if ( isset( $_POST['soumettre'] )  ) {
    if ( isset( $_POST['id']) && is_numeric( $_POST['id'] ) ) {
        $existe = false;

        $table_livre = $wpdb->prefix . 'william_livre';
        $enreg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_livre WHERE id = %d", $_POST['id'] ) );
        $erreur_sql = $wpdb->last_error;
        
        if ( $erreur_sql == "" && $wpdb->num_rows > 0 ) {
            $existe = true;
        }
        if ( $existe ) {
            $id = htmlspecialchars( $_POST['id'] );
        }
    }

    if ( isset( $id ) && wp_verify_nonce( $_POST['nonce_valide'], "modifier_item_bd_$id" ) ) {
        $_SESSION['william_nonce_formulaire_valide'] = true;

        if ( isset( $_POST['titre'] ) ) {
            $titre = htmlspecialchars( $_POST['titre'] );
        }

        if ( isset( $_POST['categorie'] ) ) {
            $existe = false;

            $table_categories = $wpdb->prefix . 'william_categorie';
            $enreg = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table_categories WHERE id = '%d'", $_POST['categorie'] ) );
            $erreur_sql = $wpdb->last_error;

            if ( $erreur_sql == "" && $wpdb->num_rows > 0 ) {
                $existe = true;
            }
            if ( $existe ) {
                $categorie = htmlspecialchars( $_POST['categorie'] );
            }
        }

        if ( isset( $_POST['auteur'] ) ) {
            $auteur = htmlspecialchars( $_POST['auteur'] );
        }

        if ( isset( $_POST['description'] ) ) {
            $description = htmlspecialchars( $_POST['description'] );
        }

        if ( isset( $_POST['nombrePage'] ) ) {
            $nombrePage = htmlspecialchars( $_POST['nombrePage'] );
        }

        if ( isset( $titre ) && isset( $categorie ) && isset( $auteur ) && isset( $description ) && isset( $nombrePage ) ) {
            $table_livre = $wpdb->prefix . 'william_livre';
            $reussite = $wpdb->update(
                $table_livre,
                array( 
                    'titre' => $titre,
                    'categorie_id' => $categorie,
                    'auteur' => $auteur,
                    'description' => $description, 
                    'nombre_page' => $nombrePage,
                ),
                array( 'id' => $id ),
                array(
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                    '%d',
                )
            );

            if ( $reussite === false ) {
                william_log_debug( $wpdb->last_error );

                $_SESSION['william_erreur_mise_a_jour_item'] = 'La modification des données du livre n\'a pas pu être effectué.';
            }
            else {
                $_SESSION['william_mise_a_jour_item_reussie'] = true;
            }
        }
    }
}

$url = admin_url( "admin.php?page=william_gestion" );
wp_redirect( $url );