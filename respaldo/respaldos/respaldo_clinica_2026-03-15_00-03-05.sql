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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (1,'RESTABLECIMIENTO_COMPLETO','superadmin','2026-03-12 04:22:57','Restablecimiento completo ejecutado el 2026-03-12 00:22:57'),(2,'crear trabajador','superadmin','2026-03-12 04:30:18','ID: 2, Nombre: Martín Martínez, Cédula: V-21324213'),(3,'crear departamento','superadmin','2026-03-12 04:30:52','Departamento: Almacen'),(4,'crear departamento','superadmin','2026-03-12 04:31:18','Departamento: Compras'),(5,'crear trabajador','superadmin','2026-03-12 04:32:01','ID: 3, Nombre: Eladio Carrion, Cédula: V-23123123'),(6,'crear trabajador','superadmin','2026-03-12 04:32:24','ID: 4, Nombre: Ricky Martin, Cédula: V-2312323'),(7,'Creacion de usuario','almacenista','2026-03-12 04:33:17','Usuario creado con ID trabajador: 3'),(8,'Creacion de usuario','obrero','2026-03-12 04:33:48','Usuario creado con ID trabajador: 2'),(9,'Creacion de usuario','supervisor','2026-03-12 04:34:36','Usuario creado con ID trabajador: 4'),(10,'crear insumo','superadmin','2026-03-12 04:35:46','ID: HERR-123, Nombre: Llave de Tubo, Categoría: 2'),(11,'crear insumo','superadmin','2026-03-12 04:36:39','ID: PLOM-124, Nombre: Tubo PVC, Categoría: 3'),(12,'editar insumo','superadmin','2026-03-12 04:37:04','ID: PLOM-124, Nombre: Tubo PVC (sin modificar cantidad)'),(13,'editar insumo','superadmin','2026-03-12 04:37:15','ID: HERR-123, Nombre: Llave de Tubo (sin modificar cantidad)'),(14,'Cierre de Sesion','superadmin','2026-03-12 04:37:35',NULL),(15,'Inicio de Sesion','superadmin','2026-03-12 04:37:44',NULL),(16,'Cierre de Sesion','superadmin','2026-03-12 04:37:48',NULL),(17,'Inicio de Sesion','obrero','2026-03-12 04:37:56',NULL),(18,'Cierre de Sesion','obrero','2026-03-12 04:38:08',NULL),(19,'Inicio de Sesion','supervisor','2026-03-14 04:14:29',NULL),(20,'Cierre de Sesion','supervisor','2026-03-14 04:14:48',NULL),(21,'Inicio de Sesion','almacenista','2026-03-14 04:15:00',NULL),(22,'crear incidencia','almacenista','2026-03-14 04:15:27','ID: 1, Descripción: se cayo una puerta'),(23,'Cierre de Sesion','almacenista','2026-03-14 04:15:31',NULL),(24,'Inicio de Sesion','supervisor','2026-03-14 04:15:39',NULL),(25,'crear fases incidencia','supervisor','2026-03-14 04:16:22','ID incidencia: 1, Tipo: Ejemplo plomería, Fases creadas: 2'),(26,'aprobar incidencia','supervisor','2026-03-14 04:16:22','ID: 1, Tipo: Ejemplo plomería, Trabajador asignado: Martín Martínez'),(27,'Cierre de Sesion','supervisor','2026-03-14 04:16:42',NULL),(28,'Inicio de Sesion','obrero','2026-03-14 04:16:50',NULL),(29,'Cierre de Sesion','obrero','2026-03-14 04:17:17',NULL),(30,'Inicio de Sesion','supervisor','2026-03-14 04:17:30',NULL),(31,'Cierre de Sesion','supervisor','2026-03-14 04:18:27',NULL),(32,'Inicio de Sesion','obrero','2026-03-14 04:18:40',NULL),(33,'Cierre de Sesion','obrero','2026-03-14 04:20:10',NULL),(34,'Inicio de Sesion','almacenista','2026-03-14 04:20:19',NULL),(35,'Cierre de Sesion','almacenista','2026-03-14 04:20:52',NULL),(36,'Inicio de Sesion','supervisor','2026-03-14 04:21:11',NULL),(37,'Cierre de Sesion','supervisor','2026-03-14 04:21:35',NULL),(38,'Inicio de Sesion','obrero','2026-03-14 04:21:46',NULL),(39,'Cierre de Sesion','obrero','2026-03-14 04:21:56',NULL),(40,'Inicio de Sesion','almacenista','2026-03-14 04:22:11',NULL),(41,'Cierre de Sesion','almacenista','2026-03-14 04:23:19',NULL),(42,'Inicio de Sesion','superadmin','2026-03-14 04:23:31',NULL),(43,'Cierre de Sesion','superadmin','2026-03-14 04:24:21',NULL),(44,'Inicio de Sesion','almacenista','2026-03-14 04:24:30',NULL),(45,'crear incidencia','almacenista','2026-03-14 04:24:50','ID: 2, Descripción: sadasdasdsad'),(46,'Cierre de Sesion','almacenista','2026-03-14 04:24:52',NULL),(47,'Inicio de Sesion','supervisor','2026-03-14 04:25:03',NULL),(48,'crear fases incidencia','supervisor','2026-03-14 04:25:15','ID incidencia: 2, Tipo: General, Fases creadas: 6'),(49,'aprobar incidencia','supervisor','2026-03-14 04:25:15','ID: 2, Tipo: General, Trabajador asignado: Martín Martínez'),(50,'Cierre de Sesion','supervisor','2026-03-14 04:25:17',NULL),(51,'Inicio de Sesion','obrero','2026-03-14 04:25:33',NULL),(52,'Cierre de Sesion','obrero','2026-03-14 04:26:29',NULL),(53,'Inicio de Sesion','superadmin','2026-03-14 04:26:38',NULL),(54,'crear incidencia','superadmin','2026-03-14 04:30:00','ID: 3, Descripción: sadasdasdad'),(55,'crear trabajador','superadmin','2026-03-14 04:32:10','ID: 5, Nombre: prueba martinez aa, Cédula: V-23253534'),(56,'Creacion de usuario','obrero2','2026-03-14 04:32:36','Usuario creado con ID trabajador: 5'),(57,'crear fases incidencia','superadmin','2026-03-14 04:32:46','ID incidencia: 3, Tipo: Prueba, Fases creadas: 2'),(58,'aprobar incidencia','superadmin','2026-03-14 04:32:46','ID: 3, Tipo: Prueba, Trabajador asignado: prueba martinez aa'),(59,'Cierre de Sesion','superadmin','2026-03-14 04:32:48',NULL),(60,'Inicio de Sesion','obrero2','2026-03-14 04:34:23',NULL),(61,'Cierre de Sesion','obrero2','2026-03-14 04:34:29',NULL),(62,'Inicio de Sesion','obrero','2026-03-14 04:34:41',NULL),(63,'Cierre de Sesion','obrero','2026-03-14 04:43:07',NULL),(64,'Inicio de Sesion','superadmin','2026-03-14 04:43:16',NULL),(65,'Inicio de Sesion','superadmin','2026-03-14 09:37:18',NULL),(66,'Inicio de Sesion','superadmin','2026-03-15 01:52:08',NULL),(67,'SESSION_TIMEOUT','superadmin','2026-03-15 01:52:09','Sesión cerrada por inactividad'),(68,'Inicio de Sesion','superadmin','2026-03-15 01:52:20',NULL),(69,'Inicio de Sesion','superadmin','2026-03-15 03:37:58',NULL),(70,'Inicio de Sesion','superadmin','2026-03-15 03:58:59',NULL),(71,'Cierre de Sesion','superadmin','2026-03-15 03:59:11',NULL),(72,'Inicio de Sesion','superadmin','2026-03-15 04:02:26',NULL);
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
INSERT INTO `departamentos` VALUES (1,'Mantenimiento','Departamento encargado del mantenimiento general de las instalaciones','Sótano','0212-555-1234','mantenimiento@clinica.com','Johander Hernández','2026-03-12 04:22:57'),(2,'Almacen','Almacena cosas','Edificio A piso b','02432131232','Almacen@gmail.com','Carlos Obregon','2026-03-12 04:30:52'),(3,'Compras','Compra cosas','casita casota','02432131232','Compras@gmail.com','Carlos Panaparo','2026-03-12 04:31:18');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_salida_almacen`
--

LOCK TABLES `detalle_salida_almacen` WRITE;
/*!40000 ALTER TABLE `detalle_salida_almacen` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_solicitud_material`
--

