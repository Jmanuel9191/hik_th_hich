/* ==========================================================================
   HUK · huk-usuarios.js  (módulo Usuarios del panel)
   1. Bloquear / Activar: <a data-usuario-estado="URL" data-accion="bloquear|activar" data-nombre="...">
      Pide el motivo (obligatorio) y hace POST a URL con {motivo}.
   2. Desbloquear intentos: <a data-usuario-intentos="URL" data-nombre="...">
   3. Formulario: <div data-solo-rol="3"> se muestra solo si el select [data-rol-select] vale 3
      (Gerente de área → "Ecosistemas a cargo").
   4. País → Ciudad: el select [data-pais-select] trae data-ciudades='{"Bolivia":[...],...}';
      al cambiar el país se recarga [data-ciudad-select] (Select2 con tags: se puede escribir otra).
   Respuesta esperada del servidor: {"exito": true|false, "mensaje": "..."}
   ========================================================================== */
(function ($) {
  "use strict";

  function csrf() {
    var datos = {};
    datos[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
    return datos;
  }

  function recargar() {
    if ($.pjax && $("#usuarios-grid").length) {
      $.pjax.reload({ container: "#usuarios-grid", timeout: 5000 });
    } else {
      window.location.reload();
    }
  }

  function enviar(url, datos) {
    return $.post(url, $.extend(csrf(), datos || {}))
      .done(function (r) {
        Swal.fire({
          icon: r && r.exito ? "success" : "error",
          titleText: (r && r.mensaje) || "No se pudo completar la acción.",
        }).then(function () {
          if (r && r.exito) recargar();
        });
      })
      .fail(function () {
        Swal.fire({ icon: "error", titleText: "Error de conexión. Intentá de nuevo." });
      });
  }

  /* 1. Bloquear / Activar con motivo */
  $(document).on("click", "[data-usuario-estado]", function (e) {
    e.preventDefault();
    var $b = $(this);
    var bloquear = $b.data("accion") === "bloquear";
    Swal.fire({
      titleText: (bloquear ? "Bloquear a " : "Activar a ") + $b.data("nombre"),
      input: "textarea",
      inputLabel: "Motivo",
      inputPlaceholder: bloquear ? "Ej.: ya no trabaja en la empresa" : "Ej.: vuelve a trabajar con nosotros",
      showCancelButton: true,
      confirmButtonText: bloquear ? "Bloquear" : "Activar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: bloquear ? "#a33a2c" : "#bd944f",
      inputValidator: function (v) {
        if (!v || !v.trim()) return "Escribí el motivo.";
      },
    }).then(function (res) {
      if (res.isConfirmed) enviar($b.data("usuario-estado"), { motivo: res.value.trim() });
    });
  });

  /* 2. Desbloquear por intentos fallidos */
  $(document).on("click", "[data-usuario-intentos]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      icon: "question",
      titleText: "¿Desbloquear a " + $b.data("nombre") + "?",
      text: "Podrá volver a intentar entrar ahora mismo.",
      showCancelButton: true,
      confirmButtonText: "Desbloquear",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#bd944f",
    }).then(function (res) {
      if (res.isConfirmed) enviar($b.data("usuario-intentos"));
    });
  });

  /* 3. Campos que dependen del tipo de usuario */
  function mostrarSegunRol() {
    var rol = String($("[data-rol-select]").val() || "");
    $("[data-solo-rol]").each(function () {
      $(this).toggleClass("hidden", String($(this).data("solo-rol")) !== rol);
    });
  }
  $(document).on("change", "[data-rol-select]", mostrarSegunRol);
  $(mostrarSegunRol);

  /* 4. País → Ciudad */
  $(document).on("change", "[data-pais-select]", function () {
    var mapa = $(this).data("ciudades") || {};
    var ciudades = mapa[$(this).val()] || [];
    var $ciudad = $("[data-ciudad-select]");
    $ciudad.empty().append(new Option("", "", false, false));
    $.each(ciudades, function (i, c) { $ciudad.append(new Option(c, c, false, false)); });
    $ciudad.val(null).trigger("change");
  });
})(jQuery);
