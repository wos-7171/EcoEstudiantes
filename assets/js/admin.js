var imagenes = document.getElementById('imagenes');
if (imagenes) {
    imagenes.addEventListener('change', function () {
        if (this.files.length > 3) {
            alert('Solo se pueden anexar 3 imagenes. Elige de nuevo.');
            this.value = '';
        }
    });
}
