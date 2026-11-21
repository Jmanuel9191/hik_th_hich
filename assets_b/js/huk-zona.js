/* ==========================================================================
   HUK · Zona para subir archivos (app\widgets\ZonaArchivo)
   <div data-huk-zona data-lista="1|0"> <div class=huk-zona-caja> input file · texto · [data-huk-zona-lista] </div>
     [data-huk-zona-elegir] [data-huk-zona-quitar] </div>
   El input queda encima de la zona (invisible): clic y arrastrar funcionan solos. Este archivo solo muestra
   lo elegido (miniatura si es imagen, ícono si no), resalta al arrastrar y maneja Elegir / Quitar.
   ========================================================================== */
(function () {
  "use strict";

  var ICONOS = { pdf: "fa-file-pdf-o", doc: "fa-file-word-o", docx: "fa-file-word-o", xls: "fa-file-excel-o", xlsx: "fa-file-excel-o",
    ppt: "fa-file-powerpoint-o", pptx: "fa-file-powerpoint-o", mp4: "fa-file-video-o", mov: "fa-file-video-o", m4v: "fa-file-video-o",
    webm: "fa-file-video-o", avi: "fa-file-video-o", mkv: "fa-file-video-o" };

  function zonaDe(el) { return el.closest("[data-huk-zona]"); }
  function inputDe(z) { return z.querySelector("input[type=file]"); }
  function peso(b) { return b >= 1048576 ? (b / 1048576).toFixed(1) + " MB" : Math.max(1, Math.round(b / 1024)) + " KB"; }

  function pintar(z) {
    var input = inputDe(z), lista = z.querySelector("[data-huk-zona-lista]"), elegir = z.querySelector("[data-huk-zona-elegir]");
    var archivos = input && input.files ? Array.prototype.slice.call(input.files) : [];
    z.classList.toggle("tiene-archivo", archivos.length > 0);
    z.classList.toggle("is-deshabilitado", !!(input && input.disabled));
    if (elegir) elegir.querySelector("span").textContent = archivos.length ? elegir.getAttribute("data-texto-cambiar") : elegir.getAttribute("data-texto");
    if (!lista) return;
    lista.querySelectorAll("img").forEach(function (i) { URL.revokeObjectURL(i.src); });
    lista.innerHTML = "";
    if (z.getAttribute("data-lista") !== "1") return;
    archivos.forEach(function (f) {
      var item = document.createElement("div");
      item.className = "huk-zona-archivo";
      var ext = (f.name.split(".").pop() || "").toLowerCase();
      if (/^image\//.test(f.type)) {
        var img = document.createElement("img");
        img.src = URL.createObjectURL(f);
        img.alt = "";
        item.appendChild(img);
      } else {
        var ic = document.createElement("i");
        ic.className = "fa " + (ICONOS[ext] || "fa-file-o");
        ic.setAttribute("aria-hidden", "true");
        item.appendChild(ic);
      }
      var t = document.createElement("span");
      t.innerHTML = "<strong></strong><small></small>";
      t.querySelector("strong").textContent = f.name;
      t.querySelector("small").textContent = peso(f.size);
      item.appendChild(t);
      lista.appendChild(item);
    });
  }

  document.addEventListener("change", function (e) {
    if (e.target.matches("[data-huk-zona] input[type=file]")) pintar(zonaDe(e.target));
  });
  document.addEventListener("click", function (e) {
    var b = e.target.closest("[data-huk-zona-elegir],[data-huk-zona-quitar]");
    if (!b) return;
    var z = zonaDe(b), input = inputDe(z);
    if (!input || input.disabled) return;
    if (b.hasAttribute("data-huk-zona-elegir")) {
      input.click();
    } else {
      input.value = "";
      input.dispatchEvent(new Event("change", { bubbles: true }));
      if (window.jQuery) window.jQuery(input).trigger("change");
    }
  });
  ["dragenter", "dragover"].forEach(function (ev) {
    document.addEventListener(ev, function (e) {
      var z = e.target.closest && e.target.closest("[data-huk-zona]");
      if (z) z.classList.add("is-arrastrando");
    });
  });
  ["dragleave", "drop"].forEach(function (ev) {
    document.addEventListener(ev, function (e) {
      var z = e.target.closest && e.target.closest("[data-huk-zona]");
      if (z) z.classList.remove("is-arrastrando");
    });
  });
  document.addEventListener("reset", function (e) {
    setTimeout(function () { e.target.querySelectorAll && e.target.querySelectorAll("[data-huk-zona]").forEach(pintar); }, 0);
  }, true);
  document.querySelectorAll("[data-huk-zona]").forEach(pintar);
})();
