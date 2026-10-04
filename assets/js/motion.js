(function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('a[href^="#"]').forEach(function (enlace) {
        enlace.addEventListener('click', function (e) {
            var id = enlace.getAttribute('href');
            if (!id || id === '#') return;
            var destino = document.querySelector(id);
            if (!destino) return;
            e.preventDefault();
            destino.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        });
    });

    var ruta = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.main-nav a, .sticky-nav a').forEach(function (enlace) {
        var href = (enlace.getAttribute('href') || '').split('/').pop();
        if (href && href === ruta) enlace.classList.add('activo');
    });

    if (reduce || !('IntersectionObserver' in window)) return;

    var objetivos = document.querySelectorAll('.card, .section, .chart-box');
    var visto = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (entrada) {
            if (entrada.isIntersecting) {
                entrada.target.classList.add('ee-visible');
                visto.unobserve(entrada.target);
            }
        });
    }, { threshold: 0.12 });
    objetivos.forEach(function (el) { visto.observe(el); });
})();
