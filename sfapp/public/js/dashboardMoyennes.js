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
    loadData('temp');
    loadData('hum');
    loadData('co2');
});

function loadData(type) {
    fetch(`/api/captures/moyenne/par/type/${type}`)
        .then(response => response.json())
        .then(data => {
            let element = document.getElementById(`${type}_moy`);
            if (element) {
                element.innerHTML = "";
                element.innerHTML = `${data} ${type === 'temp' ? '°C' : type === 'hum' ? '%' : 'ppm'}`;

                if (type === 'temp') {
                    if (data < intervalleTemp[0] || data > intervalleTemp[3]) {
                        element.classList.add('text-red');
                    }
                    else if (data < intervalleTemp[1] || data > intervalleTemp[2]) {
                        element.classList.add('text-orange');
                    }
                }
                else if(type === 'hum') {
                    if (data === 100 || data === 0) {
                        element.classList.add('text-red');
                    }
                    else if (data < 40 || data > 70) {
                        element.classList.add('text-orange');
                    }
                }
                else if(type === 'co2') {
                    if (data > 1500 || data < 400 ) {
                        element.classList.add('text-red');
                    }
                    else if (data >= 1000) {
                        element.classList.add('text-orange');
                    }
                }
            }
        })
        .catch(error => console.error('Erreur lors du chargement des données:', error));
}