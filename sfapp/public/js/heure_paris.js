function mettreAJourHeureParis() {
    let maintenant = new Date();
    let options = { timeZone: 'Europe/Paris', hour12: false, hour: '2-digit', minute: '2-digit' };
    document.getElementById('heureParis').innerHTML = maintenant.toLocaleTimeString('fr-FR', options);
}

setInterval(mettreAJourHeureParis, 60000);
mettreAJourHeureParis();

