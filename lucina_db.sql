-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: lucina_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `lucina_db`
--

/*!40000 DROP DATABASE IF EXISTS `lucina_db`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `lucina_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `lucina_db`;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `cli_id` int(11) NOT NULL AUTO_INCREMENT,
  `cli_nombre` varchar(100) NOT NULL,
  `cli_nif` varchar(20) DEFAULT NULL,
  `cli_telefono` varchar(20) DEFAULT NULL,
  `cli_email` varchar(150) DEFAULT NULL,
  `cli_poblacion` varchar(100) DEFAULT NULL,
  `cli_provincia` varchar(100) DEFAULT NULL,
  `cli_direccion` varchar(255) DEFAULT NULL,
  `cli_observaciones` mediumtext DEFAULT NULL,
  `cli_activo` tinyint(1) DEFAULT 1,
  `cli_fecha_alta` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`cli_id`),
  UNIQUE KEY `cli_nif` (`cli_nif`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Usuario Demo',NULL,NULL,'demo@lucina.es','Madrid',NULL,NULL,NULL,1,CURRENT_TIMESTAMP);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companias`
--

DROP TABLE IF EXISTS `companias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companias` (
  `comp_id` int(11) NOT NULL AUTO_INCREMENT,
  `comp_nombre` varchar(100) NOT NULL,
  `comp_logo_url` varchar(255) DEFAULT NULL,
  `comp_observaciones` mediumtext DEFAULT NULL,
  `comp_activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`comp_id`),
  UNIQUE KEY `comp_nombre` (`comp_nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companias`
--

LOCK TABLES `companias` WRITE;
/*!40000 ALTER TABLE `companias` DISABLE KEYS */;
INSERT INTO `companias` VALUES (2,'Gana Energía','https://vaporeta.ganaenergia.com/gana-web/v1/gana-logo.svg','',1),(3,'Naturgy','https://stproportalcorporativo.blob.core.windows.net/uploads/2022/10/logo-naturgy.svg',NULL,1);
/*!40000 ALTER TABLE `companias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facturas` (
  `fac_id` int(11) NOT NULL AUTO_INCREMENT,
  `fac_user_id` int(11) NOT NULL,
  `fac_cli_id` int(11) NOT NULL,
  `fac_cups` varchar(30) NOT NULL,
  `fac_poblacion` varchar(100) DEFAULT NULL,
  `fac_provincia` varchar(100) DEFAULT NULL,
  `fac_ruta_archivo` varchar(255) NOT NULL,
  `fac_consumo_p1_kwh` decimal(10,2) DEFAULT NULL,
  `fac_consumo_p2_kwh` decimal(10,2) DEFAULT NULL,
  `fac_consumo_p3_kwh` decimal(10,2) DEFAULT NULL,
  `fac_potencia_contratada_p1_kw` decimal(5,2) DEFAULT NULL,
  `fac_potencia_contratada_p2_kw` decimal(5,2) DEFAULT NULL,
  `fac_importe_total_factura` decimal(10,2) DEFAULT NULL,
  `fac_impuesto_electrico_importe` decimal(10,2) DEFAULT NULL,
  `fac_alquiler_contador_importe` decimal(10,2) DEFAULT NULL,
  `fac_iva_porcentaje` int(11) DEFAULT NULL,
  `fac_tiene_bono_social` tinyint(1) DEFAULT NULL,
  `fac_excedentes_kwh` decimal(10,2) DEFAULT NULL,
  `fac_observaciones` mediumtext DEFAULT NULL,
  `fac_fecha` datetime DEFAULT current_timestamp(),
  `fac_activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`fac_id`),
  KEY `fk_fac_user` (`fac_user_id`),
  KEY `fk_fac_cli` (`fac_cli_id`),
  CONSTRAINT `fk_fac_cli` FOREIGN KEY (`fac_cli_id`) REFERENCES `clientes` (`cli_id`),
  CONSTRAINT `fk_fac_user` FOREIGN KEY (`fac_user_id`) REFERENCES `usuarios` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
INSERT INTO `facturas` VALUES (1,1,1,'ES0021000000000001AA',NULL,NULL,'demo/factura_demo_1.pdf',145.20,98.50,0.00,4.60,4.60,87.32,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-12 15:28:39',1),(2,1,1,'ES0021000000000001AA',NULL,NULL,'demo/factura_demo_2.pdf',178.40,115.30,0.00,4.60,4.60,102.15,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-12 15:28:39',1),(3,1,1,'ES0021000000000001AA',NULL,NULL,'demo/factura_demo_3.pdf',150.50,120.30,0.00,4.60,4.60,95.50,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-12 15:28:39',1);
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tarifas`
--

DROP TABLE IF EXISTS `tarifas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifas` (
  `tar_id` int(11) NOT NULL AUTO_INCREMENT,
  `tar_comp_id` int(11) NOT NULL,
  `tar_nombre_tarifa` varchar(100) NOT NULL,
  `tar_fecha_actualizacion` date DEFAULT NULL,
  `tar_precio_energia_p1` decimal(10,6) DEFAULT NULL,
  `tar_precio_energia_p2` decimal(10,6) DEFAULT NULL,
  `tar_precio_energia_p3` decimal(10,6) DEFAULT NULL,
  `tar_precio_potencia_p1` decimal(10,6) DEFAULT NULL,
  `tar_precio_potencia_p2` decimal(10,6) DEFAULT NULL,
  `tar_observaciones` mediumtext DEFAULT NULL,
  `tar_activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`tar_id`),
  KEY `fk_tar_comp` (`tar_comp_id`),
  CONSTRAINT `fk_tar_comp` FOREIGN KEY (`tar_comp_id`) REFERENCES `companias` (`comp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifas`
--

LOCK TABLES `tarifas` WRITE;
/*!40000 ALTER TABLE `tarifas` DISABLE KEYS */;
INSERT INTO `tarifas` VALUES (3,2,'Tarifa 24 Horas','2026-04-12',0.129000,0.129000,0.129000,0.089434,0.089434,NULL,1),(4,2,'Tarifa Tramos Horarios','2026-04-12',0.181000,0.114000,0.090000,0.089434,0.089434,NULL,1),(5,3,'Tarifa Por Uso Luz','2026-04-12',0.109900,0.109900,0.109900,0.123030,0.037337,NULL,1),(6,3,'Tarifa Noche','2026-04-12',0.180200,0.107200,0.071800,0.123030,0.037337,NULL,1);
/*!40000 ALTER TABLE `tarifas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_nombre_completo` varchar(100) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `user_user` varchar(50) DEFAULT NULL,
  `user_password_hash` varchar(255) NOT NULL,
  `user_activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  UNIQUE KEY `user_user` (`user_user`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','admin@lucina.es','admin','$2y$10$DuQIULhArMz9y4zkQivWju4RqecQlig1lRiA4oxFkx3hR5BkN27nW',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-12 15:28:39
