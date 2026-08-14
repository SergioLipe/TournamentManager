-- ---------------------------------------------------------------------------
-- Tournament — actualização de uma base de dados existente.
--
--   >>> FAZER BACKUP ANTES DE CORRER ISTO. <<<
--       mysqldump -u utilizador -p nome_da_bd > backup.sql
--
-- Só correr uma vez. O passo 2 reinterpreta bytes: aplicado duas vezes
-- estraga os acentos em vez de os corrigir.
--
-- As passwords não são tocadas aqui — ficam em texto simples até cada
-- utilizador entrar uma vez, momento em que o Login.php as converte em hash
-- automaticamente.
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- 1. Limpar linhas órfãs
--
-- O dump tinha competidores com TemaId 1 e 6, para temas que já não existem
-- (a antiga pasta "Comida Portuguesa" e "GUELGAY"). Sem os apagar, a chave
-- estrangeira do passo 5 não pode ser criada.
-- ---------------------------------------------------------------------------

DELETE c FROM `competidor` c
    LEFT JOIN `tema` t ON t.`id` = c.`TemaId`
    WHERE t.`id` IS NULL;

DELETE t FROM `tema` t
    LEFT JOIN `utilizador` u ON u.`id` = t.`utilizadorId`
    WHERE t.`utilizadorId` IS NULL OR u.`id` IS NULL;

COMMIT;

-- ---------------------------------------------------------------------------
-- 2. Corrigir a codificação dos textos
--
-- As colunas eram latin1 mas continham bytes utf8 — daí "Bacalhau Ã  Lagareiro"
-- em vez de "Bacalhau à Lagareiro". Passar por VARBINARY preserva os bytes e
-- volta a lê-los como utf8; um CONVERT TO CHARACTER SET faria o contrário,
-- transcodificando-os e duplicando o problema.
-- ---------------------------------------------------------------------------

ALTER TABLE `competidor`
    MODIFY `nome`   VARBINARY(50),
    MODIFY `imagem` VARBINARY(255);

ALTER TABLE `competidor`
    MODIFY `nome`   VARCHAR(50)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    MODIFY `imagem` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;

ALTER TABLE `tema`
    MODIFY `nome` VARBINARY(25);

ALTER TABLE `tema`
    MODIFY `nome` VARCHAR(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `utilizador`
    MODIFY `username` VARBINARY(30),
    MODIFY `password` VARBINARY(255);

ALTER TABLE `utilizador`
    MODIFY `username` VARCHAR(30)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    MODIFY `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

-- ---------------------------------------------------------------------------
-- 3. Charset por omissão das tabelas e motor InnoDB
--
-- O MyISAM não tem transacções nem chaves estrangeiras: o api/resultado.php
-- precisa das primeiras e os passos seguintes das segundas.
-- ---------------------------------------------------------------------------

ALTER TABLE `utilizador` ENGINE = InnoDB, DEFAULT CHARSET = utf8mb4, COLLATE = utf8mb4_unicode_ci;
ALTER TABLE `tema`       ENGINE = InnoDB, DEFAULT CHARSET = utf8mb4, COLLATE = utf8mb4_unicode_ci;
ALTER TABLE `competidor` ENGINE = InnoDB, DEFAULT CHARSET = utf8mb4, COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 4. Coluna `publico`
--
-- Substitui o "utilizadorId = 2" que estava escrito à mão em várias queries.
-- ---------------------------------------------------------------------------

ALTER TABLE `tema`
    ADD COLUMN `publico` TINYINT(1) NOT NULL DEFAULT 0 AFTER `utilizadorId`,
    ADD COLUMN `criadoEm` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- O utilizador 2 ("admin") era o dono dos temas de base.
UPDATE `tema` SET `publico` = 1 WHERE `utilizadorId` = 2;

ALTER TABLE `utilizador`
    ADD COLUMN `criadoEm` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- ---------------------------------------------------------------------------
-- 5. Caminhos de imagem que não correspondem a nenhum ficheiro
--
-- A base de dados aponta para "Imagens/Food/Rojões.jfif" mas o ficheiro no
-- repositório chama-se "rojoes.jfif". Em Windows isto passava despercebido;
-- num servidor Linux, onde maiúsculas e acentos contam, a imagem dá 404.
--
-- Tem de vir depois do passo 2: só com a codificação já corrigida é que o
-- valor guardado é mesmo "Rojões.jfif" e não "RojÃµes.jfif".
-- ---------------------------------------------------------------------------

UPDATE `competidor`
    SET `imagem` = 'Imagens/Food/rojoes.jfif'
    WHERE `imagem` LIKE 'Imagens/Food/Roj%.jfif';

-- ---------------------------------------------------------------------------
-- 6. Restrições de integridade
-- ---------------------------------------------------------------------------

ALTER TABLE `tema`
    MODIFY `utilizadorId` INT(11) NOT NULL,
    ADD UNIQUE KEY `tema_por_utilizador` (`utilizadorId`, `nome`),
    ADD KEY `publico` (`publico`),
    ADD CONSTRAINT `fk_tema_utilizador`
        FOREIGN KEY (`utilizadorId`) REFERENCES `utilizador` (`id`)
        ON DELETE CASCADE;

ALTER TABLE `competidor`
    MODIFY `TemaId` INT(11) NOT NULL,
    MODIFY `nBatalhasVencidas` INT(11) NOT NULL DEFAULT 0,
    MODIFY `nBatalhasPerdidas` INT(11) NOT NULL DEFAULT 0,
    MODIFY `nTorneiosVencidos` INT(11) NOT NULL DEFAULT 0,
    ADD CONSTRAINT `fk_competidor_tema`
        FOREIGN KEY (`TemaId`) REFERENCES `tema` (`id`)
        ON DELETE CASCADE;

-- ---------------------------------------------------------------------------
-- 7. Verificação
--
-- Correr a seguir para confirmar que os acentos ficaram bem:
--     SELECT nome FROM competidor WHERE nome LIKE '%Bacalhau%';
-- Deve mostrar "Bacalhau à Lagareiro" e não "Bacalhau Ã  Lagareiro".
-- ---------------------------------------------------------------------------
