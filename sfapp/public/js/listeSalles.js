function intervallesTempSaison() {
    let dateActuelle = new Date();
    let anneeActuelle = dateActuelle.getFullYear();
    let dateDebutHiverPrecedent = new Date(anneeActuelle - 1, 11, 22); // Mois en JS commence à 0
    let dateFinHiver = new Date(anneeActuelle, 2, 19, 23, 59, 59);
    let dateDebutPrintemps = new Date(anneeActuelle, 2, 20);
    let dateFinPrintemps = new Date(anneeActuelle, 5, 19);
    let dateDebutEte = new Date(anneeActuelle, 5, 20);
    let dateFinEte = new Date(anneeActuelle, 8, 22);
    let dateDebutAutomne = new Date(anneeActuelle, 8, 23);
    let dateFinAutomne = new Date(anneeActuelle, 11, 21);
    let dateDebutHiver = new Date(anneeActuelle, 11, 22);

    if ((dateActuelle >= dateDebutHiverPrecedent && dateActuelle <= dateFinHiver) ||
        (dateActuelle >= dateDebutHiver && dateActuelle <= new Date(anneeActuelle, 11, 31, 23, 59, 59))) {
        return [16, 18, 22, 24];
    }
    else if (dateActuelle >= dateDebutPrintemps && dateActuelle <= dateFinPrintemps) {
        return [18, 20, 24, 26];
    }
    else if (dateActuelle >= dateDebutEte && dateActuelle <= dateFinEte) {
        return [24, 26, 28, 30];
    }
    else if (dateActuelle >= dateDebutAutomne && dateActuelle <= dateFinAutomne) {
        return [17, 19, 23, 25];
    }
    return [];
}

const intervalleTemp = intervallesTempSaison();


document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/captures/liste/salles/avec/donnees')
        .then(response => response.json())
        .then(data => {
            data.forEach(salle => {
                updateSalleData(salle);
            });
        })
        .catch(error => console.error('Erreur:', error));
});

function updateSalleData(salle) {
    console.log(salle);
    // Mise à jour des données de l'humidité
    let humElement = document.getElementById(`hum_${salle.localisation}`);
    if (humElement) {
        humElement.innerHTML = `${salle.hum}%`;
        if (salle.hum === 100 || salle.hum === 0) {
            humElement.classList.add('text-red');
        }
        else if (salle.hum < 40 || salle.hum > 70) {
            humElement.classList.add('text-orange');
        }

        if (salle.hum === null || salle.hum === undefined || salle.hum === '' || salle.hum === 'nan') {
            humElement.innerHTML = '--%';
        }
    }

    // Mise à jour des données de la température
    let tempElement = document.getElementById(`temp_${salle.localisation}`);
    if (tempElement) {
        tempElement.innerHTML = `${salle.temp}°C`;
        if (salle.temp < intervalleTemp[0] || salle.temp > intervalleTemp[3]) {
            tempElement.classList.add('text-red');
        }
        else if (salle.temp < intervalleTemp[1] || salle.temp > intervalleTemp[2]) {
            tempElement.classList.add('text-orange');
        }

        if (salle.temp === null || salle.temp === undefined || salle.temp === '' || salle.temp === 'nan') {
            tempElement.innerHTML = '--°C';
        }
    }

    // Mise à jour des données du CO2
    let co2Element = document.getElementById(`co2_${salle.localisation}`);
    if (co2Element) {
        co2Element.innerHTML = `${salle.co2}ppm`;
        if (salle.co2 > 1500 || salle.co2 < 400 ) {
            co2Element.classList.add('text-red');
        }
        else if (salle.co2 >= 1000) {
            co2Element.classList.add('text-orange');
        }

        if (salle.co2 === null || salle.co2 === undefined || salle.co2 === '' || salle.co2 === 'nan') {
            co2Element.innerHTML = '--ppm';
        }
    }
}