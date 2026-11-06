-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Pát 06. lis 2026, 12:48
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `projekt`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `mistnosti`
--

CREATE TABLE `mistnosti` (
  `zkratka_mistnosti` varchar(5) NOT NULL,
  `popis` varchar(30) NOT NULL,
  `kapacita` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `mistnosti`
--

INSERT INTO `mistnosti` (`zkratka_mistnosti`, `popis`, `kapacita`) VALUES
('A101', 'Pocitacova ucebna', 25),
('A102', 'Prednaskova mistnost', 40),
('B201', 'Seminarni mistnost', 20),
('B202', 'Pocitacova laborat', 30),
('C101', 'Velka ucebna', 50);

-- --------------------------------------------------------

--
-- Struktura tabulky `pedagogove`
--

CREATE TABLE `pedagogove` (
  `kod_pedagoga` varchar(20) NOT NULL,
  `jmeno` varchar(30) NOT NULL,
  `prijmeni` varchar(30) NOT NULL,
  `tituly_pred_jmenem` varchar(20) NOT NULL,
  `tituly_za_jmenem` varchar(20) NOT NULL,
  `heslo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `pedagogove`
--

INSERT INTO `pedagogove` (`kod_pedagoga`, `jmeno`, `prijmeni`, `tituly_pred_jmenem`, `tituly_za_jmenem`, `heslo`) VALUES
('P001', 'Jan', 'Novák', 'Ing.', 'Ph.D.', '$2y$10$bN0yfTmcdoDTlowZAj7KqueC1gMmjCItzXtoabdYFF/qjZJbOCQ3u'),
('P002', 'Petr', 'Svoboda', 'Mgr.', '', '$2y$10$bN0yfTmcdoDTlowZAj7KqueC1gMmjCItzXtoabdYFF/qjZJbOCQ3u'),
('P003', 'Martin', 'Dvořák', 'Ing.', '', '$2y$10$bN0yfTmcdoDTlowZAj7KqueC1gMmjCItzXtoabdYFF/qjZJbOCQ3u'),
('P004', 'Tomáš', 'Černý', 'Mgr.', 'Ph.D.', '$2y$10$bN0yfTmcdoDTlowZAj7KqueC1gMmjCItzXtoabdYFF/qjZJbOCQ3u'),
('P005', 'Pavel', 'Procházka', 'Ing.', '', '$2y$10$bN0yfTmcdoDTlowZAj7KqueC1gMmjCItzXtoabdYFF/qjZJbOCQ3u');

-- --------------------------------------------------------

--
-- Struktura tabulky `pedagogove_predmety`
--

CREATE TABLE `pedagogove_predmety` (
  `kod_pedagoga` varchar(20) NOT NULL,
  `zkratka_predmetu` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `pedagogove_predmety`
--

INSERT INTO `pedagogove_predmety` (`kod_pedagoga`, `zkratka_predmetu`) VALUES
('P001', 'ALG'),
('P001', 'DB'),
('P002', 'PRG'),
('P003', 'SIT'),
('P004', 'WEB'),
('P005', 'DB'),
('P005', 'PRG');

-- --------------------------------------------------------

--
-- Struktura tabulky `predmety`
--

CREATE TABLE `predmety` (
  `zkratka_predmetu` varchar(5) NOT NULL,
  `nazev` varchar(50) NOT NULL,
  `pocet_kreditu` smallint(6) NOT NULL,
  `pocet_hodin_prednasek` smallint(6) NOT NULL,
  `pocet_hodin_cviceni` smallint(6) NOT NULL,
  `ukonceni` varchar(2) NOT NULL,
  `anotace` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `predmety`
--

INSERT INTO `predmety` (`zkratka_predmetu`, `nazev`, `pocet_kreditu`, `pocet_hodin_prednasek`, `pocet_hodin_cviceni`, `ukonceni`, `anotace`) VALUES
('ALG', 'Algoritmy a datove struktury', 5, 2, 2, 'ZK', 'Algoritmy a datové struktury.'),
('DB', 'Databazove systemy', 5, 2, 2, 'ZK', 'Návrh a správa databází.'),
('PRG', 'Programovani', 5, 2, 2, 'ZK', 'Základy programování a algoritmizace.'),
('SIT', 'Pocitacove site', 4, 2, 2, 'ZP', 'Základy počítačových sítí.'),
('WEB', 'Webove technologie', 4, 2, 2, 'ZP', 'Tvorba webových aplikací.');

-- --------------------------------------------------------

--
-- Struktura tabulky `studenti`
--

CREATE TABLE `studenti` (
  `kod_studenta` varchar(10) NOT NULL,
  `jmeno` varchar(30) NOT NULL,
  `prijmeni` varchar(30) NOT NULL,
  `heslo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `studenti`
--

INSERT INTO `studenti` (`kod_studenta`, `jmeno`, `prijmeni`, `heslo`) VALUES
('S000000001', 'Anna', 'Novotná', '$2y$10$nzTJMbEbXAqrPSLO2hnVHeGVnYtsI5ba8Q4HJ..V/9YRl7nPNFds2'),
('S000000002', 'Jan', 'Svoboda', '$2y$10$nzTJMbEbXAqrPSLO2hnVHeGVnYtsI5ba8Q4HJ..V/9YRl7nPNFds2'),
('S000000003', 'Petr', 'Dvořák', '$2y$10$nzTJMbEbXAqrPSLO2hnVHeGVnYtsI5ba8Q4HJ..V/9YRl7nPNFds2'),
('S000000004', 'Eva', 'Černá', '$2y$10$nzTJMbEbXAqrPSLO2hnVHeGVnYtsI5ba8Q4HJ..V/9YRl7nPNFds2'),
('S000000005', 'Martin', 'Procházka', '$2y$10$nzTJMbEbXAqrPSLO2hnVHeGVnYtsI5ba8Q4HJ..V/9YRl7nPNFds2');

-- --------------------------------------------------------

--
-- Struktura tabulky `studenti_predmety`
--

CREATE TABLE `studenti_predmety` (
  `kod_studenta` varchar(10) NOT NULL,
  `zkratka_predmetu` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `studenti_predmety`
--

INSERT INTO `studenti_predmety` (`kod_studenta`, `zkratka_predmetu`) VALUES
('S000000001', 'ALG'),
('S000000001', 'DB'),
('S000000002', 'PRG'),
('S000000002', 'WEB'),
('S000000003', 'ALG'),
('S000000003', 'SIT'),
('S000000004', 'DB'),
('S000000004', 'WEB'),
('S000000005', 'PRG'),
('S000000005', 'SIT');

-- --------------------------------------------------------

--
-- Struktura tabulky `vypsane_terminy`
--

CREATE TABLE `vypsane_terminy` (
  `id_terminu` int(11) NOT NULL,
  `zkratka_mistnosti` varchar(5) NOT NULL,
  `kod_pedagoga` varchar(20) NOT NULL,
  `zkratka_predmetu` varchar(5) NOT NULL,
  `datum_cas` datetime NOT NULL,
  `max_pocet_prihlasenych` smallint(6) NOT NULL,
  `poznamka` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `vypsane_terminy`
--

INSERT INTO `vypsane_terminy` (`id_terminu`, `zkratka_mistnosti`, `kod_pedagoga`, `zkratka_predmetu`, `datum_cas`, `max_pocet_prihlasenych`, `poznamka`) VALUES
(10, 'C101', 'P001', 'ALG', '2026-11-06 07:10:00', 50, 'Vezměte si vlastní papíry'),
(11, 'A101', 'P001', 'DB', '2026-11-10 12:20:00', 25, ''),
(12, 'A101', 'P002', 'PRG', '2026-11-24 12:24:00', 25, ''),
(13, 'B202', 'P003', 'SIT', '2026-11-13 13:30:00', 5, ''),
(14, 'A101', 'P004', 'WEB', '2026-11-06 10:35:00', 3, ''),
(15, 'A101', 'P004', 'WEB', '2026-11-06 03:33:00', 2, ''),
(16, 'B201', 'P005', 'DB', '2026-11-05 22:33:00', 2, ''),
(17, 'A102', 'P005', 'PRG', '2026-11-06 02:33:00', 3, 'Vlastní notebook!'),
(18, 'A101', 'P005', 'DB', '2026-11-06 01:35:00', 2, '');

-- --------------------------------------------------------

--
-- Struktura tabulky `vysledky`
--

CREATE TABLE `vysledky` (
  `id_vysledku` smallint(6) NOT NULL,
  `popis` varchar(20) NOT NULL,
  `typ` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `vysledky`
--

INSERT INTO `vysledky` (`id_vysledku`, `popis`, `typ`) VALUES
(1, 'Vyborny', 'ZK'),
(2, 'Velmi dobre', 'ZK'),
(3, 'Dobre', 'ZK'),
(4, 'Neprospel', 'ZK'),
(5, 'Prospel', 'ZP'),
(6, 'Neprospel', 'ZP');

-- --------------------------------------------------------

--
-- Struktura tabulky `zapsane_terminy`
--

CREATE TABLE `zapsane_terminy` (
  `id_terminu` int(11) NOT NULL,
  `kod_studenta` varchar(10) NOT NULL,
  `id_vysledku` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `zapsane_terminy`
--

INSERT INTO `zapsane_terminy` (`id_terminu`, `kod_studenta`, `id_vysledku`) VALUES
(10, 'S000000001', 1),
(10, 'S000000003', 4),
(13, 'S000000003', NULL),
(13, 'S000000005', NULL),
(15, 'S000000002', NULL),
(15, 'S000000004', NULL),
(16, 'S000000001', NULL),
(16, 'S000000004', NULL),
(17, 'S000000002', NULL),
(17, 'S000000005', NULL);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `mistnosti`
--
ALTER TABLE `mistnosti`
  ADD PRIMARY KEY (`zkratka_mistnosti`);

--
-- Indexy pro tabulku `pedagogove`
--
ALTER TABLE `pedagogove`
  ADD PRIMARY KEY (`kod_pedagoga`);

--
-- Indexy pro tabulku `pedagogove_predmety`
--
ALTER TABLE `pedagogove_predmety`
  ADD PRIMARY KEY (`kod_pedagoga`,`zkratka_predmetu`),
  ADD KEY `zkratka_predmetu` (`zkratka_predmetu`);

--
-- Indexy pro tabulku `predmety`
--
ALTER TABLE `predmety`
  ADD PRIMARY KEY (`zkratka_predmetu`);

--
-- Indexy pro tabulku `studenti`
--
ALTER TABLE `studenti`
  ADD PRIMARY KEY (`kod_studenta`);

--
-- Indexy pro tabulku `studenti_predmety`
--
ALTER TABLE `studenti_predmety`
  ADD PRIMARY KEY (`kod_studenta`,`zkratka_predmetu`),
  ADD KEY `zkratka_predmetu` (`zkratka_predmetu`);

--
-- Indexy pro tabulku `vypsane_terminy`
--
ALTER TABLE `vypsane_terminy`
  ADD PRIMARY KEY (`id_terminu`),
  ADD KEY `id_terminu` (`id_terminu`,`zkratka_mistnosti`,`kod_pedagoga`,`zkratka_predmetu`,`datum_cas`,`max_pocet_prihlasenych`,`poznamka`),
  ADD KEY `zkratka_mistnosti` (`zkratka_mistnosti`),
  ADD KEY `zkratka_predmetu` (`zkratka_predmetu`),
  ADD KEY `kod_pedagoga` (`kod_pedagoga`);

--
-- Indexy pro tabulku `vysledky`
--
ALTER TABLE `vysledky`
  ADD PRIMARY KEY (`id_vysledku`);

--
-- Indexy pro tabulku `zapsane_terminy`
--
ALTER TABLE `zapsane_terminy`
  ADD PRIMARY KEY (`id_terminu`,`kod_studenta`),
  ADD KEY `kod_studenta` (`kod_studenta`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `vypsane_terminy`
--
ALTER TABLE `vypsane_terminy`
  MODIFY `id_terminu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `pedagogove_predmety`
--
ALTER TABLE `pedagogove_predmety`
  ADD CONSTRAINT `pedagogove_predmety_ibfk_1` FOREIGN KEY (`kod_pedagoga`) REFERENCES `pedagogove` (`kod_pedagoga`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pedagogove_predmety_ibfk_2` FOREIGN KEY (`zkratka_predmetu`) REFERENCES `predmety` (`zkratka_predmetu`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `studenti_predmety`
--
ALTER TABLE `studenti_predmety`
  ADD CONSTRAINT `studenti_predmety_ibfk_1` FOREIGN KEY (`zkratka_predmetu`) REFERENCES `predmety` (`zkratka_predmetu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `studenti_predmety_ibfk_2` FOREIGN KEY (`kod_studenta`) REFERENCES `studenti` (`kod_studenta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `vypsane_terminy`
--
ALTER TABLE `vypsane_terminy`
  ADD CONSTRAINT `vypsane_terminy_ibfk_1` FOREIGN KEY (`zkratka_predmetu`) REFERENCES `predmety` (`zkratka_predmetu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vypsane_terminy_ibfk_2` FOREIGN KEY (`kod_pedagoga`) REFERENCES `pedagogove` (`kod_pedagoga`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vypsane_terminy_ibfk_3` FOREIGN KEY (`zkratka_mistnosti`) REFERENCES `mistnosti` (`zkratka_mistnosti`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `zapsane_terminy`
--
ALTER TABLE `zapsane_terminy`
  ADD CONSTRAINT `zapsane_terminy_ibfk_1` FOREIGN KEY (`kod_studenta`) REFERENCES `studenti` (`kod_studenta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `zapsane_terminy_ibfk_2` FOREIGN KEY (`id_terminu`) REFERENCES `vypsane_terminy` (`id_terminu`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
