document.addEventListener("DOMContentLoaded", function() {
    function getQueryParam(name) {
        const results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        }
        return decodeURI(results[1]) || 0;
    }

    var scrollToId = getQueryParam('scrollTo');

    if (scrollToId && document.getElementById(scrollToId)) {
        var element = document.getElementById(scrollToId);

        // Calculer la position de l'élément
        var elementPosition = element.getBoundingClientRect().top + window.pageYOffset;

        // Hauteur de la bannière
        var bannerHeight = 300;

        // Position de défilement ajustée
        var offsetPosition = elementPosition - bannerHeight;

        // Faire défiler vers la position ajustée
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });

        // Ajoutez la classe pour l'animation après un court délai
        setTimeout(function() {
            element.classList.add('blink-border');

            // Optionnel: supprimer la classe après l'animation
            setTimeout(function() {
                element.classList.remove('blink-border');
            }, 3000); // Durée totale de l'animation (1s * 3 clignotements)
        }, 500); // Délai avant le début de l'animation
    }
});
