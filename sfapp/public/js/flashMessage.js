document.addEventListener('DOMContentLoaded', function() {
    // Trouver tous les éléments avec la classe .alert
    var alerts = document.querySelectorAll('.alert');

    // Fonction pour réduire l'opacité
    function fadeOut(element) {
        var op = 1;  // initial opacity
        var timer = setInterval(function () {
            if (op <= 0.1){
                clearInterval(timer);
                element.style.display = 'none';
            }
            element.style.opacity = op;
            element.style.filter = 'alpha(opacity=' + op * 100 + ")";
            op -= op * 0.1;
        }, 50);
    }

    // Attendre 5 secondes après le chargement de la page
    setTimeout(function() {
        alerts.forEach(function(alert) {
            fadeOut(alert);
        });
    }, 5000);
});
