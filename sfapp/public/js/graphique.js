// Déclaration des variables

// Récupérer le nom de la salle depuis l'url (dernier élément de l'url)
const nomSalle = window.location.pathname.split("/").pop();
console.log(nomSalle);
let typeDonneesAffichee = null; // temp, hum, co2

let checkTypeDifference = false;

const groupageDonnees = {
    CinqMinutes: 0,
    Heure: 1,
    Jour: 2,
}
let groupageActuel = groupageDonnees.CinqMinutes;

let donneesRecuperees = [];   // JSON
let donneesTransformees = []; // [[date, valeur]]
let donneesAffichees = [];    // [[date, valeur]]

let currentChart = null;

// Récupération des éléments HTML
const chartContainer = document.getElementById('chart-container');
const chartTimeline = document.getElementById('chart-timeline');
const baseContainer = document.getElementById('base-container');
const groupageBoutons = document.getElementById('groupage-buttons');

//-----------------------------------------------


// Récupère les données en enviyant une reqête GET à l'API de l'app Symfony
// Exemple de retour : Format JSON [{"nom": "temp", "valeur": "21.5",  "dateCapture": "2021-03-01 10:00:00", "localisation": "D307", ...}, ...]
async function chargerDonnees(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Réseau ou réponse non valide');
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur lors du chargement des données:', error);
        throw error;
    }
}

// Transforme les données reçues (JSON) en un tableau de tableaux [[date, valeur]]
function transformerDonnees(jsonData) {
    let dataMapped = jsonData.map(item => [new Date(item.dateCapture), parseFloat(item.valeur)]);

    // Correction du décalage horaire et conversion en timestamp
    dataMapped = dataMapped.map(item => {
        let date = item[0];
        date.setMinutes(date.getMinutes() - date.getTimezoneOffset()); // Ajustement du fuseau horaire
        return [date.getTime(), item[1]];
    });

    // Trier les données par date en ordre croissant
    dataMapped.sort((a, b) => a[0] - b[0]);

    return dataMapped;
}

// Tranforme le tableau de données en un tableau de tableaux [[date, valeur]] en regroupant les valeurs par jour
function averageValuesByDay(data) {
    const dayToValuesMap = new Map();

    data.forEach(([timestamp, value]) => {
        // Convertir le timestamp en date et normaliser à minuit pour regrouper par jour
        const date = new Date(timestamp);
        const day = new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime();

        // Ajouter la valeur à la liste des valeurs pour ce jour
        if (!dayToValuesMap.has(day)) {
            dayToValuesMap.set(day, []);
        }
        dayToValuesMap.get(day).push(value);
    });

    // Calculer la moyenne pour chaque jour et tronquer au dixième
    const result = Array.from(dayToValuesMap, ([day, values]) => {
        const sum = values.reduce((acc, val) => acc + val, 0);
        const average = sum / values.length;
        const truncatedAverage = Math.round(average * 10) / 10;
        return [day, truncatedAverage];
    });

    return result;
}

// Tranforme le tableau de données en un tableau de tableaux [[date, valeur]] en regroupant les valeurs par heure
function averageValuesByHour(data) {
    const hourToValuesMap = new Map();

    data.forEach(([timestamp, value]) => {
        // Convertir le timestamp en date et normaliser à l'heure la plus proche
        const date = new Date(timestamp);
        const hour = new Date(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours()).getTime();

        // Ajouter la valeur à la liste des valeurs pour cette heure
        if (!hourToValuesMap.has(hour)) {
            hourToValuesMap.set(hour, []);
        }
        hourToValuesMap.get(hour).push(value);
    });

    // Calculer la moyenne pour chaque heure et tronquer au dixième
    const result = Array.from(hourToValuesMap, ([hour, values]) => {
        const sum = values.reduce((acc, val) => acc + val, 0);
        const average = sum / values.length;
        const truncatedAverage = Math.round(average * 10) / 10;
        return [hour, truncatedAverage];
    });

    return result;
}

//-----------------------------------------------

