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

const storage = multer.memoryStorage();
const upload = multer({ storage });

// ------------------ RUTAS HTTP ------------------

// Página de registro (si tienes un front en /public/registro.html)
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'registro.html'));
});

// Registro de usuario
app.post('/registro', upload.single('img_p'), (req, res) => {
  const nombre = req.body.NOMBRE;
  const correo = req.body.CORREO.trim().toLowerCase();
  const Username = req.body.Username.trim().toLowerCase();
  const contraseña = req.body.CONTRA;
  const imagen = req.file ? req.file.buffer.toString('base64') : null;

  if (!nombre || !correo || !contraseña || !Username)
    return res.status(400).json({ msg: 'Faltan datos' });

  const checkSql = 'SELECT * FROM usuario WHERE CORREO = ? OR Username = ?';
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
      INSERT INTO usuario (NOMBRE, CORREO, CONTRA, Username, puntos, img_p)
      VALUES (?, ?, ?, ?, 10, ?)
    `;
    db.query(insertSql, [nombre, correo, contraseña, Username, imagen], (err2, result2) => {
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

  const sql = 'SELECT * FROM usuario WHERE CORREO = ? AND CONTRA = ?';
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
        id: u.ID_USUARIO,
        NOMBRE: u.NOMBRE,
        CORREO: u.CORREO,
        Username: u.Username,
        puntos: u.puntos,
        img_p: u.img_p,
      },
    });
  });
});

// Buscar usuarios para agregar amigos
app.get('/usuarios', (req, res) => {
  const q = req.query.q || '';
  const sql = 'SELECT ID_USUARIO, Username, img_p, puntos FROM usuario WHERE Username LIKE ?';

  db.query(sql, [`%${q}%`], (err, result) => {
    if (err) {
      console.error('❌ Error al consultar usuarios:', err);
      return res.status(500).json({ msg: 'Error al obtener usuarios' });
    }
    res.json(result);
  });
});

// Agregar amigo
app.post('/agregar-amigo', (req, res) => {
  const { usuario_id, amigo_id } = req.body;

  if (!usuario_id || !amigo_id) {
    return res.status(400).json({ msg: 'Faltan datos' });
  }

  const sql = `
    INSERT INTO amistad (ID_USUARIO1, ID_USUARIO2, FECHA_AMISTAD)
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
    SELECT u.ID_USUARIO, u.Username, u.img_p, u.puntos
    FROM amigos a
    JOIN usuario u ON u.ID_USUARIO = a.ID_USUARIO2
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

  const insertGrupo = 'INSERT INTO grupo (NOMBRE) VALUES (?)';
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

    const insertMiembros = 'INSERT INTO grupo_miembros (ID_GRUPO, ID_USUARIO) VALUES ?';
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
    FROM grupo g
    JOIN grupo_miembros gm ON g.ID_GRURO = gm.ID_GRUPO
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

// Mensajes de grupo
app.post('/grupo/:grupoId/mensaje', (req, res) => {
  const { grupoId } = req.params;
  const { emisorId, mensaje } = req.body;

  if (!grupoId || !emisorId || !mensaje) {
    return res.status(400).json({ msg: 'Faltan datos para enviar mensaje' });
  }

  const sql = `
    INSERT INTO mensaje_grupo (ID_GRUPO, ID_EMISOR, MENSAJE)
    VALUES (?, ?, ?)
  `;
  db.query(sql, [grupoId, emisorId, mensaje], (err, result) => {
    if (err) {
      console.error('❌ Error al guardar mensaje:', err);
      return res.status(500).json({ msg: 'Error al guardar mensaje' });
    }
    res.json({ msg: 'Mensaje enviado', idMensaje: result.insertId });
  });
});

// Obtener mensajes de grupo
app.get('/grupo/:grupoId/mensajes', (req, res) => {
  const { grupoId } = req.params;

  const sql = `
    SELECT m.ID_MENSAJE, m.MENSAJE, m.FECHA_ENVIO, u.Username AS autor
    FROM mensaje_grupo m
    JOIN usuario u ON m.ID_EMISOR = u.ID_USUARIO
    WHERE m.ID_GRUPO = ?
    ORDER BY m.FECHA_ENVIO ASC
  `;
  db.query(sql, [grupoId], (err, result) => {
    if (err) {
      console.error('❌ Error al obtener mensajes:', err);
      return res.status(500).json({ msg: 'Error al obtener mensajes' });
    }
    res.json(result);
  });
});

// ==================== SOCKET.IO ====================

// ⚠️ SOLO UNA VEZ:
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST'],
  },
});

const usuariosConectados = {};

io.on('connection', (socket) => {
  console.log('🟢 Usuario conectado:', socket.id);

  // Registrar usuario
  socket.on('registrarUsuario', (userId) => {
    usuariosConectados[userId] = socket.id;
    console.log(`🆕 Usuario ${userId} registrado con socket ${socket.id}`);
  });

  // Oferta WebRTC
  socket.on('offer', (data) => {
    // data: { to, from, sdp }
    const destino = usuariosConectados[data.to];
    if (destino) {
      io.to(destino).emit('offer', { from: data.from, sdp: data.sdp });
    }
  });

  // Respuesta WebRTC
  socket.on('answer', (data) => {
    const destino = usuariosConectados[data.to];
    if (destino) {
      io.to(destino).emit('answer', { from: data.from, sdp: data.sdp });
    }
  });

  // ICE Candidates
  socket.on('ice-candidate', (data) => {
    const destino = usuariosConectados[data.to];
    if (destino) {
      io.to(destino).emit('ice-candidate', { from: data.from, candidate: data.candidate });
    }
  });

  // Chat privado
  socket.on('mensajePrivado', ({ de, para, texto }) => {
    console.log(`📨 Mensaje de ${de} para ${para}: ${texto}`);

    const sqlConversacion = `
      SELECT ID_CONVERSACION FROM conversacion 
      WHERE (ID_USUARIO1=? AND ID_USUARIO2=?) 
         OR (ID_USUARIO1=? AND ID_USUARIO2=?)
    `;

    db.query(sqlConversacion, [de, para, para, de], (err, result) => {
      if (err) return console.error('❌ Error consultando conversación:', err);

      if (result.length > 0) {
        guardarMensaje(result[0].ID_CONVERSACION);
      } else {
        const sqlNueva = `
          INSERT INTO conversacion (ID_USUARIO1, ID_USUARIO2, FECHA_CREACION)
          VALUES (?, ?, NOW())
        `;
        db.query(sqlNueva, [de, para], (err2, result2) => {
          if (err2) return console.error('❌ Error creando conversación:', err2);
          guardarMensaje(result2.insertId);
        });
      }
    });

    function guardarMensaje(ID_CONVERSACION) {
      const sqlMensaje = `
        INSERT INTO mensaje (ID_CONVERSACION, ID_EMISOR, MENSAJE, FECHA_ENVIO)
        VALUES (?, ?, ?, NOW())
      `;
      db.query(sqlMensaje, [ID_CONVERSACION, de, texto], (err3) => {
        if (err3) return console.error('❌ Error guardando mensaje:', err3);

        const destino = usuariosConectados[para];
        if (destino) {
          io.to(destino).emit('recibirMensaje', { de, texto });
        }
      });
    }
  });

  socket.on('disconnect', () => {
    for (const [id, sid] of Object.entries(usuariosConectados)) {
      if (sid === socket.id) {
        delete usuariosConectados[id];
        console.log(`🚫 Usuario ${id} desconectado`);
      }
    }
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Servidor con Socket.IO corriendo en puerto ${PORT}`);
});
