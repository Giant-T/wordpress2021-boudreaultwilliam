// il faut laisser le temps à WordPress de charger la page et plus particulièrement le shortcode qui affiche le formulaire
document.addEventListener("DOMContentLoaded", function(event) {
    // Variables du formulaire
    const courriel = document.getElementById('courriel');

    const sujet = document.getElementById('sujet');

    const message = document.getElementById('message');

    const formulaireContact = document.getElementById('formulaireContact');

    if (courriel != null && courriel.labels.length > 0) {
        courriel.onblur = () => {
            validerCourriel(courriel, courriel.labels[0]);
        };
    }

    if (sujet != null && sujet.labels.length > 0) {
        sujet.onblur = () => {
            validerChamp(sujet, sujet.labels[0], 50);
        };
    }

    if (message != null && message.labels.length > 0) {
        message.onblur = () => {
            validerChamp(message, message.labels[0], 500);
        };
    }

    if (formulaireContact != null) {
        formulaireContact.onsubmit = gererRecaptcha;
    }
});


/**
 * Validation du champ courriel du formulaire
 * 
 * @author William Boudreault
 * 
 * @param {input} champCourriel 
 * @param {label} labelCourriel 
 * 
 * @returns {boolean} True si le courriel est valide. | False si le courriel est invalide.
 */
function validerCourriel(champCourriel, labelCourriel) {
    if (champCourriel != null && labelCourriel != null) {
        const regexCourriel = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        champCourriel.value = champCourriel.value.toLowerCase();
        let valide = regexCourriel.test(String(champCourriel.value));
        if (!valide) {
            ajouterMessageErreur(champCourriel, labelCourriel, "Le courriel n'est pas valide. Format: exemple@courriel.abc");
        }
        else {
            retirerMessageErreur(champCourriel, labelCourriel);
        }
        return valide
    }
}

/**
 * Valide un champ de formulaire.
 * 
 * @author William Boudreault
 * 
 * @param {input} champVerif L'input a verifier.
 * @param {label} labelChamp Le label de l'input.
 * @param {int} longuerMax La longuer maximum du input.
 * 
 * @returns {boolean} True si le champ est valide. | False si le champ est invalide.
 */
function validerChamp(champVerif, labelChamp, longueurMax) {
    if (champVerif != null && labelChamp != null) {
        let valide = false;
        if (champVerif.value.length < 1) {
            ajouterMessageErreur(champVerif, labelChamp, "Le champ n'est pas rempli.");
        }
        else if (champVerif.value.length > longueurMax) {
            ajouterMessageErreur(champVerif, labelChamp, `Le champ doit comporter moins de ${longueurMax} caractères.`);
        }
        else {
            valide = true;
            retirerMessageErreur(champVerif, labelChamp);
        }
        return valide;
    }
}

/**
 * Ajoute un message d'erreur pour un formulaire ainsi que les classes d'erreur.
 * 
 * @author William Boudreault
 * 
 * @param {input} input L'input du formulaire.
 * @param {label} label Le label de l'input.
 * @param {string} message Le message d'erreur a afficher.
 */
function ajouterMessageErreur(input, label, message) {
    if (input != null && label != null) {
        input.classList.add("input-erreur");

        if (label.nextSibling.tagName != "SPAN") {
            let messageErreur = document.createElement('span');
            messageErreur.classList.add("label-erreur");
            messageErreur.innerText = message;
            label.parentNode.insertBefore(messageErreur, label.nextSibling);
        }
    }
}

/**
 * Retire le message d'erreur ainsi que les classes d'erreurs.
 * 
 * @author William Boudreault
 * 
 * @param {input} input L'input du formulaire.
 * @param {label} label Le label de l'input.
 */
function retirerMessageErreur(input, label) {
    if (input != null && label != null) {
        input.classList.remove("input-erreur");
        if (label.nextSibling.tagName == "SPAN") {
            label.parentNode.removeChild(label.nextSibling);
        }
    }
}

/**
 * Valide le formulaire de contact et empeche l'envoi s'il n'est pas complet.
 * 
 * @author William Boudreault
 */
function validerFormulaireContact() {
    const courrielValide = validerCourriel(courriel, courriel.labels[0]);
    const sujetValide = validerChamp(sujet, sujet.labels[0], 50);
    const messageValide = validerChamp(message, message.labels[0], 500);
    if (courrielValide && sujetValide && messageValide) {
        return true;
    }
    return false;
}

/**
 * Valide le captcha avec google recaptcha v3
 * 
 * @returns {boolean} True si le captcha est valide. | False si le captcha est invalide.
 */
function gererRecaptcha(event) {
    event.preventDefault();

    // note : ceci n'est pas du jQuery et fonctionnera avec en JavaScript pur
    grecaptcha.ready(function() {
        grecaptcha.execute('6LdyR_0cAAAAAIHvUfQUdWy8PbiVsFuphgL4u1O4', {action: 'soumissioncontact'}).then(function(token) {
            // ajout de la réponse de Google reCAPTCHA dans le formulaire
            let input = document.createElement('input');
            input.setAttribute("type", "hidden");
            input.setAttribute("name", "g-recaptcha-response");
            input.setAttribute("value", token);
            formulaireContact.appendChild(input);

            // soumission du formulaire
            if (!validerFormulaireContact()) {
                event.preventDefault();
            } else {   // ne cause pas de boucle sans fin puisqu'ici, aucun événement submit n'est déclenché (https://developer.mozilla.org/fr/docs/Web/API/HTMLFormElement/submit)       
                formulaireContact.submit();
            }
        });
    });
}