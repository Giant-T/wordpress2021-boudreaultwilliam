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
$home = get_page_by_title( 'Accueil' );
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
   if ( WP_DEBUG ) {
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
   if ( WP_DEBUG ) {
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
 * 
 * Utilisation : add_action('wp_head', 'william_monfavicon');
 * 
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
 * 
 * Utilisation : add_shortcode('williamafficheritems', 'william_afficher_items');
 * 
 * @author William Boudreault 
 */
function william_afficher_items() {
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
 * 
 * Utilisation : add_action('wp_enqueue_scripts', 'william_style_afficher');
 * 
 * @author William Boudreault
 */
function william_style_afficher() {
   wp_enqueue_style( 'williamafficheritems', get_stylesheet_directory_uri() . '/css/william-afficher-table.css' );
}

add_shortcode( 'williamafficheritems', 'william_afficher_items' );
add_action( 'wp_enqueue_scripts', 'william_style_afficher' );

/** 
 * Crée les tables et insère les données
 * 
 * Utilisation : add_action( "after_switch_theme", "william_initialisation_bd" );
 * 
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
   ) $charset_collate;";
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
 * Crée la table de province
 * 
 * Utilisation: add_action( 'after_switch_theme', 'william_initialisation_province' );
 * 
 * @author William Boudreault
 */
function william_initialisation_province() {
   global $wpdb;

   $charset_collate = $wpdb->get_charset_collate();
   $table_province = $wpdb->prefix . 'william_province';
   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

   $sql = "CREATE TABLE $table_province (
      id bigint(20) unsigned primary key auto_increment,
      nom varchar(255) not null
   ) $charset_collate;";
   dbDelta( $sql );

   $requete = "SELECT COUNT(*) FROM $table_province";
   $presence_donnees = $wpdb->get_var( $requete );

   if ( is_null( $presence_donnees ) || $presence_donnees == 0) {
      $donnees = array(
         array( 1, 'Québec' ),
         array( 2, 'Ontario' ),
      );
      
      foreach( $donnees as $donnee ) {
         $reussite = $wpdb->insert(
            $table_province,
            array(
                  'id' => $donnee[0],
                  'nom' => $donnee[1],
            ),

            array(
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

add_action( 'after_switch_theme', 'william_initialisation_province' );

/**
 * Crée la table de ville
 * 
 * Utilisation: add_action( 'after_switch_theme', 'william_initialisation_ville' );
 * 
 * @author William Boudreault
 */
function william_initialisation_ville() {
   global $wpdb;

   $charset_collate = $wpdb->get_charset_collate();
   $table_ville = $wpdb->prefix . 'william_ville';
   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

   $sql = "CREATE TABLE $table_ville (
      id bigint(20) unsigned primary key auto_increment,
      nom varchar(255) not null,
      province bigint(20) unsigned not null
   ) $charset_collate;";
   dbDelta( $sql );

   $requete = "SELECT COUNT(*) FROM $table_ville";
   $presence_donnees = $wpdb->get_var( $requete );

   if ( is_null( $presence_donnees ) || $presence_donnees == 0) {
      $donnees = array(
         array( 1, 'Québec', 1 ),
         array( 2, 'Victoriaville', 1 ),
         array( 3, 'Montréal', 1 ),
         array( 4, 'Toronto', 2 ),
         array( 5, 'Otawa', 2 ),
         array( 6, 'Ajax', 2),
      );
      
      foreach( $donnees as $donnee ) {
         $reussite = $wpdb->insert(
            $table_ville,
            array(
                  'id' => $donnee[0],
                  'nom' => $donnee[1],
                  'province' => $donnee[2],
            ),

            array(
                  '%d',
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

add_action( 'after_switch_theme', 'william_initialisation_ville' );

/**
 * Crée la table d'inscription
 * 
 * Utilisation: add_action() 
 * 
 * @author William Boudreault
 */
function william_initialisation_inscription() {
   global $wpdb;

   $charset_collate = $wpdb->get_charset_collate();
   $table_inscription = $wpdb->prefix . 'william_inscription';
   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

   $sql = "CREATE TABLE $table_inscription (
      id bigint(20) unsigned primary key auto_increment,
      nomfamille varchar(255) not null,
      prenom varchar(255) not null,
      addresse varchar(255) not null,
      ville bigint(20) unsigned not null,
      telephone varchar(12) not null,
      courriel varchar(255) not null,
      addresseip varchar(16) not null
   ) $charset_collate;";
   dbDelta( $sql );
}

add_action( 'after_switch_theme', 'william_initialisation_inscription' );

/**
 * Shortcode du formulaire d'inscription
 * 
 * Utilisation: add_shortcode('williamformulaireinscription', 'william_formulaire_inscription')
 * 
 * @author William Boudreault
 */
function william_formulaire_inscription() {
   $code_html = '';
   $url_action = get_stylesheet_directory_uri() . '/enregistrer-inscription.php';
   $code_html .= "<form class='no-print' method='POST' action='$url_action' id='formulaireinscription'>";
   $code_html .= "<label for='prenom'>". __( 'Prénom', 'william' ) .":*</label><br>";
   $code_html .= "<input type='text' required id='prenom' name='prenom' maxlength='255'><br>";
   $code_html .= "<label for='nomfamille'>". __( 'Nom de famille', 'william' ) .":*</label><br>";
   $code_html .= "<input type='text' required id='nomfamille' name='nomfamille' maxlength='255'><br>";
   $code_html .= "<label for='addresse'>". __( 'Adresse', 'william' ) .":*</label><br>";
   $code_html .= "<input type='text' required id='addresse' name='addresse' maxlength='255' placeholder=\"123 rue de l'Importance\"><br>";
   $code_html .= "<label for='province'>". __('Province', 'william' ) .":*</label><br>";
   $code_html .= "<select id='province' name='province' >";
   global $wpdb;
   $table_province = $wpdb->prefix . 'william_province'; 
   $requete = "SELECT id, nom FROM $table_province ORDER BY nom ASC";
   $resultat = $wpdb->get_results( $requete );

   $erreur_sql = $wpdb->last_error;
   if ( $erreur_sql == "" ) {
      if ( $wpdb->num_rows > 0 ){
         $code_html .= "<option value='' selected>" . __( "Veuillez selectionner une province...", 'william' ) . "</option>";
         foreach( $resultat as $enreg ) {
            $code_html .=  "<option value='$enreg->id'>$enreg->nom</option>";
         }
      }
      else {
         $code_html .= "<option value='' selected>" . __( "Aucune donnée est disponible", 'william' ) . "</option>";
      }
   }
   else { 
      $code_html .= "<option value='' selected>" . __( "Accès aux données impossible.", 'william' ) . "</option>";
   }
   $code_html .= "</select><br>";
   $code_html .= "<label for='ville'>". __('Ville', 'william' ) .":*</label><br>";
   $code_html .= "<select id='ville' name='ville' >";
   $code_html .= "<option value='' selected>". __('Aucune ville.', 'william' ). "</option>";
   $code_html .= "</select><br>";
   $code_html .= "<label for='telephone'>". __( 'Numéro de téléphone', 'william' ) .":*</label><br>";
   $code_html .= "<input type='text' required id='telephone' name='telephone' maxlength='12' placeholder='123 456 7891'><br>";
   $code_html .= "<label for='courriel'>". __( 'Addresse courriel', 'william' ) .":*</label><br>";
   $code_html .= "<input type='text' required id='courriel' name='courriel' maxlength='255' placeholder='example@courriel.com'><br><br>";
   $code_html .= "<input type='submit' name='soumettre_inscription' value='". __( 'Soumettre', 'william' )."'>";
   $code_html .= "</form>";
   return $code_html;
}

add_shortcode('williamformulaireinscription', 'william_formulaire_inscription');


/**
 * Affiche le message si l'inscription a reussie
 * 
 * Utilisation : add_shortcode('williamaffichermessageinscription', 'william_afficher_message_inscription');
 * 
 * @author William Boudreault
 */
function william_afficher_message_inscription() {
   if ( isset( $_SESSION['william_ajout_inscription_reussi'] ) && isset( $_SESSION['william_message_inscription'] ) ) {
      if ( $_SESSION['william_ajout_inscription_reussi'] ) {
         echo "<div class='message-reussite'>";
      }
      else {
         echo "<div class='message-erreur'>";
      }
      echo $_SESSION['william_message_inscription'];
      echo "</div>";

      $_SESSION['william_message_inscription'] = null;
      $_SESSION['william_ajout_inscription_reussi'] = null;
   }
}

add_shortcode( 'williamaffichermessageinscription', 'william_afficher_message_inscription' );

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
   echo '<div class="messagegeneral">'. __( 'Attention : le 16 juin à 11h, des travaux d\'entretien seront effectués. Le site ne sera pas disponible pendant deux heures.' ) . '</div>';
};

// add_action( 'loop_start', 'william_avertir_maintenance' );

/**
 * Cree une page d'admin.
 * 
 * Utilisation : add_menu_page(
 *     __("William - Gestion", "william"),
 *     __("William", "william"),
 *     "manage_options",
 *     "william_gestion",
 *     "william_creer_page_admin"
 *  );
 * 
 * @author William Boudreault
 */
function william_creer_page_admin() {
   global $title;
   
   if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
      if ( isset( $_GET['id'] ) ) {
         $id = $_GET['id'];
         william_creer_page_edition($id);
      }
      else {
         echo '<div class="notice notice-warning"><p>';
         _e( "La provenance du lien d'édition semble poser problème.", "william" );
         echo '</p></div>';
      }
   }
   else {
      ?>
      <div class="wrap">
      <h1 class="wp-heading-inline"><?php echo $title; ?></h1>
      <?php
      $url_ajout = admin_url( "admin.php?page=william_ajout" );
      echo "<a href='$url_ajout' class='page-title-action'>". __( "Ajouter", 'william') . "</a>";
      echo "<hr class='wp-header-end'>";
      echo william_afficher_items_admin();
   }
   ?> 
   </div>
   <?php
}

/**
* Charge des scripts et des feuilles de style dans toutes les pages du tableau de bord.
*
* Utilisation : add_action('admin_enqueue_scripts', 'william_charger_css_js_admin');
*
* @author Christiane Lagacé
*
*/
function william_charger_css_js_admin( $hook ) {

   global $william_hook_gestion; // variable initialisée lors de l'ajout de l'option de menu

   // fichiers à charger seulement pour la page de gestion de mon thème
   if ( $hook == $william_hook_gestion ) {

       wp_enqueue_style( 'ma-bibliotheque-popup', get_stylesheet_directory_uri() . '/ma-bibliotheque-popup/style.css' );
       wp_enqueue_script( 'ma-bibliotheque-popup', get_stylesheet_directory_uri() . '/ma-bibliotheque-popup/script.js', null, null, true );
       wp_enqueue_script( 'confirmation-suppression', get_stylesheet_directory_uri() . '/js/confirmation-suppression.js', array( 'ma-bibliotheque-popup' ), null, true );
   }
}

add_action( 'admin_enqueue_scripts', 'william_charger_css_js_admin' );

/**
 * Crée la page d'édition de donnée.
 * 
 * Utilisation : william_creer_page_edition($id);
 * 
 * @author William Boudreault
 */
function william_creer_page_edition( $id ) {
   $url_action = get_stylesheet_directory_uri() . '/mise-a-jour-item.php';
   global $title;
   global $wpdb;
   $table_livre = $wpdb->prefix . 'william_livre';
   $requete = $wpdb->prepare( "SELECT titre, categorie_id, auteur, description, nombre_page FROM $table_livre WHERE id = %d;", $id );
   $livre = $wpdb->get_row( $requete );

   $erreur_sql = $wpdb->last_error;
   if ( $erreur_sql == "" && $wpdb->num_rows > 0 )
   {
      ?>
      <div class="wrap">
         <h1 class="wp-heading-inline"><?php echo $title; ?></h1>
         <form method="post" id="formulaireModif" class="no-print" action="<?php echo $url_action; ?>">
            <?php wp_nonce_field( "modifier_item_bd_$id", 'nonce_valide', true ); ?>
            <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
            <label for="titre"><?php _e( 'Titre', 'william' ); ?>:</label><br>
            <input type="text" id="titre" name="titre" required value="<?php echo $livre->titre; ?>" maxlength="255"><br>
            <label for="categorie"><?php _e( 'Categorie', 'william' ); ?>:</label><br>
            <select id="categorie" name="categorie" required>
            <?php
            $table_categorie = $wpdb->prefix . 'william_categorie'; 
            $requete = "SELECT id, titre FROM $table_categorie ORDER BY titre ASC";
            $resultat = $wpdb->get_results( $requete );
            $erreur_sql = $wpdb->last_error;
            if ( $erreur_sql == "" ) {
               if ( $wpdb->num_rows > 0 ){
                  foreach( $resultat as $enreg ) {
                     echo "<option value='$enreg->id' "; 
                     if ( $enreg->id == $livre->categorie_id ) { echo 'selected'; } 
                     echo ">$enreg->titre</option>";
                  }
               }
               else {
                  echo "<option value='' selected>" . __( "Aucune donnée est disponible", 'william') . "</option>";
               }
            }
            else { 
               echo "<option value='' selected>" . __( "Accès aux données impossible.", 'william' ) . "</option>";
            }
            ?>
            </select><br>
            <label for="auteur"><?php _e( 'Auteur', 'william' ) ?>:</label><br>
            <input type="text" id="auteur" name="auteur" required value="<?php echo $livre->auteur;?>" maxlength="255"><br>
            <label for="description"><?php _e( 'Description', 'william' ); ?>:</label><br>
            <textarea id="description" name="description" required maxlength="255"><?php echo esc_attr( $livre->description ); ?></textarea><br>
            <label for="nombrePage"><?php _e( 'Nombre de pages', 'william' ) ?> (1, 10000):</label><br>
            <input type="number" id="nombrePage" name="nombrePage" required min='1' max="10000" value='<?php echo $livre->nombre_page; ?>'><br><br>
            <input type="submit" name="soumettre" value="Submit">
         </form>
      </div>
      <?php
   }
   else {
      echo '<div class="notice notice-warning"><p>';
      _e( "Impossible d'afficher les données de la base de données.", "william" );
      echo '</p></div>';
   }
}

/**
 * Fonction qui affiche les items pour le menu admin
 * 
 * Utilisation : echo william_afficher_items_admin();
 * 
 * @author William Boudreault 
 */
function william_afficher_items_admin() {
   global $wpdb;
   $output = '';
   $table_livre = $wpdb->prefix . 'william_livre';
   $table_categorie = $wpdb->prefix . 'william_categorie';
   $requete = "SELECT l.titre AS titre_livre, auteur, c.titre AS categorie, nombre_page, l.id as id_livre FROM $table_livre l INNER JOIN $table_categorie c ON c.id = l.categorie_id ORDER BY l.nombre_page ASC";
   $resultat = $wpdb->get_results( $requete );
   $erreur_sql = $wpdb->last_error;

   if ( $erreur_sql == "" ) {
      if ( $wpdb->num_rows > 0 ) {
         $output .= "<table class='wp-list-table widefat fixed striped table-view-list'>";
         $output .= '<thead>';
         $output .= "<tr>";
         $output .= "<th>". __( "Titre", "william" ) ."</th>";
         $output .= "<th>". __( "Auteur", "william" ) ."</th>";
         $output .= "<th>". __( "Categorie", "william" ) ."</th>";
         $output .= "<th>". __( "Nombre de pages", "william" ) ."</th>";
         $output .= "</tr>";
         $output .= '</thead>';
         $output .= "<tbody>";
         foreach( $resultat as $enreg ) {
            $output .= "<tr>";
            $url_suppression = get_stylesheet_directory_uri() . "/suppression-item.php?id=$enreg->id_livre";
            $url_suppression_protege = wp_nonce_url( $url_suppression, "supprimer_item_$enreg->id_livre" );
            $output .= "<td class='title column-title has-row-actions column-primary page-title'>$enreg->titre_livre<div class='row-actions'> <span class='edit'><a href='". admin_url( "admin.php?page=william_gestion&id=$enreg->id_livre&action=edit" ) ."'>". __( 'Modifier', 'william' ) ."</a></span> | <span class='trash'><a href='". $url_suppression_protege ."' class='submitdelete'>". __( 'Supprimer', 'william' ) ."</a></span></div></td>";
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
 * Crée la page d'ajout d'item.
 * 
 * Utilisation : add_submenu_page( 'william_gestion', __("William - Ajouter Items", "william"), __("Ajouter items", 'william'), "manage_options", "william_ajout", "william_creer_page_ajout" );
 * 
 * @author William Boudreault
 */
function william_creer_page_ajout() {
   global $title;
   $url_action = get_stylesheet_directory_uri() . '/enregistrer-item.php';
   ?>
   <div class="wrap">
      <h1 class="wp-heading-inline"><?php echo $title; ?></h1>
      <form method="post" class="no-print" id="formulaireItem" action=" <?php echo $url_action; ?>" >
         <?php wp_nonce_field( 'ajouter_item_bd', 'validite_nonce', true ); ?>
         <label for="titre"><?php _e( 'Titre', 'william' ) ?>:</label><br>
         <input type="text" id="titre" name="titre" required placeholder='<?php _e( 'Titre', 'william' ) ?>' maxlength="255"><br>
         <label for="categorie"><?php _e('Categorie', 'william') ?>:</label><br>
         <select id="categorie" name="categorie" required>
            <?php
            global $wpdb;
            $table_categorie = $wpdb->prefix . 'william_categorie'; 
            $requete = "SELECT id, titre FROM $table_categorie ORDER BY titre ASC";
            $resultat = $wpdb->get_results( $requete );

            $erreur_sql = $wpdb->last_error;
            if ( $erreur_sql == "" ) {
               if ( $wpdb->num_rows > 0 ){
                  echo "<option value='' selected>" . __( "Veuillez selectionner une catégorie...", 'william' ) . "</option>";
                  foreach( $resultat as $enreg ) {
                     echo "<option value='$enreg->id'>$enreg->titre</option>";
                  }
               }
               else {
                  echo "<option value='' selected>" . __( "Aucune donnée est disponible", 'william' ) . "</option>";
               }
            }
            else { 
               echo "<option value='' selected>" . __( "Accès aux données impossible.", 'william' ) . "</option>";
            }
            ?>
         </select><br>
         <label for="auteur"><?php _e( 'Auteur', 'william' ) ?>:</label><br>
         <input type="text" id="auteur" name="auteur" required placeholder='<?php _e( 'Auteur', 'william' ) ?>' maxlength="255"><br>
         <label for="description"><?php _e( 'Description', 'william' ) ?>:</label><br>
         <textarea id="description" name="description" required maxlength="255"></textarea><br>
         <label for="nombrePage"><?php _e( 'Nombre de pages', 'william' ) ?> (1, 10000):</label><br>
         <input type="number" id="nombrePage" name="nombrePage" required min='1' max="10000" value='1'><br><br>
         <input type="submit" name="soumettre" value="Submit">
      </form>
   </div>
   <?php
}

/**
 * Affiche un message indiquant que l'item a été ajouté avec succès, seulement si la variable de session existe.
 *
 * Utilisation : add_action( 'admin_notices', 'william_message_ajout_item_reussi' );
 *
 * @author Christiane Lagacé
 *
 */
function william_message_ajout_item_reussi() {
   if ( isset( $_SESSION['william_ajout_reussi'] ) && $_SESSION['william_ajout_reussi'] ) {
      echo '<div class="notice notice-success is-dismissable"><p>';
      _e( "L'item a été ajouté avec succès !", "william" );
      echo '</p></div>';
 
      // supprime la variable de session pour ne pas que le message soit affiché à nouveau
      $_SESSION['william_ajout_reussi'] = null;
   }
}
 
add_action( 'admin_notices', 'william_message_ajout_item_reussi' );

/**
 * Affiche un message indiquant que l'item a été modifié avec succès
 * 
 * Utilisation : add_action( 'admin_notices', 'william_message_modification_item_reussi' );
 * 
 * @author William Boudreault
 */
function william_message_modification_item_reussi() {
   if ( isset( $_SESSION['william_mise_a_jour_item_reussie'] ) && isset( $_SESSION['william_erreur_mise_a_jour_item'] ) ) {
      if ( $_SESSION['william_mise_a_jour_item_reussie'] ) {
         echo '<div class="notice notice-success is-dismissable"><p>';
         _e( "L'item a été modifié avec succès !", "william" );
      }
      else {
         echo '<div class="notice notice-warning is-dismissable"><p>';
         echo $_SESSION['william_erreur_mise_a_jour_item'];
      }
      echo '</p></div>';

      // supprime la variable de session pour ne pas que le message soit affiché à nouveau
      $_SESSION['william_mise_a_jour_item_reussie'] = null;
      $_SESSION['william_erreur_mise_a_jour_item'] = null;
   }
}

add_action( 'admin_notices', 'william_message_modification_item_reussi' );

/**
 * Affiche un message indiquant que l'item a été supprimé avec succès
 * 
 * Utilisation : add_action( 'admin_notices', 'william_message_suppression_item_reussi' );
 * 
 * @author William Boudreault
 */
function william_message_suppression_item_reussi() {
   if ( isset( $_SESSION['william_suppression_item_reussie'] ) && $_SESSION['william_suppression_item_reussie'] ) {
      echo '<div class="notice notice-success is-dismissable"><p>';
      _e( "L'item a été supprimé avec succès !", "william" );
      echo '</p></div>';
 
      // supprime la variable de session pour ne pas que le message soit affiché à nouveau
      $_SESSION['william_suppression_item_reussie'] = null;
   }
}
 
add_action( 'admin_notices', 'william_message_suppression_item_reussi' );

/**
 * Ajoute un menu a la page admin
 * 
 * Utilisation : add_action( "admin_menu", "william_ajouter_menu_tableau_bord" );
 * 
 * @author William Boudreault
 */
function william_ajouter_menu_tableau_bord() {
   global $william_hook_gestion;

   $william_hook_gestion = add_menu_page(
      __( "William - Gestion", "william" ),
      __( "William", "william" ),
      "manage_options",
      "william_gestion",
      "william_creer_page_admin"
   );

   add_submenu_page(
      'william_gestion',
      __( "William - Ajouter Items", "william" ),
      __( "Ajouter items", 'william' ),
      "manage_options",
      "william_ajout",
      "william_creer_page_ajout"
   );
}

add_action( "admin_menu", "william_ajouter_menu_tableau_bord" );

/**
 * Affichage du formulaire de contact
 * 
 * Utilisation : add_shortcode('williamformulairecontact', 'william_formulaire_contact');
 * 
 * @author William Boudreault
 */
function william_formulaire_contact() {
   $champCourriel = __( "Courriel", "william" );
   $champSujet = __( "Sujet", "william" );
   $champMessage = __( "Message", "william" );
   $output = "<form id='formulaireContact' class='no-print' action='". get_stylesheet_directory_uri(). "/envoyer-courriel-contact.php"."' method='post'>
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

add_shortcode( 'williamformulairecontact', 'william_formulaire_contact' );

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
 * Utilisation : add_shortcode('williamaffichermessageformulaire', 'william_afficher_message_formulaire');
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

add_shortcode( 'williamaffichermessageformulaire', 'william_afficher_message_formulaire' );

/**
 * Charge les scripts du theme.
 * 
 * Utilisation : add_action( 'wp_enqueue_scripts', 'william_charger_js' );
 * 
 * @author William Boudreault
 */
function william_charger_js() {
   global $post;

   // charge Google reCAPTCHA seulement si on est sur le formulaire de contact
   if ( has_shortcode( $post->post_content, 'williamformulairecontact' ) ) {
      wp_enqueue_script( 'script_formulaire_contact', get_stylesheet_directory_uri() . '/js/formulaire_script.js', array(), null, true );
      // charge l'API de Google reCAPTCHA
      wp_enqueue_script( 'apigooglerecaptcha', 'https://www.google.com/recaptcha/api.js?render=6LdyR_0cAAAAAIHvUfQUdWy8PbiVsFuphgL4u1O4' );
   }
   elseif ( has_shortcode( $post->post_content, 'williamformulaireinscription' ) ) {
      wp_enqueue_script( 'williamformulaireinscription', get_stylesheet_directory_uri() . '/js/formulaire_inscription.js', array(), null, true );
      wp_localize_script( 'williamformulaireinscription', 'variablesPHP', ['urlThemeEnfant' => get_stylesheet_directory_uri()] );
   }
}

add_action( 'wp_enqueue_scripts', 'william_charger_js' );