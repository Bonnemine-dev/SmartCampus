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
        if (radio.id === 'filtre_salle_form_ordinateurs_0' || radio.id === 'filtre_salle_form_sa_0') {
            radio.checked = true;
        }
    });
});
