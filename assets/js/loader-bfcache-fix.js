/**
 * Fix global para loaders: evita que la pantalla de carga quede "pegada"
 * cuando el usuario vuelve atrás (restauración desde bfcache) y la oculta
 * automáticamente después de 3 segundos como máximo.
 */
(function () {
    'use strict';

    var LOADER_IDS = ['apc-loader-overlay', 'apc-pse-loading-overlay'];
    var AUTO_HIDE_MS = 3000;
    var hideTimers = new WeakMap();

    function hideLoaders() {
        LOADER_IDS.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        document.querySelectorAll('.overlay-cargando--abierto').forEach(function (el) {
            el.classList.remove('overlay-cargando--abierto');
        });
    }

    function scheduleAutoHide(el) {
        if (hideTimers.has(el)) clearTimeout(hideTimers.get(el));
        hideTimers.set(el, setTimeout(function () {
            el.style.display = 'none';
        }, AUTO_HIDE_MS));
    }

    // Al volver atrás/adelante, el navegador restaura la página desde bfcache
    // con el loader visible: ocultarlo siempre en ese caso.
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) hideLoaders();
    });

    // Ocultar cualquier loader residual al terminar de cargar la página.
    window.addEventListener('load', function () {
        setTimeout(hideLoaders, 150);
    });

    // Vigilar SOLO los loaders de transición de página (apc-loader-overlay,
    // apc-pse-loading-overlay). El overlay de procesamiento de pago
    // (.overlay-cargando de paso-dos) NO se auto-oculta: debe permanecer
    // visible hasta que el backend responda y la navegación ocurra.
    function watchLoader(el) {
        var obs = new MutationObserver(function () {
            if (el.style.display === 'flex') scheduleAutoHide(el);
        });
        obs.observe(el, { attributes: true, attributeFilter: ['style'] });
    }

    function init() {
        LOADER_IDS.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) watchLoader(el);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
