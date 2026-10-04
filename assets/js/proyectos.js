document.querySelectorAll('.filtros a[data-estado]').forEach(function (enlace) {
    enlace.addEventListener('click', function (ev) {
        ev.preventDefault();
        var estado = enlace.getAttribute('data-estado');
        document.querySelectorAll('.filtros a').forEach(function (a) { a.classList.remove('activo'); });
        enlace.classList.add('activo');
        document.querySelectorAll('#grid-proyectos .card-proyecto').forEach(function (tarjeta) {
            tarjeta.style.display = (tarjeta.getAttribute('data-estado') === estado) ? '' : 'none';
        });
    });
});
var todos = document.getElementById('f-todos');
if (todos) {
    todos.addEventListener('click', function (ev) {
        ev.preventDefault();
        document.querySelectorAll('.filtros a').forEach(function (a) { a.classList.remove('activo'); });
        todos.classList.add('activo');
        document.querySelectorAll('#grid-proyectos .card-proyecto').forEach(function (tarjeta) { tarjeta.style.display = ''; });
    });
}
