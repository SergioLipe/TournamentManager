-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql300.infinityfree.com
-- Generation Time: Jul 03, 2025 at 07:39 AM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_36547358_torneio`
--

-- --------------------------------------------------------

--
-- Table structure for table `competidor`
--

CREATE TABLE `competidor` (
  `id` int(11) NOT NULL,
  `nBatalhasVencidas` int(11) DEFAULT 0,
  `nBatalhasPerdidas` int(11) DEFAULT 0,
  `nTorneiosVencidos` int(11) DEFAULT 0,
  `imagem` varchar(255) DEFAULT NULL,
  `nome` varchar(50) NOT NULL,
  `TemaId` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `competidor`
--

INSERT INTO `competidor` (`id`, `nBatalhasVencidas`, `nBatalhasPerdidas`, `nTorneiosVencidos`, `imagem`, `nome`, `TemaId`) VALUES
(1, 0, 1, 0, 'Imagens/Comida Portuguesa/alheira.jpg', 'alheira', 1),
(2, 0, 0, 0, 'Imagens/Comida Portuguesa/Arroz de cabidelas.jfif', 'Arroz de cabidelas', 1),
(3, 0, 0, 0, 'Imagens/Comida Portuguesa/arroz_pato.jpg', 'arroz_pato', 1),
(4, 0, 0, 0, 'Imagens/Comida Portuguesa/Bacalhau Ã  Lagareiro.jpg', 'Bacalhau Ã  Lagareiro', 1),
(5, 2, 1, 0, 'Imagens/Comida Portuguesa/bacalhau_braz.jpg', 'bacalhau_braz', 1),
(6, 2, 0, 1, 'Imagens/Comida Portuguesa/bife.jpg', 'bife', 1),
(7, 1, 1, 0, 'Imagens/Comida Portuguesa/cheeseburger.jpg', 'cheeseburger', 1),
(8, 0, 0, 0, 'Imagens/Comida Portuguesa/cozido.jpg', 'cozido', 1),
(9, 1, 3, 0, 'Imagens/Comida Portuguesa/Dobrada.jpg', 'Dobrada', 1),
(10, 0, 1, 0, 'Imagens/Comida Portuguesa/feijoada_transmontana.jpg', 'feijoada_transmontana', 1),
(11, 1, 1, 0, 'Imagens/Comida Portuguesa/francesinha.jpg', 'francesinha', 1),
(12, 2, 1, 2, 'Imagens/Comida Portuguesa/Frango Piri-Piri.jfif', 'Frango Piri-Piri', 1),
(13, 0, 0, 0, 'Imagens/Comida Portuguesa/lasanha.jpg', 'lasanha', 1),
(14, 0, 0, 0, 'Imagens/Comida Portuguesa/polvo_Lagareiro.jpg', 'polvo_Lagareiro', 1),
(15, 0, 0, 0, 'Imagens/Comida Portuguesa/RojÃµes.jfif', 'RojÃµes', 1),
(16, 2, 1, 1, 'Imagens/Comida Portuguesa/Sardinhas.jpg', 'Sardinhas', 1),
(17, 1, 0, 0, 'Imagens/Comida Portuguesa/sushi.jpg', 'sushi', 1),
(18, 0, 1, 0, 'Imagens/Comida Portuguesa/Tripas a modo do Porto.jfif', 'Tripas a modo do Porto', 1),
(19, 1, 3, 0, 'Imagens/Filmes/300.jpeg', '300', 2),
(20, 1, 6, 0, 'Imagens/Filmes/Avatar.jpeg', 'Avatar', 2),
(21, 2, 5, 0, 'Imagens/Filmes/Book of Eli.jpeg', 'Book of Eli', 2),
(22, 4, 5, 1, 'Imagens/Filmes/Da Vinci Code.jpeg', 'Da Vinci Code', 2),
(23, 1, 5, 0, 'Imagens/Filmes/Django Unchained.jpeg', 'Django Unchained', 2),
(24, 2, 4, 0, 'Imagens/Filmes/Fight Club.jpeg', 'Fight Club', 2),
(25, 8, 7, 0, 'Imagens/Filmes/Forest Gump.jpeg', 'Forest Gump', 2),
(26, 3, 3, 0, 'Imagens/Filmes/Gladiador.jpeg', 'Gladiador', 2),
(27, 4, 4, 0, 'Imagens/Filmes/Good Will Hunting.jpeg', 'Good Will Hunting', 2),
(28, 5, 4, 0, 'Imagens/Filmes/Hangover.jpeg', 'Hangover', 2),
(29, 5, 4, 0, 'Imagens/Filmes/Inception.jpeg', 'Inception', 2),
(30, 5, 5, 0, 'Imagens/Filmes/Inglorious Basterds.jpeg', 'Inglorious Basterds', 2),
(31, 11, 1, 3, 'Imagens/Filmes/Matrix.jpeg', 'Matrix', 2),
(32, 6, 4, 0, 'Imagens/Filmes/Memento.jpeg', 'Memento', 2),
(33, 4, 3, 1, 'Imagens/Filmes/No Country fol Old men.jpeg', 'No Country fol Old men', 2),
(34, 0, 2, 0, 'Imagens/Filmes/Prometheus.jpeg', 'Prometheus', 2),
(35, 3, 3, 0, 'Imagens/Filmes/Pulp Fiction.jpeg', 'Pulp Fiction', 2),
(36, 3, 1, 0, 'Imagens/Filmes/Saving Private Ryan.jpeg', 'Saving Private Ryan', 2),
(37, 6, 4, 0, 'Imagens/Filmes/Shutter Island.jpeg', 'Shutter Island', 2),
(38, 1, 7, 0, 'Imagens/Filmes/The Dark Knight.jpeg', 'The Dark Knight', 2),
(39, 4, 2, 1, 'Imagens/Bandas de Rock/AC DC.jpg', 'AC DC', 3),
(40, 3, 4, 0, 'Imagens/Bandas de Rock/Aerosmith.jpg', 'Aerosmith', 3),
(41, 6, 2, 0, 'Imagens/Bandas de Rock/Black Sabbath.jpg', 'Black Sabbath', 3),
(42, 4, 1, 0, 'Imagens/Bandas de Rock/Dire Straits.jpg', 'Dire Straits', 3),
(43, 0, 5, 0, 'Imagens/Bandas de Rock/Fleetwood Mac.webp', 'Fleetwood Mac', 3),
(44, 1, 3, 0, 'Imagens/Bandas de Rock/Guns n Roses.jpg', 'Guns n Roses', 3),
(45, 2, 2, 0, 'Imagens/Bandas de Rock/Iron Maiden.jpg', 'Iron Maiden', 3),
(46, 1, 2, 0, 'Imagens/Bandas de Rock/Kiss.jpg', 'Kiss', 3),
(47, 3, 3, 0, 'Imagens/Bandas de Rock/Led Zeppelin.jpg', 'Led Zeppelin', 3),
(48, 0, 2, 0, 'Imagens/Bandas de Rock/Metallica.png', 'Metallica', 3),
(49, 1, 3, 0, 'Imagens/Bandas de Rock/Nirvana.jpg', 'Nirvana', 3),
(50, 0, 4, 0, 'Imagens/Bandas de Rock/Oasis.webp', 'Oasis', 3),
(51, 8, 0, 2, 'Imagens/Bandas de Rock/Pink Floyd.jpg', 'Pink Floyd', 3),
(52, 3, 2, 0, 'Imagens/Bandas de Rock/Queen.jpg', 'Queen', 3),
(53, 1, 4, 0, 'Imagens/Bandas de Rock/RadioHead.png', 'RadioHead', 3),
(54, 1, 3, 0, 'Imagens/Bandas de Rock/Rage Against The Machine.jpg', 'Rage Against The Machine', 3),
(55, 3, 3, 0, 'Imagens/Bandas de Rock/The Beatles.webp', 'The Beatles', 3),
(56, 5, 3, 0, 'Imagens/Bandas de Rock/The Doors.jfif', 'The Doors', 3),
(57, 1, 1, 0, 'Imagens/Bandas de Rock/The Jimi Hendrix Experience.jpg', 'The Jimi Hendrix Experience', 3),
(58, 3, 4, 0, 'Imagens/Bandas de Rock/The Rolling Stones.jpg', 'The Rolling Stones', 3),
(59, 10, 7, 0, 'Imagens/VideoGames/Civ V.jpg', 'Civ V', 4),
(60, 1, 3, 0, 'Imagens/VideoGames/The Last of Us.webp', 'The Last of Us', 4),
(61, 1, 3, 0, 'Imagens/VideoGames/Metal Gear Solid Trilogy.jpg', 'Metal Gear Solid Trilogy', 4),
(62, 1, 2, 0, 'Imagens/VideoGames/Skyrim.jpg', 'Skyrim', 4),
(63, 7, 2, 0, 'Imagens/VideoGames/God of War Trilogy.jpg', 'God of War Trilogy', 4),
(64, 3, 4, 0, 'Imagens/VideoGames/Counter-Strike 1.6.jpg', 'Counter-Strike 1.6', 4),
(65, 0, 4, 0, 'Imagens/VideoGames/Batman Arkham Trilogy.jfif', 'Batman Arkham Trilogy', 4),
(66, 0, 3, 0, 'Imagens/VideoGames/Dishonored.png', 'Dishonored', 4),
(67, 4, 5, 0, 'Imagens/VideoGames/Call of Duty 4 Modern Warfare.jpg', 'Call of Duty 4 Modern Warfare', 4),
(68, 3, 4, 0, 'Imagens/VideoGames/Xcom.jpg', 'Xcom', 4),
(69, 9, 4, 1, 'Imagens/VideoGames/GTA San Andreas.jfif', 'GTA San Andreas', 4),
(70, 2, 2, 0, 'Imagens/VideoGames/League of Legends.jpg', 'League of Legends', 4),
(71, 7, 3, 1, 'Imagens/VideoGames/Fallout NV.jfif', 'Fallout NV', 4),
(72, 4, 6, 0, 'Imagens/VideoGames/Final Fantasy X.webp', 'Final Fantasy X', 4),
(73, 0, 4, 0, 'Imagens/VideoGames/Borderlands.jpg', 'Borderlands', 4),
(74, 1, 4, 0, 'Imagens/VideoGames/Resident Evil 4.jpg', 'Resident Evil 4', 4),
(75, 2, 2, 0, 'Imagens/VideoGames/Dark Souls Trilogy.jpg', 'Dark Souls Trilogy', 4),
(76, 4, 3, 0, 'Imagens/VideoGames/Red Dead Redemption 2.jpg', 'Red Dead Redemption 2', 4),
(77, 0, 4, 0, 'Imagens/VideoGames/Assassin Creed IV Black Flag.jpg', 'Assassin Creed IV Black Flag', 4),
(78, 2, 6, 0, 'Imagens/VideoGames/Minecraft.avif', 'Minecraft', 4),
(79, 2, 4, 0, 'Imagens/VideoGames/The Legend of Zelda Breath of the Wild.jpg', 'The Legend of Zelda Breath of the Wild', 4),
(80, 11, 2, 4, 'Imagens/VideoGames/Mass Effect Trilogy.jpg', 'Mass Effect Trilogy', 4),
(81, 4, 2, 1, 'Imagens/VideoGames/Grand Theft Auto V.jpg', 'Grand Theft Auto V', 4),
(82, 1, 1, 0, 'Imagens/VideoGames/The Witcher 3 Wild Hunt.avif', 'The Witcher 3 Wild Hunt', 4),
(83, 3, 4, 0, 'Imagens/VideoGames/BioShock Trilogy.jpg', 'BioShock Trilogy', 4),
(84, 3, 4, 0, 'Imagens/VideoGames/Portal 2.jpg', 'Portal 2', 4),
(85, 5, 1, 0, 'Imagens/VideoGames/God of War Reboot.avif', 'God of War Reboot', 4),
(86, 1, 5, 0, 'Imagens/VideoGames/Metal Gear Solid V.jpg', 'Metal Gear Solid V', 4),
(87, 0, 2, 0, 'Imagens/Food/Tripas a modo do Porto.jfif', 'Tripas a modo do Porto', 5),
(88, 3, 3, 0, 'Imagens/Food/sushi.jpg', 'sushi', 5),
(89, 0, 2, 0, 'Imagens/Food/Sardinhas.jpg', 'Sardinhas', 5),
(90, 0, 0, 0, 'Imagens/Food/RojÃµes.jfif', 'RojÃµes', 5),
(91, 5, 1, 1, 'Imagens/Food/polvo_Lagareiro.jpg', 'polvo_Lagareiro', 5),
(92, 0, 2, 0, 'Imagens/Food/lasanha.jpg', 'lasanha', 5),
(93, 2, 2, 0, 'Imagens/Food/Frango Piri-Piri.jfif', 'Frango Piri-Piri', 5),
(94, 4, 1, 1, 'Imagens/Food/francesinha.jpg', 'francesinha', 5),
(95, 0, 3, 0, 'Imagens/Food/Dobrada.jpg', 'Dobrada', 5),
(96, 0, 2, 0, 'Imagens/Food/feijoada_transmontana.jpg', 'feijoada_transmontana', 5),
(97, 1, 1, 0, 'Imagens/Food/cozido.jpg', 'cozido', 5),
(98, 4, 5, 0, 'Imagens/Food/cheeseburger.jpg', 'cheeseburger', 5),
(99, 0, 1, 0, 'Imagens/Food/bife.jpg', 'bife', 5),
(100, 2, 2, 0, 'Imagens/Food/bacalhau_braz.jpg', 'bacalhau_braz', 5),
(101, 2, 2, 0, 'Imagens/Food/alheira.jpg', 'alheira', 5),
(102, 0, 1, 0, 'Imagens/Food/Arroz de cabidelas.jfif', 'Arroz de cabidelas', 5),
(103, 0, 2, 0, 'Imagens/Food/arroz_pato.jpg', 'arroz_pato', 5),
(104, 0, 0, 0, 'Imagens/Food/Bacalhau Ã  Lagareiro.jpg', 'Bacalhau Ã  Lagareiro', 5),
(105, 0, 0, 0, 'Imagens/Food/Kebab.jpg', 'Kebab', 5),
(106, 0, 1, 0, 'Imagens/Food/Paella de marisco.jpeg', 'Paella de marisco', 5),
(107, 0, 1, 0, 'Imagens/Food/Pad Thai.webp', 'Pad Thai', 5),
(108, 0, 1, 0, 'Imagens/Food/Bolonhesa.webp', 'Bolonhesa', 5),
(109, 0, 0, 0, 'Imagens/Food/carne de porco Ã  alentejana.jpg', 'carne de porco Ã  alentejana', 5),
(110, 1, 2, 0, 'Imagens/Food/Carbonara.jpg', 'Carbonara', 5),
(111, 1, 0, 0, 'Imagens/Food/Tacos.jpg', 'Tacos', 5),
(112, 4, 2, 0, 'Imagens/Food/Ramen.jpg', 'Ramen', 5),
(114, 0, 0, 0, 'Imagens/GUELGAY/CC_Back.jpeg', 'CC_Back', 6),
(115, 0, 0, 0, 'Imagens/GUELGAY/CC_Front.jpeg', 'CC_Front', 6);

-- --------------------------------------------------------

--
-- Table structure for table `tema`
--

CREATE TABLE `tema` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) NOT NULL,
  `utilizadorId` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tema`
--

INSERT INTO `tema` (`id`, `nome`, `utilizadorId`) VALUES
(5, 'Food', 2),
(2, 'Filmes', 2),
(3, 'Bandas de Rock', 2),
(4, 'VideoGames', 2);

-- --------------------------------------------------------

--
-- Table structure for table `utilizador`
--

CREATE TABLE `utilizador` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `utilizador`
--

INSERT INTO `utilizador` (`id`, `username`, `password`) VALUES
(1, 'teste3', '123'),
(2, 'admin', '12345');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `competidor`
--
ALTER TABLE `competidor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `TemaId` (`TemaId`);

--
-- Indexes for table `tema`
--
ALTER TABLE `tema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_utilizador` (`utilizadorId`);

--
-- Indexes for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `competidor`
--
ALTER TABLE `competidor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `tema`
--
ALTER TABLE `tema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
