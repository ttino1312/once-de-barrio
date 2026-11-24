CREATE DATABASE IF NOT EXISTS once_de_barrio_conectado CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE once_de_barrio_conectado;

CREATE TABLE selecciones (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(80) NOT NULL UNIQUE);
CREATE TABLE mundiales (id INT AUTO_INCREMENT PRIMARY KEY, anio INT NOT NULL UNIQUE);
CREATE TABLE planteles (
  id INT AUTO_INCREMENT PRIMARY KEY, seleccion_id INT NOT NULL, mundial_id INT NOT NULL,
  UNIQUE (seleccion_id, mundial_id),
  FOREIGN KEY (seleccion_id) REFERENCES selecciones(id), FOREIGN KEY (mundial_id) REFERENCES mundiales(id)
);
CREATE TABLE jugadores (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(60) NOT NULL, apellido VARCHAR(60) NOT NULL);
CREATE TABLE plantel_jugadores (
  id INT AUTO_INCREMENT PRIMARY KEY, plantel_id INT NOT NULL, jugador_id INT NOT NULL, posicion VARCHAR(10) NOT NULL, dorsal INT, valoracion INT NOT NULL,
  UNIQUE (plantel_id, jugador_id), FOREIGN KEY (plantel_id) REFERENCES planteles(id), FOREIGN KEY (jugador_id) REFERENCES jugadores(id)
);
CREATE TABLE partidas (id INT AUTO_INCREMENT PRIMARY KEY, nombre_equipo VARCHAR(80) NOT NULL, formacion VARCHAR(20) NOT NULL, fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE equipo_partida (
  id INT AUTO_INCREMENT PRIMARY KEY, partida_id INT NOT NULL, plantel_jugador_id INT NOT NULL, slot VARCHAR(10) NOT NULL,
  UNIQUE (partida_id, slot), UNIQUE (partida_id, plantel_jugador_id),
  FOREIGN KEY (partida_id) REFERENCES partidas(id), FOREIGN KEY (plantel_jugador_id) REFERENCES plantel_jugadores(id)
);
CREATE TABLE resultados (id INT AUTO_INCREMENT PRIMARY KEY, partida_id INT NOT NULL, ronda VARCHAR(30) NOT NULL, rival VARCHAR(80) NOT NULL, goles_favor INT NOT NULL, goles_contra INT NOT NULL, FOREIGN KEY (partida_id) REFERENCES partidas(id));

INSERT INTO selecciones (id, nombre) VALUES (1,'Argentina'),(2,'Brasil'),(3,'España'),(4,'Francia');
INSERT INTO mundiales (id, anio) VALUES (1,2022),(2,2002),(3,2010),(4,2018);
INSERT INTO planteles (id, seleccion_id, mundial_id) VALUES (1,1,1),(2,2,2),(3,3,3),(4,4,4);
INSERT INTO jugadores (id,nombre,apellido) VALUES
(1,'Emiliano','Martínez'),(2,'Nahuel','Molina'),(3,'Cristian','Romero'),(4,'Nicolás','Otamendi'),(5,'Marcos','Acuña'),(6,'Rodrigo','De Paul'),(7,'Enzo','Fernández'),(8,'Alexis','Mac Allister'),(9,'Lionel','Messi'),(10,'Julián','Álvarez'),(11,'Ángel','Di María'),
(12,'Marcos','Silva'),(13,'Cafu',''),(14,'Lúcio',''),(15,'Roque','Júnior'),(16,'Roberto','Carlos'),(17,'Gilberto','Silva'),(18,'Kléberson',''),(19,'Ronaldinho','Gaúcho'),(20,'Rivaldo',''),(21,'Ronaldo','Nazário'),(22,'Denílson',''),
(23,'Iker','Casillas'),(24,'Sergio','Ramos'),(25,'Gerard','Piqué'),(26,'Carles','Puyol'),(27,'Joan','Capdevila'),(28,'Sergio','Busquets'),(29,'Xabi','Alonso'),(30,'Xavi','Hernández'),(31,'Andrés','Iniesta'),(32,'David','Villa'),(33,'Pedro','Rodríguez'),
(34,'Hugo','Lloris'),(35,'Benjamin','Pavard'),(36,'Raphaël','Varane'),(37,'Samuel','Umtiti'),(38,'Lucas','Hernández'),(39,'N''Golo','Kanté'),(40,'Paul','Pogba'),(41,'Antoine','Griezmann'),(42,'Kylian','Mbappé'),(43,'Olivier','Giroud'),(44,'Ousmane','Dembélé');
INSERT INTO plantel_jugadores (plantel_id,jugador_id,posicion,dorsal,valoracion) VALUES
(1,1,'ARQ',23,91),(1,2,'DEF',26,82),(1,3,'DEF',13,87),(1,4,'DEF',19,88),(1,5,'DEF',8,83),(1,6,'MED',7,86),(1,7,'MED',24,89),(1,8,'MED',20,87),(1,9,'DEL',10,97),(1,10,'DEL',9,88),(1,11,'DEL',11,87),
(2,12,'ARQ',1,84),(2,13,'DEF',2,89),(2,14,'DEF',3,90),(2,15,'DEF',4,84),(2,16,'DEF',6,93),(2,17,'MED',8,87),(2,18,'MED',16,82),(2,19,'MED',11,95),(2,20,'DEL',10,93),(2,21,'DEL',9,96),(2,22,'DEL',17,84),
(3,23,'ARQ',1,91),(3,24,'DEF',15,91),(3,25,'DEF',3,88),(3,26,'DEF',5,92),(3,27,'DEF',11,84),(3,28,'MED',16,89),(3,29,'MED',14,88),(3,30,'MED',8,94),(3,31,'DEL',6,93),(3,32,'DEL',7,90),(3,33,'DEL',18,85),
(4,34,'ARQ',1,88),(4,35,'DEF',2,85),(4,36,'DEF',4,89),(4,37,'DEF',5,85),(4,38,'DEF',21,84),(4,39,'MED',13,91),(4,40,'MED',6,88),(4,41,'MED',7,90),(4,42,'DEL',10,96),(4,43,'DEL',9,86),(4,44,'DEL',11,87);
