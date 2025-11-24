// server.js
const express = require('express');
const path = require('path');
const cors = require('cors');
const db = require('./db');
const multer = require('multer');
const http = require('http');
const { Server } = require('socket.io');

const app = express();

// =====================================
// CORS (simple y abierto para desarrollo)
// =====================================
app.use(cors({
  origin: '*',
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
}));

app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  if (req.method === 'OPTIONS') return res.sendStatus(200);
  next();
});

// ------------------ MIDDLEWARES ------------------
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

// Si algún día quieres subir archivos desde Node, esto te sirve.
// Por ahora solo lo usamos en /registro (aunque la BD ya no tiene img_p).
const storage = multer.memoryStorage();
const upload = multer({ storage });

// ------------------ RUTAS HTTP ------------------

// Página de registro (si tienes un front en /public/registro.html)
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'registro.html'));
});

// ==================== AUTH ====================

// Registro de usuario (usa tabla USUARIO sin img_p)
app.post('/registro', upload.single('img_p'), (req, res) => {
  const nombre = req.body.NOMBRE;
  const correo = req.body.CORREO?.trim().toLowerCase();
  const Username = req.body.Username?.trim().toLowerCase();
  const contraseña = req.body.CONTRA;

  if (!nombre || !correo || !contraseña || !Username) {
    return res.status(400).json({ msg: 'Faltan datos' });
  }

  const checkSql = 'SELECT * FROM USUARIO WHERE CORREO = ? OR Username = ?';
  db.query(checkSql, [correo, Username], (err, result) => {
    if (err) {
      console.error('❌ Error al verificar duplicados:', err);
      return res.status(500).json({ msg: 'Error en el servidor' });
    }

    if (result.length > 0) {
      const existente = result[0];
      if (existente.CORREO === correo && existente.Username === Username)
        return res.status(409).json({ msg: 'Correo y usuario ya registrados' });
      else if (existente.CORREO === correo)
        return res.status(409).json({ msg: 'Correo ya registrado' });
      else if (existente.Username === Username)
        return res.status(409).json({ msg: 'Usuario ya registrado' });
    }

    const insertSql = `
      INSERT INTO USUARIO (NOMBRE, CORREO, CONTRA, Username, puntos)
      VALUES (?, ?, ?, ?, 10)
    `;
    db.query(insertSql, [nombre, correo, contraseña, Username], (err2, result2) => {
      if (err2) {
        if (err2.code === 'ER_DUP_ENTRY')
          return res.status(409).json({ msg: 'Correo o usuario ya existen' });
        console.error('❌ Error al insertar:', err2);
        return res.status(500).json({ msg: 'Error en el servidor' });
      }

      console.log('✅ Usuario registrado con ID:', result2.insertId);
      res.json({ msg: 'Usuario registrado exitosamente' });
    });
  });
});

// Login
app.post('/login', (req, res) => {
  const correo = req.body.CORREO;
  const contraseña = req.body.CONTRA;

  if (!correo || !contraseña)
    return res.status(400).json({ msg: 'Faltan datos' });

  const sql = 'SELECT * FROM USUARIO WHERE CORREO = ? AND CONTRA = ?';
  db.query(sql, [correo, contraseña], (err, result) => {
    if (err) {
      console.error('❌ Error al consultar:', err);
      return res.status(500).json({ msg: 'Error en el servidor' });
    }

    if (result.length === 0)
      return res.status(401).json({ msg: 'Correo o contraseña incorrectos' });

    const u = result[0];
    res.json({
      msg: 'Login exitoso',
      user: {
        ID_USUARIO: u.ID_USUARIO,
        NOMBRE: u.NOMBRE,
        CORREO: u.CORREO,
        Username: u.Username,
        puntos: u.puntos,
      },
    });
  });
});

// ==================== USUARIOS / AMIGOS ====================

// Buscar usuarios para agregar amigos
app.get('/usuarios', (req, res) => {
  const q = req.query.q || '';
  const sql = 'SELECT ID_USUARIO, Username, puntos FROM USUARIO WHERE Username LIKE ?';

  db.query(sql, [`%${q}%`], (err, result) => {
    if (err) {
      console.error('❌ Error al consultar usuarios:', err);
      return res.status(500).json({ msg: 'Error al obtener usuarios' });
    }
    res.json(result);
  });
});

// Agregar amigo (usa tabla AMIGOS)
app.post('/agregar-amigo', (req, res) => {
  const { usuario_id, amigo_id } = req.body;

  if (!usuario_id || !amigo_id) {
    return res.status(400).json({ msg: 'Faltan datos' });
  }

  const sql = `
    INSERT INTO AMIGOS (ID_USUARIO1, ID_USUARIO2, FECHA_AMISTAD)
    VALUES (?, ?, NOW())
  `;

  db.query(sql, [usuario_id, amigo_id], (err) => {
    if (err) {
      if (err.code === 'ER_DUP_ENTRY') {
        return res.status(409).json({ msg: 'Ya son amigos' });
      }
      console.error('❌ Error al agregar amigo:', err);
      return res.status(500).json({ msg: 'Error en el servidor' });
    }

    console.log(`✅ Nueva amistad: ${usuario_id} ↔ ${amigo_id}`);
    res.json({ msg: 'Amigo agregado correctamente' });
  });
});

