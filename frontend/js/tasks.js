// MTasking - Lógica de tareas dentro de un proyecto

const API = 'http://localhost:8083/mtasking/backend/index.php';

// Obtener ID del proyecto desde la URL
const params     = new URLSearchParams(window.location.search);
const projectId  = params.get('id');

if (!projectId) {
  window.location.href = 'dashboard.html';
}

// ---- Init ----
(async function init() {
  try {
    const res = await fetch(`${API}/auth/me`, { credentials: 'include' });
    const data = await res.json();
    if (!data.user) {
      window.location.href = 'index.html';
      return;
    }
    document.getElementById('nav-username').textContent = data.user.nombre;
  } catch (_) {
    window.location.href = 'index.html';
    return;
  }

  await loadProjectInfo();
  await loadUsers();
  await loadTasks();
})();

// ---- Cerrar sesión ----
async function logout() {
  await fetch(`${API}/auth/logout`, { method: 'POST', credentials: 'include' });
  sessionStorage.clear();
  window.location.href = 'index.html';
}

// ---- Cargar info del proyecto ----
async function loadProjectInfo() {
  try {
    const res = await fetch(`${API}/projects/${projectId}`, { credentials: 'include' });
    const data = await res.json();
    if (data.project) {
      document.getElementById('project-title').textContent = data.project.nombre;
      document.getElementById('proj-name').textContent     = data.project.nombre;
      document.getElementById('proj-desc').textContent     = data.project.descripcion || '';
      document.title = `MTasking - ${data.project.nombre}`;
    }
  } catch (_) {}
}

// ---- Cargar usuarios (para el select de responsable) ----
async function loadUsers() {
  try {
    const res = await fetch(`${API}/users`, { credentials: 'include' });
    const data = await res.json();
    const select = document.getElementById('task-responsable');
    (data.users || []).forEach(u => {
      const opt = document.createElement('option');
      opt.value = u.id;
      opt.textContent = u.nombre;
      select.appendChild(opt);
    });
  } catch (_) {}
}

// ---- Crear tarea ----
async function createTask() {
  const titulo       = document.getElementById('task-titulo').value.trim();
  const descripcion  = document.getElementById('task-desc').value.trim();
  const estado       = document.getElementById('task-estado').value;
  const responsable  = document.getElementById('task-responsable').value;

  if (!titulo) {
    showMsg('task-error', 'El título de la tarea es obligatorio.');
    return;
  }

  const body = {
    titulo,
    descripcion,
    estado,
    proyecto_id: parseInt(projectId),
    responsable_id: responsable ? parseInt(responsable) : null,
  };

  try {
    const res = await fetch(`${API}/tasks`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify(body),
    });
    const data = await res.json();

    if (!res.ok) {
      showMsg('task-error', data.error || 'Error al crear la tarea.');
      return;
    }

    showMsg('task-success', 'Tarea creada correctamente.', 'success');
    document.getElementById('task-titulo').value = '';
    document.getElementById('task-desc').value = '';
    document.getElementById('task-estado').value = 'Pendiente';
    document.getElementById('task-responsable').value = '';
    await loadTasks();
  } catch (e) {
    showMsg('task-error', 'No se pudo conectar con el servidor.');
  }
}

// ---- Cargar tareas ----
async function loadTasks() {
  const container = document.getElementById('tasks-container');

  try {
    const res = await fetch(`${API}/projects/${projectId}/tasks`, { credentials: 'include' });
    const data = await res.json();

    if (!data.tasks || data.tasks.length === 0) {
      container.innerHTML = '<p class="empty-state">Este proyecto no tiene tareas aún.</p>';
      return;
    }

    container.innerHTML = `<div class="task-list">${data.tasks.map(renderTask).join('')}</div>`;
  } catch (e) {
    container.innerHTML = '<p class="empty-state">Error al cargar las tareas.</p>';
  }
}

// ---- Renderizar tarea ----
function renderTask(t) {
  const responsable = t.responsable_nombre || 'Sin asignar';

  return `
    <div class="task-item">
      <div onclick="openTask(${t.id})" style="cursor:pointer; flex:1;">
        <div class="task-title">${escapeHtml(t.titulo)}</div>
        <div class="task-meta">Responsable: ${escapeHtml(responsable)}</div>
      </div>
      <div style="display:flex; align-items:center; gap:10px;">
        <select class="estado-select" onchange="changeStatus(${t.id}, this.value)" onclick="event.stopPropagation()">
          <option value="Pendiente"   ${t.estado==='Pendiente'   ? 'selected':''}>Pendiente</option>
          <option value="En progreso" ${t.estado==='En progreso' ? 'selected':''}>En progreso</option>
          <option value="Terminado"   ${t.estado==='Terminado'   ? 'selected':''}>Terminado</option>
        </select>
        <button class="btn btn-danger btn-sm" onclick="event.stopPropagation(); deleteTask(${t.id})">Eliminar</button>
      </div>
    </div>
  `;
}

// ---- Cambiar estado de tarea ----
async function changeStatus(id, estado) {
  try {
    await fetch(`${API}/tasks/${id}/status`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ estado }),
    });
    // Recargar lista para reflejar el cambio visual
    await loadTasks();
  } catch (e) {
    showMsg('task-error', 'No se pudo actualizar el estado.');
  }
}

// ---- Eliminar tarea ----
async function deleteTask(id) {
  if (!confirm('¿Eliminar esta tarea?')) return;
  try {
    await fetch(`${API}/tasks/${id}`, {
      method: 'DELETE',
      credentials: 'include',
    });
    await loadTasks();
  } catch (e) {
    showMsg('task-error', 'No se pudo eliminar la tarea.');
  }
}

// ---- Abrir detalle de tarea (modal) ----
async function openTask(id) {
  try {
    const res = await fetch(`${API}/tasks/${id}`, { credentials: 'include' });
    const data = await res.json();
    if (!data.task) return;

    const t = data.task;
    document.getElementById('modal-titulo').textContent      = t.titulo;
    document.getElementById('modal-descripcion').textContent = t.descripcion || '—';
    document.getElementById('modal-responsable').textContent = t.responsable_nombre || 'Sin asignar';
    document.getElementById('modal-proyecto').textContent    = t.proyecto_nombre;
    document.getElementById('modal-fecha').textContent       = new Date(t.created_at).toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' });

    const estadoEl = document.getElementById('modal-estado');
    estadoEl.innerHTML = `<span class="badge ${estadoBadge(t.estado)}">${escapeHtml(t.estado)}</span>`;

    document.getElementById('task-modal').classList.add('show');
  } catch (_) {}
}

// ---- Cerrar modal ----
function closeModal() {
  document.getElementById('task-modal').classList.remove('show');
}

// Cerrar modal al clickear fuera
document.getElementById('task-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// ---- Utilidades ----
function estadoBadge(estado) {
  if (estado === 'Pendiente')   return 'badge-pendiente';
  if (estado === 'En progreso') return 'badge-progreso';
  if (estado === 'Terminado')   return 'badge-terminado';
  return '';
}

function showMsg(id, msg, type = 'error') {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.className = `alert alert-${type} show`;
  setTimeout(() => el.classList.remove('show'), 4000);
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(String(str)));
  return div.innerHTML;
}
