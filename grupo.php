<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear grupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/grupo.css">
</head>

<body>
    <header>
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-4">
                <h4 class="text-primary fw-bold m-0">⚽ SocioMatch</h4>
                <nav class="d-none d-md-flex gap-3">
                    <a href="index.php" class="btn btn-link text-decoration-none">Inicio</a>
                    <a href="grupo.php" class="btn btn-link text-decoration-none active">Grupos</a>
                    <a href="metas.php" class="btn btn-link text-decoration-none">Metas Diarias</a>
                    <a href="predicciones.php" class="btn btn-link text-decoration-none">Predicciones</a>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="points-badge">
                    ⭐ <span id="userPoints">0</span>
                </div>
                <img src="img/image1.png" alt="perfil" class="rounded-circle"
                    style="width:40px; height:40px; object-fit:cover;">
            </div>
        </div>
    </header>

    <main class="container-fluid mt-3 chat-layout">
        <div class="row h-100">
            <!-- 🔹 Columna izquierda: crear grupo + lista de grupos -->
            <aside class="col-12 col-md-4 col-lg-3 chat-sidebar">
                <!-- Crear grupo -->
                <div class="card mb-2">
                    <div class="card-header py-2">
                        <strong>Crear grupo (3 personas)</strong>
                    </div>
                    <div class="card-body">
                        <div id="friendsList">
                            <p class="text-muted small">Cargando amigos...</p>
                        </div>

                        <div class="mt-3">
                            <label for="chatTitle" class="form-label mb-1">Nombre del grupo</label>
                            <input type="text" id="chatTitle" class="form-control form-control-sm"
                                placeholder="Escribe el nombre del grupo">
                        </div>

                        <button class="btn btn-primary btn-sm w-100 mt-3" id="createGroupBtn">
                            Crear grupo
                        </button>
                    </div>
                </div>

                <!-- Lista de grupos -->
                <div class="card flex-grow-1">
                    <div class="card-header py-2">
                        <strong>Mis grupos</strong>
                    </div>
                    <div class="card-body p-0">
                        <div id="groupsContainer">
                            <p class="text-muted small p-2 m-0">No hay grupos creados aún.</p>
                        </div>
                     
                    </div>
                    
                </div>
            </aside>

            <!-- 🔹 Columna derecha: chat del grupo -->
            <section class="col-12 col-md-8 col-lg-9 chat-main">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
    <div>
        <div class="chat-header-title" id="currentGroupName">Selecciona un grupo</div>
        <div class="chat-header-subtitle text-muted" id="currentGroupMembers">No hay grupo seleccionado</div>
    </div>

    <!-- 🔸 Botón Asignar Tarea -->
    <button id="btnAsignarTarea" class="btn btn-warning btn-sm text-white"  style="display:none;">
        <i class="bi bi-clipboard-check"></i> Asignar tarea
    </button>
</div>

                    <div id="groupMessages" class="messages-container">
                        <p class="text-muted text-center mt-5">
                            Elige un grupo de la izquierda para empezar a chatear.
                        </p>
                    </div>

                    <div class="card-footer">
                        <form id="groupMessageForm" class="d-flex gap-2">
                            <input type="text" id="groupMessageInput" class="form-control"
                                placeholder="Escribe un mensaje..." autocomplete="off">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>

<!-- 📌 MODAL PARA CREAR Y ASIGNAR TAREA AL GRUPO -->
<div class="modal fade" id="modalAsignarTarea" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Asignar tarea al grupo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <label class="form-label">Descripción de la tarea</label>
        <input type="text" id="tareaDescripcion" class="form-control mb-3" placeholder="Ej. Realizar una predicción">

        <label class="form-label">Puntos que otorgará</label>
        <input type="number" id="tareaPuntos" class="form-control" value="10" min="5" max="100">
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="crearTareaGrupo()">Crear y asignar</button>
      </div>

    </div>
  </div>
