var estadoActivo = '';

function aplicarFiltros() {
    var texto = (document.getElementById('buscar-proyecto').value || '').toLowerCase().trim();
    var visibles = 0;
    document.querySelectorAll('#grid-proyectos .card-proyecto').forEach(function (tarjeta) {
        var coincideEstado = estadoActivo === '' || tarjeta.getAttribute('data-estado') === estadoActivo;
        var coincideTexto = texto === '' || (tarjeta.getAttribute('data-texto') || '').indexOf(texto) !== -1;
        var mostrar = coincideEstado && coincideTexto;
        tarjeta.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
    });
    var aviso = document.getElementById('sin-resultados');
    if (aviso) aviso.style.display = visibles === 0 ? '' : 'none';
}

document.querySelectorAll('.filtros a[data-estado]').forEach(function (enlace) {
    enlace.addEventListener('click', function (ev) {
        ev.preventDefault();
        estadoActivo = enlace.getAttribute('data-estado');
        document.querySelectorAll('.filtros a').forEach(function (a) { a.classList.remove('activo'); });
        enlace.classList.add('activo');
        aplicarFiltros();
    });
});
var todos = document.getElementById('f-todos');
if (todos) {
    todos.addEventListener('click', function (ev) {
        ev.preventDefault();
        estadoActivo = '';
        document.querySelectorAll('.filtros a').forEach(function (a) { a.classList.remove('activo'); });
        todos.classList.add('activo');
        aplicarFiltros();
    });
}
var buscar = document.getElementById('buscar-proyecto');
if (buscar) {
    buscar.addEventListener('input', aplicarFiltros);
}
