<?php
// chargement des fonctionnalités WordPress nécessaires au traitement
require_once "../../../wp-load.php";

// les variables de session seront initialisées à true seulement si tous les tests sont réussis
$_SESSION['william_ajout_inscription_reussi'] = false;
$_SESSION['william_message_inscription'] = '';
$_POST = stripslashes_deep( $_POST );

if ( isset( $_POST['soumettre_inscription'] ) ) {
    // Prenom
    if ( isset( $_POST['prenom'] ) && mb_strlen( $_POST['prenom'] ) <= 255 ) {
        $prenom = htmlspecialchars($_POST['prenom']);
    }
    else {
        $_SESSION['william_message_inscription'] .= __( 'Le prénom est invalide', 'william' );
    }

    // Nom de famille
    if ( isset( $_POST['nomfamille'] ) && mb_strlen( $_POST['nomfamille'] ) <= 255 ) {
        $nomfamille = htmlspecialchars($_POST['nomfamille']);
    }
    else {
        $_SESSION['william_message_inscription'] .= __( 'Le nom de famille est invalide', 'william' );
    }

    // Addresse
    if ( isset( $_POST['addresse'] ) && mb_strlen( $_POST['addresse'] ) <= 255 ) {
        $addresse = htmlspecialchars($_POST['addresse']);
    }
    else {
        $_SESSION['william_message_inscription'] .= __( 'L\'addresse est invalide', 'william' );
    }

    // Ville
    if ( isset( $_POST['ville'] ) && is_numeric( $_POST['ville'] ) ) {
        $table_ville = $wpdb->prefix . 'william_ville';
        $enreg = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table_ville WHERE id = %d;", $_POST['ville'] ) );
        $erreur_sql = $wpdb->last_error;

        if ( $erreur_sql == "" && $wpdb->num_rows > 0) {
            $ville = htmlspecialchars( $_POST['ville'] );
        }
        else {
            $_SESSION['william_message_inscription'] .= __( 'La ville n\'existe pas dans la base de donnée.', 'william' );
        }
    }
    else {
        $_SESSION['william_message_inscription'] .= __( 'La ville n\'est pas rempli.', 'william' );
    }

    // Tel
    if ( isset( $_POST['telephone'] ) ) {
        if ( is_numeric( $_POST['telephone'] ) && mb_strlen( $_POST['telephone'] ) <= 12 ) {
            $telephone = htmlspecialchars( $_POST['telephone'] );
        }
        else {
            $_SESSION['william_message_inscription'] .= __( 'Le numéro de téléphone est invalide', 'william' );
        }
    }
    else {
        $_SESSION['william_message_inscription'] .= __( 'Le numéro de téléphone n\'est pas rempli', 'william' );
    }

    // Email
    if ( isset( $_POST['courriel'] ) ) {
        if ( preg_match( '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $_POST['courriel'] ) && mb_strlen( $_POST['courriel'] <= 255 ) ) {
            $courriel = htmlspecialchars( $_POST['courriel'] );
        }
        else {
            $_SESSION['william_message_inscription'] .= __( 'Le courriel est invalide. ', 'william');
        }
    }
    else {
        $_SESSION['william_message_inscription'] .= __( 'Le courriel n\'est pas rempli. ', 'william');
    }

    if ( !empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $addresseip = $_SERVER['REMOTE_ADDR'];
    }
    else {
        $_SESSION['william_message_inscription'] .= __( 'Impossible de déterminer la provenance du message. ');
    }

    if ( isset( $prenom ) && isset( $nomfamille ) && isset( $addresse ) && isset( $ville ) && isset( $telephone ) && isset( $courriel ) && isset( $addresseip ) ) {
        global $wpdb;
        $table_inscription = $wpdb->prefix . 'william_inscription';
        $requete = $wpdb->prepare( "INSERT INTO $table_inscription(prenom, nomfamille, addresse, ville, telephone, courriel, addresseip) VALUES(%s, %s, %s, %d, %s, %s, %s)", $prenom, $nomfamille, $addresse, $ville, $telephone, $courriel, $addresseip );
        $reussite = $wpdb->query( $requete );
        if ( $reussite ) {
            $_SESSION['william_ajout_inscription_reussi'] = true;
            $_SESSION['william_message_inscription'] = 'Soumission du formulaire réussie!';
        }
        else {
            william_log_debug( $wpdb->last_error );
            $_SESSION['william_message_inscription'] = 'L\'inscription n\'a pas pu être enregistré.';
        }
    }
}
else {
    $_SESSION['william_message_inscription'] .= __( 'Vous ne provenez pas du formulaire', 'william' );
}

$url = get_site_url();
wp_redirect( $url );