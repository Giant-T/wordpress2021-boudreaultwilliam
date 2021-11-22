/**
 * Affiche la boîte de confirmation.
 * @author Christiane Lagacé <christianelagace.com>
 *
 * @param {String} question.
 * @param {String} href URL de la page vers laquelle le traitement sera transféré si on clique sur Oui.
 */
 function afficherPopup(question, href) {
    // au cas où une boîte aurait déjà été générée
    effacerBoite();

    const divOverlay = document.createElement("div");
    divOverlay.onclick = effacerSiClickEnDehors;
    divOverlay.classList.add("mon-popup-confirmation-overlay");


    const divBoite = document.createElement("div");
    divBoite.classList.add("mon-popup-confirmation");
    divOverlay.appendChild(divBoite);


    const divQuestion = document.createElement("div");
    divQuestion.innerHTML = question;
    divQuestion.classList.add("question");
    divBoite.appendChild(divQuestion);


    const boutonOui = document.createElement("button");
    boutonOui.innerHTML = "Oui";
    boutonOui.dataset.href = href;
    boutonOui.classList.add("button");
    boutonOui.classList.add("gauche");
    boutonOui.onclick = redirigerVersHref;
    divBoite.appendChild(boutonOui);


    const boutonNon = document.createElement("button");
    boutonNon.innerHTML = "Non";
    boutonNon.classList.add("button");
    boutonNon.classList.add("droite");
    boutonNon.onclick = effacerBoite;
    divBoite.appendChild(boutonNon);


    document.body.appendChild(divOverlay);
}


/**
* Retrouve l'attribut href du lien qui a mené à l'affichage de la boîte de confirmation.
* @author Christiane Lagacé <christianelagace.com>
*
* @param {Event} event.
*
* @return {string} Valeur de l'attribut href ou "" s'il n'y en avait pas.
*/
function retrouverHrefLien(event) {
    let href = "";
    const lien = event.target;


    if (lien != null) {
        href = lien.getAttribute("href") || "";
    }


    return href;
}


/**
* Redirige vers la page qui correspond à l'attribut href ou recharge la page actuelle si href est à blanc.
* @author Christiane Lagacé <christianelagace.com>
*
* @param {Event} event.
*/
function redirigerVersHref(event) {
    const bouton = event.target;
    let href = "";


    if (bouton != null) {
        // retrouve l'attribut data-href du bouton
        href = bouton.dataset.href || "";
    }


    if (href != "") {
        // affiche la page de l'attribut href
        window.location.href = href;
    }
    else {
        // réaffiche la page actuelle
        window.location.reload();
    }
}


/**
* Efface la boîte de confirmation
* @author Christiane Lagacé <christianelagace.com>
*/
function effacerBoite() {
    document.querySelectorAll('div.mon-popup-confirmation-overlay').forEach(e => e.remove());
}


/**
 * Efface la boîte de confirmation si l'usager clique sur le overlay en dehors de la division interne.
 * @author Christiane Lagacé <christianelagace.com>
 *
 * @param {Event} event.
 */
function effacerSiClickEnDehors(event) {
    const overlay = document.getElementsByClassName('mon-popup-confirmation-overlay');
    const popup = document.getElementsByClassName('mon-popup-confirmation');


    if (overlay.length > 0 && popup.length > 0) {
        // il ne peut jamais y avoir plus d'une boîte de confirmation puiqu'on détruit les existantes avant d'en créer une nouvelle
        if (event.target != popup[0]) {
            effacerBoite();
        }
    }
}