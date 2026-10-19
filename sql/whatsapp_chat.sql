-- =====================================================================
-- Chat WhatsApp (Yupana en la nube <-> API WhatsApp local en Node)
-- Ejecutar sobre yupana_db (MariaDB 10.4+ / MySQL 5.7+)
--
-- Flujo:
--   * Entrantes: Node hace POST al webhook de Yupana -> wa_mensajes
--   * Salientes desde la web: se insertan en wa_mensajes con
--     estado_envio='pendiente'; Node los consulta cada 2-3 s, los envia
--     y confirma (wa_message_id / error).
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Instancias (sesiones) de la API WhatsApp local
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wa_instancias` (
  `id`                 VARCHAR(50)  NOT NULL COMMENT 'Mismo id que en la API Node (instancia fija: yupana)',
  `label`              VARCHAR(100) NOT NULL,
  `numero`             VARCHAR(20)  DEFAULT NULL COMMENT 'Numero conectado a la sesion',
  `estado`             VARCHAR(20)  NOT NULL DEFAULT 'desconocido' COMMENT 'ready, qr, loading, disconnected, auth_failure...',
  `ultimo_contacto_at` DATETIME     DEFAULT NULL COMMENT 'Ultimo polling/webhook recibido desde Node',
  `activo`             TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`         DATETIME     DEFAULT NULL,
  `updated_at`         DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Conversaciones (una por instancia + contacto/grupo)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wa_chats` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `instancia_id`      VARCHAR(50)  NOT NULL,
  `wa_chat_id`        VARCHAR(100) NOT NULL COMMENT '51999999999@c.us o xxxx@g.us',
  `numero`            VARCHAR(20)  DEFAULT NULL COMMENT 'Solo digitos, con codigo de pais',
  `nombre`            VARCHAR(150) DEFAULT NULL COMMENT 'Pushname del contacto o nombre del grupo',
  `es_grupo`          TINYINT(1)   NOT NULL DEFAULT 0,
  `cliente_id`        INT UNSIGNED DEFAULT NULL COMMENT 'clientes.id si el numero coincide',
  `no_leidos`         INT UNSIGNED NOT NULL DEFAULT 0,
  `ultimo_mensaje`    VARCHAR(255) DEFAULT NULL COMMENT 'Vista previa para la lista de chats',
  `ultimo_mensaje_at` DATETIME     DEFAULT NULL,
  `archivado`         TINYINT(1)   NOT NULL DEFAULT 0,
  `agente_id`         INT UNSIGNED DEFAULT NULL COMMENT 'usuarios.id que atiende el chat; NULL = sin asignar',
  `asignado_at`       DATETIME     DEFAULT NULL,
  `estado_chat`       ENUM('abierto','cerrado') NOT NULL DEFAULT 'abierto',
  `created_at`        DATETIME     DEFAULT NULL,
  `updated_at`        DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wa_chats_instancia_chat` (`instancia_id`, `wa_chat_id`),
  KEY `idx_wa_chats_cliente` (`cliente_id`),
  KEY `idx_wa_chats_ultimo` (`instancia_id`, `archivado`, `ultimo_mensaje_at`),
  KEY `idx_wa_chats_agente` (`agente_id`, `estado_chat`, `ultimo_mensaje_at`),
  CONSTRAINT `fk_wa_chats_instancia` FOREIGN KEY (`instancia_id`)
    REFERENCES `wa_instancias` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Mensajes (tambien es la bandeja de salida / outbox)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wa_mensajes` (
  `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `chat_id`        INT UNSIGNED     NOT NULL,
  `instancia_id`   VARCHAR(50)      NOT NULL,
  `wa_message_id`  VARCHAR(191)     DEFAULT NULL COMMENT 'message.id._serialized; NULL mientras esta pendiente de envio',
  `direccion`      ENUM('entrante','saliente') NOT NULL,
  `origen`         ENUM('whatsapp','chat_web','celular','cron') NOT NULL DEFAULT 'whatsapp',
  `usuario_id`     INT UNSIGNED     DEFAULT NULL COMMENT 'usuarios.id que envio desde el chat web',
  `autor`          VARCHAR(100)     DEFAULT NULL COMMENT 'En grupos: quien escribio',
  `tipo`           VARCHAR(20)      NOT NULL DEFAULT 'chat' COMMENT 'chat, image, document, audio, ptt, video, sticker, location...',
  `cuerpo`         TEXT             DEFAULT NULL COMMENT 'Texto o caption',
  `tiene_media`    TINYINT(1)       NOT NULL DEFAULT 0,
  `respuesta_a`    VARCHAR(191)     DEFAULT NULL COMMENT 'wa_message_id del mensaje citado',
  `estado_envio`   ENUM('pendiente','enviando','enviado','error') DEFAULT NULL COMMENT 'Solo salientes creados desde Yupana',
  `intentos`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `error`          VARCHAR(255)     DEFAULT NULL,
  `ack`            TINYINT          NOT NULL DEFAULT 0 COMMENT 'Estado actual: -1 error, 0 pendiente, 1 servidor, 2 entregado, 3 leido, 4 reproducido',
  `enviado_at`     DATETIME         DEFAULT NULL COMMENT 'Salientes: ack 1 (llego al servidor de WhatsApp)',
  `entregado_at`   DATETIME         DEFAULT NULL COMMENT 'Salientes: ack 2 (entregado al telefono del contacto)',
  `visto_at`       DATETIME         DEFAULT NULL COMMENT 'Salientes: ack 3/4 (leido/reproducido por el contacto)',
  `fecha_mensaje`  DATETIME         NOT NULL,
  `created_at`     DATETIME         DEFAULT NULL,
  `updated_at`     DATETIME         DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wa_mensajes_instancia_msg` (`instancia_id`, `wa_message_id`),
  KEY `idx_wa_mensajes_chat` (`chat_id`, `id`),
  KEY `idx_wa_mensajes_outbox` (`instancia_id`, `estado_envio`),
  KEY `idx_wa_mensajes_usuario` (`usuario_id`),
  CONSTRAINT `fk_wa_mensajes_chat` FOREIGN KEY (`chat_id`)
    REFERENCES `wa_chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Archivos multimedia (guardados en disco del lado de Yupana)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wa_mensajes_media` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `mensaje_id`     BIGINT UNSIGNED NOT NULL,
  `mimetype`       VARCHAR(100)    NOT NULL,
  `nombre_archivo` VARCHAR(255)    DEFAULT NULL COMMENT 'Nombre original',
  `ruta`           VARCHAR(255)    NOT NULL COMMENT 'Relativa a writable/uploads/whatsapp/',
  `tamano_bytes`   INT UNSIGNED    DEFAULT NULL,
  `created_at`     DATETIME        DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wa_media_mensaje` (`mensaje_id`),
  CONSTRAINT `fk_wa_media_mensaje` FOREIGN KEY (`mensaje_id`)
    REFERENCES `wa_mensajes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Lecturas por usuario: hasta que mensaje leyo cada agente en cada chat
-- (no leidos por usuario = entrantes con id > ultimo_mensaje_id)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wa_chat_lecturas` (
  `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `chat_id`           INT UNSIGNED    NOT NULL,
  `usuario_id`        INT UNSIGNED    NOT NULL,
  `ultimo_mensaje_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `leido_at`          DATETIME        NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wa_lecturas_chat_usuario` (`chat_id`, `usuario_id`),
  KEY `idx_wa_lecturas_usuario` (`usuario_id`),
  CONSTRAINT `fk_wa_lecturas_chat` FOREIGN KEY (`chat_id`)
    REFERENCES `wa_chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Historial de asignaciones / transferencias / cierres
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wa_chat_eventos` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chat_id`       INT UNSIGNED    NOT NULL,
  `usuario_id`    INT UNSIGNED    DEFAULT NULL COMMENT 'Quien hizo la accion; NULL = sistema',
  `tipo`          ENUM('asignado','transferido','liberado','cerrado','reabierto') NOT NULL,
  `de_usuario_id` INT UNSIGNED    DEFAULT NULL,
  `a_usuario_id`  INT UNSIGNED    DEFAULT NULL,
  `nota`          VARCHAR(255)    DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wa_eventos_chat` (`chat_id`, `id`),
  CONSTRAINT `fk_wa_eventos_chat` FOREIGN KEY (`chat_id`)
    REFERENCES `wa_chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Datos iniciales
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `wa_instancias` (`id`, `label`, `estado`, `activo`, `created_at`, `updated_at`)
VALUES ('yupana', 'Yupana', 'desconocido', 1, NOW(), NOW());
