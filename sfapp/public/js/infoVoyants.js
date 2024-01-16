document.querySelectorAll('.voyant').forEach(function(voyant) {
    voyant.addEventListener('mouseover', function() {
        let message;
        let couleurClass;

        if (this.classList.contains('voyant-vert')) {
            message = 'En marche';
            couleurClass = 'info-voyant-vert';
        } else if (this.classList.contains('voyant-orange')) {
            message = 'Problème détecté';
            couleurClass = 'info-voyant-orange';
        } else if (this.classList.contains('voyant-rouge')) {
            message = 'Éteint';
            couleurClass = 'info-voyant-rouge';
        }

        let infoDiv = document.createElement('div');
        infoDiv.classList.add('info-voyant', couleurClass);
        infoDiv.textContent = message;
        this.appendChild(infoDiv);

        console.log("Hover", this)
    });

    voyant.addEventListener('mouseout', function() {
        let infoDiv = this.querySelector('.info-voyant');
        if (infoDiv) {
            this.removeChild(infoDiv);
        }
    });
});
