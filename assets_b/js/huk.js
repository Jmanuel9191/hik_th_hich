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
     Escriba el valor final dentro: sin JS (o para buscadores) se ve igual. */
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

  /* 2b. Contador de texto: <b data-count-texto>USD 5-10 mil M</b> — cada número del texto sube desde 0 a la vez
     (mantiene lo demás: "USD", "-", "mil M", "+"...). Los puntos de miles se respetan ("1.000"). */
  var contadoresTexto = document.querySelectorAll("[data-count-texto]");
  function contarTexto(el) {
    var original = el.getAttribute("data-count-texto") || el.textContent;
    el.setAttribute("data-count-texto", original);
    var partes = original.split(/(\d{1,3}(?:\.\d{3})+|\d+)/); // impares = números
    var metas = partes.map(function (p, i) { return i % 2 ? parseInt(p.replace(/\./g, ""), 10) : null; });
    function pintar(k) {
      el.textContent = partes.map(function (p, i) {
        if (!(i % 2)) return p;
        var v = Math.round(metas[i] * k);
        return p.indexOf(".") > -1 ? v.toLocaleString("es-BO") : String(v);
      }).join("");
    }
    if (reduceMotion) { el.textContent = original; return; }
    var inicio = null, dur = 1400;
    function paso(ts) {
      if (!inicio) inicio = ts;
      var t = Math.min((ts - inicio) / dur, 1);
      pintar(1 - Math.pow(1 - t, 3));
      if (t < 1) { requestAnimationFrame(paso); } else { el.textContent = original; }
    }
    pintar(0);
    requestAnimationFrame(paso);
  }
  if (contadoresTexto.length && "IntersectionObserver" in window) {
    var cto = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { contarTexto(e.target); cto.unobserve(e.target); } });
    }, { threshold: 0.5 });
    contadoresTexto.forEach(function (el) { cto.observe(el); });
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
  /* 3c. Barra de progreso de lectura: <div class="huk-progress"> (layout) */
  var progreso = document.querySelector(".huk-progress");
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
    if (progreso) {
      var alto = doc.scrollHeight - window.innerHeight;
      progreso.style.transform = "scaleX(" + (alto > 0 ? Math.min(y / alto, 1) : 0) + ")";
    }
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

  /* 8. Copiar al portapapeles: <button data-copiar="info@..." data-copiar-ok="¡Copiado!"> */
  function copiarTexto(texto) {
    if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(texto);
    return new Promise(function (ok, error) { // http (sin https): método viejo
      var t = document.createElement("textarea");
      t.value = texto; t.setAttribute("readonly", ""); t.className = "sr-only";
      document.body.appendChild(t); t.select();
      var hecho = false;
      try { hecho = document.execCommand("copy"); } catch (e) { hecho = false; }
      document.body.removeChild(t);
      hecho ? ok() : error();
    });
  }
  document.addEventListener("click", function (e) {
    var b = e.target.closest("[data-copiar]");
    if (!b) return;
    if (!b._hukOriginal) b._hukOriginal = b.innerHTML; // guarda el contenido (texto o ícono) para volver a ponerlo
    var original = b._hukOriginal;
    copiarTexto(b.getAttribute("data-copiar")).then(function () {
      b.textContent = b.getAttribute("data-copiar-ok") || "¡Copiado!";
      b.classList.add("is-ok");
    }, function () {
      b.textContent = b.getAttribute("data-copiar");
    });
    clearTimeout(b._hukCopiar);
    b._hukCopiar = setTimeout(function () { b.innerHTML = original; b.classList.remove("is-ok"); }, 2000);
  });

  /* 9. Punto dorado que sigue al mouse (solo con mouse y sin "movimiento reducido") */
  var conMouse = window.matchMedia && window.matchMedia("(hover: hover) and (pointer: fine)").matches;
  if (conMouse && !reduceMotion) {
    var punto = document.createElement("div");
    punto.className = "huk-cursor";
    punto.setAttribute("aria-hidden", "true");
    document.body.appendChild(punto);
    var mx = -100, my = -100, px = -100, py = -100, andando = false;
    var CLICABLE = "a, button, [role=button], label, select, summary, input[type=submit], input[type=checkbox], input[type=radio]";
    function mover() {
      px += (mx - px) * 0.22; // sigue al mouse con un poquito de retraso
      py += (my - py) * 0.22;
      punto.style.transform = "translate3d(" + px.toFixed(1) + "px," + py.toFixed(1) + "px,0)";
      if (Math.abs(mx - px) > 0.1 || Math.abs(my - py) > 0.1) { requestAnimationFrame(mover); } else { andando = false; }
    }
    document.addEventListener("mousemove", function (e) {
      if (px < 0) { px = e.clientX; py = e.clientY; } // primera vez: aparece donde está el mouse
      mx = e.clientX; my = e.clientY;
      punto.classList.add("is-on");
      punto.classList.toggle("is-link", !!(e.target.closest && e.target.closest(CLICABLE)));
      if (!andando) { andando = true; requestAnimationFrame(mover); }
    }, { passive: true });
    document.documentElement.addEventListener("mouseleave", function () { punto.classList.remove("is-on"); });
  }

  /* 10. Carrusel de uno en uno: <section data-banner> con [data-banner-slide], puntitos [data-banner-ir]
     y flechas [data-banner-anterior] / [data-banner-siguiente]. Lo usan el banner del Inicio y los
     proyectos destacados de cada ecosistema.
     Cambia solo cada 5 s (se pausa con el mouse encima, con el foco adentro o con la pestaña oculta).
     En celular se puede deslizar con el dedo; con el foco adentro, con ← →. Con "movimiento reducido" no cambia solo. */
  document.querySelectorAll("[data-banner]").forEach(function (banner) {
    var slides = banner.querySelectorAll("[data-banner-slide]");
    var puntos = banner.querySelectorAll("[data-banner-ir]");
    if (slides.length < 2) return;
    var actual = 0, timer = null, pausa = false, ESPERA = 5000;

    function ir(n) {
      n = (n + slides.length) % slides.length;
      if (n === actual) return;
      slides[actual].classList.remove("is-activo");
      slides[actual].setAttribute("aria-hidden", "true");
      slides[n].classList.add("is-activo");
      slides[n].removeAttribute("aria-hidden");
      puntos.forEach(function (p, i) {
        p.classList.toggle("is-activo", i === n);
        if (i === n) { p.setAttribute("aria-current", "true"); } else { p.removeAttribute("aria-current"); }
      });
      actual = n;
    }
    function programar() {
      clearTimeout(timer);
      if (!reduceMotion && !pausa && !document.hidden) timer = setTimeout(function () { ir(actual + 1); programar(); }, ESPERA);
    }
    puntos.forEach(function (p) {
      p.addEventListener("click", function () { ir(parseInt(p.getAttribute("data-banner-ir"), 10)); programar(); });
    });
    var anterior = banner.querySelector("[data-banner-anterior]");
    var siguiente = banner.querySelector("[data-banner-siguiente]");
    if (anterior) anterior.addEventListener("click", function () { ir(actual - 1); programar(); });
    if (siguiente) siguiente.addEventListener("click", function () { ir(actual + 1); programar(); });
    banner.addEventListener("mouseenter", function () { pausa = true; clearTimeout(timer); });
    banner.addEventListener("mouseleave", function () { pausa = false; programar(); });
    banner.addEventListener("focusin", function () { pausa = true; clearTimeout(timer); });
    banner.addEventListener("focusout", function () { pausa = false; programar(); });
    document.addEventListener("visibilitychange", programar);
    banner.addEventListener("keydown", function (e) {
      if (e.key === "ArrowLeft") { e.preventDefault(); ir(actual - 1); }
      if (e.key === "ArrowRight") { e.preventDefault(); ir(actual + 1); }
    });

    var x0 = null;
    banner.addEventListener("touchstart", function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    banner.addEventListener("touchend", function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      x0 = null;
      if (Math.abs(dx) > 50) { ir(actual + (dx < 0 ? 1 : -1)); programar(); }
    }, { passive: true });

    programar();
  });

  /* 11. Carrusel de tarjetas del Inicio: <section data-tarjetas> con [data-tarjetas-pista] y [data-tarjeta].
     Cuántas se ven lo dice el CSS (--tarjetas-por-vista: 3 / 2 / 1). Si entran todas: sin flechas y quieto.
     Si no: flechas y avanza solo cada 5 s (al llegar al final vuelve al principio). Pausa con el mouse
     encima o con el foco adentro; en celular se desliza con el dedo. */
  document.querySelectorAll("[data-tarjetas]").forEach(function (sec) {
    var pista = sec.querySelector("[data-tarjetas-pista]");
    var celdas = sec.querySelectorAll("[data-tarjeta]");
    var ant = sec.querySelector("[data-tarjetas-anterior]");
    var sig = sec.querySelector("[data-tarjetas-siguiente]");
    if (!pista || !celdas.length) return;
    var actual = 0, timer = null, pausa = false, ESPERA = 5000;

    function porVista() { return parseInt(getComputedStyle(sec).getPropertyValue("--tarjetas-por-vista"), 10) || 1; }
    function maximo() { return Math.max(0, celdas.length - porVista()); }
    function paso() { return celdas.length > 1 ? celdas[1].offsetLeft - celdas[0].offsetLeft : 0; }
    function mostrar() {
      var max = maximo(), n = porVista();
      if (actual > max) actual = max;
      pista.style.transform = actual ? "translateX(" + (-actual * paso()) + "px)" : "";
      if (ant) ant.hidden = max === 0;
      if (sig) sig.hidden = max === 0;
      celdas.forEach(function (c, i) {
        if (i < actual || i >= actual + n) { c.setAttribute("aria-hidden", "true"); } else { c.removeAttribute("aria-hidden"); }
      });
      programar();
    }
    function ir(n) {
      var max = maximo();
      actual = n > max ? 0 : (n < 0 ? max : n);
      mostrar();
    }
    function programar() {
      clearTimeout(timer);
      if (maximo() > 0 && !reduceMotion && !pausa && !document.hidden) timer = setTimeout(function () { ir(actual + 1); }, ESPERA);
    }

    if (ant) ant.addEventListener("click", function () { ir(actual - 1); });
    if (sig) sig.addEventListener("click", function () { ir(actual + 1); });
    sec.addEventListener("mouseenter", function () { pausa = true; clearTimeout(timer); });
    sec.addEventListener("mouseleave", function () { pausa = false; programar(); });
    sec.addEventListener("focusin", function () { pausa = true; clearTimeout(timer); });
    sec.addEventListener("focusout", function () { pausa = false; programar(); });
    document.addEventListener("visibilitychange", programar);
    var esperaResize = null;
    window.addEventListener("resize", function () { clearTimeout(esperaResize); esperaResize = setTimeout(mostrar, 150); });

    var x0 = null;
    pista.addEventListener("touchstart", function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    pista.addEventListener("touchend", function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      x0 = null;
      if (Math.abs(dx) > 50 && maximo() > 0) ir(actual + (dx < 0 ? 1 : -1));
    }, { passive: true });

    mostrar();
  });

  /* 12. Parallax de costado: <span data-parallax="0.12"> se corre en horizontal según dónde está su bloque
     al hacer scroll (baja → se corre a la derecha; sube → vuelve a la izquierda). El número es la intensidad.
     Con "movimiento reducido" queda quieto. */
  var paralax = document.querySelectorAll("[data-parallax]");
  if (paralax.length && !reduceMotion) {
    var paralaxPendiente = false;
    function moverParalax() {
      paralax.forEach(function (el) {
        var r = el.parentElement.getBoundingClientRect();
        if (r.bottom < -200 || r.top > window.innerHeight + 200) return; // fuera de pantalla: no hace nada
        var k = parseFloat(el.getAttribute("data-parallax")) || 0.12;
        el.style.translate = (r.top * -k).toFixed(1) + "px 0";
      });
      paralaxPendiente = false;
    }
    window.addEventListener("scroll", function () {
      if (!paralaxPendiente) { paralaxPendiente = true; requestAnimationFrame(moverParalax); }
    }, { passive: true });
    window.addEventListener("resize", moverParalax);
    moverParalax();
  }

  /* 13. Líneas de fondo (<div data-lineas> en el layout): empiezan donde termina el banner, para no pasar por él.
     Sin banner, cubren todo el contenido. Se reacomodan si el banner cambia de alto. */
  var lineas = document.querySelector("[data-lineas]");
  if (lineas) {
    var bannerArriba = document.querySelector("#contenido > .huk-banner");
    function ubicarLineas() {
      lineas.style.top = bannerArriba ? (bannerArriba.offsetTop + bannerArriba.offsetHeight) + "px" : "";
    }
    ubicarLineas();
    window.addEventListener("resize", ubicarLineas);
    window.addEventListener("load", ubicarLineas);
    if (bannerArriba && "ResizeObserver" in window) new ResizeObserver(ubicarLineas).observe(bannerArriba);
  }

  /* 14. Visión de expansión (Nosotros → Ejes estratégicos): <section data-expansion> con países [data-expansion-pais].
         Al pasar el mouse (o el foco) por un país de la lista se pone dorado y se resalta en el mapa;
         al pasar por un país del mapa se marca en la lista. Al salir, vuelve todo como estaba. */
  document.querySelectorAll("[data-expansion]").forEach(function (bloque) {
    var paises = bloque.querySelectorAll("[data-expansion-pais]");
    var paths = bloque.querySelectorAll(".huk-mapa-pais");
    function marcar(codigo) {
      paises.forEach(function (el) { el.classList.toggle("is-activo", el.getAttribute("data-expansion-pais") === codigo); });
      paths.forEach(function (path) { path.classList.toggle("is-seleccionado", path.getAttribute("data-pais") === codigo); });
    }
    paises.forEach(function (el) {
      var codigo = el.getAttribute("data-expansion-pais");
      el.addEventListener("mouseenter", function () { marcar(codigo); });
      el.addEventListener("focus", function () { marcar(codigo); });
      el.addEventListener("mouseleave", function () { marcar(null); });
      el.addEventListener("blur", function () { marcar(null); });
    });
    paths.forEach(function (path) {
      if (!path.classList.contains("is-activo")) return; // solo los países de la lista
      path.addEventListener("mouseenter", function () { marcar(path.getAttribute("data-pais")); });
      path.addEventListener("mouseleave", function () { marcar(null); });
    });
  });

  /* 15. Galería de fotos de una noticia: <div data-galeria> con [data-galeria-foto], flechas, miniaturas [data-galeria-ir]
         y contador [data-galeria-contador]. También con las flechas del teclado y deslizando el dedo. */
  document.querySelectorAll("[data-galeria]").forEach(function (g) {
    var fotos = g.querySelectorAll("[data-galeria-foto]");
    var miniaturas = g.querySelectorAll("[data-galeria-ir]");
    var contador = g.querySelector("[data-galeria-contador]");
    var actual = 0;
    if (fotos.length < 2) return;
    function ir(n) {
      actual = (n + fotos.length) % fotos.length;
      fotos.forEach(function (f, i) { f.classList.toggle("is-activa", i === actual); });
      miniaturas.forEach(function (m, i) {
        m.classList.toggle("is-activa", i === actual);
        if (i === actual && m.scrollIntoView) m.parentNode.scrollTo({ left: m.offsetLeft - m.parentNode.clientWidth / 2 + m.clientWidth / 2, behavior: "smooth" });
      });
      if (contador) contador.textContent = (actual + 1) + " / " + fotos.length;
    }
    var ant = g.querySelector("[data-galeria-anterior]");
    var sig = g.querySelector("[data-galeria-siguiente]");
    if (ant) ant.addEventListener("click", function () { ir(actual - 1); });
    if (sig) sig.addEventListener("click", function () { ir(actual + 1); });
    miniaturas.forEach(function (m) {
      m.addEventListener("click", function () { ir(parseInt(m.getAttribute("data-galeria-ir"), 10)); });
    });
    g.setAttribute("tabindex", "0");
    g.addEventListener("keydown", function (e) {
      if (e.key === "ArrowLeft") ir(actual - 1);
      if (e.key === "ArrowRight") ir(actual + 1);
    });
    var x0 = null;
    g.addEventListener("touchstart", function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    g.addEventListener("touchend", function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 40) ir(actual + (dx < 0 ? 1 : -1));
      x0 = null;
    });
  });

  /* 16. Pasar de página con las flechas del teclado: <a data-tecla-anterior> y <a data-tecla-siguiente>
         (ej. ecosistema anterior / siguiente). No actúa si se está escribiendo o hay una galería con el foco. */
  var teclaAnterior = document.querySelector("[data-tecla-anterior]");
  var teclaSiguiente = document.querySelector("[data-tecla-siguiente]");
  if (teclaAnterior || teclaSiguiente) {
    document.addEventListener("keydown", function (e) {
      if (e.altKey || e.ctrlKey || e.metaKey || e.shiftKey) return;
      var t = e.target;
      if (t.closest && (t.closest("input, textarea, select, [contenteditable], [data-galeria], [data-banner]"))) return;
      var destino = e.key === "ArrowLeft" ? teclaAnterior : (e.key === "ArrowRight" ? teclaSiguiente : null);
      if (destino) window.location.href = destino.href;
    });
  }

  /* 12. Proyectos destacados de a 2 (views/site/widgets/proyecto_carrusel.php): pista con scroll-snap.
     Se ven 2 (1 en celular). Si hay más: avanza de a UNO solo cada 5 s y al final vuelve al principio;
     flechas, puntitos (uno por posición), dedo y ← → del teclado. Pausa con el mouse encima o el foco adentro.
     Con "reducir movimiento" no avanza solo. */
  document.querySelectorAll("[data-proy-pista]").forEach(function (pista) {
    var control = pista.parentNode.querySelector("[data-proy-control]");
    if (!control) return;
    var puntos = control.querySelector("[data-proy-puntos]");
    var ant = control.querySelector("[data-proy-anterior]");
    var sig = control.querySelector("[data-proy-siguiente]");
    var items = pista.children, porVista = 1, posiciones = 1, timer = null, pausa = false, ESPERA = 5000, t;
    var paso = function () { return items.length > 1 ? items[1].offsetLeft - items[0].offsetLeft : pista.clientWidth; };
    var actual = function () {
      if (pista.scrollLeft + pista.clientWidth >= pista.scrollWidth - 2) return posiciones - 1;
      return Math.round(pista.scrollLeft / Math.max(paso(), 1));
    };
    var ir = function (n) {
      n = n > posiciones - 1 ? 0 : (n < 0 ? posiciones - 1 : n); // da la vuelta
      pista.scrollTo({ left: items[n].offsetLeft - items[0].offsetLeft, behavior: reduceMotion ? "auto" : "smooth" });
      programar();
    };
    var programar = function () {
      clearTimeout(timer);
      if (posiciones > 1 && !reduceMotion && !pausa && !document.hidden) timer = setTimeout(function () { ir(actual() + 1); }, ESPERA);
    };
    var marcar = function () {
      var a = actual();
      Array.prototype.forEach.call(puntos.children, function (b, i) {
        b.classList.toggle("is-activo", i === a);
        if (i === a) b.setAttribute("aria-current", "true"); else b.removeAttribute("aria-current");
      });
    };
    var armar = function () {
      porVista = Math.max(1, Math.round(pista.clientWidth / Math.max(paso(), 1)));
      posiciones = Math.max(1, items.length - porVista + 1);
      control.hidden = posiciones < 2;
      puntos.innerHTML = "";
      for (var i = 0; i < posiciones; i++) {
        var b = document.createElement("button");
        b.type = "button";
        b.className = "huk-banner-punto";
        b.setAttribute("aria-label", "Ir al proyecto " + (i + 1));
        b.addEventListener("click", ir.bind(null, i));
        puntos.appendChild(b);
      }
      marcar();
      programar();
    };
    ant.addEventListener("click", function () { ir(actual() - 1); });
    sig.addEventListener("click", function () { ir(actual() + 1); });
    pista.addEventListener("keydown", function (e) {
      if (e.key === "ArrowLeft") { e.preventDefault(); ir(actual() - 1); }
      else if (e.key === "ArrowRight") { e.preventDefault(); ir(actual() + 1); }
    });
    var zona = pista.parentNode;
    zona.addEventListener("mouseenter", function () { pausa = true; clearTimeout(timer); });
    zona.addEventListener("mouseleave", function () { pausa = false; programar(); });
    zona.addEventListener("focusin", function () { pausa = true; clearTimeout(timer); });
    zona.addEventListener("focusout", function () { pausa = false; programar(); });
    document.addEventListener("visibilitychange", programar);
    pista.addEventListener("scroll", function () { clearTimeout(t); t = setTimeout(marcar, 80); }, { passive: true });
    window.addEventListener("resize", function () { clearTimeout(t); t = setTimeout(armar, 150); });
    armar();
  });
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