</div>


    </main>


    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <!-- ========= JS ========= -->
    <script>
        // ******************************************************
        // CONSTANTE DE BASE URL LOCAL (igual que en index.php)
        // Conexión con Socket.IO (mismo host, puerto 3000)
const socket = io(`${window.location.protocol}//${window.location.hostname}:3000`);

const BASE_API_URL = 'http://192.168.1.93/OR-INTERNET/api';
        // ******************************************************

        const createGroupBtn = document.getElementById('createGroupBtn');
        const groupsContainer = document.getElementById('groupsContainer');
        const friendsListDiv = document.getElementById('friendsList');
        const groupMessagesDiv = document.getElementById('groupMessages');
        const groupMessageForm = document.getElementById('groupMessageForm');
        const groupMessageInput = document.getElementById('groupMessageInput');
        const currentGroupNameEl = document.getElementById('currentGroupName');
        const currentGroupMembersEl = document.getElementById('currentGroupMembers');

        const user = JSON.parse(localStorage.getItem('usuario'));
        let currentGroupId = null;

        // 🚀 Al cargar la página
        document.addEventListener('DOMContentLoaded', async () => {
    if (!user) {
        alert('Debes iniciar sesión para ver tus grupos.');
        window.location.href = 'iniciosesion.php';
        return;
    }

    const userId = user.ID_USUARIO || user.id_usuario;
    document.getElementById('userPoints').textContent = user.puntos || 0;

    // Registrar usuario en socket (ya lo usas en index.php)
    socket.on('connect', () => {
        console.log('🟢 Conectado a Socket.IO (grupos):', socket.id);
        socket.emit('registrarUsuario', userId);
    });

    await cargarAmigosUsuario(userId);
    await cargarGruposUsuario(userId);
});


        // 📦 Cargar amigos
        async function cargarAmigosUsuario(userId) {
            friendsListDiv.innerHTML = '<p class="text-muted small">Cargando amigos...</p>';
            try {
                const res = await fetch(`${BASE_API_URL}/amigos.php?id=${userId}`);
                const amigos = await res.json();

                if (!amigos || amigos.length === 0) {
                    friendsListDiv.innerHTML = '<p class="text-muted small">Aún no tienes amigos agregados.</p>';
                    return;
                }

                friendsListDiv.innerHTML = '';
                amigos.forEach(a => {
                    const div = document.createElement('div');
                    div.classList.add('friend-item');
                    div.dataset.id = a.ID_USUARIO;

                    div.innerHTML = `
                        <span>${a.Username}</span>
                        <div class="status">
                            <span class="offline">⚪</span>
                            <input type="checkbox" 
                                   class="form-check-input friend-checkbox" 
                                   value="${a.ID_USUARIO}">
                        </div>
                    `;
                    friendsListDiv.appendChild(div);
                });

            } catch (err) {
                console.error('❌ Error al cargar amigos:', err);
                friendsListDiv.innerHTML = '<p class="text-danger small">Error al conectar con el servidor.</p>';
            }
        }

        // 📦 Cargar grupos
        async function cargarGruposUsuario(userId) {
            groupsContainer.innerHTML = '<p class="text-muted small p-2 m-0">Cargando grupos...</p>';
            try {
                const res = await fetch(`${BASE_API_URL}/grupos.php?user_id=${userId}`);
                const grupos = await res.json();

                if (!grupos || grupos.length === 0) {
                    groupsContainer.innerHTML = '<p class="text-muted small p-2 m-0">No hay grupos creados aún.</p>';
                    return;
                }

                groupsContainer.innerHTML = '';
                grupos.forEach(g => {
                    const div = document.createElement('div');
                    div.classList.add('group-item', 'border-bottom', 'px-3', 'py-2');

                    div.innerHTML = `
                        <div class="fw-semibold">${g.NOMBRE}</div>
                        <div class="text-muted small">Integrantes: ${g.miembros_nombres || 'N/D'}</div>
                    `;

                    div.addEventListener('click', () => {
                        document.querySelectorAll('.group-item').forEach(el => el.classList.remove('active'));
                        div.classList.add('active');
                        abrirChatGrupo(g.ID_GRUPO, g.NOMBRE, g.miembros_nombres || '');
                    });

                    groupsContainer.appendChild(div);
                });

            } catch (err) {
                console.error('❌ Error al cargar grupos:', err);
                groupsContainer.innerHTML = '<p class="text-danger small p-2 m-0">Error al conectar con el servidor.</p>';
            }
        }

        // 📤 Crear grupo (exactamente 3 personas)
        createGroupBtn.addEventListener('click', async () => {
            const title = document.getElementById('chatTitle').value.trim();
            const userId = user.ID_USUARIO || user.id_usuario;

            const selectedFriends = Array
                .from(document.querySelectorAll('.friend-checkbox:checked'))
                .map(cb => Number(cb.value));

            if (!userId) {
                alert("Debes iniciar sesión para crear un grupo.");
                return;
            }

            if (selectedFriends.length !== 2) {
                alert("Debes seleccionar exactamente 2 amigos (grupo de 3 personas: tú + 2 amigos).");
                return;
            }

            if (!title) {
                alert("Por favor ingresa un título para el grupo.");
                return;
            }

            const miembrosIds = [userId, ...selectedFriends];

            try {
                const res = await fetch(`${BASE_API_URL}/crear-grupo.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nombre: title,
                        creador_id: userId,
                        miembros_ids: miembrosIds
                    })
                });

                const data = await res.json();
                if (res.ok) {
                    alert('✅ Grupo creado correctamente');
                    await cargarGruposUsuario(userId);
                    document.getElementById('chatTitle').value = '';
                    document.querySelectorAll('.friend-checkbox:checked')
                        .forEach(cb => cb.checked = false);
                } else {
                    alert('⚠️ ' + (data.msg || 'Error al crear grupo'));
                }

            } catch (err) {
                console.error('❌ Error al conectar con el servidor:', err);
                alert('Error al conectar con el servidor.');
            }
        });

        // 💬 Abrir chat de grupo
        async function abrirChatGrupo(idGrupo, nombreGrupo, miembrosTexto) {
    currentGroupId = idGrupo;
    currentGroupNameEl.textContent = nombreGrupo;
    currentGroupMembersEl.textContent = miembrosTexto || 'Integrantes no disponibles';
   document.getElementById('btnAsignarTarea').style.display = 'inline-block';
   document.getElementById('btnAsignarTarea').addEventListener('click', function () {
    if (!currentGroupId) {
        alert("Primero selecciona un grupo.");
        return;
    }
    document.getElementById('btnAsignarTarea').onclick = abrirModalCrearTarea;
});

    // 🔵 Unirse a la sala Socket.IO de este grupo
    socket.emit('joinGrupo', { ID_GRUPO: idGrupo });

    groupMessagesDiv.innerHTML = '<p class="text-muted small text-center mt-3">Cargando mensajes...</p>';

    try {
        const res = await fetch(`${BASE_API_URL}/mensajes_grupo.php?id_grupo=${idGrupo}`);
        const mensajes = await res.json();

        renderMensajesGrupo(mensajes);
    } catch (err) {
        console.error('❌ Error al cargar mensajes de grupo:', err);
        groupMessagesDiv.innerHTML = '<p class="text-danger small text-center mt-3">Error al cargar mensajes.</p>';
    }
}

// Función para abrir el modal (asegúrate de que el modal tenga el ID correcto)
function abrirModalCrearTarea() {
  const modal = new bootstrap.Modal(document.getElementById('modalAsignarTarea'));
  modal.show();
}



        function renderMensajesGrupo(mensajes) {
            groupMessagesDiv.innerHTML = '';
            if (!mensajes || mensajes.length === 0) {
                groupMessagesDiv.innerHTML = '<p class="text-muted small text-center mt-3">No hay mensajes en este grupo aún.</p>';
                return;
            }

            const myId = user.ID_USUARIO || user.id_usuario;

            mensajes.forEach(m => {
                const isMine = Number(m.ID_EMISOR) === Number(myId);
                const bubble = document.createElement('div');
                bubble.classList.add('message-bubble', isMine ? 'message-mine' : 'message-other');

                bubble.innerHTML = `
                    <div class="message-author">${m.autor || (isMine ? 'Tú' : 'Usuario')}</div>
                    <div>${m.MENSAJE}</div>
                    <div class="message-time">${m.FECHA_ENVIO}</div>
                `;

                groupMessagesDiv.appendChild(bubble);
            });

            groupMessagesDiv.scrollTop = groupMessagesDiv.scrollHeight;
        }

        // ✉️ Enviar mensaje al grupo
      groupMessageForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const texto = groupMessageInput.value.trim();
    if (!texto) return;
    if (!currentGroupId) {
        alert("Selecciona un grupo primero.");
        return;
    }

    const myId = user.ID_USUARIO || user.id_usuario;

    // 1️⃣ Pintar al instante en MI pantalla
    const ahora = new Date();
    const horaLocal = ahora.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const bubble = document.createElement('div');
    bubble.classList.add('message-bubble', 'message-mine');
    bubble.innerHTML = `
        <div class="message-author">Tú</div>
        <div>${texto}</div>
        <div class="message-time">${horaLocal}</div>
    `;
    groupMessagesDiv.appendChild(bubble);
    groupMessagesDiv.scrollTop = groupMessagesDiv.scrollHeight;
    groupMessageInput.value = '';

    // 2️⃣ Guardar en la BD (PHP)
    try {
        const res = await fetch(`${BASE_API_URL}/enviar_mensaje_grupo.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_grupo: currentGroupId,
                id_emisor: myId,
                mensaje: texto
            })
        });

        const data = await res.json();

        if (!res.ok) {
            console.error('❌ Error en enviar_mensaje_grupo.php', data);
            return;
        }

        // 3️⃣ Avisar a los otros dispositivos por Socket.IO
        socket.emit('mensajeGrupoNuevo', {
            ID_GRUPO: currentGroupId,
            ID_EMISOR: myId,
            MENSAJE: texto,
            autor: user.Username,
            FECHA_ENVIO: horaLocal
        });

    } catch (err) {
        console.error('❌ Error enviando mensaje:', err);
    }
});