// Création et affichage du graphique avec ApexCharts
// donnesGraphique : tableau de tableaux [[date, valeur]]
// nomSalle : string
// typeGraphique : string (temp, hum, co2)
function creerGraphique(donneesGraphique, nomSalle, typeGraphique) {
    var debut = donneesGraphique[0][0];
    var fin = donneesGraphique[donneesGraphique.length - 1][0];

    var nom, yaxisMax;
    switch (typeGraphique) {
        case 'temp':
            nom = "Température (°C)";
            yaxisMax = 30;
            break;
        case 'hum':
            nom = "Humidité (%)";
            yaxisMax = 100;
            break;
        case 'co2':
            nom = "CO2 (ppm)";
            yaxisMax = 2000;
            break;
        default:
            throw new Error('Type de graphique non supporté');
    }

    var options = {
        series: [{ data: donneesGraphique, name: nom }],
        chart: {
            id: 'area-datetime',
            type: 'area',
            height: 350,
            background: '#202020', // Couleur de fond pour le thème sombre
            foreColor: '#fff',
            zoom: {
                autoScaleYaxis: true
            }
        },
        xaxis: {
            type: 'datetime',
            min: debut,
            max: fin,
            tickAmount: 10,
        },
        yaxis: {
            min: typeGraphique === 'temp' ? 14 : 0,
            max: yaxisMax,
        },
        title: {
            text: `${nom} pour la salle ${nomSalle}`,
            align: 'left'
        },
        dataLabels: {
            enabled: false
        },
        markers: {
            size: 0,
            style: 'hollow',
        },
        stroke: {
            curve: "straight",
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            },
            theme: "dark"
        },
        toolbar: {
            theme: "dark"
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.9,
                stops: [0, 100]
            }
        },
    };

    // Destruction du graphique actuel
    if (currentChart && checkTypeDifference) {
        currentChart.destroy();
    }

    chartTimeline.innerHTML = "";
    currentChart = new ApexCharts(chartTimeline, options);
    currentChart.render();
}

//-----------------------------------------------


// Vérifie si le graphique est déjà affiché, sinon l'affiche (updateGraph)
function displayGraph(typeDonnees){
    console.log("displayGraph");

    checkTypeDifference = (typeDonnees !== typeDonneesAffichee);

    if (typeDonnees !== typeDonneesAffichee){
        donneesRecuperees = [];
        donneesTransformees = [];
        donneesAffichees = [];

        typeDonneesAffichee = typeDonnees;
        groupageActuel = groupageDonnees.CinqMinutes;

        updateGraph();
    }
}

// Gère le changement de groupage des données (5 minutes, 1 heure ou 1 jour)
function changerGroupage(groupage){
    console.log("changerGroupage");
    if(groupage != groupageActuel){
        if(groupage == 1){
            donneesAffichees = averageValuesByHour(donneesTransformees);
            creerGraphique(donneesAffichees, nomSalle, typeDonneesAffichee);
        }
        else if(groupage == 2){
            donneesAffichees = averageValuesByDay(donneesTransformees);
            creerGraphique(donneesAffichees, nomSalle, typeDonneesAffichee);
        }
        else{
            donneesAffichees = donneesTransformees;
            creerGraphique(donneesAffichees, nomSalle, typeDonneesAffichee);
        }
        groupageActuel = groupage;
    }
}

// Mets à jour le graphique lors du changement de type de données
// - Récupère les données (fetch sur API Controller de l'app Symfony)
// - Transforme les données
// - Crée et affiche le graphique
async function updateGraph(){
    console.log("updateGraph");

    // Retire le graphique actuel et les boutons de l'affichage et message de chargement
    chartContainer.style.display = "none";
    groupageBoutons.style.display = "none";
    baseContainer.innerHTML = "<p>Chargement...</p>";
    baseContainer.style.display = "block";


    // Récupération des données
    url = 'http://localhost:8000/api/captures/' + nomSalle + '/' + typeDonneesAffichee;
    donneesRecuperees = await chargerDonnees(url);
    donneesTransformees = transformerDonnees(donneesRecuperees);
    if(donneesAffichees.length == 0){
        donneesAffichees = donneesTransformees;
    }

    // Affichage du graphique
    baseContainer.style.display = "none";
    groupageBoutons.style.display = "block";
    chartContainer.style.display = "block";

    creerGraphique(donneesAffichees, nomSalle, typeDonneesAffichee);
}

//-----------------------------------------------




