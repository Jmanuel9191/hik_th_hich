/* ==========================================================================
   HUK · Banco de proyectos (panel)
   1. Formulario según el tipo: <input data-proyecto-tipo> muestra/oculta [data-solo-tipo="..."]
      y cambia las etiquetas con data-etiqueta-financiamiento / data-etiqueta-inversion.
   2. Estado:   <button data-proyecto-estado="URL" data-texto="Publicar" data-aviso="...">
   3. Eliminar: <button data-proyecto-eliminar="URL" data-volver="URL" data-nombre="...">
   4. Subida con barra de progreso: <form data-subida data-max-bytes="..." data-max-texto="100 MB">
      (bloquea el botón mientras sube; responde JSON {exito, mensaje}).
   5. Galería: al terminar de subir, recarga.
   6. Avance: al elegir etapa ([data-porcentajes-etapa]) sugiere su % en [data-porcentaje-avance].
   7. Barras de % sin estilos en la vista: <div class="progress-bar" data-ancho="35">.
   8. Avances, varios archivos: <form data-subida-varios> con <input type=file multiple data-subida-archivos>;
      arma una lista con una descripción por archivo y los sube de a uno (uno por pedido, con progreso).
   9. Descripción de un archivo: <button data-leyenda-editar="URL" data-leyenda="..." data-max="300"> → modal.
   ========================================================================== */
