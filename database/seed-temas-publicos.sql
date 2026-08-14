-- ---------------------------------------------------------------------------
-- Temas públicos e respectivos competidores.
--
-- Correr DEPOIS do schema.sql, numa base de dados que já tenha uma conta de
-- utilizador criada (ver database/criar-admin.php). Substituir 'admin' abaixo
-- pelo teu username, se for outro.
--
-- Os caminhos correspondem aos ficheiros publicados em Imagens/. Os nomes a
-- mostrar mantêm os acentos originais, mesmo que o ficheiro em disco seja só
-- ASCII: o servidor de FTP deste alojamento recusa nomes com espaços ou
-- acentos, mas a base de dados não tem esse problema.
--
-- Pode correr-se as vezes que forem precisas. Os temas que já existirem são
-- reaproveitados — mantêm o id, e portanto os links para as estatísticas
-- continuam válidos — e só os competidores são substituídos.
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;

SET @dono = (SELECT id FROM utilizador WHERE username = 'admin');

-- ---------- Food ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Food', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Food' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Arroz de cabidelas', 'Imagens/Food/Arroz-de-cabidelas.jfif', @t),
    ('Bacalhau à Lagareiro', 'Imagens/Food/Bacalhau-a-Lagareiro.jpg', @t),
    ('Bolonhesa', 'Imagens/Food/Bolonhesa.webp', @t),
    ('Carbonara', 'Imagens/Food/Carbonara.jpg', @t),
    ('Dobrada', 'Imagens/Food/Dobrada.jpg', @t),
    ('Frango Piri-Piri', 'Imagens/Food/Frango-Piri-Piri.jfif', @t),
    ('Kebab', 'Imagens/Food/Kebab.jpg', @t),
    ('Pad Thai', 'Imagens/Food/Pad-Thai.webp', @t),
    ('Paella de marisco', 'Imagens/Food/Paella-de-marisco.jpeg', @t),
    ('Ramen', 'Imagens/Food/Ramen.jpg', @t),
    ('Sardinhas', 'Imagens/Food/Sardinhas.jpg', @t),
    ('Tacos', 'Imagens/Food/Tacos.jpg', @t),
    ('Tripas a modo do Porto', 'Imagens/Food/Tripas-a-modo-do-Porto.jfif', @t),
    ('alheira', 'Imagens/Food/alheira.jpg', @t),
    ('arroz_pato', 'Imagens/Food/arroz_pato.jpg', @t),
    ('bacalhau_braz', 'Imagens/Food/bacalhau_braz.jpg', @t),
    ('bife', 'Imagens/Food/bife.jpg', @t),
    ('carne de porco à alentejana', 'Imagens/Food/carne-de-porco-a-alentejana.jpg', @t),
    ('cheeseburger', 'Imagens/Food/cheeseburger.jpg', @t),
    ('cozido', 'Imagens/Food/cozido.jpg', @t),
    ('feijoada_transmontana', 'Imagens/Food/feijoada_transmontana.jpg', @t),
    ('francesinha', 'Imagens/Food/francesinha.jpg', @t),
    ('lasanha', 'Imagens/Food/lasanha.jpg', @t),
    ('polvo_Lagareiro', 'Imagens/Food/polvo_Lagareiro.jpg', @t),
    ('Rojões', 'Imagens/Food/rojoes.jfif', @t),
    ('sushi', 'Imagens/Food/sushi.jpg', @t);

-- ---------- Filmes ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Filmes', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Filmes' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('300', 'Imagens/Filmes/300.jpeg', @t),
    ('Avatar', 'Imagens/Filmes/Avatar.jpeg', @t),
    ('Book of Eli', 'Imagens/Filmes/Book-of-Eli.jpeg', @t),
    ('Da Vinci Code', 'Imagens/Filmes/Da-Vinci-Code.jpeg', @t),
    ('Django Unchained', 'Imagens/Filmes/Django-Unchained.jpeg', @t),
    ('Fight Club', 'Imagens/Filmes/Fight-Club.jpeg', @t),
    ('Forest Gump', 'Imagens/Filmes/Forest-Gump.jpeg', @t),
    ('Gladiador', 'Imagens/Filmes/Gladiador.jpeg', @t),
    ('Good Will Hunting', 'Imagens/Filmes/Good-Will-Hunting.jpeg', @t),
    ('Hangover', 'Imagens/Filmes/Hangover.jpeg', @t),
    ('Inception', 'Imagens/Filmes/Inception.jpeg', @t),
    ('Inglorious Basterds', 'Imagens/Filmes/Inglorious-Basterds.jpeg', @t),
    ('Matrix', 'Imagens/Filmes/Matrix.jpeg', @t),
    ('Memento', 'Imagens/Filmes/Memento.jpeg', @t),
    ('No Country fol Old men', 'Imagens/Filmes/No-Country-fol-Old-men.jpeg', @t),
    ('Prometheus', 'Imagens/Filmes/Prometheus.jpeg', @t),
    ('Pulp Fiction', 'Imagens/Filmes/Pulp-Fiction.jpeg', @t),
    ('Saving Private Ryan', 'Imagens/Filmes/Saving-Private-Ryan.jpeg', @t),
    ('Shutter Island', 'Imagens/Filmes/Shutter-Island.jpeg', @t),
    ('The Dark Knight', 'Imagens/Filmes/The-Dark-Knight.jpeg', @t);

