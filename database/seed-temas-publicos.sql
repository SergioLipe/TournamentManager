-- ---------------------------------------------------------------------------
-- Temas públicos e respectivos competidores.
--
-- GERADO por tools/gerar-seed.php a partir do conteúdo de Imagens/.
-- Não editar à mão: para mudar um nome, muda-se o CSV em tools/nomes/ e
-- volta-se a correr o gerador.
--
-- Correr DEPOIS do schema.sql, numa base de dados que já tenha uma conta de
-- utilizador criada (ver database/criar-admin.php).
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

-- ---------- Animals ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Animals', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Animals' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Barn Owl', 'Imagens/Animals/Barn-Owl.jpg', @t),
    ('Blue Whale', 'Imagens/Animals/Blue-Whale.jpg', @t),
    ('Brown Bear', 'Imagens/Animals/Brown-Bear.jpg', @t),
    ('Cheetah', 'Imagens/Animals/Cheetah.jpg', @t),
    ('Chimpanzee', 'Imagens/Animals/Chimpanzee.jpg', @t),
    ('Dolphin', 'Imagens/Animals/Dolphin.jpg', @t),
    ('Elephant', 'Imagens/Animals/Elephant.jpg', @t),
    ('Emperor Penguin', 'Imagens/Animals/Emperor-Penguin.jpg', @t),
    ('Giant Panda', 'Imagens/Animals/Giant-Panda.jpg', @t),
    ('Giraffe', 'Imagens/Animals/Giraffe.jpg', @t),
    ('Golden Eagle', 'Imagens/Animals/Golden-Eagle.jpg', @t),
    ('Gorilla', 'Imagens/Animals/Gorilla.jpg', @t),
    ('Great White Shark', 'Imagens/Animals/Great-White-Shark.jpg', @t),
    ('Hippopotamus', 'Imagens/Animals/Hippopotamus.jpg', @t),
    ('King Cobra', 'Imagens/Animals/King-Cobra.jpg', @t),
    ('Lion', 'Imagens/Animals/Lion.jpg', @t),
    ('Nile Crocodile', 'Imagens/Animals/Nile-Crocodile.jpg', @t),
    ('Orca', 'Imagens/Animals/Orca.jpg', @t),
    ('Polar Bear', 'Imagens/Animals/Polar-Bear.jpg', @t),
    ('Red Fox', 'Imagens/Animals/Red-Fox.jpg', @t),
    ('Red Kangaroo', 'Imagens/Animals/Red-Kangaroo.jpg', @t),
    ('Tiger', 'Imagens/Animals/Tiger.jpg', @t),
    ('White Rhinoceros', 'Imagens/Animals/White-Rhinoceros.jpg', @t),
    ('Wolf', 'Imagens/Animals/Wolf.jpg', @t);

-- ---------- Classic Cars ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Classic Cars', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Classic Cars' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Alfa Romeo Giulia', 'Imagens/Classic-Cars/Alfa-Romeo-Giulia.jpg', @t),
    ('Aston Martin DB5', 'Imagens/Classic-Cars/Aston-Martin-DB5.jpg', @t),
    ('BMW M3', 'Imagens/Classic-Cars/BMW-M3.jpg', @t),
    ('Chevrolet Corvette', 'Imagens/Classic-Cars/Chevrolet-Corvette.jpg', @t),
    ('Citroen DS', 'Imagens/Classic-Cars/Citroen-DS.jpg', @t),
    ('DeLorean', 'Imagens/Classic-Cars/DeLorean.jpg', @t),
    ('Dodge Charger', 'Imagens/Classic-Cars/Dodge-Charger.jpg', @t),
    ('Ferrari 250 GTO', 'Imagens/Classic-Cars/Ferrari-250-GTO.jpg', @t),
    ('Ferrari F40', 'Imagens/Classic-Cars/Ferrari-F40.jpg', @t),
    ('Fiat 500', 'Imagens/Classic-Cars/Fiat-500.jpg', @t),
    ('Ford Mustang', 'Imagens/Classic-Cars/Ford-Mustang.jpg', @t),
    ('Jaguar E-Type', 'Imagens/Classic-Cars/Jaguar-E-Type.jpg', @t),
    ('Lamborghini Countach', 'Imagens/Classic-Cars/Lamborghini-Countach.jpg', @t),
    ('Lamborghini Miura', 'Imagens/Classic-Cars/Lamborghini-Miura.jpg', @t),
    ('Lancia Delta', 'Imagens/Classic-Cars/Lancia-Delta.jpg', @t),
    ('Mazda RX-7', 'Imagens/Classic-Cars/Mazda-RX-7.jpg', @t),
    ('Mercedes 300 SL', 'Imagens/Classic-Cars/Mercedes-300-SL.jpg', @t),
    ('Mini Cooper', 'Imagens/Classic-Cars/Mini-Cooper.jpg', @t),
    ('Nissan Skyline GT-R', 'Imagens/Classic-Cars/Nissan-Skyline-GT-R.jpg', @t),
    ('Peugeot 205 GTI', 'Imagens/Classic-Cars/Peugeot-205-GTI.jpg', @t),
    ('Porsche 911', 'Imagens/Classic-Cars/Porsche-911.jpg', @t),
    ('Shelby Cobra', 'Imagens/Classic-Cars/Shelby-Cobra.jpg', @t),
    ('Toyota Supra', 'Imagens/Classic-Cars/Toyota-Supra.png', @t),
    ('Volkswagen Beetle', 'Imagens/Classic-Cars/Volkswagen-Beetle.jpg', @t);

