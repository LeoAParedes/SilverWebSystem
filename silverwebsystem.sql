-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2025 at 03:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `silverwebsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `design`
--

CREATE TABLE `design` (
  `designid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `unit_launch_price` decimal(10,2) DEFAULT NULL,
  `category` text DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `design`
--

INSERT INTO `design` (`designid`, `name`, `creation_date`, `description`, `edition`, `unit_launch_price`, `category`, `size`) VALUES
(4, 'Flor de loto', '2025-01-08 08:00:00', '24 Segmentos de una Flor de Loto que muestran una dinámica de cambio para activar la curación con una triquetra en el centro.\n', '1', 300.00, 'parche', '6cm'),
(11, 'Piramide fuerte', '2025-01-08 08:00:00', 'Piramide de concentracion de la fuerza interior', '1', 300.00, 'parche', '6cm'),
(14, 'DeltaPrism', '2025-01-08 08:00:00', 'Vision CMYK de DeltaStar. Diseñado en Mexicali por Leonardo Paredes.', '1', 300.00, 'Parche', '6cm'),
(15, 'Corona', '2025-01-02 08:00:00', 'Corona de la divinidad natural. El simbolo del trono real.', '1', 300.00, 'Parche', '6cm'),
(16, 'Anillo de Fuego', '2025-01-09 08:00:00', 'Anillo de la protección, El vinculo con un guardian es un pacto con fuego.', '1', 250.00, 'Parche', '6cm'),
(17, 'Estrella del Norte', '2025-01-09 08:00:00', 'Guía en el cielo de un nuevo comienzo. Polo de concentración positivo.', '1', 300.00, 'Parche', '6cm');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `designid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `image_path`, `name`, `designid`) VALUES
(9, '/../../app/assets/img/Amor Cuadrado.png', 'Piramide fuerte', 11),
(10, '/../../app/assets/img/florLotoFull.png', 'Flor de loto', 4),
(11, '/app/assets/img/png delta color.png', 'DeltaPrism', 14),
(12, '/app/assets/img/Corona.png', 'Corona', 15),
(13, '/app/assets/img/AnilloOriginal.png', 'Anillo de Fuego', 16),
(14, '/app/assets/img/EstrellaDelNorte.png', 'Estrella del Norte', 17);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `image` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `image`, `created_at`) VALUES
(1, '123', '$2y$10$2TehUsam7GZvC4E/h5r2EeduoJKQkP0VCp4lx4EbfvEMvNIYDlZby', '', NULL, '2025-01-06 01:28:48');

-- --------------------------------------------------------

--
-- Table structure for table `userwishlist`
--

CREATE TABLE `userwishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `wishlist` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `designid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userwishlist`
--

INSERT INTO `userwishlist` (`id`, `user_id`, `wishlist`, `created_at`, `designid`) VALUES
(1, 1, 1, '2025-01-10 04:05:28', 11),
(2, 1, 1, '2025-01-10 04:43:59', 15),
(3, 1, 1, '2025-01-10 04:44:03', 17),
(4, 1, 1, '2025-01-28 18:16:12', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `design`
--
ALTER TABLE `design`
  ADD PRIMARY KEY (`designid`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_design` (`designid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `userwishlist`
--
ALTER TABLE `userwishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `iddesign` (`designid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `design`
--
ALTER TABLE `design`
  MODIFY `designid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `userwishlist`
--
ALTER TABLE `userwishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `fk_design` FOREIGN KEY (`designid`) REFERENCES `design` (`designid`);

--
-- Constraints for table `userwishlist`
--
ALTER TABLE `userwishlist`
  ADD CONSTRAINT `iddesign` FOREIGN KEY (`designid`) REFERENCES `design` (`designid`),
  ADD CONSTRAINT `userwishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
