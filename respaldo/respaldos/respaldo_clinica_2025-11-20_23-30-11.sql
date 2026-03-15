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
  `id_usuario` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `fk_auditoria_usuario` (`id_usuario`),
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (1,'Cierre de Sesion','superadmin','2025-10-10 03:15:59',NULL,NULL),(2,'Inicio de Sesion','superadmin','2025-10-10 03:16:27',NULL,NULL),(3,'Creacion de usuario','Ander','2025-10-10 03:21:15','Usuario creado con ID trabajador: 5',NULL),(4,'Creacion de usuario','Almacenista','2025-10-10 03:21:25','Usuario creado con ID trabajador: 2',NULL),(5,'Creacion de usuario','Supervisor','2025-10-10 03:21:35','Usuario creado con ID trabajador: 3',NULL),(6,'Creacion de usuario','Gerente','2025-10-10 03:21:44','Usuario creado con ID trabajador: 4',NULL),(7,'Inicio de Sesion','superadmin','2025-10-12 10:50:10',NULL,NULL),(8,'Inicio de Sesion','Ander','2025-10-28 07:37:06',NULL,NULL),(9,'Inicio de Sesion','Ander','2025-10-30 12:28:23',NULL,NULL),(10,'Cierre de Sesion','Ander','2025-10-30 12:37:49',NULL,NULL),(11,'Inicio de Sesion','superadmin','2025-10-30 12:37:56',NULL,NULL),(12,'Inicio de Sesion','superadmin','2025-10-30 19:39:01',NULL,NULL),(13,'Cierre de Sesion','superadmin','2025-10-30 22:00:34',NULL,NULL),(14,'Inicio de Sesion','superadmin','2025-10-30 22:01:41',NULL,NULL),(15,'Inicio de Sesion','superadmin','2025-11-02 08:09:37',NULL,NULL),(16,'Inicio de Sesion','superadmin','2025-11-02 15:50:49',NULL,NULL),(17,'Inicio de Sesion','superadmin','2025-11-14 09:56:31',NULL,NULL),(18,'Inicio de Sesion','superadmin','2025-11-19 03:24:34',NULL,NULL),(19,'Cierre de Sesion','superadmin','2025-11-20 11:28:16',NULL,NULL),(20,'Inicio de Sesion','superadmin','2025-11-20 11:30:07',NULL,NULL),(21,'Cierre de Sesion','superadmin','2025-11-20 11:35:19',NULL,NULL),(22,'Inicio de Sesion','superadmin','2025-11-20 11:35:38',NULL,NULL),(23,'Inicio de Sesion','superadmin','2025-11-20 11:44:13',NULL,NULL),(24,'Inicio de Sesion','superadmin','2025-11-20 11:44:39',NULL,NULL),(25,'Inicio de Sesion','superadmin','2025-11-20 11:46:32',NULL,NULL),(26,'Cierre de Sesion','superadmin','2025-11-20 11:50:01',NULL,NULL),(27,'Inicio de Sesion','superadmin','2025-11-20 11:50:08',NULL,NULL),(28,'Cierre de Sesion','superadmin','2025-11-20 11:52:37',NULL,NULL),(29,'Inicio de Sesion','superadmin','2025-11-20 11:52:46',NULL,NULL),(30,'Cierre de Sesion','superadmin','2025-11-20 11:55:06',NULL,NULL),(31,'Inicio de Sesion','superadmin','2025-11-20 11:55:11',NULL,NULL),(32,'Inicio de Sesion','superadmin','2025-11-20 12:16:42',NULL,NULL),(33,'SESSION_TIMEOUT','superadmin','2025-11-20 12:16:42','Sesión cerrada por inactividad',NULL),(34,'Inicio de Sesion','superadmin','2025-11-20 12:21:41',NULL,NULL),(35,'Cierre de Sesion','superadmin','2025-11-20 12:21:44',NULL,NULL),(36,'Inicio de Sesion','superadmin','2025-11-20 12:22:08',NULL,NULL),(37,'Cierre de Sesion','superadmin','2025-11-20 12:22:10',NULL,NULL),(38,'Inicio de Sesion','superadmin','2025-11-20 12:22:20',NULL,NULL),(39,'Cierre de Sesion','superadmin','2025-11-20 12:22:23',NULL,NULL),(40,'Inicio de Sesion','superadmin','2025-11-20 12:26:41',NULL,NULL),(41,'Inicio de Sesion','superadmin','2025-11-20 12:32:47',NULL,NULL),(42,'SESSION_TIMEOUT','superadmin','2025-11-20 12:32:47','Sesión cerrada por inactividad',NULL),(43,'Inicio de Sesion','superadmin','2025-11-20 12:33:01',NULL,NULL),(44,'Cierre de Sesion','superadmin','2025-11-20 12:33:17',NULL,NULL),(45,'USUARIO_BLOQUEADO','superadmin','2025-11-20 12:48:38','Usuario bloqueado por 30 minutos después de 3 intentos fallidos',NULL),(46,'Inicio de Sesion','superadmin','2025-11-20 12:56:38',NULL,NULL),(47,'Cierre de Sesion','superadmin','2025-11-20 12:56:54',NULL,NULL),(48,'USUARIO_BLOQUEADO','superadmin','2025-11-20 12:57:24','Usuario bloqueado permanentemente después de 3 intentos fallidos',NULL),(49,'CONTRASENA_RECUPERADA','Ander','2025-11-20 13:02:10','Contraseña recuperada - Usuario desbloqueado',NULL),(50,'Inicio de Sesion','superadmin2','2025-11-20 13:18:09',NULL,NULL),(51,'Cierre de Sesion','superadmin2','2025-11-20 13:18:12',NULL,NULL),(52,'Inicio de Sesion','superadmin3','2025-11-20 13:19:12',NULL,NULL),(53,'Cierre de Sesion','superadmin3','2025-11-20 13:19:25',NULL,NULL),(54,'CONTRASENA_RECUPERADA','superadmin3','2025-11-20 13:24:52','Contraseña recuperada - Usuario desbloqueado - Intentos reiniciados',NULL),(55,'Inicio de Sesion','superadmin3','2025-11-20 13:25:23',NULL,NULL),(56,'Cierre de Sesion','superadmin3','2025-11-20 13:25:25',NULL,NULL),(57,'Inicio de Sesion','superadmin3','2025-11-20 13:26:18',NULL,NULL),(58,'Cierre de Sesion','superadmin3','2025-11-20 13:26:35',NULL,NULL),(59,'USUARIO_BLOQUEADO','superadmin3','2025-11-20 13:27:08','Usuario bloqueado permanentemente después de 3 intentos fallidos',NULL),(60,'CONTRASENA_RECUPERADA','superadmin3','2025-11-20 13:28:15','Contraseña recuperada - Usuario desbloqueado - Intentos reiniciados',NULL),(61,'Inicio de Sesion','superadmin3','2025-11-20 13:28:26',NULL,NULL),(62,'Cierre de Sesion','superadmin3','2025-11-20 13:28:28',NULL,NULL),(63,'USUARIO_BLOQUEADO','superadmin3','2025-11-20 13:35:00','Usuario bloqueado permanentemente después de 3 intentos fallidos',NULL),(64,'CONTRASENA_RECUPERADA','superadmin3','2025-11-20 13:38:49','Contraseña recuperada',NULL),(65,'Inicio de Sesion','superadmin3','2025-11-20 13:39:34',NULL,NULL),(66,'Inicio de Sesion','superadmin3','2025-11-20 14:28:47',NULL,NULL),(67,'SESSION_TIMEOUT','superadmin3','2025-11-20 14:28:47','Sesión cerrada por inactividad',NULL),(68,'Inicio de Sesion','superadmin3','2025-11-20 14:28:58',NULL,NULL),(69,'Cierre de Sesion','superadmin3','2025-11-20 14:29:01',NULL,NULL),(70,'Inicio de Sesion','superadmin3','2025-11-21 10:08:48',NULL,NULL),(71,'SESSION_TIMEOUT','superadmin3','2025-11-21 02:48:18','Sesión cerrada por inactividad',NULL),(72,'Inicio de Sesion','superadmin3','2025-11-21 02:48:28',NULL,NULL),(73,'SESSION_TIMEOUT','superadmin','2025-11-20 23:44:03','Sesión cerrada por inactividad',NULL),(74,'Inicio de Sesion','superadmin3','2025-11-21 00:46:46',NULL,NULL),(75,'Inicio de Sesion','superadmin3','2025-11-21 03:29:15',NULL,NULL);
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
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_insumo`
--

LOCK TABLES `categorias_insumo` WRITE;
/*!40000 ALTER TABLE `categorias_insumo` DISABLE KEYS */;
INSERT INTO `categorias_insumo` VALUES (2,'Cañería'),(1,'Ferretería');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (1,'Mantenimiento','Departamento encargado de Mantener los equipos','Edificio Principal','0243-1234567','mmtt@clinica.com','Ana Pérez','2025-10-10 03:16:18'),(2,'Almacén','Almacena cosas','Piso 5 B','04221212312','Almacen@gmail.com','Almacenista hernandez','2025-10-10 03:16:49'),(3,'Compras','Compra cosas','Piso 5 C','04222131231','Compras@gmail.com','Compras Hernandez','2025-10-10 03:17:17');
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
INSERT INTO `detalle_salida_almacen` VALUES (1,1,'123',2.00,0.00,NULL,NULL,'2025-10-30 12:40:05');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_solicitud_material`
--

