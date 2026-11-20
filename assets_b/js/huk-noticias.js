/*
 * Formulario de noticias (panel):
 *  · [data-slug-de="#campo-titulo"] muestra cómo queda la dirección en la web (/noticias/titulo-de-la-noticia)
 *    mientras se escribe el título o la dirección propia.
 *  · [data-contador="300"] cuenta los caracteres del resumen.
 *  · Galería (Kartik FileInput #galeria-imagenes): al terminar de subir, recarga para ver las fotos guardadas.
 */
(function () {
    "use strict";

    function slug(texto) {
        return texto.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase()
            .replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "").slice(0, 200);
    }

    document.querySelectorAll("[data-slug-vista]").forEach(function (vista) {
        var titulo = document.querySelector(vista.getAttribute("data-titulo"));
        var propio = document.querySelector(vista.getAttribute("data-slug"));
        var base = vista.getAttribute("data-base");
        function pintar() {
            var s = slug((propio && propio.value.trim()) || (titulo ? titulo.value : ""));
            vista.textContent = base + (s || "…");
        }
        [titulo, propio].forEach(function (el) { if (el) el.addEventListener("input", pintar); });
        pintar();
    });

    document.querySelectorAll("[data-contador]").forEach(function (campo) {
        var max = parseInt(campo.getAttribute("data-contador"), 10);
        var aviso = document.createElement("small");
        aviso.className = "huk-contador";
        campo.parentNode.appendChild(aviso);
        function contar() {
            var n = campo.value.length;
            aviso.textContent = n + " / " + max;
            aviso.classList.toggle("is-cerca", n > max * 0.9);
        }
        campo.addEventListener("input", contar);
        contar();
    });

    if (window.jQuery) {
        window.jQuery(document).on("filebatchuploadcomplete", "#galeria-imagenes", function () {
            window.location.reload();
        });
    }
})();
