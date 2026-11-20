/*
 * Formulario de banners: al cambiar la "Página" muestra solo los textos que usa esa página.
 * data-campos (en el select) = {"inicio": ["antetitulo","titulo","texto","boton"], "prefooter": [...], ...}
 * Cada grupo de campos lleva data-campo="antetitulo|texto|boton"; la ayuda de cada página, data-ayuda-de="clave".
 */
(function ($) {
    "use strict";

    function aplicar($select) {
        var mapa = $select.data("campos") || {};
        var pagina = String($select.val());
        var campos = mapa[pagina] || [];
        var $form = $select.closest("form");
        $form.find("[data-campo]").each(function () {
            $(this).prop("hidden", campos.indexOf($(this).data("campo")) === -1);
        });
        $form.find("[data-ayuda-de]").each(function () {
            $(this).prop("hidden", String($(this).data("ayuda-de")) !== pagina);
        });
    }

    $(document).on("change", "[data-banner-ubicacion]", function () {
        aplicar($(this));
    });

    $(function () {
        $("[data-banner-ubicacion]").each(function () {
            aplicar($(this));
        });
    });
})(jQuery);
