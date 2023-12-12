document.addEventListener('DOMContentLoaded', function() {
    var svg2 = document.getElementById('svg2');
    var svg3 = document.getElementById('svg3');

    function toggleSVGs() {
        if (svg2.style.opacity == '0' && svg3.style.opacity == '0') {
            svg2.style.opacity = '1';
            setTimeout(toggleSVGs, 500); 
        } else if (svg2.style.opacity == '1' && svg3.style.opacity == '0') {
            svg3.style.opacity = '1';
            setTimeout(toggleSVGs, 2000); 
        } else {
            svg2.style.opacity = '0';
            svg3.style.opacity = '0';
            setTimeout(toggleSVGs, 500); 
        }
    }

    toggleSVGs(); 
});
