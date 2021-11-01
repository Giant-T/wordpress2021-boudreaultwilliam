<?php
require_once "../../../wp-load.php";

// Variables
$_SESSION['reussite_bd'] = false;
$_SESSION['reussite_courriel'] = false;
        
if (isset($_POST['courriel'])) {
    $captcha = $_POST['g-recaptcha-response'];
    if (preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $_POST['courriel'])) {
        $courriel = htmlspecialchars( $_POST['courriel'] );
    }
    else {
        $_SESSION['message_bd'] = __('Le courriel est invalide. ', 'william');
    }
}

if (isset($_POST['sujet'])) {
    if (strlen($_POST['sujet']) > 0 && strlen($_POST['sujet']) < 50) {
        $sujet = htmlspecialchars($_POST['sujet']);
    }
    else {
        $_SESSION['message_bd'] .= __('Le sujet est invalide. ', 'william');
    }
}

if (isset($_POST['message'])) {
    if (strlen($_POST['message']) > 0 && strlen($_POST['message']) < 500) {
        $message = htmlspecialchars( $_POST['message'] );
    }
    else {
        $_SESSION['message_bd'] .= __('Le message est invalide. ', 'william');
    }
}

if ( isset($_POST['soumissionFormContact']) ) {
    // si le captcha a été reçu
    if ( $captcha ) {
        $url = 'https://www.google.com/recaptcha/api/siteverify';

        $data = [
            'secret' => '6LdyR_0cAAAAAGtAUXEFNAS4_4mQ6xRcFNzA_2fQ',
            'response' => $captcha,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
            "ssl"=> [
                 "verify_peer"=>!WP_DEBUG,   // pendant le développement, on ne veut pas de vérification SSL. Sans ceci, on obtiendrait  l'erreur "Warning: file_get_contents(): SSL operation failed with code 1"
                 "verify_peer_name"=>!WP_DEBUG,
            ],
        ];

        $contexte  = stream_context_create( $options );
        $reponse = file_get_contents( $url, false, $contexte );
        $clesReponse = json_decode( $reponse, true );
   
        if ( $clesReponse['success'] == true && $clesReponse['action'] == 'soumissioncontact' ) {
            // le test est réussi, on peut poursuivre le traitement du formulaire, notamment la validation côté serveur, puis procéder à l'envoi du courriel
            if ( isset( $courriel ) && isset( $sujet ) && isset( $message ) ) {
                $_SESSION['message_courriel'] = '';
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
        } else {
            // Test échoué. Afficher un message du genre "Le jeton de sécurité a expiré ou vous êtes considéré comme un robot. Votre message n'a pas pu être envoyé."
            $_SESSION['message_courriel'] = __('Le jeton de sécurité a expiré ou vous êtes considéré comme un robot. Votre message n\'a pas pu être envoyé.');
        }
    } else {
        // Captcha pas reçu. Afficher un message du genre "Il n'est pas possible de vérifier si vous êtes un robot pour l'instant. Votre message n'a pas pu être envoyé."
            $_SESSION['message_courriel'] = __('Il n\'est pas possible de vérifier si vous êtes un robot pour l\'instant. Votre message n\'a pas pu être envoyé.');
    }
}


$url = get_site_url();
wp_redirect( $url );