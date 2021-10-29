<?php
/**
 * Fonction de rappel du hook after_setup_theme, exécutée après que le thème ait été initialisé
 *
 * Utilisation : add_action( 'after_setup_theme', 'monprefixe_apres_initialisation_theme' );
 *
 * @author Christiane Lagacé
 *
 */
function william_apres_initialisation_theme() {
    // Retirer la balise <meta name="generator">
    remove_action( 'wp_head', 'wp_generator' ); 
}
 
add_action( 'after_setup_theme', 'william_apres_initialisation_theme' );
 
/**
 * Change l'attribut ?ver des .css et des .js pour utiliser celui de la version de style.css
 *
 * Utilisation : add_filter( 'style_loader_src', 'monprefixe_attribut_version_style', 9999 );
 *               add_filter( 'script_loader_src', 'monprefixe_attribut_version_style', 9999 );
 * Suppositions critiques : dans l'entête du fichier style.css du thème enfant, le numéro de version
 *                          à utiliser est inscrit à la ligne Version (ex : Version: ...)
 *
 * @author Christiane Lagacé
 * @return String Url de la ressource, se terminant par ?ver= suivi du numéro de version lu dans style.css
 *
 */
function william_attribut_version_style( $src ) {
   $version = william_version_style();
   if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
      $src = remove_query_arg( 'ver', $src );
      $src = add_query_arg( 'ver', $version, $src );
   }
   return $src;
}
 
add_filter( 'style_loader_src', 'william_attribut_version_style', 9999 );
add_filter( 'script_loader_src', 'william_attribut_version_style', 9999 );
 
/**
 * Retrouve le numéro de version de la feuille de style
 *
 * Utilisation : $version = monprefixe_version_style();
 * Suppositions critiques : dans l'entête du fichier style.css du thème enfant, le numéro de version
 *                          à utiliser est inscrit à la ligne Version (ex : Version: ...)
 *
 * @author Christiane Lagacé
 * @return String Le numéro de version lu dans style.css ou, s'il est absent, le numéro 1.0
 *
 */
function william_version_style() {
   $default_headers =  array( 'Version' => 'Version' );
   $fichier = get_stylesheet_directory() . '/style.css';
   $data = get_file_data( $fichier, $default_headers );
   if ( empty( $data['Version'] ) ) {
      return "1.0";
   } else {
      return $data['Version'];
   }
}

// Source: https://www.theblog.ca/literal-comments
function william_comment_post( $incoming_comment ) {
	$incoming_comment['comment_content'] = htmlspecialchars($incoming_comment['comment_content']);
	$incoming_comment['comment_content'] = str_replace( "'", '&apos;', $incoming_comment['comment_content'] );

	return( $incoming_comment );
}

function william_comment_display( $comment_to_display ) {

	$comment_to_display = str_replace( '&apos;', "'", $comment_to_display );

	return $comment_to_display;
}

add_filter( 'preprocess_comment', 'plc_comment_post', '', 1 );
add_filter( 'comment_text', 'plc_comment_display', '', 1 );
add_filter( 'comment_text_rss', 'plc_comment_display', '', 1 );
add_filter( 'comment_excerpt', 'plc_comment_display', '', 1 );
// This stops WordPress from trying to automatically make hyperlinks on text:
remove_filter( 'comment_text', 'make_clickable', 9 );

// Page d'accueil en premier
$home = get_page_by_title('Accueil');
update_option( 'page_on_front', $home->ID );
update_option( 'show_on_front', 'page' );

/**
 * Enregistre une information de débogage dans le fichier debug.log, seulement si WP_DEBUG est à true
 *
 * Utilisation : monprefixe_log_debug( 'test' );
 * Inspiré de http://wp.smashingmagazine.com/2011/03/08/ten-things-every-wordpress-plugin-developer-should-know/
 *
 * @author Christiane Lagacé <christianelagace.com>
 *
 */
function william_log_debug( $message ) {
   if ( WP_DEBUG === true ) {
      if ( is_array( $message ) || is_object( $message ) ) {
         error_log( 'Message de débogage: ' . print_r( $message, true ) );
      } else {
         error_log( 'Message de débogage: ' . $message );
      }
   }
}

/**
 * Affiche une information de débogage à l'écran, seulement si WP_DEBUG est à true
 *
 * Utilisation : monprefixe_echo_debug( 'test' );
 * Suppositions critiques : le style .debug doit définir l'apparence du texte
 *
 * @author Christiane Lagacé <christianelagace.com>
 *
 */
