/*document.querySelectorAll('.souhait').forEach(item => {
    item.addEventListener('dragstart', dragStart);
});

function dragStart(event) {
    event.dataTransfer.setData("text", event.target.id);
}

document.getElementById('installees').addEventListener('drop', drop);
document.getElementById('installees').addEventListener('dragover', allowDrop);

function allowDrop(event) {
    event.preventDefault();
}

function drop(event) {
    event.preventDefault();
    var data = event.dataTransfer.getData("text");
    // event.target.appendChild(document.getElementById(data));

    console.log(data);

    // Afficher la popup avec les informations de la carte
    var cardText = document.getElementById(data).textContent;
    document.getElementById('cardInfo').textContent = cardText;
    document.getElementById('modifyLink').href = "/modifier-etat-experimentation/installee/" + cardText;
    //document.getElementById('overlay').style.display = 'flex';
}

// Fermer le popup
document.getElementById('cancelButton').addEventListener('click', function() {
    //document.getElementById('overlay').style.display = 'none';
});*/

// Sélectionner toutes les cartes "souhait" et ajouter l'événement dragstart
document.querySelectorAll('.souhait').forEach(item => {
    item.addEventListener('dragstart', dragStart);
});

function dragStart(event) {
    event.dataTransfer.setData("text", event.target.id);
}

// Ajouter les événements dragover et drop à la div "installees"
document.getElementById('installees').addEventListener('drop', drop);
document.getElementById('installees').addEventListener('dragover', allowDrop);

function allowDrop(event) {
    event.preventDefault();
}

function drop(event) {
    event.preventDefault();
    var data = event.dataTransfer.getData("text");
    var draggedElement = document.getElementById(data);

    // Récupérer les informations de la carte et les afficher dans le popup
    var nomSalle = draggedElement.querySelector('#nomsalle').textContent;
    var nomSa = draggedElement.querySelector('h5:not(#nomsalle)').textContent;

    console.log(event.target.id);
    if(event.target.id === "installees") {
        document.getElementById('cardInfo').textContent = "Valider l'installation du " + nomSa + " dans la salle " + nomSalle + " ?";
        document.getElementById('modifyLink').href = "/modifier-etat-experimentation/installee/" + nomSalle;
        document.getElementById('overlay').style.display = 'flex';
    }
}

// Gérer la fermeture du popup
document.getElementById('cancelButton').addEventListener('click', function() {
    document.getElementById('overlay').style.display = 'none';
});