-- ---------- Cocktails ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Cocktails', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Cocktails' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Bloody Mary', 'Imagens/Cocktails/Bloody-Mary.jpg', @t),
    ('Caipirinha', 'Imagens/Cocktails/Caipirinha.jpg', @t),
    ('Cosmopolitan', 'Imagens/Cocktails/Cosmopolitan.jpg', @t),
    ('Daiquiri', 'Imagens/Cocktails/Daiquiri.jpg', @t),
    ('Espresso Martini', 'Imagens/Cocktails/Espresso-Martini.jpg', @t),
    ('Gin and Tonic', 'Imagens/Cocktails/Gin-and-Tonic.jpg', @t),
    ('Long Island Iced Tea', 'Imagens/Cocktails/Long-Island-Iced-Tea.jpg', @t),
    ('Mai Tai', 'Imagens/Cocktails/Mai-Tai.jpg', @t),
    ('Manhattan', 'Imagens/Cocktails/Manhattan.jpg', @t),
    ('Margarita', 'Imagens/Cocktails/Margarita.jpg', @t),
    ('Martini', 'Imagens/Cocktails/Martini.jpg', @t),
    ('Mojito', 'Imagens/Cocktails/Mojito.jpg', @t),
    ('Moscow Mule', 'Imagens/Cocktails/Moscow-Mule.jpg', @t),
    ('Negroni', 'Imagens/Cocktails/Negroni.jpg', @t),
    ('Old Fashioned', 'Imagens/Cocktails/Old-Fashioned.jpg', @t),
    ('Pina Colada', 'Imagens/Cocktails/Pina-Colada.jpg', @t),
    ('Porto Tonico', 'Imagens/Cocktails/Porto-Tonico.jpg', @t),
    ('Sangria', 'Imagens/Cocktails/Sangria.jpg', @t),
    ('Sazerac', 'Imagens/Cocktails/Sazerac.jpg', @t),
    ('Tom Collins', 'Imagens/Cocktails/Tom-Collins.jpg', @t),
    ('Whiskey Sour', 'Imagens/Cocktails/Whiskey-Sour.jpg', @t);

-- ---------- Dinosaurs ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Dinosaurs', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Dinosaurs' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Allosaurus', 'Imagens/Dinosaurs/Allosaurus.jpg', @t),
    ('Ankylosaurus', 'Imagens/Dinosaurs/Ankylosaurus.jpg', @t),
    ('Apatosaurus', 'Imagens/Dinosaurs/Apatosaurus.jpg', @t),
    ('Archaeopteryx', 'Imagens/Dinosaurs/Archaeopteryx.jpg', @t),
    ('Argentinosaurus', 'Imagens/Dinosaurs/Argentinosaurus.png', @t),
    ('Brachiosaurus', 'Imagens/Dinosaurs/Brachiosaurus.jpg', @t),
    ('Carnotaurus', 'Imagens/Dinosaurs/Carnotaurus.jpg', @t),
    ('Deinonychus', 'Imagens/Dinosaurs/Deinonychus.jpg', @t),
    ('Dilophosaurus', 'Imagens/Dinosaurs/Dilophosaurus.jpg', @t),
    ('Diplodocus', 'Imagens/Dinosaurs/Diplodocus.jpg', @t),
    ('Giganotosaurus', 'Imagens/Dinosaurs/Giganotosaurus.jpg', @t),
    ('Iguanodon', 'Imagens/Dinosaurs/Iguanodon.jpg', @t),
    ('Mosasaurus', 'Imagens/Dinosaurs/Mosasaurus.jpg', @t),
    ('Pachycephalosaurus', 'Imagens/Dinosaurs/Pachycephalosaurus.jpg', @t),
    ('Parasaurolophus', 'Imagens/Dinosaurs/Parasaurolophus.jpg', @t),
    ('Pteranodon', 'Imagens/Dinosaurs/Pteranodon.jpg', @t),
    ('Quetzalcoatlus', 'Imagens/Dinosaurs/Quetzalcoatlus.jpg', @t),
    ('Spinosaurus', 'Imagens/Dinosaurs/Spinosaurus.jpg', @t),
    ('Stegosaurus', 'Imagens/Dinosaurs/Stegosaurus.jpg', @t),
    ('Therizinosaurus', 'Imagens/Dinosaurs/Therizinosaurus.jpg', @t),
    ('Triceratops', 'Imagens/Dinosaurs/Triceratops.jpg', @t),
    ('Tyrannosaurus', 'Imagens/Dinosaurs/Tyrannosaurus.jpg', @t),
    ('Utahraptor', 'Imagens/Dinosaurs/Utahraptor.jpg', @t),
    ('Velociraptor', 'Imagens/Dinosaurs/Velociraptor.jpg', @t);

