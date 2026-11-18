/* ==========================================================================
   HUK · main.js
   Solo los carruseles de Owl. Lo demás que hacía el main.js de Electra
   (spinner, WOW, volver arriba con easing) ahora lo hace huk.js.
   ========================================================================== */
(function ($) {
    "use strict";

    if (!$ || !$.fn.owlCarousel) {
        return; // si Owl no está cargado, no rompe nada
    }

    var flechas = [
        '<i class="fa-solid fa-arrow-left" aria-hidden="true"></i>',
        '<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>'
    ];

    // Carrusel principal (banners)
    $(".header-carousel").owlCarousel({
        animateOut: "fadeOut",
        items: 1,
        autoplay: true,
        autoplayHoverPause: true,
        smartSpeed: 1000,
        dots: false,
        loop: true,
        nav: true,
        navText: flechas
    });

    // Carrusel de ecosistemas / servicios
    $(".service-carousel").owlCarousel({
        autoplay: true,
        autoplayHoverPause: true,
        smartSpeed: 1200,
        dots: false,
        loop: true,
        margin: 25,
        nav: true,
        navText: flechas,
        responsive: { 0: { items: 1 }, 768: { items: 2 }, 1200: { items: 3 } }
    });

    // Carrusel de testimonios
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        autoplayHoverPause: true,
        smartSpeed: 1200,
        dots: true,
        loop: true,
        margin: 25,
        nav: false,
        responsive: { 0: { items: 1 }, 1200: { items: 2 } }
    });

})(window.jQuery);
