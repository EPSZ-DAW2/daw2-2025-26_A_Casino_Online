-- ==========================================
-- BASE DE DATOS COMPLETA: CASINO ONLINE
-- GRUPOS G1 A G6 - YII2 FRAMEWORK
-- ==========================================
DROP DATABASE IF EXISTS casino_db; -- Borra la anterior si existe (Cuidado, borra datos)
CREATE DATABASE casino_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE casino_db;

-- 1. MÓDULO DE USUARIOS (G1)
-- Incluye adaptaciones para Yii2 (auth_key, tokens) y Requisitos de Negocio (VIP, KYC)
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Autenticación y Seguridad Yii2
    nick VARCHAR(50) NOT NULL UNIQUE COMMENT 'Requisito: Nick único',
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    auth_key VARCHAR(32) NOT NULL COMMENT 'Clave de autenticación para "Recordarme" en Yii2',
    password_reset_token VARCHAR(255) UNIQUE COMMENT 'Token para recuperar contraseña olvidada',
    access_token VARCHAR(255) NULL,
    
    -- Datos Personales y Rol
    rol VARCHAR(30) NOT NULL DEFAULT 'jugador' COMMENT 'Diferenciación de permisos',
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    telefono VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    avatar_url VARCHAR(255) DEFAULT 'default_avatar.png' COMMENT 'Requisito: Avatar personalizable',
    
    -- Estado, VIP y Verificación
    nivel_vip ENUM('Bronce', 'Plata', 'Oro') DEFAULT 'Bronce' COMMENT 'Requisito: Nivel VIP',
    puntos_progreso INT DEFAULT 0 COMMENT 'Barra de progreso visual',
    estado_cuenta ENUM('Activo', 'Bloqueado') DEFAULT 'Activo',
    estado_verificacion ENUM('Pendiente', 'Verificado', 'Rechazado') DEFAULT 'Pendiente',
    foto_dni VARCHAR(255) NULL COMMENT 'Ruta archivo DNI para verificación',
    foto_selfie VARCHAR(255) NULL COMMENT 'Ruta archivo Selfie para verificación',
    notas_internas TEXT COMMENT 'Notas para el admin sobre el usuario',
    
    -- Sistema de Afiliados
    codigo_referido_propio VARCHAR(20) UNIQUE COMMENT 'Código para invitar a otros',
    id_padrino INT NULL COMMENT 'Usuario que invitó a este cliente',
    
    CONSTRAINT fk_usuario_padrino FOREIGN KEY (id_padrino) REFERENCES usuario(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. MÓDULO FINANCIERO (G2)
-- Monedero y Transacciones
CREATE TABLE monedero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    saldo_real DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Dinero retirable',
    saldo_bono DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Dinero bloqueado/promocional',
    divisa VARCHAR(3) DEFAULT 'EUR',
    
    CONSTRAINT fk_monedero_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transaccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo_operacion ENUM('Deposito', 'Retirada', 'Apuesta', 'Premio', 'Comision_Afiliado') NOT NULL,
    categoria ENUM('Slots', 'Ruleta', 'Cartas', 'Banco') COMMENT 'Para gráficas de gasto',
    cantidad DECIMAL(10, 2) NOT NULL,
    metodo_pago VARCHAR(50) COMMENT 'Visa, Bizum, Crypto',
    referencia_externa VARCHAR(100) NULL COMMENT 'ID de transacción de la pasarela de pago',
    estado ENUM('Pendiente', 'Completado', 'Rechazado') DEFAULT 'Pendiente',
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_transaccion_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. MÓDULO DE JUEGOS (G3)
-- Catálogo y Logs de Partidas
CREATE TABLE juego (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    proveedor VARCHAR(50) NOT NULL COMMENT 'NetEnt, Playtech, etc.',
    tipo ENUM('Slot', 'Ruleta', 'Cartas') NOT NULL,
    tematica VARCHAR(50) COMMENT 'Egipto, Futuro, etc.',
    rtp DECIMAL(5, 2) NOT NULL COMMENT 'Porcentaje de retorno teórico',
    url_caratula VARCHAR(255) COMMENT 'Para el Grid visual',
    activo TINYINT(1) DEFAULT 1 COMMENT 'Interruptor Activo/Inactivo',
    es_nuevo TINYINT(1) DEFAULT 0 COMMENT 'Etiqueta Nuevo',
    en_mantenimiento TINYINT(1) DEFAULT 0,
    tasa_pago_actual DECIMAL(5, 2) DEFAULT 0 COMMENT 'Estadística Hot/Cold',
    estado_racha ENUM('Caliente', 'Fria', 'Neutro') DEFAULT 'Neutro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE historial_partida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_juego INT NOT NULL,
    cantidad_apostada DECIMAL(10, 2) NOT NULL,
    cantidad_ganada DECIMAL(10, 2) NOT NULL,
    detalle_tecnico JSON COMMENT 'Cartas exactas o número salido',
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_partida_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    CONSTRAINT fk_partida_juego FOREIGN KEY (id_juego) REFERENCES juego(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. MÓDULO DE TORNEOS (G4)
CREATE TABLE torneo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    id_juego_asociado INT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    coste_entrada DECIMAL(10, 2) DEFAULT 0,
    bolsa_premios DECIMAL(10, 2) DEFAULT 0 COMMENT 'Premios garantizados',
    estado ENUM('Abierto', 'En Curso', 'Finalizado', 'Cancelado') DEFAULT 'Abierto',
    
    CONSTRAINT fk_torneo_juego FOREIGN KEY (id_juego_asociado) REFERENCES juego(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE participacion_torneo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_torneo INT NOT NULL,
    id_usuario INT NOT NULL,
    puntuacion_actual INT DEFAULT 0 COMMENT 'Para Ranking en vivo',
    posicion_final INT NULL,
    premio_ganado DECIMAL(10, 2) DEFAULT 0,
    
    CONSTRAINT fk_part_torneo FOREIGN KEY (id_torneo) REFERENCES torneo(id) ON DELETE CASCADE,
    CONSTRAINT fk_part_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. MÓDULO DE SEGURIDAD (G5)
CREATE TABLE log_visita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    direccion_ip VARCHAR(45) NOT NULL,
    dispositivo VARCHAR(255),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_visita_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE alerta_fraude (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'Colusión, Bot, Chip Dumping',
    nivel_riesgo ENUM('Alto', 'Medio', 'Bajo') DEFAULT 'Medio',
    estado ENUM('Pendiente', 'Investigando', 'Resuelto') DEFAULT 'Pendiente',
    detalles_tecnicos TEXT,
    fecha_detectada DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_fraude_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. MÓDULO SOCIAL (G6)
-- Juegos
CREATE TABLE logro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    icono_trofeo VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE logro_usuario (
    id_usuario INT NOT NULL,
    id_logro INT NOT NULL,
    fecha_desbloqueo DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id_usuario, id_logro),
    CONSTRAINT fk_logro_usu FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    CONSTRAINT fk_logro_def FOREIGN KEY (id_logro) REFERENCES logro(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mesa_privada (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_anfitrion INT NOT NULL,
    tipo_juego VARCHAR(50),
    contrasena_acceso VARCHAR(255) COMMENT 'Para partidas cerradas',
    estado_mesa ENUM('Abierta', 'Jugando', 'Cerrada') DEFAULT 'Abierta',
    
    CONSTRAINT fk_mesa_anfitrion FOREIGN KEY (id_anfitrion) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mensaje_chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NULL COMMENT 'Si es NULL, es chat global o de soporte',
    id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    es_ofensivo TINYINT(1) DEFAULT 0 COMMENT 'Marcado por filtro de palabras',
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_chat_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_mesa FOREIGN KEY (id_mesa) REFERENCES mesa_privada(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;