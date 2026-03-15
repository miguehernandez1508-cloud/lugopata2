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
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (1,'RESTABLECIMIENTO_COMPLETO','superadmin','2025-11-29 18:59:18','Restablecimiento completo del sistema ejecutado el 2025-11-29 14:59:18'),(2,'crear trabajador','superadmin','2025-11-29 19:01:55','ID: 2, Nombre: Supervisor Hernández, Cédula: 3029402'),(3,'crear trabajador','superadmin','2025-11-29 19:02:38','ID: 3, Nombre: Gerente Manuel, Cédula: 30294824'),(4,'crear departamento','superadmin','2025-11-29 19:03:39','Departamento: Almacén'),(5,'crear trabajador','superadmin','2025-11-29 19:04:11','ID: 4, Nombre: Almacenista Hernández, Cédula: 3252342'),(6,'crear trabajador','superadmin','2025-11-29 19:04:50','ID: 5, Nombre: Obrero Hernández, Cédula: 23213242'),(7,'Creacion de usuario','Supervisor','2025-11-29 19:05:27','Usuario creado con ID trabajador: 2'),(8,'Creacion de usuario','Almacenista','2025-11-29 19:05:54','Usuario creado con ID trabajador: 4'),(9,'Creacion de usuario','Obrero','2025-11-29 19:06:22','Usuario creado con ID trabajador: 5'),(10,'Creacion de usuario','Gerente','2025-11-29 19:06:39','Usuario creado con ID trabajador: 3'),(11,'Cierre de Sesion','superadmin','2025-11-29 19:10:27',NULL),(12,'Inicio de Sesion','superadmin','2025-11-29 19:48:44',NULL),(13,'Inicio de Sesion','Gerente','2025-11-29 19:55:54',NULL),(14,'SESSION_TIMEOUT','Gerente','2025-11-29 19:55:54','Sesión cerrada por inactividad'),(15,'Inicio de Sesion','Gerente','2025-11-29 19:56:08',NULL),(16,'crear trabajador','Gerente','2025-11-29 19:57:00','ID: 6, Nombre: Juanchito hernandez, Cédula: 23123123'),(17,'Creacion de usuario','Almacenista2','2025-11-29 19:57:33','Usuario creado con ID trabajador: 6'),(18,'Actualizacion de usuario','Almacenista2','2025-11-29 19:58:17','Usuario actualizado: Almacenista2 (ID: 6)'),(19,'Actualizacion de usuario','Almacenista2','2025-11-29 19:59:07','Usuario actualizado: Almacenista2 (ID: 6)'),(20,'Actualizacion de usuario','Almacenista2','2025-11-29 20:04:31','Usuario actualizado: Almacenista2 (ID: 6)'),(21,'crear trabajador','Gerente','2025-11-29 20:05:25','ID: 7, Nombre: ejemplo Hernández, Cédula: 123123'),(22,'Creacion de usuario','Almacenista3','2025-11-29 20:05:53','Usuario creado con ID trabajador: 7'),(23,'Actualizacion de usuario','Almacenista3','2025-11-29 20:06:10','Usuario actualizado: Almacenista3 (ID: 7)'),(24,'Eliminacion de usuario','Almacenista3','2025-11-29 20:06:21','Usuario eliminado: Almacenista3 (ID: 7)'),(25,'Cierre de Sesion','Gerente','2025-11-29 20:07:32',NULL),(26,'USUARIO_BLOQUEADO','Obrero','2025-11-29 20:08:28','Usuario bloqueado permanentemente después de 3 intentos fallidos'),(27,'Inicio de Sesion','Almacenista','2025-11-29 20:10:52',NULL),(28,'crear incidencia','Almacenista','2025-11-29 20:11:10','ID: 1, Descripción: Se tapó el baño'),(29,'Cierre de Sesion','Almacenista','2025-11-29 20:11:12',NULL),(30,'Inicio de Sesion','Supervisor','2025-11-29 20:11:27',NULL),(31,'Cierre de Sesion','Supervisor','2025-11-29 20:11:53',NULL),(32,'Inicio de Sesion','Almacenista','2025-11-29 20:12:08',NULL),(33,'crear insumo','Almacenista','2025-11-29 20:16:11','ID: PLO-123, Nombre: Llave de tubo, Categoría: 7'),(34,'crear insumo','Almacenista','2025-11-29 20:20:09','ID: PLO2-1234, Nombre: Tubo PVC, Categoría: 8'),(35,'eliminar insumo','Almacenista','2025-11-29 20:20:46','ID: PLO2-1234, Nombre: Tubo PVC'),(36,'crear insumo','Almacenista','2025-11-29 20:22:16','ID: PLO3-12398, Nombre: Tubo PVC, Categoría: 9'),(37,'Cierre de Sesion','Almacenista','2025-11-29 20:23:21',NULL),(38,'Inicio de Sesion','superadmin','2025-11-29 20:23:34',NULL),(39,'crear departamento','superadmin','2025-11-29 20:24:03','Departamento: Compras'),(40,'Cierre de Sesion','superadmin','2025-11-29 20:24:05',NULL),(41,'Inicio de Sesion','Almacenista','2025-11-29 20:24:21',NULL),(42,'eliminar insumo','Almacenista','2025-11-29 20:25:34','ID: PLO3-12398, Nombre: Tubo PVC'),(43,'crear insumo','Almacenista','2025-11-29 20:27:30','ID: PLM2-48935, Nombre: Tubo PVC, Categoría: 12'),(44,'Cierre de Sesion','Almacenista','2025-11-29 20:31:18',NULL),(45,'Inicio de Sesion','Almacenista','2025-11-29 20:31:28',NULL),(46,'crear incidencia','Almacenista','2025-11-29 20:32:17','ID: 2, Descripción: SE HA ROTO UN TUBO'),(47,'Cierre de Sesion','Almacenista','2025-11-29 20:32:54',NULL),(48,'Inicio de Sesion','Supervisor','2025-11-29 20:33:01',NULL),(49,'crear trabajador','Supervisor','2025-11-29 20:36:56','ID: 8, Nombre: Plomero Hernández, Cédula: 2384834'),(50,'crear trabajador','Supervisor','2025-11-29 20:39:38','ID: 9, Nombre: Plomero Hernández, Cédula: 2838238'),(51,'crear fases incidencia','Supervisor','2025-11-29 20:43:10','ID incidencia: 1, Tipo: Plomería, Fases creadas: 2'),(52,'aprobar incidencia','Supervisor','2025-11-29 20:43:10','ID: 1, Tipo: Plomería, Trabajador asignado: Johander Hernández'),(53,'crear trabajador','Supervisor','2025-11-29 20:47:25','ID: 10, Nombre: Plomero2 Hernández, Cédula: 23123'),(54,'crear fases incidencia','Supervisor','2025-11-29 20:48:22','ID incidencia: 2, Tipo: Plomería ejemplo, Fases creadas: 2'),(55,'aprobar incidencia','Supervisor','2025-11-29 20:48:22','ID: 2, Tipo: Plomería ejemplo, Trabajador asignado: Plomero2 Hernández'),(56,'Cierre de Sesion','Supervisor','2025-11-29 20:49:27',NULL),(57,'Inicio de Sesion','Almacenista','2025-11-29 20:50:05',NULL),(58,'crear incidencia','Almacenista','2025-11-29 20:50:15','ID: 3, Descripción: SE HA ROTO UN TUBO'),(59,'Cierre de Sesion','Almacenista','2025-11-29 20:50:17',NULL),(60,'Inicio de Sesion','Supervisor','2025-11-29 20:50:26',NULL),(61,'crear trabajador','Supervisor','2025-11-29 20:53:25','ID: 11, Nombre: Plomero3 Hernández, Cédula: 1283823'),(62,'crear fases incidencia','Supervisor','2025-11-29 20:54:14','ID incidencia: 3, Tipo: Plomería ejemplo2, Fases creadas: 2'),(63,'aprobar incidencia','Supervisor','2025-11-29 20:54:14','ID: 3, Tipo: Plomería ejemplo2, Trabajador asignado: Plomero3 Hernández'),(64,'Cierre de Sesion','Supervisor','2025-11-29 20:55:36',NULL),(65,'Inicio de Sesion','Gerente','2025-11-29 20:55:55',NULL),(66,'Creacion de usuario','Obrero2','2025-11-29 20:56:23','Usuario creado con ID trabajador: 11'),(67,'Cierre de Sesion','Gerente','2025-11-29 20:56:24',NULL),(68,'Inicio de Sesion','Obrero2','2025-11-29 20:56:33',NULL),(69,'Cierre de Sesion','Obrero2','2025-11-29 20:58:59',NULL),(70,'Inicio de Sesion','Supervisor','2025-11-29 20:59:20',NULL),(71,'Inicio de Sesion','Supervisor','2025-11-30 00:36:10',NULL),(72,'SESSION_TIMEOUT','Supervisor','2025-11-30 00:36:10','Sesión cerrada por inactividad'),(73,'Inicio de Sesion','Supervisor','2025-11-30 00:36:19',NULL),(74,'Cierre de Sesion','Supervisor','2025-11-30 00:39:23',NULL),(75,'Inicio de Sesion','Almacenista','2025-11-30 00:39:31',NULL),(76,'Cierre de Sesion','Almacenista','2025-11-30 00:40:04',NULL),(77,'Inicio de Sesion','Gerente','2025-11-30 00:40:19',NULL),(78,'Aprobación de solicitud','Gerente','2025-11-30 00:41:55','Gerente aprobó solicitud 1: Se aprueba'),(79,'Cierre de Sesion','Gerente','2025-11-30 00:42:27',NULL),(80,'Inicio de Sesion','Almacenista','2025-11-30 00:42:35',NULL),(81,'Cierre de Sesion','Almacenista','2025-11-30 00:45:49',NULL),(82,'Inicio de Sesion','Supervisor','2025-11-30 00:46:02',NULL),(83,'Cierre de Sesion','Supervisor','2025-11-30 00:47:26',NULL),(84,'Inicio de Sesion','superadmin','2025-11-30 00:47:34',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_insumo`
--

LOCK TABLES `categorias_insumo` WRITE;
/*!40000 ALTER TABLE `categorias_insumo` DISABLE KEYS */;
INSERT INTO `categorias_insumo` VALUES (1,'Materiales Eléctricos','Materiales para instalaciones eléctricas','ELEC'),(2,'Herramientas','Herramientas de trabajo general','HERR'),(3,'Materiales de Plomería','Materiales para reparaciones de plomería','PLOM'),(4,'Materiales de Carpintería','Materiales para trabajos de carpintería','CARP'),(5,'Limpieza','Productos de limpieza y aseo','LIMP'),(6,'Cañería','Tubos etc','CAA'),(7,'Plomería','Insumos de plomería','PLO'),(8,'Plomería2','cosas de plomeria','PLO2'),(9,'Plomería3','Cosas de plomeria','PLO3'),(10,'Plomería4','Plomeria','PLM'),(12,'Plomería5','plomeria','PLM2');
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
INSERT INTO `departamentos` VALUES (1,'Mantenimiento','Departamento encargado del mantenimiento general de las instalaciones','Sótano','0212-555-1234','mantenimiento@empresa.com','Johander Hernández','2025-11-29 18:59:17'),(2,'Almacén','Lugar donde se almacenan los insumos','piso 2 departamento 5','02432321321','Almacen@clugo.com','Manuel almacén','2025-11-29 19:03:39'),(3,'Compras','compra cosas','piso 2 departamento 6','02122132132','compras@gmail.com','Manuel compras','2025-11-29 20:24:03');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_salida_almacen`
--

LOCK TABLES `detalle_salida_almacen` WRITE;
/*!40000 ALTER TABLE `detalle_salida_almacen` DISABLE KEYS */;
INSERT INTO `detalle_salida_almacen` VALUES (1,1,'PLO-123',5.00,3.00,'../assets/imagenes/firmas/firma_1764443051.png',NULL,'2025-11-29 20:17:19'),(2,2,'PLM2-48935',200.00,200.00,'../assets/imagenes/firmas/firma_1764443051.png',NULL,'2025-11-29 20:28:21'),(3,2,'PLO-123',2.00,2.00,'../assets/imagenes/firmas/firma_1764443051.png',NULL,'2025-11-29 20:28:21');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_solicitud_material`
--

LOCK TABLES `detalle_solicitud_material` WRITE;
/*!40000 ALTER TABLE `detalle_solicitud_material` DISABLE KEYS */;
INSERT INTO `detalle_solicitud_material` VALUES (1,1,'PLO-123',2,2,'../assets/imagenes/firmas/firma_1764443051.png','2025-11-30 00:38:33','../assets/imagenes/firmas/firma_1764442915.png','../assets/imagenes/firmas/firma_1764442958.png'),(2,1,'PLM2-48935',20,20,'../assets/imagenes/firmas/firma_1764443051.png','2025-11-30 00:38:33','../assets/imagenes/firmas/firma_1764442915.png','../assets/imagenes/firmas/firma_1764442958.png');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_trabajador`
--

LOCK TABLES `detalle_trabajador` WRITE;
/*!40000 ALTER TABLE `detalle_trabajador` DISABLE KEYS */;
INSERT INTO `detalle_trabajador` VALUES (1,1,'Electricidad','avanzado'),(2,1,'Plomería','avanzado'),(3,1,'Carpintería','avanzado'),(4,1,'Aires acondicionados','avanzado'),(5,1,'Computadoras','avanzado'),(6,1,'Pintura','avanzado'),(7,1,'Albañilería','avanzado'),(8,2,'baño','intermedio'),(9,3,'Eléctrica','basico'),(10,5,'Plomería','basico'),(11,8,'Plomería','basico'),(12,9,'Plomería','basico'),(13,10,'Plomería ejemplo','basico'),(14,11,'Plomería ejemplo2','basico');
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fases_incidencia`
--

LOCK TABLES `fases_incidencia` WRITE;
/*!40000 ALTER TABLE `fases_incidencia` DISABLE KEYS */;
INSERT INTO `fases_incidencia` VALUES (8,'Diagnóstico Inicial','Evaluación del problema y determinación de la causa raíz',1,'General',1),(9,'Reparación Principal','Ejecución de las reparaciones o mantenimiento necesario',2,'General',1),(10,'Pruebas de Funcionamiento','Verificación de que el equipo/sistema funciona correctamente',3,'General',1),(11,'Limpieza y Orden','Limpieza del área de trabajo y organización de herramientas',4,'General',1),(12,'Documentación Final','Registro de actividades realizadas y materiales utilizados',5,'General',0),(13,'Verificación de Tensión','Medición de voltaje y corriente en el circuito afectado',2,'Eléctrica',1),(14,'Reparación de Cableado','Sustitución o reparación de cables y conexiones',3,'Eléctrica',1),(15,'Localización de Fugas','Identificación de puntos de fuga en tuberías',2,'Plomería',1),(16,'Sustitución de Tuberías','Cambio de tuberías dañadas o corroídas',3,'Plomería',1),(17,'Limpieza de Filtros','Limpieza o sustitución de filtros de aire',2,'Aire Acondicionado',1),(18,'Verificación de Gas','Comprobación de nivel de refrigerante',3,'Aire Acondicionado',1),(19,'Diagnostico ferretería','ferretea',1,'Ferretería',1),(20,'Prueba ferretería','pruebita',2,'Ferretería',0),(21,'hacer locuras','pues hacer locuritas',2,'una locurita',1),(22,'Diagnóstico de pupu','ver la caca',1,'baño',1),(23,'Diagnóstico 2','kkj',2,'baño',1),(24,'Diagnostico de tubo','Diagnosticar la cañería',1,'Ejemplo cañería',1),(25,'Reparación','reparación de tubo',2,'Ejemplo cañería',1),(26,'Diagnostico de fuga','Diagnosticar la fuga',1,'Plomería ejemplo',1),(27,'Reemplazo de tubo','reemplazar el tubo',2,'Plomería ejemplo',1),(28,'Diagnóstico de fuga','Diagnosticar la fua',1,'Plomería ejemplo2',1),(29,'Reemplazo de tubo','reemplazar el tubo',2,'Plomería ejemplo2',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_fases`
--

LOCK TABLES `incidencia_fases` WRITE;
/*!40000 ALTER TABLE `incidencia_fases` DISABLE KEYS */;
INSERT INTO `incidencia_fases` VALUES (1,1,15,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(2,1,16,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(3,2,26,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(4,2,27,'pendiente',NULL,NULL,NULL,NULL,NULL,NULL),(5,3,28,'aprobada','2025-11-29 16:57:58','2025-11-29 16:59:55',2,'Muy bien hecho','estuvo dificil','[\"\\/lugopata\\/assets\\/incidencias\\/3\\/fase_28_1764449878_0.jpg\"]'),(6,3,29,'aprobada','2025-11-29 16:58:19','2025-11-29 17:00:04',2,'Bien','Estuvo dificil x2','[\"\\/lugopata\\/assets\\/incidencias\\/3\\/fase_29_1764449899_0.jpg\"]');
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
INSERT INTO `incidencias` VALUES (1,'2025-11-29',NULL,NULL,2,1,'Se tapó el baño','piso 2 departamento 5','Urgente','Pendiente',3,1),(2,'2025-11-29',NULL,NULL,2,1,'SE HA ROTO UN TUBO','piso 2 departamento 5','Urgente','Pendiente',3,10),(3,'2025-11-29',NULL,'2025-11-29 17:00:04',2,1,'SE HA ROTO UN TUBO','piso 2 departamento 5','Urgente','Finalizada',3,11);
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
INSERT INTO `insumos` VALUES ('PLM2-48935','PLM2',48935,'Tubo PVC','Tubo','Metro(s)',20.00,1.00,200.00,'1764448050_tubo pvc.png','2025-11-29 16:27:30',12),('PLO-123','PLO',123,'Llave de tubo','llave ','Unidad(es)',2.00,1.00,5.00,'1764447371_Llave de tubo.jpg','2025-11-29 16:16:11',7);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_materiales`
--

LOCK TABLES `solicitud_materiales` WRITE;
/*!40000 ALTER TABLE `solicitud_materiales` DISABLE KEYS */;
INSERT INTO `solicitud_materiales` VALUES (1,'2025-11-30','Supervisor Hernández','Almacenista',1,2,1,NULL,'Se tapó el baño ayuda!',5,'Finalizada','Se aprueba');
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
INSERT INTO `solicitud_salida_almacen` VALUES (1,'2025-11-29','Almacenista Hernández',2,'Almacenisto',1,'Solicitud de reposición de stock para: Llave de tubo','En espera'),(2,'2025-11-29','Almacenista Hernández',2,'Almacenista',3,'Solicitud de reposición de stock para: Tubo PVC','Finalizada');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_almacen`
--

LOCK TABLES `stock_almacen` WRITE;
/*!40000 ALTER TABLE `stock_almacen` DISABLE KEYS */;
INSERT INTO `stock_almacen` VALUES (1,'PLO-123',3.00,'2025-11-29 20:45:40'),(5,'PLM2-48935',180.00,'2025-11-29 20:45:39');
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajadores`
--

LOCK TABLES `trabajadores` WRITE;
/*!40000 ALTER TABLE `trabajadores` DISABLE KEYS */;
INSERT INTO `trabajadores` VALUES (1,'31.583.133','Johander','Hernández','04243031431','Caracas, Venezuela',NULL,1),(2,'3029402','Supervisor','Hernández','04122312312','Casa averico #23','../assets/imagenes/firmas/firma_1764442915.png',1),(3,'30294824','Gerente','Manuel','04241232131','Casa #4 avenida los obos','../assets/imagenes/firmas/firma_1764442958.png',1),(4,'3252342','Almacenista','Hernández','04241231232','Casa 25 #5','../assets/imagenes/firmas/firma_1764443051.png',2),(5,'23213242','Obrero','Hernández','04222321321','Casa 24 avenida bolívar','../assets/imagenes/firmas/firma_1764443090.png',1),(6,'23123123','Juanchito','hernandez','04122132131','casa','../assets/imagenes/firmas/firma_1764446220.png',2),(7,'123123','ejemplo','Hernández','04222213123','casa','../assets/imagenes/firmas/firma_1764446725.png',2),(8,'2384834','Plomero','Hernández','04222131232','Casa b 23','../assets/imagenes/firmas/firma_1764448616.png',1),(9,'2838238','Plomero','Hernández','04222328382','Piso b','../assets/imagenes/firmas/firma_1764448777.png',1),(10,'23123','Plomero2','Hernández','04222131232','Casa 5','../assets/imagenes/firmas/firma_1764449245.png',1),(11,'1283823','Plomero3','Hernández','04221231231','casa 3','../assets/imagenes/firmas/firma_1764449605.png',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'superadmin','$2y$10$UoeA6QBkdo6fM3Ms8XdTNe7xWtVvecDcq3MXqE/0mU.ivZEej8SvK','Miguehernandez1508@gmail.com','superadministrador',1,0,0,NULL),(2,'Supervisor','$2y$10$k1PUYmdFW1vkRoCuJgfAgu/NiKlYkyUTNNcFkLqLl8hlgyL1RHLUK','migue@gmail.com','supmantenimiento',2,0,0,NULL),(3,'Almacenista','$2y$10$MXpzOI/lWrXJ56I.KjiHLuFP6pOzz8sOGdIVOm5SxriOPAGgQm9tO','almacenista@gmail.com','almacenista',4,0,0,NULL),(4,'Obrero','$2y$10$tvidsCI6TL4fVrirtP3fkeqe5jphBYTPpxfm76pL3gRSbiztuKQDW','Obrero@gmail.com','obmantenimiento',5,3,1,NULL),(5,'Gerente','$2y$10$k1cjdXlnMErESFQDSOFKEOxG2oP8bLzSGPQIMPh6pUsMac9r.TT1u','Gerente@gmail.com','admin',3,0,0,NULL),(6,'Almacenista2','$2y$10$7HC8hwMyh1xIKgzFhKAKouPoU2lkUPiuP6fplDewyCThvGGVw/6HS','ejemplo3@gmail.com','almacenista',6,0,0,NULL),(8,'Obrero2','$2y$10$D0kC0EqewG5KJWccmL984OWWmU7UwkPsFg4mIN3s3oHXBFBvEPI9i','sadjsad@gmail.com','obmantenimiento',11,0,0,NULL);
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

-- Dump completed on 2025-11-29 20:48:23