// Lista de amigos
app.get('/amigos/:userId', (req, res) => {
  const { userId } = req.params;
  const sql = `
    SELECT u.ID_USUARIO, u.Username, u.puntos
    FROM AMIGOS a
    JOIN USUARIO u ON u.ID_USUARIO = a.ID_USUARIO2
    WHERE a.ID_USUARIO1 = ?
  `;

  db.query(sql, [userId], (err, result) => {
    if (err) {
      console.error('❌ Error al obtener amigos:', err);
      return res.status(500).json({ msg: 'Error al obtener amigos' });
    }
    res.json(result);
  });
});

// ==================== GRUPOS ====================

// Crear grupo
app.post('/crear-grupo', (req, res) => {
  const { nombre, creador_id, miembros } = req.body;

  if (!nombre || !creador_id || !Array.isArray(miembros)) {
    return res.status(400).json({ msg: 'Faltan datos para crear grupo' });
  }

  const insertGrupo = 'INSERT INTO GRUPO (NOMBRE) VALUES (?)';
  db.query(insertGrupo, [nombre], (err, result) => {
    if (err) {
      if (err.code === 'ER_DUP_ENTRY') {
        return res.status(409).json({ msg: 'Ya existe un grupo con ese nombre' });
      }
      console.error('❌ Error al crear grupo:', err);
      return res.status(500).json({ msg: 'Error al crear grupo' });
    }

    const grupoId = result.insertId;
    const todosMiembros = [...miembros, creador_id];
    const values = todosMiembros.map(id => [grupoId, id]);

    const insertMiembros = 'INSERT INTO GRUPO_MIEMBROS (ID_GRUPO, ID_USUARIO) VALUES ?';
    db.query(insertMiembros, [values], (err2) => {
      if (err2) {
        console.error('❌ Error al agregar miembros:', err2);
        return res.status(500).json({ msg: 'Error al agregar miembros' });
      }

      console.log(`✅ Grupo "${nombre}" creado con ID ${grupoId}`);
      res.json({ msg: 'Grupo creado correctamente', grupoId });
    });
  });
});

// Grupos de un usuario
app.get('/grupos/:userId', (req, res) => {
  const { userId } = req.params;
  const sql = `
    SELECT g.ID_GRUPO, g.NOMBRE, g.FECHA_CREACION
    FROM GRUPO g
    JOIN GRUPO_MIEMBROS gm ON g.ID_GRUPO = gm.ID_GRUPO
    WHERE gm.ID_USUARIO = ?
  `;
  db.query(sql, [userId], (err, result) => {
    if (err) {
      console.error('❌ Error al obtener grupos:', err);
      return res.status(500).json({ msg: 'Error al obtener grupos' });
    }
    res.json(result);
  });
});

// Mensajes de grupo (por ahora solo texto, pero tabla ya admite archivos)
app.post('/grupo/:grupoId/mensaje', (req, res) => {
  const { grupoId } = req.params;
  const { emisorId, mensaje, tipo, archivo_url, archivo_mime, archivo_nombre } = req.body;

  if (!grupoId || !emisorId || (!mensaje && !archivo_url)) {
    return res.status(400).json({ msg: 'Faltan datos para enviar mensaje' });
  }

  const sql = `
    INSERT INTO MENSAJE_GRUPO 
    (ID_GRUPO, ID_EMISOR, MENSAJE, TIPO, ARCHIVO_URL, ARCHIVO_MIME, ARCHIVO_NOMBRE_ORIGINAL)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  `;
  db.query(sql, [
    grupoId,
    emisorId,
    mensaje || null,
    tipo || 'texto',
    archivo_url || null,
    archivo_mime || null,
    archivo_nombre || null
  ], (err, result) => {
    if (err) {
      console.error('❌ Error al guardar mensaje de grupo:', err);
      return res.status(500).json({ msg: 'Error al guardar mensaje' });
    }
    res.json({ msg: 'Mensaje enviado', idMensaje: result.insertId });
  });
});

// Obtener mensajes de grupo
app.get('/grupo/:grupoId/mensajes', (req, res) => {
  const { grupoId } = req.params;

  const sql = `
    SELECT 
      m.ID_MENSAJE, 
      m.MENSAJE, 
      m.TIPO,
      m.ARCHIVO_URL,
      m.ARCHIVO_MIME,
      m.ARCHIVO_NOMBRE_ORIGINAL,
      m.FECHA_ENVIO, 
      u.Username AS autor
    FROM MENSAJE_GRUPO m
    JOIN USUARIO u ON m.ID_EMISOR = u.ID_USUARIO
    WHERE m.ID_GRUPO = ?
    ORDER BY m.FECHA_ENVIO ASC
  `;
  db.query(sql, [grupoId], (err, result) => {
    if (err) {
      console.error('❌ Error al obtener mensajes de grupo:', err);
      return res.status(500).json({ msg: 'Error al obtener mensajes' });
    }
    res.json(result);
  });
});

