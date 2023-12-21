const konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
let userInput = [];
let konamiActivated = false;

function checkKonamiCode(event) {
    if (konamiActivated) {
        // Si le code Konami est déjà activé, ne rien faire
        return;
    }

    userInput.push(event.key);


    // Comparer la séquence utilisateur avec le code Konami
    if (userInput.length === konamiCode.length && userInput.every((value, index) => value.toLowerCase() === konamiCode[index].toLowerCase())) {
        // Afficher l'audio si le code est correct
        document.getElementById('konami-audio').style.display = 'block';
        // Lancer l'audio
        document.getElementById('konami-audio').play();
        // Activer le code Konami
        konamiActivated = true;
        // Réinitialiser la séquence utilisateur
        userInput = [];
    } else if (userInput.length >= konamiCode.length) {
        // Réinitialiser la séquence utilisateur si elle dépasse la longueur du code Konami
        userInput = [];
    }

    if(event.key != konamiCode[userInput.length-1]){
        // Réinitialiser la séquence utilisateur si elle dépasse la longueur du code Konami
        userInput = [];
    }
}

// Ajouter un écouteur d'événement pour la touche
document.addEventListener('keydown', checkKonamiCode);