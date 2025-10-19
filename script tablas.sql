CREATE DATABASE pwci;
USE pwci;

CREATE TABLE USUARIO(
	ID_USUARIO INT PRIMARY KEY auto_increment NOT NULL,
	NOMBRE VARCHAR(50),
	CORREO VARCHAR(50),
	CONTRA VARCHAR(50),
	Username varchar(50) not null,
    puntos int,
    img_p longtext
);
select * from USUARIO
