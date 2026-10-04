// ═══════════════════════════════════════════════════════════
// PARTE 1 — Referencias del DOM
// ═══════════════════════════════════════════════════════════
const passInput = document.getElementById('password');
const toggleBtn = document.getElementById('togglePass');

const regPassword = document.getElementById('regPassword');
const regPasswordConf = document.getElementById('regPasswordConf');
const toggleRegBtn = document.getElementById('toggleRegPass');
const toggleRegConfBtn = document.getElementById('toggleRegPassConf');

// ═══════════════════════════════════════════════════════════
// PARTE 2 — Validaciones reutilizables
// ═══════════════════════════════════════════════════════════
function validarPassword(pass) {
    const errores = [];
    if (pass.length < 8) errores.push('Mínimo 8 caracteres');
    if (!/[A-Z]/.test(pass)) errores.push('Al menos una mayúscula');
    if (!/[0-9]/.test(pass)) errores.push('Al menos un número');
    return errores;
}

// ═══════════════════════════════════════════════════════════
// PARTE 3 — Mostrar / Ocultar contraseñas
// ═══════════════════════════════════════════════════════════
function crearToggle(btn, input) {
    if (!btn || !input) return;
    btn.addEventListener('click', () => {
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = 'Ocultar';
        } else {
            input.type = 'password';
            btn.textContent = 'Mostrar';
        }
    });
}

crearToggle(toggleBtn, passInput);
crearToggle(toggleRegBtn, regPassword);
crearToggle(toggleRegConfBtn, regPasswordConf);

// ═══════════════════════════════════════════════════════════
// PARTE 4 — Medidor de fortaleza de contraseña (registro)
// ═══════════════════════════════════════════════════════════
const barraFuerza = document.getElementById('barraFuerza');
const textoFuerza = document.getElementById('textoFuerza');

if (regPassword) {
    regPassword.addEventListener('input', () => {
        const errores = validarPassword(regPassword.value);
        const puntaje = 3 - errores.length;

        const config = [
            { pct: '0%', color: '#e0e0e0', label: '' },
            { pct: '33%', color: '#e53935', label: 'Débil' },
            { pct: '66%', color: '#ffb300', label: 'Regular' },
            { pct: '100%', color: '#43a047', label: 'Fuerte' },
        ][puntaje];

        barraFuerza.style.width = config.pct;
        barraFuerza.style.background = config.color;
        textoFuerza.textContent = config.label;
    });
}

// ═══════════════════════════════════════════════════════════
// PARTE 5 — Alternar entre Login y Registro
// ═══════════════════════════════════════════════════════════
function mostrarTab(tab) {
    const panelLogin = document.getElementById('panelLogin');
    const panelRegistro = document.getElementById('panelRegistro');
    const tabLogin = document.getElementById('tabLogin');
    const tabRegister = document.getElementById('tabRegister');

    if (tab === 'login') {
        panelLogin.style.display = 'block';
        panelRegistro.style.display = 'none';
        tabLogin.classList.add('active');
        tabRegister.classList.remove('active');
    } else {
        panelLogin.style.display = 'none';
        panelRegistro.style.display = 'block';
        tabLogin.classList.remove('active');
        tabRegister.classList.add('active');
    }
}