-- ---------- Bandas de Rock ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Bandas de Rock', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Bandas de Rock' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('AC DC', 'Imagens/Bandas-de-Rock/AC-DC.jpg', @t),
    ('Aerosmith', 'Imagens/Bandas-de-Rock/Aerosmith.jpg', @t),
    ('Black Sabbath', 'Imagens/Bandas-de-Rock/Black-Sabbath.jpg', @t),
    ('Dire Straits', 'Imagens/Bandas-de-Rock/Dire-Straits.jpg', @t),
    ('Fleetwood Mac', 'Imagens/Bandas-de-Rock/Fleetwood-Mac.webp', @t),
    ('Guns n Roses', 'Imagens/Bandas-de-Rock/Guns-n-Roses.jpg', @t),
    ('Iron Maiden', 'Imagens/Bandas-de-Rock/Iron-Maiden.jpg', @t),
    ('Kiss', 'Imagens/Bandas-de-Rock/Kiss.jpg', @t),
    ('Led Zeppelin', 'Imagens/Bandas-de-Rock/Led-Zeppelin.jpg', @t),
    ('Metallica', 'Imagens/Bandas-de-Rock/Metallica.png', @t),
    ('Nirvana', 'Imagens/Bandas-de-Rock/Nirvana.jpg', @t),
    ('Oasis', 'Imagens/Bandas-de-Rock/Oasis.webp', @t),
    ('Pink Floyd', 'Imagens/Bandas-de-Rock/Pink-Floyd.jpg', @t),
    ('Queen', 'Imagens/Bandas-de-Rock/Queen.jpg', @t),
    ('RadioHead', 'Imagens/Bandas-de-Rock/RadioHead.png', @t),
    ('Rage Against The Machine', 'Imagens/Bandas-de-Rock/Rage-Against-The-Machine.jpg', @t),
    ('The Beatles', 'Imagens/Bandas-de-Rock/The-Beatles.webp', @t),
    ('The Doors', 'Imagens/Bandas-de-Rock/The-Doors.jfif', @t),
    ('The Jimi Hendrix Experience', 'Imagens/Bandas-de-Rock/The-Jimi-Hendrix-Experience.jpg', @t),
    ('The Rolling Stones', 'Imagens/Bandas-de-Rock/The-Rolling-Stones.jpg', @t);

-- ---------- VideoGames ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('VideoGames', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'VideoGames' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Assassin Creed IV Black Flag', 'Imagens/VideoGames/Assassin-Creed-IV-Black-Flag.jpg', @t),
    ('Batman Arkham Trilogy', 'Imagens/VideoGames/Batman-Arkham-Trilogy.jfif', @t),
    ('BioShock Trilogy', 'Imagens/VideoGames/BioShock-Trilogy.jpg', @t),
    ('Borderlands', 'Imagens/VideoGames/Borderlands.jpg', @t),
    ('Call of Duty 4 Modern Warfare', 'Imagens/VideoGames/Call-of-Duty-4-Modern-Warfare.jpg', @t),
    ('Civ V', 'Imagens/VideoGames/Civ-V.jpg', @t),
    ('Counter-Strike 1.6', 'Imagens/VideoGames/Counter-Strike-1.6.jpg', @t),
    ('Dark Souls Trilogy', 'Imagens/VideoGames/Dark-Souls-Trilogy.jpg', @t),
    ('Dishonored', 'Imagens/VideoGames/Dishonored.png', @t),
    ('Fallout NV', 'Imagens/VideoGames/Fallout-NV.jfif', @t),
    ('Final Fantasy X', 'Imagens/VideoGames/Final-Fantasy-X.webp', @t),
    ('GTA San Andreas', 'Imagens/VideoGames/GTA-San-Andreas.jfif', @t),
    ('God of War Reboot', 'Imagens/VideoGames/God-of-War-Reboot.avif', @t),
    ('God of War Trilogy', 'Imagens/VideoGames/God-of-War-Trilogy.jpg', @t),
    ('Grand Theft Auto V', 'Imagens/VideoGames/Grand-Theft-Auto-V.jpg', @t),
    ('League of Legends', 'Imagens/VideoGames/League-of-Legends.jpg', @t),
    ('Mass Effect Trilogy', 'Imagens/VideoGames/Mass-Effect-Trilogy.jpg', @t),
    ('Metal Gear Solid Trilogy', 'Imagens/VideoGames/Metal-Gear-Solid-Trilogy.jpg', @t),
    ('Metal Gear Solid V', 'Imagens/VideoGames/Metal-Gear-Solid-V.jpg', @t),
    ('Minecraft', 'Imagens/VideoGames/Minecraft.avif', @t),
    ('Portal 2', 'Imagens/VideoGames/Portal-2.jpg', @t),
    ('Red Dead Redemption 2', 'Imagens/VideoGames/Red-Dead-Redemption-2.jpg', @t),
    ('Resident Evil 4', 'Imagens/VideoGames/Resident-Evil-4.jpg', @t),
    ('Skyrim', 'Imagens/VideoGames/Skyrim.jpg', @t),
    ('The Last of Us', 'Imagens/VideoGames/The-Last-of-Us.webp', @t),
    ('The Legend of Zelda Breath of the Wild', 'Imagens/VideoGames/The-Legend-of-Zelda-Breath-of-the-Wild.jpg', @t),
    ('The Witcher 3 Wild Hunt', 'Imagens/VideoGames/The-Witcher-3-Wild-Hunt.avif', @t),
    ('Xcom', 'Imagens/VideoGames/Xcom.jpg', @t);

