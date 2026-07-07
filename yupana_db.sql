-- ============================================================
-- YUPANA - Sistema Administrativo para Estudio Contable
-- Versión: 1.0
-- Motor:   MySQL 8.0+ / MariaDB 10.4+
-- País:    Perú
-- Autor:   Generado por Claude
-- ============================================================
-- Tablas: 29 (10 catálogos + 19 operativas)
-- Charset: utf8mb4 (soporte completo de caracteres y emojis)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE";

DROP DATABASE IF EXISTS yupana_db;
CREATE DATABASE yupana_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE yupana_db;

-- ============================================================
-- SECCIÓN 1: CATÁLOGOS BASE
-- Tablas de datos maestros (pocas filas, pocas escrituras)
-- ============================================================

-- ------------------------------------------------------------
-- 1.1 Roles de usuario del sistema
-- ------------------------------------------------------------
CREATE TABLE roles (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(20)  NOT NULL,
  nombre          VARCHAR(80)  NOT NULL,
  descripcion     VARCHAR(255) DEFAULT NULL,
  nivel           TINYINT UNSIGNED NOT NULL DEFAULT 1, -- 1=junior, 5=admin
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_roles_codigo (codigo)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.2 Regímenes tributarios (Perú - SUNAT)
-- ------------------------------------------------------------
CREATE TABLE regimenes_tributarios (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(10)  NOT NULL,           -- p.ej. 'NRUS', 'RER', 'RMT', 'RG'
  nombre          VARCHAR(120) NOT NULL,           -- 'Nuevo RUS', etc.
  descripcion     TEXT         DEFAULT NULL,
  aplica_a        ENUM('natural','juridica','ambos') NOT NULL DEFAULT 'ambos',
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_regimenes_codigo (codigo)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.3 Tipos de servicio contable
-- ------------------------------------------------------------
CREATE TABLE tipos_servicio (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(20)  NOT NULL,           -- 'CONT_GEN', 'NOMINA', 'AUDIT', etc.
  nombre          VARCHAR(120) NOT NULL,
  descripcion     VARCHAR(255) DEFAULT NULL,
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tipos_servicio_codigo (codigo)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.4 Tipos de comprobante de pago (catálogo SUNAT)
-- ------------------------------------------------------------
CREATE TABLE tipos_comprobante (
  id              TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(2)   NOT NULL,           -- '01' Factura, '03' Boleta, etc.
  nombre          VARCHAR(80)  NOT NULL,
  abreviatura     VARCHAR(20)  NOT NULL,           -- 'FAC', 'BOL', 'RH', etc.
  aplica_a        ENUM('ambos','natural','juridica') NOT NULL DEFAULT 'ambos',
  requiere_ruc    TINYINT(1)   NOT NULL DEFAULT 0, -- 1=obligatorio RUC
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tipos_comprobante_codigo (codigo)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.5 Tipos de documento (categorías de archivos)
-- ------------------------------------------------------------
CREATE TABLE tipos_documento (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(20)  NOT NULL,           -- 'CONTRATO','DNI','RUC','DJA','OTROS'
  nombre          VARCHAR(120) NOT NULL,
  descripcion     VARCHAR(255) DEFAULT NULL,
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tipos_documento_codigo (codigo)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.6 Métodos de pago
-- ------------------------------------------------------------
CREATE TABLE metodos_pago (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(20)  NOT NULL,           -- 'TRANSF','YAPE','PLIN','EFECT','CHEQUE'
  nombre          VARCHAR(80)  NOT NULL,
  descripcion     VARCHAR(255) DEFAULT NULL,
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_metodos_pago_codigo (codigo)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.7 Tipos de contacto
-- ------------------------------------------------------------
CREATE TABLE tipos_contacto (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre          VARCHAR(60)  NOT NULL,           -- 'Email','Teléfono','WhatsApp','LinkedIn'
  icono           VARCHAR(40)  DEFAULT NULL,
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tipos_contacto_nombre (nombre)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.8 Tipos de dirección
-- ------------------------------------------------------------
CREATE TABLE tipos_direccion (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre          VARCHAR(40)  NOT NULL,           -- 'Fiscal','Comercial','Domicilio','Sucursal'
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tipos_direccion_nombre (nombre)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.9 Ubigeos del Perú (departamento / provincia / distrito)
-- ------------------------------------------------------------
CREATE TABLE ubigeos (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          CHAR(6)      NOT NULL,           -- INEI 6 dígitos: DDPPDI
  departamento    VARCHAR(60)  NOT NULL,
  provincia       VARCHAR(60)  NOT NULL,
  distrito        VARCHAR(80)  NOT NULL,
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_ubigeos_codigo (codigo),
  KEY idx_ubigeos_departamento (departamento)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 1.10 Actividades económicas (CIIU Rev. 4 - Perú)
-- ------------------------------------------------------------
CREATE TABLE actividades_economicas (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(10)  NOT NULL,           -- CIIU: 4 dígitos (rev 4) o 5
  descripcion     VARCHAR(255) NOT NULL,
  categoria       VARCHAR(80)  DEFAULT NULL,
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_actividades_codigo (codigo)
) ENGINE=InnoDB;


-- ============================================================
-- SECCIÓN 2: ENTIDADES PRINCIPALES
-- ============================================================

-- ------------------------------------------------------------
-- 2.1 Usuarios del sistema (contadores, asistentes, admin)
-- ------------------------------------------------------------
CREATE TABLE usuarios (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  rol_id          SMALLINT UNSIGNED NOT NULL,
  nombres         VARCHAR(120) NOT NULL,
  apellidos       VARCHAR(120) NOT NULL,
  email           VARCHAR(180) NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  dni             CHAR(8)      DEFAULT NULL,
  telefono        VARCHAR(25)  DEFAULT NULL,
  avatar_url      VARCHAR(255) DEFAULT NULL,
  estado          ENUM('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  ultimo_acceso   DATETIME     DEFAULT NULL,
  email_verified_at DATETIME   DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_usuarios_email (email),
  UNIQUE KEY uk_usuarios_dni (dni),
  KEY idx_usuarios_rol (rol_id),
  CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2.2 Clientes (personas naturales y jurídicas)
-- ------------------------------------------------------------
CREATE TABLE clientes (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(20)  NOT NULL,                    -- código interno CLI-00001
  tipo_persona    ENUM('natural','juridica') NOT NULL,
  -- Identificación
  ruc             CHAR(11)     DEFAULT NULL,               -- 11 dígitos (Perú)
  dni             CHAR(8)      DEFAULT NULL,               -- 8 dígitos
  ce              VARCHAR(15)  DEFAULT NULL,               -- Carnet de Extranjería
  -- Datos según tipo de persona
  nombres         VARCHAR(120) DEFAULT NULL,               -- para naturales
  apellidos       VARCHAR(120) DEFAULT NULL,               -- para naturales
  razon_social    VARCHAR(255) DEFAULT NULL,               -- para jurídicas
  nombre_comercial VARCHAR(255) DEFAULT NULL,
  -- Datos generales
  email           VARCHAR(180) DEFAULT NULL,
  telefono        VARCHAR(25)  DEFAULT NULL,
  fecha_nacimiento DATE        DEFAULT NULL,               -- natural
  fecha_constitucion DATE      DEFAULT NULL,               -- jurídica
  -- Dirección principal
  direccion       VARCHAR(255) DEFAULT NULL,
  ubigeo_id       INT UNSIGNED DEFAULT NULL,
  referencia      VARCHAR(255) DEFAULT NULL,
  -- Régimen actual (denormalizado para consultas rápidas)
  regimen_actual_id SMALLINT UNSIGNED DEFAULT NULL,
  fecha_alta      DATE         NOT NULL,
  fecha_baja      DATE         DEFAULT NULL,
  estado          ENUM('activo','inactivo','suspendido','baja') NOT NULL DEFAULT 'activo',
  motivo_baja     VARCHAR(255) DEFAULT NULL,
  observaciones   TEXT         DEFAULT NULL,
  -- Auditoría
  usuario_registro_id INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_clientes_codigo (codigo),
  UNIQUE KEY uk_clientes_ruc (ruc),
  UNIQUE KEY uk_clientes_dni (dni),
  KEY idx_clientes_razon_social (razon_social),
  KEY idx_clientes_nombres (nombres, apellidos),
  KEY idx_clientes_estado (estado),
  KEY idx_clientes_regimen (regimen_actual_id),
  KEY idx_clientes_ubigeo (ubigeo_id),
  CONSTRAINT fk_clientes_regimen_actual FOREIGN KEY (regimen_actual_id) REFERENCES regimenes_tributarios (id) ON DELETE SET NULL,
  CONSTRAINT fk_clientes_ubigeo FOREIGN KEY (ubigeo_id) REFERENCES ubigeos (id) ON DELETE SET NULL,
  CONSTRAINT fk_clientes_usuario_registro FOREIGN KEY (usuario_registro_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  -- Valida coherencia RUC/DNI según tipo (lo aplicamos a nivel app)
  CONSTRAINT chk_clientes_identificacion CHECK (
    (tipo_persona = 'natural'  AND (dni IS NOT NULL OR ce IS NOT NULL)) OR
    (tipo_persona = 'juridica' AND ruc IS NOT NULL)
  )
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2.3 M:M Cliente - Actividades económicas (CIIU)
-- ------------------------------------------------------------
CREATE TABLE cliente_actividades (
  cliente_id      INT UNSIGNED NOT NULL,
  actividad_id    INT UNSIGNED NOT NULL,
  es_principal    TINYINT(1)   NOT NULL DEFAULT 0,
  fecha_inicio    DATE         DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (cliente_id, actividad_id),
  KEY idx_cliente_act_actividad (actividad_id),
  CONSTRAINT fk_cliact_cliente  FOREIGN KEY (cliente_id)   REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_cliact_actividad FOREIGN KEY (actividad_id) REFERENCES actividades_economicas (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2.4 Contactos del cliente (pueden ser varios)
-- ------------------------------------------------------------
CREATE TABLE contactos_cliente (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  tipo_contacto_id SMALLINT UNSIGNED NOT NULL,
  valor           VARCHAR(180) NOT NULL,                  -- email, teléfono, etc.
  contacto_nombre VARCHAR(180) DEFAULT NULL,             -- a nombre de quién está
  cargo           VARCHAR(120) DEFAULT NULL,             -- 'Gerente','Contador externo'
  es_principal    TINYINT(1)   NOT NULL DEFAULT 0,
  observaciones   VARCHAR(255) DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contactos_cliente (cliente_id),
  KEY idx_contactos_tipo (tipo_contacto_id),
  CONSTRAINT fk_contactos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_contactos_tipo    FOREIGN KEY (tipo_contacto_id) REFERENCES tipos_contacto (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2.5 Direcciones del cliente (puede tener más de una)
-- ------------------------------------------------------------
CREATE TABLE direcciones_cliente (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  tipo_direccion_id SMALLINT UNSIGNED NOT NULL,
  direccion       VARCHAR(255) NOT NULL,
  ubigeo_id       INT UNSIGNED DEFAULT NULL,
  referencia      VARCHAR(255) DEFAULT NULL,
  es_principal    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_dir_cliente (cliente_id),
  KEY idx_dir_tipo (tipo_direccion_id),
  KEY idx_dir_ubigeo (ubigeo_id),
  CONSTRAINT fk_dir_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_dir_tipo    FOREIGN KEY (tipo_direccion_id) REFERENCES tipos_direccion (id) ON DELETE RESTRICT,
  CONSTRAINT fk_dir_ubigeo  FOREIGN KEY (ubigeo_id) REFERENCES ubigeos (id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- SECCIÓN 3: SERVICIOS, TARIFAS E HISTORIAL
-- ============================================================

-- ------------------------------------------------------------
-- 3.1 Servicios contratados por cliente (M:M con atributos)
-- ------------------------------------------------------------
CREATE TABLE servicios_contratados (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  tipo_servicio_id SMALLINT UNSIGNED NOT NULL,
  fecha_inicio    DATE         NOT NULL,
  fecha_fin       DATE         DEFAULT NULL,
  descripcion     VARCHAR(255) DEFAULT NULL,
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_serv_cli (cliente_id),
  KEY idx_serv_tipo (tipo_servicio_id),
  CONSTRAINT fk_serv_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_serv_tipo    FOREIGN KEY (tipo_servicio_id) REFERENCES tipos_servicio (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3.2 Historial de tarifas mensuales por cliente
-- Un cliente puede tener varios montos en distintos periodos
-- ------------------------------------------------------------
CREATE TABLE tarifas_mensuales (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  servicio_contratado_id INT UNSIGNED DEFAULT NULL,    -- opcional: tarifa por servicio
  monto           DECIMAL(10,2) NOT NULL,              -- en soles
  moneda          CHAR(3)      NOT NULL DEFAULT 'PEN',
  fecha_inicio    DATE         NOT NULL,              -- inclusive
  fecha_fin       DATE         DEFAULT NULL,          -- NULL = vigente
  motivo_cambio   VARCHAR(255) DEFAULT NULL,
  usuario_id      INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tarifa_cliente (cliente_id, fecha_inicio),
  KEY idx_tarifa_servicio (servicio_contratado_id),
  KEY idx_tarifa_usuario (usuario_id),
  CONSTRAINT fk_tarifa_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_tarifa_servicio FOREIGN KEY (servicio_contratado_id) REFERENCES servicios_contratados (id) ON DELETE SET NULL,
  CONSTRAINT fk_tarifa_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT chk_tarifa_fechas CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio),
  CONSTRAINT chk_tarifa_monto CHECK (monto >= 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3.3 Tarifas anuales por cliente (honorarios anuales)
-- ------------------------------------------------------------
CREATE TABLE tarifas_anuales (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  anio            SMALLINT UNSIGNED NOT NULL,         -- 2024, 2025, ...
  monto           DECIMAL(10,2) NOT NULL,
  moneda          CHAR(3)      NOT NULL DEFAULT 'PEN',
  concepto        VARCHAR(255) DEFAULT NULL,         -- 'Pago anual','Regularización', etc.
  observaciones   TEXT         DEFAULT NULL,
  usuario_id      INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tarifa_anual_cliente_anio (cliente_id, anio),
  KEY idx_tarifa_anio (anio),
  CONSTRAINT fk_tarifa_anual_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_tarifa_anual_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT chk_tarifa_anual_monto CHECK (monto >= 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3.4 Historial de regímenes del cliente
-- ------------------------------------------------------------
CREATE TABLE cliente_regimen_historial (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  regimen_id      SMALLINT UNSIGNED NOT NULL,
  fecha_inicio    DATE         NOT NULL,
  fecha_fin       DATE         DEFAULT NULL,          -- NULL = vigente
  motivo          VARCHAR(255) DEFAULT NULL,
  documento_sustento VARCHAR(255) DEFAULT NULL,       -- ruta al archivo de sustento
  usuario_id      INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reghist_cliente (cliente_id, fecha_inicio),
  KEY idx_reghist_regimen (regimen_id),
  KEY idx_reghist_usuario (usuario_id),
  CONSTRAINT fk_reghist_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_reghist_regimen FOREIGN KEY (regimen_id) REFERENCES regimenes_tributarios (id) ON DELETE RESTRICT,
  CONSTRAINT fk_reghist_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT chk_reghist_fechas CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio)
) ENGINE=InnoDB;


-- ============================================================
-- SECCIÓN 4: CONTRATOS Y DOCUMENTOS
-- ============================================================

-- ------------------------------------------------------------
-- 4.1 Contratos firmados con el cliente
-- ------------------------------------------------------------
CREATE TABLE contratos (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  numero_contrato VARCHAR(40)  NOT NULL,
  tipo            ENUM('servicios','confidencialidad','honorarios','otro') NOT NULL DEFAULT 'servicios',
  fecha_firma     DATE         NOT NULL,
  fecha_inicio    DATE         NOT NULL,
  fecha_fin       DATE         DEFAULT NULL,
  monto_total     DECIMAL(10,2) DEFAULT NULL,
  moneda          CHAR(3)      NOT NULL DEFAULT 'PEN',
  estado          ENUM('borrador','activo','vencido','renovado','rescindido') NOT NULL DEFAULT 'activo',
  archivo_url     VARCHAR(500) NOT NULL,              -- ruta del PDF firmado
  archivo_nombre  VARCHAR(255) NOT NULL,
  archivo_size    INT UNSIGNED DEFAULT NULL,          -- bytes
  archivo_hash    VARCHAR(64)  DEFAULT NULL,          -- SHA-256
  observaciones   TEXT         DEFAULT NULL,
  usuario_id      INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_contratos_numero (numero_contrato),
  KEY idx_contratos_cliente (cliente_id),
  KEY idx_contratos_estado (estado),
  KEY idx_contratos_fecha_fin (fecha_fin),
  CONSTRAINT fk_contratos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_contratos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4.2 Otros documentos asociados al cliente
-- ------------------------------------------------------------
CREATE TABLE documentos_cliente (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  tipo_documento_id SMALLINT UNSIGNED NOT NULL,
  nombre_documento VARCHAR(255) NOT NULL,
  descripcion     VARCHAR(255) DEFAULT NULL,
  archivo_url     VARCHAR(500) NOT NULL,
  archivo_nombre  VARCHAR(255) NOT NULL,
  archivo_size    INT UNSIGNED DEFAULT NULL,
  archivo_hash    VARCHAR(64)  DEFAULT NULL,
  fecha_documento DATE         DEFAULT NULL,
  fecha_vencimiento DATE       DEFAULT NULL,
  usuario_id      INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_doccli_cliente (cliente_id),
  KEY idx_doccli_tipo (tipo_documento_id),
  KEY idx_doccli_vencimiento (fecha_vencimiento),
  CONSTRAINT fk_doccli_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_doccli_tipo    FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento (id) ON DELETE RESTRICT,
  CONSTRAINT fk_doccli_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- SECCIÓN 5: COBROS, PAGOS Y COMPROBANTES
-- ============================================================

-- ------------------------------------------------------------
-- 5.1 Cobros mensuales generados
-- (uno por periodo; control de estado y vencimiento)
-- ------------------------------------------------------------
CREATE TABLE cobros_mensuales (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  servicio_contratado_id INT UNSIGNED DEFAULT NULL,
  periodo         CHAR(7)      NOT NULL,              -- 'YYYY-MM'
  fecha_emision   DATE         NOT NULL,
  fecha_vencimiento DATE       NOT NULL,
  concepto        VARCHAR(255) DEFAULT NULL,
  monto           DECIMAL(10,2) NOT NULL,
  monto_pagado    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  moneda          CHAR(3)      NOT NULL DEFAULT 'PEN',
  estado          ENUM('pendiente','pagado','parcial','vencido','anulado') NOT NULL DEFAULT 'pendiente',
  observaciones   TEXT         DEFAULT NULL,
  usuario_id      INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_cobro_cliente_periodo_servicio (cliente_id, periodo, servicio_contratado_id),
  KEY idx_cobros_estado (estado),
  KEY idx_cobros_vencimiento (fecha_vencimiento),
  KEY idx_cobros_periodo (periodo),
  CONSTRAINT fk_cobros_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_cobros_servicio FOREIGN KEY (servicio_contratado_id) REFERENCES servicios_contratados (id) ON DELETE SET NULL,
  CONSTRAINT fk_cobros_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT chk_cobros_monto CHECK (monto >= 0),
  CONSTRAINT chk_cobros_pagado CHECK (monto_pagado >= 0 AND monto_pagado <= monto)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5.2 Pagos aplicados a cobros
-- (un cobro puede tener varios pagos parciales)
-- ------------------------------------------------------------
CREATE TABLE pagos (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cobro_id        INT UNSIGNED NOT NULL,
  metodo_pago_id  SMALLINT UNSIGNED NOT NULL,
  fecha_pago      DATE         NOT NULL,
  monto           DECIMAL(10,2) NOT NULL,
  moneda          CHAR(3)      NOT NULL DEFAULT 'PEN',
  numero_operacion VARCHAR(80) DEFAULT NULL,
  comprobante_url VARCHAR(500) DEFAULT NULL,          -- voucher / captura
  banco           VARCHAR(80)  DEFAULT NULL,
  observaciones   TEXT         DEFAULT NULL,
  usuario_id      INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pagos_cobro (cobro_id),
  KEY idx_pagos_fecha (fecha_pago),
  KEY idx_pagos_metodo (metodo_pago_id),
  CONSTRAINT fk_pagos_cobro  FOREIGN KEY (cobro_id) REFERENCES cobros_mensuales (id) ON DELETE CASCADE,
  CONSTRAINT fk_pagos_metodo FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago (id) ON DELETE RESTRICT,
  CONSTRAINT fk_pagos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT chk_pagos_monto CHECK (monto > 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5.3 Comprobantes emitidos al cliente (facturas, boletas, RH)
-- ------------------------------------------------------------
CREATE TABLE comprobantes_emitidos (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  tipo_comprobante_id TINYINT UNSIGNED NOT NULL,
  serie           VARCHAR(4)   NOT NULL,              -- 'F001','B001','E001'
  numero          VARCHAR(20)  NOT NULL,
  fecha_emision   DATE         NOT NULL,
  periodo         CHAR(7)      DEFAULT NULL,          -- 'YYYY-MM' al que corresponde
  moneda          CHAR(3)      NOT NULL DEFAULT 'PEN',
  subtotal        DECIMAL(10,2) NOT NULL,
  igv             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total           DECIMAL(10,2) NOT NULL,
  estado_sunat    ENUM('pendiente','enviado','aceptado','rechazado','anulado','baja') NOT NULL DEFAULT 'pendiente',
  estado_pago     ENUM('no_pagado','pagado','parcial') NOT NULL DEFAULT 'no_pagado',
  xml_url         VARCHAR(500) DEFAULT NULL,
  cdr_url         VARCHAR(500) DEFAULT NULL,
  pdf_url         VARCHAR(500) DEFAULT NULL,
  hash_cpe        VARCHAR(64)  DEFAULT NULL,
  observaciones   TEXT         DEFAULT NULL,
  usuario_id      INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_comprobante_serie_numero (serie, numero, tipo_comprobante_id),
  KEY idx_comp_cliente (cliente_id),
  KEY idx_comp_fecha (fecha_emision),
  KEY idx_comp_estado_sunat (estado_sunat),
  CONSTRAINT fk_comp_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_comp_tipo    FOREIGN KEY (tipo_comprobante_id) REFERENCES tipos_comprobante (id) ON DELETE RESTRICT,
  CONSTRAINT fk_comp_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT chk_comp_montos CHECK (total >= 0 AND subtotal >= 0 AND igv >= 0)
) ENGINE=InnoDB;


-- ============================================================
-- SECCIÓN 6: OPERACIÓN CONTABLE
-- ============================================================

-- ------------------------------------------------------------
-- 6.1 Períodos contables (libros electrónicos PLE)
-- ------------------------------------------------------------
CREATE TABLE periodos_contables (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  anio            SMALLINT UNSIGNED NOT NULL,
  mes             TINYINT UNSIGNED NOT NULL,         -- 1..12
  estado          ENUM('abierto','en_proceso','cerrado','presentado') NOT NULL DEFAULT 'abierto',
  fecha_cierre    DATE         DEFAULT NULL,
  fecha_presentacion DATE      DEFAULT NULL,
  numero_operaciones INT UNSIGNED DEFAULT NULL,
  observaciones   TEXT         DEFAULT NULL,
  usuario_cierre_id INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_periodo_cliente (cliente_id, anio, mes),
  KEY idx_periodo_estado (estado),
  CONSTRAINT fk_periodo_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_periodo_usuario FOREIGN KEY (usuario_cierre_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT chk_periodo_mes CHECK (mes BETWEEN 1 AND 12)
) ENGINE=InnoDB;


-- ============================================================
-- SECCIÓN 7: NOTAS, TAREAS Y ALERTAS
-- ============================================================

-- ------------------------------------------------------------
-- 7.1 Notas / bitácora del cliente
-- ------------------------------------------------------------
CREATE TABLE notas_cliente (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED NOT NULL,
  usuario_id      INT UNSIGNED NOT NULL,
  nota            TEXT         NOT NULL,
  tipo            ENUM('general','cobranza','tributaria','laboral','recordatorio') NOT NULL DEFAULT 'general',
  fecha           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notas_cliente (cliente_id, fecha),
  KEY idx_notas_usuario (usuario_id),
  CONSTRAINT fk_notas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_notas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7.2 Tareas asignadas
-- ------------------------------------------------------------
CREATE TABLE tareas (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED DEFAULT NULL,
  titulo          VARCHAR(180) NOT NULL,
  descripcion     TEXT         DEFAULT NULL,
  prioridad       ENUM('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  estado          ENUM('pendiente','en_progreso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  fecha_vencimiento DATE       DEFAULT NULL,
  fecha_completada DATETIME    DEFAULT NULL,
  asignado_a_id   INT UNSIGNED DEFAULT NULL,
  creado_por_id   INT UNSIGNED NOT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tareas_cliente (cliente_id),
  KEY idx_tareas_asignado (asignado_a_id),
  KEY idx_tareas_estado (estado),
  KEY idx_tareas_vencimiento (fecha_vencimiento),
  CONSTRAINT fk_tareas_cliente    FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_tareas_asignado   FOREIGN KEY (asignado_a_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT fk_tareas_creador    FOREIGN KEY (creado_por_id) REFERENCES usuarios (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7.3 Alertas automáticas del sistema
-- (vencimientos, cumpleaños, declaraciones, etc.)
-- ------------------------------------------------------------
CREATE TABLE alertas_sistema (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id      INT UNSIGNED DEFAULT NULL,
  tipo            ENUM('vencimiento_contrato','vencimiento_documento','cobro_vencido','declaracion_proxima','cumpleanos','onboarding') NOT NULL,
  titulo          VARCHAR(180) NOT NULL,
  mensaje         TEXT         DEFAULT NULL,
  fecha_alerta    DATE         NOT NULL,
  estado          ENUM('pendiente','vista','resuelta','descartada') NOT NULL DEFAULT 'pendiente',
  usuario_asignado_id INT UNSIGNED DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_alertas_estado (estado),
  KEY idx_alertas_fecha (fecha_alerta),
  KEY idx_alertas_cliente (cliente_id),
  CONSTRAINT fk_alertas_cliente  FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
  CONSTRAINT fk_alertas_usuario  FOREIGN KEY (usuario_asignado_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- SECCIÓN 8: AUDITORÍA
-- ============================================================

-- ------------------------------------------------------------
-- 8.1 Log de cambios (trazabilidad de operaciones)
-- ------------------------------------------------------------
CREATE TABLE log_auditoria (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tabla           VARCHAR(80)  NOT NULL,
  registro_id     INT UNSIGNED NOT NULL,
  accion          ENUM('insert','update','delete') NOT NULL,
  datos_anteriores JSON        DEFAULT NULL,
  datos_nuevos    JSON         DEFAULT NULL,
  usuario_id      INT UNSIGNED DEFAULT NULL,
  ip              VARCHAR(45)  DEFAULT NULL,
  user_agent      VARCHAR(255) DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_log_tabla (tabla, registro_id),
  KEY idx_log_usuario (usuario_id),
  KEY idx_log_fecha (created_at),
  CONSTRAINT fk_log_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SECCIÓN 9: DATOS SEMILLA (CATÁLOGOS SUNAT / BÁSICOS)
-- ============================================================

-- Regímenes tributarios del Perú
INSERT INTO regimenes_tributarios (codigo, nombre, descripcion, aplica_a) VALUES
  ('NRUS', 'Nuevo RUS', 'Nuevo Régimen Único Simplificado. Ingresos brutos anuales hasta S/ 96,000. Solo personas naturales.', 'natural'),
  ('RER',  'Régimen Especial de Renta (RER)', 'Personas naturales y jurídicas con ingresos anuales hasta S/ 525,000.', 'ambos'),
  ('RMT',  'Régimen MYPE Tributario (RMT)', 'Micro y pequeñas empresas. Ingresos anuales hasta 1,700 UIT.', 'ambos'),
  ('RG',   'Régimen General', 'Personas naturales y jurídicas sin límite de ingresos. Lleva contabilidad completa.', 'ambos'),
  ('RA',   'Régimen de la Amazonía', 'Regímenes especiales para la Amazonía (Ley N° 27037).', 'ambos'),
  ('NRUS_ANT', 'RUS (anterior)', 'Régimen Único Simplificado derogado, mantenido solo histórico.', 'natural');

-- Tipos de servicio
INSERT INTO tipos_servicio (codigo, nombre, descripcion) VALUES
  ('CONT_GEN',  'Contabilidad General',     'Llevar libros contables, Estados Financieros, PLE'),
  ('NOMINA',    'Planilla y Nóminas',       'Gestión de planilla, PLAME, T-Registro, CTS, gratificaciones'),
  ('IMPUESTOS', 'Liquidación de Impuestos', 'PDT 621, 601, 616, declaraciones mensuales y anuales'),
  ('AUDIT',     'Auditoría',                'Auditoría financiera, tributaria o de gestión'),
  ('CONST',     'Constitución de Empresa',  'Trámite de RUC, búsqueda de nombre, estatutos'),
  ('FISCAL',    'Asesoría Fiscal',          'Planeamiento tributario, fiscalizaciones SUNAT'),
  ('LABORAL',   'Asesoría Laboral',         'Consultas laborales, contratos, sanciones'),
  ('CONTABLE_ESP', 'Servicios Contables Especiales', 'Pericias, informes técnicos, due diligence'),
  ('OTRO',      'Otro',                     'Servicios no categorizados');

-- Tipos de comprobante de pago (SUNAT)
INSERT INTO tipos_comprobante (codigo, nombre, abreviatura, aplica_a, requiere_ruc) VALUES
  ('01', 'Factura electrónica',         'FAC', 'juridica', 1),
  ('03', 'Boleta de venta electrónica', 'BOL', 'ambos',    0),
  ('07', 'Nota de crédito',             'NC',  'ambos',    0),
  ('08', 'Nota de débito',              'ND',  'ambos',    0),
  ('09', 'Guía de remisión',            'GR',  'ambos',    0),
  ('14', 'Recibo por honorarios',       'RH',  'natural',  0),
  ('40', 'Comprobante de retención',    'CR',  'juridica', 1);

-- Tipos de documento
INSERT INTO tipos_documento (codigo, nombre, descripcion) VALUES
  ('CONTRATO',   'Contrato',         'Contratos firmados con el cliente'),
  ('DNI',        'DNI',              'Documento Nacional de Identidad'),
  ('RUC',        'RUC',              'Registro Único de Contribuyentes'),
  ('CE',         'Carnet de Extranjería', 'Documento de identidad extranjeros'),
  ('DJA',        'Declaración Jurada',     'Declaraciones juradas'),
  ('CONSTITUCION','Escritura de Constitución', 'Partida registral / SUNARP'),
  ('LICENCIA',   'Licencia / Autorización', 'Licencias municipales, autorizaciones'),
  ('PODER',      'Poderes',          'Poderes notariales, cartas poder'),
  ('ESTADO_CTA', 'Estado de Cuenta', 'Estados de cuenta bancarios'),
  ('OTRO',       'Otro documento',   'Cualquier otro documento');

-- Métodos de pago
INSERT INTO metodos_pago (codigo, nombre, descripcion) VALUES
  ('TRANSF', 'Transferencia bancaria', 'Transferencia desde cuenta bancaria'),
  ('YAPE',   'Yape',                   'Aplicativo Yape (BCP)'),
  ('PLIN',   'Plin',                   'Aplicativo Plin'),
  ('EFECT',  'Efectivo',               'Pago en efectivo en oficina'),
  ('CHEQUE', 'Cheque',                 'Cheque no negociable'),
  ('TARJETA','Tarjeta de crédito/débito','Pago con tarjeta'),
  ('DEPOSITO','Depósito en cuenta',     'Depósito directo en cuenta del estudio'),
  ('OTRO',   'Otro método',            'Otros métodos no listados');

-- Tipos de contacto
INSERT INTO tipos_contacto (nombre, icono) VALUES
  ('Email',     'mail'),
  ('Teléfono',  'phone'),
  ('Celular',   'smartphone'),
  ('WhatsApp',  'message-circle'),
  ('LinkedIn',  'linkedin'),
  ('Facebook',  'facebook'),
  ('Sitio Web', 'globe');

-- Tipos de dirección
INSERT INTO tipos_direccion (nombre) VALUES
  ('Fiscal'),
  ('Comercial'),
  ('Domicilio'),
  ('Sucursal'),
  ('Almacén'),
  ('Obra');

-- Roles iniciales
INSERT INTO roles (codigo, nombre, descripcion, nivel) VALUES
  ('ADMIN',    'Administrador',   'Acceso total al sistema', 5),
  ('SOCIO',    'Socio',           'Dueño o socio del estudio contable', 4),
  ('CONTADOR', 'Contador Senior', 'Contador principal, puede cerrar períodos', 3),
  ('ASISTENTE','Asistente Contable','Apoya en registros y tareas operativas', 2),
  ('PRACT',    'Practicante',     'Acceso limitado de consulta', 1);

-- Usuario administrador inicial (cambiar password en primer login)
-- password: admin123   hash bcrypt genérico (reemplazar en producción)
INSERT INTO usuarios (rol_id, nombres, apellidos, email, password_hash, dni, estado) VALUES
  (1, 'Administrador', 'del Sistema', 'admin@yupana.local', '$2y$10$REEMPLAZAR_CON_HASH_BCRYPT_VALIDO', '00000000', 'activo');


-- ============================================================
-- SECCIÓN 10: VISTAS ÚTILES PARA REPORTES
-- ============================================================

-- Vista: clientes con su régimen actual
CREATE OR REPLACE VIEW v_clientes_regimen AS
SELECT
  c.id, c.codigo, c.tipo_persona, c.ruc, c.dni, c.razon_social,
  c.nombre_comercial, c.nombres, c.apellidos, c.email, c.telefono,
  c.estado, c.fecha_alta,
  r.codigo AS regimen_codigo, r.nombre AS regimen_nombre
FROM clientes c
LEFT JOIN regimenes_tributarios r ON c.regimen_actual_id = r.id;

-- Vista: tarifa mensual vigente por cliente
CREATE OR REPLACE VIEW v_tarifa_vigente AS
SELECT
  t.id, t.cliente_id, c.codigo AS cliente_codigo,
  COALESCE(c.razon_social, CONCAT(c.nombres,' ',c.apellidos)) AS cliente_nombre,
  t.monto, t.moneda, t.fecha_inicio, t.fecha_fin, t.motivo_cambio
FROM tarifas_mensuales t
JOIN clientes c ON c.id = t.cliente_id
WHERE t.fecha_fin IS NULL;

-- Vista: cobros pendientes / vencidos con días de mora
CREATE OR REPLACE VIEW v_cobros_pendientes AS
SELECT
  co.id, co.cliente_id, cl.codigo AS cliente_codigo,
  COALESCE(cl.razon_social, CONCAT(cl.nombres,' ',cl.apellidos)) AS cliente_nombre,
  cl.email, cl.telefono,
  co.periodo, co.fecha_emision, co.fecha_vencimiento,
  co.monto, co.monto_pagado, (co.monto - co.monto_pagado) AS saldo,
  co.estado,
  CASE
    WHEN co.fecha_vencimiento < CURDATE() AND co.estado NOT IN ('pagado','anulado')
    THEN DATEDIFF(CURDATE(), co.fecha_vencimiento)
    ELSE 0
  END AS dias_mora
FROM cobros_mensuales co
JOIN clientes cl ON cl.id = co.cliente_id
WHERE co.estado IN ('pendiente','parcial','vencido');

-- Vista: total facturado por cliente por año
CREATE OR REPLACE VIEW v_facturacion_anual AS
SELECT
  cl.id AS cliente_id,
  cl.codigo AS cliente_codigo,
  COALESCE(cl.razon_social, CONCAT(cl.nombres,' ',cl.apellidos)) AS cliente_nombre,
  YEAR(co.fecha_emision) AS anio,
  COUNT(*) AS total_comprobantes,
  SUM(co.total) AS monto_total,
  SUM(CASE WHEN co.estado_sunat = 'anulado' THEN co.total ELSE 0 END) AS monto_anulado
FROM clientes cl
JOIN comprobantes_emitidos co ON co.cliente_id = cl.id
GROUP BY cl.id, cl.codigo, cliente_nombre, anio;

-- Vista: historial de regímenes del cliente
CREATE OR REPLACE VIEW v_cliente_regimen_historial AS
SELECT
  crh.id, crh.cliente_id, c.codigo AS cliente_codigo,
  COALESCE(c.razon_social, CONCAT(c.nombres,' ',c.apellidos)) AS cliente_nombre,
  r.codigo AS regimen_codigo, r.nombre AS regimen_nombre,
  crh.fecha_inicio, crh.fecha_fin,
  CASE WHEN crh.fecha_fin IS NULL THEN 'Vigente' ELSE 'Anterior' END AS situacion,
  crh.motivo
FROM cliente_regimen_historial crh
JOIN clientes c ON c.id = crh.cliente_id
JOIN regimenes_tributarios r ON r.id = crh.regimen_id;

-- ============================================================
-- SECCIÓN 11: PROCEDIMIENTOS ÚTILES
-- ============================================================

DELIMITER $$

-- Genera el siguiente código de cliente (CLI-00001, CLI-00002, ...)
CREATE PROCEDURE sp_siguiente_codigo_cliente(OUT siguiente VARCHAR(20))
BEGIN
  DECLARE max_num INT UNSIGNED DEFAULT 0;
  SELECT MAX(CAST(SUBSTRING(codigo, 5) AS UNSIGNED)) INTO max_num
  FROM clientes
  WHERE codigo REGEXP '^CLI-[0-9]+$';
  SET siguiente = CONCAT('CLI-', LPAD(max_num + 1, 5, '0'));
END$$

-- Cierra un período contable validando que no esté ya cerrado
CREATE PROCEDURE sp_cerrar_periodo(IN p_cliente_id INT UNSIGNED, IN p_anio SMALLINT, IN p_mes TINYINT, IN p_usuario_id INT UNSIGNED)
BEGIN
  UPDATE periodos_contables
  SET estado = 'cerrado',
      fecha_cierre = CURDATE(),
      usuario_cierre_id = p_usuario_id
  WHERE cliente_id = p_cliente_id
    AND anio = p_anio
    AND mes = p_mes
    AND estado NOT IN ('cerrado','presentado');
END$$

DELIMITER ;

-- ============================================================
-- SECCIÓN 12: DATOS DE PRUEBA (EJEMPLO)
-- ============================================================

-- Ubigeos principales (algunos ejemplos - cargar INEI completo aparte)
INSERT INTO ubigeos (codigo, departamento, provincia, distrito) VALUES
  ('150101', 'Lima',       'Lima',       'Lima'),
  ('150113', 'Lima',       'Lima',       'Jesús María'),
  ('150114', 'Lima',       'Lima',       'La Molina'),
  ('150115', 'Lima',       'Lima',       'La Victoria'),
  ('150122', 'Lima',       'Lima',       'Miraflores'),
  ('150130', 'Lima',       'Lima',       'San Borja'),
  ('150131', 'Lima',       'Lima',       'San Isidro'),
  ('150132', 'Lima',       'Lima',       'San Juan de Lurigancho'),
  ('150133', 'Lima',       'Lima',       'San Juan de Miraflores'),
  ('150140', 'Lima',       'Lima',       'Santiago de Surco'),
  ('150141', 'Lima',       'Lima',       'Surquillo'),
  ('130101', 'La Libertad','Trujillo',   'Trujillo'),
  ('040101', 'Arequipa',   'Arequipa',   'Arequipa'),
  ('080101', 'Cusco',      'Cusco',      'Cusco'),
  ('200101', 'Piura',      'Piura',      'Piura');

-- Actividades económicas CIIU (algunos ejemplos)
INSERT INTO actividades_economicas (codigo, descripcion, categoria) VALUES
  ('6920',  'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'Servicios profesionales'),
  ('7010',  'Actividades de oficinas centrales', 'Gestión administrativa'),
  ('7022',  'Actividades de consultoría de gestión', 'Consultoría'),
  ('4719',  'Comercio al por menor en comercios no especializados', 'Comercio'),
  ('5610',  'Actividades de restaurantes y de servicio móvil de comidas', 'Restaurantes'),
  ('6201',  'Programación informática', 'Tecnología'),
  ('6202',  'Consultoría informática y gestión de instalaciones informáticas', 'Tecnología'),
  ('7110',  'Actividades de arquitectura e ingeniería y otras actividades técnicas de consultoría', 'Servicios profesionales'),
  ('6820',  'Actividades inmobiliarias realizadas con bienes propios o arrendados', 'Inmobiliaria'),
  ('8690',  'Otras actividades de atención de la salud humana', 'Salud');

-- Cliente de ejemplo
INSERT INTO clientes (codigo, tipo_persona, ruc, razon_social, nombre_comercial, email, telefono, direccion, regimen_actual_id, fecha_alta, estado, usuario_registro_id)
VALUES ('CLI-00001', 'juridica', '20123456789', 'Comercial ABC S.A.C.', 'ABC Comercial',
        'contacto@abc.com', '987654321', 'Av. La Marina 123, La Molina', 3, '2026-01-15', 'activo', 1);

-- Asignar actividad económica al cliente de ejemplo
INSERT INTO cliente_actividades (cliente_id, actividad_id, es_principal) VALUES (1, 4, 1);

-- Tarifa mensual para el cliente de ejemplo
INSERT INTO tarifas_mensuales (cliente_id, monto, fecha_inicio, motivo_cambio, usuario_id)
VALUES (1, 850.00, '2026-01-01', 'Tarifa inicial', 1);

-- Tarifa anual para el cliente de ejemplo
INSERT INTO tarifas_anuales (cliente_id, anio, monto, concepto, usuario_id)
VALUES (1, 2026, 10200.00, 'Pago anual 2026 (12 meses)', 1);

-- Historial de régimen para el cliente de ejemplo
INSERT INTO cliente_regimen_historial (cliente_id, regimen_id, fecha_inicio, motivo, usuario_id)
VALUES
  (1, 4, '2024-01-01', 'Inicio como Régimen General', 1),
  (1, 3, '2026-01-01', 'Cambio a MYPE Tributario', 1);

-- Servicio contratado
INSERT INTO servicios_contratados (cliente_id, tipo_servicio_id, fecha_inicio, descripcion, activo)
VALUES (1, 1, '2026-01-15', 'Contabilidad general mensual', 1),
       (1, 2, '2026-01-15', 'Planilla de 5 trabajadores', 1);

-- Contacto del cliente
INSERT INTO contactos_cliente (cliente_id, tipo_contacto_id, valor, contacto_nombre, cargo, es_principal)
VALUES (1, 4, '51987654321', 'Juan Pérez', 'Gerente General', 1);

-- Cobro mensual del cliente (corriente mes)
INSERT INTO cobros_mensuales (cliente_id, servicio_contratado_id, periodo, fecha_emision, fecha_vencimiento, monto, estado, usuario_id)
VALUES (1, 1, '2026-06', '2026-06-01', '2026-06-15', 850.00, 'pendiente', 1);


-- ============================================================
-- SECCIÓN 13: SEGURIDAD — MÓDULOS Y PERMISOS (RBAC granular)
-- Cada rol accede a uno o varios módulos y, dentro de cada módulo,
-- puede tener permisos específicos: leer, crear, editar, eliminar,
-- exportar, importar, aprobar, anular, cerrar, reimprimir, etc.
-- ============================================================

-- ------------------------------------------------------------
-- 13.1 Módulos del sistema (catálogo configurable)
-- ------------------------------------------------------------
CREATE TABLE modulos (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  padre_id        SMALLINT UNSIGNED DEFAULT NULL,         -- NULL = módulo raíz; si tiene valor = submódulo
  codigo          VARCHAR(60)  NOT NULL,                  -- 'clientes','clientes.naturales','comprobantes',...
  nombre          VARCHAR(120) NOT NULL,                  -- 'Clientes','Personas Naturales',...
  descripcion     VARCHAR(255) DEFAULT NULL,
  icono           VARCHAR(40)  DEFAULT NULL,              -- nombre de icono para UI
  ruta            VARCHAR(120) DEFAULT NULL,              -- ruta en frontend ('/clientes', '/clientes/naturales', ...)
  orden           SMALLINT UNSIGNED NOT NULL DEFAULT 0,   -- para ordenar en menú
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_modulos_codigo (codigo),
  KEY idx_modulos_padre (padre_id),
  KEY idx_modulos_orden (orden),
  CONSTRAINT fk_modulos_padre FOREIGN KEY (padre_id) REFERENCES modulos (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13.2 Catálogo de acciones / permisos atómicos
-- (CRUD + acciones especiales del sistema contable)
-- ------------------------------------------------------------
CREATE TABLE permisos (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo          VARCHAR(40)  NOT NULL,                  -- 'leer','crear','editar','eliminar',...
  nombre          VARCHAR(80)  NOT NULL,
  descripcion     VARCHAR(255) DEFAULT NULL,
  activo          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_permisos_codigo (codigo)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13.3 Relación rol ↔ módulo (a qué módulos puede entrar el rol)
-- Si un rol no aparece aquí para un módulo, no puede acceder a él.
-- ------------------------------------------------------------
CREATE TABLE roles_modulos (
  rol_id          SMALLINT UNSIGNED NOT NULL,
  modulo_id       SMALLINT UNSIGNED NOT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rol_id, modulo_id),
  KEY idx_rm_modulo (modulo_id),
  CONSTRAINT fk_rm_rol    FOREIGN KEY (rol_id)    REFERENCES roles    (id) ON DELETE CASCADE,
  CONSTRAINT fk_rm_modulo FOREIGN KEY (modulo_id) REFERENCES modulos  (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13.4 Permisos específicos por (rol, módulo)
-- Si un rol entra al módulo pero no tiene un permiso aquí, no
-- puede ejecutar esa acción. Permite control fino por acción.
-- ------------------------------------------------------------
CREATE TABLE roles_modulos_permisos (
  rol_id          SMALLINT UNSIGNED NOT NULL,
  modulo_id       SMALLINT UNSIGNED NOT NULL,
  permiso_id      SMALLINT UNSIGNED NOT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (rol_id, modulo_id, permiso_id),
  KEY idx_rmp_permiso (permiso_id),
  CONSTRAINT fk_rmp_rm     FOREIGN KEY (rol_id, modulo_id) REFERENCES roles_modulos (rol_id, modulo_id) ON DELETE CASCADE,
  CONSTRAINT fk_rmp_permiso FOREIGN KEY (permiso_id)       REFERENCES permisos     (id)              ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- SECCIÓN 14: DATOS SEMILLA — MÓDULOS Y PERMISOS
-- ============================================================

-- ============================================================
-- Módulos raíz del sistema Yupana (padre_id = NULL)
-- ============================================================
INSERT INTO modulos (padre_id, codigo, nombre, descripcion, icono, ruta, orden) VALUES
  (NULL, 'dashboard',    'Dashboard',              'Panel principal con indicadores y alertas',           'home',           '/dashboard',              10),
  (NULL, 'clientes',     'Clientes',               'Gestión de personas naturales y jurídicas',           'users',          '/clientes',               20),
  (NULL, 'servicios',    'Servicios Contratados',  'Servicios contables contratados por cliente',        'briefcase',      '/servicios',              30),
  (NULL, 'tarifas',      'Tarifas',                'Tarifas mensuales y anuales por cliente',            'dollar-sign',    '/tarifas',                40),
  (NULL, 'comprobantes', 'Comprobantes de Pago',   'Facturas, boletas, notas, recibos por honorarios',    'file-text',      '/comprobantes',           50),
  (NULL, 'cobros',       'Cobros y Pagos',         'Cobros mensuales y pagos aplicados',                 'credit-card',    '/cobros',                 60),
  (NULL, 'contratos',    'Contratos',              'Contratos firmados con clientes',                    'file-signature', '/contratos',              70),
  (NULL, 'documentos',   'Documentos',             'Documentos asociados al cliente (DNI, RUC, etc.)',   'folder',         '/documentos',             80),
  (NULL, 'periodos',     'Períodos Contables',     'Apertura, cierre y presentación de períodos',        'calendar',       '/periodos',               90),
  (NULL, 'notas',        'Notas / Bitácora',       'Notas internas sobre clientes',                      'message-square', '/notas',                 100),
  (NULL, 'tareas',       'Tareas',                 'Tareas asignadas al equipo',                         'check-square',   '/tareas',                110),
  (NULL, 'alertas',      'Alertas',                'Alertas automáticas del sistema',                    'bell',           '/alertas',               120),
  (NULL, 'reportes',     'Reportes',               'Reportes gerenciales y fiscales',                    'bar-chart',      '/reportes',              130),
  (NULL, 'auditoria',    'Auditoría',              'Log de cambios y trazabilidad',                      'activity',       '/auditoria',             140),
  (NULL, 'usuarios',     'Usuarios',               'Gestión de usuarios del sistema',                    'user-cog',       '/usuarios',              150),
  (NULL, 'roles',        'Roles y Permisos',       'Gestión de roles, módulos y permisos',               'shield',         '/roles',                 160);


-- ============================================================
-- Submódulos (hijos de los módulos raíz)
-- Todos comparten el mismo padre_id del módulo raíz correspondiente.
-- ============================================================

-- Submódulos de Clientes
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'clientes.naturales',  'Personas Naturales',  'user',          '/clientes/naturales',  21 FROM modulos WHERE codigo='clientes' UNION ALL
  SELECT id, 'clientes.juridicas',  'Personas Jurídicas',  'building',      '/clientes/juridicas',  22 FROM modulos WHERE codigo='clientes' UNION ALL
  SELECT id, 'clientes.contactos',  'Contactos',           'phone',         '/clientes/contactos',  23 FROM modulos WHERE codigo='clientes' UNION ALL
  SELECT id, 'clientes.direcciones','Direcciones',         'map-pin',       '/clientes/direcciones',24 FROM modulos WHERE codigo='clientes' UNION ALL
  SELECT id, 'clientes.actividades','Actividades CIIU',    'briefcase',     '/clientes/actividades',25 FROM modulos WHERE codigo='clientes' UNION ALL
  SELECT id, 'clientes.regimenes',  'Historial Regímenes', 'refresh-cw',    '/clientes/regimenes',  26 FROM modulos WHERE codigo='clientes';

-- Submódulos de Servicios
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'servicios.tipos',       'Tipos de Servicio',     'list',       '/servicios/tipos',       31 FROM modulos WHERE codigo='servicios' UNION ALL
  SELECT id, 'servicios.contratados', 'Servicios Contratados', 'check',      '/servicios/contratados', 32 FROM modulos WHERE codigo='servicios' UNION ALL
  SELECT id, 'servicios.asignar',     'Asignar a Cliente',     'user-plus',  '/servicios/asignar',     33 FROM modulos WHERE codigo='servicios';

-- Submódulos de Tarifas
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'tarifas.mensuales','Tarifas Mensuales', 'calendar',  '/tarifas/mensuales', 41 FROM modulos WHERE codigo='tarifas' UNION ALL
  SELECT id, 'tarifas.anuales',  'Tarifas Anuales',   'calendar',  '/tarifas/anuales',   42 FROM modulos WHERE codigo='tarifas' UNION ALL
  SELECT id, 'tarifas.vigentes', 'Tarifas Vigentes',  'check',     '/tarifas/vigentes',  43 FROM modulos WHERE codigo='tarifas';

-- Submódulos de Comprobantes
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'comprobantes.facturas',     'Facturas',                 'file-text',  '/comprobantes/facturas',     51 FROM modulos WHERE codigo='comprobantes' UNION ALL
  SELECT id, 'comprobantes.boletas',      'Boletas de Venta',         'file-text',  '/comprobantes/boletas',      52 FROM modulos WHERE codigo='comprobantes' UNION ALL
  SELECT id, 'comprobantes.notas_credito','Notas de Crédito',         'rotate-ccw', '/comprobantes/notas-credito',53 FROM modulos WHERE codigo='comprobantes' UNION ALL
  SELECT id, 'comprobantes.notas_debito', 'Notas de Débito',          'rotate-cw',  '/comprobantes/notas-debito', 54 FROM modulos WHERE codigo='comprobantes' UNION ALL
  SELECT id, 'comprobantes.recibos',      'Recibos por Honorarios',   'file-text',  '/comprobantes/recibos',      55 FROM modulos WHERE codigo='comprobantes';

-- Submódulos de Cobros y Pagos
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'cobros.mensuales','Cobros Mensuales',  'credit-card','/cobros/mensuales', 61 FROM modulos WHERE codigo='cobros' UNION ALL
  SELECT id, 'cobros.pagos',    'Pagos Aplicados',   'dollar-sign','/cobros/pagos',     62 FROM modulos WHERE codigo='cobros' UNION ALL
  SELECT id, 'cobros.morosidad','Reporte Morosidad', 'alert-triangle','/cobros/morosidad',63 FROM modulos WHERE codigo='cobros';

-- Submódulos de Contratos
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'contratos.firmados','Contratos Firmados',  'file-signature','/contratos/firmados',71 FROM modulos WHERE codigo='contratos' UNION ALL
  SELECT id, 'contratos.vigentes','Contratos Vigentes',  'check',         '/contratos/vigentes',72 FROM modulos WHERE codigo='contratos' UNION ALL
  SELECT id, 'contratos.vencidos','Contratos Vencidos',  'alert-circle',  '/contratos/vencidos',73 FROM modulos WHERE codigo='contratos';

-- Submódulos de Documentos
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'documentos.archivos','Archivos del Cliente', 'folder-open', '/documentos/archivos', 81 FROM modulos WHERE codigo='documentos' UNION ALL
  SELECT id, 'documentos.vencidos','Documentos Vencidos',  'alert-circle','/documentos/vencidos',82 FROM modulos WHERE codigo='documentos';

-- Submódulos de Períodos Contables
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'periodos.apertura','Apertura de Período', 'play-circle', '/periodos/apertura',91 FROM modulos WHERE codigo='periodos' UNION ALL
  SELECT id, 'periodos.cierre',  'Cierre Mensual',      'lock',        '/periodos/cierre',  92 FROM modulos WHERE codigo='periodos' UNION ALL
  SELECT id, 'periodos.ple',     'Presentación PLE',    'upload',      '/periodos/ple',     93 FROM modulos WHERE codigo='periodos' UNION ALL
  SELECT id, 'periodos.pdt',     'Presentación PDT',    'upload',      '/periodos/pdt',     94 FROM modulos WHERE codigo='periodos';

-- Submódulos de Notas / Bitácora
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'notas.generales',  'Notas Generales',  'message-square','/notas/generales',  101 FROM modulos WHERE codigo='notas' UNION ALL
  SELECT id, 'notas.cobranza',   'Notas de Cobranza','phone',         '/notas/cobranza',   102 FROM modulos WHERE codigo='notas' UNION ALL
  SELECT id, 'notas.tributaria', 'Notas Tributarias','file-text',     '/notas/tributaria', 103 FROM modulos WHERE codigo='notas' UNION ALL
  SELECT id, 'notas.laboral',    'Notas Laborales',  'users',         '/notas/laboral',    104 FROM modulos WHERE codigo='notas';

-- Submódulos de Tareas
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'tareas.mias',      'Mis Tareas',        'user',          '/tareas/mias',      111 FROM modulos WHERE codigo='tareas' UNION ALL
  SELECT id, 'tareas.asignadas', 'Asignadas a Mí',    'inbox',         '/tareas/asignadas', 112 FROM modulos WHERE codigo='tareas' UNION ALL
  SELECT id, 'tareas.creadas',   'Creadas por Mí',    'edit-3',        '/tareas/creadas',   113 FROM modulos WHERE codigo='tareas';

-- Submódulos de Alertas
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'alertas.vencimiento_contrato',  'Vencimiento de Contratos',  'file-signature','/alertas/vencimiento-contrato',  121 FROM modulos WHERE codigo='alertas' UNION ALL
  SELECT id, 'alertas.vencimiento_documento','Vencimiento de Documentos', 'file',          '/alertas/vencimiento-documento',122 FROM modulos WHERE codigo='alertas' UNION ALL
  SELECT id, 'alertas.cobro_vencido',         'Cobros Vencidos',           'credit-card',   '/alertas/cobro-vencido',         123 FROM modulos WHERE codigo='alertas' UNION ALL
  SELECT id, 'alertas.declaracion',           'Declaraciones Próximas',    'upload',        '/alertas/declaracion',           124 FROM modulos WHERE codigo='alertas' UNION ALL
  SELECT id, 'alertas.cumpleanos',            'Cumpleaños',                'gift',          '/alertas/cumpleanos',            125 FROM modulos WHERE codigo='alertas';

-- Submódulos de Reportes
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'reportes.facturacion','Facturación Anual',     'bar-chart','/reportes/facturacion',131 FROM modulos WHERE codigo='reportes' UNION ALL
  SELECT id, 'reportes.cobranza',   'Cobranza Mensual',      'line-chart','/reportes/cobranza',   132 FROM modulos WHERE codigo='reportes' UNION ALL
  SELECT id, 'reportes.morosidad',  'Morosidad por Cliente', 'alert-triangle','/reportes/morosidad',133 FROM modulos WHERE codigo='reportes' UNION ALL
  SELECT id, 'reportes.regimenes',  'Historial de Regímenes','refresh-cw','/reportes/regimenes',  134 FROM modulos WHERE codigo='reportes';

-- Submódulos de Usuarios
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'usuarios.listado','Listado de Usuarios', 'list',   '/usuarios',         151 FROM modulos WHERE codigo='usuarios' UNION ALL
  SELECT id, 'usuarios.rol',    'Asignar Rol',         'user-check','/usuarios/rol',  152 FROM modulos WHERE codigo='usuarios';

-- Submódulos de Roles y Permisos
INSERT INTO modulos (padre_id, codigo, nombre, icono, ruta, orden)
  SELECT id, 'roles.listado', 'Listado de Roles',  'list',    '/roles',          161 FROM modulos WHERE codigo='roles' UNION ALL
  SELECT id, 'roles.permisos','Permisos por Módulo','shield', '/roles/permisos', 162 FROM modulos WHERE codigo='roles';

-- Catálogo de acciones / permisos
INSERT INTO permisos (codigo, nombre, descripcion) VALUES
  ('leer',       'Leer',         'Consultar y visualizar registros'),
  ('crear',      'Crear',        'Crear nuevos registros'),
  ('editar',     'Editar',       'Modificar registros existentes'),
  ('eliminar',   'Eliminar',     'Borrar o desactivar registros'),
  ('exportar',   'Exportar',     'Descargar datos a Excel, PDF, CSV'),
  ('importar',   'Importar',     'Cargar datos desde archivos externos'),
  ('aprobar',    'Aprobar',      'Aprobar procesos (cierres, declaraciones)'),
  ('anular',     'Anular',       'Anular comprobantes, cobros o pagos'),
  ('cerrar',     'Cerrar',       'Cerrar períodos o procesos'),
  ('reimprimir', 'Reimprimir',   'Reimprimir comprobantes emitidos');


-- ------------------------------------------------------------
-- Asignación de módulos por rol
-- (roles según SECCIÓN 1.1: 1=ADMIN, 2=SOCIO, 3=CONTADOR,
--                           4=ASISTENTE, 5=PRACT)
-- ------------------------------------------------------------

-- ADMIN: todos los módulos
INSERT INTO roles_modulos (rol_id, modulo_id)
  SELECT 1, id FROM modulos;

-- SOCIO: gestión operativa + reportes, sin administración de usuarios/roles
INSERT INTO roles_modulos (rol_id, modulo_id) VALUES
  (2, (SELECT id FROM modulos WHERE codigo='dashboard')),
  (2, (SELECT id FROM modulos WHERE codigo='clientes')),
  (2, (SELECT id FROM modulos WHERE codigo='servicios')),
  (2, (SELECT id FROM modulos WHERE codigo='tarifas')),
  (2, (SELECT id FROM modulos WHERE codigo='comprobantes')),
  (2, (SELECT id FROM modulos WHERE codigo='cobros')),
  (2, (SELECT id FROM modulos WHERE codigo='contratos')),
  (2, (SELECT id FROM modulos WHERE codigo='documentos')),
  (2, (SELECT id FROM modulos WHERE codigo='periodos')),
  (2, (SELECT id FROM modulos WHERE codigo='notas')),
  (2, (SELECT id FROM modulos WHERE codigo='tareas')),
  (2, (SELECT id FROM modulos WHERE codigo='alertas')),
  (2, (SELECT id FROM modulos WHERE codigo='reportes')),
  (2, (SELECT id FROM modulos WHERE codigo='auditoria'));

-- CONTADOR: operación contable completa, sin gestión de usuarios
INSERT INTO roles_modulos (rol_id, modulo_id) VALUES
  (3, (SELECT id FROM modulos WHERE codigo='dashboard')),
  (3, (SELECT id FROM modulos WHERE codigo='clientes')),
  (3, (SELECT id FROM modulos WHERE codigo='servicios')),
  (3, (SELECT id FROM modulos WHERE codigo='tarifas')),
  (3, (SELECT id FROM modulos WHERE codigo='comprobantes')),
  (3, (SELECT id FROM modulos WHERE codigo='cobros')),
  (3, (SELECT id FROM modulos WHERE codigo='contratos')),
  (3, (SELECT id FROM modulos WHERE codigo='documentos')),
  (3, (SELECT id FROM modulos WHERE codigo='periodos')),
  (3, (SELECT id FROM modulos WHERE codigo='notas')),
  (3, (SELECT id FROM modulos WHERE codigo='tareas')),
  (3, (SELECT id FROM modulos WHERE codigo='alertas')),
  (3, (SELECT id FROM modulos WHERE codigo='reportes'));

-- ASISTENTE: operación diaria, sin períodos ni auditoría
INSERT INTO roles_modulos (rol_id, modulo_id) VALUES
  (4, (SELECT id FROM modulos WHERE codigo='dashboard')),
  (4, (SELECT id FROM modulos WHERE codigo='clientes')),
  (4, (SELECT id FROM modulos WHERE codigo='servicios')),
  (4, (SELECT id FROM modulos WHERE codigo='tarifas')),
  (4, (SELECT id FROM modulos WHERE codigo='comprobantes')),
  (4, (SELECT id FROM modulos WHERE codigo='cobros')),
  (4, (SELECT id FROM modulos WHERE codigo='contratos')),
  (4, (SELECT id FROM modulos WHERE codigo='documentos')),
  (4, (SELECT id FROM modulos WHERE codigo='notas')),
  (4, (SELECT id FROM modulos WHERE codigo='tareas')),
  (4, (SELECT id FROM modulos WHERE codigo='alertas'));

-- PRACTICANTE: solo consulta en módulos básicos
INSERT INTO roles_modulos (rol_id, modulo_id) VALUES
  (5, (SELECT id FROM modulos WHERE codigo='dashboard')),
  (5, (SELECT id FROM modulos WHERE codigo='clientes')),
  (5, (SELECT id FROM modulos WHERE codigo='tareas')),
  (5, (SELECT id FROM modulos WHERE codigo='notas'));


-- ------------------------------------------------------------
-- Permisos por (rol, módulo)
-- (permisos según catálogo: 1=leer, 2=crear, 3=editar, 4=eliminar,
--   5=exportar, 6=importar, 7=aprobar, 8=anular, 9=cerrar, 10=reimprimir)
-- ------------------------------------------------------------

-- ADMIN: todos los permisos en todos sus módulos
INSERT INTO roles_modulos_permisos (rol_id, modulo_id, permiso_id)
  SELECT rm.rol_id, rm.modulo_id, p.id
  FROM roles_modulos rm
  CROSS JOIN permisos p
  WHERE rm.rol_id = 1 AND p.activo = 1;

-- SOCIO: todo excepto importar (lo reservamos para admin)
INSERT INTO roles_modulos_permisos (rol_id, modulo_id, permiso_id)
  SELECT rm.rol_id, rm.modulo_id, p.id
  FROM roles_modulos rm
  JOIN permisos p ON p.activo = 1
  WHERE rm.rol_id = 2 AND p.codigo <> 'importar';

-- CONTADOR: operativo completo (incluye cerrar períodos), sin importar ni eliminar auditoría
INSERT INTO roles_modulos_permisos (rol_id, modulo_id, permiso_id)
  SELECT rm.rol_id, rm.modulo_id, p.id
  FROM roles_modulos rm
  JOIN permisos p ON p.activo = 1
  WHERE rm.rol_id = 3
    AND p.codigo <> 'importar'
    AND NOT (rm.modulo_id = (SELECT id FROM modulos WHERE codigo='auditoria') AND p.codigo = 'eliminar');

-- ASISTENTE: leer, crear, editar, exportar, reimprimir (sin eliminar, importar, aprobar, anular, cerrar)
INSERT INTO roles_modulos_permisos (rol_id, modulo_id, permiso_id)
  SELECT rm.rol_id, rm.modulo_id, p.id
  FROM roles_modulos rm
  JOIN permisos p ON p.activo = 1
  WHERE rm.rol_id = 4
    AND p.codigo IN ('leer','crear','editar','exportar','reimprimir');

-- PRACTICANTE: solo lectura
INSERT INTO roles_modulos_permisos (rol_id, modulo_id, permiso_id)
  SELECT rm.rol_id, rm.modulo_id, p.id
  FROM roles_modulos rm
  JOIN permisos p ON p.activo = 1 AND p.codigo = 'leer'
  WHERE rm.rol_id = 5;


-- Vista útil: jerarquía de módulos (padres con sus hijos) para construir el menú
CREATE OR REPLACE VIEW v_modulos_jerarquia AS
SELECT
  p.id            AS padre_id,
  p.codigo        AS padre_codigo,
  p.nombre        AS padre_nombre,
  p.icono         AS padre_icono,
  p.ruta          AS padre_ruta,
  p.orden         AS padre_orden,
  h.id            AS hijo_id,
  h.codigo        AS hijo_codigo,
  h.nombre        AS hijo_nombre,
  h.icono         AS hijo_icono,
  h.ruta          AS hijo_ruta,
  h.orden         AS hijo_orden
FROM modulos p
LEFT JOIN modulos h ON h.padre_id = p.id AND h.activo = 1
WHERE p.padre_id IS NULL AND p.activo = 1
ORDER BY p.orden, h.orden;

-- Vista útil: permisos efectivos por rol (para chequear en la app)
CREATE OR REPLACE VIEW v_rol_permisos AS
SELECT
  r.id   AS rol_id,
  r.codigo AS rol_codigo,
  r.nombre AS rol_nombre,
  m.codigo AS modulo_codigo,
  m.nombre AS modulo_nombre,
  p.codigo AS permiso_codigo,
  p.nombre AS permiso_nombre
FROM roles r
JOIN roles_modulos           rm ON rm.rol_id    = r.id
JOIN modulos                 m  ON m.id         = rm.modulo_id
JOIN roles_modulos_permisos  rmp ON rmp.rol_id  = rm.rol_id AND rmp.modulo_id = rm.modulo_id
JOIN permisos                p  ON p.id         = rmp.permiso_id
WHERE r.activo = 1 AND m.activo = 1 AND p.activo = 1;


-- ============================================================
-- RESUMEN FINAL
-- ============================================================
SELECT 'Base de datos yupana_db creada correctamente' AS mensaje;
SELECT 'Total de tablas:' AS info, COUNT(*) AS total
  FROM information_schema.tables
  WHERE table_schema = 'yupana_db';
