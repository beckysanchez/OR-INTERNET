const mysql = require('mysql2');

const db = mysql.createConnection({
  host: 'tu-host-de-railway.proxy.rlwy.net',
  user: 'root',
  password: 'auVsnsFRweRTUBAdcoikMbcZbySCvAUs',
  database: 'railway',
  port: 11186
});

db.connect((err) => {
  if (err) {
    console.error('❌ Error al conectar a MySQL:', err);
  } else {
    console.log('✅ Conectado a MySQL en Railway');
  }
});

module.exports = db;