LOCK TABLES `detalle_solicitud_material` WRITE;
/*!40000 ALTER TABLE `detalle_solicitud_material` DISABLE KEYS */;
INSERT INTO `detalle_solicitud_material` VALUES (1,1,'123',2,0,NULL,'2025-10-30 23:33:48',NULL,NULL);
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
INSERT INTO `detalle_trabajador` VALUES (1,3,'Carpintería'),(2,4,'Electricidad'),(3,5,'Aires acondicionados');
/*!40000 ALTER TABLE `detalle_trabajador` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia_imagenes`
--

LOCK TABLES `incidencia_imagenes` WRITE;
/*!40000 ALTER TABLE `incidencia_imagenes` DISABLE KEYS */;
INSERT INTO `incidencia_imagenes` VALUES (1,1,'/lugopata/assets/incidencias/1/1762027828_0.jpg','2025-11-02 08:10:28'),(2,1,'/lugopata/assets/incidencias/1/1762055618_0.jpg','2025-11-02 15:53:38');
/*!40000 ALTER TABLE `incidencia_imagenes` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencias`
--

LOCK TABLES `incidencias` WRITE;
/*!40000 ALTER TABLE `incidencias` DISABLE KEYS */;
INSERT INTO `incidencias` VALUES (1,'2025-10-30','2025-10-09',NULL,3,3,'dsf','Leve','Pendiente',2,NULL);
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
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `unidad_medida` varchar(50) NOT NULL,
  `cantidad` decimal(10,2) DEFAULT 0.00,
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
INSERT INTO `insumos` VALUES ('123','Llave Inglesa','Una llave','Unidad(es)',0.00,'1760023366_llave-inglesa.png','2025-10-09 11:22:46',1),('1232','Llavesita','Una llave pequeña','Unidad(es)',0.00,'1760023542_revisar.png','2025-10-09 11:25:42',1),('1234','Tubo PVC','Un tubo','Metro(s)',0.00,'1760023418_Tubopvc.jpg','2025-10-09 11:23:38',2);
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
  PRIMARY KEY (`id`),
  KEY `fk_recuperacion_usuario` (`usuario_id`),
  CONSTRAINT `fk_recuperacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recuperacion`