(function ($) {
  "use strict";

  function csrf() {
    var d = {};
    d[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
    return d;
  }

  function error(msg) {
    Swal.fire({ icon: "error", titleText: msg || "No se pudo completar." });
  }

  /* 1. Tipo */
  function aplicarTipo() {
    var tipo = $("[data-proyecto-tipo]:checked").val();
    if (!tipo) return;
    $("[data-solo-tipo]").each(function () {
      $(this).toggleClass("hidden", $(this).data("solo-tipo") !== tipo);
    });
    $("[data-etiqueta-" + tipo + "]").each(function () {
      var $l = $(this);
      var $req = $l.find(".required, abbr").detach();
      $l.text($l.data("etiqueta-" + tipo)).append($req);
    });
    $(".huk-proyecto-tipo").removeClass("is-activo");
    $("[data-proyecto-tipo]:checked").closest(".huk-proyecto-tipo").addClass("is-activo");
  }
  $(document).on("change", "[data-proyecto-tipo]", aplicarTipo);
  $(aplicarTipo);

  /* 2. Estado */
  $(document).on("click", "[data-proyecto-estado]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      icon: "question",
      titleText: $b.data("texto") + "?",
      text: $b.data("aviso"),
      showCancelButton: true,
      confirmButtonText: $b.data("texto"),
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#bd944f"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      $.post($b.data("proyecto-estado"), csrf())
        .done(function (r) {
          if (r && r.exito) {
            Swal.fire({ icon: "success", titleText: r.mensaje }).then(function () { window.location.reload(); });
          } else {
            error(r && r.mensaje);
          }
        })
        .fail(function () { error("Error de conexión. Intente de nuevo."); });
    });
  });

  /* 3. Eliminar proyecto */
  $(document).on("click", "[data-proyecto-eliminar]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      icon: "warning",
      titleText: "¿Eliminar «" + $b.data("nombre") + "»?",
      text: "Se borran la ficha, las fotos, los videos y los documentos. No se puede deshacer.",
      showCancelButton: true,
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#a33a2c"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      $.post($b.data("proyecto-eliminar"), csrf())
        .done(function (r) {
          if (r && r.exito) {
            Swal.fire({ icon: "success", titleText: r.mensaje }).then(function () { window.location.href = $b.data("volver"); });
          } else {
            error(r && r.mensaje);
          }
        })
        .fail(function () { error("Error de conexión. Intente de nuevo."); });
    });
  });

  /* 4. Subida con progreso */
  var subiendo = false;
  window.addEventListener("beforeunload", function (e) {
    if (subiendo) { e.preventDefault(); e.returnValue = ""; }
  });

  $(document).on("submit", "form[data-subida]", function (e) {
    e.preventDefault();
    var form = this;
    var $f = $(form);
    if (subiendo) return;
    var max = parseInt($f.data("max-bytes"), 10) || 0;
    var archivo = $f.find("[data-subida-archivo]")[0];
    if (archivo && archivo.files.length && max && archivo.files[0].size > max) {
      error("El archivo pesa " + (archivo.files[0].size / 1048576).toFixed(1) + " MB. El máximo es " + $f.data("max-texto") + ".");
      return;
    }
    var $btn = $f.find("[data-subida-boton]");
    var textoBtn = $btn.html();
    var $barra = $f.find("[data-subida-barra]");
    var $prog = $barra.find(".progress-bar");
    var conArchivo = archivo && archivo.files.length > 0;

    subiendo = true;
    $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> ' + (conArchivo ? "Subiendo…" : "Guardando…"));
    if (conArchivo) {
      $barra.prop("hidden", false);
      $prog.css("width", "0%").text("0%");
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", form.action);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.upload.addEventListener("progress", function (ev) {
      if (!ev.lengthComputable) return;
      var p = Math.round((ev.loaded / ev.total) * 100);
      $prog.css("width", p + "%").text(p < 100 ? p + "%" : "Procesando… (si hay que convertir el video puede tardar unos minutos)");
    });
    function terminar() {
      subiendo = false;
      $btn.prop("disabled", false).html(textoBtn);
      $barra.prop("hidden", true);
    }
    xhr.onload = function () {
      var r = null;
      try { r = JSON.parse(xhr.responseText); } catch (err) { r = null; }
      terminar();
      if (xhr.status === 200 && r && r.exito) {
        // con aviso (ej. video H.265 que no todos los navegadores ven): se muestra el texto completo
        Swal.fire(r.aviso ? { icon: "warning", titleText: "Video agregado", text: r.mensaje } : { icon: "success", titleText: r.mensaje })
          .then(function () { window.location.reload(); });
      } else if (xhr.status === 413 || xhr.status === 400) {
        error("El servidor rechazó el envío. Puede que el archivo sea más grande de lo que acepta el hosting.");
      } else {
        error(r && r.mensaje);
      }
    };
    xhr.onerror = function () { terminar(); error("Error de conexión. Intente de nuevo."); };
    xhr.send(new FormData(form));
  });

  /* 5. Galería */
  $(document).on("filebatchuploadcomplete", "#proyecto-galeria", function () {
    window.location.reload();
  });

  /* 6. % sugerido por etapa */
  var sugerido = null;
  $(document).on("change", "[data-porcentajes-etapa]", function () {
    var mapa = $(this).data("porcentajes-etapa") || {};
    var $pct = $("[data-porcentaje-avance]");
    var actual = $pct.val();
    var nuevo = mapa[$(this).val()];
    if (nuevo === undefined) return;
    if (actual === "" || String(actual) === String(sugerido)) {
      $pct.val(nuevo);
    }
    sugerido = nuevo;
  });

  /* 8. Varios archivos con descripción */
  function peso(b) { return b >= 1048576 ? (b / 1048576).toFixed(1) + " MB" : Math.max(1, Math.round(b / 1024)) + " KB"; }
  function icono(nombre) {
    var ext = (nombre.split(".").pop() || "").toLowerCase();
    if (["jpg", "jpeg", "png", "webp"].indexOf(ext) >= 0) return "fa-picture-o";
    if (["mp4", "mov", "m4v", "webm"].indexOf(ext) >= 0) return "fa-film";
    return "fa-file-o";
  }

  $(document).on("change", "[data-subida-archivos]", function () {
    var $f = $(this).closest("form");
    var $lista = $f.find("[data-subida-lista]").empty();
    var max = $f.data("max-leyenda") || 300;
    $.each(this.files, function (i, file) {
      var $li = $("<li>");
      $("<span>").addClass("huk-subida-nombre")
        .append($("<i>").addClass("fa " + icono(file.name)), " ", document.createTextNode(file.name + " · " + peso(file.size)))
        .appendTo($li);
      $("<input>", { type: "text", "class": "form-control input-sm", maxlength: max, placeholder: "Descripción (opcional)", "data-leyenda-de": i }).appendTo($li);
      $lista.append($li);
    });
  });

  function subirUno(url, datos, alProgreso) {
    return new Promise(function (resolver) {
      var xhr = new XMLHttpRequest();
      xhr.open("POST", url);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.upload.addEventListener("progress", function (ev) { if (ev.lengthComputable) alProgreso(Math.round(ev.loaded / ev.total * 100)); });
      xhr.onload = function () {
        var r = null;
        try { r = JSON.parse(xhr.responseText); } catch (err) { r = null; }
        resolver(xhr.status === 200 && r ? r : { exito: false, mensaje: "El servidor rechazó el envío (¿archivo demasiado grande para el hosting?)." });
      };
      xhr.onerror = function () { resolver({ exito: false, mensaje: "Error de conexión." }); };
      xhr.send(datos);
    });
  }

  $(document).on("submit", "form[data-subida-varios]", function (e) {
    e.preventDefault();
    if (subiendo) return;
    var form = this;
    var $f = $(form);
    var input = $f.find("[data-subida-archivos]")[0];
    var max = parseInt($f.data("max-bytes"), 10) || 0;
    var csrfNombre = $('meta[name="csrf-param"]').attr("content");
    var csrfValor = $('meta[name="csrf-token"]').attr("content");
    var tareas = [];

    for (var i = 0; i < input.files.length; i++) {
      var file = input.files[i];
      if (max && file.size > max) {
        error("«" + file.name + "» pesa " + peso(file.size) + ". El máximo es " + $f.data("max-texto") + ".");
        return;
      }
      tareas.push({ nombre: file.name, archivo: file, leyenda: $f.find('[data-leyenda-de="' + i + '"]').val() || "" });
    }
    var url = $.trim($f.find("input[name=url]").val());
    if (url) {
      tareas.push({ nombre: "enlace", url: url, leyenda: $f.find("input[name=leyenda]").val() || "" });
    }
    if (!tareas.length) {
      error("Elija uno o varios archivos, o pegue un enlace de YouTube o Vimeo.");
      return;
    }

    var $btn = $f.find("[data-subida-boton]");
    var textoBtn = $btn.html();
    var $barra = $f.find("[data-subida-barra]").prop("hidden", false);
    var $prog = $barra.find(".progress-bar");
    var $texto = $barra.find("[data-subida-texto]");
    var errores = [];
    var hechos = 0;
    subiendo = true;
    $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Subiendo…');

    tareas.reduce(function (cadena, t, n) {
      return cadena.then(function () {
        var datos = new FormData();
        datos.append(csrfNombre, csrfValor);
        datos.append("leyenda", t.leyenda);
        if (t.archivo) { datos.append("archivo", t.archivo); } else { datos.append("url", t.url); }
        $texto.text("Subiendo " + (n + 1) + " de " + tareas.length + ": " + t.nombre + " — no cierres esta página.");
        $prog.css("width", "0%").text("0%");
        return subirUno(form.action, datos, function (p) { $prog.css("width", p + "%").text(p < 100 ? p + "%" : "Procesando…"); })
          .then(function (r) {
            if (r.exito) { hechos++; } else { errores.push(t.nombre + ": " + (r.mensaje || "no se pudo subir.")); }
          });
      });
    }, Promise.resolve()).then(function () {
      subiendo = false;
      $btn.prop("disabled", false).html(textoBtn);
      $barra.prop("hidden", true);
      if (!errores.length) {
        Swal.fire({ icon: "success", titleText: hechos === 1 ? "Listo, se agregó." : "Listo, se agregaron " + hechos + "." })
          .then(function () { window.location.reload(); });
      } else {
        Swal.fire({
          icon: hechos ? "warning" : "error",
          titleText: hechos ? "Se subieron " + hechos + ", pero hubo problemas" : "No se pudo subir",
          text: errores.join(" · ")
        }).then(function () { if (hechos) window.location.reload(); });
      }
    });
  });

  /* 9. Descripción de un archivo (modal) */
  $(document).on("click", "[data-leyenda-editar]", function (e) {
    e.preventDefault();
    var $b = $(this);
    Swal.fire({
      titleText: "Descripción",
      input: "textarea",
      inputValue: $b.data("leyenda") || "",
      inputPlaceholder: "Ej.: En este video se ve la llegada de la maquinaria",
      inputAttributes: { maxlength: $b.data("max") || 300 },
      showCancelButton: true,
      confirmButtonText: "Guardar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#bd944f"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      var datos = csrf();
      datos.leyenda = res.value;
      $.post($b.data("leyenda-editar"), datos)
        .done(function (r) {
          if (r && r.exito) { window.location.reload(); } else { error(r && r.mensaje); }
        })
        .fail(function () { error("Error de conexión. Intente de nuevo."); });
    });
  });

  /* 7. Ancho de las barras */
  function anchos() {
    $(".progress-bar[data-ancho]").each(function () {
      $(this).css("width", Math.max(0, Math.min(100, parseInt($(this).data("ancho"), 10) || 0)) + "%");
    });
  }
  $(anchos);
})(jQuery);
