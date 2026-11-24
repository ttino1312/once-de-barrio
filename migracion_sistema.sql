USE once_de_barrio_conectado;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  alias VARCHAR(30) NOT NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  rol ENUM('jugador','admin') NOT NULL DEFAULT 'jugador',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS juegos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL UNIQUE
);
INSERT IGNORE INTO juegos (id, nombre) VALUES (1, 'Once de Barrio'), (2, 'Juego de prueba (sin partidas)');

-- Las partidas viejas quedan sin usuario. No se borran ni aparecen en el ranking nuevo.
ALTER TABLE partidas
  ADD COLUMN usuario_id INT NULL,
  ADD COLUMN juego_id INT NULL,
  ADD CONSTRAINT fk_partida_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  ADD CONSTRAINT fk_partida_juego FOREIGN KEY (juego_id) REFERENCES juegos(id);

CREATE TABLE IF NOT EXISTS puntajes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partida_id INT NOT NULL UNIQUE,
  usuario_id INT NOT NULL,
  juego_id INT NOT NULL,
  puntos INT NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (partida_id) REFERENCES partidas(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (juego_id) REFERENCES juegos(id),
  INDEX idx_ranking (juego_id, usuario_id, puntos, creado_en)
);
