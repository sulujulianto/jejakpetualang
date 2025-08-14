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
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(50) NOT NULL,
  `user_id` int DEFAULT NULL,
  `total` int DEFAULT NULL,
  `alamat_pengiriman` text,
  `status` enum('Menunggu Pembayaran','Diproses','Dikirim','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran',
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `nomor_resi` varchar(100) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `tanggal_transaksi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  KEY `transaksi_ibfk_1` (`user_id`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (1,'INV-6878A73CC1B3A',2,234950,'JL PPKPI JAKARTA','Selesai','COD',NULL,NULL,'2025-07-17 07:33:16'),(2,'INV-6879B42C560F3',2,0,'JL PPKPI JAKARTA','Selesai','E-Wallet',NULL,NULL,'2025-07-18 02:40:44'),(3,'INV-6879D09772042',2,234950,'JL PPKPI JAKARTA','Selesai','Transfer Bank',NULL,NULL,'2025-07-18 04:41:59'),(6,'INV-6899735A7AF62',2,0,'JL PPKPI JAKARTA','Selesai','COD',NULL,NULL,'2025-08-11 04:36:42'),(7,'INV-6899986630E46',2,0,'JL PPKPI JAKARTA','Diproses','COD',NULL,NULL,'2025-08-11 07:14:46'),(8,'INV-689C40D8B7FB2',2,275000,'JL PPKPI JAKARTA','Diproses','COD',NULL,NULL,'2025-08-13 07:38:00');
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-14 14:04:27
