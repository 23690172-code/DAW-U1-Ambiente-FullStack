CREATE TABLE IF NOT EXISTS solicitudes_servicio (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  servicio_id INT UNSIGNED NOT NULL,
  administrador_id INT UNSIGNED NULL,
  precio_contratado DECIMAL(10,2) NOT NULL,
  fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_programada DATETIME NULL,
  fecha_inicio DATETIME NULL,
  fecha_fin DATETIME NULL,
  estado ENUM('SOLICITADA', 'AUTORIZADA', 'RECHAZADA', 'PROGRAMADA', 'EN_PROCESO', 'COMPLETADA', 'CANCELADA') NOT NULL DEFAULT 'SOLICITADA',
  observaciones_cliente TEXT NULL,
  observaciones_admin TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_solicitud_cliente FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_solicitud_servicio FOREIGN KEY (servicio_id) REFERENCES servicios(id),
  CONSTRAINT fk_solicitud_admin FOREIGN KEY (administrador_id) REFERENCES usuarios(id),
  INDEX idx_solicitud_cliente(usuario_id),
  INDEX idx_solicitud_estado(estado),
  INDEX idx_solicitud_fecha(fecha_programada)
);

CREATE TABLE IF NOT EXISTS historial_solicitud (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitud_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL,
  estado_anterior VARCHAR(30) NULL,
  estado_nuevo VARCHAR(30) NOT NULL,
  comentario TEXT NULL,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_historial_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes_servicio(id),
  CONSTRAINT fk_historial_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  INDEX idx_historial_solicitud(solicitud_id)
);