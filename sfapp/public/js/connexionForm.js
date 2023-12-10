
const formOverlay = document.getElementById('overlay');

function afficherFormTechnicien() {
    document.getElementById('username').value = 'technicien';
    document.querySelector('.connexion-form h2').innerHTML = 'technicien';
    document.getElementById('target-path').value = '/technicien/liste-souhaits';
    // Afficher le pop-up
    formOverlay.style.display = 'flex';
}

function afficherFormChargeMission() {
    document.getElementById('username').value = 'chargemission';
    document.querySelector('.connexion-form h2').innerHTML = 'charge de mission';
    document.getElementById('target-path').value = '/charge-de-mission/plan-experimentation';
    // Afficher le pop-up
    formOverlay.style.display = 'flex';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.connexion-form').addEventListener('click', function(e) {
        e.stopPropagation(); // Empêche l'événement de se propager à l'overlay
    });

    formOverlay.addEventListener('click', function() {
        formOverlay.style.display = 'none';
    });
});
