const mysql = require('mysql2');
const connection = mysql.createConnection({
  host: 'localhost',      
  user: 'root',           
  password: 'Monse171002.',
  database: 'pwci',
});

connection.connect((err) => {
  if (err) {
    console.error('❌ Error al conectar a MySQL:', err);
    return;
  }
  console.log('✅ Conexión exitosa a MySQL');
});

module.exports = connection;
