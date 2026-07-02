// MTasking - Lógica del dashboard de proyectos

const API = 'http://localhost:8083/mtasking/backend/index.php';

// ---- Verificar sesión ----
(async function init() {
  try {
    const res = await fetch(`${API}/auth/me`, { credentials: 'include' });
    const data = await res.json();
    if (!data.user) {
      window.location.href = 'index.html';
      return;
    }
    document.getElementById('nav-username').textContent = data.user.nombre;
    sessionStorage.setItem('user_nombre', data.user.nombre);
    sessionStorage.setItem('user_id', data.user.id);
  } catch (_) {
    window.location.href = 'index.html';
    return;
  }

  loadProjects();
})();

// ---- Cerrar sesión ----
async function logout() {
  await fetch(`${API}/auth/logout`, { method: 'POST', credentials: 'include' });
  sessionStorage.clear();
  window.location.href = 'index.html';
}

// ---- Mostrar mensajes ----
function showMsg(id, msg, type = 'error') {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.className = `alert alert-${type} show`;
  setTimeout(() => el.classList.remove('show'), 4000);
}

// ---- Crear proyecto ----
async function createProject() {
  const nombre = document.getElementById('proj-nombre').value.trim();
  const desc   = document.getElementById('proj-desc').value.trim();

  if (!nombre) {
    showMsg('project-error', 'El nombre del proyecto es obligatorio.');
    return;
  }

  try {
    const res = await fetch(`${API}/projects`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ nombre, descripcion: desc }),
    });
    const data = await res.json();

    if (!res.ok) {
      showMsg('project-error', data.error || 'Error al crear proyecto.');
      return;
    }

    showMsg('project-success', 'Proyecto creado.', 'success');
    document.getElementById('proj-nombre').value = '';
    document.getElementById('proj-desc').value = '';
    loadProjects();
  } catch (e) {
    showMsg('project-error', 'No se pudo conectar con el servidor.');
  }
}

// ---- Cargar lista de proyectos ----
async function loadProjects() {
  const container = document.getElementById('projects-container');

  try {
    const res = await fetch(`${API}/projects`, { credentials: 'include' });
    const data = await res.json();

    if (!data.projects || data.projects.length === 0) {
      container.innerHTML = '<p class="empty-state">No tienes proyectos aún. ¡Crea el primero!</p>';
      return;
    }

    container.innerHTML = `<div class="project-grid">${data.projects.map(renderProject).join('')}</div>`;
  } catch (e) {
    container.innerHTML = '<p class="empty-state">Error al cargar proyectos.</p>';
  }
}

// ---- Renderizar tarjeta de proyecto ----
function renderProject(p) {
  const fecha = new Date(p.created_at).toLocaleDateString('es-CO', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
  const desc = p.descripcion ? p.descripcion.substring(0, 80) + (p.descripcion.length > 80 ? '...' : '') : 'Sin descripción';

  return `
    <div class="project-item">
      <div onclick="openProject(${p.id})" style="cursor:pointer;">
        <h4>${escapeHtml(p.nombre)}</h4>
        <p>${escapeHtml(desc)}</p>
        <div class="project-date">${fecha}</div>
      </div>
      <button class="btn btn-secondary btn-sm" style="margin-top:10px; width:100%;"
        onclick="event.stopPropagation(); openEditModal(${p.id}, '${escapeJs(p.nombre)}', '${escapeJs(p.descripcion || '')}')">
        Editar
      </button>
    </div>
  `;
}

// ---- Abrir proyecto ----
function openProject(id) {
  window.location.href = `project.html?id=${id}`;
}

// ---- Modal de edición ----
function openEditModal(id, nombre, descripcion) {
  document.getElementById('edit-id').value       = id;
  document.getElementById('edit-nombre').value   = nombre;
  document.getElementById('edit-desc').value     = descripcion;
  document.getElementById('edit-modal').classList.add('show');
}

function closeEditModal() {
  document.getElementById('edit-modal').classList.remove('show');
}

async function saveProject() {
  const id         = document.getElementById('edit-id').value;
  const nombre     = document.getElementById('edit-nombre').value.trim();
  const descripcion = document.getElementById('edit-desc').value.trim();

  if (!nombre) {
    showMsg('project-error', 'El nombre no puede estar vacío.');
    return;
  }

  try {
    const res = await fetch(`${API}/projects/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ nombre, descripcion }),
    });
    const data = await res.json();

    if (!res.ok) {
      showMsg('project-error', data.error || 'Error al actualizar.');
      return;
    }

    closeEditModal();
    showMsg('project-success', 'Proyecto actualizado.', 'success');
    loadProjects();
  } catch (e) {
    showMsg('project-error', 'No se pudo conectar con el servidor.');
  }

}

// Cerrar modal al click fuera
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
  });
});

// ---- Utilidades ----
function escapeHtml(str) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(str));
  return div.innerHTML;
}

function escapeJs(str) {
  return String(str).replace(/'/g, "\\'").replace(/\n/g, ' ');
}
