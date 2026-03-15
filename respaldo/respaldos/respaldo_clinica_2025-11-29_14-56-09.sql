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
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (1,'Inicio de Sesion','superadmin','2025-11-24 12:15:37',NULL),(2,'Cierre de Sesion','superadmin','2025-11-24 12:15:56',NULL),(3,'Inicio de Sesion','superadmin','2025-11-24 12:31:52',NULL),(4,'Cierre de Sesion','superadmin','2025-11-24 12:31:55',NULL),(5,'Inicio de Sesion','superadmin','2025-11-24 12:34:41',NULL),(6,'Cierre de Sesion','superadmin','2025-11-24 12:35:05',NULL),(7,'Inicio de Sesion','superadmin','2025-11-24 12:35:56',NULL),(8,'Cierre de Sesion','superadmin','2025-11-24 12:35:59',NULL),(9,'Inicio de Sesion','superadmin','2025-11-24 12:37:47',NULL),(10,'Aprobación de solicitud','superadmin','2025-11-24 12:45:41','El usuario \'superadmin\' aprobó la solicitud ID 1'),(11,'Inicio de Sesion','superadmin','2025-11-27 15:49:28',NULL),(12,'Inicio de Sesion','superadmin','2025-11-27 21:49:40',NULL),(13,'Inicio de Sesion','superadmin','2025-11-27 22:15:41',NULL),(14,'SESSION_TIMEOUT','superadmin','2025-11-27 22:15:41','Sesión cerrada por inactividad'),(15,'Inicio de Sesion','superadmin','2025-11-27 22:15:56',NULL),(16,'Creacion de usuario','Supervisor','2025-11-27 22:31:25','Usuario creado con ID trabajador: 2'),(17,'Creacion de usuario','Obrero','2025-11-27 22:32:08','Usuario creado con ID trabajador: 3'),(18,'Cierre de Sesion','superadmin','2025-11-27 22:32:26',NULL),(19,'Inicio de Sesion','Supervisor','2025-11-27 22:32:38',NULL),(20,'Cierre de Sesion','Supervisor','2025-11-27 22:32:58',NULL),(21,'Inicio de Sesion','Obrero','2025-11-27 22:33:19',NULL),(22,'Cierre de Sesion','Obrero','2025-11-27 22:33:47',NULL),(23,'Inicio de Sesion','superadmin','2025-11-27 22:34:17',NULL),(24,'SESSION_TIMEOUT','superadmin','2025-11-27 22:52:31','Sesión cerrada por inactividad'),(25,'Inicio de Sesion','superadmin','2025-11-27 22:52:42',NULL),(26,'SESSION_TIMEOUT','superadmin','2025-11-27 23:01:26','Sesión cerrada por inactividad'),(27,'Inicio de Sesion','superadmin','2025-11-27 23:01:37',NULL),(28,'Inicio de Sesion','superadmin','2025-11-27 23:08:39',NULL),(29,'SESSION_TIMEOUT','superadmin','2025-11-27 23:08:39','Sesión cerrada por inactividad'),(30,'Inicio de Sesion','superadmin','2025-11-27 23:08:48',NULL),(31,'Inicio de Sesion','superadmin','2025-11-27 23:17:09',NULL),(32,'SESSION_TIMEOUT','superadmin','2025-11-27 23:17:10','Sesión cerrada por inactividad'),(33,'Inicio de Sesion','superadmin','2025-11-27 23:17:18',NULL),(34,'SESSION_TIMEOUT','superadmin','2025-11-27 23:27:55','Sesión cerrada por inactividad'),(35,'Inicio de Sesion','superadmin','2025-11-27 23:28:04',NULL),(36,'Cierre de Sesion','superadmin','2025-11-27 23:29:57',NULL),(37,'Inicio de Sesion','Obrero','2025-11-27 23:30:13',NULL),(38,'Cierre de Sesion','Obrero','2025-11-27 23:30:57',NULL),(39,'Inicio de Sesion','superadmin','2025-11-27 23:31:09',NULL),(40,'Inicio de Sesion','superadmin','2025-11-28 01:38:52',NULL),(41,'SESSION_TIMEOUT','superadmin','2025-11-28 01:38:52','Sesión cerrada por inactividad'),(42,'Inicio de Sesion','superadmin','2025-11-28 01:39:06',NULL),(43,'SESSION_TIMEOUT','superadmin','2025-11-28 01:46:50','Sesión cerrada por inactividad'),(44,'Inicio de Sesion','superadmin','2025-11-28 01:46:59',NULL),(45,'Cierre de Sesion','superadmin','2025-11-28 01:48:20',NULL),(46,'Inicio de Sesion','Obrero','2025-11-28 01:49:22',NULL),(47,'Cierre de Sesion','Obrero','2025-11-28 01:49:37',NULL),(48,'Inicio de Sesion','superadmin','2025-11-28 01:50:20',NULL),(49,'SESSION_TIMEOUT','superadmin','2025-11-28 01:58:33','Sesión cerrada por inactividad'),(50,'Inicio de Sesion','Obrero','2025-11-28 01:58:41',NULL),(51,'Cierre de Sesion','Obrero','2025-11-28 01:59:10',NULL),(52,'Inicio de Sesion','superadmin','2025-11-28 01:59:18',NULL),(53,'Cierre de Sesion','superadmin','2025-11-28 02:18:57',NULL),(54,'Inicio de Sesion','Obrero','2025-11-28 02:19:11',NULL),(55,'Cierre de Sesion','Obrero','2025-11-28 02:19:35',NULL),(56,'Inicio de Sesion','superadmin','2025-11-28 02:20:21',NULL),(57,'Cierre de Sesion','superadmin','2025-11-28 02:25:47',NULL),(58,'Inicio de Sesion','Obrero','2025-11-28 02:25:54',NULL),(59,'Cierre de Sesion','Obrero','2025-11-28 02:26:06',NULL),(60,'Inicio de Sesion','superadmin','2025-11-28 02:26:13',NULL),(61,'Inicio de Sesion','superadmin','2025-11-28 02:33:35',NULL),(62,'SESSION_TIMEOUT','superadmin','2025-11-28 02:33:35','Sesión cerrada por inactividad'),(63,'Inicio de Sesion','superadmin','2025-11-28 02:33:49',NULL),(64,'Cierre de Sesion','superadmin','2025-11-28 02:37:13',NULL),(65,'Inicio de Sesion','Obrero','2025-11-28 02:37:21',NULL),(66,'Cierre de Sesion','Obrero','2025-11-28 02:38:18',NULL),(67,'Inicio de Sesion','superadmin','2025-11-28 02:38:27',NULL),(68,'Cierre de Sesion','superadmin','2025-11-28 02:59:46',NULL),(69,'Inicio de Sesion','superadmin','2025-11-28 03:00:04',NULL),(70,'Inicio de Sesion','superadmin','2025-11-28 21:44:51',NULL),(71,'SESSION_TIMEOUT','superadmin','2025-11-28 21:59:58','Sesión cerrada por inactividad'),(72,'Inicio de Sesion','superadmin','2025-11-28 22:00:04',NULL),(73,'SESSION_TIMEOUT','superadmin','2025-11-28 22:14:42','Sesión cerrada por inactividad'),(74,'Inicio de Sesion','superadmin','2025-11-28 22:14:52',NULL),(75,'SESSION_TIMEOUT','superadmin','2025-11-28 22:56:49','Sesión cerrada por inactividad'),(76,'Inicio de Sesion','superadmin','2025-11-28 22:56:57',NULL),(77,'Cierre de Sesion','superadmin','2025-11-28 22:58:13',NULL),(78,'Inicio de Sesion','superadmin','2025-11-28 22:58:26',NULL),(79,'Inicio de Sesion','superadmin','2025-11-29 05:32:55',NULL),(80,'SESSION_TIMEOUT','superadmin','2025-11-29 09:30:54','Sesión cerrada por inactividad'),(81,'Inicio de Sesion','superadmin','2025-11-29 09:31:15',NULL),(82,'Actualizacion de usuario','Obrero','2025-11-29 09:50:35','Usuario actualizado: Obrero (ID: 3)'),(83,'SESSION_TIMEOUT','superadmin','2025-11-29 18:36:50','Sesión cerrada por inactividad'),(84,'Inicio de Sesion','superadmin','2025-11-29 18:36:57',NULL),(85,'Inicio de Sesion','superadmin','2025-11-29 18:38:49',NULL),(86,'Creacion de usuario','Supervisor2','2025-11-29 18:39:28','Usuario creado con ID trabajador: 4'),(87,'Cierre de Sesion','superadmin','2025-11-29 18:39:41',NULL),(88,'Inicio de Sesion','Supervisor','2025-11-29 18:39:49',NULL),(89,'crear trabajador','Supervisor','2025-11-29 18:40:06','ID: 5, Nombre: Juanchito sadsad, Cédula: 123123'),(90,'crear departamento','Supervisor','2025-11-29 18:41:34','Departamento: sad'),(91,'crear trabajador','Supervisor','2025-11-29 18:42:41','ID: 6, Nombre: Manuel segundo, Cédula: 884394934'),(92,'crear trabajador','Supervisor','2025-11-29 18:48:47','ID: 7, Nombre: nuevo sadsad, Cédula: 21323'),(93,'crear trabajador','Supervisor','2025-11-29 18:49:22','ID: 8, Nombre: sdsad sad, Cédula: 213213'),(94,'Cierre de Sesion','Supervisor','2025-11-29 18:49:51',NULL),(95,'Inicio de Sesion','superadmin','2025-11-29 18:50:02',NULL),(96,'crear fases incidencia','superadmin','2025-11-29 18:54:50','ID incidencia: 18, Tipo: Aire Acondicionado, Fases creadas: 2'),(97,'aprobar incidencia','superadmin','2025-11-29 18:54:50','ID: 18, Tipo: Aire Acondicionado, Trabajador asignado: sdsad sad');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_insumo`
--

LOCK TABLES `categorias_insumo` WRITE;
/*!40000 ALTER TABLE `categorias_insumo` DISABLE KEYS */;
INSERT INTO `categorias_insumo` VALUES (1,'Ferretería','Una cosa ferretera','FE');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (1,'Mantenimiento','Departamento encargado del mantenimiento y reparación de equipos e instalaciones','Edificio Principal - Planta Baja','0243-555-1234','mantenimiento@clinica.com','Jefe de Mantenimiento','2025-11-24 12:15:19'),(2,'sad','sad','casita loca','02122131232','jsd@j.com','asd','2025-11-29 18:41:34');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_salida_almacen`
--

