// MTasking - Lógica de autenticación

const API = 'http://localhost:8083/mtasking/backend/index.php';

// Redirigir si ya está autenticado
(async function checkSession() {
  try {
    const res = await fetch(`${API}/auth/me`, { credentials: 'include' });
    const data = await res.json();
    if (data.user) {
      window.location.href = 'dashboard.html';
    }
  } catch (_) {}
})();

// ---- Cambiar entre tabs ----
function switchTab(tab) {
  document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  document.getElementById('form-' + tab).classList.add('active');
}

// ---- Mostrar mensaje ----
function showMsg(id, msg, type = 'error') {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.className = `alert alert-${type} show`;
  setTimeout(() => el.classList.remove('show'), 4000);
}

// ---- Registro ----
async function register() {
  const nombre   = document.getElementById('reg-nombre').value.trim();
  const email    = document.getElementById('reg-email').value.trim();
  const password = document.getElementById('reg-password').value.trim();

  if (!nombre || !email || !password) {
    showMsg('register-error', 'Completa todos los campos.');
    return;
  }

  try {
    const res = await fetch(`${API}/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre, email, password }),
    });
    const data = await res.json();

    if (!res.ok) {
      showMsg('register-error', data.error || 'Error al registrar.');
      return;
    }

    showMsg('register-success', '¡Cuenta creada! Ahora inicia sesión.', 'success');
    document.getElementById('reg-nombre').value = '';
    document.getElementById('reg-email').value = '';
    document.getElementById('reg-password').value = '';
    setTimeout(() => switchTab('login'), 1500);
  } catch (e) {
    showMsg('register-error', 'No se pudo conectar con el servidor.');
  }
}

// ---- Login ----
async function login() {
  const email    = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value.trim();

  if (!email || !password) {
    showMsg('login-error', 'Ingresa tu email y contraseña.');
    return;
  }

  try {
    const res = await fetch(`${API}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ email, password }),
    });
    const data = await res.json();

    if (!res.ok) {
      showMsg('login-error', data.error || 'Error al iniciar sesión.');
      return;
    }

    // Guardar nombre en sessionStorage para navbar
    sessionStorage.setItem('user_nombre', data.user.nombre);
    sessionStorage.setItem('user_id', data.user.id);
    window.location.href = 'dashboard.html';
  } catch (e) {
    showMsg('login-error', 'No se pudo conectar con el servidor.');
  }
}

// Permitir Enter en los inputs
document.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    const activeForm = document.querySelector('.auth-form.active').id;
    if (activeForm === 'form-login') login();
    else register();
  }
});
