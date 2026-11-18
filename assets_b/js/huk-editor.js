/* ==========================================================================
   HUK · Editor de texto (TinyMCE 7 local, licencia GPL)
   <textarea data-editor data-tinymce-base="URL de lib/tinymce" [data-editor-alto="300"]>
   Se usa en Ecosistemas (descripción) y Equipo (biografía). El servidor limpia el HTML (HtmlEditor).
   ========================================================================== */
(function ($) {
  "use strict";

  $(function () {
    var $areas = $("textarea[data-editor]");
    if (!$areas.length || typeof window.tinymce === "undefined") return;
    var base = $areas.first().data("tinymce-base");
    $areas.each(function () {
      window.tinymce.init({
        target: this,
        license_key: "gpl",
        base_url: base,
        suffix: ".min",
        language: "es",
        language_url: base + "/langs/es.js",
        promotion: false,
        branding: false,
        menubar: false,
        toolbar_mode: "wrap", // todos los botones a la vista (sin "…")
        height: $(this).data("editor-alto") || 460,
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
  });

})(jQuery);
