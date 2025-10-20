const mysql = require('mysql2');

const connection = mysql.createConnection({
  host: 'yamabiko.proxy.rlwy.net',  // 👈 el host que te da Railway
  user: 'root',                      // 👈 el usuario
  password: 'TU_CONTRASEÑA_DE_RAILWAY', // 👈 la contraseña (quita los asteriscos)
  database: 'railway',               // 👈 el nombre de la base
  port: 11186                       // 👈 el puerto que te muestra Railway
});

connection.connect((err) => {
  if (err) {
    console.error('❌ Error al conectar a MySQL:', err);
    return;
  }
  console.log('✅ Conexión exitosa a MySQL en Railway');
});

module.exports = connection;

