/* ==========================================================================
   HUK · Inversores (panel)
   1. Con motivo (rechazar, bloquear/activar): <button data-inversor-motivo="URL" data-titulo="..." data-texto="..."
      data-boton="Rechazar" data-color="#a33a2c"> → modal con el motivo → POST → recarga.
   2. Acción simple (reenviar correo): <button data-inversor-post="URL" data-titulo="..." data-texto="...">.
   ========================================================================== */
(function ($) {
  "use strict";

  function csrf() {
    var d = {};
    d[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
    return d;
  }

  function enviar(url, datos) {
    $.post(url, $.extend(csrf(), datos || {}))
      .done(function (r) {
        Swal.fire({ icon: r && r.exito ? "success" : "error", titleText: (r && r.mensaje) || "No se pudo completar." })
          .then(function () { if (r && r.exito) window.location.reload(); });
      })
      .fail(function () { Swal.fire({ icon: "error", titleText: "Error de conexión. Intente de nuevo." }); });
  }

  /* 1 */
  $(document).on("click", "[data-inversor-motivo]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      icon: "question",
      titleText: $b.data("titulo"),
      text: $b.data("texto"),
      input: "textarea",
      inputPlaceholder: "Motivo (entre 5 y 500 caracteres)",
      inputAttributes: { maxlength: 500 },
      showCancelButton: true,
      confirmButtonText: $b.data("boton"),
      cancelButtonText: "Cancelar",
      confirmButtonColor: $b.data("color") || "#bd944f",
      inputValidator: function (v) { return (!v || v.trim().length < 5) ? "Escriba el motivo." : null; }
    }).then(function (res) {
      if (res.isConfirmed) enviar($b.data("inversor-motivo"), { motivo: res.value });
    });
  });

  /* 2 */
  $(document).on("click", "[data-inversor-post]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      icon: "question",
      titleText: $b.data("titulo"),
      text: $b.data("texto"),
      showCancelButton: true,
      confirmButtonText: "Sí, enviar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#bd944f"
    }).then(function (res) {
      if (res.isConfirmed) enviar($b.data("inversor-post"));
    });
  });
})(jQuery);
