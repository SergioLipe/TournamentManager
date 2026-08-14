-- ---------------------------------------------------------------------------
-- Tournament — esquema para uma instalação nova.
--
-- Para actualizar uma base de dados que já existe, usar migration.sql.
--
-- Diferenças face ao esquema original:
--   * InnoDB em vez de MyISAM, para haver transacções e chaves estrangeiras;
--   * utf8mb4 em vez de latin1, que era a origem dos nomes com "Ã ";
--   * coluna `publico`, que substitui o `utilizadorId = 2` fixo no código;
--   * `password` guarda um hash, nunca a password em texto.
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `competidor`;
DROP TABLE IF EXISTS `tema`;
DROP TABLE IF EXISTS `utilizador`;

CREATE TABLE `utilizador` (
    `id`       INT(11)      NOT NULL AUTO_INCREMENT,
    -- 255 caracteres: o password_hash() devolve 60 com bcrypt, mas os
    -- algoritmos mais recentes ocupam mais.
    `username` VARCHAR(30)  NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `criadoEm` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `tema` (
    `id`           INT(11)     NOT NULL AUTO_INCREMENT,
    `nome`         VARCHAR(25) NOT NULL,
    `utilizadorId` INT(11)     NOT NULL,
    -- 1 = visível para toda a gente, 0 = só para o dono.
    `publico`      TINYINT(1)  NOT NULL DEFAULT 0,
    `criadoEm`     TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tema_por_utilizador` (`utilizadorId`, `nome`),
    KEY `publico` (`publico`),
    CONSTRAINT `fk_tema_utilizador`
        FOREIGN KEY (`utilizadorId`) REFERENCES `utilizador` (`id`)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `competidor` (
    `id`                INT(11)      NOT NULL AUTO_INCREMENT,
    `nome`              VARCHAR(50)  NOT NULL,
    `imagem`            VARCHAR(255) DEFAULT NULL,
    `TemaId`            INT(11)      NOT NULL,
    `nBatalhasVencidas` INT(11)      NOT NULL DEFAULT 0,
    `nBatalhasPerdidas` INT(11)      NOT NULL DEFAULT 0,
    `nTorneiosVencidos` INT(11)      NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `TemaId` (`TemaId`),
    CONSTRAINT `fk_competidor_tema`
        FOREIGN KEY (`TemaId`) REFERENCES `tema` (`id`)
        ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Não se cria aqui nenhum utilizador: um hash de password não se escreve à
-- mão num ficheiro .sql. Depois de correr este esquema:
--
--   php database/criar-admin.php admin
--
-- e esse comando cria a conta e marca os temas públicos.
-- ---------------------------------------------------------------------------
