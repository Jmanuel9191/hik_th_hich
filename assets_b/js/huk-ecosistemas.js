/* ==========================================================================
   HUK · huk-ecosistemas.js  (módulo Ecosistemas del panel)
   1. (El editor TinyMCE está en huk-editor.js / EditorAsset)
   2. (Las imágenes ahora usan app\widgets\CampoImagen + huk-imagen.js)
   3. Activar / Desactivar: <a data-eco-estado="URL" data-accion="activar|desactivar" data-nombre="...">
   3b. Eliminar: <a data-eco-eliminar="URL" data-nombre="...">
   4. Galería: al terminar de subir, recarga para mostrar las fotos con su botón de borrar.
   ========================================================================== */
(function ($) {
  "use strict";

  /* 3. Activar / Desactivar */
  $(document).on("click", "[data-eco-estado]", function (e) {
    e.preventDefault();
    var $b = $(this);
    var activar = $b.data("accion") === "activar";
    Swal.fire({
      icon: "question",
      titleText: (activar ? "¿Activar " : "¿Desactivar ") + $b.data("nombre") + "?",
      text: activar ? "Se va a mostrar en la web." : "Deja de mostrarse en la web. No se borra nada.",
      showCancelButton: true,
      confirmButtonText: activar ? "Activar" : "Desactivar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: activar ? "#bd944f" : "#a33a2c",
    }).then(function (res) {
      if (!res.isConfirmed) return;
      var datos = {};
      datos[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
      $.post($b.data("eco-estado"), datos)
        .done(function (r) {
          Swal.fire({ icon: r && r.exito ? "success" : "error", titleText: (r && r.mensaje) || "No se pudo cambiar el estado." })
            .then(function () { window.location.reload(); });
        })
        .fail(function () { Swal.fire({ icon: "error", titleText: "Error de conexión. Intentá de nuevo." }); });
    });
  });

  /* 3b. Eliminar (el servidor no deja si tiene proyectos asignados) */
  $(document).on("click", "[data-eco-eliminar]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      icon: "warning",
      titleText: "¿Eliminar " + $b.data("nombre") + "?",
      text: "Se borran el ecosistema, sus imágenes y su galería, y se quita a los gerentes que lo tenían. No se puede deshacer. Si solo querés ocultarlo, usá Desactivar.",
      showCancelButton: true,
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#a33a2c"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      var datos = {};
      datos[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
      $.post($b.data("eco-eliminar"), datos)
        .done(function (r) {
          Swal.fire({ icon: r && r.exito ? "success" : "error", titleText: (r && r.mensaje) || "No se pudo eliminar." })
            .then(function () { if (r && r.exito) window.location.reload(); });
        })
        .fail(function () { Swal.fire({ icon: "error", titleText: "Error de conexión. Intentá de nuevo." }); });
    });
  });

  /* 4. Galería */
  $(document).on("filebatchuploadcomplete", "#galeria-imagenes", function () {
    window.location.reload();
  });
})(jQuery);
