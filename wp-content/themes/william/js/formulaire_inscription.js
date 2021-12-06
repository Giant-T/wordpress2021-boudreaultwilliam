const url = variablesPHP.urlThemeEnfant + '/initialiser-villes.php';

province = document.getElementById('province');
ville = document.getElementById('ville');

if (province != null && ville != null) {
    province.onchange = appelAjax;
}

/**
 * Fonction qui effectue l'appel Ajax
 * 
 * @author William Boudreault
 * 
 * @param {Event} event L'évènement qui déclenche l'appel
 */
function appelAjax(event) {
    fetch(`${url}?id_province=${province.value}`
    ).then((response) => {
        if (!response.ok) {
            throw new Error(`Problème - code d'état HTTP: ${response.status}`);
        }
        return response.json();
    }).then((body) => {
        retirerOptions(ville);
        remplirOptions(ville, body);
    }).catch((e) => {
        console.log(e.toString());
        divReponse.innerHTML = "Un problème nous empêche de compléter le traitement demandé.";
    });
}

/**
 * Ajoute les informations obtenues lors de la requete
 * 
 * @author William Boudreault
 * 
 * @param {JSON} jsonArray Les informations obtenues
 * @param {HTMLSelectElement} select Le select auquel ajouter les informations
 */
function remplirOptions(select, jsonArray) {
    for (let i = 0; i < jsonArray.length; i++) {
        let obj = jsonArray[i];
        let option = document.createElement('option');
        option.value = obj.id;
        option.innerText = obj.nom;
        select.appendChild(option);
    }
    select.firstChild.selected = true;
}

/**
 * Retire les options d'un select
 * 
 * @author William Boudreault
 * 
 * @param {HTMLSelectElement} select Le select du formulaire
 */
function retirerOptions(select) {
    while(select.options.length > 0) {
        select.remove(0);
    }
}