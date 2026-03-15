-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: clinica
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
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `accion` varchar(50) NOT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `detalle` text DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (1,'RESTABLECIMIENTO_COMPLETO','superadmin','2025-12-13 18:59:52','Restablecimiento completo ejecutado el 2025-12-13 14:59:52'),(2,'crear departamento','superadmin','2025-12-13 19:00:59','Departamento: Almacén'),(3,'crear trabajador','superadmin','2025-12-13 19:01:44','ID: 2, Nombre: Almacenista Hernández, Cédula: 2132142'),(4,'crear trabajador','superadmin','2025-12-13 19:09:29','ID: 3, Nombre: Gerente Hernandez, Cédula: 21321312'),(5,'Creacion de usuario','Almacenista','2025-12-13 19:09:52','Usuario creado con ID trabajador: 2'),(6,'crear trabajador','superadmin','2025-12-13 19:10:22','ID: 4, Nombre: Supervisor Hernández, Cédula: 21234324'),(7,'Creacion de usuario','Supervisor','2025-12-13 19:10:53','Usuario creado con ID trabajador: 4'),(8,'Cierre de Sesion','superadmin','2025-12-13 19:12:34',NULL),(9,'Inicio de Sesion','superadmin','2025-12-13 19:12:54',NULL),(10,'crear trabajador','superadmin','2025-12-13 19:15:21','ID: 5, Nombre: Ejemplo Plomería, Cédula: 3423434'),(11,'Creacion de usuario','Obrero','2025-12-13 19:15:52','Usuario creado con ID trabajador: 5'),(12,'Cierre de Sesion','superadmin','2025-12-13 20:05:23',NULL),(13,'Inicio de Sesion','Almacenista','2025-12-13 20:05:43',NULL),(14,'crear incidencia','Almacenista','2025-12-13 20:06:24','ID: 1, Descripción: Se rompió un tubo'),(15,'Cierre de Sesion','Almacenista','2025-12-13 20:06:37',NULL),(16,'Inicio de Sesion','Supervisor','2025-12-13 20:06:47',NULL),(17,'crear fases incidencia','Supervisor','2025-12-13 20:07:51','ID incidencia: 1, Tipo: Ejemplo plomería, Fases creadas: 2'),(18,'aprobar incidencia','Supervisor','2025-12-13 20:07:51','ID: 1, Tipo: Ejemplo plomería, Trabajador asignado: Ejemplo Plomería'),(19,'Cierre de Sesion','Supervisor','2025-12-13 20:08:13',NULL),(20,'Inicio de Sesion','Obrero','2025-12-13 20:08:24',NULL),(21,'Cierre de Sesion','Obrero','2025-12-13 20:10:14',NULL),(22,'Inicio de Sesion','Supervisor','2025-12-13 20:10:22',NULL),(23,'Cierre de Sesion','Supervisor','2025-12-13 20:11:16',NULL),(24,'Inicio de Sesion','Almacenista','2025-12-13 20:11:30',NULL),(25,'Cierre de Sesion','Almacenista','2025-12-13 20:12:35',NULL),(26,'Inicio de Sesion','Obrero','2025-12-13 20:12:47',NULL),(27,'Cierre de Sesion','Obrero','2025-12-13 20:13:19',NULL),(28,'Inicio de Sesion','Supervisor','2025-12-13 20:13:50',NULL),(29,'Cierre de Sesion','Supervisor','2025-12-13 20:14:31',NULL),(30,'Inicio de Sesion','Almacenista','2025-12-13 20:14:39',NULL),(31,'Cierre de Sesion','Almacenista','2025-12-13 20:16:52',NULL),(32,'Inicio de Sesion','superadmin','2025-12-13 20:17:23',NULL),(33,'crear insumo','superadmin','2025-12-13 20:18:01','ID: HERR-123, Nombre: Llave de tubo, Categoría: 2'),(34,'crear insumo','superadmin','2025-12-13 20:18:23','ID: PLOM-2312, Nombre: Tubo PVC, Categoría: 3'),(35,'crear departamento','superadmin','2025-12-13 20:19:36','Departamento: Compras'),(36,'Cierre de Sesion','superadmin','2025-12-13 20:21:17',NULL),(37,'Inicio de Sesion','Supervisor','2025-12-13 20:21:25',NULL),(38,'Cierre de Sesion','Supervisor','2025-12-13 20:22:12',NULL),(39,'Inicio de Sesion','superadmin','2025-12-13 20:22:23',NULL),(40,'Creacion de usuario','Gerente','2025-12-13 20:22:52','Usuario creado con ID trabajador: 3'),(41,'Cierre de Sesion','superadmin','2025-12-13 20:22:54',NULL),(42,'Inicio de Sesion','Gerente','2025-12-13 20:23:14',NULL),(43,'Aprobación de solicitud','Gerente','2025-12-13 20:23:31','Gerente aprobó solicitud 1: se aprueba'),(44,'Cierre de Sesion','Gerente','2025-12-13 20:23:35',NULL),(45,'Inicio de Sesion','Almacenista','2025-12-13 20:23:43',NULL),(46,'Cierre de Sesion','Almacenista','2025-12-13 20:24:28',NULL),(47,'Inicio de Sesion','superadmin','2025-12-13 20:24:35',NULL),(48,'Inicio de Sesion','superadmin','2025-12-20 06:59:43',NULL),(49,'Inicio de Sesion','superadmin','2025-12-23 02:29:54',NULL),(50,'Inicio de Sesion','superadmin','2025-12-23 02:40:59',NULL),(51,'crear incidencia','superadmin','2025-12-23 03:43:34','ID: 2, Descripción: DIDI ESCAPO OTRA VEZ '),(52,'editar departamento','superadmin','2025-12-23 06:21:13','ID: 1, Nuevo nombre: Mantenimiento'),(53,'editar departamento','superadmin','2025-12-23 06:21:40','ID: 1, Nuevo nombre: Mantenimiento'),(54,'editar departamento','superadmin','2025-12-23 06:21:49','ID: 1, Nuevo nombre: Mantenimiento'),(55,'Cierre de Sesion','superadmin','2025-12-23 06:52:19',NULL),(56,'Inicio de Sesion','superadmin','2025-12-23 19:45:49',NULL),(57,'SESSION_TIMEOUT','superadmin','2025-12-24 03:46:01','Sesión cerrada por inactividad'),(58,'Inicio de Sesion','superadmin','2025-12-24 03:47:34',NULL),(59,'Inicio de Sesion','superadmin','2025-12-24 04:33:05',NULL),(60,'Cierre de Sesion','superadmin','2025-12-24 04:35:00',NULL),(61,'SESSION_TIMEOUT','superadmin','2025-12-24 06:39:32','Sesión cerrada por inactividad'),(62,'Inicio de Sesion','superadmin','2025-12-24 06:39:47',NULL),(63,'Inicio de Sesion','superadmin','2025-12-24 07:01:22',NULL),(64,'Cierre de Sesion','superadmin','2025-12-24 07:09:38',NULL),(65,'editar insumo','superadmin','2025-12-24 07:30:44','ID: PLOM-2312, Nombre: Tubo PVC (sin modificar cantidad)'),(66,'Cierre de Sesion','superadmin','2025-12-24 07:34:34',NULL),(67,'Inicio de Sesion','superadmin','2025-12-24 07:35:21',NULL),(68,'crear incidencia','superadmin','2025-12-24 07:55:31','ID: 3, Descripción: maikol se cayo '),(69,'crear fases incidencia','superadmin','2025-12-24 08:14:54','ID incidencia: 3, Tipo: General, Fases creadas: 6'),(70,'Cierre de Sesion','superadmin','2025-12-24 08:17:13',NULL),(71,'Inicio de Sesion','superadmin','2025-12-24 16:42:18',NULL),(72,'Cierre de Sesion','superadmin','2025-12-24 18:12:46',NULL),(73,'Inicio de Sesion','superadmin','2025-12-24 19:23:59',NULL),(74,'Inicio de Sesion','superadmin','2025-12-24 19:50:37',NULL),(75,'Cierre de Sesion','superadmin','2025-12-24 19:51:14',NULL),(76,'editar insumo','superadmin','2025-12-24 19:52:28','ID: PLOM-2312, Nombre: Tubo PVC (sin modificar cantidad)'),(77,'crear incidencia','superadmin','2025-12-24 20:30:22','ID: 4, Descripción: diomiorfwfwsfdgrgergerg'),(78,'crear fases incidencia','superadmin','2025-12-24 20:30:55','ID incidencia: 4, Tipo: General, Fases creadas: 6'),(79,'Cierre de Sesion','superadmin','2025-12-24 20:31:14',NULL),(80,'Inicio de Sesion','superadmin','2025-12-24 21:19:57',NULL),(81,'editar insumo','superadmin','2025-12-24 22:35:37','ID: PLOM-2312, Nombre: Tubo PVC (sin modificar cantidad)'),(82,'editar insumo','superadmin','2025-12-24 22:36:27','ID: PLOM-2312, Nombre: Tubo PVC (sin modificar cantidad)'),(83,'Cierre de Sesion','superadmin','2025-12-24 23:53:03',NULL),(84,'Inicio de Sesion','superadmin','2025-12-25 06:24:59',NULL),(85,'Inicio de Sesion','superadmin','2025-12-25 08:11:58',NULL),(86,'Cierre de Sesion','superadmin','2025-12-25 08:15:31',NULL),(87,'Cierre de Sesion','superadmin','2025-12-25 08:45:37',NULL),(88,'Inicio de Sesion','superadmin','2025-12-25 16:47:59',NULL),(89,'Cierre de Sesion','superadmin','2025-12-25 16:48:02',NULL),(90,'Inicio de Sesion','superadmin','2025-12-25 17:29:38',NULL),(91,'Cierre de Sesion','superadmin','2025-12-25 17:46:54',NULL),(92,'Inicio de Sesion','superadmin','2025-12-25 18:09:49',NULL),(93,'crear trabajador','superadmin','2025-12-25 18:10:48','ID: 6, Nombre: RICARDO PINTO, Cédula: 30294654'),(94,'Creacion de usuario','ricardo','2025-12-25 18:11:46','Usuario creado con ID trabajador: 6'),(95,'Cierre de Sesion','superadmin','2025-12-25 18:11:53',NULL),(96,'Inicio de Sesion','superadmin','2025-12-25 18:28:10',NULL),(97,'Cierre de Sesion','superadmin','2025-12-25 19:01:38',NULL),(98,'Inicio de Sesion','superadmin','2025-12-25 19:02:11',NULL),(99,'Inicio de Sesion','superadmin','2025-12-25 19:10:07',NULL),(100,'Cierre de Sesion','superadmin','2025-12-25 19:18:30',NULL),(101,'Inicio de Sesion','superadmin','2025-12-25 19:18:41',NULL),(102,'Cierre de Sesion','superadmin','2025-12-25 19:47:42',NULL),(103,'Inicio de Sesion','superadmin','2025-12-25 19:48:14',NULL),(104,'Cierre de Sesion','superadmin','2025-12-25 19:50:50',NULL),(105,'Inicio de Sesion','superadmin','2025-12-25 19:56:28',NULL),(106,'Cierre de Sesion','superadmin','2025-12-25 19:56:49',NULL),(107,'Inicio de Sesion','superadmin','2025-12-25 19:57:01',NULL),(108,'Cierre de Sesion','superadmin','2025-12-25 19:57:07',NULL),(109,'Inicio de Sesion','superadmin','2025-12-25 21:00:49',NULL),(110,'Cierre de Sesion','superadmin','2025-12-25 21:01:07',NULL),(111,'Inicio de Sesion','superadmin','2025-12-25 21:01:16',NULL),(112,'Cierre de Sesion','superadmin','2025-12-25 21:01:38',NULL),(113,'Inicio de Sesion','superadmin','2025-12-25 21:05:29',NULL),(114,'Inicio de Sesion','superadmin','2025-12-25 21:11:03',NULL),(115,'Cierre de Sesion','superadmin','2025-12-25 21:12:53',NULL),(116,'Inicio de Sesion','superadmin','2025-12-25 21:13:26',NULL),(117,'Cierre de Sesion','superadmin','2025-12-25 22:10:17',NULL),(118,'Inicio de Sesion','superadmin','2025-12-25 22:10:26',NULL),(119,'Inicio de Sesion','superadmin','2025-12-25 22:20:40',NULL),(120,'Cierre de Sesion','superadmin','2025-12-25 22:39:56',NULL),(121,'Inicio de Sesion','superadmin','2025-12-25 22:43:21',NULL),(122,'SESSION_TIMEOUT','superadmin','2025-12-25 23:46:15','Sesión cerrada por inactividad'),(123,'Inicio de Sesion','superadmin','2025-12-25 23:47:30',NULL),(124,'Inicio de Sesion','superadmin','2025-12-25 23:51:26',NULL),(125,'SESSION_TIMEOUT','superadmin','2025-12-25 23:51:26','Sesión cerrada por inactividad'),(126,'Cierre de Sesion','superadmin','2025-12-25 23:51:54',NULL),(127,'Inicio de Sesion','superadmin','2025-12-25 23:52:20',NULL),(128,'Cierre de Sesion','superadmin','2025-12-25 23:59:14',NULL),(129,'Inicio de Sesion','superadmin','2025-12-26 00:00:10',NULL),(130,'Cierre de Sesion','superadmin','2025-12-26 00:02:57',NULL),(131,'Inicio de Sesion','superadmin','2025-12-26 02:08:50',NULL),(132,'Inicio de Sesion','superadmin','2025-12-26 02:52:36',NULL),(133,'Cierre de Sesion','superadmin','2025-12-26 02:55:56',NULL),(134,'Inicio de Sesion','superadmin','2025-12-26 02:59:23',NULL),(135,'Cierre de Sesion','superadmin','2025-12-26 03:16:28',NULL),(136,'Cierre de Sesion','superadmin','2025-12-26 03:33:52',NULL),(137,'Inicio de Sesion','superadmin','2026-01-01 20:13:48',NULL),(138,'Inicio de Sesion','superadmin','2026-01-01 20:16:15',NULL),(139,'Cierre de Sesion','superadmin','2026-01-01 23:02:20',NULL),(140,'Inicio de Sesion','superadmin','2026-01-01 23:02:45',NULL),(141,'Cierre de Sesion','superadmin','2026-01-01 23:14:23',NULL),(142,'Inicio de Sesion','superadmin','2026-01-01 23:14:34',NULL),(143,'Cierre de Sesion','superadmin','2026-01-01 23:30:39',NULL),(144,'Cierre de Sesion','superadmin','2026-01-01 23:30:47',NULL),(145,'Inicio de Sesion','superadmin','2026-01-03 00:17:43',NULL),(146,'Cierre de Sesion','superadmin','2026-01-03 01:06:10',NULL),(147,'Inicio de Sesion','superadmin','2026-03-05 04:38:21',NULL),(148,'Cierre de Sesion','superadmin','2026-03-05 05:18:13',NULL),(149,'Inicio de Sesion','superadmin','2026-03-05 05:18:30',NULL),(150,'Cierre de Sesion','superadmin','2026-03-05 05:30:07',NULL),(151,'Inicio de Sesion','superadmin','2026-03-05 05:30:33',NULL),(152,'Inicio de Sesion','superadmin','2026-03-05 05:32:34',NULL),(153,'Cierre de Sesion','superadmin','2026-03-05 06:18:20',NULL),(154,'Inicio de Sesion','superadmin','2026-03-05 06:18:55',NULL),(155,'Cierre de Sesion','superadmin','2026-03-05 06:37:41',NULL),(156,'Inicio de Sesion','superadmin','2026-03-05 06:37:54',NULL),(157,'Cierre de Sesion','superadmin','2026-03-05 07:11:52',NULL),(158,'Inicio de Sesion','superadmin','2026-03-05 07:12:26',NULL),(159,'Cierre de Sesion','superadmin','2026-03-05 08:00:03',NULL),(160,'Cierre de Sesion','superadmin','2026-03-05 08:00:07',NULL),(161,'Inicio de Sesion','superadmin','2026-03-05 08:03:20',NULL),(162,'Cierre de Sesion','superadmin','2026-03-05 08:07:40',NULL),(163,'Inicio de Sesion','superadmin','2026-03-05 08:08:02',NULL),(164,'Cierre de Sesion','superadmin','2026-03-05 08:10:31',NULL),(165,'Inicio de Sesion','superadmin','2026-03-05 08:13:33',NULL),(166,'Cierre de Sesion','superadmin','2026-03-05 08:14:30',NULL),(167,'Inicio de Sesion','superadmin','2026-03-05 08:15:29',NULL),(168,'Cierre de Sesion','superadmin','2026-03-05 08:20:17',NULL),(169,'Inicio de Sesion','superadmin','2026-03-05 08:20:27',NULL),(170,'Cierre de Sesion','superadmin','2026-03-05 08:20:40',NULL),(171,'Inicio de Sesion','superadmin','2026-03-05 08:20:55',NULL),(172,'Cierre de Sesion','superadmin','2026-03-05 08:22:56',NULL),(173,'Inicio de Sesion','superadmin','2026-03-05 20:09:48',NULL),(174,'Cierre de Sesion','superadmin','2026-03-05 20:21:19',NULL),(175,'Inicio de Sesion','superadmin','2026-03-05 20:21:41',NULL),(176,'Cierre de Sesion','superadmin','2026-03-05 20:23:06',NULL),(177,'Inicio de Sesion','superadmin','2026-03-05 20:24:15',NULL),(178,'Cierre de Sesion','superadmin','2026-03-05 22:07:31',NULL),(179,'Inicio de Sesion','superadmin','2026-03-05 22:07:37',NULL),(180,'Cierre de Sesion','superadmin','2026-03-05 22:08:07',NULL),(181,'Inicio de Sesion','superadmin','2026-03-05 22:08:16',NULL),(182,'Cierre de Sesion','superadmin','2026-03-05 22:19:21',NULL),(183,'Inicio de Sesion','superadmin','2026-03-05 22:22:02',NULL),(184,'Cierre de Sesion','superadmin','2026-03-05 22:22:51',NULL),(185,'Inicio de Sesion','superadmin','2026-03-05 22:23:00',NULL),(186,'Cierre de Sesion','superadmin','2026-03-05 22:41:31',NULL),(187,'Inicio de Sesion','superadmin','2026-03-05 23:11:12',NULL),(188,'Cierre de Sesion','superadmin','2026-03-05 23:13:01',NULL),(189,'Inicio de Sesion','superadmin','2026-03-05 23:53:55',NULL),(190,'Cierre de Sesion','superadmin','2026-03-06 00:21:07',NULL),(191,'Inicio de Sesion','superadmin','2026-03-06 00:41:16',NULL),(192,'Cierre de Sesion','superadmin','2026-03-06 00:42:48',NULL),(193,'Inicio de Sesion','superadmin','2026-03-06 00:42:59',NULL),(194,'crear fases incidencia','superadmin','2026-03-06 01:44:13','ID incidencia: 2, Tipo: General, Fases creadas: 6'),(195,'Inicio de Sesion','superadmin','2026-03-06 03:37:13',NULL),(196,'Inicio de Sesion','superadmin','2026-03-09 23:29:35',NULL),(197,'Cierre de Sesion','superadmin','2026-03-09 23:35:39',NULL),(198,'Inicio de Sesion','Gerente','2026-03-09 23:35:47',NULL),(199,'Inicio de Sesion','superadmin','2026-03-09 23:57:36',NULL),(200,'SESSION_TIMEOUT','superadmin','2026-03-10 01:29:54','Sesión cerrada por inactividad'),(201,'USUARIO_BLOQUEADO','Gerente','2026-03-10 03:32:05','Usuario bloqueado permanentemente después de 3 intentos fallidos'),(202,'Inicio de Sesion','superadmin','2026-03-10 03:41:32',NULL);
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_insumo`
--

DROP TABLE IF EXISTS `categorias_insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias_insumo` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `abreviatura` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_insumo`
--

