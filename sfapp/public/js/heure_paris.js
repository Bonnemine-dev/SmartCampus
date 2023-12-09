function mettreAJourHeureParis() {
    var maintenant = new Date();
    var options = { timeZone: 'Europe/Paris', hour12: false };
    var heureParis = maintenant.toLocaleTimeString('fr-FR', options);
    document.getElementById('heureParis').innerHTML = heureParis;
}

setInterval(mettreAJourHeureParis, 1000); // Mettre à jour chaque seconde
mettreAJourHeureParis(); // Mettre à jour immédiatement
