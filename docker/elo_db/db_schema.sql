-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 10 Lis 2018, 16:31
-- Wersja serwera: 10.1.34-MariaDB
-- Wersja PHP: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `elo_app`
--

CREATE DATABASE IF NOT EXISTS elo_app;
USE elo_app;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `games`
--

CREATE TABLE `games` (
  `game_nid` int(11) NOT NULL,
  `winner_user_nid` int(11) NOT NULL,
  `looser_user_nid` int(11) NOT NULL,
  `winner_rating_before` int(11) NOT NULL,
  `looser_rating_before` int(11) NOT NULL,
  `rating_diff` int(11) NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `user_nid` int(11) NOT NULL,
  `code` varchar(3) NOT NULL,
  `name` varchar(45) NOT NULL,
  `team` varchar(45) NOT NULL,
  `rating` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `cdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_nid`),
  ADD UNIQUE KEY `winner_user_nid_2` (`winner_user_nid`,`looser_user_nid`,`cdate`),
  ADD KEY `winner_user_nid` (`winner_user_nid`),
  ADD KEY `looser_user_nid` (`looser_user_nid`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_nid`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `games`
--
ALTER TABLE `games`
  MODIFY `game_nid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
  MODIFY `user_nid` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`winner_user_nid`) REFERENCES `users` (`user_nid`) ON DELETE CASCADE,
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`looser_user_nid`) REFERENCES `users` (`user_nid`) ON DELETE CASCADE;
COMMIT;

CREATE TABLE `password` (
  `password` varchar(40) NOT NULL,
  PRIMARY KEY (`password`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `password` (`password`) VALUES
('cd798c453817754e7fab67c9572fc9d3182a2ee2');
