<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocioMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <header>
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-4">
                <h4 class="text-primary fw-bold m-0">⚽ SocioMatch</h4>
                <nav class="d-none d-md-flex gap-3">
                    <a href="iniciosesion.php" class="btn btn-link text-decoration-none active">Inicio de sesion</a>
                    <a href="grupo.php" class="btn btn-link text-decoration-none">Crear grupo</a>
                    <a href="metas.php" class="btn btn-link text-decoration-none">Metas Diarias</a>
                    <a href="predicciones.php" class="btn btn-link text-decoration-none">Predicciones</a>
                    <a href="registro.php" class="btn btn-link text-decoration-none">Registro</a>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-3">
                <input type="text" class="form-control form-control-sm d-none d-sm-block" style="width:200px;"
                    placeholder="Buscar...">

                <div class="points-badge">
                    ⭐ <span id="userPoints">0</span>
                </div>

                <img src="img/usuario-generico.png" alt="perfil" class="rounded-circle"
                    style="width:40px; height:40px; object-fit:cover;" id="profileImg">

                <button id="btnCerrarSesion" class="btn btn-outline-danger btn-sm" style="display:none;">
                    Cerrar sesión
                </button>
            </div>
        </div>
    </header>

    <div class="container-fluid mt-4">
        <div class="row">
              <aside class="col-lg-2 d-none d-lg-block sidebar">
            <div class="card p-3">
                <h6 class="fw-bold">Metas Diarias</h6>
                <ul id="metasList" class="list-unstyled small">
                    <li>✔️ Predecir 1 partido</li>
                    <li>⬜ Agregar 1 amigo</li>
                    <li>⬜ Ganar 20 puntos</li>
                </ul>
                <a href="metas.php" class="btn btn-sm btn-outline-success mt-2">Ver más</a>
            </div>

            <hr>

            <div class="card mt-3 p-2">
                <h6 class="fw-bold">Mis Amigos</h6>
                <div id="sidebarFriendsList">
                    <p class="text-muted small">Aún no tienes amigos agregados.</p>
                </div>
            </div>
        </aside>

              <section class="col-lg-7">
            <div class="row g-3">
                <!-- 🟢 PARTIDO EN CURSO (API) -->
                <div class="col-12">
                    <div class="card p-3 shadow-sm">
                        <h6 class="fw-bold">Partido en curso</h6>
                        <div id="partidoEnCurso" class="mt-2">
                            <em>Cargando partido...</em>
                        </div>
                    </div>
                </div>

                <!-- 🟢 AMIGOS -->
                <div class="col-12">
                    <div class="card p-3">
                        <h6 class="fw-bold">Amigos</h6>
                        <input type="text" id="searchUser" class="form-control mb-2"
                            placeholder="Buscar usuario por username">
                        <div id="searchResults" class="mb-2"></div>
                        <div id="friendsList" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </section>

            <aside class="col-lg-3">
                     <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold m-0" id="chatTitle">Chat</h6>
                        <div class="d-flex align-items-center gap-2">
                        
                          <button id="btnVideollamada" class="btn btn-outline-primary btn-sm rounded-circle">
    <i class="bi bi-camera-video-fill"></i>
