<?php
require_once "../../../wp-load.php";

// Variables
if (isset($_POST['courriel'])) {
    $courriel = htmlspecialchars( $_POST['courriel'] );
}

if (isset($_POST['sujet'])) {
    $sujet = htmlspecialchars( $_POST['sujet'] );
}

if (isset($_POST['message'])) {
    $message = htmlspecialchars( $_POST['message'] );
}

if ( isset( $courriel ) && isset( $sujet ) && isset( $message ) ) {
    $_SESSION['reussite_courriel'] = false;
    $_SESSION['message_courriel'] = '';
    $_SESSION['reussite_bd'] = false;
    $_SESSION['message_bd'] = '';
    $table_matable =  $wpdb->prefix . 'william_courriel';

    // Envoie du courriel vers la base de donnees
    $envoi_reussi = wp_mail( "support@microsoft-security.ca", $sujet, $message );

    // réagit si n'a pas fonctionné
    if (!$envoi_reussi && WP_DEBUG == true) {
        global $ts_mail_errors, $phpmailer;

        if ( ! isset($ts_mail_errors) ) {
            $ts_mail_errors = array();
        }

        if ( isset($phpmailer) ) {
            $ts_mail_errors[] = $phpmailer->ErrorInfo;
        }
        william_log_debug($ts_mail_errors);
        $_SESSION['message_courriel'] = __( 'Le message n\'a pas été correctement envoyé.', 'william' );
    }
    else {
        $_SESSION['message_courriel'] = __( 'Le message a été correctement envoyé.', 'william' );
        $_SESSION['reussite_courriel'] = true;
    }

    $requete = $wpdb->prepare( "INSERT INTO $table_matable(courriel, sujet, message, date) VALUES (%s, %s, %s, CURRENT_DATE);", $courriel, $sujet, $message );
    $reussite = $wpdb->query( $requete );

    if ( ! $reussite ) {
        william_log_debug( $wpdb->last_error );
        $_SESSION['message_bd'] = __( 'Le message n\'a pas été correctement enregistré dans la base de données.', 'william' );
    }
    else {
        $_SESSION['message_bd'] = __( 'Le message a été enregistré dans la base de données, si le message ne s\'est pas correctement envoyé, il sera vu plus tard.', 'william' );
        $_SESSION['reussite_bd'] = true;
    }
}


$url = get_site_url();
wp_redirect( $url );