LOCK TABLES `detalle_salida_almacen` WRITE;
/*!40000 ALTER TABLE `detalle_salida_almacen` DISABLE KEYS */;
INSERT INTO `detalle_salida_almacen` VALUES (1,1,'FE-123',200.00,0.00,NULL,NULL,'2025-11-28 23:28:56');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_solicitud_material`
--

LOCK TABLES `detalle_solicitud_material` WRITE;
/*!40000 ALTER TABLE `detalle_solicitud_material` DISABLE KEYS */;
INSERT INTO `detalle_solicitud_material` VALUES (1,1,'FE-123',20,0,NULL,'2025-11-24 12:39:50',NULL,NULL),(2,2,'FE-123',1,0,NULL,'2025-11-29 05:36:39',NULL,NULL),(3,3,'FE-123',2,0,NULL,'2025-11-29 18:41:15',NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_trabajador`
--

LOCK TABLES `detalle_trabajador` WRITE;
/*!40000 ALTER TABLE `detalle_trabajador` DISABLE KEYS */;
INSERT INTO `detalle_trabajador` VALUES (1,3,'Plomería','basico'),(2,4,'baño','intermedio'),(5,7,'Eléctrica','basico'),(6,8,'baño','basico'),(7,8,'Aire Acondicionado','basico');
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
  PRIMARY KEY (`id_fase`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fases_incidencia`
--

LOCK TABLES `fases_incidencia` WRITE;
/*!40000 ALTER TABLE `fases_incidencia` DISABLE KEYS */;
INSERT INTO `fases_incidencia` VALUES (8,'Diagnóstico Inicial','Evaluación del problema y determinación de la causa raíz',1,'General',1),(9,'Reparación Principal','Ejecución de las reparaciones o mantenimiento necesario',2,'General',1),(10,'Pruebas de Funcionamiento','Verificación de que el equipo/sistema funciona correctamente',3,'General',1),(11,'Limpieza y Orden','Limpieza del área de trabajo y organización de herramientas',4,'General',1),(12,'Documentación Final','Registro de actividades realizadas y materiales utilizados',5,'General',0),(13,'Verificación de Tensión','Medición de voltaje y corriente en el circuito afectado',2,'Eléctrica',1),(14,'Reparación de Cableado','Sustitución o reparación de cables y conexiones',3,'Eléctrica',1),(15,'Localización de Fugas','Identificación de puntos de fuga en tuberías',2,'Plomería',1),(16,'Sustitución de Tuberías','Cambio de tuberías dañadas o corroídas',3,'Plomería',1),(17,'Limpieza de Filtros','Limpieza o sustitución de filtros de aire',2,'Aire Acondicionado',1),(18,'Verificación de Gas','Comprobación de nivel de refrigerante',3,'Aire Acondicionado',1),(19,'Diagnostico ferretería','ferretea',1,'Ferretería',1),(20,'Prueba ferretería','pruebita',2,'Ferretería',0),(21,'hacer locuras','pues hacer locuritas',2,'una locurita',1),(22,'Diagnóstico de pupu','ver la caca',1,'baño',1),(23,'Diagnóstico 2','kkj',2,'baño',1);
/*!40000 ALTER TABLE `fases_incidencia` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=244 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_fases`
--

LOCK TABLES `incidencia_fases` WRITE;
/*!40000 ALTER TABLE `incidencia_fases` DISABLE KEYS */;
INSERT INTO `incidencia_fases` VALUES (26,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(27,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(28,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(29,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(30,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(31,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(32,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(33,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(34,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(35,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(36,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(37,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(38,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(39,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(40,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(41,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(42,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(43,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(44,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(45,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(46,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(47,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(48,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(49,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(50,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(51,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(52,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(53,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(54,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(55,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(56,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(57,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(58,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(59,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(60,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(61,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(62,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(63,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(64,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(65,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(66,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(67,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(68,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(69,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(70,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(71,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(72,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(73,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(74,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(75,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(76,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(77,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(78,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(79,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(80,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(81,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(82,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(83,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(84,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(85,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(86,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(87,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(88,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(89,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(90,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(91,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(92,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(93,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(94,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(95,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(96,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(97,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(98,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(99,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(100,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(101,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(102,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(103,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(104,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(105,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(106,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(107,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(108,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(109,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(110,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(111,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(112,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(113,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(114,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(115,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(116,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(117,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(118,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(119,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(120,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(121,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(122,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(123,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(124,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(125,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(126,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(127,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(128,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(129,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(130,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(131,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(132,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(133,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(134,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(135,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(136,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(137,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(138,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(139,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(140,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(141,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(142,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(143,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(144,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(145,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(146,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(147,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(148,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(149,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(150,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(151,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(152,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(153,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(154,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(155,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(156,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(157,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(158,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(159,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(160,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(161,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(162,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(163,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(164,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(165,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(166,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(167,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(168,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(169,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(170,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(171,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(172,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(173,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(174,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(175,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(176,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(177,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(178,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(179,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(180,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(181,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(182,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(183,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(184,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(185,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(186,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(187,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(188,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(189,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(190,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(191,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(192,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(193,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(194,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(195,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(196,5,13,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(197,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(198,5,14,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(199,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(200,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(201,5,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(202,5,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(203,5,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(204,5,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(205,5,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(206,6,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(207,6,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(208,6,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(209,6,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(210,6,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(211,7,8,'aprobada','2025-11-28 00:30:44','2025-11-28 00:31:30',1,'Muy bien',NULL,'[\"\\/lugopata\\/assets\\/incidencias\\/7\\/fase_8_1764286244_0.jpg\"]'),(212,7,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(213,7,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(214,7,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(215,7,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(216,8,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(217,8,19,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(218,8,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(219,8,20,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(220,8,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(221,8,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(222,8,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(223,9,19,'aprobada','2025-11-28 03:26:04','2025-11-28 03:26:20',1,'','loco','[\"\\/lugopata\\/assets\\/incidencias\\/9\\/fase_19_1764296764_0.jpg\"]'),(224,9,20,'aprobada','2025-11-28 02:59:00','2025-11-28 02:59:29',1,'Muy bien hecho','Bueno fue una locura','[\"\\/lugopata\\/assets\\/incidencias\\/9\\/fase_20_1764295140_0.jpg\"]'),(225,10,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(226,10,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(227,10,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(228,10,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(229,10,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(230,11,21,'aprobada','2025-11-28 03:19:30','2025-11-28 03:20:32',1,'bien bien','','[\"\\/lugopata\\/assets\\/incidencias\\/11\\/fase_21_1764296370_0.jpg\"]'),(231,12,8,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(232,12,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(233,12,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(234,12,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(235,12,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(236,15,22,'aprobada','2025-11-28 03:37:58','2025-11-28 03:38:49',1,'locurita','lkjlk','[\"\\/lugopata\\/assets\\/incidencias\\/15\\/fase_22_1764297478_0.jpg\"]'),(237,15,23,'aprobada','2025-11-28 03:38:11','2025-11-28 03:38:57',1,'jj','lkjh','[\"\\/lugopata\\/assets\\/incidencias\\/15\\/fase_23_1764297491_0.jpg\"]'),(238,16,22,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(239,16,23,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(240,17,22,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(241,17,23,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(242,18,17,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(243,18,18,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_rechazos`
--

LOCK TABLES `incidencia_rechazos` WRITE;
/*!40000 ALTER TABLE `incidencia_rechazos` DISABLE KEYS */;
INSERT INTO `incidencia_rechazos` VALUES (1,10,1,'naguara de loco quique 2','2025-11-28 01:47:38'),(2,12,1,'pues esto y lo otro','2025-11-28 02:02:48'),(3,13,1,'bueno una locura','2025-11-28 02:05:21'),(4,14,1,'asd','2025-11-28 02:10:08');
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencias`
--

LOCK TABLES `incidencias` WRITE;
/*!40000 ALTER TABLE `incidencias` DISABLE KEYS */;
INSERT INTO `incidencias` VALUES (1,'2025-11-27','2025-11-28',NULL,1,1,'juancho',NULL,'Urgente','Pendiente',1,NULL),(2,'2025-11-27','2025-11-29',NULL,1,1,'sad',NULL,'Urgente','Pendiente',1,1),(3,'2025-11-27','2025-11-29',NULL,1,1,'necesito',NULL,'Urgente','Pendiente',1,3),(4,'2025-11-27','2025-11-29',NULL,1,1,'sadsad',NULL,'Urgente','Pendiente',1,NULL),(5,'2025-11-27','2025-11-28',NULL,1,1,'sdasad',NULL,'Urgente','Pendiente',1,1),(6,'2025-11-28','2025-11-28',NULL,1,1,'das',NULL,'Urgente','Pendiente',1,1),(7,'2025-11-28','2025-11-29',NULL,1,1,'calculadora',NULL,'Moderada','Pendiente',1,3),(8,'2025-11-28','2025-11-28',NULL,1,1,'ferrita',NULL,'Urgente','Pendiente',1,3),(9,'2025-11-28','2025-11-29','2025-11-28 03:26:20',1,1,'1',NULL,'Urgente','Finalizada',1,3),(10,'2025-11-28','2025-11-29',NULL,1,1,'2',NULL,'Urgente','Pendiente',1,NULL),(11,'2025-11-28','2025-11-29',NULL,1,1,'sad',NULL,'Urgente','Pendiente',1,3),(12,'2025-11-28','2025-11-15',NULL,1,1,'sdf',NULL,'Urgente','Pendiente',1,NULL),(13,'2025-11-28','2025-11-22',NULL,1,1,'sad',NULL,'Urgente','Rechazada',1,NULL),(14,'2025-11-28','2025-11-28',NULL,1,1,'recha',NULL,'Urgente','Rechazada',1,NULL),(15,'2025-11-28','2025-11-29','2025-11-28 03:38:58',1,1,'tapo el baño',NULL,'Urgente','Finalizada',1,3),(16,'2025-11-28','2025-11-30',NULL,1,1,'se tapó el baño coño',NULL,'Urgente','Pendiente',1,4),(17,'2025-11-28','2025-11-29',NULL,1,1,'baño',NULL,'Urgente','Pendiente',1,4),(18,'2025-11-29','2025-11-29',NULL,1,1,'asd',NULL,'Urgente','Pendiente',1,8),(19,'2025-11-29','2025-11-29',NULL,1,1,'asd',NULL,'Urgente','En espera',1,NULL),(20,'2025-11-29',NULL,NULL,1,1,'sadsad','casita loca','Urgente','En espera',1,NULL);
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
INSERT INTO `insumos` VALUES ('FE-123','FE',123,'Llave de tubo','Una llavesita','Unidad(es)',0.00,1.00,100.00,'1763987964_WhatsApp Image 2025-11-21 at 14.10.33.jpeg','2025-11-24 08:39:24',1);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recuperacion`
--

LOCK TABLES `recuperacion` WRITE;
/*!40000 ALTER TABLE `recuperacion` DISABLE KEYS */;
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
INSERT INTO `solicitud_materiales` VALUES (1,'2025-11-24','Johander Hernandez','Manuelito',1,1,NULL,NULL,'Loco loco',1,'Pendiente',NULL),(2,'2025-11-29','Johander Hernandez','sadsad',1,1,NULL,NULL,'asdsad',NULL,'En espera',NULL),(3,'2025-11-29','Ricardo Pinto','sadsad',1,1,19,NULL,'dsf',NULL,'En espera',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_salida_almacen`
--

LOCK TABLES `solicitud_salida_almacen` WRITE;
/*!40000 ALTER TABLE `solicitud_salida_almacen` DISABLE KEYS */;
INSERT INTO `solicitud_salida_almacen` VALUES (1,'2025-11-29','Johander Hernandez',1,'100',1,'Solicitud de reposición de stock para: Llave de tubo','En espera');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_almacen`
--

LOCK TABLES `stock_almacen` WRITE;
/*!40000 ALTER TABLE `stock_almacen` DISABLE KEYS */;
INSERT INTO `stock_almacen` VALUES (1,'FE-123',0.00,'2025-11-24 08:39:25');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajadores`
--

LOCK TABLES `trabajadores` WRITE;
/*!40000 ALTER TABLE `trabajadores` DISABLE KEYS */;
INSERT INTO `trabajadores` VALUES (1,'31583133','Johander','Hernandez','0424-3031431','Av. Principal, Urbanización Los Samanes, Caracas',NULL,1),(2,'30294654','Ricardo','Pinto','0412-9876543','Calle 5, Residencias El Paraíso, Caracas',NULL,1),(3,'123213','Juanchito','manuel','04222312312','sadsa','../assets/imagenes/firmas/firma_1764282706.png',1),(4,'812381','bañador','sadsa','04222131231','sadsa','../assets/imagenes/firmas/firma_1764368385.png',1),(5,'123123','Juanchito','sadsad','04221231232','sadsa','../assets/imagenes/firmas/firma_1764441606.png',1),(6,'884394934','Manuel','segundo','04121232131','cosa loca','../assets/imagenes/firmas/firma_1764441761.png',1),(7,'21323','nuevo','sadsad','04222321312','juanchito','../assets/imagenes/firmas/firma_1764442127.png',1),(8,'213213','sdsad','sad','0422213213','sadsadsa','../assets/imagenes/firmas/firma_1764442162.png',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'superadmin','$2y$10$UoeA6QBkdo6fM3Ms8XdTNe7xWtVvecDcq3MXqE/0mU.ivZEej8SvK','Miguehernandez1508@gmail.com','superadministrador',1,0,0,NULL),(2,'Supervisor','$2y$10$s0V/o63Tm0V0YCPXYDTWbe1rCLAI/zj5b31nqVMK5w9i./qZO/d8e','migue@g.com','supmantenimiento',2,0,0,NULL),(3,'Obrero','$2y$10$34ZVjmtj2bJ5mooPoouOeeC.eNUpVrFGArmxYcS1KAX/mOB9uFFuu','sadjsa@gmail.com','obmantenimiento',3,0,1,NULL),(4,'Supervisor2','$2y$10$GSgSDLRqn.9ASC4sRzJ1t.y1RQsDMfe5elMWro37S6FT9zHQc9ELG','sdadjsa@gmail.com','supmantenimiento',4,0,0,NULL);
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

-- Dump completed on 2025-11-29 14:56:10
