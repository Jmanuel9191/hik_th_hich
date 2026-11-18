/* ==========================================================================
   HUK · huk-admin.js  (panel admin)
   1. Mostrar / ocultar contraseña: <button data-ver-clave> dentro de .huk-adm-field
   2. Mensajes flash con SweetAlert2: <body data-flash='{"type":"success","message":"..."}'>
   ========================================================================== */
(function ($) {
  "use strict";

  /* 1. Mostrar / ocultar contraseña */
  $(document).on("click", "[data-ver-clave]", function () {
    var $btn = $(this);
    var $input = $btn.closest(".huk-adm-field").find("input");
    var ver = $input.attr("type") === "password";
    $input.attr("type", ver ? "text" : "password");
    $btn.find(".fa").toggleClass("fa-eye", !ver).toggleClass("fa-eye-slash", ver);
    $btn.attr("aria-label", ver ? "Ocultar contraseña" : "Mostrar contraseña");
  });

  /* 2. Mensaje flash (lo deja el controlador con setFlash('flash', [...])) */
  $(function () {
    var flash = $("body").data("flash");
    if (!flash || !flash.message || typeof window.Swal === "undefined") return;
    window.Swal.fire({
      icon: flash.type || "info",
      title: flash.message,
      confirmButtonText: "Aceptar",
      confirmButtonColor: "#bd944f"
    });
  });
})(window.jQuery);
