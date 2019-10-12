# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Hôte: 127.0.0.1 (MySQL 10.0.14-MariaDB-4)
# Base de données: directory
# Temps de génération: 2015-04-16 10:54:30 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

# Création de la base de données
# ------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `directory`;
USE `directory`;

# Affichage de la table addresses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `addresses`;

CREATE TABLE `addresses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('Domicile','Bureau') CHARACTER SET utf8 NOT NULL DEFAULT 'Domicile',
  `street` text CHARACTER SET utf8,
  `zip` int(11) DEFAULT NULL,
  `city` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `contact_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;

INSERT INTO `addresses` (`id`, `type`, `street`, `zip`, `city`, `contact_id`)
VALUES
	(1,'Domicile','92 rue de Lausanne\r\nAppartement 631',76000,'Rouen',1);

/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table contacts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `contacts`;

CREATE TABLE `contacts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `type` enum('Individu','Société') CHARACTER SET utf8 NOT NULL DEFAULT 'Individu',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;

INSERT INTO `contacts` (`id`, `first_name`, `last_name`, `type`)
VALUES
	(1,'Camille','Terol','Individu');

/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table emails
# ------------------------------------------------------------

DROP TABLE IF EXISTS `emails`;

CREATE TABLE `emails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('Personnel','Professionnel') CHARACTER SET utf8 NOT NULL DEFAULT 'Personnel',
  `email` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `contact_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `emails_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

LOCK TABLES `emails` WRITE;
/*!40000 ALTER TABLE `emails` DISABLE KEYS */;

INSERT INTO `emails` (`id`, `type`, `email`, `contact_id`)
VALUES
	(1,'Personnel','camille@terol.fr',1),
	(2,'Professionnel','camille@cdm.io',1);

/*!40000 ALTER TABLE `emails` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table phones
# ------------------------------------------------------------

DROP TABLE IF EXISTS `phones`;

CREATE TABLE `phones` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('Domicile','Bureau','Mobile','Mobile Professionnel') CHARACTER SET utf8 NOT NULL DEFAULT 'Domicile',
  `number` varchar(13) CHARACTER SET utf8 DEFAULT NULL,
  `country_code` varchar(5) CHARACTER SET utf8 DEFAULT '+33',
  `contact_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `phones_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

LOCK TABLES `phones` WRITE;
/*!40000 ALTER TABLE `phones` DISABLE KEYS */;

INSERT INTO `phones` (`id`, `type`, `number`, `country_code`, `contact_id`)
VALUES
	(1,'Domicile','0278714645','+33',1),
	(2,'Mobile','0685445893','+33',1);

/*!40000 ALTER TABLE `phones` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `hash` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `nickname` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `username`, `hash`, `nickname`)
VALUES
	(1,'admin','$2y$10$M1IHDx8EROVFwlDI4SpPTOnAA7gY2/N37j3UB6pm9ifwiBrL7uY7.','Administrateur');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
