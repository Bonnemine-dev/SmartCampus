// Fonction pour ouvrir le popup
function ouvrirPopupFiltreSalles() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('popup').style.display = 'block';
}

// Fonction pour fermer le popup
function fermerPopupFiltreSalles() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('popup').style.display = 'none';
}

document.getElementById('resetButton').addEventListener('click', function() {
    // Ciblez toutes les cases à cocher et les boutons radio du formulaire
    var checkboxes = document.querySelectorAll('.popup-filtre-salles input[type="checkbox"]');
    var radios = document.querySelectorAll('.popup-filtre-salles input[type="radio"]');

    // Décochez toutes les cases à cocher
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;
    });

    // Réinitialiser les boutons radio à une valeur par défaut si nécessaire
    radios.forEach(function(radio) {
        // Décochez ou réinitialisez les boutons radio ici si nécessaire
        radio.checked = false;
    });
});
