/* ==========================================================================
   HUK-THE-ICHI · huk.js  (sin jQuery)
   Reemplaza: WOW.js, waypoints, easing, el spinner y el "back to top" de Electra.
   Cargar al final del <body>.
   ========================================================================== */
(function () {
  "use strict";

  var doc = document.documentElement;
  doc.classList.remove("no-js");

  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* 1. Aparición al hacer scroll: <div data-reveal data-reveal-delay="150"> */
  var revealEls = document.querySelectorAll("[data-reveal]");
  if (!("IntersectionObserver" in window) || reduceMotion) {
    revealEls.forEach(function (el) { el.classList.add("is-in"); });
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var delay = parseInt(el.getAttribute("data-reveal-delay") || "0", 10);
        setTimeout(function () { el.classList.add("is-in"); }, delay);
        io.unobserve(el);
      });
    }, { threshold: 0.15, rootMargin: "0px 0px -40px 0px" });
    revealEls.forEach(function (el) { io.observe(el); });
  }

  /* 2. Contadores: <b data-count="25" data-suffix="+">25+</b>
     Escribí el valor final dentro: sin JS (o para buscadores) se ve igual. */
  var counters = document.querySelectorAll("[data-count]");
  function runCounter(el) {
    var target = parseFloat(el.getAttribute("data-count"));
    var suffix = el.getAttribute("data-suffix") || "";
    var prefix = el.getAttribute("data-prefix") || "";
    if (reduceMotion) { el.textContent = prefix + target.toLocaleString("es-BO") + suffix; return; }
    var start = null, duration = 1400;
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = prefix + Math.round(target * eased).toLocaleString("es-BO") + suffix;
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  if (counters.length && "IntersectionObserver" in window) {
    var co = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { runCounter(e.target); co.unobserve(e.target); } });
    }, { threshold: 0.5 });
    counters.forEach(function (el) { co.observe(el); });
  } else {
    counters.forEach(runCounter);
  }

  /* 3. Navbar pegada: <nav class="huk-navbar" data-sticky> */
  var nav = document.querySelector(".huk-navbar[data-sticky]");
  /* 4. Botón volver arriba: <a href="#" class="huk-back-top"> */
  var backTop = document.querySelector(".huk-back-top");
  var navOffset = nav ? nav.offsetTop : 0;
  /* 3b. Header compacto: aparece cuando el header principal ya salió de la pantalla */
  var sticky = document.querySelector(".huk-sticky");
  var hdMain = document.querySelector(".huk-hd-main");
  function limiteSticky() { return hdMain ? hdMain.offsetTop + hdMain.offsetHeight + 40 : 300; }
  var stickyLimit = limiteSticky();
  if (sticky) sticky.querySelectorAll("a, button").forEach(function (el) { el.tabIndex = -1; });
  window.addEventListener("resize", function () { stickyLimit = limiteSticky(); });
  var ticking = false;
  function onScroll() {
    var y = window.scrollY || window.pageYOffset;
    if (nav) nav.classList.toggle("is-stuck", y > navOffset);
    if (sticky) {
      var visible = y > stickyLimit;
      if (visible !== sticky.classList.contains("is-visible")) {
        sticky.classList.toggle("is-visible", visible);
        sticky.setAttribute("aria-hidden", visible ? "false" : "true");
        sticky.querySelectorAll("a, button").forEach(function (el) { el.tabIndex = visible ? 0 : -1; });
      }
    }
    if (backTop) backTop.classList.toggle("is-visible", y > 400);
    ticking = false;
  }
  window.addEventListener("scroll", function () {
    if (!ticking) { requestAnimationFrame(onScroll); ticking = true; }
  }, { passive: true });
  onScroll();
  if (backTop) {
    backTop.addEventListener("click", function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: reduceMotion ? "auto" : "smooth" });
    });
  }

  /* 5. Zona para soltar archivos: <label class="huk-drop"><input type="file" hidden></label> */
  document.querySelectorAll(".huk-drop").forEach(function (zone) {
    var input = zone.querySelector("input[type=file]");
    var label = zone.querySelector("[data-drop-label]");
    ["dragenter", "dragover"].forEach(function (ev) {
      zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add("is-over"); });
    });
    ["dragleave", "drop"].forEach(function (ev) {
      zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove("is-over"); });
    });
    zone.addEventListener("drop", function (e) {
      if (input && e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event("change", { bubbles: true }));
      }
    });
    if (input && label) {
      input.addEventListener("change", function () {
        var names = Array.prototype.map.call(input.files, function (f) { return f.name; });
        label.textContent = names.length ? names.join(", ") : label.getAttribute("data-drop-label");
      });
    }
  });

  /* 6. NDA: habilita "Aceptar" solo al llegar al final del texto y marcar la casilla.
     <div class="huk-nda"> … <div class="huk-nda-text"> … <input id="nda-check"> … <button id="nda-ok"> */
  var ndaText = document.querySelector(".huk-nda-text");
  var ndaCheck = document.getElementById("nda-check");
  var ndaOk = document.getElementById("nda-ok");
  if (ndaText && ndaCheck && ndaOk) {
    var leido = false;
    function revisar() { ndaOk.disabled = !(leido && ndaCheck.checked); }
    function alFinal() {
      if (ndaText.scrollTop + ndaText.clientHeight >= ndaText.scrollHeight - 8) {
        leido = true; ndaCheck.disabled = false; revisar();
      }
    }
    ndaCheck.disabled = true; ndaOk.disabled = true;
    ndaText.addEventListener("scroll", alFinal, { passive: true });
    ndaCheck.addEventListener("change", revisar);
    alFinal(); // por si el texto es corto
  }

  /* 7. Hilo de comentarios: baja al último mensaje */
  document.querySelectorAll(".huk-thread").forEach(function (t) { t.scrollTop = t.scrollHeight; });
})();

