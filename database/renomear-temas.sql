-- ---------------------------------------------------------------------------
-- Passa os nomes dos temas antigos para inglês.
--
-- Correr UMA VEZ, ANTES do seed-temas-publicos.sql, em qualquer base de dados
-- que já tenha os temas com os nomes antigos.
--
-- Porquê um ficheiro à parte: o tema é identificado pelo par
-- (utilizadorId, nome), que é a chave única da tabela. Se o seed corresse com
-- os nomes novos sem este passo, o INSERT ... ON DUPLICATE KEY não encontrava
-- o tema 'Filmes' e criava um tema 'Movies' novo, com id novo — os antigos
-- ficavam lá pendurados e os links das estatísticas apontavam para o tema
-- vazio. Renomear primeiro mantém o id, e com ele o histórico de batalhas.
--
-- O UPDATE IGNORE é de propósito: se já existir um tema com o nome novo,
-- a linha é saltada em vez de rebentar com a chave única, e o ficheiro pode
-- correr-se as vezes que forem precisas.
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;

SET @dono = (SELECT id FROM utilizador WHERE username = 'admin');

UPDATE IGNORE tema SET nome = 'Movies'      WHERE utilizadorId = @dono AND nome = 'Filmes';
UPDATE IGNORE tema SET nome = 'Rock Bands'  WHERE utilizadorId = @dono AND nome = 'Bandas de Rock';
UPDATE IGNORE tema SET nome = 'Video Games' WHERE utilizadorId = @dono AND nome = 'VideoGames';

-- 'Food' já estava em inglês e fica como está.

SELECT id, nome, publico FROM tema WHERE utilizadorId = @dono ORDER BY nome;
