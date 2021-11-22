<?php
// chargement des fonctionnalités WordPress nécessaires au traitement
require_once "../../../wp-load.php";

// les variables de session seront initialisées à true seulement si tous les tests sont réussis
$_SESSION['william_ajout_reussi'] = false;
$_SESSION['william_nonce_valide'] = false;
$_POST = stripslashes_deep($_POST);
 
if ( isset( $_POST['soumettre'] ) ) {   // si la page a reçu des données du formulaire 
 
    // vérification de la validité du nonce
    if ( wp_verify_nonce( $_POST['validite_nonce'], 'ajouter_item_bd' )) {
 
        $_SESSION['william_nonce_valide'] = true;
 
        // validation côté serveur des données lues dans le formulaire
        if ( isset( $_POST['titre']) ) {
            $titre = htmlspecialchars( $_POST['titre'] );
        }
 
        if ( isset( $_POST['categorie']) ) {
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

        if ( isset( $_POST['auteur']) ) {
            $auteur = htmlspecialchars( $_POST['auteur'] );
        }

        if ( isset( $_POST['description']) ) {
            $description = htmlspecialchars( $_POST['description'] );
        }

        if ( isset( $_POST['nombrePage'] ) ) {
            $nombrePage = htmlspecialchars( $_POST['nombrePage'] );
        } 

        // traitement du formulaire
        if (isset( $titre ) && isset( $categorie ) && isset( $auteur ) && isset( $description ) && isset( $nombrePage ) ) {
            $table_matable =  $wpdb->prefix . 'william_livre';
            $requete = $wpdb->prepare( "INSERT INTO $table_matable(titre, categorie_id, auteur, description, nombre_page) VALUES (%s, %d, %s, %s, %d);", $titre, $categorie, $auteur, $description, $nombrePage );
            $reussite = $wpdb->query( $requete );
            if ($reussite) {
                $_SESSION['william_ajout_reussi'] = true;
            }
        }
    }
}

$url = admin_url( "admin.php?page=william_gestion" );
wp_redirect( $url );