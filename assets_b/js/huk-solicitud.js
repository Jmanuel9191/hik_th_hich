/* ==========================================================================
   HUK · Solicitud de inversor (web y panel)
   - Persona / Empresa: <input data-inversor-tipo> muestra u oculta [data-solo-empresa]
     y cambia la etiqueta de [data-etiqueta-persona] / [data-etiqueta-empresa].
   - Ver la contraseña: <button data-ver-clave="#id">.
   ========================================================================== */
(function ($) {
  "use strict";

  function aplicar() {
    var $marcado = $("[data-inversor-tipo]:checked");
    if (!$marcado.length) return;
    var tipo = $marcado.val();
    $("[data-solo-empresa]").toggleClass("d-none hidden", tipo !== "empresa");
    $("[data-etiqueta-" + tipo + "]").each(function () {
      var $l = $(this);
      var $req = $l.find(".required, abbr").detach();
      $l.text($l.data("etiqueta-" + tipo)).append($req);
    });
    $("[data-inversor-tipo]").closest("label").removeClass("is-activo");
    $marcado.closest("label").addClass("is-activo");
  }
  $(document).on("change", "[data-inversor-tipo]", aplicar);
  $(aplicar);

  $(document).on("click", "[data-ver-clave]", function (e) {
    e.preventDefault();
    var $i = $($(this).data("ver-clave"));
    var ver = $i.attr("type") === "password";
    $i.attr("type", ver ? "text" : "password");
    $(this).find("i").toggleClass("fa-eye fa-eye-slash");
    $(this).attr("aria-label", ver ? "Ocultar contraseña" : "Ver contraseña");
  });
})(jQuery);