function william_echo_debug( $message ) {
   if ( WP_DEBUG === true ) {
       if ( ! empty( $message ) ) {
           echo '<span class="debug">';
            if ( is_array( $message ) || is_object( $message ) ) {
               echo '<pre>';
               print_r( $message ) ;
               echo '</pre>';
            } else {
               echo $message ;
           }
           echo '</span>';
       }
   }
}

/**
 * Ajouter mon favicon
 * @author William Boudreault
 */
function william_monfavicon() {
   if (WP_DEBUG) {
      echo '<link rel="icon" href="'. get_site_url().'/image.png"  type="image/png" />';
   }
   else {
      echo '<link rel="icon" href="'. get_site_url().'/favicon.png"  type="image/png" />';
   }
}

add_action('wp_head', 'william_monfavicon');

/**
 * Fonction qui affiche les items
 * @author William Boudreault 
 */
function william_afficheritems() {
   global $wpdb;
   $output = '';
   $table_livre = $wpdb->prefix . 'william_livre';
   $table_categorie = $wpdb->prefix . 'william_categorie';
   $requete = "SELECT l.titre AS titre_livre, auteur, c.titre AS categorie, nombre_page FROM $table_livre l INNER JOIN $table_categorie c ON c.id = l.categorie_id ORDER BY l.nombre_page ASC";
   $resultat = $wpdb->get_results( $requete );

   $erreur_sql = $wpdb->last_error;

   if ( $erreur_sql == "" ) {
      if ( $wpdb->num_rows > 0 ) {
         $output .= "<table>";
         $output .= "<tbody>";
         $output .= "<tr>";
         $output .= "<th>". __("Titre", "william") ."</th>";
         $output .= "<th>". __("Auteur", "william") ."</th>";
         $output .= "<th>". __("Categorie", "william") ."</th>";
         $output .= "<th>". __("Nombre de pages", "william") ."</th>";
         $output .= "</tr>";
         foreach( $resultat as $enreg ) {
            $output .= "<tr>";
            $output .= "<td>$enreg->titre_livre</td>";
            $output .= "<td>$enreg->auteur</td>";
            $output .= "<td>$enreg->categorie</td>";
            $output .= "<td>$enreg->nombre_page</td>";
            $output .= "</tr>";
         }
         $output .= "</tbody>";
         $output .= "</table>";
      } else {
         $output .= '<div class="messageavertissement">';
         $output .= __( 'Aucune donnée ne correspond aux critères demandés.', 'william' );
         $output .= '</div>';
      }

   } else {
      $output .= '<div class="messageerreur">';
      $output .= __( 'Oups ! Un problème a été rencontré.', 'william' );
      $output .= '</div>';

      // afficher l'erreur à l'écran seulement si on est en mode débogage
      william_echo_debug( $erreur_sql );
   }

   return $output;
}

/**
 * Mets le style sur le shortcode afficher item
 * @author William Boudreault
 */
function william_style_afficher() {
   wp_enqueue_style( 'williamafficheritems', get_stylesheet_directory_uri() . '/css/william-afficher-table.css' );
}

add_shortcode('williamafficheritems', 'william_afficheritems');
add_action('wp_enqueue_scripts', 'william_style_afficher');

/** 
 * Crée les tables et insère les données
 * @author William Boudreault 
 */