-- ---------- Dog Breeds ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Dog Breeds', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Dog Breeds' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Akita', 'Imagens/Dog-Breeds/Akita.png', @t),
    ('Australian Shepherd', 'Imagens/Dog-Breeds/Australian-Shepherd.jpg', @t),
    ('Beagle', 'Imagens/Dog-Breeds/Beagle.jpg', @t),
    ('Border Collie', 'Imagens/Dog-Breeds/Border-Collie.jpg', @t),
    ('Boxer', 'Imagens/Dog-Breeds/Boxer.jpg', @t),
    ('Bulldog', 'Imagens/Dog-Breeds/Bulldog.jpg', @t),
    ('Chihuahua', 'Imagens/Dog-Breeds/Chihuahua.jpg', @t),
    ('Cocker Spaniel', 'Imagens/Dog-Breeds/Cocker-Spaniel.jpg', @t),
    ('Dalmatian', 'Imagens/Dog-Breeds/Dalmatian.jpg', @t),
    ('Dobermann', 'Imagens/Dog-Breeds/Dobermann.jpg', @t),
    ('French Bulldog', 'Imagens/Dog-Breeds/French-Bulldog.jpg', @t),
    ('German Shepherd', 'Imagens/Dog-Breeds/German-Shepherd.jpg', @t),
    ('Golden Retriever', 'Imagens/Dog-Breeds/Golden-Retriever.jpg', @t),
    ('Great Dane', 'Imagens/Dog-Breeds/Great-Dane.jpg', @t),
    ('Labrador', 'Imagens/Dog-Breeds/Labrador.jpg', @t),
    ('Poodle', 'Imagens/Dog-Breeds/Poodle.jpg', @t),
    ('Portuguese Water Dog', 'Imagens/Dog-Breeds/Portuguese-Water-Dog.jpg', @t),
    ('Pug', 'Imagens/Dog-Breeds/Pug.jpg', @t),
    ('Rottweiler', 'Imagens/Dog-Breeds/Rottweiler.jpg', @t),
    ('Saint Bernard', 'Imagens/Dog-Breeds/Saint-Bernard.jpg', @t),
    ('Shiba Inu', 'Imagens/Dog-Breeds/Shiba-Inu.jpg', @t),
    ('Shih Tzu', 'Imagens/Dog-Breeds/Shih-Tzu.jpg', @t),
    ('Siberian Husky', 'Imagens/Dog-Breeds/Siberian-Husky.jpg', @t),
    ('Yorkshire Terrier', 'Imagens/Dog-Breeds/Yorkshire-Terrier.jpg', @t);

