CREATE DATABASE  IF NOT EXISTS `jejakpetualang` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `jejakpetualang`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: jejakpetualang
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `transaksi_item`
--

DROP TABLE IF EXISTS `transaksi_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_item` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transaksi_id` int DEFAULT NULL,
  `produk_id` int DEFAULT NULL,
  `ukuran` varchar(50) NOT NULL,
  `jumlah` int DEFAULT NULL,
  `harga` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_transaksi_item_transaksi` (`transaksi_id`),
  KEY `fk_transaksi_item_produk` (`produk_id`),
  CONSTRAINT `fk_transaksi_item_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_item_transaksi` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transaksi_item_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_item_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_item`
--

LOCK TABLES `transaksi_item` WRITE;
/*!40000 ALTER TABLE `transaksi_item` DISABLE KEYS */;
INSERT INTO `transaksi_item` VALUES (1,1,12,'N/A',1,469900),(2,2,2,'N/A',1,664000),(3,3,12,'N/A',1,469900),(6,6,1,'N/A',1,0),(7,7,3,'N/A',1,575000),(8,8,9,'XXL',1,275000);
/*!40000 ALTER TABLE `transaksi_item` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-14 14:04:26