function william_initialisation_bd() {
   global $wpdb;
   
   $charset_collate = $wpdb->get_charset_collate();
   $table_matable =  $wpdb->prefix . 'william_courriel';
   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

   $sql = "CREATE TABLE $table_matable (
      id bigint(20) unsigned primary key auto_increment,
      courriel varchar(255) not null,
      sujet varchar(255) not null,
      message varchar(10000) not null,
      date date not null
   ) $charset_collate";
   dbDelta( $sql );

   $table_matable =  $wpdb->prefix . 'william_categorie';
   $sql = "CREATE TABLE $table_matable (
      id bigint(20) unsigned primary key auto_increment,
      titre varchar(255),
      description varchar(255)
   ) $charset_collate;";

   dbDelta( $sql );
   $requete = "SELECT COUNT(*) FROM $table_matable";
   $presence_donnees = $wpdb->get_var( $requete );

   if ( is_null( $presence_donnees ) || $presence_donnees == 0) {
      $donnees = array(
         array( 1, 'Fiction', 'Les livres de fiction decoulent de la fiction!' ),
         array( 2, 'Horreur', 'Les livres d\'Horreur decoulent de l\'Horreur!' ),
         array( 3, 'Fantastique', 'Les livres fantastiques decoulent du fantastique!' ),
         array( 4, 'Manuel', 'Les manuels decoulent de savoir!' ),
      );
      
      foreach( $donnees as $donnee ) {
         $reussite = $wpdb->insert(
            $table_matable,
            array(
                  'id' => $donnee[0],
                  'titre' => $donnee[1],
                  'description' => $donnee[2],
            ),

            array(
                  '%d',
                  '%s',
                  '%s',
            )
         );

         if ( ! $reussite ) {
            william_log_debug( $wpdb->last_error );
         }

      }
   }

   $table_matable = $wpdb->prefix . 'william_livre';
   
   $sql = "CREATE TABLE $table_matable (
      id bigint(20) unsigned primary key auto_increment,
      categorie_id bigint(20) unsigned not null,
      titre varchar(255),
      auteur varchar(255),
      description varchar(255),
      nombre_page int
   ) $charset_collate;";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );

   $requete = "SELECT COUNT(*) FROM $table_matable";
   $presence_donnees = $wpdb->get_var( $requete );

   if ( is_null( $presence_donnees ) || $presence_donnees == 0) {
      $donnees = array(
         array( 1, 1, 'Bible', 'Dieu', 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quod, facere! Quas doloremque modi fugiat, dolor corporis magnam magni, et enim debitis, quam explicabo.', 666 ),
         array( 2, 2, 'Malfaisant', 'Gab', 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quod, facere! Quas doloremque modi fugiat, dolor corporis magnam magni, et enim debitis, quam explicabo.', 100 ),
         array( 3, 3, 'Le Horla', 'Maupassant', 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quod, facere! Quas doloremque modi fugiat, dolor corporis magnam magni, et enim debitis, quam explicabo.', 200 ),
         array( 4, 4, 'Le Web pour les Nuls', 'Un gars pas nul', 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Quod, facere! Quas doloremque modi fugiat, dolor corporis magnam magni, et enim debitis, quam explicabo.', 435 ),
         array( 5, 4, 'Le Web pour les très Nuls', 'Un gars nul', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam animi nostrum quas ipsum nemo perspiciatis provident numquam ex suscipit? Eaque, placeat.', 32 ),
         array( 6, 1, 'Le nouveau testament', 'Dieu', 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Labore sunt ratione, exercitationem cum quod alias est ex natus ea consequuntur repudiandae aliquam quos, consectetur fuga ipsum sed illum non commodi? Ad.', 999 ),
      );
      
      foreach( $donnees as $donnee ) {
         $reussite = $wpdb->insert(
            $table_matable,
            array(
                  'id' => $donnee[0],
                  'categorie_id' => $donnee[1],
                  'titre' => $donnee[2],
                  'auteur' => $donnee[3],
                  'description' => $donnee[4],
                  'nombre_page' => $donnee[5],
            ),

            array(
                  '%d',
                  '%d',
                  '%s',
                  '%s',
                  '%s',
                  '%d',
            )
         );

         if ( ! $reussite ) {
            william_log_debug( $wpdb->last_error );
         }

      }
   }
}

add_action( "after_switch_theme", "william_initialisation_bd" );

/**
 * Ajout de la table jaime lors du changement de theme
 * @author William Boudreault
 */
function william_ajouter_table_jaime() {
   global $wpdb;

   $charset_collate = $wpdb->get_charset_collate();
   $table_matable =  $wpdb->prefix . 'william_likes';

   $sql = "CREATE TABLE $table_matable (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      usager_id bigint(20) NOT NULL,
      date datetime NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate; ";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
   william_insert_table_jaime( $table_matable );
}

/**
 * Insertion des donnees dans la table jaime
 * @author William Boudreault
 */
function william_insert_table_jaime($table) {
   global $wpdb;

   $requete = "SELECT COUNT(*) FROM $table";
   $presence_donnees = $wpdb->get_var( $requete );

   if ( is_null( $presence_donnees ) || ! $presence_donnees ) {
      $donnees = array(
         array( 1, 1, '2021-10-04' ),
         array( 2, 1, '2021-10-04' ),
         array( 3, 1, '2021-10-04' ),
      );
      
      foreach( $donnees as $donnee ) {
         $reussite = $wpdb->insert(
            $table,
            array(
                  'id' => $donnee[0],
                  'usager_id' => $donnee[1],
                  'date' => $donnee[2],
            ),

            array(
                  '%d',
                  '%d',
                  '%s',
            )
         );

         if ( ! $reussite ) {
            william_log_debug( $wpdb->last_error );
         }

      }
   }
}

add_action( "after_switch_theme", "william_ajouter_table_jaime" );

/**
 * Affiche les utilisateurs qui ont aimer ainsi que la date de leur jaime
 * @author William Boudreault
 */
function william_afficher_jaimes() {
   global $wpdb;
   $output = '';
   $table_likes = $wpdb->prefix . 'william_likes';
   $table_users = $wpdb->prefix . 'users';

   $requete = "SELECT user_nicename, convert(DATE, date) as date_jaime FROM $table_likes INNER JOIN $table_users ON $table_users.id = usager_id ORDER BY date_jaime ASC;";
   $resultat = $wpdb->get_results( $requete );

   $erreur_sql = $wpdb->last_error;

   if ( $erreur_sql == "" ) {
      if ( $wpdb->num_rows > 0 ) {
         $output .= "<table>";
         $output .= "<tbody>";
         $output .= "<tr>";
         $output .= "<th>". __("Nom", "william") ."</th>";
         $output .= "<th>". __("Date", "william") ."</th>";
         $output .= "</tr>";
         foreach( $resultat as $enreg ) {
            $output .= "<tr>";
            $output .= "<td>$enreg->user_nicename</td>";
            $output .= "<td>$enreg->date_jaime</td>";
            $output .= "</tr>";
         }
         $output .= "</tbody>";
         $output .= "</table>";
      } else {
         $output .= '<div class="messageavertissement">';
         $output .= __( 'Aucune donnée ne correspond aux critères demandés.', "william");
         $output .= '</div>';
      }

   } else {
      $output .= '<div class="messageerreur">';
      $output .= __( 'Oups ! Un problème a été rencontré.', "william");
      $output .= '</div>';

      // afficher l'erreur à l'écran seulement si on est en mode débogage
      william_echo_debug( $erreur_sql );
   }

   return $output;
}

/**
 * Mets le style sur le shortcode afficher jaime 
 * @author William Boudreault
 */
function william_style_afficher_jaimes() {
   wp_enqueue_style( 'williamafficherjaimes', get_stylesheet_directory_uri() . '/css/william-afficher-table.css' );
}

add_shortcode('williamafficherjaimes', 'william_afficher_jaimes');
add_action('wp_enqueue_scripts', 'william_style_afficher_jaimes');

/**
 * Avertir l'usager qu'une maintenance du site est prévue prochainement.
 *
 * Utilisation : add_action( 'loop_start', 'william_avertir_maintenance' );
 *
 * @author Christiane Lagacé
 *
 */
function william_avertir_maintenance( $array ) {
   // on pourrait aussi travailler avec la base de données pour savoir quand un message doit être affiché ou non et pour retrouver le message à afficher.
   echo '<div class="messagegeneral">'. __('Attention : le 16 juin à 11h, des travaux d\'entretien seront effectués. Le site ne sera pas disponible pendant deux heures.') . '</div>';
};

// add_action( 'loop_start', 'william_avertir_maintenance' );


/**
 * Cree une page d'admin.
 * @author William Boudreault
 */
function william_creer_page_admin() {
   global $title;
   ?>
   <div class="wrap">
      <h1 class="wp-heading-inline"><?php echo $title; ?></h1>
      <?php
         echo william_afficheritems();
      ?> 
   </div>
   <?php
}

/**
 * Ajoute un menu a la page admin
 * 
 * @author William Boudreault
 */
function william_ajouter_menu_tableau_bord() {
   add_menu_page(
      __("William - Gestion", "william"),
      __("William", "william"),
      "manage_options",
      "william_gestion",
      "william_creer_page_admin"
   );
}

add_action( "admin_menu", "william_ajouter_menu_tableau_bord" );

/**
 * Affichage du formulaire de contact
 * 
 * @author William Boudreault
 */
function william_formulaire_contact() {
   $champCourriel = __("Courriel", "william");
   $champSujet = __("Sujet", "william");
   $champMessage = __("Message", "william");
   $output = "<form id='formulaireContact' action='". get_stylesheet_directory_uri(). "/envoyer-courriel-contact.php"."' method='post'>
                  <label for='courriel'>*$champCourriel:</label><br>
                  <input type='email' id='courriel' name='courriel' placeholder='exemple@courriel.abc'><br>
                  <label for='sujet'>*$champSujet:</label><br>
                  <input type='text' id='sujet' name='sujet' placeholder='Sujet'><br>
                  <label for='message'>*$champMessage:</label><br>
                  <textarea id='message' name='message'></textarea><br>
                  <input type='submit' name='soumissionFormContact' value='Soumettre'>
               </form>";
   return $output;
}

add_shortcode('williamformulairecontact', 'william_formulaire_contact');

/**
 * Active les variables de session.
 *
 * Utilisation : add_action( 'init', 'william_session_start', 1 );
 *
 * @author Christiane Lagacé
 *
 */
function william_session_start() {
   if ( ! session_id() ) {
      @session_start();
   }
}
 
add_action( 'init', 'william_session_start', 1 );

/**
* Configurer l'envoi de courriel par SMTP.
*
* Utilisation : add_action( 'phpmailer_init', 'monprefixe_configurer_courriel' );
* L'envoi de courriel ser fera comme suit :
* wp_mail( "destinataire@sondomaine.com", "Sujet", "Message" );
*
* @author Christiane Lagacé
*
*/
function william_configurer_courriel( $phpmailer ) {
   $phpmailer->isSMTP();
   $phpmailer->Host = SMTP_HOST;
   $phpmailer->SMTPAuth = SMTP_AUTH;
   $phpmailer->Port = SMTP_PORT;
   $phpmailer->SMTPSecure = SMTP_SECURE;
   $phpmailer->Username = SMTP_USERNAME;
   $phpmailer->Password = SMTP_PASSWORD;
   $phpmailer->From = SMTP_FROM;
   $phpmailer->FromName = SMTP_FROMNAME;
   $phpmailer->SMTPOptions = array(
      'ssl' => array(
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
      )
      );
}

add_action( 'phpmailer_init', 'william_configurer_courriel' );

/**
 * Logue les erreurs en cas de problème si WP_DEBUG est à true.
 *
 * Utilisation : add_action('wp_mail_failed', 'monprefixe_erreur_courriel', 10, 1);
 * Ceci appellera automatiquement cette fonction en cas d'erreur après avoir fait
 * wp_mail( "destinataire@sondomaine.com", "Sujet", "Message" );
 *
 * @author Christiane Lagacé
 *
 */
function william_erreur_courriel( $wp_error ) {
   william_log_debug( $wp_error );
}

add_action( 'wp_mail_failed', 'william_erreur_courriel', 10, 1 );

/**
 * Affiche le message si le formulare a reussie
 * 
 * @author William Boudreault
 */
function william_afficher_message_formulaire() {
   if ( isset( $_SESSION['reussite_courriel'] ) && isset( $_SESSION['message_courriel'] ) ) {
      if ( $_SESSION['reussite_courriel'] ) {
         echo "<div class='message-reussite'>";
      }
      else {
         echo "<div class='message-erreur'>";
      }
      echo $_SESSION['message_courriel'];
      echo "</div>";

      $_SESSION['message_courriel'] = null;
      $_SESSION['reussite_courriel'] = null;
   }

   if ( isset( $_SESSION['reussite_bd'] ) && isset( $_SESSION['message_bd'] ) ) {
      if ( $_SESSION['reussite_bd'] ) {
         echo "<div class='message-reussite'>";
      }
      else {
         echo "<div class='message-erreur'>";
      }
      echo $_SESSION['message_bd'];
      echo "</div>";

      $_SESSION['message_bd'] = null;
      $_SESSION['reussite_bd'] = null;
   }
}

add_shortcode('williamaffichermessageformulaire', 'william_afficher_message_formulaire');

/**
 * Charge les scripts du theme.
 * 
 * @author William Boudreault
 */
function william_charger_js() {
   global $post;

   // charge Google reCAPTCHA seulement si on est sur le formulaire de contact
   if ( has_shortcode( $post->post_content, 'williamformulairecontact') ) {
      wp_enqueue_script( 'script_formulaire_contact', get_stylesheet_directory_uri() . '/js/formulaire_script.js', array(), null, true );
      // charge l'API de Google reCAPTCHA
      wp_enqueue_script( 'apigooglerecaptcha', 'https://www.google.com/recaptcha/api.js?render=6LdyR_0cAAAAAIHvUfQUdWy8PbiVsFuphgL4u1O4' );

      // charge le code JavaScript pour gérer Google reCAPTCHA
      wp_enqueue_script( 'googlerecaptcha', get_stylesheet_directory_uri() . '/js/google-recaptcha.js' );
   }
}

add_action( 'wp_enqueue_scripts', 'william_charger_js' );