LOCK TABLES `detalle_solicitud_material` WRITE;
/*!40000 ALTER TABLE `detalle_solicitud_material` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_trabajador`
--

LOCK TABLES `detalle_trabajador` WRITE;
/*!40000 ALTER TABLE `detalle_trabajador` DISABLE KEYS */;
INSERT INTO `detalle_trabajador` VALUES (1,2,'Ejemplo plomería','basico'),(2,5,'Prueba','basico');
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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fases_incidencia`
--

LOCK TABLES `fases_incidencia` WRITE;
/*!40000 ALTER TABLE `fases_incidencia` DISABLE KEYS */;
INSERT INTO `fases_incidencia` VALUES (8,'Diagnóstico Inicial','Evaluación del problema y determinación de la causa raíz',1,'General',1,1),(9,'Reparación Principal','Ejecución de las reparaciones o mantenimiento necesario',2,'General',1,1),(10,'Pruebas de Funcionamiento','Verificación de que el equipo/sistema funciona correctamente',3,'General',1,1),(11,'Limpieza y Orden','Limpieza del área de trabajo y organización de herramientas',4,'General',1,1),(12,'Documentación Final','Registro de actividades realizadas y materiales utilizados',5,'General',0,1),(13,'Verificación de Tensión','Medición de voltaje y corriente en el circuito afectado',2,'Eléctrica',1,1),(14,'Reparación de Cableado','Sustitución o reparación de cables y conexiones',3,'Eléctrica',1,1),(15,'Localización de Fugas','Identificación de puntos de fuga en tuberías',2,'Plomería',1,1),(16,'Sustitución de Tuberías','Cambio de tuberías dañadas o corroídas',3,'Plomería',1,1),(17,'Limpieza de Filtros','Limpieza o sustitución de filtros de aire',2,'Aire Acondicionado',1,1),(19,'Diagnostico ferretería','ferretea',1,'Ferretería',1,1),(20,'Prueba ferretería','pruebita',2,'Ferretería',0,1),(30,'Confirmación del Solicitante','El usuario que reportó la incidencia verifica y acepta el trabajo realizado',6,'General',0,1),(33,'Diagnostico','diagnostico de tubo',1,'Ejemplo plomería',1,1),(35,'Reparación','Se debe reparar',2,'Ejemplo plomería',1,1),(37,'Test de wuebo','rfgtrgdgg',3,'Aire Acondicionado',1,1),(38,'Test de wuebo','rfgtrgdgg',3,'Aire Acondicionado',1,1),(39,'Diagnóstico','diagnostica',1,'Prueba',1,0),(40,'reparacion loca','repara pue',2,'Prueba',1,0);
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
INSERT INTO `incidencia_conformidad` VALUES (1,1,2,1,4,'','2026-03-14 00:22:58',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_fases`
--