LOCK TABLES `categorias_insumo` WRITE;
/*!40000 ALTER TABLE `categorias_insumo` DISABLE KEYS */;
INSERT INTO `categorias_insumo` VALUES (1,'Materiales Eléctricos','Materiales para instalaciones eléctricas','ELEC'),(2,'Herramientas','Herramientas de trabajo general','HERR'),(3,'Materiales de Plomería','Materiales para reparaciones de plomería','PLOM'),(4,'Materiales de Carpintería','Materiales para trabajos de carpintería','CARP'),(5,'Limpieza','Productos de limpieza y aseo','LIMP');
/*!40000 ALTER TABLE `categorias_insumo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departamentos`
--

DROP TABLE IF EXISTS `departamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departamentos` (
  `id_departamento` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_departamento`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (1,'Mantenimiento','mantenimiento general','Sótano','','mantenimiento@clinica.com','Johander Hernández','2025-12-13 18:59:51'),(2,'Almacén','Almacén de la clínica','piso 2 departamento 6','02432131223','almacen@gmail.com','Manuel almacén','2025-12-13 19:00:59'),(3,'Compras','Compras','piso 2 departamento 6','02432131231','compras@gmail.com','Compras Hernandez','2025-12-13 20:19:35');
/*!40000 ALTER TABLE `departamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_salida_almacen`
--

DROP TABLE IF EXISTS `detalle_salida_almacen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_salida_almacen` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud` int(11) NOT NULL,
  `id_insumo` varchar(50) NOT NULL,
  `cantidad_solicitada` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cantidad_entregada` decimal(10,2) NOT NULL DEFAULT 0.00,
  `firma_emisor` varchar(255) DEFAULT NULL,
  `firma_receptor` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle`),
  KEY `id_solicitud` (`id_solicitud`),
  KEY `id_insumo` (`id_insumo`),
  CONSTRAINT `detalle_salida_almacen_ibfk_1` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitud_salida_almacen` (`id_solicitud`) ON DELETE CASCADE,
  CONSTRAINT `detalle_salida_almacen_ibfk_2` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_salida_almacen`
--

LOCK TABLES `detalle_salida_almacen` WRITE;
/*!40000 ALTER TABLE `detalle_salida_almacen` DISABLE KEYS */;
INSERT INTO `detalle_salida_almacen` VALUES (1,1,'HERR-123',2.00,0.00,NULL,NULL,'2025-12-13 20:19:01'),(2,1,'PLOM-2312',200.00,0.00,NULL,NULL,'2025-12-13 20:19:01'),(3,2,'HERR-123',10.00,10.00,NULL,NULL,'2025-12-13 20:20:02'),(4,2,'PLOM-2312',200.00,200.00,NULL,NULL,'2025-12-13 20:20:02');
/*!40000 ALTER TABLE `detalle_salida_almacen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_solicitud_material`
--

DROP TABLE IF EXISTS `detalle_solicitud_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_solicitud_material` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud` int(11) NOT NULL,
  `id_insumo` varchar(50) NOT NULL,
  `cantidad_pedida` int(11) NOT NULL DEFAULT 0,
  `cantidad_recibida` int(11) NOT NULL DEFAULT 0,
  `firma_receptor` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `firma_emisor` varchar(255) DEFAULT NULL,
  `firma_aprobador` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_solicitud` (`id_solicitud`),
  KEY `id_insumo` (`id_insumo`),
  CONSTRAINT `detalle_solicitud_material_ibfk_1` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitud_materiales` (`id_solicitud`) ON DELETE CASCADE,
  CONSTRAINT `detalle_solicitud_material_ibfk_2` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_solicitud_material`
--

LOCK TABLES `detalle_solicitud_material` WRITE;
/*!40000 ALTER TABLE `detalle_solicitud_material` DISABLE KEYS */;
INSERT INTO `detalle_solicitud_material` VALUES (1,1,'HERR-123',5,5,'../assets/imagenes/firmas/firma_1765652504.png','2025-12-13 20:22:05','../assets/imagenes/firmas/firma_1765653021.png','../assets/imagenes/firmas/firma_1765652969.png'),(2,1,'PLOM-2312',100,100,'../assets/imagenes/firmas/firma_1765652504.png','2025-12-13 20:22:05','../assets/imagenes/firmas/firma_1765653021.png','../assets/imagenes/firmas/firma_1765652969.png'),(3,2,'HERR-123',1,0,NULL,'2025-12-23 03:17:08',NULL,NULL),(4,3,'PLOM-2312',10,0,NULL,'2025-12-24 06:59:07',NULL,NULL);
/*!40000 ALTER TABLE `detalle_solicitud_material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_trabajador`
--

DROP TABLE IF EXISTS `detalle_trabajador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_trabajador` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_trabajador` int(11) NOT NULL,
  `aptitud` varchar(100) NOT NULL,
  `nivel_experiencia` enum('basico','intermedio','avanzado') DEFAULT 'basico',
  PRIMARY KEY (`id_detalle`),
  KEY `id_trabajador` (`id_trabajador`),
  CONSTRAINT `detalle_trabajador_ibfk_1` FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores` (`id_trabajador`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_trabajador`
--

LOCK TABLES `detalle_trabajador` WRITE;
/*!40000 ALTER TABLE `detalle_trabajador` DISABLE KEYS */;
INSERT INTO `detalle_trabajador` VALUES (1,3,'Eléctrica','basico'),(2,4,'Eléctrica','basico'),(3,5,'Ejemplo plomería','intermedio');
/*!40000 ALTER TABLE `detalle_trabajador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fases_incidencia`
--

DROP TABLE IF EXISTS `fases_incidencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fases_incidencia` (
  `id_fase` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_fase` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) NOT NULL,
  `tipo_incidencia` varchar(50) DEFAULT 'General',
  `requiere_evidencia` tinyint(1) DEFAULT 1,
  `seguimiento_secuencial` tinyint(1) DEFAULT 1 COMMENT '1=secuencial, 0=paralelo',
  PRIMARY KEY (`id_fase`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fases_incidencia`
--

LOCK TABLES `fases_incidencia` WRITE;
/*!40000 ALTER TABLE `fases_incidencia` DISABLE KEYS */;
INSERT INTO `fases_incidencia` VALUES (8,'Diagnóstico Inicial','Evaluación del problema y determinación de la causa raíz',1,'General',1,1),(9,'Reparación Principal','Ejecución de las reparaciones o mantenimiento necesario',2,'General',1,1),(10,'Pruebas de Funcionamiento','Verificación de que el equipo/sistema funciona correctamente',3,'General',1,1),(11,'Limpieza y Orden','Limpieza del área de trabajo y organización de herramientas',4,'General',1,1),(12,'Documentación Final','Registro de actividades realizadas y materiales utilizados',5,'General',0,1),(13,'Verificación de Tensión','Medición de voltaje y corriente en el circuito afectado',2,'Eléctrica',1,1),(14,'Reparación de Cableado','Sustitución o reparación de cables y conexiones',3,'Eléctrica',1,1),(15,'Localización de Fugas','Identificación de puntos de fuga en tuberías',2,'Plomería',1,1),(16,'Sustitución de Tuberías','Cambio de tuberías dañadas o corroídas',3,'Plomería',1,1),(17,'Limpieza de Filtros','Limpieza o sustitución de filtros de aire',2,'Aire Acondicionado',1,1),(19,'Diagnostico ferretería','ferretea',1,'Ferretería',1,1),(20,'Prueba ferretería','pruebita',2,'Ferretería',0,1),(30,'Confirmación del Solicitante','El usuario que reportó la incidencia verifica y acepta el trabajo realizado',6,'General',0,1),(33,'Diagnostico','diagnostico de tubo',1,'Ejemplo plomería',1,1),(35,'Reparación','Se debe reparar',2,'Ejemplo plomería',1,1),(37,'Test de wuebo','rfgtrgdgg',3,'Aire Acondicionado',1,1),(38,'Test de wuebo','rfgtrgdgg',3,'Aire Acondicionado',1,1);
/*!40000 ALTER TABLE `fases_incidencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencia_conformidad`
--

DROP TABLE IF EXISTS `incidencia_conformidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidencia_conformidad` (
  `id_conformidad` int(11) NOT NULL AUTO_INCREMENT,
  `id_incidencia` int(11) NOT NULL,
  `id_usuario_solicitante` int(11) NOT NULL,
  `confirmada` tinyint(1) DEFAULT 0,
  `calificacion` int(11) DEFAULT NULL CHECK (`calificacion` between 1 and 5),
  `comentarios` text DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `evidencia_firma` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_conformidad`),
  UNIQUE KEY `id_incidencia` (`id_incidencia`),
  KEY `id_usuario_solicitante` (`id_usuario_solicitante`),
  CONSTRAINT `incidencia_conformidad_ibfk_1` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencias` (`id_incidencia`) ON DELETE CASCADE,
  CONSTRAINT `incidencia_conformidad_ibfk_2` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_conformidad`
--

LOCK TABLES `incidencia_conformidad` WRITE;
/*!40000 ALTER TABLE `incidencia_conformidad` DISABLE KEYS */;
INSERT INTO `incidencia_conformidad` VALUES (1,1,2,1,4,'Se ha completado el trabajo correctamente','2025-12-13 16:15:27',NULL);
/*!40000 ALTER TABLE `incidencia_conformidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencia_fases`
--

DROP TABLE IF EXISTS `incidencia_fases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidencia_fases` (
  `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_incidencia` int(11) NOT NULL,
  `id_fase` int(11) NOT NULL,
  `estado` enum('pendiente','completada','aprobada','rechazada') DEFAULT 'pendiente',
  `fecha_completado` datetime DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `id_supervisor_aprobador` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `comentarios_obrero` text DEFAULT NULL,
  `evidencias` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`evidencias`)),
  PRIMARY KEY (`id_seguimiento`),
  KEY `id_incidencia` (`id_incidencia`),
  KEY `id_fase` (`id_fase`),
  KEY `id_supervisor_aprobador` (`id_supervisor_aprobador`),
  CONSTRAINT `incidencia_fases_ibfk_1` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencias` (`id_incidencia`) ON DELETE CASCADE,
  CONSTRAINT `incidencia_fases_ibfk_2` FOREIGN KEY (`id_fase`) REFERENCES `fases_incidencia` (`id_fase`) ON DELETE CASCADE,
  CONSTRAINT `incidencia_fases_ibfk_3` FOREIGN KEY (`id_supervisor_aprobador`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_fases`
--

LOCK TABLES `incidencia_fases` WRITE;
/*!40000 ALTER TABLE `incidencia_fases` DISABLE KEYS */;
INSERT INTO `incidencia_fases` VALUES (1,1,33,'aprobada','2025-12-13 16:09:54','2025-12-13 16:10:55',3,'Se aprueba correctamente','Se detecto el lugar de la fuga','[\"\\/lugopata\\/assets\\/incidencias\\/1\\/fase_33_1765656594_0.jpg\"]'),(2,1,35,'aprobada','2025-12-13 16:13:11','2025-12-13 16:13:59',3,'Muy bien hecho','Se reparó el tubo correctamente','[\"\\/lugopata\\/assets\\/incidencias\\/1\\/fase_35_1765656791_0.jpg\"]'),(3,1,30,'aprobada','2025-12-13 16:15:27',NULL,NULL,'Se ha completado el trabajo correctamente',NULL,NULL),(4,3,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(5,3,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(6,3,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(7,3,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(8,3,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(9,3,30,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(10,4,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(11,4,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(12,4,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(13,4,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(14,4,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(15,4,30,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(16,2,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(17,2,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(18,2,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(19,2,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(20,2,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(21,2,30,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `incidencia_fases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencia_imagenes`
--

DROP TABLE IF EXISTS `incidencia_imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidencia_imagenes` (
  `id_imagen` int(11) NOT NULL AUTO_INCREMENT,
  `id_incidencia` int(11) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_imagen`),
  KEY `id_incidencia` (`id_incidencia`),
  CONSTRAINT `incidencia_imagenes_ibfk_1` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencias` (`id_incidencia`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_imagenes`
--

LOCK TABLES `incidencia_imagenes` WRITE;
/*!40000 ALTER TABLE `incidencia_imagenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `incidencia_imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencia_rechazos`
--

DROP TABLE IF EXISTS `incidencia_rechazos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidencia_rechazos` (
  `id_rechazo` int(11) NOT NULL AUTO_INCREMENT,
  `id_incidencia` int(11) NOT NULL,
  `id_supervisor` int(11) NOT NULL,
  `justificacion` text NOT NULL,
  `fecha_rechazo` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_rechazo`),
  KEY `id_incidencia` (`id_incidencia`),
  KEY `id_supervisor` (`id_supervisor`),
  CONSTRAINT `incidencia_rechazos_ibfk_1` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencias` (`id_incidencia`) ON DELETE CASCADE,
  CONSTRAINT `incidencia_rechazos_ibfk_2` FOREIGN KEY (`id_supervisor`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_rechazos`
--

LOCK TABLES `incidencia_rechazos` WRITE;
/*!40000 ALTER TABLE `incidencia_rechazos` DISABLE KEYS */;
/*!40000 ALTER TABLE `incidencia_rechazos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencias`
--

DROP TABLE IF EXISTS `incidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidencias` (
  `id_incidencia` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `fecha_estimada_finalizacion` date DEFAULT NULL,
  `fecha_finalizacion` datetime DEFAULT NULL,
  `departamento_emisor` int(11) NOT NULL,
  `departamento_receptor` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `prioridad` enum('Urgente','Moderada','Leve') NOT NULL,
  `estado` enum('En espera','Pendiente','Finalizada','Rechazada') DEFAULT 'En espera',
  `id_firma_usuario` int(11) NOT NULL,
  `id_trabajador_asignado` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_incidencia`),
  KEY `departamento_emisor` (`departamento_emisor`),
  KEY `departamento_receptor` (`departamento_receptor`),
  KEY `id_firma_usuario` (`id_firma_usuario`),
  KEY `id_trabajador_asignado` (`id_trabajador_asignado`),
  CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`departamento_emisor`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `incidencias_ibfk_2` FOREIGN KEY (`departamento_receptor`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `incidencias_ibfk_3` FOREIGN KEY (`id_firma_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `incidencias_ibfk_4` FOREIGN KEY (`id_trabajador_asignado`) REFERENCES `trabajadores` (`id_trabajador`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencias`
--

LOCK TABLES `incidencias` WRITE;
/*!40000 ALTER TABLE `incidencias` DISABLE KEYS */;
INSERT INTO `incidencias` VALUES (1,'2025-12-13',NULL,'2025-12-13 16:15:27',2,1,'Se rompió un tubo','piso 2 departamento 5','Urgente','Finalizada',2,5),(2,'2025-12-23',NULL,NULL,2,3,'DIDI ESCAPO OTRA VEZ ','Sótano','Urgente','En espera',1,NULL),(3,'2025-12-24',NULL,NULL,3,3,'maikol se cayo ','piso 1 ','Urgente','En espera',1,NULL),(4,'2025-12-24',NULL,NULL,1,2,'diomiorfwfwsfdgrgergerg','Sótano','Moderada','En espera',1,NULL);
/*!40000 ALTER TABLE `incidencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `insumos`
--

DROP TABLE IF EXISTS `insumos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insumos` (
  `id_insumo` varchar(50) NOT NULL,
  `abreviatura_categoria` varchar(10) DEFAULT NULL,
  `codigo_numerico` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `unidad_medida` varchar(50) NOT NULL,
  `cantidad` decimal(10,2) DEFAULT 0.00,
  `stock_minimo` decimal(10,2) DEFAULT 1.00,
  `stock_maximo` decimal(10,2) DEFAULT 5.00,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_categoria` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_insumo`),
  KEY `fk_categoria_insumo` (`id_categoria`),
  CONSTRAINT `fk_categoria_insumo` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_insumo` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insumos`
--

LOCK TABLES `insumos` WRITE;
/*!40000 ALTER TABLE `insumos` DISABLE KEYS */;
INSERT INTO `insumos` VALUES ('HERR-123','HERR',123,'Llave de tubo','Llave','Unidad(es)',5.00,1.00,10.00,'1765657081_Llave de tubo.jpg','2025-12-13 16:18:01',2),('PLOM-2312','PLOM',2312,'Tubo PVC','Tubo','Metro(s)',100.00,1.00,200.00,'1765657103_tubo pvc.png','2025-12-13 16:18:23',5);
/*!40000 ALTER TABLE `insumos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recuperacion`
--

DROP TABLE IF EXISTS `recuperacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recuperacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiracion` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recuperacion`
--

LOCK TABLES `recuperacion` WRITE;
/*!40000 ALTER TABLE `recuperacion` DISABLE KEYS */;
INSERT INTO `recuperacion` VALUES (2,6,'ee25e7fe404aac038dcce9d5cf85b3b2ac4fbbfe69e4d2a27d6a78edad632b35','2025-12-25 20:13:57');
/*!40000 ALTER TABLE `recuperacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitud_materiales`
--

DROP TABLE IF EXISTS `solicitud_materiales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solicitud_materiales` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `emisor` varchar(100) DEFAULT NULL,
  `receptor` varchar(100) NOT NULL,
  `departamento_emisor` int(11) NOT NULL,
  `departamento_destino` int(11) NOT NULL,
  `id_incidencia` int(11) DEFAULT NULL,
  `razon_manual` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `id_aprobador` int(11) DEFAULT NULL,
  `estado` enum('En espera','Pendiente','Finalizada','Rechazada') DEFAULT 'En espera',
  `razon_solicitud` text DEFAULT NULL,
  PRIMARY KEY (`id_solicitud`),
  KEY `fk_departamento_emisor` (`departamento_emisor`),
  KEY `fk_departamento_destino` (`departamento_destino`),
  KEY `fk_aprobador` (`id_aprobador`),
  KEY `idx_solicitud_incidencia` (`id_incidencia`),
  CONSTRAINT `fk_aprobador` FOREIGN KEY (`id_aprobador`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_departamento_destino` FOREIGN KEY (`departamento_destino`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_departamento_emisor` FOREIGN KEY (`departamento_emisor`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_solicitud_incidencia` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencias` (`id_incidencia`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_materiales`
--

LOCK TABLES `solicitud_materiales` WRITE;
/*!40000 ALTER TABLE `solicitud_materiales` DISABLE KEYS */;
INSERT INTO `solicitud_materiales` VALUES (1,'2025-12-13','Supervisor Hernández','Compras Hernandez',1,2,NULL,'Para reparar una tubería','Necesito esas cosas',5,'Finalizada','se aprueba'),(2,'2025-12-23','Johander Hernández','Dr. Emilo',2,2,NULL,NULL,'ay',NULL,'En espera',NULL),(3,'2025-12-24','Johander Hernández','Dr. Emilo',2,3,NULL,'me cague ','papel',NULL,'En espera',NULL);
/*!40000 ALTER TABLE `solicitud_materiales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitud_salida_almacen`
--

DROP TABLE IF EXISTS `solicitud_salida_almacen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solicitud_salida_almacen` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `emisor` varchar(100) NOT NULL,
  `departamento_emisor` int(11) NOT NULL,
  `receptor` varchar(100) NOT NULL,
  `departamento_destino` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('En espera','Pendiente','Finalizada','Rechazada') DEFAULT 'En espera',
  PRIMARY KEY (`id_solicitud`),
  KEY `departamento_emisor` (`departamento_emisor`),
  KEY `departamento_destino` (`departamento_destino`),
  CONSTRAINT `solicitud_salida_almacen_ibfk_1` FOREIGN KEY (`departamento_emisor`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `solicitud_salida_almacen_ibfk_2` FOREIGN KEY (`departamento_destino`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_salida_almacen`
--

LOCK TABLES `solicitud_salida_almacen` WRITE;
/*!40000 ALTER TABLE `solicitud_salida_almacen` DISABLE KEYS */;
INSERT INTO `solicitud_salida_almacen` VALUES (1,'2025-12-13','Johander Hernández',1,'Almacenista Hernández',2,'Solicitud de reposición de stock para: Llave de tubo','En espera'),(2,'2025-12-13','Johander Hernández',1,'Compras Hernandez',3,'Por favor necesito esos insumos','Finalizada');
/*!40000 ALTER TABLE `solicitud_salida_almacen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_almacen`
--

DROP TABLE IF EXISTS `stock_almacen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_almacen` (
  `id_stock` int(11) NOT NULL AUTO_INCREMENT,
  `id_insumo` varchar(50) NOT NULL,
  `cantidad` decimal(10,2) DEFAULT 0.00,
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_stock`),
  UNIQUE KEY `id_insumo` (`id_insumo`),
  CONSTRAINT `fk_insumo_almacen` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_almacen`
--

LOCK TABLES `stock_almacen` WRITE;
/*!40000 ALTER TABLE `stock_almacen` DISABLE KEYS */;
INSERT INTO `stock_almacen` VALUES (1,'HERR-123',5.00,'2025-12-13 16:24:25'),(2,'PLOM-2312',10.00,'2025-12-24 18:35:37');
/*!40000 ALTER TABLE `stock_almacen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajadores`
--

DROP TABLE IF EXISTS `trabajadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trabajadores` (
  `id_trabajador` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `firma` varchar(255) DEFAULT NULL,
  `id_departamento` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_trabajador`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `id_departamento` (`id_departamento`),
  CONSTRAINT `trabajadores_ibfk_1` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajadores`
--

LOCK TABLES `trabajadores` WRITE;
/*!40000 ALTER TABLE `trabajadores` DISABLE KEYS */;
INSERT INTO `trabajadores` VALUES (1,'31.583.133','Johander','Hernández','04243031431','Caracas, Venezuela',NULL,1),(2,'2132142','Almacenista','Hernández','04122131231','casita 24 calle 6','../assets/imagenes/firmas/firma_1765652504.png',2),(3,'21321312','Gerente','Hernandez','04222131232','Casita 25 #23','../assets/imagenes/firmas/firma_1765652969.png',1),(4,'21234324','Supervisor','Hernández','04122132131','casita 24','../assets/imagenes/firmas/firma_1765653021.png',1),(5,'3423434','Ejemplo','Plomería','04222131231','Casita 24','../assets/imagenes/firmas/firma_1765653321.png',1),(6,'30294654','RICARDO','PINTO','04123453161','ovallera','../assets/imagenes/firmas/firma_1766686248.png',2);
/*!40000 ALTER TABLE `trabajadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nivel` enum('superadministrador','admin','sistemas','supmantenimiento','obmantenimiento','almacenista','solicitante') NOT NULL DEFAULT 'solicitante',
  `id_trabajador` int(11) NOT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueado` tinyint(1) DEFAULT 0,
  `password_anterior` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `id_trabajador` (`id_trabajador`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores` (`id_trabajador`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'superadmin','$2y$10$UoeA6QBkdo6fM3Ms8XdTNe7xWtVvecDcq3MXqE/0mU.ivZEej8SvK','Miguehernandez1508@gmail.com','superadministrador',1,0,0,NULL),(2,'Almacenista','$2y$10$/AL98BqvW5h5R2Q4djeO1uKMuNXgA1/M40QUUyi.bNJeKqXpdUjxy','almacenista@gmail.com','almacenista',2,0,0,NULL),(3,'Supervisor','$2y$10$pCdDtf3MeEcN.vXZz8L0n.He0S6FVU8.LMlxC9dXoOa1qbhTvjZdq','supervisor@gmail.com','supmantenimiento',4,2,0,NULL),(4,'Obrero','$2y$10$wMlmnLnX6w2ZDBqKqpYkteH5Pj7jh4YeIpybKkCDeAG4e/p/kExN2','Obrero@gmail.com','obmantenimiento',5,0,0,NULL),(5,'Gerente','$2y$10$rogbHsYRjOWu3wTJg4iNUOCp2MaRjEEyjSLkTUfjXIlLpzfecws6W','Gerente@gmail.com','admin',3,3,1,NULL),(6,'ricardo','$2y$10$HD2v2LSx4MBfytHMGwNhouPIImk.ABIame7BrC9aDNCVge2eB1mNS','pintoricardo526@gmail.com','sistemas',6,0,0,NULL);
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

-- Dump completed on 2026-03-09 23:45:01