--

LOCK TABLES `recuperacion` WRITE;
/*!40000 ALTER TABLE `recuperacion` DISABLE KEYS */;
INSERT INTO `recuperacion` VALUES (4,2,'45875c86f7df923488559a0dd8445bc503a621ced51e5c928d1952e768abe9f8','2025-11-20 03:06:05');
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
  `descripcion` text DEFAULT NULL,
  `id_aprobador` int(11) DEFAULT NULL,
  `estado` enum('En espera','Pendiente','Finalizada','Rechazada') DEFAULT 'En espera',
  PRIMARY KEY (`id_solicitud`),
  KEY `fk_departamento_emisor` (`departamento_emisor`),
  KEY `fk_departamento_destino` (`departamento_destino`),
  KEY `fk_aprobador` (`id_aprobador`),
  CONSTRAINT `fk_aprobador` FOREIGN KEY (`id_aprobador`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_departamento_destino` FOREIGN KEY (`departamento_destino`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_departamento_emisor` FOREIGN KEY (`departamento_emisor`) REFERENCES `departamentos` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_materiales`
--

LOCK TABLES `solicitud_materiales` WRITE;
/*!40000 ALTER TABLE `solicitud_materiales` DISABLE KEYS */;
INSERT INTO `solicitud_materiales` VALUES (1,'2025-10-30','Johander Hernández','Alejando martinez',2,2,'necesito',NULL,'En espera');
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
INSERT INTO `solicitud_salida_almacen` VALUES (1,'2025-10-30','Johander Hernández',3,'sad',3,'sad','En espera');
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
INSERT INTO `stock_almacen` VALUES (1,'123',0.00,'2025-10-09 11:22:46'),(2,'1234',0.00,'2025-10-09 11:23:38'),(3,'1232',0.00,'2025-10-09 11:25:42');
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
INSERT INTO `trabajadores` VALUES (1,'31583133','Johander','Hernández','04147654321','Av. Principal #123',NULL,1),(2,'12345','Almacenista','Hernández','04222312312','Lugar','../assets/imagenes/firmas/firma_1760023063.png',2),(3,'13242','Supervisor','Hernández','04222321321','Lugar x2','../assets/imagenes/firmas/firma_1760023135.png',1),(4,'315831233','Gerente','Hernandez','04222131232','Piso 5b','../assets/imagenes/firmas/firma_1760023173.png',1),(5,'31583132','Johander','Hernández','04243031431','Casa','../assets/imagenes/firmas/firma_1760023254.png',1);
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
  `ultimo_intento` datetime DEFAULT NULL,
  `password_anterior` varchar(255) DEFAULT NULL,
  `bloqueado` tinyint(1) DEFAULT 0,
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
INSERT INTO `usuarios` VALUES (2,'Ander','$2y$10$/KdT9ZBmlG3eMhYbwW5vd.NhG9BCZmwx.OGNAsvf7Di.tcciS09bW','Miguehersdnandez1508@gmail','admin',5,0,NULL,'$2y$10$/KdT9ZBmlG3eMhYbwW5vd.NhG9BCZmwx.OGNAsvf7Di.tcciS09bW',0),(3,'Almacenista','$2y$10$X2TJprWMLrDkFQYWIidJy.4huU6mz78ynM43czKzbZs9DbQKo06va','Almacenista@gmail.com','almacenista',2,0,NULL,NULL,0),(4,'Supervisor','$2y$10$BiXV7K96.fl9uenrmXUuWuuhzFsR7RxQaVAdQRqvP6wD0Lx1JUqwS','Sup@gmail.com','supmantenimiento',3,0,NULL,NULL,0),(5,'Gerente','$2y$10$dd082BLK9pE9hNDfUNY6Ne9O/wVX...fuy7beUhpfwxxiwZXXTkMO','Admin@gmail.com','admin',4,0,NULL,NULL,0),(7,'superadmin2','$2y$10$UoeA6QBkdo6fM3Ms8XdTNe7xWtVvecDcq3MXqE/0mU.ivZEej8SvK','superadmin@empresa.com','superadministrador',1,0,NULL,NULL,0),(8,'superadmin3','$2y$10$95wwoKSIYaszBmf2KVbjeOUuggw3qr1Ab4TuLQ1/kmyg0Y30fHTVG','Miguehernandez1508@gmail.com','superadministrador',1,0,NULL,'$2y$10$x8EFNPkb/nyJmd0sdvrDWOL6L5PO4jMaZGNdmGl04vJkqNhJ5NhiS',0);
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

-- Dump completed on 2025-11-20 23:30:12
