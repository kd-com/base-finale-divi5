document.addEventListener('DOMContentLoaded', function() {
    // Appliquer AOS sur tous les blocks ayant les attributs data-aos ou data-aos-delay
    var blocks = document.querySelectorAll('[data-aos], [data-aos-delay]');
    if (blocks.length > 0 && typeof AOS !== 'undefined') {
        AOS.init();
    }
});