socket.on('recibirMensajeGrupo', (m) => {
    if (!currentGroupId) return;
    if (Number(m.ID_GRUPO) !== Number(currentGroupId)) return;

    const myId = user.ID_USUARIO || user.id_usuario;
    const isMine = Number(m.ID_EMISOR) === Number(myId);

    // evitar duplicar mis propios mensajes
    if (isMine) return;

    const bubble = document.createElement('div');
    bubble.classList.add('message-bubble', 'message-other');
    bubble.innerHTML = `
        <div class="message-author">${m.autor || 'Usuario'}</div>
        <div>${m.MENSAJE}</div>
        <div class="message-time">${m.FECHA_ENVIO || ''}</div>
    `;
    groupMessagesDiv.appendChild(bubble);
    groupMessagesDiv.scrollTop = groupMessagesDiv.scrollHeight;
});
async function crearTareaGrupo() {
    const descripcion = document.getElementById('tareaDescripcion').value;
    const puntos = document.getElementById('tareaPuntos').value;

    if (!descripcion || !currentGroupId) {
        alert("Falta descripción o no hay grupo seleccionado");
        return;
    }

    const response = await fetch(`${BASE_API_URL}/tareas/crear_tarea.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_grupo: currentGroupId,
            creador_id: user.ID_USUARIO,
            descripcion: descripcion,
            puntos: puntos
        })
    });

    const data = await response.json();
    if (data.success) {
        alert("Tarea asignada correctamente al grupo");
        bootstrap.Modal.getInstance(document.getElementById('modalAsignarTarea')).hide();
    } else {
        alert("Error al asignar tarea");
    }
}


    </script>
    

</body>

</html>