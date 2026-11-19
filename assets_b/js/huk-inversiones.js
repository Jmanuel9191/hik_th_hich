/* ==========================================================================
   HUK · Inversiones (panel)
   1. Formulario de pago: si la moneda del pago no es la del proyecto, muestra "Equivale a…".
      Proyecto: <select data-pago-proyecto> con <option data-moneda data-codigo>. Moneda: <select data-pago-moneda>.
   2. Tipo de pago: marca la opción elegida y cambia la ayuda de "Referencia" (data-ayuda).
   3. Subir / reemplazar comprobante desde la lista: <button data-pago-comprobante="URL">.
   ========================================================================== */
(function ($) {
  "use strict";

  function csrf() {
    var d = {};
    d[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
    return d;
  }

  /* 1 */
  function equivalente() {
    var $p = $("[data-pago-proyecto]");
    if (!$p.length) return;
    var $op = $p.find("option:selected");
    var monedaProyecto = String($op.data("moneda") || "");
    var monedaPago = String($("[data-pago-moneda]").val() || "");
    var otra = monedaProyecto !== "" && monedaPago !== "" && monedaProyecto !== monedaPago;
    $("[data-pago-codigo]").text($op.data("codigo") || "");
    $("[data-pago-equivalente]").toggleClass("hidden", !otra);
  }

  $(document).on("change", "[data-pago-proyecto]", function () {
    // al elegir proyecto, la moneda del pago arranca en la del proyecto
    var m = $(this).find("option:selected").data("moneda");
    if (m && !$(this).data("moneda-tocada")) $("[data-pago-moneda]").val(String(m));
    equivalente();
  });
  $(document).on("change", "[data-pago-moneda]", function () {
    $("[data-pago-proyecto]").data("moneda-tocada", true);
    equivalente();
  });

  /* 2 */
  function tipo() {
    var $m = $("[data-pago-tipo]:checked");
    $("[data-pago-tipo]").closest("label").removeClass("is-activo");
    $m.closest("label").addClass("is-activo");
    if ($m.length) $("[data-pago-referencia]").attr("placeholder", $m.data("ayuda"));
  }
  $(document).on("change", "[data-pago-tipo]", tipo);

  $(function () { equivalente(); tipo(); });

  /* 3 */
  $(document).on("click", "[data-pago-comprobante]", function (e) {
    e.preventDefault();
    var url = $(this).data("pago-comprobante");
    var $in = $('<input type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" class="hidden">').appendTo("body");
    $in.on("change", function () {
      if (!this.files.length) return;
      var fd = new FormData();
      $.each(csrf(), function (k, v) { fd.append(k, v); });
      fd.append("comprobante", this.files[0]);
      Swal.fire({ titleText: "Subiendo comprobante…", allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
      $.ajax({ url: url, method: "POST", data: fd, processData: false, contentType: false })
        .done(function (r) {
          Swal.fire({ icon: r && r.exito ? "success" : "error", titleText: (r && r.mensaje) || "No se pudo subir." })
            .then(function () { if (r && r.exito) window.location.reload(); });
        })
        .fail(function () { Swal.fire({ icon: "error", titleText: "Error de conexión. Intentá de nuevo." }); })
        .always(function () { $in.remove(); });
    });
    $in.trigger("click");
  });
})(jQuery);
