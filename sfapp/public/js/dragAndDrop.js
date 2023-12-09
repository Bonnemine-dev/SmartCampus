var originalColumnId = null;

// Sélectionner toutes les cartes "souhait" et ajouter l'événement dragstart
document.querySelectorAll('.souhait').forEach(item => {
    item.addEventListener('dragstart', dragStart);
});

function dragStart(event) {
    event.dataTransfer.setData("text", event.target.id);
    // Stocker l'ID de la colonne parente
    originalColumnId = event.target.closest('.liste-experimentations').id;
}

// Ajouter les événements dragover et drop à la div "installees"
document.getElementById('installees').addEventListener('drop', drop);
document.getElementById('installees').addEventListener('dragover', allowDrop);

function allowDrop(event) {
    event.preventDefault();
}

function drop(event) {
    event.preventDefault();
    let data = event.dataTransfer.getData("text");
    let draggedElement = document.getElementById(data);

    // Récupérer les informations de la carte
    let nomSalle = draggedElement.querySelector('#nomsalle').textContent;
    let nomSa = draggedElement.querySelector('h5:not(#nomsalle)').textContent;

    // Utiliser closest pour obtenir toujours l'ID de la div .liste-experimentations
    let targetColumn = event.target.closest('.liste-experimentations').id;

    console.log(targetColumn, " - ",  originalColumnId," - ", nomSalle," - ", nomSa);

    if(targetColumn === "installees") {
        document.getElementById('cardInfo').textContent = "Valider l'installation du " + nomSa + " dans la salle " + nomSalle + " ?";
        document.getElementById('modifyLink').href = "/technicien/modifier-etat-experimentation/installee/" + nomSalle;
        document.getElementById('overlay').style.display = 'flex';
    }
}

// Gérer la fermeture du popup
document.getElementById('cancelButton').addEventListener('click', function() {
    document.getElementById('overlay').style.display = 'none';
});

// ---------------------------------------------

// Scroll automatique vers la salle sélectionnée

function scrollTo(scrollId) {
    let elementCible = document.getElementById(scrollId);
    if (elementCible) {
        let conteneur = elementCible.closest('.liste-experimentations'); // Ajustez si nécessaire
        conteneur.scrollTop = elementCible.offsetTop - conteneur.offsetTop; // Défiler vers la div
        //elementCible.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'end' });

        // Ajouter la classe .blink-border et la retirer après 3 secondes
        elementCible.classList.add('blink-border');
        setTimeout(function() {
            elementCible.classList.remove('blink-border');
        }, 3000);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    function getQueryParam(name) {
        const results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        }
        return decodeURI(results[1]) || 0;
    }

    let scrollToId = getQueryParam('scrollTo');

    if (scrollToId && document.getElementById(scrollToId)) {
        scrollTo(scrollToId);
    }
});