// ==================== SOCKET.IO ====================

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST'],
  },
});

const usuariosConectados = {}; // { userId: socketId }

io.on('connection', (socket) => {
  console.log('🟢 Usuario conectado:', socket.id);

  // ===========================================
  // 1) REGISTRO DE USUARIO
  // ===========================================
  socket.on('registrarUsuario', (userId) => {
    usuariosConectados[userId] = socket.id;
    console.log(`🆕 Usuario ${userId} registrado con socket ${socket.id}`);
  });


  // ===========================================
  // 2) WEBRTC SEÑALIZACIÓN
  // ===========================================
  socket.on('offer', (data) => {
    const destino = usuariosConectados[data.to];
    if (destino) io.to(destino).emit('offer', data);
  });

  socket.on('answer', (data) => {
    const destino = usuariosConectados[data.to];
    if (destino) io.to(destino).emit('answer', data);
  });

  socket.on('ice-candidate', (data) => {
    const destino = usuariosConectados[data.to];
    if (destino) io.to(destino).emit('ice-candidate', data);
  });


  // ===========================================
  // 3) CHAT PRIVADO
  // ===========================================
  socket.on('mensajePrivado', ({ 
    de, para, texto, archivo_url, archivo_mime, archivo_nombre, tipo 
  }) => {

    console.log('➡️ mensajePrivado recibido:', {
      de, para, texto, archivo_url, archivo_mime, archivo_nombre, tipo
    });

    // --- Buscar o crear conversación ---
    const sqlConv = `
      SELECT ID_CONVERSACION FROM CONVERSACION 
      WHERE (ID_USUARIO1=? AND ID_USUARIO2=?)
         OR (ID_USUARIO1=? AND ID_USUARIO2=?)
    `;

    db.query(sqlConv, [de, para, para, de], (err, result) => {
      if (err) return console.error('❌ Error consultando conversación:', err);

      if (result.length > 0) {
        guardarMensaje(result[0].ID_CONVERSACION);
      } else {
        const sqlNueva = `
          INSERT INTO CONVERSACION (ID_USUARIO1, ID_USUARIO2)
          VALUES (?, ?)
        `;
        db.query(sqlNueva, [de, para], (err2, result2) => {
          if (err2) return console.error('❌ Error creando conversación:', err2);
          guardarMensaje(result2.insertId);
        });
      }
    });

    function guardarMensaje(ID_CONVERSACION) {
      const sqlMensaje = `
        INSERT INTO MENSAJE 
        (ID_CONVERSACION, ID_EMISOR, MENSAJE, TIPO, ARCHIVO_URL, ARCHIVO_MIME, ARCHIVO_NOMBRE_ORIGINAL)
        VALUES (?, ?, ?, ?, ?, ?, ?)
      `;

      db.query(sqlMensaje, [
        ID_CONVERSACION,
        de,
        texto || null,
        tipo || 'texto',
        archivo_url || null,
        archivo_mime || null,
        archivo_nombre || null
      ], (err3) => {
        if (err3) return console.error('❌ Error guardando mensaje:', err3);

        const destino = usuariosConectados[para];
        if (destino) {
          io.to(destino).emit('recibirMensaje', {
            de, texto, archivo_url, archivo_mime, archivo_nombre, tipo
          });
        }
      });
    }
  });


  // ===========================================
  // 4) CHAT GRUPAL
  // ===========================================

  // --- Unirse a un grupo ---
  socket.on('joinGrupo', ({ ID_GRUPO }) => {
    if (!ID_GRUPO) return;

    const nuevaSala = `grupo_${ID_GRUPO}`;

    // abandonar sala anterior si existía
    if (socket.data.currentGroupId) {
      socket.leave(`grupo_${socket.data.currentGroupId}`);
    }

    socket.join(nuevaSala);
    socket.data.currentGroupId = ID_GRUPO;

    console.log(`👥 Socket ${socket.id} unido a ${nuevaSala}`);
  });


  // --- Nuevo mensaje de grupo ---
  socket.on('mensajeGrupoNuevo', (msg) => {
    const groupId = msg.ID_GRUPO;
    if (!groupId) return;

    const sala = `grupo_${groupId}`;
    console.log(`💬 Mensaje NUEVO en ${sala}:`, msg);

    // enviar a todos MENOS al que lo envió
    socket.to(sala).emit('recibirMensajeGrupo', msg);
  });


  // ===========================================
  // 5) DESCONECTAR USUARIO
  // ===========================================
  socket.on('disconnect', () => {
    for (const [id, sid] of Object.entries(usuariosConectados)) {
      if (sid === socket.id) {
        delete usuariosConectados[id];
        console.log(`🚫 Usuario ${id} desconectado`);
      }
    }
  });
});


// ===========================================
// 6) ARRANCAR SERVIDOR
// ===========================================
const PORT = process.env.PORT || 3000;
server.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Servidor con Socket.IO en puerto ${PORT}`);
});
