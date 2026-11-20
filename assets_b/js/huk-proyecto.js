/* ==========================================================================
   HUK-THE-ICHI · huk-proyecto.js — página de cada proyecto (sin jQuery). Va después de huk.js.
   1. Menú de anclas <nav data-pro-menu>: se pega debajo del header compacto cuando este aparece
      (huk.js 3b, .huk-sticky.is-visible) y marca .is-pegado al quedar fijo.
   2. Clic en una ancla [data-pro-ancla]: baja suave hasta la sección, descontando el header y el menú.
   3. Sección activa: subraya en dorado la que está leyendo y la centra en el menú (celular).
   Con "movimiento reducido" salta sin animación.
   4. Videos [data-pro-videos]: cambiar de video con la lista; YouTube/Vimeo se cargan recién al tocar play;
      los archivos sin menú del botón derecho (no se ofrece "Guardar video").
   ========================================================================== */
(function () {
  "use strict";

  var menu = document.querySelector("[data-pro-menu]");
  if (!menu) return;

  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var header = document.querySelector(".huk-sticky");
  var lista = menu.querySelector(".huk-pro-menu-lista");
  var enlaces = Array.prototype.slice.call(menu.querySelectorAll("[data-pro-ancla]"));
  var secciones = enlaces.map(function (a) { return document.getElementById(a.getAttribute("data-pro-ancla")); });

  /* 1. Debajo del header compacto */
  function altoHeader() {
    return header && header.classList.contains("is-visible") ? header.getBoundingClientRect().height : 0;
  }
  function acomodar() {
    menu.style.setProperty("--huk-pro-menu-top", altoHeader() + "px");
    menu.classList.toggle("is-pegado", menu.getBoundingClientRect().top <= altoHeader() + 1);
  }
  if (header && "MutationObserver" in window) {
    new MutationObserver(acomodar).observe(header, { attributes: true, attributeFilter: ["class"] });
  }

  /* 2. Scroll suave hasta la sección */
  function desplazamiento() {
    // al bajar siempre aparece el header compacto: se lo descuenta aunque todavía no esté visible
    var h = header ? header.getBoundingClientRect().height : 0;
    return h + menu.getBoundingClientRect().height + 12;
  }
  enlaces.forEach(function (a, i) {
    a.addEventListener("click", function (e) {
      var destino = secciones[i];
      if (!destino) return;
      e.preventDefault();
      var y = destino.getBoundingClientRect().top + window.pageYOffset - desplazamiento();
      window.scrollTo({ top: y, behavior: reduceMotion ? "auto" : "smooth" });
      if (history.replaceState) history.replaceState(null, "", "#" + destino.id);
      marcar(i);
    });
  });

  /* 3. Sección activa */
  var actual = -1;
  function marcar(i) {
    if (i === actual) return;
    actual = i;
    enlaces.forEach(function (a, j) {
      a.classList.toggle("is-activo", j === i);
      if (j === i) { a.setAttribute("aria-current", "location"); } else { a.removeAttribute("aria-current"); }
    });
    var a = enlaces[i];
    if (a && lista && lista.scrollWidth > lista.clientWidth) {
      lista.scrollTo({ left: a.offsetLeft - lista.clientWidth / 2 + a.offsetWidth / 2, behavior: reduceMotion ? "auto" : "smooth" });
    }
  }
  function seccionActual() {
    var limite = desplazamiento() + 40;
    var i = -1;
    secciones.forEach(function (s, j) {
      if (s && s.getBoundingClientRect().top <= limite) i = j;
    });
    // al final de la página, la última
    if (window.innerHeight + window.pageYOffset >= document.documentElement.scrollHeight - 4) i = secciones.length - 1;
    return i;
  }

  var pendiente = false;
  window.addEventListener("scroll", function () {
    if (pendiente) return;
    pendiente = true;
    requestAnimationFrame(function () {
      acomodar();
      var i = seccionActual();
      if (i === -1) { enlaces.forEach(function (a) { a.classList.remove("is-activo"); a.removeAttribute("aria-current"); }); actual = -1; }
      else marcar(i);
      pendiente = false;
    });
  }, { passive: true });
  window.addEventListener("resize", acomodar);
  acomodar();

  /* Si entró con #ancla en la dirección, ubica la sección descontando el menú */
  if (location.hash) {
    var idx = enlaces.map(function (a) { return "#" + a.getAttribute("data-pro-ancla"); }).indexOf(location.hash);
    if (idx > -1) {
      window.addEventListener("load", function () {
        var y = secciones[idx].getBoundingClientRect().top + window.pageYOffset - desplazamiento();
        window.scrollTo({ top: y, behavior: "auto" });
      });
    }
  }
})();

/* 4. Videos ----------------------------------------------------------------- */
(function () {
  "use strict";

  function iframe(src, titulo) {
    var f = document.createElement("iframe");
    f.src = src + (src.indexOf("?") > -1 ? "&" : "?") + "autoplay=1&rel=0";
    f.title = titulo || "Video";
    f.allow = "autoplay; encrypted-media; picture-in-picture; fullscreen";
    f.allowFullscreen = true;
    f.referrerPolicy = "strict-origin-when-cross-origin";
    return f;
  }
  function sinMenu(v) { v.addEventListener("contextmenu", function (e) { e.preventDefault(); }); }

  // Fachada de YouTube / Vimeo: al tocarla se pone el reproductor de verdad
  document.addEventListener("click", function (e) {
    var b = e.target.closest && e.target.closest("[data-pro-video-embed]");
    if (!b) return;
    b.parentNode.replaceChild(iframe(b.getAttribute("data-pro-video-embed"), b.getAttribute("data-titulo")), b);
  });

  document.querySelectorAll("[data-pro-videos]").forEach(function (caja) {
    var escenario = caja.querySelector("[data-pro-video-escenario]");
    escenario.querySelectorAll("video").forEach(sinMenu);
    var botones = caja.querySelectorAll("[data-pro-video]");
    botones.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var d;
        try { d = JSON.parse(btn.getAttribute("data-pro-video")); } catch (err) { return; }
        botones.forEach(function (o) { o.classList.toggle("is-activo", o === btn); o.toggleAttribute("aria-current", o === btn); });
        var viejo = escenario.querySelector("video");
        if (viejo) { viejo.pause(); viejo.removeAttribute("src"); viejo.load(); }
        escenario.innerHTML = "";
        if (d.tipo === "archivo") {
          var v = document.createElement("video");
          v.controls = true;
          v.playsInline = true;
          v.preload = "metadata";
          v.setAttribute("controlslist", "nodownload noremoteplayback");
          v.setAttribute("disablepictureinpicture", "");
          if (d.poster) v.poster = d.poster;
          v.src = d.src;
          v.title = d.titulo;
          sinMenu(v);
          escenario.appendChild(v);
          v.play().catch(function () { /* el navegador puede pedir un toque: queda el botón play */ });
        } else {
          escenario.appendChild(iframe(d.src, d.titulo));
        }
        if (escenario.getBoundingClientRect().top < 0) escenario.scrollIntoView({ block: "start", behavior: "smooth" });
      });
    });
  });
})();
