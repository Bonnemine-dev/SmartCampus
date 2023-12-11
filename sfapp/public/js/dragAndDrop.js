let originalColumnId = null;

// Sélection des boutons
const installationsButton = document.getElementById('installations-bouton');
const retraitsButton = document.getElementById('retraits-bouton');

// Sélection des divs à modifier
const installationsDiv = document.querySelector('.installations');
const retraitsDiv = document.querySelector('.retraits');


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
document.getElementById('retirees').addEventListener('drop', drop);
document.getElementById('retirees').addEventListener('dragover', allowDrop);

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

    if(targetColumn === "installees" && originalColumnId === "demandeInstallation") {
        document.getElementById('cardInfo').textContent = "Valider l'installation du " + nomSa + " dans la salle " + nomSalle + " ?";
        document.getElementById('modifyLink').href = "/technicien/modifier-etat-experimentation/installee/" + nomSalle;
        document.getElementById('overlay').style.display = 'flex';
    }
    else if(targetColumn === "retirees" && originalColumnId === "demandeRetrait") {
        document.getElementById('cardInfo').textContent = "Valider le retrait du " + nomSa + " de la salle " + nomSalle + " ?";
        document.getElementById('modifyLink').href = "/technicien/modifier-etat-experimentation/retiree/" + nomSalle;
        document.getElementById('overlay').style.display = 'flex';
    }
}

// Gérer la fermeture du popup
document.getElementById('cancelButton').addEventListener('click', function() {
    document.getElementById('overlay').style.display = 'none';
});

// ---------------------------------------------

// Scroll automatique vers la salle sélectionnée

function scrollToRoom(scrollId) {

    let elementCible = document.getElementById(scrollId);
    console.log("Scroll vers l'élément : ", elementCible, " - ", scrollId);

    if (elementCible) {
         // Ajustez si nécessaire
        let conteneur = elementCible.closest('.liste-experimentations');
        let autreConteneur = elementCible.closest('.retraits') || elementCible.closest('.installations');
        console.log(autreConteneur.classList)

        if (autreConteneur.classList.contains('retraits')) {
            retraitsDiv.classList.add('active');
            installationsDiv.classList.remove('active');
            retraitsButton.classList.add('active');
            installationsButton.classList.remove('active');
        }

        let offsetPosition =  conteneur.getBoundingClientRect().top + window.pageYOffset - 300;
        // Faire défiler vers la position du conteneur
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });

        conteneur.scrollTop = elementCible.offsetTop - conteneur.offsetTop; // Défiler vers la div

        // Ajouter la classe .blink-border et la retirer après 3 secondes
        elementCible.classList.add('blink-border');
        setTimeout(function() {
            setTimeout(function() {
                elementCible.classList.remove('blink-border');
            }, 3000);
        }, 1000);

    }
}

// Gérer le clic sur les boutons de switch
function switchButton() {

    // Gestion de l'événement de clic sur le bouton installations
    installationsButton.addEventListener('click', function() {
        installationsDiv.classList.add('active');
        retraitsDiv.classList.remove('active');
        installationsButton.classList.add('active');
        retraitsButton.classList.remove('active');
    });

    // Gestion de l'événement de clic sur le bouton retraits
    retraitsButton.addEventListener('click', function() {
        retraitsDiv.classList.add('active');
        installationsDiv.classList.remove('active');
        retraitsButton.classList.add('active');
        installationsButton.classList.remove('active');
    });
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
        scrollToRoom(scrollToId);
    }

    switchButton();
});
