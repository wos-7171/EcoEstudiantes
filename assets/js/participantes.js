function ajustarNota(sel) {
    var campo = sel.form.querySelector('input[name="nota"]');
    if (!campo) return;
    if (sel.value === 'Participó') {
        campo.required = true;
    } else {
        campo.required = false;
        campo.value = '';
    }
}
