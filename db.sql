-- phpMyAdmin SQL Dump
-- version 4.2.10
-- http://www.phpmyadmin.net
--
-- Počítač: localhost:8889
-- Vytvořeno: Ned 05. dub 2015, 13:35
-- Verze serveru: 5.5.38
-- Verze PHP: 5.6.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Databáze: `bugs-nette-skeleton`
--
CREATE DATABASE IF NOT EXISTS `bugs-nette-skeleton` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `bugs-nette-skeleton`;

-- --------------------------------------------------------

--
-- Struktura tabulky `albums`
--

DROP TABLE IF EXISTS `albums`;
CREATE TABLE `albums` (
`id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `photo` int(11) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `addedBy` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedBy` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
`id` int(11) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `date` datetime NOT NULL,
  `heading` varchar(30) NOT NULL,
  `text` text NOT NULL,
  `inserted` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `insertedBy` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedBy` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
`id` int(11) NOT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `owner` int(11) NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uploadedBy` int(11) NOT NULL,
  `group` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `forum`
--

DROP TABLE IF EXISTS `forum`;
CREATE TABLE `forum` (
`id` int(11) NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `inserted` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `insertedBy` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedBy` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
`id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `photo` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `inserted` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `insertedBy` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedBy` int(11) NOT NULL,
  `lang` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
`id` int(11) NOT NULL,
  `presenter` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `lang` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `contents` text COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `photos`
--

DROP TABLE IF EXISTS `photos`;
CREATE TABLE `photos` (
`id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `album` int(11) NOT NULL,
  `extension` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uploadedBy` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
`id` int(11) NOT NULL,
  `username` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `surname` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `albums`
--
ALTER TABLE `albums`
 ADD PRIMARY KEY (`id`), ADD KEY `photo` (`photo`), ADD KEY `updatedBy` (`updatedBy`);

--
-- Klíče pro tabulku `events`
--
ALTER TABLE `events`
 ADD PRIMARY KEY (`id`), ADD KEY `insertedBy` (`insertedBy`), ADD KEY `updatedBy` (`updatedBy`);

--
-- Klíče pro tabulku `files`
--
ALTER TABLE `files`
 ADD PRIMARY KEY (`id`), ADD KEY `files_ibfk_1` (`uploadedBy`), ADD KEY `files_ibfk_2` (`owner`);

--
-- Klíče pro tabulku `forum`
--
ALTER TABLE `forum`
 ADD PRIMARY KEY (`id`), ADD KEY `forum_ibfk_1` (`updatedBy`), ADD KEY `forum_ibfk_2` (`insertedBy`), ADD KEY `forum_ibfk_3` (`parent`);

--
-- Klíče pro tabulku `news`
--
ALTER TABLE `news`
 ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `pages`
--
ALTER TABLE `pages`
 ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `photos`
--
ALTER TABLE `photos`
 ADD PRIMARY KEY (`id`), ADD KEY `album` (`album`), ADD KEY `uploadedBy` (`uploadedBy`);

--
-- Klíče pro tabulku `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `albums`
--
ALTER TABLE `albums`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT pro tabulku `events`
--
ALTER TABLE `events`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT pro tabulku `files`
--
ALTER TABLE `files`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT pro tabulku `forum`
--
ALTER TABLE `forum`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=81;
--
-- AUTO_INCREMENT pro tabulku `news`
--
ALTER TABLE `news`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT pro tabulku `pages`
--
ALTER TABLE `pages`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=46;
--
-- AUTO_INCREMENT pro tabulku `photos`
--
ALTER TABLE `photos`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=34;
--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=56;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `albums`
--
ALTER TABLE `albums`
ADD CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`photo`) REFERENCES `photos` (`id`),
ADD CONSTRAINT `albums_ibfk_2` FOREIGN KEY (`updatedBy`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `events`
--
ALTER TABLE `events`
ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`insertedBy`) REFERENCES `users` (`id`),
ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`updatedBy`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `files`
--
ALTER TABLE `files`
ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`uploadedBy`) REFERENCES `users` (`id`),
ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`owner`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `forum`
--
ALTER TABLE `forum`
ADD CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`updatedBy`) REFERENCES `users` (`id`),
ADD CONSTRAINT `forum_ibfk_2` FOREIGN KEY (`insertedBy`) REFERENCES `users` (`id`),
ADD CONSTRAINT `forum_ibfk_3` FOREIGN KEY (`parent`) REFERENCES `forum` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `photos`
--
ALTER TABLE `photos`
ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`album`) REFERENCES `albums` (`id`),
ADD CONSTRAINT `photos_ibfk_2` FOREIGN KEY (`uploadedBy`) REFERENCES `users` (`id`);