</button>
                            <button id="btnOpcionesChat" class="btn btn-outline-success btn-sm rounded-circle">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chat-box mb-2"></div>
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Selecciona un amigo para chatear">
                        <button class="btn btn-primary">Enviar</button>
                    </div>
                </div>
                <div id="videoPopup" class="video-popup" style="display:none;">
                    <div class="video-content">
                        <button id="closePopup" class="btn btn-sm btn-danger mb-2">Cerrar</button>
                        <h5 class="text-center text-primary">Videollamada</h5>
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <video id="myVideo" autoplay playsinline muted class="rounded border"
                                style="width:45%; min-width:200px;"></video>
                            <video id="friendVideo" autoplay playsinline class="rounded border"
                                style="width:45%; min-width:200px;"></video>
                        </div>
                    </div>
                </div>

            </aside>
        </div>
    </div>

    <footer class="text-center text-muted small py-4">
        SocioMatch • Quinela + chat con amigos
    </footer>

    <script>
        // ******************************************************
        // CONSTANTE DE BASE URL (HA SIDO MODIFICADA)
        // Reemplaza 'sociomatch' con el nombre de tu carpeta si es diferente
       const BASE_API_URL = 'http://192.168.1.120/api'; 
        // ******************************************************

        // Manejo de usuario logueado / cierre de sesión
        document.addEventListener("DOMContentLoaded", async () => {
            const user = JSON.parse(localStorage.getItem('usuario'));

            const userPoints = document.getElementById('userPoints');
            const profileImg = document.getElementById('profileImg');
            const loginBtn = document.querySelector('nav a[href="iniciosesion.php"]'); // Ajuste a .php
            const registerBtn = document.querySelector('nav a[href="registro.php"]'); // Ajuste a .php
            const btnCerrarSesion = document.getElementById('btnCerrarSesion');

            if (user) {
                userPoints.textContent = user.puntos || 0;
                profileImg.src = user.img_p ? `data:image/png;base64,${user.img_p}` : 'img/image1.png';

                if (loginBtn) loginBtn.remove();
                if (registerBtn) registerBtn.remove();

                btnCerrarSesion.style.display = 'inline-block';
            } else {
                userPoints.textContent = 0;
                profileImg.src = 'img/usuario-generico.png';
                btnCerrarSesion.style.display = 'none';
            }

            btnCerrarSesion.addEventListener('click', () => {
                localStorage.removeItem('usuario');
                location.reload();
            });

    function getCurrentUser() {
    try {
        const userJson = localStorage.getItem('user');
        if (userJson) {
            const userData = JSON.parse(userJson);
            // Asegúrate de que los nombres de las propiedades coincidan con tu base de datos
            return { 
                id: userData.ID_USUARIO, 
                username: userData.Username 
            };
        }
    } catch (e) {
        console.error("Error al obtener datos del usuario de localStorage:", e);
    }
    return null; // Retorna null si no hay datos
    }

            // ---------- amigos y chat con DB real ----------
            const searchInput = document.getElementById('searchUser');
            const searchResults = document.getElementById('searchResults');
            const friendsList = document.getElementById('friendsList');
            const chatBox = document.querySelector('.chat-box');
            const input = document.querySelector('.input-group input');
            const sendBtn = document.querySelector('.input-group button');

            let friends = [];
            let chats = {};

            async function renderFriends() {
                if (!user) return;
                console.log("Usuario guardado:", user);
              const res = await fetch(`${BASE_API_URL}/amigos.php?id=${user.ID_USUARIO}`);
                
                friends = await res.json();
                friendsList.innerHTML = '';
                friends.forEach(f => {
                    const div = document.createElement('div');
                    div.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'border-bottom', 'py-2');
                    div.innerHTML = `
                        <div>
                            <img src="${f.img_p || 'img/usuario-generico.png'}" class="rounded-circle me-2" style="width:30px;height:30px;object-fit:cover;">
                            ${f.Username} <small class="text-muted">Amigo</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary btnMessage">mensaje</button>
                        </div>
                    `;
                    friendsList.appendChild(div);
                  div.querySelector('.btnMessage').addEventListener('click', () => {
                    openChat(f.Username, f.ID_USUARIO); 
                });


                });
                 renderSidebarFriends();
            }

            // 🟢 NUEVA función que llena la card lateral
           function renderSidebarFriends() {

            const sidebarFriends = document.getElementById('sidebarFriendsList');
            sidebarFriends.innerHTML = '';
            if (friends.length === 0) {
                sidebarFriends.innerHTML = `<p class="text-muted small">Aún no tienes amigos agregados.</p>`;
                return;
            }


            friends.forEach(f => {
                const div = document.createElement('div');
                div.classList.add('d-flex', 'align-items-center', 'mb-2');
                div.innerHTML = `
                <img src="${f.img_p || 'img/usuario-generico.png'}" 
                 class="rounded-circle me-2" 
                 style="width:30px;height:30px;object-fit:cover;">
                 <span>${f.Username}</span>
                 `;
                 sidebarFriends.appendChild(div);
                });
            }

            // 🔍 Buscar usuarios en la BD y mostrar botón "Agregar"
            searchInput.addEventListener('input', async () => {
                const query = searchInput.value.trim();
                searchResults.innerHTML = '';
                if (!query) return;

                try {
                    // ******************************************************
                    // CAMBIO DE URL: De Render a XAMPP (API PHP)
                    const res = await fetch(`${BASE_API_URL}/usuarios.php?q=${query}`); 
                    // ******************************************************
                    const users = await res.json();

                    console.log('👀 Usuarios recibidos:', users);

                    users.forEach(u => {
                        // Evitar mostrar al mismo usuario o amigos ya agregados
                        if (friends.some(f => f.ID_USUARIO === u.ID_USUARIO) || u.ID_USUARIO === user.ID_USUARIO) return;

                        const div = document.createElement('div');
                        div.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'border', 'p-1', 'mb-1', 'rounded');
                        div.innerHTML = `
                            <div class="d-flex align-items-center">
                                <img src="${u.img_p ? `data:image/png;base64,${u.img_p}` : 'img/usuario-generico.png'}"
                                    class="rounded-circle me-2"
                                    style="width:30px;height:30px;object-fit:cover;">
                                ${u.Username}
                            </div>
                            <button class="btn btn-sm btn-primary">Agregar</button>
                        `;
                        searchResults.appendChild(div);

                        div.querySelector('button').addEventListener('click', async () => {
                            try {
                                // ******************************************************
                                // CAMBIO DE URL: De Render a XAMPP (API PHP)
                                const res = await fetch(`${BASE_API_URL}/agregar_amigo.php`, {
                                   
                                // ******************************************************
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                 body: JSON.stringify({
                                    ID_USUARIO1: user.ID_USUARIO,   // TU ID guardado
                                    ID_USUARIO2: u.ID_USUARIO       // ID del amigo encontrado
                                    })

                                });
                                const data = await res.json();
                                console.log('📩 Respuesta al agregar amigo:', data);
                                 console.log('Voy a mandar:', {ID_USUARIO1: user.ID_USUARIO, ID_USUARIO2: u.ID_USUARIO});

                                if (res.ok) {
                                    alert('Amigo agregado correctamente');
                                    renderFriends();
                                    searchResults.innerHTML = '';
                                    searchInput.value = '';
                                } else {
                                    alert('Error al agregar amigo: ' + (data.msg || ''));
                                }
                            } catch (err) {
                                console.error('Error en fetch:', err);
                                alert('Error al conectar con el servidor');
                            }
                        });
                    });

                } catch (error) {
                    console.error('❌ Error cargando usuarios:', error);
                }
            });

            



            // Llamamos la función cuando cargue la página:
             document.addEventListener("DOMContentLoaded", loadLiveMatch);
          async function openChat(friendUsername, friendId) {
    chats = {}; // Ya no usamos localStorage

    const chatTitle = document.getElementById('chatTitle');
    chatTitle.textContent = `Chat con ${friendUsername}`;

    chatBox.innerHTML = '';
    input.placeholder = `Escribe un mensaje a ${friendUsername}...`;

    // 1️⃣ Obtener ID_CONVERSACION desde PHP (si no existe, lo crea)
    const res = await fetch(`${BASE_API_URL}/obtener_conversacion.php?id1=${user.ID_USUARIO}&id2=${friendId}`);
    const data = await res.json();
    const conversationId = data.ID_CONVERSACION;

    // 2️⃣ Cargar historial desde BD
    fetch(`${BASE_API_URL}/obtener_mensajes.php?ID_CONVERSACION=${conversationId}`)
        .then(res => res.json())
        .then(mensajes => {
            mensajes.forEach(m => {
                addMessageToUI(m.MENSAJE, m.ID_EMISOR == user.ID_USUARIO);
            });
        });

    // 3️⃣ Enviar mensaje: guardar en BD + mostrar en UI
    sendBtn.onclick = () => {
    const text = input.value.trim();
    if (!text) return;

    addMessageToUI(text, true);
    input.value = '';

    // 🔹 Guardar en la base de datos
    fetch(`${BASE_API_URL}/enviar_mensaje.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            ID_CONVERSACION: conversationId,
            ID_EMISOR: user.ID_USUARIO,
            MENSAJE: text
        })
    });

    // 🔹 Enviar en tiempo real por socket
    socket.emit("mensajePrivado", {
        de: user.ID_USUARIO,
        para: friendId,
        texto: text
    });
};

}
            renderFriends();
        });
        
    </script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
document.getElementById('btnSend').addEventListener('click', sendMessage);

async function sendMessage() {
    const content = document.getElementById('msgInput').value;
    if (!content) return;

    await fetch('api/messages.php?action=send', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            sender_id: currentUserId,
            receiver_id: currentFriendId,
            content: content
        })
    });

    document.getElementById('msgInput').value = '';
    loadMessages();
}

async function loadMessages() {
    const res = await fetch(`api/messages.php?action=get&user_id=${currentUserId}&friend_id=${currentFriendId}`);
    const data = await res.json();

    let html = '';
    data.messages.forEach(msg => {
        html += `<div class="${msg.id_emisor == currentUserId ? 'msg-me' : 'msg-peer'}">
                    ${msg.contenido}
                 </div>`;
    });

    document.querySelector('.chat-box').innerHTML = html;
}
</script>

   <script>
    document.addEventListener('DOMContentLoaded', () => {
    const partido = JSON.parse(localStorage.getItem("partidoSeleccionado"));
    if (partido) {
        document.getElementById("selectedMatch").textContent = partido.name;
        document.getElementById("homeTeam").textContent = partido.home;
        document.getElementById("awayTeam").textContent = partido.away;
        document.getElementById("selectedMatchId").value = partido.id;
        document.getElementById("predictionForm").style.display = 'block';
    }});

    const partidoCard = document.getElementById("partidoEnCurso");
    let partidos = [];   // Aquí se guardarán todos los partidos
    let indexPartido = 0; // Para saber en cuál estamos

    async function loadLiveMatch() {
        try {
            const res = await fetch("https://www.thesportsdb.com/api/v1/json/3/eventspastleague.php?id=4328");
            const data = await res.json();

            partidos = data.events; // Guardamos todos
            mostrarPartido();
        } catch (err) {
            console.error(err);
            partidoCard.innerHTML = `<em>Error al cargar partido.</em>`;
        }
    }

    function mostrarPartido() {
        if (!partidos || partidos.length === 0) {
            partidoCard.innerHTML = `<em>No hay partidos disponibles.</em>`;
            return;
        }

        const partido = partidos[indexPartido];

        partidoCard.innerHTML = `
            <div class="alert alert-danger p-2 mt-2 shadow-sm text-center">
                <strong>${partido.strHomeTeam} vs ${partido.strAwayTeam}</strong><br>
                ${partido.intHomeScore} - ${partido.intAwayScore} <br>
                <small>${partido.dateEvent} • ${partido.strTime}</small><br>

                <div class="mt-2 d-flex gap-2 justify-content-center">

                    <button class="btn btn-sm btn-warning" id="btnPredecir">Predecir</button>
                    <button class="btn btn-sm btn-outline-secondary" id="btnSiguiente">Siguiente partido ⏭️</button>
                </div>
            </div>
        `;

        document.getElementById("btnSiguiente").addEventListener("click", () => {
            indexPartido = (indexPartido + 1) % partidos.length;
            mostrarPartido();
        });

        document.getElementById("btnPredecir").addEventListener("click", () => {
            const matchInfo = {
                id: partido.idEvent,
                name: `${partido.strHomeTeam} vs ${partido.strAwayTeam}`,
                home: partido.strHomeTeam,
                away: partido.strAwayTeam
            };
            localStorage.setItem("partidoSeleccionado", JSON.stringify(matchInfo));
            window.location.href = "predicciones.php"; 
        });
    }


    
    loadLiveMatch();
</script>
<script>
    // =========================================================
    // ⚠️ CONFIGURACIÓN NECESARIA ⚠️
    // Asegúrate de que estas variables se definan ANTES de este script
    // Deben contener la información del usuario logueado y el objetivo de la llamada.
    const currentUser = { id: 1, username: 'usuarioA' }; // <-- Reemplazar con tu lógica de sesión
    let targetUserId = 2; // <-- Reemplazar con el ID del amigo al que se está llamando
    // =========================================================

    const socket = io(); // Asume que el socket se conecta al mismo host/puerto

    // Variables globales de WebRTC
    let pc = null;
    let localStream = null;
    let remoteStream = null;
    const pendingIceCandidates = [];

    // Configuración de Servidores STUN (NECESARIA para conectividad)
    const rtcConfig = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            // Puedes añadir más servidores STUN o un servidor TURN si lo tienes
        ]
    };

    // Referencias a elementos del DOM
    const btnVideollamada = document.getElementById("btnVideollamada");
    const videoPopup = document.getElementById("videoPopup");
    const closePopup = document.getElementById("closePopup");
    const myVideo = document.getElementById("myVideo");
    const friendVideo = document.getElementById("friendVideo");

    document.addEventListener("DOMContentLoaded", () => {
        if (!btnVideollamada || !videoPopup || !closePopup || !myVideo || !friendVideo) {
            console.error("⚠️ Algunos elementos del DOM no se encuentran.");
            return;
        }

        // 1. Registrar usuario al conectar
        socket.on('connect', () => {
            console.log("🟢 Conectado al servidor de Socket.IO:", socket.id);
            // Esto le dice al servidor qué ID de usuario está usando este socket
            socket.emit("registrarUsuario", currentUser.id);
        });

        // 2. Manejar clic de inicio de llamada (el OFERENTE)
        btnVideollamada.addEventListener("click", () => {
            if (targetUserId) {
                startCall(targetUserId);
            } else {
                alert('Selecciona un amigo para llamar.');
            }
        });

        // 3. Manejar cierre de POPUP
        closePopup.addEventListener("click", () => {
            endCall(true); // Notificar al otro peer que la llamada terminó
        });

        // 4. Manejar eventos de señalización del servidor
        setupSocketListeners();
    });

    // ====================================================================
    // ⚙️ LÓGICA DE WEBRTC
    // ====================================================================

    // Función principal para iniciar la llamada (el que ofrece/llama)
    async function startCall(targetId) {
        console.log('📞 Iniciando llamada a usuario:', targetId);
        videoPopup.style.display = "flex"; // Abrir popup

        try {
            await preparePeer(targetId);
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            
            // 🔥 ADAPTADO A TUS EVENTOS DE SERVER.JS 🔥
            // Tu servidor espera un evento llamado 'offer'
            socket.emit('offer', {
                to: targetId,
                from: currentUser.id,
                sdp: offer
            });
            console.log('📤 Enviando oferta WebRTC al servidor.');

        } catch (e) {
            console.error('Error al iniciar llamada:', e);
            alert('Error al iniciar llamada: ' + (e?.message || e));
            endCall(false);
        }
    }

    // Configura la conexión Peer
    async function preparePeer(targetId) {
        if (pc) return; // Ya está configurado

        // Crea la conexión Peer con la configuración STUN
        pc = new RTCPeerConnection(rtcConfig);

        // 👂 Manejo de ICE Candidates locales
        pc.onicecandidate = (event) => {
            if (event.candidate) {
                // 🔥 ADAPTADO A TUS EVENTOS DE SERVER.JS 🔥
                // Tu servidor espera un evento llamado 'ice-candidate'
                socket.emit('ice-candidate', {
                    to: targetId,
                    from: currentUser.id,
                    candidate: event.candidate
                });
            }
        };

        // 👂 Manejo del stream remoto que llega
        pc.ontrack = (event) => {
            console.log('✅ Stream remoto recibido.');
            if (friendVideo) friendVideo.srcObject = event.streams[0];
        };

        // Obtener stream de la cámara (Tu código original + verificación de seguridad)
        try {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            if (myVideo) myVideo.srcObject = localStream;
            
            // Añadir los tracks locales a la conexión Peer
            localStream.getTracks().forEach((track) => pc.addTrack(track, localStream));
        } catch (e) {
            console.error('❌ Error accediendo a la cámara:', e);
            throw new Error('Cámara/micrófono no disponibles. ¿Estás en HTTPS/localhost?');
        }
    }

    // Procesa los ICE Candidates en cola cuando llega la Remote Description
    async function flushPendingIceCandidates() {
        if (!pc || !pc.remoteDescription || !pendingIceCandidates.length) return;
        console.log(`🚿 Aplicando ${pendingIceCandidates.length} ICE candidates en cola...`);
        
        const queued = pendingIceCandidates.splice(0, pendingIceCandidates.length); // Vaciar la cola
        for (const c of queued) {
            try {
                await pc.addIceCandidate(new RTCIceCandidate(c));
            } catch (e) {
                console.error('Error aplicando ICE candidate:', e);
            }
        }
    }


    // Finaliza la llamada y limpia recursos
    function endCall(notifyPeer = false) {
        if (notifyPeer) {
            // Se puede agregar un evento 'end-call' a tu servidor si es necesario
            console.log('👋 Notificando el fin de la llamada.');
        }

        try { localStream && localStream.getTracks().forEach(t => t.stop()); } catch {}
        try { pc && pc.close(); } catch {}

        pc = null;
        localStream = null;
        remoteStream = null;
        pendingIceCandidates.length = 0;

        if (myVideo) myVideo.srcObject = null;
        if (friendVideo) friendVideo.srcObject = null;
        videoPopup.style.display = "none";
        console.log('📞 Llamada finalizada y recursos limpiados.');
    }

    // ====================================================================
    // 📩 MANEJO DE EVENTOS DE SOCKET.IO
    // ====================================================================
    function setupSocketListeners() {
        // Escucha la OFERTA (el que RECIBE la llamada)
        // 🔥 ADAPTADO A TUS EVENTOS DE SERVER.JS 🔥
        socket.on('offer', async ({ from, sdp }) => {
            // Asegúrate de que el ID sea el tuyo y el de destino sea el correcto
            if (from === targetUserId) { // Simplificado: asume que es el único con quien hablamos
                console.log(`📥 Oferta recibida de ${from}. Preparando respuesta...`);
                videoPopup.style.display = "flex"; // Abrir popup al recibir llamada
                
                try {
                    await preparePeer(from);
                    await pc.setRemoteDescription(new RTCSessionDescription(sdp));
                    
                    const answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);

                    // 🔥 ADAPTADO A TUS EVENTOS DE SERVER.JS 🔥
                    // Tu servidor espera un evento llamado 'answer'
                    socket.emit('answer', {
                        to: from,
                        from: currentUser.id,
                        sdp: answer
                    });
                    console.log('📤 Enviando respuesta WebRTC.');

                    // Procesar los candidatos que se hayan acumulado mientras esperábamos la oferta
                    flushPendingIceCandidates(); 

                } catch (e) {
                    console.error('Error al recibir oferta:', e);
                    endCall(false);
                }
            }
        });

        // Escucha la RESPUESTA (el que OFRECIÓ la llamada)
        // 🔥 ADAPTADO A TUS EVENTOS DE SERVER.JS 🔥
        socket.on('answer', async ({ from, sdp }) => {
             if (from === targetUserId && pc) {
                console.log(`📥 Respuesta recibida de ${from}. Estableciendo conexión...`);
                try {
                    await pc.setRemoteDescription(new RTCSessionDescription(sdp));
                    // Ya tenemos la descripción remota, ahora procesamos los candidatos en cola
                    flushPendingIceCandidates(); 
                    console.log('🎉 Conexión P2P casi lista.');
                } catch(e) {
                    console.error('Error al recibir respuesta:', e);
                }
            }
        });

        // Escucha los ICE CANDIDATES (ambos peers)
        // 🔥 ADAPTADO A TUS EVENTOS DE SERVER.JS 🔥
        socket.on('ice-candidate', async ({ from, candidate }) => {
            if (from === targetUserId) {
                console.log(`📥 ICE Candidate recibido de ${from}.`);
                // Usamos la función de cola para aplicar el candidato solo cuando haya RemoteDescription
                addIceCandidateOrQueue(candidate);
            }
        });

        // Función auxiliar para aplicar o encolar ICE
        async function addIceCandidateOrQueue(candidate) {
            // Si la conexión existe Y tiene una descripción remota ya establecida
            if (pc && pc.remoteDescription && pc.remoteDescription.type) {
                try {
                    await pc.addIceCandidate(new RTCIceCandidate(candidate));
                } catch (e) {
                    // Esto puede pasar si el candidato ya no es válido, generalmente se ignora
                    console.warn('Error aplicando ICE candidate:', e.name);
                }
            } else {
                // Si la conexión aún no ha recibido la Offer o Answer, guardamos el candidato
                pendingIceCandidates.push(candidate);
            }
        }
    }
</script>

</body>

</html>