/* HUK · Mi cuenta del inversor
   1. Pestañas: la abierta queda en la URL (?tab=) para que al recargar o volver se mantenga.
   2. Foto de perfil: al elegirla se ve al instante y se envía sola (valida tipo y peso antes). */
(function () {
  "use strict";

  document.querySelectorAll(".huk-perfil-tab").forEach(function (b) {
    b.addEventListener("shown.bs.tab", function () {
      try {
        var url = new URL(window.location.href);
        url.searchParams.set("tab", b.getAttribute("data-tab"));
        window.history.replaceState(null, "", url.toString());
      } catch (e) { /* navegador viejo: no pasa nada */ }
    });
  });

  var input = document.querySelector("[data-foto-input]");
  if (!input) return;
  var TIPOS = /^image\/(jpeg|png|webp)$/, MAX = 8 * 1024 * 1024;
  var ayuda = document.querySelector("[data-foto-ayuda]");
  var textoAyuda = ayuda ? ayuda.textContent : "";

  input.addEventListener("change", function () {
    var f = input.files && input.files[0];
    if (!f) return;
    var error = !TIPOS.test(f.type) ? "Elija una foto JPG, PNG o WEBP." : (f.size > MAX ? "La foto pesa más de 8 MB." : "");
    if (ayuda) { ayuda.textContent = error || "Guardando su foto…"; ayuda.classList.toggle("is-error", !!error); }
    if (error) { input.value = ""; return; }

    var avatar = input.closest(".huk-perfil-avatar");
    var img = avatar.querySelector("[data-foto-img]");
    if (!img) {
      img = document.createElement("img");
      img.setAttribute("data-foto-img", "");
      img.alt = "Foto de perfil";
      var ini = avatar.querySelector("[data-foto-iniciales]");
      if (ini) ini.replaceWith(img); else avatar.prepend(img);
    }
    img.src = URL.createObjectURL(f);
    avatar.classList.add("is-subiendo");
    input.form.submit();
  });

  window.addEventListener("pageshow", function () {
    if (ayuda) { ayuda.textContent = textoAyuda; ayuda.classList.remove("is-error"); }
  });
})();