-- ---------- European Capitals ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('European Capitals', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'European Capitals' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Amsterdam', 'Imagens/European-Capitals/Amsterdam.png', @t),
    ('Athens', 'Imagens/European-Capitals/Athens.jpg', @t),
    ('Berlin', 'Imagens/European-Capitals/Berlin.jpg', @t),
    ('Bratislava', 'Imagens/European-Capitals/Bratislava.jpg', @t),
    ('Brussels', 'Imagens/European-Capitals/Brussels.jpg', @t),
    ('Bucharest', 'Imagens/European-Capitals/Bucharest.jpg', @t),
    ('Budapest', 'Imagens/European-Capitals/Budapest.jpg', @t),
    ('Copenhagen', 'Imagens/European-Capitals/Copenhagen.jpg', @t),
    ('Dublin', 'Imagens/European-Capitals/Dublin.jpg', @t),
    ('Helsinki', 'Imagens/European-Capitals/Helsinki.jpg', @t),
    ('Lisbon', 'Imagens/European-Capitals/Lisbon.jpg', @t),
    ('Ljubljana', 'Imagens/European-Capitals/Ljubljana.jpg', @t),
    ('London', 'Imagens/European-Capitals/London.jpg', @t),
    ('Madrid', 'Imagens/European-Capitals/Madrid.jpg', @t),
    ('Oslo', 'Imagens/European-Capitals/Oslo.jpg', @t),
    ('Paris', 'Imagens/European-Capitals/Paris.jpg', @t),
    ('Prague', 'Imagens/European-Capitals/Prague.jpg', @t),
    ('Reykjavik', 'Imagens/European-Capitals/Reykjavik.jpg', @t),
    ('Rome', 'Imagens/European-Capitals/Rome.jpg', @t),
    ('Stockholm', 'Imagens/European-Capitals/Stockholm.jpg', @t),
    ('Tallinn', 'Imagens/European-Capitals/Tallinn.jpg', @t),
    ('Vienna', 'Imagens/European-Capitals/Vienna.jpg', @t),
    ('Warsaw', 'Imagens/European-Capitals/Warsaw.jpg', @t),
    ('Zagreb', 'Imagens/European-Capitals/Zagreb.jpg', @t);

-- ---------- Footballers ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Footballers', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Footballers' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Andres Iniesta', 'Imagens/Footballers/Andres-Iniesta.jpg', @t),
    ('Bernardo Silva', 'Imagens/Footballers/Bernardo-Silva.jpg', @t),
    ('Cristiano Ronaldo', 'Imagens/Footballers/Cristiano-Ronaldo.jpg', @t),
    ('Deco', 'Imagens/Footballers/Deco.jpg', @t),
    ('Diego Maradona', 'Imagens/Footballers/Diego-Maradona.jpg', @t),
    ('Erling Haaland', 'Imagens/Footballers/Erling-Haaland.jpg', @t),
    ('Eusebio', 'Imagens/Footballers/Eusebio.jpg', @t),
    ('Gianluigi Buffon', 'Imagens/Footballers/Gianluigi-Buffon.jpg', @t),
    ('Iker Casillas', 'Imagens/Footballers/Iker-Casillas.jpg', @t),
    ('Kevin De Bruyne', 'Imagens/Footballers/Kevin-De-Bruyne.jpg', @t),
    ('Kylian Mbappe', 'Imagens/Footballers/Kylian-Mbappe.jpg', @t),
    ('Lionel Messi', 'Imagens/Footballers/Lionel-Messi.jpg', @t),
    ('Luis Figo', 'Imagens/Footballers/Luis-Figo.jpg', @t),
    ('Luka Modric', 'Imagens/Footballers/Luka-Modric.jpg', @t),
    ('Mohamed Salah', 'Imagens/Footballers/Mohamed-Salah.jpg', @t),
    ('Neymar', 'Imagens/Footballers/Neymar.jpg', @t),
    ('Pele', 'Imagens/Footballers/Pele.jpg', @t),
    ('Robert Lewandowski', 'Imagens/Footballers/Robert-Lewandowski.jpg', @t),
    ('Ronaldinho', 'Imagens/Footballers/Ronaldinho.jpg', @t),
    ('Ronaldo', 'Imagens/Footballers/Ronaldo.jpg', @t),
    ('Rui Costa', 'Imagens/Footballers/Rui-Costa.png', @t),
    ('Virgil van Dijk', 'Imagens/Footballers/Virgil-van-Dijk.jpg', @t),
    ('Xavi', 'Imagens/Footballers/Xavi.jpg', @t),
    ('Zinedine Zidane', 'Imagens/Footballers/Zinedine-Zidane.jpg', @t);

-- ---------- International Food ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('International Food', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'International Food' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Baklava', 'Imagens/International-Food/Baklava.png', @t),
    ('Steak', 'Imagens/International-Food/bife.jpg', @t),
    ('Spaghetti Bolognese', 'Imagens/International-Food/Bolonhesa.webp', @t),
    ('Burrito', 'Imagens/International-Food/Burrito.jpg', @t),
    ('Butter Chicken', 'Imagens/International-Food/Butter-Chicken.jpg', @t),
    ('Carbonara', 'Imagens/International-Food/Carbonara.jpg', @t),
    ('Ceviche', 'Imagens/International-Food/Ceviche.jpg', @t),
    ('Cheeseburger', 'Imagens/International-Food/cheeseburger.jpg', @t),
    ('Churro', 'Imagens/International-Food/Churro.jpg', @t),
    ('Croissant', 'Imagens/International-Food/Croissant.jpg', @t),
    ('Croque Monsieur', 'Imagens/International-Food/Croque-Monsieur.jpg', @t),
    ('Curry', 'Imagens/International-Food/Curry.jpg', @t),
    ('Dim Sum', 'Imagens/International-Food/Dim-Sum.jpg', @t),
    ('Dumpling', 'Imagens/International-Food/Dumpling.jpg', @t),
    ('Falafel', 'Imagens/International-Food/Falafel.jpg', @t),
    ('Fish and Chips', 'Imagens/International-Food/Fish-and-Chips.jpg', @t),
    ('Hummus', 'Imagens/International-Food/Hummus.jpg', @t),
    ('Kebab', 'Imagens/International-Food/Kebab.jpg', @t),
    ('Lasagna', 'Imagens/International-Food/lasanha.jpg', @t),
    ('Pad Thai', 'Imagens/International-Food/Pad-Thai.webp', @t),
    ('Seafood Paella', 'Imagens/International-Food/Paella-de-marisco.jpeg', @t),
    ('Pho', 'Imagens/International-Food/Pho.jpg', @t),
    ('Pizza', 'Imagens/International-Food/Pizza.jpg', @t),
    ('Poutine', 'Imagens/International-Food/Poutine.jpg', @t),
    ('Ramen', 'Imagens/International-Food/Ramen.jpg', @t),
    ('Sushi', 'Imagens/International-Food/sushi.jpg', @t),
    ('Tacos', 'Imagens/International-Food/Tacos.jpg', @t),
    ('Tiramisu', 'Imagens/International-Food/Tiramisu.jpg', @t);

-- ---------- Movies ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Movies', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Movies' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('300', 'Imagens/Filmes/300.jpeg', @t),
    ('American History X', 'Imagens/Filmes/American-History-X.png', @t),
    ('Avatar', 'Imagens/Filmes/Avatar.jpeg', @t),
    ('Back to the Future', 'Imagens/Filmes/Back-to-the-Future.jpg', @t),
    ('The Book of Eli', 'Imagens/Filmes/Book-of-Eli.jpeg', @t),
    ('The Da Vinci Code', 'Imagens/Filmes/Da-Vinci-Code.jpeg', @t),
    ('Django Unchained', 'Imagens/Filmes/Django-Unchained.jpeg', @t),
    ('Fight Club', 'Imagens/Filmes/Fight-Club.jpeg', @t),
    ('Forrest Gump', 'Imagens/Filmes/Forest-Gump.jpeg', @t),
    ('Gladiator', 'Imagens/Filmes/Gladiador.jpeg', @t),
    ('Good Will Hunting', 'Imagens/Filmes/Good-Will-Hunting.jpeg', @t),
    ('The Hangover', 'Imagens/Filmes/Hangover.jpeg', @t),
    ('Inception', 'Imagens/Filmes/Inception.jpeg', @t),
    ('Inglourious Basterds', 'Imagens/Filmes/Inglorious-Basterds.jpeg', @t),
    ('Interstellar', 'Imagens/Filmes/Interstellar.jpg', @t),
    ('Joker', 'Imagens/Filmes/Joker.jpg', @t),
    ('Jurassic Park', 'Imagens/Filmes/Jurassic-Park.jpg', @t),
    ('The Matrix', 'Imagens/Filmes/Matrix.jpeg', @t),
    ('Memento', 'Imagens/Filmes/Memento.jpeg', @t),
    ('No Country for Old Men', 'Imagens/Filmes/No-Country-fol-Old-men.jpeg', @t),
    ('Parasite', 'Imagens/Filmes/Parasite.png', @t),
    ('Prometheus', 'Imagens/Filmes/Prometheus.jpeg', @t),
    ('Pulp Fiction', 'Imagens/Filmes/Pulp-Fiction.jpeg', @t),
    ('Saving Private Ryan', 'Imagens/Filmes/Saving-Private-Ryan.jpeg', @t),
    ('Se7en', 'Imagens/Filmes/Se7en.jpg', @t),
    ('Shutter Island', 'Imagens/Filmes/Shutter-Island.jpeg', @t),
    ('Star Wars', 'Imagens/Filmes/Star-Wars.png', @t),
    ('The Dark Knight', 'Imagens/Filmes/The-Dark-Knight.jpeg', @t),
    ('The Departed', 'Imagens/Filmes/The-Departed.jpg', @t),
    ('The Godfather', 'Imagens/Filmes/The-Godfather.jpg', @t),
    ('The Lord of the Rings: The Fellowship of the Ring', 'Imagens/Filmes/The-Lord-of-the-Rings-The-Fellowship-of-the-Ring.jpg', @t),
    ('The Shawshank Redemption', 'Imagens/Filmes/The-Shawshank-Redemption.jpg', @t),
    ('The Silence of the Lambs', 'Imagens/Filmes/The-Silence-of-the-Lambs.jpg', @t),
    ('Titanic', 'Imagens/Filmes/Titanic.png', @t),
    ('Whiplash', 'Imagens/Filmes/Whiplash.jpg', @t);

-- ---------- Portuguese Desserts ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Portuguese Desserts', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Portuguese Desserts' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Aletria', 'Imagens/Portuguese-Desserts/Aletria.jpg', @t),
    ('Arroz Doce', 'Imagens/Portuguese-Desserts/Arroz-Doce.jpg', @t),
    ('Baba de Camelo', 'Imagens/Portuguese-Desserts/Baba-de-Camelo.jpg', @t),
    ('Bolo de Mel', 'Imagens/Portuguese-Desserts/Bolo-de-Mel.jpg', @t),
    ('Broa', 'Imagens/Portuguese-Desserts/Broa.jpg', @t),
    ('Chocolate Mousse', 'Imagens/Portuguese-Desserts/Chocolate-Mousse.jpg', @t),
    ('Encharcada', 'Imagens/Portuguese-Desserts/Encharcada.jpg', @t),
    ('Fatias de Tomar', 'Imagens/Portuguese-Desserts/Fatias-de-Tomar.jpg', @t),
    ('Filhos', 'Imagens/Portuguese-Desserts/Filhos.jpg', @t),
    ('Leite Creme', 'Imagens/Portuguese-Desserts/Leite-Creme.jpg', @t),
    ('Ovos Moles', 'Imagens/Portuguese-Desserts/Ovos-Moles.jpg', @t),
    ('Pao de Lo', 'Imagens/Portuguese-Desserts/Pao-de-Lo.jpg', @t),
    ('Papos de Anjo', 'Imagens/Portuguese-Desserts/Papos-de-Anjo.jpg', @t),
    ('Pastel de Nata', 'Imagens/Portuguese-Desserts/Pastel-de-Nata.jpg', @t),
    ('Pastel de Tentugal', 'Imagens/Portuguese-Desserts/Pastel-de-Tentugal.jpg', @t),
    ('Queijada', 'Imagens/Portuguese-Desserts/Queijada.jpg', @t),
    ('Rabanadas', 'Imagens/Portuguese-Desserts/Rabanadas.jpg', @t),
    ('Salame de Chocolate', 'Imagens/Portuguese-Desserts/Salame-de-Chocolate.jpg', @t),
    ('Serradura', 'Imagens/Portuguese-Desserts/Serradura.jpg', @t),
    ('Sonhos', 'Imagens/Portuguese-Desserts/Sonhos.jpg', @t),
    ('Toucinho do Ceu', 'Imagens/Portuguese-Desserts/Toucinho-do-Ceu.jpg', @t),
    ('Travesseiro', 'Imagens/Portuguese-Desserts/Travesseiro.jpg', @t);

-- ---------- Portuguese Food ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Portuguese Food', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Portuguese Food' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Alheira', 'Imagens/Portuguese-Food/alheira.jpg', @t),
    ('Amêijoas à Bulhão Pato', 'Imagens/Portuguese-Food/Ameijoas-a-Bulhao-Pato.jpg', @t),
    ('Arroz de Cabidela', 'Imagens/Portuguese-Food/Arroz-de-cabidelas.jfif', @t),
    ('Arroz de Marisco', 'Imagens/Portuguese-Food/Arroz-de-Marisco.jpg', @t),
    ('Arroz de Pato', 'Imagens/Portuguese-Food/arroz_pato.jpg', @t),
    ('Bacalhau à Gomes de Sá', 'Imagens/Portuguese-Food/Bacalhau-a-Gomes-de-Sa.jpg', @t),
    ('Bacalhau à Lagareiro', 'Imagens/Portuguese-Food/Bacalhau-a-Lagareiro.jpg', @t),
    ('Bacalhau à Brás', 'Imagens/Portuguese-Food/bacalhau_braz.jpg', @t),
    ('Bifana', 'Imagens/Portuguese-Food/Bifana.jpg', @t),
    ('Bola de Berlim', 'Imagens/Portuguese-Food/Bola-de-Berlim.jpg', @t),
    ('Caldo Verde', 'Imagens/Portuguese-Food/Caldo-Verde.jpg', @t),
    ('Carne de Porco à Alentejana', 'Imagens/Portuguese-Food/carne-de-porco-a-alentejana.jpg', @t),
    ('Cheeseburger', 'Imagens/Portuguese-Food/cheeseburger.jpg', @t),
    ('Cozido à Portuguesa', 'Imagens/Portuguese-Food/cozido.jpg', @t),
    ('Dobrada', 'Imagens/Portuguese-Food/Dobrada.jpg', @t),
    ('Feijoada à Transmontana', 'Imagens/Portuguese-Food/feijoada_transmontana.jpg', @t),
    ('Francesinha', 'Imagens/Portuguese-Food/francesinha.jpg', @t),
    ('Piri-Piri Chicken', 'Imagens/Portuguese-Food/Frango-Piri-Piri.jfif', @t),
    ('Kebab', 'Imagens/Portuguese-Food/Kebab.jpg', @t),
    ('Leitão à Bairrada', 'Imagens/Portuguese-Food/Leitao-a-Bairrada.jpg', @t),
    ('Pastéis de Bacalhau', 'Imagens/Portuguese-Food/Pasteis-de-Bacalhau.jpg', @t),
    ('Polvo à Lagareiro', 'Imagens/Portuguese-Food/polvo_Lagareiro.jpg', @t),
    ('Presunto', 'Imagens/Portuguese-Food/Presunto.jpg', @t),
    ('Queijo da Serra', 'Imagens/Portuguese-Food/Queijo-da-Serra.jpg', @t),
    ('Rojões', 'Imagens/Portuguese-Food/rojoes.jfif', @t),
    ('Grilled Sardines', 'Imagens/Portuguese-Food/Sardinhas.jpg', @t),
    ('Sushi', 'Imagens/Portuguese-Food/sushi.jpg', @t),
    ('Tacos', 'Imagens/Portuguese-Food/Tacos.jpg', @t),
    ('Tripas à Moda do Porto', 'Imagens/Portuguese-Food/Tripas-a-modo-do-Porto.jfif', @t),
    ('Vinho do Porto', 'Imagens/Portuguese-Food/Vinho-do-Porto.jpg', @t);

-- ---------- Portuguese Landmarks ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Portuguese Landmarks', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Portuguese Landmarks' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Aguas Livres Aqueduct', 'Imagens/Portuguese-Landmarks/Aguas-Livres-Aqueduct.jpg', @t),
    ('Alcobaca Monastery', 'Imagens/Portuguese-Landmarks/Alcobaca-Monastery.jpg', @t),
    ('Batalha Monastery', 'Imagens/Portuguese-Landmarks/Batalha-Monastery.jpg', @t),
    ('Belem Tower', 'Imagens/Portuguese-Landmarks/Belem-Tower.jpg', @t),
    ('Bom Jesus do Monte', 'Imagens/Portuguese-Landmarks/Bom-Jesus-do-Monte.jpg', @t),
    ('Cabo da Roca', 'Imagens/Portuguese-Landmarks/Cabo-da-Roca.jpg', @t),
    ('Chapel of Bones', 'Imagens/Portuguese-Landmarks/Chapel-of-Bones.jpg', @t),
    ('Clerigos Tower', 'Imagens/Portuguese-Landmarks/Clerigos-Tower.jpg', @t),
    ('Convent of Christ', 'Imagens/Portuguese-Landmarks/Convent-of-Christ.jpg', @t),
    ('Guimaraes Castle', 'Imagens/Portuguese-Landmarks/Guimaraes-Castle.jpg', @t),
    ('Jeronimos Monastery', 'Imagens/Portuguese-Landmarks/Jeronimos-Monastery.png', @t),
    ('Livraria Lello', 'Imagens/Portuguese-Landmarks/Livraria-Lello.jpg', @t),
    ('Mafra Palace', 'Imagens/Portuguese-Landmarks/Mafra-Palace.jpg', @t),
    ('Padrao dos Descobrimentos', 'Imagens/Portuguese-Landmarks/Padrao-dos-Descobrimentos.jpg', @t),
    ('Pena Palace', 'Imagens/Portuguese-Landmarks/Pena-Palace.jpg', @t),
    ('Porto Cathedral', 'Imagens/Portuguese-Landmarks/Porto-Cathedral.jpg', @t),
    ('Queluz Palace', 'Imagens/Portuguese-Landmarks/Queluz-Palace.jpg', @t),
    ('Quinta da Regaleira', 'Imagens/Portuguese-Landmarks/Quinta-da-Regaleira.jpg', @t),
    ('Sao Jorge Castle', 'Imagens/Portuguese-Landmarks/Sao-Jorge-Castle.jpg', @t),
    ('University of Coimbra', 'Imagens/Portuguese-Landmarks/University-of-Coimbra.png', @t);

-- ---------- Rock Bands ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Rock Bands', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Rock Bands' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('AC/DC', 'Imagens/Bandas-de-Rock/AC-DC.jpg', @t),
    ('Aerosmith', 'Imagens/Bandas-de-Rock/Aerosmith.jpg', @t),
    ('Black Sabbath', 'Imagens/Bandas-de-Rock/Black-Sabbath.jpg', @t),
    ('Dire Straits', 'Imagens/Bandas-de-Rock/Dire-Straits.jpg', @t),
    ('Fleetwood Mac', 'Imagens/Bandas-de-Rock/Fleetwood-Mac.webp', @t),
    ('Guns N'' Roses', 'Imagens/Bandas-de-Rock/Guns-n-Roses.jpg', @t),
    ('Iron Maiden', 'Imagens/Bandas-de-Rock/Iron-Maiden.jpg', @t),
    ('Kiss', 'Imagens/Bandas-de-Rock/Kiss.jpg', @t),
    ('Led Zeppelin', 'Imagens/Bandas-de-Rock/Led-Zeppelin.jpg', @t),
    ('Metallica', 'Imagens/Bandas-de-Rock/Metallica.png', @t),
    ('Nirvana', 'Imagens/Bandas-de-Rock/Nirvana.jpg', @t),
    ('Oasis', 'Imagens/Bandas-de-Rock/Oasis.webp', @t),
    ('Pink Floyd', 'Imagens/Bandas-de-Rock/Pink-Floyd.jpg', @t),
    ('Queen', 'Imagens/Bandas-de-Rock/Queen.jpg', @t),
    ('Radiohead', 'Imagens/Bandas-de-Rock/RadioHead.png', @t),
    ('Rage Against the Machine', 'Imagens/Bandas-de-Rock/Rage-Against-The-Machine.jpg', @t),
    ('The Beatles', 'Imagens/Bandas-de-Rock/The-Beatles.webp', @t),
    ('The Doors', 'Imagens/Bandas-de-Rock/The-Doors.jfif', @t),
    ('The Jimi Hendrix Experience', 'Imagens/Bandas-de-Rock/The-Jimi-Hendrix-Experience.jpg', @t),
    ('The Rolling Stones', 'Imagens/Bandas-de-Rock/The-Rolling-Stones.jpg', @t);

-- ---------- TV Shows ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('TV Shows', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'TV Shows' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Band of Brothers', 'Imagens/TV-Shows/Band-of-Brothers.jpg', @t),
    ('Better Call Saul', 'Imagens/TV-Shows/Better-Call-Saul.png', @t),
    ('Breaking Bad', 'Imagens/TV-Shows/Breaking-Bad.png', @t),
    ('Bridgerton', 'Imagens/TV-Shows/Bridgerton.png', @t),
    ('Chernobyl', 'Imagens/TV-Shows/Chernobyl.jpg', @t),
    ('Dexter', 'Imagens/TV-Shows/Dexter.png', @t),
    ('Downton Abbey', 'Imagens/TV-Shows/Downton-Abbey.jpg', @t),
    ('Fargo', 'Imagens/TV-Shows/Fargo.png', @t),
    ('Game of Thrones', 'Imagens/TV-Shows/Game-of-Thrones.jpg', @t),
    ('Homeland', 'Imagens/TV-Shows/Homeland.jpg', @t),
    ('How I Met Your Mother', 'Imagens/TV-Shows/How-I-Met-Your-Mother.png', @t),
    ('Lost', 'Imagens/TV-Shows/Lost.png', @t),
    ('Mad Men', 'Imagens/TV-Shows/Mad-Men.jpg', @t),
    ('Narcos', 'Imagens/TV-Shows/Narcos.jpg', @t),
    ('Peaky Blinders', 'Imagens/TV-Shows/Peaky-Blinders.jpg', @t),
    ('Sherlock', 'Imagens/TV-Shows/Sherlock.jpg', @t),
    ('Stranger Things', 'Imagens/TV-Shows/Stranger-Things.png', @t),
    ('The Big Bang Theory', 'Imagens/TV-Shows/The-Big-Bang-Theory.png', @t),
    ('The Boys', 'Imagens/TV-Shows/The-Boys.png', @t),
    ('The Crown', 'Imagens/TV-Shows/The-Crown.jpg', @t),
    ('The Simpsons', 'Imagens/TV-Shows/The-Simpsons.png', @t),
    ('The Wire', 'Imagens/TV-Shows/The-Wire.png', @t),
    ('True Detective', 'Imagens/TV-Shows/True-Detective.jpg', @t),
    ('Vikings', 'Imagens/TV-Shows/Vikings.png', @t);

-- ---------- Video Games ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('Video Games', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'Video Games' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Assassin''s Creed IV: Black Flag', 'Imagens/VideoGames/Assassin-Creed-IV-Black-Flag.jpg', @t),
    ('Batman: Arkham Trilogy', 'Imagens/VideoGames/Batman-Arkham-Trilogy.jfif', @t),
    ('BioShock Trilogy', 'Imagens/VideoGames/BioShock-Trilogy.jpg', @t),
    ('Borderlands', 'Imagens/VideoGames/Borderlands.jpg', @t),
    ('Call of Duty 4: Modern Warfare', 'Imagens/VideoGames/Call-of-Duty-4-Modern-Warfare.jpg', @t),
    ('Celeste', 'Imagens/VideoGames/Celeste.png', @t),
    ('Civilization V', 'Imagens/VideoGames/Civ-V.jpg', @t),
    ('Counter-Strike 1.6', 'Imagens/VideoGames/Counter-Strike-1.6.jpg', @t),
    ('Dark Souls Trilogy', 'Imagens/VideoGames/Dark-Souls-Trilogy.jpg', @t),
    ('Diablo II', 'Imagens/VideoGames/Diablo-II.png', @t),
    ('Dishonored', 'Imagens/VideoGames/Dishonored.png', @t),
    ('Doom (2016)', 'Imagens/VideoGames/Doom-2016-video-game.jpg', @t),
    ('Elden Ring', 'Imagens/VideoGames/Elden-Ring.jpg', @t),
    ('Fallout: New Vegas', 'Imagens/VideoGames/Fallout-NV.jfif', @t),
    ('Final Fantasy X', 'Imagens/VideoGames/Final-Fantasy-X.webp', @t),
    ('God of War (2018)', 'Imagens/VideoGames/God-of-War-Reboot.avif', @t),
    ('God of War Trilogy', 'Imagens/VideoGames/God-of-War-Trilogy.jpg', @t),
    ('Grand Theft Auto V', 'Imagens/VideoGames/Grand-Theft-Auto-V.jpg', @t),
    ('GTA: San Andreas', 'Imagens/VideoGames/GTA-San-Andreas.jfif', @t),
    ('Half-Life 2', 'Imagens/VideoGames/Half-Life-2.jpg', @t),
    ('Hollow Knight', 'Imagens/VideoGames/Hollow-Knight.jpg', @t),
    ('League of Legends', 'Imagens/VideoGames/League-of-Legends.jpg', @t),
    ('Mass Effect Trilogy', 'Imagens/VideoGames/Mass-Effect-Trilogy.jpg', @t),
    ('Metal Gear Solid Trilogy', 'Imagens/VideoGames/Metal-Gear-Solid-Trilogy.jpg', @t),
    ('Metal Gear Solid V', 'Imagens/VideoGames/Metal-Gear-Solid-V.jpg', @t),
    ('Minecraft', 'Imagens/VideoGames/Minecraft.avif', @t),
    ('Overwatch', 'Imagens/VideoGames/Overwatch.jpg', @t),
    ('Portal 2', 'Imagens/VideoGames/Portal-2.jpg', @t),
    ('Red Dead Redemption 2', 'Imagens/VideoGames/Red-Dead-Redemption-2.jpg', @t),
    ('Resident Evil 4', 'Imagens/VideoGames/Resident-Evil-4.jpg', @t),
    ('Skyrim', 'Imagens/VideoGames/Skyrim.jpg', @t),
    ('StarCraft II', 'Imagens/VideoGames/StarCraft-II.png', @t),
    ('Stardew Valley', 'Imagens/VideoGames/Stardew-Valley.png', @t),
    ('Super Mario 64', 'Imagens/VideoGames/Super-Mario-64.png', @t),
    ('Terraria', 'Imagens/VideoGames/Terraria.jpg', @t),
    ('The Last of Us', 'Imagens/VideoGames/The-Last-of-Us.webp', @t),
    ('Zelda: Breath of the Wild', 'Imagens/VideoGames/The-Legend-of-Zelda-Breath-of-the-Wild.jpg', @t),
    ('The Witcher 3: Wild Hunt', 'Imagens/VideoGames/The-Witcher-3-Wild-Hunt.avif', @t),
    ('World of Warcraft', 'Imagens/VideoGames/World-of-Warcraft.png', @t),
    ('XCOM', 'Imagens/VideoGames/Xcom.jpg', @t);

-- ---------- World Landmarks ----------
INSERT INTO tema (nome, utilizadorId, publico) VALUES ('World Landmarks', @dono, 1)
    ON DUPLICATE KEY UPDATE publico = 1;
SET @t = (SELECT id FROM tema WHERE nome = 'World Landmarks' AND utilizadorId = @dono);
DELETE FROM competidor WHERE TemaId = @t;
INSERT INTO competidor (nome, imagem, TemaId) VALUES
    ('Acropolis of Athens', 'Imagens/World-Landmarks/Acropolis-of-Athens.jpg', @t),
    ('Alhambra', 'Imagens/World-Landmarks/Alhambra.jpg', @t),
    ('Angkor Wat', 'Imagens/World-Landmarks/Angkor-Wat.jpg', @t),
    ('Big Ben', 'Imagens/World-Landmarks/Big-Ben.jpg', @t),
    ('Burj Khalifa', 'Imagens/World-Landmarks/Burj-Khalifa.jpg', @t),
    ('Chichen Itza', 'Imagens/World-Landmarks/Chichen-Itza.jpg', @t),
    ('Christ the Redeemer', 'Imagens/World-Landmarks/Christ-the-Redeemer.jpg', @t),
    ('Colosseum', 'Imagens/World-Landmarks/Colosseum.jpg', @t),
    ('Eiffel Tower', 'Imagens/World-Landmarks/Eiffel-Tower.jpg', @t),
    ('Golden Gate Bridge', 'Imagens/World-Landmarks/Golden-Gate-Bridge.jpg', @t),
    ('Great Wall of China', 'Imagens/World-Landmarks/Great-Wall-of-China.jpg', @t),
    ('Hagia Sophia', 'Imagens/World-Landmarks/Hagia-Sophia.jpg', @t),
    ('Leaning Tower of Pisa', 'Imagens/World-Landmarks/Leaning-Tower-of-Pisa.jpg', @t),
    ('Machu Picchu', 'Imagens/World-Landmarks/Machu-Picchu.jpg', @t),
    ('Moai', 'Imagens/World-Landmarks/Moai.jpg', @t),
    ('Mount Rushmore', 'Imagens/World-Landmarks/Mount-Rushmore.jpg', @t),
    ('Neuschwanstein Castle', 'Imagens/World-Landmarks/Neuschwanstein-Castle.jpg', @t),
    ('Petra', 'Imagens/World-Landmarks/Petra.jpg', @t),
    ('Pyramids of Giza', 'Imagens/World-Landmarks/Pyramids-of-Giza.jpg', @t),
    ('Sagrada Familia', 'Imagens/World-Landmarks/Sagrada-Familia.jpg', @t),
    ('Statue of Liberty', 'Imagens/World-Landmarks/Statue-of-Liberty.jpg', @t),
    ('Stonehenge', 'Imagens/World-Landmarks/Stonehenge.jpg', @t),
    ('Sydney Opera House', 'Imagens/World-Landmarks/Sydney-Opera-House.jpg', @t),
    ('Taj Mahal', 'Imagens/World-Landmarks/Taj-Mahal.jpg', @t);
