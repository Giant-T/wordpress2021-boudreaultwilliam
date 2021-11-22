// associe la fonction confirmerSuppression() au clic sur un lien
document.addEventListener("DOMContentLoaded", function(event) {
    // l'événement doit provenir de la balise <a> qui contient l'attribut href
    // ajuster le sélecteur selon votre projet
    let liensSuppression = document.querySelectorAll(".trash a");


    Array.from(liensSuppression).forEach(function (lienSuppression) {
        lienSuppression.onclick = confirmerSuppression;
    });
});

/**
 * Fait apparaître une boîte de confirmation.
 * @author Christiane Lagacé <christianelagace.com>
 *
 * @param {Event} event.
 *
 * @return {type} Return value description.
 */

function confirmerSuppression(event) {
    // Aucune soumission tant que l'usager n'a pas confirmé.
    event.preventDefault();

    const href = retrouverHrefLien(event);


    afficherPopup("Désirez-vous vraiment supprimer cet item?", href);
}