/* ==========================================================================
   Selector de idioma con banderas (agregado)
   Lee el idioma de la cookie "googtrans" (/es/en) y traduce con doGTranslate
   o con el <select> oculto de GTranslate. Si no hay ninguno, usa la cookie y recarga.
   ========================================================================== */
(function () {
  "use strict";
  var sw = document.querySelector("[data-lang-switch]");
  if (!sw) return;
  var btn = sw.querySelector(".huk-lang-btn");
  var list = sw.querySelector(".huk-lang-list");
  var items = Array.prototype.slice.call(list.querySelectorAll("[data-lang]"));
  var BASE = "es";

  function idiomaActual() {
    var m = document.cookie.match(/(?:^|;\s*)googtrans=\/[^\/]+\/([^;]+)/);
    return m ? decodeURIComponent(m[1]) : BASE;
  }
  function marcar(code) {
    var item = items.filter(function (li) { return li.getAttribute("data-lang") === code; })[0] || items[0];
    items.forEach(function (li) { li.setAttribute("aria-selected", li === item ? "true" : "false"); });
    sw.querySelector("[data-lang-flag]").src = item.querySelector("img").src;
    sw.querySelector("[data-lang-name]").textContent = item.querySelector("span").textContent;
  }
  function abrir() { list.hidden = false; btn.setAttribute("aria-expanded", "true"); (list.querySelector('[aria-selected="true"]') || items[0]).focus(); }
  function cerrar(foco) { list.hidden = true; btn.setAttribute("aria-expanded", "false"); if (foco) btn.focus(); }
  function porCookie(code) {
    var host = location.hostname, exp = "expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
    document.cookie = "googtrans=; " + exp; document.cookie = "googtrans=; " + exp + "; domain=" + host; document.cookie = "googtrans=; " + exp + "; domain=." + host;
    if (code !== BASE) document.cookie = "googtrans=/" + BASE + "/" + code + "; path=/";
    location.reload();
  }
  function traducir(code) {
    marcar(code);
    var par = BASE + "|" + code;
    if (typeof window.doGTranslate === "function") { window.doGTranslate(par); return; }
    var sel = document.querySelector(".gtranslate_wrapper select");
    if (sel) { sel.value = par; if (sel.value === par) { sel.dispatchEvent(new Event("change", { bubbles: true })); return; } }
    porCookie(code);
  }

  marcar(idiomaActual());
  btn.addEventListener("click", function () { list.hidden ? abrir() : cerrar(true); });
  items.forEach(function (li) { li.addEventListener("click", function () { cerrar(true); traducir(li.getAttribute("data-lang")); }); });
  list.addEventListener("keydown", function (e) {
    var i = items.indexOf(document.activeElement);
    if (e.key === "ArrowDown") { e.preventDefault(); items[Math.min(i + 1, items.length - 1)].focus(); }
    else if (e.key === "ArrowUp") { e.preventDefault(); items[Math.max(i - 1, 0)].focus(); }
    else if (e.key === "Enter" || e.key === " ") { e.preventDefault(); if (i > -1) items[i].click(); }
    else if (e.key === "Escape" || e.key === "Tab") { cerrar(e.key === "Escape"); }
  });
  document.addEventListener("click", function (e) { if (!sw.contains(e.target)) cerrar(false); });
})();
