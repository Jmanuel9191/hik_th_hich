/* ==========================================================================
   HUK · huk-ecosistemas.js  (módulo Ecosistemas del panel)
   1. Editor TinyMCE: <textarea data-editor data-tinymce-base="URL de lib/tinymce">
   2. Imágenes: <input type="file" data-preview="#idImg"> muestra la vista previa;
      <input type="checkbox" data-quitar="#idImg"> la oculta (se borra al guardar).
   3. Activar / Desactivar: <a data-eco-estado="URL" data-accion="activar|desactivar" data-nombre="...">
   4. Galería: al terminar de subir, recarga para mostrar las fotos con su botón de borrar.
   ========================================================================== */
(function ($) {
  "use strict";

  /* 1. Editor */
  $(function () {
    var $areas = $("textarea[data-editor]");
    if (!$areas.length || typeof window.tinymce === "undefined") return;
    var base = $areas.first().data("tinymce-base");
    window.tinymce.init({
      selector: "textarea[data-editor]",
      license_key: "gpl",
      base_url: base,
      suffix: ".min",
      language: "es",
      language_url: base + "/langs/es.js",
      promotion: false,
      branding: false,
      menubar: false,
      toolbar_mode: "wrap", // todos los botones a la vista (sin "…")
      height: 460,
      plugins: "advlist lists link table code fullscreen wordcount",
      toolbar:
        "undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | " +
        "alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | " +
        "table link | removeformat code fullscreen",
      block_formats: "Párrafo=p; Título 2=h2; Título 3=h3; Título 4=h4",
      font_size_formats: "12px 14px 16px 18px 20px 24px 28px 32px",
      table_default_styles: { "border-collapse": "collapse", width: "100%" },
      paste_data_images: false, // las imágenes van en la galería, no pegadas en el texto
      convert_urls: false,
      content_style: "body{font-family:Figtree,Segoe UI,Arial,sans-serif;font-size:15px;color:#1d1b17}" +
        "table{border-collapse:collapse}td,th{border:1px solid #d9d3c5;padding:4px 6px}",
      setup: function (ed) {
        ed.on("change", function () { ed.save(); });
      },
    });
  });

  /* 2. Vista previa / quitar imagen */
  $(document).on("change", "input[type=file][data-preview]", function () {
    var $img = $($(this).data("preview"));
    var f = this.files && this.files[0];
    if (!f || !$img.length) return;
    $img.attr("src", URL.createObjectURL(f)).removeClass("hidden");
    $("input[data-quitar='" + $(this).data("preview") + "']").prop("checked", false);
  });
  $(document).on("change", "input[type=checkbox][data-quitar]", function () {
    $($(this).data("quitar")).toggleClass("is-quitar", this.checked);
  });

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

  /* 4. Galería */
  $(document).on("filebatchuploadcomplete", "#galeria-imagenes", function () {
    window.location.reload();
  });
})(jQuery);
