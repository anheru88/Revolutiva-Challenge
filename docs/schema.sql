-- =============================================================================
-- PayIn Platform — Esquema relacional de referencia (MySQL 8+)
--
-- La fuente de verdad son las migraciones de Laravel en backend/database/migrations.
-- Este script se ofrece como entregable/documentación (ver PRD §15).
-- Importes en la unidad menor de la moneda (entero). UUID como id público.
-- =============================================================================

CREATE TABLE customers (
    id    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid  CHAR(36)     NOT NULL,
    name  VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY customers_uuid_unique (uuid)
) ENGINE = InnoDB;

CREATE TABLE accounts (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid           CHAR(36)     NOT NULL,
    customer_id    BIGINT UNSIGNED NOT NULL,
    account_number VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY accounts_uuid_unique (uuid),
    KEY accounts_customer_id_index (customer_id),
    CONSTRAINT accounts_customer_id_foreign
        FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE payment_methods (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid       CHAR(36)     NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    type       VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY payment_methods_uuid_unique (uuid),
    KEY payment_methods_account_id_index (account_id),
    CONSTRAINT payment_methods_account_id_foreign
        FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE payment_providers (
    id   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY payment_providers_code_unique (code)
) ENGINE = InnoDB;

CREATE TABLE pay_ins (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid                CHAR(36)     NOT NULL,
    customer_id         BIGINT UNSIGNED NOT NULL,
    account_id          BIGINT UNSIGNED NOT NULL,
    payment_method_id   BIGINT UNSIGNED NOT NULL,
    payment_provider_id BIGINT UNSIGNED NOT NULL,
    amount              BIGINT UNSIGNED NOT NULL,
    currency            CHAR(3)      NOT NULL,
    status              VARCHAR(255) NOT NULL,
    provider_request    JSON         NULL,
    provider_response   JSON         NULL,
    created_at          TIMESTAMP    NULL,
    updated_at          TIMESTAMP    NULL,
    PRIMARY KEY (id),
    UNIQUE KEY pay_ins_uuid_unique (uuid),
    KEY pay_ins_status_index (status),
    CONSTRAINT pay_ins_customer_id_foreign         FOREIGN KEY (customer_id)         REFERENCES customers (id),
    CONSTRAINT pay_ins_account_id_foreign          FOREIGN KEY (account_id)          REFERENCES accounts (id),
    CONSTRAINT pay_ins_payment_method_id_foreign   FOREIGN KEY (payment_method_id)   REFERENCES payment_methods (id),
    CONSTRAINT pay_ins_payment_provider_id_foreign FOREIGN KEY (payment_provider_id) REFERENCES payment_providers (id)
) ENGINE = InnoDB;

CREATE TABLE pay_in_status_history (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    pay_in_id       BIGINT UNSIGNED NOT NULL,
    previous_status VARCHAR(255) NULL,
    current_status  VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP    NULL,
    PRIMARY KEY (id),
    KEY pay_in_status_history_pay_in_id_index (pay_in_id),
    CONSTRAINT pay_in_status_history_pay_in_id_foreign
        FOREIGN KEY (pay_in_id) REFERENCES pay_ins (id) ON DELETE CASCADE
) ENGINE = InnoDB;
