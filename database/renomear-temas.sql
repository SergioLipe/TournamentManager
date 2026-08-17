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

-- 'Food' virou dois temas: os pratos internacionais que já lá estavam ficam
-- com o tema (e o id, e as estatísticas), 'Portuguese Food' nasce de novo no
-- seed a seguir com as receitas portuguesas que saíram de 'Food'.
UPDATE IGNORE tema SET nome = 'International Food' WHERE utilizadorId = @dono AND nome = 'Food';

-- 'Portuguese Food' passou a conter também todos os pratos internacionais,
-- por isso já não é só português — o nome muda para reflectir isso. Sem
-- efeito se este ficheiro corre antes de o tema alguma vez ter existido.
UPDATE IGNORE tema SET nome = 'Food in Portugal' WHERE utilizadorId = @dono AND nome = 'Portuguese Food';

-- 'Portuguese Landmarks' foi removido a pedido. Isto só apaga alguma coisa se
-- o seed anterior já tiver corrido numa base de dados; se nunca chegou a
-- correr, as duas linhas seguintes não encontram nada e não fazem nada.
DELETE competidor FROM competidor
    JOIN tema ON tema.id = competidor.TemaId
    WHERE tema.utilizadorId = @dono AND tema.nome = 'Portuguese Landmarks';
DELETE FROM tema WHERE utilizadorId = @dono AND nome = 'Portuguese Landmarks';

SELECT id, nome, publico FROM tema WHERE utilizadorId = @dono ORDER BY nome;
