/* HUK · "Quiero invertir" (views/proyectos/_interes_modal.php)
   - "A conversar" oculta el monto.
   - Envía el formulario por AJAX: marca los errores en cada campo o, si salió bien, muestra la confirmación
     en el modal y reemplaza los bloques de la página (banner y pie) por el estado nuevo, sin recargar. */
(function () {
  "use strict";
  var form = document.querySelector("[data-invertir-form]");
  if (!form) return;
  var modal = document.getElementById("modal-invertir");
  var cuerpo = form.querySelector("[data-invertir-cuerpo]");
  var pie = form.querySelector("[data-invertir-pie]");
  var aviso = form.querySelector("[data-invertir-aviso]");
  var enviar = form.querySelector("[data-invertir-enviar]");
  var zonaMonto = form.querySelector("[data-monto-zona]");

  function tipoMonto() {
    var r = form.querySelector('[name="QuieroInvertirForm[tipo_monto]"]:checked');
    return r ? r.value : "monto";
  }
  function actualizarMonto() { zonaMonto.hidden = tipoMonto() !== "monto"; }
  form.addEventListener("change", function (e) { if (e.target.matches("[data-tipo-monto]")) actualizarMonto(); });

  function limpiar() {
    aviso.hidden = true;
    form.querySelectorAll(".is-invalid").forEach(function (el) { el.classList.remove("is-invalid"); });
    form.querySelectorAll("[data-error-de]").forEach(function (el) { el.textContent = ""; el.classList.remove("d-block"); });
  }
  function marcar(errores) {
    var primero = null;
    Object.keys(errores).forEach(function (id) {
      var input = document.getElementById(id);
      var caja = form.querySelector('[data-error-de="' + id + '"]');
      if (input) { input.classList.add("is-invalid"); primero = primero || input; }
      if (caja) { caja.textContent = errores[id]; caja.classList.add("d-block"); primero = primero || caja; }
    });
    if (primero) { primero.scrollIntoView({ block: "center", behavior: "smooth" }); if (primero.focus) primero.focus({ preventScroll: true }); }
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    limpiar();
    enviar.disabled = true;
    var texto = enviar.innerHTML;
    enviar.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Enviando…';
    fetch(form.action, { method: "POST", body: new FormData(form), headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }, credentials: "same-origin" })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (r) {
        if (!r.exito) {
          aviso.textContent = r.mensaje || "Revise los campos marcados.";
          aviso.hidden = false;
          marcar(r.errores || {});
          return;
        }
        Object.keys(r.zonas || {}).forEach(function (lugar) {
          document.querySelectorAll('[data-interes-zona="' + lugar + '"]').forEach(function (z) { z.outerHTML = r.zonas[lugar]; });
        });
        cuerpo.hidden = true;
        pie.hidden = true;
        var ok = document.createElement("div");
        ok.className = "huk-inv-exito";
        ok.setAttribute("data-invertir-exito", "");
        ok.innerHTML = '<div class="huk-inv-exito-icono"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>'
          + "<h3></h3><p></p>"
          + '<button type="button" class="huk-btn-oro" data-bs-dismiss="modal">Entendido</button>';
        ok.querySelector("h3").textContent = "¡Listo!";
        ok.querySelector("p").textContent = r.mensaje;
        form.appendChild(ok);
        ok.querySelector("button").focus();
      })
      .catch(function () {
        aviso.textContent = "No se pudo enviar. Revise su conexión e intente de nuevo.";
        aviso.hidden = false;
      })
      .finally(function () { enviar.disabled = false; enviar.innerHTML = texto; });
  });

  // Al cerrar después de enviar: vuelve el formulario (ya con lo guardado) para poder actualizarlo.
  modal.addEventListener("hidden.bs.modal", function () {
    var ok = form.querySelector("[data-invertir-exito]");
    if (!ok) return;
    ok.remove();
    cuerpo.hidden = false;
    pie.hidden = false;
    enviar.innerHTML = '<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Guardar cambios';
    var ante = modal.querySelector(".huk-modal-ante");
    if (ante) ante.textContent = "Actualizar mi interés";
    var acepto = document.getElementById("quieroinvertirform-acepto");
    if (acepto) acepto.checked = false;
  });

  actualizarMonto();
})();