LOCK TABLES `incidencia_fases` WRITE;
/*!40000 ALTER TABLE `incidencia_fases` DISABLE KEYS */;
INSERT INTO `incidencia_fases` VALUES (1,1,33,'aprobada','2026-03-14 00:17:05','2026-03-14 00:18:02',4,'muy bien','q loco','[\"\\/lugopata\\/assets\\/incidencias\\/1\\/fase_33_1773461825_0.jpg\"]'),(2,1,35,'aprobada','2026-03-14 00:19:02','2026-03-14 00:21:26',4,'muy bien loco','naguara','[\"\\/lugopata\\/assets\\/incidencias\\/1\\/fase_35_1773461942_0.jpg\"]'),(3,1,30,'aprobada','2026-03-14 00:22:58',NULL,NULL,'',NULL,NULL),(4,2,8,'completada','2026-03-14 00:25:50',NULL,NULL,NULL,'que locuraaaa','[\"\\/lugopata\\/assets\\/incidencias\\/2\\/fase_8_1773462350_0.jpg\"]'),(5,2,9,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(6,2,10,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(7,2,11,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(8,2,12,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(9,2,30,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(10,3,39,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(11,3,40,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencias`
--

LOCK TABLES `incidencias` WRITE;
/*!40000 ALTER TABLE `incidencias` DISABLE KEYS */;
INSERT INTO `incidencias` VALUES (1,'2026-03-14',NULL,'2026-03-14 00:22:58',2,1,'se cayo una puerta','piso b calle 2','Leve','Finalizada',2,2),(2,'2026-03-14',NULL,NULL,3,1,'sadasdasdsad','sadas','Urgente','Pendiente',2,2),(3,'2026-03-14',NULL,NULL,3,1,'sadasdasdad','sadsa','Urgente','Pendiente',1,5);
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
INSERT INTO `insumos` VALUES ('HERR-123','HERR',123,'Llave de Tubo','Una llave de tubo','Unidad(es)',1.00,1.00,5.00,'1773290146_Llave de tubo.jpg','2026-03-12 00:35:46',2),('PLOM-124','PLOM',124,'Tubo PVC','Un tubo pvc','Metro(s)',1.00,1.00,5.00,'1773290199_tubo pvc.jpg','2026-03-12 00:36:39',3);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recuperacion`
--

LOCK TABLES `recuperacion` WRITE;
/*!40000 ALTER TABLE `recuperacion` DISABLE KEYS */;
INSERT INTO `recuperacion` VALUES (1,1,'27ace6f88ec492e85ff105d9f1f1a72e3bc7aa93ea46703ad549f2807ae818a6','2026-03-15 06:01:35');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_materiales`
--

LOCK TABLES `solicitud_materiales` WRITE;
/*!40000 ALTER TABLE `solicitud_materiales` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_salida_almacen`
--

LOCK TABLES `solicitud_salida_almacen` WRITE;
/*!40000 ALTER TABLE `solicitud_salida_almacen` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_almacen`
--

LOCK TABLES `stock_almacen` WRITE;
/*!40000 ALTER TABLE `stock_almacen` DISABLE KEYS */;
INSERT INTO `stock_almacen` VALUES (1,'HERR-123',10.00,'2026-03-12 00:37:15'),(2,'PLOM-124',20.00,'2026-03-12 00:37:04');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajadores`
--

LOCK TABLES `trabajadores` WRITE;
/*!40000 ALTER TABLE `trabajadores` DISABLE KEYS */;
INSERT INTO `trabajadores` VALUES (1,'31.583.133','Johander','Hernández','04243031431','Caracas, Venezuela',NULL,1),(2,'V-21324213','Martín','Martínez','04122131232','La casona Casa 4','../assets/imagenes/firmas/firma_1773289818.png',1),(3,'V-23123123','Eladio','Carrion','04122131223','La casa b 24','../assets/imagenes/firmas/firma_1773289921.png',2),(4,'V-2312323','Ricky','Martin','04121232131','New york casa 24','../assets/imagenes/firmas/firma_1773289944.png',3),(5,'V-23253534','prueba martinez','aa','04221231212','sadsadasd','../assets/imagenes/firmas/firma_1773462730.png',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'superadmin','$2y$10$UoeA6QBkdo6fM3Ms8XdTNe7xWtVvecDcq3MXqE/0mU.ivZEej8SvK','Miguehernandez1508@gmail.com','superadministrador',1,0,0,NULL),(2,'almacenista','$2y$10$fKN3vgH0jeYD66rNAeyBTeLNKNGQl64R1ukTsTgHuD6GvdhBQDlbO','Almacenista@gmail.com','almacenista',3,0,0,NULL),(3,'obrero','$2y$10$eX8dQKfN6jH9zeUCVvp5e.z2mqqySUnvguBOzEIAXDs0Jryy30rZK','Obrero@gmail.com','obmantenimiento',2,0,0,NULL),(4,'supervisor','$2y$10$sRnx8KoXlXI1mWTKuCN7IO/2Ux7wKLT3dOYTo.p79JZjbvO.Lc0ee','supervisor@gmail.com','supmantenimiento',4,0,0,NULL),(5,'obrero2','$2y$10$QB9ysreIlY/syiNaMVJxoeZbCYyCU4H3ndzbKZQ5kK2zSbPlicQZe','asdsa@gmail.com','obmantenimiento',5,0,0,NULL);
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

-- Dump completed on 2026-03-15  0:03:06
