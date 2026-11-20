/* ==========================================================================
   HUK · Acciones de los listados con orden (Banners, Equipo…)
   1. Orden:   <button data-listado-mover="URL">                     → POST y recarga la tabla
   2. Estado:  <a data-listado-estado="URL" data-accion="activar|desactivar" data-nombre="..." [data-aviso="..."]>
   3. Borrar:  <a data-listado-eliminar="URL" data-nombre="..." [data-aviso="..."]>
   4. Destacar: <button data-listado-destacar="URL">                → POST y recarga (sin confirmar)
   Responden JSON {exito, mensaje}. La tabla va dentro de un Pjax: se recarga ese bloque.
   ========================================================================== */
(function ($) {
  "use strict";

  function csrf() {
    var d = {};
    d[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
    return d;
  }

  function recargar($origen) {
    var $pjax = $origen.closest("[data-pjax-container]");
    if ($pjax.length && $.pjax) {
      $.pjax.reload({ container: "#" + $pjax.attr("id"), timeout: 5000 });
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
      .fail(function () { Swal.fire({ icon: "error", titleText: "Error de conexión. Intente de nuevo." }); });
  }

  /* 1. Orden */
  $(document).on("click", "[data-listado-mover]", function (e) {
    e.preventDefault();
    var $b = $(this).prop("disabled", true);
    enviar($b.data("listado-mover"), function () { recargar($b); }).always(function () { $b.prop("disabled", false); });
  });

  /* 2. Estado */
  $(document).on("click", "[data-listado-estado]", function (e) {
    e.preventDefault();
    var $b = $(this);
    var activar = $b.data("accion") === "activar";
    Swal.fire({
      icon: "question",
      titleText: (activar ? "¿Activar «" : "¿Desactivar «") + $b.data("nombre") + "»?",
      text: $b.data("aviso") || (activar ? "Se va a mostrar en la web." : "Deja de mostrarse en la web. No se borra nada."),
      showCancelButton: true,
      confirmButtonText: activar ? "Activar" : "Desactivar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: activar ? "#bd944f" : "#b07a12"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      enviar($b.data("listado-estado"), function (r) {
        Swal.fire({ icon: "success", titleText: r.mensaje }).then(function () { window.location.reload(); });
      });
    });
  });

  /* 4. Destacar / quitar destacado */
  $(document).on("click", "[data-listado-destacar]", function (e) {
    e.preventDefault();
    var $b = $(this).prop("disabled", true);
    enviar($b.data("listado-destacar"), function () { recargar($b); }).always(function () { $b.prop("disabled", false); });
  });

  /* 3. Eliminar */
  $(document).on("click", "[data-listado-eliminar]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      icon: "warning",
      titleText: "¿Eliminar «" + $b.data("nombre") + "»?",
      text: $b.data("aviso") || "No se puede deshacer. Si solo desea ocultarlo, use Desactivar.",
      showCancelButton: true,
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#a33a2c"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      enviar($b.data("listado-eliminar"), function (r) {
        Swal.fire({ icon: "success", titleText: r.mensaje }).then(function () { recargar($b); });
      });
    });
  });
})(jQuery);
