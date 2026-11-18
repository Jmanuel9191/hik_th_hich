/* ==========================================================================
   HUK · Banners
   1. Orden:   <button data-banner-mover="URL">          → POST y recarga la tabla
   2. Estado:  <a data-banner-estado="URL" data-accion="activar|desactivar" data-nombre="...">
   3. Borrar:  <a data-banner-eliminar="URL" data-nombre="...">
   Las tres responden JSON {exito, mensaje}.
   ========================================================================== */
(function ($) {
  "use strict";

  function csrf() {
    var d = {};
    d[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
    return d;
  }

  function recargar() {
    if ($("#banners-grid").length && $.pjax) {
      $.pjax.reload({ container: "#banners-grid", timeout: 5000 });
    } else {
      window.location.reload();
    }
  }

  function enviar(url, alTerminar) {
    return $.post(url, csrf())
      .done(function (r) {
        if (r && r.exito) {
          alTerminar(r);
        } else {
          Swal.fire({ icon: "error", titleText: (r && r.mensaje) || "No se pudo completar." });
        }
      })
      .fail(function () { Swal.fire({ icon: "error", titleText: "Error de conexión. Intentá de nuevo." }); });
  }

  /* 1. Orden */
  $(document).on("click", "[data-banner-mover]", function (e) {
    e.preventDefault();
    var $b = $(this).prop("disabled", true);
    enviar($b.data("banner-mover"), recargar).always(function () { $b.prop("disabled", false); });
  });

  /* 2. Estado */
  $(document).on("click", "[data-banner-estado]", function (e) {
    e.preventDefault();
    var $b = $(this);
    var activar = $b.data("accion") === "activar";
    Swal.fire({
      icon: "question",
      titleText: (activar ? "¿Activar " : "¿Desactivar ") + $b.data("nombre") + "?",
      text: activar ? "Se va a mostrar en la portada (si está dentro de sus fechas)." : "Deja de mostrarse en la portada. No se borra nada.",
      showCancelButton: true,
      confirmButtonText: activar ? "Activar" : "Desactivar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: activar ? "#bd944f" : "#b07a12"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      enviar($b.data("banner-estado"), function (r) {
        Swal.fire({ icon: "success", titleText: r.mensaje }).then(function () { window.location.reload(); });
      });
    });
  });

  /* 3. Eliminar */
  $(document).on("click", "[data-banner-eliminar]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      icon: "warning",
      titleText: "¿Eliminar " + $b.data("nombre") + "?",
      text: "Se borran el banner y sus imágenes. No se puede deshacer. Si solo querés ocultarlo, usá Desactivar.",
      showCancelButton: true,
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#a33a2c"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      enviar($b.data("banner-eliminar"), function (r) {
        Swal.fire({ icon: "success", titleText: r.mensaje }).then(recargar);
      });
    });
  });
})(jQuery);
