-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 24, 2025 at 03:33 AM
-- Server version: 8.0.42-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `newportald3ti`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('superadmin','admin_ta','admin_pengelola') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin123', 'admin123@gmail.com', '$2y$12$STRphShhfeZhKvSkOPhrjeMwQg1epc.Oa.fh2XLR/Gyf4veGzx0qq', 'superadmin', '2025-02-25 00:12:25', '2025-07-17 04:18:29'),
(2, 'admin12', 'admin12@gmail.com', '$2y$12$NfyhoIPLABpK0WCJQhTCCuBEfVF8HoWIT1RRYf0vFnQP6LwX4qGpC', 'admin_ta', '2025-02-25 00:12:41', '2025-02-25 00:12:41'),
(3, 'Admin Pengelola', 'admin1@gmail.com', '$2y$12$mVmLI2ouZ0KjptCx6PiqGOTfIYsv0pH3p2gkDRbydd.TqWEYottd2', 'admin_pengelola', '2025-02-25 00:12:51', '2025-07-23 03:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `bhps`
--

CREATE TABLE `bhps` (
  `id_bhp` bigint UNSIGNED NOT NULL,
  `nama_bhp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kondisi_bagus` int NOT NULL,
  `kondisi_rusak` int NOT NULL,
  `qr_code_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peruntukan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bhps`
--

INSERT INTO `bhps` (`id_bhp`, `nama_bhp`, `kondisi_bagus`, `kondisi_rusak`, `qr_code_path`, `peruntukan`, `created_at`, `updated_at`) VALUES
(1, 'Modul Relay', 2, 3, 'cd4d6026-25a9-45e3-a8e0-296f062e6a0d.png', 'Praktik', '2025-03-03 02:12:59', '2025-05-17 11:37:27'),
(2, 'Sensor Suhu', 94, 13, '5bf20311-b2b9-4d9e-bd0e-4847d7c5712c.png', 'KBM', '2025-03-03 02:40:34', '2025-04-17 07:56:57'),
(3, 'Kapasitor 100uF', 4, 2, '1372bcbd-7b3c-499f-8f34-87f1953819d2.png', 'KBM', '2025-03-03 02:41:02', '2025-06-12 23:58:36'),
(4, 'LED 5mm', 5, 2, NULL, 'Praktik', NULL, '2025-05-17 11:42:38'),
(5, 'Resistor 10kΩ', 100, 5, 'resistor-10k.png', 'Praktik', '2025-03-04 03:00:00', '2025-04-17 08:00:00'),
(6, 'Breadboard Mini', 40, 2, 'breadboard-mini.png', 'KBM', '2025-03-04 03:01:00', '2025-04-17 08:00:00'),
(7, 'Transistor BC547', 60, 4, 'transistor-bc547.png', 'Praktik', '2025-03-04 03:02:00', '2025-04-17 08:00:00'),
(8, 'Arduino Uno', 12, 1, 'arduino-uno.png', 'Praktik', '2025-03-04 03:03:00', '2025-04-17 08:00:00'),
(9, 'LCD 16x2', 8, 0, 'lcd-16x2.png', 'Praktik', '2025-03-04 03:04:00', '2025-04-17 08:00:00'),
(10, 'Sensor Cahaya LDR', 33, 2, 'ldr-sensor.png', 'KBM', '2025-03-04 03:05:00', '2025-04-17 08:00:00'),
(11, 'IC 555 Timer', 20, 1, 'ic-555.png', 'Praktik', '2025-03-04 03:06:00', '2025-04-17 08:00:00'),
(12, 'Kabel Jumper Male-Male', 200, 10, 'kabel-jumper.png', 'Praktik', '2025-03-04 03:07:00', '2025-04-17 08:00:00'),
(13, 'Buzzer 5V', 18, 0, 'buzzer-5v.png', 'Praktik', '2025-03-04 03:08:00', '2025-04-17 08:00:00'),
(14, 'Sensor Gas MQ-2', 7, 1, 'mq2-sensor.png', 'KBM', '2025-03-04 03:09:00', '2025-04-17 08:00:00'),
(15, 'Servo Motor SG90', 15, 1, 'servo-sg90.png', 'Praktik', '2025-03-04 03:10:00', '2025-04-17 08:00:00'),
(16, 'Potensiometer 10kΩ', 90, 5, 'potensiometer.png', 'KBM', '2025-03-04 03:11:00', '2025-04-17 08:00:00'),
(17, 'IC 4017 Decade Counter', 30, 2, 'ic-4017.png', 'KBM', '2025-03-04 03:12:00', '2025-04-17 08:00:00'),
(18, 'Display 7-Segment', 22, 1, '7segment.png', 'Praktik', '2025-03-04 03:13:00', '2025-04-17 08:00:00'),
(19, 'ESP32 Dev Board', 10, 1, 'esp32.png', 'KBM', '2025-03-04 03:14:00', '2025-04-17 08:00:00'),
(20, 'Sensor Ultrasonik HC-SR04', 15, 0, 'ultrasonic.png', 'Praktik', '2025-03-04 03:15:00', '2025-04-17 08:00:00'),
(26, 'Modul Relay', 8, 2, 'cd4d6026-25a9-45e3-a8e0-296f062e6a0d.png', 'Praktik', '2025-03-03 02:12:59', '2025-04-17 07:57:25'),
(27, 'Sensor Suhu', 974, 13, '5bf20311-b2b9-4d9e-bd0e-4847d7c5712c.png', 'KBM', '2025-03-03 02:40:34', '2025-05-17 11:41:29'),
(28, 'Kapasitor 100uF', 4, 1, '1372bcbd-7b3c-499f-8f34-87f1953819d2.png', 'KBM', '2025-03-03 02:41:02', '2025-04-17 07:56:38'),
(31, 'LED 5mm', 10, 2, NULL, 'Praktik', NULL, '2025-04-17 07:57:48'),
(32, 'LED 7mm', 10, 2, NULL, 'Praktik', NULL, '2025-04-17 07:57:57'),
(33, 'Dinamooose', 8, 3, NULL, 'Praktik', NULL, '2025-04-17 07:58:14'),
(36, 'LED 9mm', 6, 2, NULL, 'Praktik', NULL, '2025-04-17 07:59:13'),
(37, 'Modul Gyro', 5, 3, NULL, 'Praktik', NULL, '2025-04-17 07:59:28'),
(42, 'ESP 32', 11, 3, 'adb1e787-9273-424b-84e6-6edb2526bb57.png', 'KBM 2', '2025-03-17 01:36:26', '2025-04-17 07:55:52'),
(43, 'Arduino Uno', 5, 1, 'd8703b2e-4a58-4e79-ba7b-d4d268f23cf8.svg', 'KBM', '2025-03-19 00:29:31', '2025-05-29 07:43:39'),
(56, 'Mouse Oni R1', 3, 6, 'd32f528f-0473-4d26-baa6-50de56e5830e.svg', 'Untuk KBM', '2025-04-21 19:06:01', '2025-06-12 00:55:30'),
(57, 'Modul Relay', 8, 2, 'cd4d6026-25a9-45e3-a8e0-296f062e6a0d.png', 'Praktik', '2025-03-03 02:12:59', '2025-04-17 07:57:25'),
(58, 'Sensor Suhu', 94, 13, '5bf20311-b2b9-4d9e-bd0e-4847d7c5712c.png', 'KBM', '2025-03-03 02:40:34', '2025-04-17 07:56:57'),
(59, 'Kapasitor 100uF', 4, 1, '1372bcbd-7b3c-499f-8f34-87f1953819d2.png', 'KBM', '2025-03-03 02:41:02', '2025-04-17 07:56:38'),
(60, 'LED 5mm', 10, 2, NULL, 'Praktik', NULL, '2025-04-17 07:57:48'),
(61, 'Resistor 10kΩ', 100, 5, 'resistor-10k.png', 'Praktik', '2025-03-04 03:00:00', '2025-04-17 08:00:00'),
(62, 'Breadboard Mini', 32, 10, 'breadboard-mini.png', 'KBM', '2025-03-04 03:01:00', '2025-05-02 04:18:31'),
(63, 'Transistor BC547', 60, 4, 'transistor-bc547.png', 'Praktik', '2025-03-04 03:02:00', '2025-04-17 08:00:00'),
(64, 'Arduino Uno', 12, 1, 'arduino-uno.png', 'Praktik', '2025-03-04 03:03:00', '2025-04-17 08:00:00'),
(65, 'LCD 16x2', 8, 0, 'lcd-16x2.png', 'Praktik', '2025-03-04 03:04:00', '2025-04-17 08:00:00'),
(66, 'Sensor Cahaya LDR', 33, 2, 'ldr-sensor.png', 'KBM', '2025-03-04 03:05:00', '2025-04-17 08:00:00'),
(67, 'IC 555 Timer', 20, 1, 'ic-555.png', 'Praktik', '2025-03-04 03:06:00', '2025-04-17 08:00:00'),
(68, 'Kabel Jumper Male-Male', 200, 10, 'kabel-jumper.png', 'Praktik', '2025-03-04 03:07:00', '2025-04-17 08:00:00'),
(69, 'Buzzer 5V', 18, 0, 'buzzer-5v.png', 'Praktik', '2025-03-04 03:08:00', '2025-04-17 08:00:00'),
(70, 'Sensor Gas MQ-2', 7, 1, 'mq2-sensor.png', 'KBM', '2025-03-04 03:09:00', '2025-04-17 08:00:00'),
(71, 'Servo Motor SG90', 15, 1, 'servo-sg90.png', 'Praktik', '2025-03-04 03:10:00', '2025-04-17 08:00:00'),
(72, 'Potensiometer 10kΩ', 90, 5, 'potensiometer.png', 'KBM', '2025-03-04 03:11:00', '2025-04-17 08:00:00'),
(73, 'IC 4017 Decade Counter', 30, 2, 'ic-4017.png', 'KBM', '2025-03-04 03:12:00', '2025-04-17 08:00:00'),
(74, 'Display 7-Segment', 22, 1, '7segment.png', 'Praktik', '2025-03-04 03:13:00', '2025-04-17 08:00:00'),
(75, 'ESP32 Dev Board', 10, 1, 'esp32.png', 'KBM', '2025-03-04 03:14:00', '2025-04-17 08:00:00'),
(76, 'Sensor Ultrasonik HC-SR04', 15, 0, 'ultrasonic.png', 'Praktik', '2025-03-04 03:15:00', '2025-04-17 08:00:00'),
(77, 'Modul Relay', 8, 2, 'cd4d6026-25a9-45e3-a8e0-296f062e6a0d.png', 'Praktik', '2025-03-03 02:12:59', '2025-04-17 07:57:25'),
(78, 'Sensor Suhu', 94, 13, '5bf20311-b2b9-4d9e-bd0e-4847d7c5712c.png', 'KBM', '2025-03-03 02:40:34', '2025-04-17 07:56:57'),
(79, 'Kapasitor 100uF', 4, 1, '1372bcbd-7b3c-499f-8f34-87f1953819d2.png', 'KBM', '2025-03-03 02:41:02', '2025-04-17 07:56:38'),
(80, 'LED 5mm', 10, 2, NULL, 'Praktik', NULL, '2025-04-17 07:57:48'),
(81, 'Resistor 10kΩ', 100, 5, 'resistor-10k.png', 'Praktik', '2025-03-04 03:00:00', '2025-04-17 08:00:00'),
(82, 'Breadboard Mini', 40, 2, 'breadboard-mini.png', 'KBM', '2025-03-04 03:01:00', '2025-04-17 08:00:00'),
(83, 'Transistor BC547', 60, 4, 'transistor-bc547.png', 'Praktik', '2025-03-04 03:02:00', '2025-04-17 08:00:00'),
(84, 'Arduino Uno', 12, 1, 'arduino-uno.png', 'Praktik', '2025-03-04 03:03:00', '2025-04-17 08:00:00'),
(85, 'LCD 16x2', 8, 0, 'lcd-16x2.png', 'Praktik', '2025-03-04 03:04:00', '2025-04-17 08:00:00'),
(86, 'Sensor Cahaya LDR', 33, 2, 'ldr-sensor.png', 'KBM', '2025-03-04 03:05:00', '2025-04-17 08:00:00'),
(87, 'IC 555 Timer', 20, 1, 'ic-555.png', 'Praktik', '2025-03-04 03:06:00', '2025-04-17 08:00:00'),
(88, 'Kabel Jumper Male-Male', 200, 10, 'kabel-jumper.png', 'Praktik', '2025-03-04 03:07:00', '2025-04-17 08:00:00'),
(89, 'Buzzer 5V', 18, 0, 'buzzer-5v.png', 'Praktik', '2025-03-04 03:08:00', '2025-04-17 08:00:00'),
(90, 'Sensor Gas MQ-2', 7, 1, 'mq2-sensor.png', 'KBM', '2025-03-04 03:09:00', '2025-04-17 08:00:00'),
(91, 'Servo Motor SG90', 15, 1, 'servo-sg90.png', 'Praktik', '2025-03-04 03:10:00', '2025-04-17 08:00:00'),
(92, 'Potensiometer 10kΩ', 90, 5, 'potensiometer.png', 'KBM', '2025-03-04 03:11:00', '2025-04-17 08:00:00'),
(93, 'IC 4017 Decade Counter', 30, 2, 'ic-4017.png', 'KBM', '2025-03-04 03:12:00', '2025-04-17 08:00:00'),
(94, 'Display 7-Segment', 22, 1, '7segment.png', 'Praktik', '2025-03-04 03:13:00', '2025-04-17 08:00:00'),
(95, 'ESP32 Dev Board', 10, 1, 'esp32.png', 'KBM', '2025-03-04 03:14:00', '2025-04-17 08:00:00'),
(96, 'Sensor Ultrasonik HC-SR04', 15, 0, 'ultrasonic.png', 'Praktik', '2025-03-04 03:15:00', '2025-04-17 08:00:00'),
(97, 'Modul Relay', 8, 2, 'cd4d6026-25a9-45e3-a8e0-296f062e6a0d.png', 'Praktik', '2025-03-03 02:12:59', '2025-04-17 07:57:25'),
(98, 'Sensor Suhu', 94, 13, '5bf20311-b2b9-4d9e-bd0e-4847d7c5712c.png', 'KBM', '2025-03-03 02:40:34', '2025-04-17 07:56:57'),
(99, 'Kapasitor 100uF', 4, 1, '1372bcbd-7b3c-499f-8f34-87f1953819d2.png', 'KBM', '2025-03-03 02:41:02', '2025-04-17 07:56:38'),
(100, 'LED 5mm', 10, 2, NULL, 'Praktik', NULL, '2025-04-17 07:57:48'),
(101, 'Resistor 10kΩ', 100, 5, 'resistor-10k.png', 'Praktik', '2025-03-04 03:00:00', '2025-04-17 08:00:00'),
(102, 'Breadboard Mini', 40, 2, 'breadboard-mini.png', 'KBM', '2025-03-04 03:01:00', '2025-04-17 08:00:00'),
(103, 'Transistor BC547', 60, 4, 'transistor-bc547.png', 'Praktik', '2025-03-04 03:02:00', '2025-04-17 08:00:00'),
(104, 'Arduino Uno', 12, 1, 'arduino-uno.png', 'Praktik', '2025-03-04 03:03:00', '2025-04-17 08:00:00'),
(105, 'LCD 16x2', 8, 0, 'lcd-16x2.png', 'Praktik', '2025-03-04 03:04:00', '2025-04-17 08:00:00'),
(106, 'Sensor Cahaya LDR', 33, 2, 'ldr-sensor.png', 'KBM', '2025-03-04 03:05:00', '2025-04-17 08:00:00'),
(107, 'IC 555 Timer', 20, 1, 'ic-555.png', 'Praktik', '2025-03-04 03:06:00', '2025-04-17 08:00:00'),
(108, 'Kabel Jumper Male-Male', 200, 10, 'kabel-jumper.png', 'Praktik', '2025-03-04 03:07:00', '2025-04-17 08:00:00'),
(109, 'Buzzer 5V', 18, 0, 'buzzer-5v.png', 'Praktik', '2025-03-04 03:08:00', '2025-04-17 08:00:00'),
(110, 'Sensor Gas MQ-2', 7, 1, 'mq2-sensor.png', 'KBM', '2025-03-04 03:09:00', '2025-04-17 08:00:00'),
(111, 'Servo Motor SG90', 15, 1, 'servo-sg90.png', 'Praktik', '2025-03-04 03:10:00', '2025-04-17 08:00:00'),
(112, 'Potensiometer 10kΩ', 90, 5, 'potensiometer.png', 'KBM', '2025-03-04 03:11:00', '2025-04-17 08:00:00'),
(113, 'IC 4017 Decade Counter', 30, 2, 'ic-4017.png', 'KBM', '2025-03-04 03:12:00', '2025-04-17 08:00:00'),
(114, 'Display 7-Segment', 22, 1, '7segment.png', 'Praktik', '2025-03-04 03:13:00', '2025-04-17 08:00:00'),
(115, 'ESP32 Dev Board', 10, 1, 'esp32.png', 'KBM', '2025-03-04 03:14:00', '2025-04-17 08:00:00'),
(116, 'Sensor Ultrasonik HC-SR04 (1)', 0, 0, 'ultrasonic.png', 'Praktik', '2025-03-04 03:15:00', '2025-06-07 06:25:15'),
(118, 'Arduino (2)', 5, 5, '259a01ef-18c4-4407-9afd-980ef3471ab6.svg', 'KBM', '2025-06-03 00:49:11', '2025-07-15 05:47:25'),
(121, 'RJ45 (2)', 8, 2, NULL, 'Untuk Pembelajaran Jaringan', '2025-07-24 02:41:43', '2025-07-24 02:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `detail_peminjaman_inventaris`
--

CREATE TABLE `detail_peminjaman_inventaris` (
  `id_detail_peminjaman` bigint UNSIGNED NOT NULL,
  `id_peminjaman_inventaris` bigint UNSIGNED NOT NULL,
  `id_inventaris` bigint UNSIGNED NOT NULL,
  `catatan_pengembalian` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `kondisi_akhir_pengembalian` enum('baik','rusak','hilang') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `tanggal_kembali` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_peminjaman_inventaris`
--

INSERT INTO `detail_peminjaman_inventaris` (`id_detail_peminjaman`, `id_peminjaman_inventaris`, `id_inventaris`, `catatan_pengembalian`, `kondisi_akhir_pengembalian`, `status`, `tanggal_kembali`, `created_at`, `updated_at`) VALUES
(5, 62, 58, 'Guwd', 'baik', 1, '2025-05-16', '2025-05-15 22:46:27', '2025-05-15 23:15:09'),
(6, 62, 59, 'Guwdd', 'baik', 1, '2025-05-16', '2025-05-15 22:46:27', '2025-05-15 23:30:34'),
(7, 62, 60, 'Guwdd', 'baik', 1, '2025-05-16', '2025-05-15 22:46:27', '2025-05-15 23:30:34'),
(8, 62, 61, 'Guwd', 'baik', 1, '2025-05-16', '2025-05-15 22:46:27', '2025-05-15 23:15:09'),
(9, 63, 58, 'ucak', 'baik', 1, '2025-05-16', '2025-05-15 23:31:13', '2025-05-15 23:35:35'),
(10, 63, 59, 'ucak', 'hilang', 1, '2025-05-16', '2025-05-15 23:31:13', '2025-05-15 23:35:14'),
(11, 63, 60, 'ucak', 'rusak', 1, '2025-05-16', '2025-05-15 23:31:13', '2025-05-15 23:32:25'),
(12, 63, 61, 'ucak', 'rusak', 1, '2025-05-16', '2025-05-15 23:31:13', '2025-05-15 23:33:52'),
(18, 66, 64, 'mantap', 'baik', 1, '2025-05-17', '2025-05-17 02:26:23', '2025-05-17 03:48:10'),
(19, 66, 65, 'mantap', 'baik', 1, '2025-05-17', '2025-05-17 02:26:23', '2025-05-17 03:48:10'),
(23, 66, 56, 'mantap', 'baik', 1, '2025-05-17', '2025-05-17 03:46:11', '2025-05-17 03:48:10'),
(32, 69, 64, 'ntap', 'baik', 1, '2025-05-17', '2025-05-17 05:22:39', '2025-05-17 05:25:22'),
(33, 69, 65, 'ntap', 'baik', 1, '2025-05-17', '2025-05-17 05:22:39', '2025-05-17 05:25:22'),
(34, 69, 56, 'ucak', 'hilang', 1, '2025-05-17', '2025-05-17 05:23:38', '2025-05-17 05:24:59'),
(35, 69, 58, 'ucak', 'rusak', 1, '2025-05-17', '2025-05-17 05:23:38', '2025-05-17 05:24:43'),
(37, 70, 64, 'aman', 'baik', 1, '2025-05-29', '2025-05-17 05:48:37', '2025-05-29 03:22:36'),
(38, 70, 65, 'gg', 'rusak', 1, '2025-05-29', '2025-05-17 05:48:37', '2025-05-29 03:24:06'),
(39, 70, 56, NULL, NULL, 0, NULL, '2025-05-17 05:49:02', '2025-05-17 05:49:02'),
(40, 70, 58, NULL, NULL, 0, NULL, '2025-05-17 05:49:02', '2025-05-17 05:49:02'),
(41, 71, 53, NULL, NULL, 0, NULL, '2025-05-29 03:28:07', '2025-05-29 03:28:07'),
(42, 71, 58, NULL, NULL, 0, NULL, '2025-05-29 03:28:07', '2025-05-29 03:28:07'),
(43, 72, 59, NULL, NULL, 0, NULL, '2025-05-29 03:34:56', '2025-05-29 03:34:56'),
(44, 73, 60, 'Uji testing pengembalian', 'baik', 1, '2025-05-29', '2025-05-29 07:54:30', '2025-05-29 07:56:35'),
(45, 73, 61, 'Uji testing pengembalian', 'baik', 1, '2025-05-29', '2025-05-29 07:54:30', '2025-05-29 07:56:35'),
(46, 74, 60, NULL, NULL, 0, NULL, '2025-06-03 00:52:42', '2025-06-03 00:52:42'),
(47, 74, 62, NULL, NULL, 0, NULL, '2025-06-03 00:52:42', '2025-06-03 00:52:42'),
(48, 75, 64, NULL, NULL, 0, NULL, '2025-06-12 02:49:38', '2025-06-12 02:49:38'),
(49, 76, 95, NULL, 'baik', 1, '2025-06-29', '2025-06-29 08:14:52', '2025-06-29 09:39:12'),
(50, 76, 91, 'test', 'hilang', 1, '2025-06-29', '2025-06-29 08:14:52', '2025-06-29 09:37:55'),
(51, 76, 94, 'test', 'baik', 1, '2025-06-29', '2025-06-29 08:14:52', '2025-06-29 09:37:50'),
(52, 76, 92, 'test', 'rusak', 1, '2025-06-29', '2025-06-29 08:14:52', '2025-06-29 09:37:52'),
(53, 76, 93, NULL, 'baik', 1, '2025-06-29', '2025-06-29 08:14:52', '2025-06-29 09:39:28'),
(56, 76, 90, NULL, 'baik', 1, '2025-06-29', '2025-06-29 09:20:41', '2025-06-29 09:39:28'),
(57, 76, 88, 'test', 'baik', 1, '2025-06-29', '2025-06-29 09:20:41', '2025-06-29 09:37:50'),
(58, 77, 95, NULL, 'baik', 1, '2025-06-29', '2025-06-29 09:44:25', '2025-06-29 09:49:34'),
(59, 77, 93, NULL, 'baik', 1, '2025-06-29', '2025-06-29 09:44:25', '2025-06-29 09:49:34'),
(60, 77, 94, NULL, 'baik', 1, '2025-06-29', '2025-06-29 09:44:25', '2025-06-29 09:49:34'),
(62, 78, 99, NULL, 'baik', 1, '2025-07-02', '2025-07-02 00:31:13', '2025-07-02 00:31:39'),
(63, 78, 97, NULL, 'baik', 1, '2025-07-02', '2025-07-02 00:31:13', '2025-07-02 00:31:39'),
(64, 78, 101, NULL, 'rusak', 1, '2025-07-02', '2025-07-02 00:31:13', '2025-07-02 00:31:40'),
(65, 78, 98, NULL, 'baik', 1, '2025-07-02', '2025-07-02 00:31:13', '2025-07-02 00:32:12'),
(66, 78, 102, NULL, 'baik', 1, '2025-07-02', '2025-07-02 00:31:13', '2025-07-02 00:32:12'),
(67, 79, 99, 'Testing pengembalian', 'baik', 1, '2025-07-02', '2025-07-02 00:33:12', '2025-07-02 00:42:22'),
(68, 79, 97, 'Testing pengembalian', 'baik', 1, '2025-07-02', '2025-07-02 00:33:12', '2025-07-02 00:42:22'),
(69, 79, 102, 'Testing pengembalian', 'baik', 1, '2025-07-02', '2025-07-02 00:33:12', '2025-07-02 00:42:22'),
(70, 80, 99, NULL, NULL, 0, NULL, '2025-07-08 20:28:12', '2025-07-08 20:28:12'),
(71, 80, 97, NULL, NULL, 0, NULL, '2025-07-08 20:28:12', '2025-07-08 20:28:12'),
(72, 80, 102, NULL, NULL, 0, NULL, '2025-07-08 20:28:12', '2025-07-08 20:28:12'),
(73, 81, 108, NULL, 'baik', 1, '2025-07-24', '2025-07-24 02:37:07', '2025-07-24 02:39:19'),
(74, 81, 109, NULL, 'baik', 1, '2025-07-24', '2025-07-24 02:37:07', '2025-07-24 02:39:19'),
(75, 81, 110, NULL, 'baik', 1, '2025-07-24', '2025-07-24 02:37:07', '2025-07-24 02:39:19'),
(76, 81, 107, NULL, 'baik', 1, '2025-07-24', '2025-07-24 02:37:07', '2025-07-24 02:39:19'),
(77, 81, 106, NULL, 'hilang', 1, '2025-07-24', '2025-07-24 02:37:07', '2025-07-24 02:39:21'),
(78, 81, 111, NULL, 'rusak', 1, '2025-07-24', '2025-07-24 02:37:07', '2025-07-24 02:39:22');

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `name`, `email`, `password`, `nik`, `no_telp`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Dosen 1', 'babskuyy@staff.uns.ac.id', '$2y$12$B6lGv9V13WdnnWORFqs31ezraIjO.lXO4PR20G4KsxYTubYIBiBfK', '123123123123123', '3134213214', 1, '2025-02-26 00:11:32', '2025-07-17 04:48:07');

-- --------------------------------------------------------

--
-- Table structure for table `foto`
--

CREATE TABLE `foto` (
  `foto_id` bigint UNSIGNED NOT NULL,
  `id_jenis_inventaris` bigint UNSIGNED DEFAULT NULL,
  `id_ruang` bigint UNSIGNED DEFAULT NULL,
  `id_bhp` bigint UNSIGNED DEFAULT NULL,
  `foto_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `foto`
--

INSERT INTO `foto` (`foto_id`, `id_jenis_inventaris`, `id_ruang`, `id_bhp`, `foto_path`, `created_at`, `updated_at`) VALUES
(61, NULL, NULL, 26, '6ee7af88-3567-4ea7-ad5d-c12ae08356ad.png', '2025-03-03', '2025-03-03'),
(62, NULL, NULL, 26, '9b670509-c438-492d-8676-a32397f6e4b4.png', '2025-03-03', '2025-03-03'),
(63, NULL, NULL, 28, 'a72d0f4d-3437-4f87-9c85-c5cf2444717b.png', '2025-03-03', '2025-03-03'),
(64, NULL, NULL, 28, 'c26148ef-8fb0-4ba8-ac77-e5c45de9503f.png', '2025-03-03', '2025-03-03'),
(65, NULL, NULL, 28, '4f5b5320-bb7f-4231-b9fe-6e5e7f6fe4d9.png', '2025-03-03', '2025-03-03'),
(77, 18, NULL, NULL, '32adcf6b-bd81-419f-bf8a-3b44b46f1d90.jpg', '2025-03-04', '2025-03-04'),
(78, 18, NULL, NULL, '492b784c-8bfd-41f0-bc59-ee8be0cefbe9.png', '2025-03-04', '2025-03-04'),
(79, NULL, 22, NULL, '0d1bd5a7-44b3-4d29-bb06-49da729d2dd7.png', '2025-03-04', '2025-03-04'),
(80, NULL, 22, NULL, 'f38d4cc0-219d-4d44-9cae-412034585d1e.jpg', '2025-03-04', '2025-03-04'),
(82, NULL, NULL, 42, 'a015fdc2-9638-486a-9f52-284146da110b.png', '2025-03-17', '2025-03-17'),
(83, NULL, NULL, 42, '87c25a24-afb4-4425-b37e-6586bc491ace.png', '2025-03-17', '2025-03-17'),
(84, NULL, NULL, 43, '2dd78d41-a990-4774-9fff-6ea8b9116da1.png', '2025-03-19', '2025-03-19'),
(88, 20, NULL, NULL, '78bd4128-3263-488e-8bd2-6201be7210a6.png', '2025-03-20', '2025-03-20'),
(91, NULL, 23, NULL, '73a6d1a8-3984-44dd-8d3d-d511e06ff8a6.png', '2025-03-24', '2025-03-24'),
(92, NULL, 23, NULL, '645c1049-0456-4c12-b708-ad6e4bf922f5.png', '2025-03-24', '2025-03-24'),
(95, NULL, 24, NULL, 'c04a0c32-d9ee-4283-a87f-3a5a9b37f562.png', '2025-03-24', '2025-03-24'),
(96, NULL, 25, NULL, 'a9636cb7-b299-4ee3-8b20-99c64dcc8365.png', '2025-04-01', '2025-04-01'),
(103, 23, NULL, NULL, '6ec65365-7b4e-4574-8ea6-e2392d1e34a1.png', '2025-04-06', '2025-04-06'),
(106, 26, NULL, NULL, '3f758a70-135a-4056-9d8f-a34e4fbe0a24.png', '2025-04-22', '2025-04-22'),
(107, NULL, NULL, 56, '5d70bc05-0a3c-429b-bc59-829269005080.png', '2025-04-22', '2025-04-22'),
(110, 27, NULL, NULL, '12b7cd35-f9f8-41ad-af49-75504097bed8.png', '2025-06-03', '2025-06-03'),
(111, NULL, NULL, 118, '9a2e875b-819a-4671-99e1-a6046c673c3a.png', '2025-06-03', '2025-06-03'),
(116, 29, NULL, NULL, 'a42a1fc9-0d56-4335-b6ed-66cf6a129dc1.jpeg', '2025-06-29', '2025-06-29'),
(117, 30, NULL, NULL, '70bb54e8-9026-47c1-b4df-758fff16df08.jpg', '2025-07-24', '2025-07-24'),
(118, NULL, NULL, 121, 'd680610d-5ba1-44b1-823c-6a14484bcd88.jpg', '2025-07-24', '2025-07-24');

-- --------------------------------------------------------

--
-- Table structure for table `gedung`
--

CREATE TABLE `gedung` (
  `id_gedung` bigint UNSIGNED NOT NULL,
  `nama_gedung` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lokasi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_lantai` bigint UNSIGNED NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gedung`
--

INSERT INTO `gedung` (`id_gedung`, `nama_gedung`, `lokasi`, `id_lantai`, `created_at`, `updated_at`) VALUES
(5, 'Student Center', '-7.559209,110.8190335,13z', 23, '2025-03-04', '2025-04-17'),
(7, 'Serbaguna', '-7.559209,110.8190335,13z', 24, '2025-03-22', '2025-04-17'),
(8, 'Auditorium', '-7.559209,110.8190335,13z', 24, '2025-03-22', '2025-04-17'),
(10, 'Rektorat', '-7.559209,110.8190335,13z', 25, '2025-04-01', '2025-04-17'),
(11, 'FKIP', '-7.559209,110.8190335,13z', 24, '2025-04-01', '2025-04-17');

-- --------------------------------------------------------

--
-- Table structure for table `inventaris`
--

CREATE TABLE `inventaris` (
  `id_inventaris` bigint UNSIGNED NOT NULL,
  `id_jenis_inventaris` bigint UNSIGNED NOT NULL,
  `no_inventaris` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_ruang` bigint UNSIGNED NOT NULL,
  `kondisi` enum('baik','rusak','hilang') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `tahun_pengadaan` int NOT NULL,
  `qr_code_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventaris`
--

INSERT INTO `inventaris` (`id_inventaris`, `id_jenis_inventaris`, `no_inventaris`, `id_ruang`, `kondisi`, `status`, `tahun_pengadaan`, `qr_code_path`, `created_at`, `updated_at`) VALUES
(53, 18, 'V101-40131', 22, 'baik', 0, 2002, 'cab559ec-3866-4a6b-ae5e-7ff5268cb766.png', '2025-03-03 21:27:34', '2025-05-29 03:28:07'),
(56, 18, 'V101-421', 22, 'baik', 0, 2002, '52508404-270f-4866-91eb-97ee16f3bc94.png', '2025-03-03 21:42:38', '2025-05-17 05:49:02'),
(58, 18, 'V101-471', 22, 'baik', 0, 2002, '161aefc3-8388-45be-a162-8314ba45fe2e.png', '2025-03-06 23:28:33', '2025-05-29 03:28:07'),
(59, 18, 'V101-406', 22, 'baik', 0, 2024, '270cd866-6355-404d-9e8b-95e34ff586ce.png', '2025-03-06 23:28:38', '2025-05-31 01:05:01'),
(60, 18, 'V101-403', 22, 'baik', 0, 2002, '4908f7f4-533e-4069-aaee-41deec16e6bb.png', '2025-03-06 23:28:41', '2025-06-03 00:52:42'),
(61, 18, 'V101-404', 22, 'hilang', 1, 2004, '97dc48f1-8cf2-4310-bbdb-e7a9ac7e81a7.png', '2025-03-06 23:28:50', '2025-05-31 00:44:42'),
(62, 22, 'V101-401', 22, 'baik', 0, 2026, 'cf893bf2-4d3c-432d-9fc4-abcf5a192cd5.png', '2025-03-20 02:40:36', '2025-06-03 00:52:42'),
(64, 21, 'V101-402', 25, 'baik', 0, 2022, 'c54f47a6-1915-4ac3-8d26-b2b4cc9eaa7a.svg', '2025-04-05 21:42:51', '2025-06-12 02:49:38'),
(65, 26, 'V01-0139', 22, 'rusak', 1, 2024, '056d8c53-62d4-4e46-8aac-33db8291a85a.svg', '2025-04-21 19:03:38', '2025-05-29 03:24:06'),
(67, 27, 'V0001-3004', 22, 'baik', 1, 2024, '85ef68a4-7f73-4212-b84c-e4e71e5db0cb.svg', '2025-06-03 00:37:54', '2025-06-03 00:37:54'),
(68, 23, '3.05.02.01.001', 25, 'baik', 1, 2024, '596564be-2b4f-4098-979a-54a35df27ff6.svg', '2025-06-07 03:27:39', '2025-06-29 08:28:42'),
(69, 23, '3.05.02.01.001.001', 25, 'baik', 1, 2024, 'b00d2fbf-5771-4bbf-879e-8ba7fec116ca.svg', '2025-06-07 03:28:03', '2025-06-07 03:28:03'),
(70, 23, '3.05.02.01.001.002', 25, 'baik', 1, 2024, '2a5ed1f1-8c00-46ae-88f0-9ba43745854d.svg', '2025-06-07 03:28:03', '2025-06-07 03:28:03'),
(71, 23, '3.05.02.01.001.003', 25, 'baik', 1, 2024, '763fddfe-d816-4788-b810-877c1a1e56c1.svg', '2025-06-07 03:28:03', '2025-06-07 03:28:03'),
(72, 23, '3.05.02.01.001.004', 25, 'baik', 1, 2024, '7a6b1791-9822-4aa0-8bd4-c7b91a062a1d.svg', '2025-06-07 03:28:03', '2025-06-07 03:28:03'),
(73, 23, '3.05.02.01.001.005', 25, 'baik', 1, 2024, '9b57a5b6-7aad-4d7e-92d6-48bd2ba8ecd2.svg', '2025-06-07 03:28:03', '2025-06-07 03:28:03'),
(75, 23, '3.05.02.01.001.006', 25, 'baik', 1, 2024, '41f52c69-dc1a-4c94-b087-127353a0342b.svg', '2025-06-07 03:33:48', '2025-06-07 03:33:48'),
(76, 23, '3.05.02.01.001.007', 25, 'baik', 1, 2024, '91885ba4-f470-4313-b557-61a8834730b6.svg', '2025-06-07 03:33:48', '2025-06-07 03:33:48'),
(77, 23, '3.05.02.01.001.008', 25, 'baik', 1, 2024, '8fda792c-68fd-4c8f-b476-6aa216b953d2.svg', '2025-06-07 03:33:48', '2025-06-07 03:33:48'),
(78, 23, '3.05.02.01.001.009', 25, 'baik', 1, 2024, '81fc07dd-ace6-454f-812d-ca4fc85e3cbc.svg', '2025-06-07 03:33:48', '2025-06-07 03:33:48'),
(79, 23, '3.05.02.01.001.010', 25, 'baik', 1, 2024, 'ee1bcc15-24fe-4a00-9841-dbfa46a8992a.svg', '2025-06-07 03:33:48', '2025-06-07 03:33:48'),
(81, 23, '3.05.02.01.001.011', 25, 'baik', 1, 2024, '2fa855cf-bf77-4d24-87cc-016525a96b3b.svg', '2025-06-07 03:37:26', '2025-06-07 03:37:26'),
(85, 23, '3.05.02.01.001.004.008', 25, 'baik', 1, 2024, '04318b2c-0774-42a8-a199-453aa1e6bd67.svg', '2025-06-07 03:38:12', '2025-06-07 03:38:12'),
(86, 23, '3.05.02.01.001.012', 25, 'baik', 1, 2024, '9fc7dae0-592b-4fa7-8ec6-08af7f78c09f.svg', '2025-06-07 03:38:35', '2025-06-07 03:38:35'),
(87, 23, '3.05.02.01.001.013', 25, 'baik', 1, 2024, 'e7d46bd4-cce1-4eab-bf45-5474a3059acd.svg', '2025-06-07 03:38:35', '2025-06-07 03:38:35'),
(88, 23, '3.05.02.01.001.014', 25, 'baik', 1, 2024, '140d1190-0b3a-480f-8f28-27416a3a447a.svg', '2025-06-07 03:38:35', '2025-06-29 09:37:50'),
(89, 23, '3.05.02.01.001.015', 25, 'baik', 1, 2024, 'f4494cd3-c40e-410f-b758-2436db6517ec.svg', '2025-06-07 03:38:35', '2025-06-07 03:38:35'),
(90, 23, '3.05.02.01.001.016', 25, 'baik', 1, 2024, 'e6a3a79a-37bd-4efd-aecf-017bb82c09bd.svg', '2025-06-07 03:38:35', '2025-06-29 09:44:45'),
(91, 29, '3.05.02.01.005.0001', 25, 'hilang', 1, 2024, NULL, '2025-06-29 07:25:59', '2025-06-29 09:37:55'),
(92, 29, '3.05.02.01.005.0002', 25, 'rusak', 1, 2024, NULL, '2025-06-29 07:25:59', '2025-06-29 09:37:52'),
(93, 29, '3.05.02.01.005.0003', 25, 'baik', 1, 2024, NULL, '2025-06-29 07:25:59', '2025-06-29 09:45:32'),
(94, 29, '3.05.02.01.005.0004', 25, 'baik', 1, 2024, NULL, '2025-06-29 07:25:59', '2025-06-29 09:45:19'),
(95, 29, '3.05.02.01.005.0005', 25, 'baik', 1, 2024, NULL, '2025-06-29 07:25:59', '2025-06-29 09:45:32'),
(96, 29, '3.02.05.01.001.0001', 25, 'baik', 1, 2024, NULL, '2025-07-02 00:26:59', '2025-07-02 00:26:59'),
(97, 29, '3.02.05.01.001.0002', 25, 'baik', 0, 2024, NULL, '2025-07-02 00:26:59', '2025-07-08 20:28:12'),
(98, 29, '3.02.05.01.001.0003', 25, 'baik', 1, 2024, NULL, '2025-07-02 00:26:59', '2025-07-02 00:32:12'),
(99, 29, '3.02.05.01.001.0004', 25, 'baik', 0, 2024, NULL, '2025-07-02 00:26:59', '2025-07-08 20:28:12'),
(100, 29, '3.02.05.01.001.0005', 25, 'baik', 1, 2024, NULL, '2025-07-02 00:26:59', '2025-07-02 00:26:59'),
(101, 29, '3.02.05.01.001.0006', 25, 'rusak', 1, 2024, NULL, '2025-07-02 00:26:59', '2025-07-02 00:31:40'),
(102, 29, '3.02.05.01.001.0007', 25, 'baik', 0, 2024, NULL, '2025-07-02 00:26:59', '2025-07-08 20:28:12'),
(103, 29, '3.02.05.01.001.0008', 25, 'baik', 1, 2024, NULL, '2025-07-02 00:26:59', '2025-07-02 00:26:59'),
(104, 29, '3.02.05.01.001.0009', 25, 'baik', 1, 2024, NULL, '2025-07-02 00:26:59', '2025-07-02 00:26:59'),
(105, 29, '3.02.05.01.001.0010', 25, 'baik', 1, 2024, NULL, '2025-07-02 00:26:59', '2025-07-02 00:26:59'),
(106, 30, '3.05.02.06.001.0001', 25, 'hilang', 1, 2024, NULL, '2025-07-24 02:34:34', '2025-07-24 02:39:21'),
(107, 30, '3.05.02.06.001.0002', 25, 'baik', 1, 2024, NULL, '2025-07-24 02:34:34', '2025-07-24 02:39:19'),
(108, 30, '3.05.02.06.001.0003', 25, 'baik', 1, 2024, NULL, '2025-07-24 02:34:34', '2025-07-24 02:39:19'),
(109, 30, '3.05.02.06.001.0004', 25, 'baik', 1, 2024, NULL, '2025-07-24 02:34:34', '2025-07-24 02:39:19'),
(110, 30, '3.05.02.06.001.0005', 25, 'baik', 1, 2024, NULL, '2025-07-24 02:34:34', '2025-07-24 02:39:19'),
(111, 30, '3.05.02.06.001.0006', 25, 'rusak', 1, 2024, NULL, '2025-07-24 02:35:53', '2025-07-24 02:39:22');

-- --------------------------------------------------------

--
-- Table structure for table `jabatan`
--

CREATE TABLE `jabatan` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jabatan`
--

INSERT INTO `jabatan` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Dosen', 'string', '2025-02-26 00:11:27', '2025-02-26 00:11:27'),
(2, 'Kalab', 'string', '2025-02-27 09:38:07', '2025-02-27 09:38:07'),
(3, 'Kaprodi', 'string', '2025-02-27 09:38:12', '2025-02-27 09:38:12'),
(4, 'Petugas', 'string', '2025-02-27 09:38:15', '2025-02-27 09:38:15');

-- --------------------------------------------------------

--
-- Table structure for table `jabatan_dosen`
--

CREATE TABLE `jabatan_dosen` (
  `id` bigint UNSIGNED NOT NULL,
  `dosen_id` bigint UNSIGNED NOT NULL,
  `jabatan_id` bigint UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jabatan_dosen`
--

INSERT INTO `jabatan_dosen` (`id`, `dosen_id`, `jabatan_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '2025-02-26 00:11:32', '2025-06-24 03:42:48'),
(2, 2, 2, 0, '2025-02-27 09:41:04', '2025-06-24 03:42:48'),
(4, 2, 4, 0, NULL, '2025-06-24 03:42:48'),
(6, 2, 3, 0, '2025-07-15 06:28:46', '2025-07-15 06:28:46');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_inventaris`
--

CREATE TABLE `jenis_inventaris` (
  `id_jenis_inventaris` bigint UNSIGNED NOT NULL,
  `nama_inventaris` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_merk` bigint UNSIGNED NOT NULL,
  `id_tipe` bigint UNSIGNED NOT NULL,
  `peruntukan` enum('alat_lab','inventaris_kantor') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status_import` enum('produk_luar_negeri','produk_dalam_negeri') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenis_inventaris`
--

INSERT INTO `jenis_inventaris` (`id_jenis_inventaris`, `nama_inventaris`, `id_merk`, `id_tipe`, `peruntukan`, `status_import`, `created_at`, `updated_at`) VALUES
(16, 'Legion', 14, 8, 'alat_lab', 'produk_luar_negeri', '2025-03-04', '2025-04-17'),
(18, 'Air Pro 2', 15, 8, 'alat_lab', 'produk_dalam_negeri', '2025-03-04', '2025-04-17'),
(20, 'RGB 75', 9, 1, 'inventaris_kantor', 'produk_dalam_negeri', '2025-03-20', '2025-04-17'),
(21, 'Oni R1', 16, 3, 'alat_lab', 'produk_luar_negeri', '2025-03-20', '2025-04-17'),
(22, '27\" Qo2141', 8, 7, 'alat_lab', 'produk_dalam_negeri', '2025-03-20', '2025-04-17'),
(23, 'Monolith', 11, 3, 'alat_lab', 'produk_luar_negeri', '2025-04-06', '2025-04-17'),
(26, 'Oni R1', 17, 12, 'alat_lab', 'produk_luar_negeri', '2025-04-22', '2025-06-03'),
(27, 'Oni R1 (2)', 23, 14, 'alat_lab', 'produk_luar_negeri', '2025-06-03', '2025-06-03'),
(29, 'Legion R1', 23, 14, 'alat_lab', 'produk_luar_negeri', '2025-06-29', '2025-07-01'),
(30, 'A36', 25, 16, 'alat_lab', 'produk_luar_negeri', '2025-07-24', '2025-07-24');

-- --------------------------------------------------------

--
-- Table structure for table `lantai_gedung`
--

CREATE TABLE `lantai_gedung` (
  `id_lantai` bigint UNSIGNED NOT NULL,
  `nama_lantai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lantai_gedung`
--

INSERT INTO `lantai_gedung` (`id_lantai`, `nama_lantai`, `created_at`, `updated_at`) VALUES
(23, 'Lantai Dasar', '2025-03-04', '2025-03-22'),
(24, 'Lantai 1', '2025-03-22', '2025-03-22'),
(25, 'Lantai 2', '2025-03-22', '2025-03-22');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` bigint UNSIGNED NOT NULL,
  `nim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `nim`, `name`, `email`, `no_telp`, `password`, `status`, `created_at`, `updated_at`) VALUES
(2, 'V3422026', 'Mahasiwaa 1', 'babskuyy@student.uns.ac.id', '1231231231', '$2y$12$8bXjF3gGRySM0RKlImjah.QVRyAROLsX4xvGbUFw2njen.7wWVJDe', 1, '2025-02-25 00:08:53', '2025-07-13 10:11:20'),
(3, 'V3422022', 'Mahasiswa 13', 'testing2@student.ac', '1231111111', '$2y$12$VNrawnaFBrU62P/aVgid7.NceScC4ce9IAN9w5jZlK2RcfR.QYVMu', 0, '2025-02-25 23:49:47', '2025-07-17 06:52:08'),
(4, 'V3422226', 'Mahasiswa 112', 'testing@student.ac', '414124213', '$2y$12$X/tlx14wSsDTJYNZ/jej7ecUWQwNsIxcHEambKVhlNsAMgWs6r0Va', 1, '2025-02-27 09:19:23', '2025-07-17 06:52:15'),
(5, 'V3422021', 'Mahasiswa 2', 'babskuyy@gmail.com', '1231231231', '$2y$12$EmkBQ6pfsoIlRDRv3d2Cb..qqvK8ImNuHIMhgpxp6eEElLjXh0Js.', 1, '2025-03-03 21:33:42', '2025-03-03 21:33:42');

-- --------------------------------------------------------

--
-- Table structure for table `merk`
--

CREATE TABLE `merk` (
  `id_merk` bigint UNSIGNED NOT NULL,
  `nama_merk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `merk`
--

INSERT INTO `merk` (`id_merk`, `nama_merk`, `created_at`, `updated_at`) VALUES
(8, 'Samsung', '2025-03-04', '2025-04-17'),
(9, 'Logitech', '2025-03-19', '2025-04-17'),
(11, 'Asus', '2025-03-19', '2025-04-17'),
(14, 'Lenovo', '2025-04-17', '2025-04-17'),
(15, 'Apple', '2025-04-17', '2025-04-17'),
(16, 'Vortex', '2025-04-17', '2025-04-17'),
(17, 'BenQ', '2025-04-17', '2025-04-17'),
(18, 'Dell', '2025-04-17', '2025-04-17'),
(19, 'Fantech', '2025-04-17', '2025-04-17'),
(20, 'Shimano', '2025-04-17', '2025-04-17'),
(21, 'Infinix', '2025-04-22', '2025-04-22'),
(23, 'Logitech', '2025-06-03', '2025-06-03'),
(25, 'Samsung 2', '2025-07-24', '2025-07-24');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(15, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(16, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(17, '2025_02_21_092348_mahasiswa', 1),
(18, '2025_02_21_092354_dosen', 1),
(19, '2025_02_21_092356_admin', 1),
(20, '2025_02_21_092358_jabatan', 1),
(21, '2025_02_21_092402_jabatan_dosen', 1),
(23, '2025_03_03_062017_bhp', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id_notification` bigint UNSIGNED NOT NULL,
  `id_peminjam` bigint UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint NOT NULL,
  `for_role` enum('mahasiswa','admin','dosen') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman_bhps`
--

CREATE TABLE `peminjaman_bhps` (
  `id_peminjaman_bhp` bigint UNSIGNED NOT NULL,
  `id_bhp` bigint UNSIGNED NOT NULL,
  `id_peminjam` bigint UNSIGNED NOT NULL,
  `tipe_peminjam` enum('mahasiswa','dosen') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_peminjam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ni_peminjam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kegunaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_pengembalian` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `jumlah_penggunaan` int NOT NULL,
  `status_pengembalian` enum('selesai','dipinjam','dikembalikan_sebagian') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dipinjam',
  `jumlah_kembali_bagus` int DEFAULT '0',
  `jumlah_kembali_rusak` int NOT NULL DEFAULT '0',
  `keterangan_pengembalian` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `qr_code_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peminjaman_bhps`
--

INSERT INTO `peminjaman_bhps` (`id_peminjaman_bhp`, `id_bhp`, `id_peminjam`, `tipe_peminjam`, `nama_peminjam`, `ni_peminjam`, `no_telp`, `kegunaan`, `tanggal_pinjam`, `tanggal_pengembalian`, `tanggal_kembali`, `jumlah_penggunaan`, `status_pengembalian`, `jumlah_kembali_bagus`, `jumlah_kembali_rusak`, `keterangan_pengembalian`, `qr_code_path`, `created_by`, `created_at`, `updated_at`) VALUES
(37, 3, 2, 'mahasiswa', 'Mahasiwa 1', 'V3422026', '1231231231', 'string', '2024-10-10', NULL, '2024-10-12', 4, 'selesai', 4, 0, 'mantap\n2025-06-12: 11111', '', 'admin1', '2025-06-11 23:50:53', '2025-06-11 23:56:23'),
(38, 56, 2, 'dosen', 'Dosen 1', '123123123123123', '3134213214', 'gg', '2025-06-12', NULL, '2025-06-12', 7, 'dikembalikan_sebagian', 7, 0, 'aman\n,   c2\n,   gg', '', 'admin1', '2025-06-11 23:59:05', '2025-06-12 00:22:02'),
(40, 56, 2, 'mahasiswa', 'Mahasiwa 1', 'V3422026', '1231231231', 'gg', '2024-10-10', '2025-07-08', NULL, 4, 'dipinjam', 0, 0, NULL, '9a82381e-e068-40d5-a5a1-904fe3b55bbc.svg', 'admin1', '2025-06-12 00:55:30', '2025-06-12 00:55:30'),
(41, 118, 5, 'mahasiswa', 'Mahasiswa 2', 'V3422021', '1231231231', 'gg', '2025-06-29', NULL, '2025-06-29', 10, 'selesai', 10, 0, 'gg', '79bd8696-6a31-4305-ac66-cd8e100c75d7.svg', 'admin1', '2025-06-29 07:26:30', '2025-06-29 08:09:28'),
(42, 118, 5, 'mahasiswa', 'Mahasiswa 2', 'V3422021', '1231231231', 'ttest', '2025-07-15', '2025-07-18', NULL, 5, 'dipinjam', 0, 0, NULL, '35713f5c-862a-4780-bfc0-b18eb5db96f6.svg', 'admin1233', '2025-07-15 05:47:25', '2025-07-15 05:49:56'),
(43, 121, 2, 'mahasiswa', 'Mahasiwaa 1', 'V3422026', '1231231231', 'Penggunaan Praktik Jaringan', '2025-07-24', '2025-07-25', '2025-07-24', 10, 'selesai', 8, 2, NULL, 'f560b5ac-09b9-4d22-8554-37d5614ad911.svg', 'Admin Pengelola', '2025-07-24 02:42:50', '2025-07-24 02:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman_inventaris`
--

CREATE TABLE `peminjaman_inventaris` (
  `id_peminjaman_inventaris` bigint UNSIGNED NOT NULL,
  `id_peminjam` bigint UNSIGNED NOT NULL,
  `status_peminjaman` enum('selesai','dipinjam','dikembalikan_sebagian') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dipinjam',
  `no_telp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_peminjam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ni_peminjam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_peminjam` enum('dosen','mahasiswa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_pengembalian` date DEFAULT NULL,
  `kegunaan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referensi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peminjaman_inventaris`
--

INSERT INTO `peminjaman_inventaris` (`id_peminjaman_inventaris`, `id_peminjam`, `status_peminjaman`, `no_telp`, `nama_peminjam`, `ni_peminjam`, `tipe_peminjam`, `tanggal_pinjam`, `tanggal_pengembalian`, `kegunaan`, `referensi`, `qr_code_path`, `created_by`, `created_at`, `updated_at`) VALUES
(62, 2, 'selesai', '3134213214', 'Dosen 1', '123123123123123', 'dosen', '2025-03-07', NULL, 'Uji Coba', 'INV-20250516054627-4748', NULL, 'admin1', '2025-05-15 22:46:27', '2025-05-15 23:30:34'),
(63, 2, 'selesai', '3134213214', 'Dosen 1', '123123123123123', 'dosen', '2025-03-07', NULL, 'Uji Coba 2', 'INV-20250516063113-2166', NULL, 'admin1', '2025-05-15 23:31:13', '2025-05-15 23:35:35'),
(66, 2, 'selesai', '1231231231', 'Mahasiwa 1', 'V3422026', 'mahasiswa', '2025-03-08', NULL, 'Uji Coba 2', 'INV-20250517091609-7511', NULL, 'admin1', '2025-05-17 02:16:09', '2025-05-17 03:48:10'),
(69, 2, 'selesai', '3134213214', 'Dosen 1', '123123123123123', 'dosen', '2025-03-08', NULL, 'Uji Coba 4', 'INV-20250517122239-1309', NULL, 'admin1', '2025-05-17 05:22:39', '2025-05-17 05:25:22'),
(70, 2, 'dikembalikan_sebagian', '3134213214', 'Dosen 1', '123123123123123', 'dosen', '2025-05-17', NULL, 'Uji Coba 4', 'INV-20250517124837-4706', NULL, 'admin1', '2025-05-17 05:48:37', '2025-05-29 03:22:36'),
(71, 5, 'dipinjam', '1231231231', 'Mahasiswa 2', 'V3422021', 'mahasiswa', '2025-05-29', '2025-07-07', 'pinjam', 'INV-20250529102807-6278', NULL, 'admin1', '2025-05-29 03:28:07', '2025-05-29 03:28:07'),
(72, 2, 'dipinjam', '3134213214', 'Dosen 1', '123123123123123', 'dosen', '2025-05-29', NULL, 'pinjam', 'INV-20250529103456-2956', NULL, 'admin1', '2025-05-29 03:34:56', '2025-05-29 03:34:56'),
(73, 5, 'selesai', '1231231231', 'Mahasiswa 2', 'V3422021', 'mahasiswa', '2025-05-29', NULL, 'Uji Testing', 'INV-20250529145430-6598', NULL, 'admin1', '2025-05-29 07:54:30', '2025-05-29 07:56:35'),
(74, 5, 'dipinjam', '1231231231', 'Mahasiswa mahasiswa mahasiswa dan mahasiswa', 'V3422021', 'mahasiswa', '2025-06-03', NULL, 'KBM', 'INV-20250603075242-2075', NULL, 'admin1', '2025-06-03 00:52:42', '2025-06-03 00:52:42'),
(75, 2, 'dipinjam', '1231231231', 'Mahasiwa 1', 'V3422026', 'mahasiswa', '2024-10-10', '2025-07-08', 'string', 'INV-20250612094938-6716', '46fbeefc-46ee-47bf-8f6f-dbfecdcfbf46.svg', 'admin1', '2025-06-12 02:49:38', '2025-06-12 02:49:38'),
(76, 5, 'selesai', '1231231231', 'Mahasiswa 2', 'V3422021', 'mahasiswa', '2025-06-29', NULL, 'gtw dah', 'INV-20250629151452-1568', '7910d08b-4e5c-4592-89ab-48ceed0a6156.svg', 'admin1', '2025-06-29 08:14:52', '2025-06-29 09:39:28'),
(77, 3, 'selesai', '1231111111', 'Mahasiswa 13', 'V3422022', 'mahasiswa', '2025-06-29', NULL, 'testt 2', 'INV-20250629164424-3895', '174ab798-8d73-4db6-8361-a3812b4b36e0.svg', 'admin1', '2025-06-29 09:44:24', '2025-06-29 09:49:34'),
(78, 5, 'selesai', '1231231231', 'Mahasiswa 2', 'V3422021', 'mahasiswa', '2025-07-02', NULL, 'Untuk keperluan KBM', 'INV-20250702073112-4983', '3fa8c750-87e7-41e0-a6f6-06cf7c3b4ca3.svg', 'admin1', '2025-07-02 00:31:12', '2025-07-02 00:32:12'),
(79, 2, 'selesai', '1231231231', 'Mahasiwa 1', 'V3422026', 'mahasiswa', '2025-07-02', NULL, 'testing mahasiswa', 'INV-20250702073312-6847', '7dd0e221-2acd-49d6-aee0-fdf26324dded.svg', 'admin1', '2025-07-02 00:33:12', '2025-07-02 00:42:22'),
(80, 5, 'dipinjam', '1231231231', 'Mahasiswa 2', 'V3422021', 'mahasiswa', '2025-07-01', '2025-07-08', 'coba tgl pengembalian', 'INV-20250709032812-2619', '261c55ca-fc18-4f98-9894-8893ae084237.svg', 'admin1', '2025-07-08 20:28:12', '2025-07-08 21:25:39'),
(81, 2, 'selesai', '1231231231', 'Mahasiwaa 1', 'V3422026', 'mahasiswa', '2025-07-24', '2025-07-25', 'Untuk KBM', 'INV-20250724093707-5429', 'e842180b-4ea0-4838-a2da-37daad30d2b5.svg', 'Admin Pengelola', '2025-07-24 02:37:07', '2025-07-24 02:39:23');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman_ruangs`
--

CREATE TABLE `peminjaman_ruangs` (
  `id_peminjaman_ruang` bigint UNSIGNED NOT NULL,
  `id_ruang` bigint UNSIGNED NOT NULL,
  `id_peminjam` bigint UNSIGNED NOT NULL,
  `id_periode` bigint UNSIGNED NOT NULL,
  `id_jenis_acara` bigint UNSIGNED NOT NULL,
  `id_sesi_mulai` bigint UNSIGNED NOT NULL,
  `id_sesi_selesai` bigint UNSIGNED NOT NULL,
  `kepentingan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `surat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_peminjaman` enum('MENUNGGU PERSETUJUAN','DISETUJUI','DITOLAK') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_peminjam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nim_peminjam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_peminjaman` date NOT NULL,
  `penanggung_jawab` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_reservasi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 2, 'auth_token', '62d6c5c1003479a0993c181722372b80f49c288840cae846618731c24cb1bd82', '[\"*\"]', '2025-02-25 00:11:37', NULL, '2025-02-25 00:10:23', '2025-02-25 00:11:37'),
(2, 'App\\Models\\User', 3, 'auth_token', 'd56ca3f862f356e7f51366ed3aa4ee2499c6013c26119a1390a9fbd51fe22285', '[\"*\"]', '2025-02-26 00:05:29', NULL, '2025-02-25 23:50:09', '2025-02-26 00:05:29'),
(3, 'App\\Models\\User', 3, 'auth_token', 'b3f6de97542e81b518d5b16546284328a8f69024ba2df668d853f9c386697b62', '[\"*\"]', NULL, NULL, '2025-02-25 23:57:28', '2025-02-25 23:57:28'),
(4, 'App\\Models\\User', 3, 'auth_token', 'ffcc67bd1d4adef82c66df41e539b71495076812b9b565fda94ef5fa51acd6d6', '[\"*\"]', NULL, NULL, '2025-02-26 00:04:57', '2025-02-26 00:04:57'),
(5, 'App\\Models\\Admin', 1, 'auth_token', '7ec4c7086f8d392af2b788ba62de56b892d486825344875cb3006f6ca4895da2', '[\"role:superadmin\"]', '2025-02-26 00:11:32', NULL, '2025-02-26 00:10:40', '2025-02-26 00:11:32'),
(6, 'App\\Models\\Dosen', 2, 'auth_token', '984532d20beeea846dcaf1020f527229d303a832ea083dc9f0f1b36812541687', '[\"*\"]', '2025-02-26 00:14:01', NULL, '2025-02-26 00:12:32', '2025-02-26 00:14:01'),
(7, 'App\\Models\\Dosen', 2, 'auth_token', '4070427c95d148d562ebac8e59b5fc6273319fc2ce330d1f443e6710d35d276c', '[\"*\"]', NULL, NULL, '2025-02-26 00:14:17', '2025-02-26 00:14:17'),
(8, 'App\\Models\\Admin', 1, 'auth_token', '6afef515bdf8e258deb19f56580f0b98daf9b559f3f07ae237a38523dc37f5dc', '[\"role:superadmin\"]', NULL, NULL, '2025-02-27 08:55:46', '2025-02-27 08:55:46'),
(9, 'App\\Models\\Admin', 1, 'auth_token', '8f0b25f5e3edf1e6d7768aa0b83202d244c12ac33875171909906f096ba2f0c7', '[\"role:superadmin\"]', NULL, NULL, '2025-02-27 08:56:00', '2025-02-27 08:56:00'),
(10, 'App\\Models\\Admin', 1, 'auth_token', '55fb4160bd1fe1455e85e87839de9a8cb2cf3b356de5ac14ffcd9b45e460b3bd', '[\"role:superadmin\"]', NULL, NULL, '2025-02-27 08:56:28', '2025-02-27 08:56:28'),
(11, 'App\\Models\\Admin', 1, 'auth_token', '75a51f5e3be630dc8a29512622e156e550453b1ea3f1b4292c590f0d268431e8', '[\"role:superadmin\"]', NULL, NULL, '2025-02-27 08:56:39', '2025-02-27 08:56:39'),
(12, 'App\\Models\\Admin', 1, 'auth_token', '9256a2946efb6856ea87d62e6c098436f19c0332e518c38aff36324732ca15ad', '[\"role:superadmin\"]', NULL, NULL, '2025-02-27 08:58:14', '2025-02-27 08:58:14'),
(13, 'App\\Models\\Admin', 1, 'auth_token', '3600e68ee8a2e8fc2c2ca151e50f800d231c4e94b888a28696bcf1a8b4f68513', '[\"role:superadmin\"]', NULL, NULL, '2025-02-27 08:59:37', '2025-02-27 08:59:37'),
(14, 'App\\Models\\Admin', 1, 'auth_token', 'dc5c684f5d5a7920bff1e07b9067d2c26593428e9ac162ba4a78cc7ea2fb5e6b', '[\"role:superadmin\"]', NULL, NULL, '2025-02-27 09:02:54', '2025-02-27 09:02:54'),
(15, 'App\\Models\\User', 4, 'auth_token', 'cd15e917d556266208c85a009ae59ba688f3c39651919d5127c9f56c9f702f0a', '[\"*\"]', NULL, NULL, '2025-02-27 09:19:48', '2025-02-27 09:19:48'),
(16, 'App\\Models\\Dosen', 2, 'auth_token', '9261a32a512ab6d338b76158821cd286a7bb7f62ea3cf8ddc5755f29d12cff74', '[\"*\"]', NULL, NULL, '2025-02-27 09:26:34', '2025-02-27 09:26:34'),
(17, 'App\\Models\\Dosen', 2, 'auth_token', 'e4a5b15a13761985664debb38c8864a32e5a92f9b600794c8e269a4c3eac14e0', '[\"*\"]', '2025-02-27 09:34:03', NULL, '2025-02-27 09:32:33', '2025-02-27 09:34:03'),
(18, 'App\\Models\\Dosen', 2, 'auth_token', '990e38923cfb25a03ef9aff193e5163cea6e4e9da405ae455a69de914b428986', '[\"*\"]', '2025-02-27 10:00:40', NULL, '2025-02-27 09:34:24', '2025-02-27 10:00:40'),
(19, 'App\\Models\\Admin', 1, 'auth_token', '8102cb0335a98b0fa16146bf58e827ba658a8e57e35c3fd751abf43da3cbb262', '[\"role:superadmin\"]', '2025-02-27 09:41:04', NULL, '2025-02-27 09:37:37', '2025-02-27 09:41:04'),
(20, 'App\\Models\\Dosen', 2, 'auth_token', 'd77ef750022115fc29d6a309742625f3142c332f5858af1601bab18128110fa7', '[\"*\"]', '2025-02-27 11:15:47', NULL, '2025-02-27 10:43:02', '2025-02-27 11:15:47'),
(21, 'App\\Models\\Dosen', 2, 'auth_token', 'aa7b67bd240c9951748499596dc4610d31427d75cc7fe330b597db174f0f2aea', '[\"*\"]', '2025-02-27 11:51:31', NULL, '2025-02-27 11:16:24', '2025-02-27 11:51:31'),
(22, 'App\\Models\\Dosen', 2, 'auth_token', 'c2f80069230035b1676f8f28a77aa2ca1fa6029e58d38afd1994d3f751ffcf63', '[\"*\"]', '2025-02-28 00:01:51', NULL, '2025-02-27 23:57:54', '2025-02-28 00:01:51'),
(23, 'App\\Models\\User', 4, 'auth_token', '413d77883f15c3afd2f97385b0912091e2db5b7360f09fe0e421f08aa313499d', '[\"*\"]', NULL, NULL, '2025-02-28 00:03:19', '2025-02-28 00:03:19'),
(24, 'App\\Models\\User', 4, 'auth_token', 'd58a64e64ba03a81d6419fe996d1430f35bcec14ad349bbc76dbba588c277e64', '[\"*\"]', NULL, NULL, '2025-02-28 00:44:55', '2025-02-28 00:44:55'),
(25, 'App\\Models\\User', 4, 'auth_token', '4233e85ad6bdf403698a184559a19cbdd6ad29d7ea7ed9b5ea961aa65a59f64b', '[\"*\"]', NULL, NULL, '2025-02-28 00:44:55', '2025-02-28 00:44:55'),
(26, 'App\\Models\\User', 4, 'auth_token', '6539c119ec38ebb8218e5193048df7fed899e847c1882cb813c5d9ff1ba22d7a', '[\"*\"]', NULL, NULL, '2025-02-28 00:45:20', '2025-02-28 00:45:20'),
(27, 'App\\Models\\Dosen', 2, 'auth_token', '1152611ce85bf872f54d6a122323d852507f280c26c3a65fe3ab6cc0c54b5884', '[\"*\"]', NULL, NULL, '2025-02-28 00:45:37', '2025-02-28 00:45:37'),
(28, 'App\\Models\\Admin', 1, 'auth_token', '9d98ab1088edbc04b6d032489e09ceb47212b2c2dc3a7fcca21aac00aa560d33', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 00:46:17', '2025-02-28 00:46:17'),
(29, 'App\\Models\\Admin', 1, 'auth_token', '92383c3f67f71b9e5163ebc79b3381436087517cf7aa5c10cf541b770c8f3a01', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 00:46:19', '2025-02-28 00:46:19'),
(30, 'App\\Models\\Admin', 1, 'auth_token', '7eaa80e753a7685a6d3380a18bdebf3f316aeb6e8ae1cbca0fbe41704a41d31c', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 00:46:24', '2025-02-28 00:46:24'),
(31, 'App\\Models\\Admin', 1, 'auth_token', '7d1efc1bd5b57714a475fa7d0772f2c3829eb783c43b63c493bee60a47c3fe6d', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 00:47:51', '2025-02-28 00:47:51'),
(32, 'App\\Models\\User', 4, 'auth_token', 'a1d21e36ece8cbab4988e0b1272c584b72db9af7dcd8781f706f993f25c6308c', '[\"*\"]', NULL, NULL, '2025-02-28 00:48:10', '2025-02-28 00:48:10'),
(33, 'App\\Models\\User', 4, 'auth_token', 'dc2b18f52583ac746e7d8d66426f5535827690ce1dceee3030822445865e8c70', '[\"*\"]', '2025-02-28 00:55:27', NULL, '2025-02-28 00:55:16', '2025-02-28 00:55:27'),
(34, 'App\\Models\\Admin', 1, 'auth_token', '85ed97fd476eb34932349001dbad6d5aac91decf39827d7c560c3fd3d6d3f2f5', '[\"role:superadmin\"]', '2025-02-28 00:56:21', NULL, '2025-02-28 00:56:15', '2025-02-28 00:56:21'),
(35, 'App\\Models\\Dosen', 2, 'auth_token', 'aee2d69bc6fe67fbae63f0945b45a7303a79734f20e7ed2c9b22b98308075dbb', '[\"*\"]', '2025-02-28 00:57:12', NULL, '2025-02-28 00:57:05', '2025-02-28 00:57:12'),
(36, 'App\\Models\\Admin', 1, 'auth_token', '3d7a798e8f3b29d397b24a1366b3d8160958e623fe5117d42d5d21260bf030a4', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 02:05:39', '2025-02-28 02:05:39'),
(37, 'App\\Models\\Admin', 1, 'auth_token', '293e629753853621a11a6c8100d11f8e665e703e52fc12021578770b53de9920', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 02:07:34', '2025-02-28 02:07:34'),
(38, 'App\\Models\\Admin', 1, 'auth_token', '21b6b3841ac9a69a8eee1e7eb12b28bedc67632393db5bc2e72b4004233a21a5', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 02:07:58', '2025-02-28 02:07:58'),
(39, 'App\\Models\\Admin', 1, 'auth_token', '31f7fc0155c49a1d52cdee28880ab4090ab146dd5c05eecfcaa85b0baf417256', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 02:09:59', '2025-02-28 02:09:59'),
(40, 'App\\Models\\Admin', 1, 'auth_token', '85b478fe1692f90b8c85e0a7e47b179a82f4aec9e6198c3855738a9a8e57e417', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 02:10:51', '2025-02-28 02:10:51'),
(41, 'App\\Models\\Admin', 1, 'auth_token', 'b8f9937e805f26824d4d4aa6ac5e01cbbe9113fcbb2cf15a87b8a2a2ccb613e1', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 02:11:48', '2025-02-28 02:11:48'),
(42, 'App\\Models\\Admin', 1, 'auth_token', '340994f2fc65bb5135ad79c63745d295495faa4694b55d70147fd1624e3c7dbd', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 02:31:07', '2025-02-28 02:31:07'),
(43, 'App\\Models\\User', 4, 'auth_token', '6b6a3632851ea5517ac4840eaec5fd1a8a3adfc0060fa1d9a1bb25371ea69225', '[\"*\"]', NULL, NULL, '2025-02-28 02:32:51', '2025-02-28 02:32:51'),
(44, 'App\\Models\\User', 4, 'auth_token', 'e3eddf4dd1835e7b8a90baf2673253ae8e6e0f38dbf848b6f57782f3cbc638b9', '[\"*\"]', NULL, NULL, '2025-02-28 02:35:27', '2025-02-28 02:35:27'),
(45, 'App\\Models\\User', 4, 'auth_token', 'accb82c2a7cf7bec3f6c041fc27b471b6a3525601f45659cbbe977be46c64f8e', '[\"*\"]', NULL, NULL, '2025-02-28 02:39:01', '2025-02-28 02:39:01'),
(46, 'App\\Models\\User', 4, 'auth_token', '29a645f9aa95c0fb09d593ff0c7907ca26969ac1af06e3d9a6e9f51538399d6b', '[\"*\"]', NULL, NULL, '2025-02-28 02:40:45', '2025-02-28 02:40:45'),
(47, 'App\\Models\\Dosen', 2, 'auth_token', '69f1758c0bf9e641881e09a1e136c89ae85d3f48c166d08cc1f7ee1edb6a4dc7', '[\"*\"]', NULL, NULL, '2025-02-28 02:42:50', '2025-02-28 02:42:50'),
(48, 'App\\Models\\Dosen', 2, 'auth_token', '072f1906dd8e12abac935de1999a725fbc4c5e30c0808d9ae1a8172c0ec77cff', '[\"*\"]', NULL, NULL, '2025-02-28 02:49:40', '2025-02-28 02:49:40'),
(49, 'App\\Models\\Dosen', 2, 'auth_token', '9e57151a0346135a416e70f46c7392d40db7badd4f12041239fd8589ba498123', '[\"*\"]', NULL, NULL, '2025-02-28 03:01:42', '2025-02-28 03:01:42'),
(50, 'App\\Models\\Dosen', 2, 'auth_token', 'a83e5de7cabfd4775dd12d8b02b9baec30d6f56690b6c36e6b0bcde0491e9711', '[\"*\"]', NULL, NULL, '2025-02-28 03:02:42', '2025-02-28 03:02:42'),
(51, 'App\\Models\\Dosen', 2, 'auth_token', '4ad411436afd4f41a9018693dccb9872572827c846062be13271d6fa0dc34bd1', '[\"*\"]', '2025-02-28 03:25:05', NULL, '2025-02-28 03:15:52', '2025-02-28 03:25:05'),
(52, 'App\\Models\\Dosen', 2, 'auth_token', 'd3f383ce77bb678edb83acbff02bc2174ef12a177b9a4b790a9e79d80d0bd43c', '[\"*\"]', '2025-02-28 03:34:11', NULL, '2025-02-28 03:26:46', '2025-02-28 03:34:11'),
(53, 'App\\Models\\Dosen', 2, 'auth_token', 'e9b4e60b250ab1410e630eeeffc31cfcd59f5a4077ffd0a6511449e386a04738', '[\"*\"]', '2025-02-28 03:45:03', NULL, '2025-02-28 03:35:41', '2025-02-28 03:45:03'),
(54, 'App\\Models\\Dosen', 2, 'auth_token', '0f4f33e2e4c04d86f791b3545c592b718355c54f7ac30199bf199cf09d5b70d0', '[\"*\"]', NULL, NULL, '2025-02-28 03:45:36', '2025-02-28 03:45:36'),
(55, 'App\\Models\\Dosen', 2, 'auth_token', '267383c4f6f3dbc5b425d1fb14f7583d7666d4b73ebb4a40aeeb7b8832c9b069', '[\"*\"]', '2025-02-28 03:48:27', NULL, '2025-02-28 03:48:07', '2025-02-28 03:48:27'),
(56, 'App\\Models\\Dosen', 2, 'auth_token', 'bb06951161948efbbdc2d886162b1690e568de878f55c1f19493f22dfab37a14', '[\"*\"]', '2025-02-28 03:53:00', NULL, '2025-02-28 03:52:37', '2025-02-28 03:53:00'),
(57, 'App\\Models\\Dosen', 2, 'auth_token', 'ee683303db50165e0e320b9ac2ba4243ddc62b8e45229b1d104a0867db31da45', '[\"*\"]', '2025-02-28 04:00:21', NULL, '2025-02-28 03:53:32', '2025-02-28 04:00:21'),
(58, 'App\\Models\\Dosen', 2, 'auth_token', '9ae020c41af0fa6d36fdd08c87df90f64e1c80944adda25493790802399ce5c3', '[\"*\"]', '2025-02-28 04:03:18', NULL, '2025-02-28 04:00:45', '2025-02-28 04:03:18'),
(59, 'App\\Models\\Dosen', 2, 'auth_token', '77bf1d66d3bac1b4d2233cb1faa3fb3460d7ed054acd95348aa83818c7ae08f7', '[\"*\"]', '2025-02-28 04:05:23', NULL, '2025-02-28 04:03:34', '2025-02-28 04:05:23'),
(60, 'App\\Models\\Dosen', 2, 'auth_token', '8a9f8318bdecfbc1e02d2c2c9ef31ad147ea0cb2830991cf546396cc47fbe339', '[\"*\"]', NULL, NULL, '2025-02-28 04:05:40', '2025-02-28 04:05:40'),
(61, 'App\\Models\\Dosen', 2, 'auth_token', '23d6ffec50d592867d1637809deb93271ae5e44f84e42b0448e27dc6ea313ebc', '[\"*\"]', '2025-02-28 04:11:57', NULL, '2025-02-28 04:06:51', '2025-02-28 04:11:57'),
(62, 'App\\Models\\Dosen', 2, 'auth_token', '1321041ca4167be8b877a4a42cf9f02eac2bcb48447eee4838abe10d7da8e178', '[\"*\"]', '2025-02-28 04:17:41', NULL, '2025-02-28 04:12:55', '2025-02-28 04:17:41'),
(63, 'App\\Models\\Dosen', 2, 'auth_token', '645784a4e35880b864626ceec916b970e34b6388676ed49a2b2036432b8eba6a', '[\"*\"]', '2025-02-28 04:19:53', NULL, '2025-02-28 04:18:27', '2025-02-28 04:19:53'),
(64, 'App\\Models\\Dosen', 2, 'auth_token', 'e999b8de188fac4bae3210ba7cf89f35a07ab4c5aebbf3b9b1d44351e84f93ff', '[\"*\"]', '2025-02-28 04:51:09', NULL, '2025-02-28 04:30:31', '2025-02-28 04:51:09'),
(65, 'App\\Models\\Dosen', 2, 'auth_token', '8ddbeb971394b954db752183678c79618e4e62c3e709d84fb2c66e87f1d22da5', '[\"*\"]', '2025-02-28 05:17:43', NULL, '2025-02-28 04:51:37', '2025-02-28 05:17:43'),
(66, 'App\\Models\\Dosen', 2, 'auth_token', '3a89c1309a65b7f9225c1befa31c12ed74c30473477c6b576714ac59b7fa2425', '[\"*\"]', '2025-02-28 05:41:05', NULL, '2025-02-28 05:19:38', '2025-02-28 05:41:05'),
(67, 'App\\Models\\Dosen', 2, 'auth_token', 'd84ef63c0d35a3252ced449dd5ce5e1dee03b877b3713783f968c8f487fa03ed', '[\"*\"]', '2025-02-28 05:59:53', NULL, '2025-02-28 05:41:47', '2025-02-28 05:59:53'),
(68, 'App\\Models\\Dosen', 2, 'auth_token', 'df8b85d1dd9b1a3bb85b779cc3b6a2bcfe92e729d61b458502f2c9d024de89fb', '[\"*\"]', '2025-02-28 06:04:47', NULL, '2025-02-28 06:00:17', '2025-02-28 06:04:47'),
(69, 'App\\Models\\Dosen', 2, 'auth_token', '30b0bf81d05ac87ebaa924b02600f2cf13b8066ee47b8987347c727ade48311b', '[\"*\"]', '2025-02-28 20:11:39', NULL, '2025-02-28 20:07:14', '2025-02-28 20:11:39'),
(70, 'App\\Models\\Dosen', 2, 'auth_token', '895cd235460f21a0bdb0c3fafdf038ef00745b0a7126c52cdb13a64ba333aa91', '[\"*\"]', '2025-02-28 21:38:04', NULL, '2025-02-28 20:14:43', '2025-02-28 21:38:04'),
(71, 'App\\Models\\Dosen', 2, 'auth_token', '3661a094effe064756c86601c28bf29ad15faa9de9a38d46d4063446aa86dc92', '[\"*\"]', '2025-02-28 21:43:44', NULL, '2025-02-28 21:38:19', '2025-02-28 21:43:44'),
(72, 'App\\Models\\User', 4, 'auth_token', 'b7cf350b2ac4cfc2ba47a5efaafb056c46fe9373544319878506a9829beaca10', '[\"*\"]', NULL, NULL, '2025-02-28 21:47:59', '2025-02-28 21:47:59'),
(73, 'App\\Models\\Admin', 1, 'auth_token', '1364f4f4e136a5ab2ae0a3a2d463de50950622e51244df3d21e1a4ba353b5e1c', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 21:49:19', '2025-02-28 21:49:19'),
(74, 'App\\Models\\User', 4, 'auth_token', '47828ebaefd97e3b81b74020c2ea54cd123a7368acbf2825477546d8ff4efe8e', '[\"*\"]', NULL, NULL, '2025-02-28 22:01:56', '2025-02-28 22:01:56'),
(75, 'App\\Models\\Dosen', 2, 'auth_token', '3f5f6e2c1bb471a17f198eb83d20bcb4a58491925239e1eda9eb8841398a6169', '[\"*\"]', '2025-02-28 22:26:18', NULL, '2025-02-28 22:13:36', '2025-02-28 22:26:18'),
(76, 'App\\Models\\Dosen', 2, 'auth_token', '5e7db10f7f6a7dc29b952633d034814b0866cc0d01de5304ea2f08979175aeea', '[\"*\"]', '2025-02-28 22:26:48', NULL, '2025-02-28 22:26:36', '2025-02-28 22:26:48'),
(77, 'App\\Models\\Dosen', 2, 'auth_token', '12efa5ecd40399bdc47bf4cd10228c3cbd40e41aaad16d54478611cbc313060e', '[\"*\"]', '2025-02-28 22:27:00', NULL, '2025-02-28 22:26:59', '2025-02-28 22:27:00'),
(78, 'App\\Models\\User', 4, 'auth_token', '0360e20727cc8678de1a8cfde72bd1151f9ae55871eb3b0b29230d09f6a4d05b', '[\"*\"]', NULL, NULL, '2025-02-28 22:27:25', '2025-02-28 22:27:25'),
(79, 'App\\Models\\Admin', 1, 'auth_token', 'f7fe08e2b3a1e1ffbbc24da198e1cb600cd414bd310f86b6d93acf44cb1597b7', '[\"role:superadmin\"]', NULL, NULL, '2025-02-28 22:28:15', '2025-02-28 22:28:15'),
(80, 'App\\Models\\Dosen', 2, 'auth_token', 'e91094ba5b31340cbdf9fcefb01f009bf808d13abea480fdc7befb647132a002', '[\"*\"]', '2025-02-28 23:46:22', NULL, '2025-02-28 22:29:04', '2025-02-28 23:46:22'),
(81, 'App\\Models\\User', 4, 'auth_token', '6b264a3d8be3bb2c0c727783e57b381c9471db072557c24977e2e3a3d19e4ce3', '[\"*\"]', NULL, NULL, '2025-03-01 00:03:24', '2025-03-01 00:03:24'),
(82, 'App\\Models\\User', 4, 'auth_token', 'd0c46a7bdf28cfe8a4f9ed8ce27377263db0189bd1d3aa46c8ee0fcf0bc23bf2', '[\"*\"]', NULL, NULL, '2025-03-01 00:11:57', '2025-03-01 00:11:57'),
(83, 'App\\Models\\User', 4, 'auth_token', 'd1e1a5138398fb97132df7e634b3dd5646d6da330181d2982f629667ad844055', '[\"*\"]', NULL, NULL, '2025-03-01 00:13:26', '2025-03-01 00:13:26'),
(84, 'App\\Models\\User', 4, 'auth_token', '5643fa2756a728968297e19479df828e4c7e4a63736d89073d8afa81142a1e1a', '[\"*\"]', NULL, NULL, '2025-03-01 00:13:54', '2025-03-01 00:13:54'),
(85, 'App\\Models\\User', 4, 'auth_token', 'f1ad7c4cbb1a79ce6f9c1f40ab0dcbb63375ef57ae63d9b12fe0bb3e0d445584', '[\"*\"]', NULL, NULL, '2025-03-01 00:14:35', '2025-03-01 00:14:35'),
(86, 'App\\Models\\User', 4, 'auth_token', '09016fcbc93a9dd8503ac1338ba32de9f747e23077d61f05f6afe29d4df5a8cc', '[\"*\"]', NULL, NULL, '2025-03-01 00:20:26', '2025-03-01 00:20:26'),
(87, 'App\\Models\\User', 4, 'auth_token', '12827c9586bb76d3c80aea09265735ac593224c1a26c60a9caa6bdc0ab16a91d', '[\"*\"]', NULL, NULL, '2025-03-01 00:22:14', '2025-03-01 00:22:14'),
(88, 'App\\Models\\User', 4, 'auth_token', '0c5d8a867630d27d91be3e6496118bc98c30c2c96b620e5d4d401e905dbf0eed', '[\"*\"]', NULL, NULL, '2025-03-01 00:23:37', '2025-03-01 00:23:37'),
(89, 'App\\Models\\User', 4, 'auth_token', '1b580deda61cd64362e103022ccaff6e680f418165bc2ae1785dbae501d3181f', '[\"*\"]', NULL, NULL, '2025-03-01 00:29:19', '2025-03-01 00:29:19'),
(90, 'App\\Models\\User', 4, 'auth_token', 'e40e2a9cb6650136ef39a70553b000a172a3a90466a98846daad781d1457e9b4', '[\"*\"]', NULL, NULL, '2025-03-01 00:29:37', '2025-03-01 00:29:37'),
(91, 'App\\Models\\User', 4, 'auth_token', '1d85ab8ad46dea9e35556d955b646c97f308b3350716229c93b1b039e6d434e0', '[\"*\"]', NULL, NULL, '2025-03-01 00:29:53', '2025-03-01 00:29:53'),
(92, 'App\\Models\\User', 4, 'auth_token', 'eab0784c798fe1bf64da64b5b9aaf208d4e8155461c34cf6c12f52a2d175b37c', '[\"*\"]', NULL, NULL, '2025-03-01 00:32:08', '2025-03-01 00:32:08'),
(93, 'App\\Models\\User', 4, 'auth_token', 'dd334709f57ce216612d7a61761c4533a66c15c4bff189bb000ec4ad4635f3a4', '[\"*\"]', NULL, NULL, '2025-03-01 00:34:38', '2025-03-01 00:34:38'),
(94, 'App\\Models\\Dosen', 2, 'auth_token', '5cc1df8b526e864e41dd39c31fe34af3f7458669c4c1596d48d7150ca16edac8', '[\"*\"]', '2025-03-01 00:40:50', NULL, '2025-03-01 00:38:43', '2025-03-01 00:40:50'),
(95, 'App\\Models\\Dosen', 2, 'auth_token', '2dcb1959ca716930155be590e15c8e13d4d0480429905b8a7153ae5780d05ca0', '[\"*\"]', '2025-03-01 00:44:48', NULL, '2025-03-01 00:41:07', '2025-03-01 00:44:48'),
(96, 'App\\Models\\Dosen', 2, 'auth_token', '3e54af21a724168caa75e34e400854f41a9c10d0cd34055af92fbe768d4d507f', '[\"*\"]', '2025-03-01 00:50:27', NULL, '2025-03-01 00:44:54', '2025-03-01 00:50:27'),
(97, 'App\\Models\\User', 4, 'auth_token', '9004dda36bfd34cc84c4ee91d1e0fefe5fe5df05a9d94f93078df54b1abfa2c8', '[\"*\"]', NULL, NULL, '2025-03-01 21:30:31', '2025-03-01 21:30:31'),
(98, 'App\\Models\\User', 4, 'auth_token', '7d5e9371bd155fbc6353ffc6139aa81a95fe161343b906bbd7226800f1dd120a', '[\"*\"]', NULL, NULL, '2025-03-01 21:58:16', '2025-03-01 21:58:16'),
(100, 'App\\Models\\User', 4, 'auth_token', '36aed7c3dfc3dace23f8692eda5dcb37170460b8d3d3f76b61b7295b128b31e4', '[\"*\"]', '2025-03-01 22:35:59', NULL, '2025-03-01 22:34:27', '2025-03-01 22:35:59'),
(101, 'App\\Models\\User', 4, 'auth_token', 'f932b0ab93d9c9de56902aab619114f134e6002aee6d9b181f6092990d05f2f2', '[\"*\"]', '2025-03-01 22:59:36', NULL, '2025-03-01 22:48:55', '2025-03-01 22:59:36'),
(102, 'App\\Models\\Dosen', 2, 'auth_token', 'd544abb0a208e67d9358c677e12005a92f54f41e41d9a4f5a993b15a26e6e999', '[\"*\"]', '2025-03-01 23:26:33', NULL, '2025-03-01 23:22:05', '2025-03-01 23:26:33'),
(103, 'App\\Models\\Dosen', 2, 'auth_token', 'e3ae20a3452efd649399a8c57d70d9539a5083871afa40301d5b98ab2f11267d', '[\"*\"]', '2025-03-01 23:27:04', NULL, '2025-03-01 23:27:04', '2025-03-01 23:27:04'),
(104, 'App\\Models\\Dosen', 2, 'auth_token', '63ce01d734322270bdb401c0cd154c83e26635126a91e8ba6fd9af22879f2f2f', '[\"*\"]', '2025-03-02 00:19:53', NULL, '2025-03-01 23:28:57', '2025-03-02 00:19:53'),
(105, 'App\\Models\\Admin', 1, 'auth_token', 'c526b472a8989ed79a04ddec500da8669eeadc30396b7b14a67340b063438896', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 00:26:01', '2025-03-02 00:26:01'),
(106, 'App\\Models\\Admin', 3, 'auth_token', 'ec7e1689214f3976c35e58ff7601d571110a37a7ef4c794308eef4d97628330d', '[\"role:admin_pengelola\"]', NULL, NULL, '2025-03-02 00:30:24', '2025-03-02 00:30:24'),
(107, 'App\\Models\\Admin', 1, 'auth_token', 'e0315fb6d00e6ff9842443cbc49f46a686be3c70bdb8752d34d46138d3feb28d', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 00:33:45', '2025-03-02 00:33:45'),
(108, 'App\\Models\\Admin', 1, 'auth_token', '408ddd0ff1fe9084cfdc37ffb96088b855fd6b85a10e4272a36bcaaeec41f013', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 00:37:06', '2025-03-02 00:37:06'),
(109, 'App\\Models\\Admin', 3, 'auth_token', '86f1a7e70ee85f4f948b5b2641e624f13a1c11a65d5b492e238602a380acaf39', '[\"role:admin_pengelola\"]', NULL, NULL, '2025-03-02 00:37:32', '2025-03-02 00:37:32'),
(110, 'App\\Models\\Admin', 1, 'auth_token', '381ee4fd9144e9b3311c9fb83a3b15302690ad87e55efae816d489de83b5e3ce', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 00:45:36', '2025-03-02 00:45:36'),
(111, 'App\\Models\\Admin', 2, 'auth_token', 'e4bfee47c5639ebe7694fb74e17717f5802ab89db5a5c7eb14769e2e761b81bd', '[\"role:admin_ta\"]', NULL, NULL, '2025-03-02 00:47:27', '2025-03-02 00:47:27'),
(112, 'App\\Models\\Admin', 3, 'auth_token', 'f1cd01ac6165e182b20cc6072a49d5e51ec0a449f20d36c036dc8b5b894c3f83', '[\"role:admin_pengelola\"]', NULL, NULL, '2025-03-02 00:47:53', '2025-03-02 00:47:53'),
(113, 'App\\Models\\Admin', 1, 'auth_token', '723304366382da5c898fe5297a0b57de44ddad7580151ee25bb90b2f3235b1c1', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 00:48:09', '2025-03-02 00:48:09'),
(114, 'App\\Models\\Dosen', 2, 'auth_token', '3e4cb9270304f58321ea5f974f41fa7d39eafc36edece3561536344c21881f4c', '[\"*\"]', '2025-03-02 00:54:02', NULL, '2025-03-02 00:54:02', '2025-03-02 00:54:02'),
(115, 'App\\Models\\Admin', 1, 'auth_token', '4411efe00e3d8bb4d4a944f5a49170e9457976aebc4d8ff1b43a012f1f85fbb8', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 00:54:57', '2025-03-02 00:54:57'),
(116, 'App\\Models\\Admin', 2, 'auth_token', 'fd0673aa7c06a05decad254a624fdc9b720306886f2b7130fd9bd37a959f49f5', '[\"role:admin_ta\"]', NULL, NULL, '2025-03-02 01:21:33', '2025-03-02 01:21:33'),
(117, 'App\\Models\\Admin', 3, 'auth_token', '175fd9038971f2032bfcf7f7f602a1215e91903a1d606e1074b95bc46423009a', '[\"role:admin_pengelola\"]', NULL, NULL, '2025-03-02 01:22:23', '2025-03-02 01:22:23'),
(118, 'App\\Models\\Admin', 1, 'auth_token', '5b99e674cc8a9f76312671d675edbd1f82059dc7fbbffaaa79f3c0058d57d2c4', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 01:22:45', '2025-03-02 01:22:45'),
(119, 'App\\Models\\Admin', 1, 'auth_token', 'e90145f04d5332dc70214b5b5a47a27c089beeb3b8814560f9255ea967243688', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 01:24:30', '2025-03-02 01:24:30'),
(120, 'App\\Models\\User', 4, 'auth_token', '710b2243027769c3b60db3a570b70a0ad12fca60d94802df2fa5d96dfc05bf34', '[\"*\"]', NULL, NULL, '2025-03-02 01:59:40', '2025-03-02 01:59:40'),
(121, 'App\\Models\\User', 4, 'auth_token', 'ff287f0c1039f8544716bb445ed92c092825141d04db0b1ee253fdae360de961', '[\"*\"]', NULL, NULL, '2025-03-02 02:01:05', '2025-03-02 02:01:05'),
(122, 'App\\Models\\User', 4, 'auth_token', '896f8f11935782d90b483b9fd4f8273ff732570dfd699dac8a0e8edab9dec24d', '[\"*\"]', '2025-03-02 02:09:05', NULL, '2025-03-02 02:01:57', '2025-03-02 02:09:05'),
(123, 'App\\Models\\User', 4, 'auth_token', '4bfe5703bc9c2bb1743a1e081c72126c04816877cba976a3ed591a7597b5757d', '[\"*\"]', NULL, NULL, '2025-03-02 02:33:00', '2025-03-02 02:33:00'),
(124, 'App\\Models\\Dosen', 2, 'auth_token', 'd4b7e4ec7f04638d54204a532aa5a359cb1a6bccc2812612663e0fa851f952fe', '[\"*\"]', '2025-03-02 02:48:22', NULL, '2025-03-02 02:48:21', '2025-03-02 02:48:22'),
(125, 'App\\Models\\Dosen', 2, 'auth_token', '65c129f6a4b27bf2e94eb3d2b22223070e55e16c1994351c8541e1f423a68dd9', '[\"*\"]', '2025-03-02 02:49:13', NULL, '2025-03-02 02:49:05', '2025-03-02 02:49:13'),
(126, 'App\\Models\\Dosen', 2, 'auth_token', '250e976d79ef4968abf1359fcc7eae462476c5bcb1e0210c1136c69cdbe5bd31', '[\"*\"]', '2025-03-02 02:49:28', NULL, '2025-03-02 02:49:27', '2025-03-02 02:49:28'),
(127, 'App\\Models\\Admin', 1, 'auth_token', '6faaa43c31416cd9f8d0e297e2ad55d664d03f5d3f770a332b9ac42a72df10d1', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 02:50:16', '2025-03-02 02:50:16'),
(128, 'App\\Models\\Admin', 1, 'auth_token', 'd35ab124d8571028645037f06487d062a9e4858555c6d10448fbb799c4bd8f4e', '[\"role:superadmin\"]', NULL, NULL, '2025-03-02 02:51:30', '2025-03-02 02:51:30'),
(129, 'App\\Models\\Admin', 3, 'auth_token', 'd3c60f52fbcd963d98b7c8e2f757213d289d19f9c2b59393eb0103048868c67e', '[\"role:admin_pengelola\"]', '2025-03-03 02:43:16', NULL, '2025-03-02 23:28:33', '2025-03-03 02:43:16'),
(130, 'App\\Models\\Admin', 3, 'auth_token', 'c719a6bbdab5ae62b9a875e8f28534a093272e4be9238632079d6f8caf692c8a', '[\"role:admin_pengelola\"]', '2025-03-03 21:44:14', NULL, '2025-03-03 18:55:21', '2025-03-03 21:44:14'),
(131, 'App\\Models\\Admin', 3, 'auth_token', 'c5d4d5980f26dd33c9689cbe28fc0ac143abe0ce90582d43c7352b4c9f8c6e5d', '[\"role:admin_pengelola\"]', NULL, NULL, '2025-03-03 19:07:08', '2025-03-03 19:07:08'),
(132, 'App\\Models\\Admin', 3, 'auth_token', '6518d68c0321cd6b32f9a0653d0079f4f7e7589c9e778a5866cdbc016dbea5d6', '[\"role:admin_pengelola\"]', '2025-03-04 23:30:00', NULL, '2025-03-04 19:46:18', '2025-03-04 23:30:00'),
(133, 'App\\Models\\Admin', 3, 'auth_token', 'daf86511b661bbe1ef6d51bdeb24d9d13d3b2f5face40c1a043eaea401d26c13', '[\"role:admin_pengelola\"]', '2025-03-06 23:49:37', NULL, '2025-03-06 23:00:59', '2025-03-06 23:49:37'),
(134, 'App\\Models\\Admin', 3, 'auth_token', 'd7286fcd666a9f5375d72d5c41c68e4e9e8ad9927144e788c23a67f60500604d', '[\"role:admin_pengelola\"]', '2025-03-09 03:49:24', NULL, '2025-03-08 23:54:24', '2025-03-09 03:49:24'),
(135, 'App\\Models\\Admin', 3, 'auth_token', '21ba6a1b688556d2d0c8d2bea4ffae93f5a605b60cebf41adce124bc6d09c914', '[\"role:admin_pengelola\"]', '2025-03-09 03:49:02', NULL, '2025-03-09 00:39:06', '2025-03-09 03:49:02'),
(136, 'App\\Models\\Admin', 3, 'auth_token', '3d8750bb53a97bba18e8d4b5c3adb2585b43f8af8beb5ec68b1e4c324708f2c8', '[\"role:admin_pengelola\"]', '2025-03-09 09:51:04', NULL, '2025-03-09 07:12:00', '2025-03-09 09:51:04'),
(137, 'App\\Models\\Admin', 3, 'auth_token', '86b56b88901406dcf79849381caf20d33d5f0b221425e8d7c282ffb5fbdfd567', '[\"role:admin_pengelola\"]', '2025-03-09 09:42:31', NULL, '2025-03-09 07:18:18', '2025-03-09 09:42:31'),
(138, 'App\\Models\\Admin', 3, 'auth_token', '1483859c0bcc59dc2fc43e4c3b5ed9b8c400fdc9632d1b68ad1c669720f74a47', '[\"role:admin_pengelola\"]', '2025-03-10 00:52:19', NULL, '2025-03-09 23:22:42', '2025-03-10 00:52:19'),
(139, 'App\\Models\\Admin', 3, 'auth_token', '2d6191512c97672ec0275bff847dcd1046a50afdfefe288efe1494e5e05383a5', '[\"role:admin_pengelola\"]', '2025-03-10 03:37:32', NULL, '2025-03-09 23:38:36', '2025-03-10 03:37:32'),
(140, 'App\\Models\\Admin', 3, 'auth_token', 'b4964f2d5830939724976ef982be296b4a1d8b3fef865963ab4b22f1b91bf87a', '[\"role:admin_pengelola\"]', '2025-03-11 00:29:20', NULL, '2025-03-10 23:01:28', '2025-03-11 00:29:20'),
(141, 'App\\Models\\User', 4, 'auth_token', '7c2f56acf2b9d7ebf57ca1f61ced8096297f869ba05dfdb7d8d5529436a8aa81', '[\"*\"]', NULL, NULL, '2025-03-11 01:35:58', '2025-03-11 01:35:58'),
(142, 'App\\Models\\Admin', 3, 'auth_token', '47e3b90f8e713af2cbcaca86705ac13690575142f213b0e5361c9fc72b1d2c22', '[\"role:admin_pengelola\"]', '2025-03-11 23:44:09', NULL, '2025-03-11 23:04:02', '2025-03-11 23:44:09'),
(143, 'App\\Models\\Admin', 3, 'auth_token', '5947094b8ed4d82a341471ace2285a9fc3963fb8c0681946e88111b3f8c0a8d9', '[\"role:admin_pengelola\"]', '2025-03-14 01:25:33', NULL, '2025-03-13 22:45:20', '2025-03-14 01:25:33'),
(144, 'App\\Models\\User', 4, 'auth_token', '46958eac574f766f7826a798cbc5df4719f812c82bd420afae8238a83268adde', '[\"*\"]', NULL, NULL, '2025-03-15 23:51:58', '2025-03-15 23:51:58'),
(146, 'App\\Models\\Admin', 3, 'auth_token', '792e1bb5fde933a1628c6bfe575c74422432896ce57532f77328e551e3d27ab2', '[\"role:admin_pengelola\"]', '2025-03-16 03:34:26', NULL, '2025-03-16 00:01:58', '2025-03-16 03:34:26'),
(147, 'App\\Models\\Admin', 3, 'auth_token', 'f764f4df8c912d82eec1ec3962c84666541a05a634e85a5b5f3ea46e4a4745ac', '[\"role:admin_pengelola\"]', '2025-03-16 01:12:53', NULL, '2025-03-16 00:33:15', '2025-03-16 01:12:53'),
(148, 'App\\Models\\Admin', 3, 'auth_token', 'cf4b4713d23e79c740a9c0abd853c69060683af417426aba3877e01919802f12', '[\"role:admin_pengelola\"]', '2025-03-17 02:32:36', NULL, '2025-03-16 23:00:47', '2025-03-17 02:32:36'),
(149, 'App\\Models\\Admin', 3, 'auth_token', '5927d9d02dce213aacb6543ece67a505594dfc1d2851269fdd012c8fd33ed2eb', '[\"role:admin_pengelola\"]', '2025-03-18 02:00:57', NULL, '2025-03-17 23:32:39', '2025-03-18 02:00:57'),
(150, 'App\\Models\\Admin', 3, 'auth_token', '1c00ce0f9cd5fcb86130d1ee7c771523cd467e9ccea3fb9725537a24fb4c35b5', '[\"role:admin_pengelola\"]', '2025-03-19 03:39:11', NULL, '2025-03-19 00:03:10', '2025-03-19 03:39:11'),
(151, 'App\\Models\\Admin', 3, 'auth_token', '6a58aac04244f8ae50be1f5ae48a3ac325b321782242385b10bd38cb61b97ca0', '[\"role:admin_pengelola\"]', '2025-03-19 23:26:52', NULL, '2025-03-19 23:26:24', '2025-03-19 23:26:52'),
(152, 'App\\Models\\Admin', 3, 'auth_token', 'd61110b6df28adea94cd3275441342afddd7e089797b5b2e134cde10715a97c3', '[\"role:admin_pengelola\"]', '2025-03-20 03:07:01', NULL, '2025-03-19 23:30:28', '2025-03-20 03:07:01'),
(153, 'App\\Models\\Admin', 3, 'auth_token', '369ec4ace1342ba3a52b718743e38d174c6b72d0e65d9f2862eca30e759349b2', '[\"role:admin_pengelola\"]', '2025-03-22 00:58:39', NULL, '2025-03-21 23:49:38', '2025-03-22 00:58:39'),
(154, 'App\\Models\\Admin', 3, 'auth_token', '46ff925adc33018f6640cc8378a010b72dc78577b8ec073b83ee9c6b513c092e', '[\"role:admin_pengelola\"]', '2025-03-23 03:50:42', NULL, '2025-03-23 03:15:47', '2025-03-23 03:50:42'),
(155, 'App\\Models\\Admin', 3, 'auth_token', '3d600e9e3d362c092729c7bcf25c42ec12b5e58488c1f7c8020c060981efd562', '[\"role:admin_pengelola\"]', '2025-03-23 03:23:22', NULL, '2025-03-23 03:23:10', '2025-03-23 03:23:22'),
(156, 'App\\Models\\Admin', 3, 'auth_token', 'ed080f8c2ee210311fa6840ad8700c165ccff162ebdd99be438d106193b61ad5', '[\"role:admin_pengelola\"]', '2025-03-24 03:19:40', NULL, '2025-03-23 23:19:51', '2025-03-24 03:19:40'),
(157, 'App\\Models\\Admin', 3, 'auth_token', 'b05b1b206e0f741d1c6604b51a4b4e90b9e3ddddf109b3c7df13be93721b92f6', '[\"role:admin_pengelola\"]', '2025-03-24 03:48:09', NULL, '2025-03-24 03:20:24', '2025-03-24 03:48:09'),
(158, 'App\\Models\\Admin', 3, 'auth_token', 'f82f2cf47c5cbd8def0a5048eb83214794864cc925b0276da4d8ac668e91553d', '[\"role:admin_pengelola\"]', '2025-03-31 23:45:58', NULL, '2025-03-31 23:19:00', '2025-03-31 23:45:58'),
(159, 'App\\Models\\Admin', 3, 'auth_token', '479b76f0223ce2bfa4cd49cff8608be75e92a15d844eccc61991270531adcb83', '[\"role:admin_pengelola\"]', '2025-04-01 12:09:13', NULL, '2025-04-01 08:11:13', '2025-04-01 12:09:13'),
(160, 'App\\Models\\Admin', 3, 'auth_token', 'd06c1077e23386624efbb560ca99b165a5dd52fbc7ca17db3b1c88ba3d4e0fbd', '[\"role:admin_pengelola\"]', '2025-04-01 12:13:33', NULL, '2025-04-01 12:12:57', '2025-04-01 12:13:33'),
(161, 'App\\Models\\Admin', 3, 'auth_token', 'e64c0468a427c81feec3457e5df29a2a19f32f94c710a8443e5904ee9ca452b6', '[\"role:admin_pengelola\"]', NULL, NULL, '2025-04-01 20:47:43', '2025-04-01 20:47:43'),
(162, 'App\\Models\\Admin', 3, 'auth_token', '8092b0fd9dbc4857a70870b75567d8c9f4298b24eac36eefe4ad335cb96c7533', '[\"role:admin_pengelola\"]', '2025-04-01 20:57:33', NULL, '2025-04-01 20:47:44', '2025-04-01 20:57:33'),
(163, 'App\\Models\\Admin', 3, 'auth_token', 'ba4619266285127fe7d832855bc08d59d25ded833f83e9172504b9bbfbfdab67', '[\"role:admin_pengelola\"]', '2025-04-05 23:58:43', NULL, '2025-04-05 20:48:26', '2025-04-05 23:58:43'),
(164, 'App\\Models\\Admin', 3, 'auth_token', 'b527593ef4db8e5ad80a450e2960199141d56880a8207c1aa3a422c56e4663e6', '[\"role:admin_pengelola\"]', '2025-04-10 01:32:47', NULL, '2025-04-10 00:10:44', '2025-04-10 01:32:47'),
(165, 'App\\Models\\Admin', 3, 'auth_token', '3f1a40851ccba087e2aacb85f22f51edac6873d47580abc69ea6fb6f0f5903b7', '[\"role:admin_pengelola\"]', '2025-04-14 01:22:36', NULL, '2025-04-14 01:20:37', '2025-04-14 01:22:36'),
(166, 'App\\Models\\Admin', 3, 'auth_token', 'da176d04ab31d9de09caa53c1fcbb3a571ddebb0bfa7dfc0c36413f22e4303dc', '[\"role:admin_pengelola\"]', '2025-04-15 07:24:51', NULL, '2025-04-15 07:24:15', '2025-04-15 07:24:51'),
(167, 'App\\Models\\Admin', 3, 'auth_token', '627d47b68a2f8847c56178089c2e76217db88edcb8a2aabbb5c4aac9f850441a', '[\"role:admin_pengelola\"]', '2025-04-16 00:18:58', NULL, '2025-04-15 23:33:30', '2025-04-16 00:18:58'),
(168, 'App\\Models\\Admin', 3, 'auth_token', 'c426b8561287d98a5ab54aa5593b6eef41486104654abb22ce2190c6af2339a4', '[\"role:admin_pengelola\"]', '2025-04-17 08:33:53', NULL, '2025-04-17 07:15:16', '2025-04-17 08:33:53'),
(170, 'App\\Models\\Admin', 3, 'auth_token', 'b4ae27ea6e64f8d85929ebc293f4a0ee404e630e28ca57443047ba6ce822751d', '[\"role:admin_pengelola\"]', '2025-04-21 19:42:01', NULL, '2025-04-21 18:59:09', '2025-04-21 19:42:01'),
(171, 'App\\Models\\Admin', 3, 'auth_token', '66f91e3669074b07a34a4b8b9fce4e3592e57135a4e9a214a0f2ce3ea25b417c', '[\"role:admin_pengelola\"]', '2025-04-25 00:52:29', NULL, '2025-04-25 00:04:49', '2025-04-25 00:52:29'),
(172, 'App\\Models\\Admin', 3, 'auth_token', '0b7e7051d554c857fb03de210f8fbbe3a59d5489099bb842347cbc1be21bac83', '[\"role:admin_pengelola\"]', '2025-05-02 05:08:39', NULL, '2025-05-02 03:16:39', '2025-05-02 05:08:39'),
(173, 'App\\Models\\Admin', 3, 'auth_token', 'a3a8e6c286ad9a58d874fff741c9b34cf5a65fafe75bf3319272d0015d26fb85', '[\"role:admin_pengelola\"]', '2025-05-02 23:52:01', NULL, '2025-05-02 23:39:24', '2025-05-02 23:52:01'),
(174, 'App\\Models\\Admin', 3, 'auth_token', 'fe8c386f1af6f9b1eda525e79e27465f8a2f2c625a5ac5fdb94e90b1cfc94a0e', '[\"role:admin_pengelola\"]', NULL, NULL, '2025-05-04 21:30:44', '2025-05-04 21:30:44'),
(175, 'App\\Models\\Admin', 3, 'auth_token', 'd82c459e3ef45b137fa2505c3e7bb19bb0f6c6e371c93078b8eb415831ca5485', '[\"role:admin_pengelola\"]', '2025-05-13 06:33:06', NULL, '2025-05-13 05:23:00', '2025-05-13 06:33:06'),
(176, 'App\\Models\\Admin', 3, 'auth_token', 'bd35120bf9a5068eea810c7b893a6bad7d6b16675dc98d83a92433e3006c3c8d', '[\"role:admin_pengelola\"]', '2025-05-15 23:35:35', NULL, '2025-05-15 22:42:01', '2025-05-15 23:35:35'),
(177, 'App\\Models\\Admin', 3, 'auth_token', '75b5f2f98d32b1a466495b469caed19ebfa433f5d4fbe72b2844c45ae0fcbe88', '[\"role:admin_pengelola\"]', '2025-05-17 05:51:42', NULL, '2025-05-17 02:12:20', '2025-05-17 05:51:42'),
(178, 'App\\Models\\Admin', 3, 'auth_token', 'f9c3a2d91c03579aed8fd9c098c566695ba94012005af8fe2752eb66f6ee9229', '[\"role:admin_pengelola\"]', '2025-05-17 11:42:38', NULL, '2025-05-17 10:49:28', '2025-05-17 11:42:38'),
(179, 'App\\Models\\Admin', 3, 'auth_token', '923aef5fda33eca32f5df6f8d553b76259c7a8e6705c859f8ed53c9014021e60', '[\"role:admin_pengelola\"]', '2025-05-18 00:37:28', NULL, '2025-05-17 23:53:15', '2025-05-18 00:37:28'),
(180, 'App\\Models\\Admin', 3, 'auth_token', '25f4f194f7b3c9702cdd9b1f3279976bf704b1b84ff10a4be994ee670d150983', '[\"role:admin_pengelola\"]', '2025-05-24 07:34:33', NULL, '2025-05-24 07:27:30', '2025-05-24 07:34:33'),
(181, 'App\\Models\\Admin', 3, 'auth_token', '995c931f7a3738c1274d40d25054c2ed4cee38b8030cdaa203025fdb2afa97dd', '[\"role:admin_pengelola\"]', '2025-05-28 23:39:16', NULL, '2025-05-28 20:29:34', '2025-05-28 23:39:16'),
(182, 'App\\Models\\Admin', 3, 'auth_token', 'db22adc2e634c1b2db7902cf6aac6b309f4dff97d36201100a30c88a791a384c', '[\"role:admin_pengelola\"]', '2025-05-28 21:04:21', NULL, '2025-05-28 20:56:46', '2025-05-28 21:04:21'),
(183, 'App\\Models\\Admin', 3, 'auth_token', '758462729fdccd1c01c52df800ca1dd329148f2ea63111d0505591b92af6fbf5', '[\"role:admin_pengelola\"]', '2025-05-29 04:26:07', NULL, '2025-05-29 01:51:56', '2025-05-29 04:26:07'),
(184, 'App\\Models\\Admin', 3, 'auth_token', '143ab45e4026b9f4df173cbb085581e8cfb599824aa74ea611091b5cc2abd075', '[\"role:admin_pengelola\"]', '2025-05-29 03:01:55', NULL, '2025-05-29 02:17:45', '2025-05-29 03:01:55'),
(185, 'App\\Models\\Admin', 3, 'auth_token', 'bd4078609b524da1699fa67a5be935812b57224d55bbc2e4e5fe0f3386d40408', '[\"role:admin_pengelola\"]', '2025-05-29 08:03:00', NULL, '2025-05-29 06:37:32', '2025-05-29 08:03:00'),
(186, 'App\\Models\\Admin', 3, 'auth_token', '01e48991adaa087f68834fb1929553169d0009a51f276342ce3d606eb3e7e32d', '[\"role:admin_pengelola\"]', '2025-05-30 00:52:12', NULL, '2025-05-29 22:59:44', '2025-05-30 00:52:12'),
(187, 'App\\Models\\Admin', 3, 'auth_token', '6c0ba481aef5cd82878291490bbbb950b5ef822171c054e82eeb02d9fff5e89b', '[\"role:admin_pengelola\"]', '2025-05-31 01:16:05', NULL, '2025-05-30 23:13:17', '2025-05-31 01:16:05'),
(188, 'App\\Models\\Admin', 3, 'auth_token', '5a40eeb336ef52b749800ff11e5615a5e665e7ed296ec0dc903b630d22b0a4e5', '[\"role:admin_pengelola\"]', '2025-05-31 00:46:51', NULL, '2025-05-31 00:11:06', '2025-05-31 00:46:51'),
(189, 'App\\Models\\Admin', 3, 'auth_token', '3a95b3b61720ac1c6e80afe2bc2e115744ae1f426d8a796227eb1d9ee8a26915', '[\"role:admin_pengelola\"]', '2025-06-01 02:16:09', NULL, '2025-05-31 23:52:14', '2025-06-01 02:16:09'),
(190, 'App\\Models\\Admin', 3, 'auth_token', '768df86c94bc0b22ede6a4f849e4a0953c8f2186b9f7682c5123fa04d04ba146', '[\"role:admin_pengelola\"]', NULL, NULL, '2025-06-01 01:12:34', '2025-06-01 01:12:34'),
(191, 'App\\Models\\Admin', 3, 'auth_token', 'e56e32c16acb90eb7eef90ef8141f93fa5ce1758f27b6c077a1545a473865c99', '[\"role:admin_pengelola\"]', '2025-06-02 04:52:01', NULL, '2025-06-02 04:37:57', '2025-06-02 04:52:01'),
(192, 'App\\Models\\Admin', 3, 'auth_token', 'db05788f97b006250fe246ed56875d55f1683d7ac9dbf12e86dd61f16bcef2c1', '[\"role:admin_pengelola\"]', '2025-06-02 04:58:29', NULL, '2025-06-02 04:46:22', '2025-06-02 04:58:29'),
(193, 'App\\Models\\Admin', 3, 'auth_token', '5b37658bcdbe3205e31a710269c39e8f697cf1617d37b29e1d5e81c599fa6d23', '[\"role:admin_pengelola\"]', '2025-06-02 05:57:15', NULL, '2025-06-02 05:52:33', '2025-06-02 05:57:15'),
(194, 'App\\Models\\Admin', 3, 'auth_token', '3d616914958b5f87793d9d9513df5f19efc6214d66d6fd5d2d6909ec4a38f7a0', '[\"role:admin_pengelola\"]', '2025-06-02 08:29:13', NULL, '2025-06-02 08:28:50', '2025-06-02 08:29:13'),
(196, 'App\\Models\\Admin', 3, 'auth_token', '773355312d927a009bf01b5e129ff720a612ac87f47a427162a8f5dc9bd0c612', '[\"role:admin_pengelola\"]', '2025-06-03 01:10:13', NULL, '2025-06-03 00:34:54', '2025-06-03 01:10:13'),
(197, 'App\\Models\\Admin', 3, 'auth_token', 'b2cba61b33d00ece47537b796cfaea8f48f5b73ab60521f138dbceeb620ee360', '[\"role:admin_pengelola\"]', '2025-06-07 06:01:25', NULL, '2025-06-07 02:01:51', '2025-06-07 06:01:25'),
(198, 'App\\Models\\Admin', 3, 'auth_token', '56827a18507e3d911dc784eb56de048fc2a2ee31bf1408c893a39ecaae3c311e', '[\"role:admin_pengelola\"]', '2025-06-07 03:38:35', NULL, '2025-06-07 03:25:32', '2025-06-07 03:38:35'),
(199, 'App\\Models\\Admin', 3, 'auth_token', '0da07879b77186a24e131a903db631d59fd9598b9acafaae596d9b34dd809bf7', '[\"role:admin_pengelola\"]', '2025-06-07 07:00:23', NULL, '2025-06-07 06:02:16', '2025-06-07 07:00:23'),
(200, 'App\\Models\\Admin', 3, 'auth_token', '705504d4d8d001f6e5a80128b64ce0c98856130e47bf67f4cf7df7609a86ebbe', '[\"role:admin_pengelola\"]', '2025-06-08 20:41:53', NULL, '2025-06-08 19:55:02', '2025-06-08 20:41:53'),
(201, 'App\\Models\\Admin', 3, 'auth_token', 'fc0fddea66dd305c770b2195906854ad64f5fa2a16abd9f66797023ffa47501e', '[\"role:admin_pengelola\"]', '2025-06-08 20:10:50', NULL, '2025-06-08 20:08:00', '2025-06-08 20:10:50'),
(202, 'App\\Models\\Admin', 3, 'auth_token', '1ba97646f17bf5c0d7a33c94b30c6b6d1f1775583587a0dc2f2e28934890d8ff', '[\"role:admin_pengelola\"]', '2025-06-10 00:53:47', NULL, '2025-06-10 00:36:04', '2025-06-10 00:53:47'),
(203, 'App\\Models\\Admin', 3, 'auth_token', 'b8054db2530f41a2c4732af047cd1b0ebb212f31e1f3df3d4c44550a9029c499', '[\"role:admin_pengelola\"]', '2025-06-12 02:49:38', NULL, '2025-06-11 23:50:06', '2025-06-12 02:49:38'),
(204, 'App\\Models\\Admin', 3, 'auth_token', 'a5e2ce9664846cb85384fdfe5d351438a533dca42e8f2d9386c6d965c626a133', '[\"role:admin_pengelola\"]', '2025-06-12 03:10:14', NULL, '2025-06-11 23:58:32', '2025-06-12 03:10:14'),
(205, 'App\\Models\\Admin', 3, 'auth_token', '360400995446402741090dfec0372929b67d0a5f92d87a2c2ec0060fd2cff661', '[\"role:admin_pengelola\"]', '2025-06-13 00:24:20', NULL, '2025-06-12 23:21:10', '2025-06-13 00:24:20'),
(206, 'App\\Models\\Admin', 3, 'auth_token', '47087ae9a9aa4828eff65a0d73e4ed8c31cedabd25c757ce9aff8ae94cf071af', '[\"role:admin_pengelola\"]', '2025-06-14 22:55:27', NULL, '2025-06-14 22:54:30', '2025-06-14 22:55:27'),
(207, 'App\\Models\\Admin', 3, 'auth_token', 'bc625f9114d457080aba7f5579d3f489149b8cadb4a082694e67cbe860d197c6', '[\"role:admin_pengelola\"]', '2025-06-15 03:03:20', NULL, '2025-06-14 23:29:28', '2025-06-15 03:03:20'),
(208, 'App\\Models\\User', 2, 'auth_token', 'caf8e052e924f47ae0bd3f69325a4abd2d81784bfcc0055532adb564676be268', '[\"*\"]', '2025-06-15 03:03:49', NULL, '2025-06-15 02:23:44', '2025-06-15 03:03:49'),
(209, 'App\\Models\\User', 2, 'auth_token', '5d0c548cc66015a1ff7ca35812677dbea7a60462c71460dbf04fa8d03caa2119', '[\"*\"]', '2025-06-15 02:59:32', NULL, '2025-06-15 02:30:17', '2025-06-15 02:59:32'),
(210, 'App\\Models\\User', 2, 'auth_token', '6bae6a3f3df6feb585e7218a9d6d69d568a05acc7c24cf1bd60f3306b88a2ab2', '[\"*\"]', '2025-06-15 23:30:24', NULL, '2025-06-15 23:27:48', '2025-06-15 23:30:24'),
(212, 'App\\Models\\Admin', 3, 'auth_token', 'd0afb20cee11616e6a4c65159b67e05adcbb08327336417d16575440a01a5a5e', '[\"role:admin_pengelola\"]', '2025-06-16 01:07:54', NULL, '2025-06-16 01:07:27', '2025-06-16 01:07:54'),
(213, 'App\\Models\\Admin', 3, 'auth_token', 'b7f693444097c27bb676cb72d5a0065ab2510944448e74614c2406407bdf5922', '[\"role:admin_pengelola\"]', '2025-06-18 01:32:07', NULL, '2025-06-18 01:32:07', '2025-06-18 01:32:07'),
(218, 'App\\Models\\Admin', 3, 'auth_token', '67bfa0761be16d1972864bccf8c30fb115c3b8cb6f77951d8e540e48a1bef45c', '[\"role:admin_pengelola\"]', '2025-06-19 00:59:20', NULL, '2025-06-19 00:55:56', '2025-06-19 00:59:20'),
(219, 'App\\Models\\User', 2, 'auth_token', 'a26a302605e49c96c6d90afc42b9316f41538ca2d3752a3a49006f08520c2e33', '[\"*\"]', '2025-06-23 00:10:18', NULL, '2025-06-22 23:12:33', '2025-06-23 00:10:18'),
(220, 'App\\Models\\Admin', 3, 'auth_token', 'a72673a5ca84658008a8592ac71b3784ba658aeec850e715dd8a3e88a1c69eff', '[\"role:admin_pengelola\"]', '2025-06-23 00:10:18', NULL, '2025-06-22 23:28:38', '2025-06-23 00:10:18'),
(223, 'App\\Models\\Dosen', 2, 'auth_token', '9c4bd31995f0458ab053c24028d8cea2b8c71cf0a6710c311e4b834bfa532f60', '[\"*\"]', '2025-06-24 03:42:49', NULL, '2025-06-24 03:39:10', '2025-06-24 03:42:49'),
(224, 'App\\Models\\Admin', 3, 'auth_token', 'cac7c3ee12ab43454d2ab20d4bff86a092bebf89ff65d40eb630909783e9b087', '[\"role:admin_pengelola\"]', '2025-06-25 02:46:20', NULL, '2025-06-25 02:45:24', '2025-06-25 02:46:20'),
(225, 'App\\Models\\Admin', 3, 'auth_token', 'eaeb3d47d88b02ab1fa11200e78c91cd9454d43da9d176f3568c0830d217396c', '[\"role:admin_pengelola\"]', '2025-06-25 23:13:56', NULL, '2025-06-25 23:13:32', '2025-06-25 23:13:56'),
(227, 'App\\Models\\Admin', 1, 'auth_token', '949e770c9db12016b7bcf7eab09a09e12532bb05d3e41853ef655a6ca5b806b4', '[\"role:superadmin\"]', '2025-06-26 00:12:15', NULL, '2025-06-25 23:32:55', '2025-06-26 00:12:15'),
(228, 'App\\Models\\Admin', 3, 'auth_token', '2737a716af7b90c71f1ff505f7fc4de6286a643cce9b80b11083d92951534701', '[\"role:admin_pengelola\"]', '2025-06-25 23:49:49', NULL, '2025-06-25 23:39:46', '2025-06-25 23:49:49'),
(229, 'App\\Models\\Admin', 1, 'auth_token', 'b70eddc962354625c5598d30e0694b43bc312508ccee56b04eb28112a58b071c', '[\"role:superadmin\"]', '2025-06-25 23:49:35', NULL, '2025-06-25 23:40:20', '2025-06-25 23:49:35'),
(230, 'App\\Models\\Admin', 2, 'auth_token', '16d04e6abbd216e20869e319207f093b3ce4afd071bcc414c80963c9d8d62b88', '[\"role:admin_ta\"]', '2025-06-25 23:50:22', NULL, '2025-06-25 23:50:04', '2025-06-25 23:50:22'),
(231, 'App\\Models\\User', 2, 'auth_token', '6f3e880883c674a7826ef459a0ca4aa31cab90a56b88ddc1356d67280644d5eb', '[\"*\"]', '2025-06-25 23:51:11', NULL, '2025-06-25 23:50:44', '2025-06-25 23:51:11'),
(232, 'App\\Models\\User', 2, 'auth_token', '70908706d785dad9b919e6107e96cf3e54a932ddca29523fd30895a274d17042', '[\"*\"]', '2025-06-28 01:34:11', NULL, '2025-06-28 01:34:03', '2025-06-28 01:34:11'),
(233, 'App\\Models\\Admin', 3, 'auth_token', '39e5d5d4ae1a0a0e94527f95fd0bd097a59794b41f8b7fcdfa9edc58248cf81b', '[\"role:admin_pengelola\"]', '2025-06-29 09:52:29', NULL, '2025-06-29 07:21:22', '2025-06-29 09:52:29'),
(234, 'App\\Models\\Admin', 3, 'auth_token', 'f72f39a394e08368f46710c69c944b806be1e0f7eb682fe139a15182d42d4782', '[\"role:admin_pengelola\"]', '2025-06-29 09:18:43', NULL, '2025-06-29 07:56:48', '2025-06-29 09:18:43'),
(235, 'App\\Models\\Admin', 3, 'auth_token', '2b7bf383adab6c4f7eaa331bd3190e3a7f2c99f76f4ca7a5e0248031b5ddfaa2', '[\"role:admin_pengelola\"]', '2025-06-29 09:19:15', NULL, '2025-06-29 09:19:08', '2025-06-29 09:19:15'),
(236, 'App\\Models\\Admin', 3, 'auth_token', '8d2d76552d236bb1b3efe0b2401c7a09ec2f7a94d1233650f7344f4e3b85b14d', '[\"role:admin_pengelola\"]', '2025-06-29 09:49:36', NULL, '2025-06-29 09:20:10', '2025-06-29 09:49:36'),
(237, 'App\\Models\\Admin', 3, 'auth_token', 'c6566b02f0a06e0864e42cf5e79dd288bab0065117109175030ec4f1681a56d7', '[\"role:admin_pengelola\"]', '2025-07-01 04:40:53', NULL, '2025-07-01 04:26:09', '2025-07-01 04:40:53'),
(238, 'App\\Models\\Admin', 3, 'auth_token', 'd41c66b2965cb988677ef2b904f1effd6a0f12f51449be3f1bba5f23db8c9727', '[\"role:admin_pengelola\"]', '2025-07-02 01:07:04', NULL, '2025-07-02 00:24:54', '2025-07-02 01:07:04'),
(239, 'App\\Models\\User', 2, 'auth_token', '1e5c9793c8b079be78e5cd5998c0fda891604e0409e53466f302f3ba9ec9d692', '[\"*\"]', '2025-07-02 01:07:04', NULL, '2025-07-02 00:34:10', '2025-07-02 01:07:04'),
(240, 'App\\Models\\Admin', 3, 'auth_token', '67e1b737f8ef01d44543152f80c6b16e029b8f5b1ce1e752d3be7d7600b39533', '[\"role:admin_pengelola\"]', '2025-07-06 04:27:49', NULL, '2025-07-06 02:34:13', '2025-07-06 04:27:49'),
(241, 'App\\Models\\Admin', 3, 'auth_token', 'db41a5b82bd33aa5c7fd94c4a3a07cd564bb939402d0222730d53e88592c5306', '[\"role:admin_pengelola\"]', '2025-07-06 22:23:51', NULL, '2025-07-06 20:54:59', '2025-07-06 22:23:51'),
(242, 'App\\Models\\User', 2, 'auth_token', 'bb4045c913132304d0266c4802431aa7d076172cbbb4b58b3c5450209285f9a6', '[\"*\"]', '2025-07-07 21:03:47', NULL, '2025-07-07 21:01:58', '2025-07-07 21:03:47'),
(243, 'App\\Models\\Admin', 3, 'auth_token', 'a45cf05fa9d74bbdb3e1a3605fc65933d15b77fe4dd67da4f67e46c245a15da4', '[\"role:admin_pengelola\"]', '2025-07-07 23:13:47', NULL, '2025-07-07 21:04:55', '2025-07-07 23:13:47'),
(244, 'App\\Models\\Admin', 3, 'auth_token', 'eb63e27268ae19a47a3db6ca1d1fecb6e2d09bf1209bfcb89a534b10751d0096', '[\"role:admin_pengelola\"]', '2025-07-08 21:25:54', NULL, '2025-07-08 20:15:25', '2025-07-08 21:25:54'),
(245, 'App\\Models\\User', 2, 'auth_token', '591be54b9d6fccd972733ede8f9ac1e45fb196b37e6dfe680f7f0881ffd9478b', '[\"*\"]', '2025-07-08 20:19:20', NULL, '2025-07-08 20:19:08', '2025-07-08 20:19:20'),
(246, 'App\\Models\\Admin', 3, 'auth_token', 'b3e758e34bb489f8c316f37579763880a3abe67f448e995bc784dd7f3dd387d0', '[\"role:admin_pengelola\"]', '2025-07-09 07:03:36', NULL, '2025-07-09 04:32:47', '2025-07-09 07:03:36'),
(247, 'App\\Models\\User', 2, 'auth_token', 'fa8c034fae5303d5e26e1723e29e94e3b8b06ec9483fdcb432e00fedee6d99fe', '[\"*\"]', '2025-07-09 07:11:03', NULL, '2025-07-09 06:57:02', '2025-07-09 07:11:03'),
(252, 'App\\Models\\User', 2, 'auth_token', '03bf1ac38af6d3f0ccec7289d70506b05ae270b1aa1f7c6867a2ed5b7ac1ae95', '[\"*\"]', '2025-07-11 06:04:18', NULL, '2025-07-11 06:04:12', '2025-07-11 06:04:18'),
(253, 'App\\Models\\Admin', 1, 'auth_token', '13e6c2fdc4aeeefb127986eb81021e7738ffc0500c33de00ff832d266492d69e', '[\"role:superadmin\"]', '2025-07-11 07:20:48', NULL, '2025-07-11 06:21:04', '2025-07-11 07:20:48'),
(255, 'App\\Models\\Admin', 1, 'auth_token', '3a905c010f7c5e87abaea4a848bb6ea55e8d1e183aeb582743fa78377647e99f', '[\"role:superadmin\"]', '2025-07-12 09:14:54', NULL, '2025-07-12 08:38:37', '2025-07-12 09:14:54'),
(256, 'App\\Models\\Admin', 1, 'auth_token', '80cfa6b8c741172e4a91613458e38c8cdebc23c0d4e5683c67b880da1c36e099', '[\"role:superadmin\"]', '2025-07-12 14:43:57', NULL, '2025-07-12 14:22:20', '2025-07-12 14:43:57'),
(257, 'App\\Models\\Admin', 1, 'auth_token', 'ffeac3b3379645807eda4190d0f25e567cdc24a23540bfa70834fb750acb6568', '[\"role:superadmin\"]', '2025-07-12 15:09:10', NULL, '2025-07-12 14:44:37', '2025-07-12 15:09:10'),
(258, 'App\\Models\\Admin', 3, 'auth_token', 'fd961ea29fa71b7677b877e330ac809291a99b455bff796622fdb0982e68cb15', '[\"role:admin_pengelola\"]', '2025-07-12 15:03:34', NULL, '2025-07-12 15:02:42', '2025-07-12 15:03:34'),
(259, 'App\\Models\\Admin', 1, 'auth_token', 'b78678026add6c5f3be7aa6b522b4cf92333cb4c1c469c4623a3b39562a7e104', '[\"role:superadmin\"]', '2025-07-13 07:10:59', NULL, '2025-07-13 06:47:28', '2025-07-13 07:10:59'),
(260, 'App\\Models\\Admin', 1, 'auth_token', '9089bbea834f4e3247c525eecb8d877d983b02cd6197fd922226a8277d2981ab', '[\"role:superadmin\"]', '2025-07-13 07:26:35', NULL, '2025-07-13 06:57:35', '2025-07-13 07:26:35'),
(261, 'App\\Models\\Admin', 1, 'auth_token', '6c131bee0732746cef29dc51f5851f93cbed1906ef06bda7f579e9d7ac302265', '[\"role:superadmin\"]', '2025-07-13 07:11:19', NULL, '2025-07-13 07:11:12', '2025-07-13 07:11:19'),
(263, 'App\\Models\\Admin', 1, 'auth_token', '71472c2eadbc23e84d7a26f7e82d14013e38131e01ac96e272bb1aa446095322', '[\"role:superadmin\"]', '2025-07-13 10:11:49', NULL, '2025-07-13 10:10:49', '2025-07-13 10:11:49'),
(264, 'App\\Models\\User', 2, 'auth_token', 'a8778dad450e1e0f72c9745f1ad19680ad36a57c6f337b41cda93bfd849c5fa7', '[\"*\"]', '2025-07-13 10:11:20', NULL, '2025-07-13 10:11:08', '2025-07-13 10:11:20'),
(265, 'App\\Models\\Admin', 1, 'auth_token', '15d4d085b9b296c136b125710cb7b32789c4fcaef647cb3ac1efd20ccb1b292c', '[\"role:superadmin\"]', '2025-07-14 04:53:42', NULL, '2025-07-14 02:04:17', '2025-07-14 04:53:42');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(266, 'App\\Models\\Admin', 1, 'auth_token', '166211abfb0196994aeebbb8ea0850d0d170b7199eb08b867c7ae862862d8ca7', '[\"role:superadmin\"]', '2025-07-15 06:28:46', NULL, '2025-07-15 05:17:47', '2025-07-15 06:28:46'),
(267, 'App\\Models\\User', 2, 'auth_token', 'f0f71397803c6ca1cc14015d36167f77b56300970080a2fdba636cb2f8d943d0', '[\"*\"]', NULL, NULL, '2025-07-15 06:15:51', '2025-07-15 06:15:51'),
(269, 'App\\Models\\Admin', 1, 'auth_token', 'f0ca09ef4d09fad3dfd98b1de7c859eba76f87233810bf520498454c7c253cec', '[\"role:superadmin\"]', '2025-07-17 07:03:45', NULL, '2025-07-17 04:13:23', '2025-07-17 07:03:45'),
(270, 'App\\Models\\Admin', 3, 'auth_token', '46ea9d9b596960defe507241d6d1fcf378e655f449d5874bafa310dedee0a013', '[\"role:admin_pengelola\"]', '2025-07-21 10:05:37', NULL, '2025-07-21 07:56:11', '2025-07-21 10:05:37'),
(271, 'App\\Models\\Admin', 3, 'auth_token', '31e1748a52819da5dbe76e6f6e7b7ec0862bfd3890cbf350fa476a2cefee1c87', '[\"role:admin_pengelola\"]', '2025-07-23 03:08:58', NULL, '2025-07-23 02:51:38', '2025-07-23 03:08:58'),
(274, 'App\\Models\\Admin', 3, 'auth_token', '1305c2fcb919abe0ec17e4458d1995a570eb116bbd68b46e74906f5956011121', '[\"role:admin_pengelola\"]', '2025-07-24 02:49:50', NULL, '2025-07-24 02:29:53', '2025-07-24 02:49:50');

-- --------------------------------------------------------

--
-- Table structure for table `ruangs`
--

CREATE TABLE `ruangs` (
  `id_ruang` bigint UNSIGNED NOT NULL,
  `nama_ruang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` int NOT NULL,
  `fasilitas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dimensi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_gedung` bigint UNSIGNED NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ruangs`
--

INSERT INTO `ruangs` (`id_ruang`, `nama_ruang`, `kapasitas`, `fasilitas`, `dimensi`, `id_gedung`, `status`, `created_at`, `updated_at`) VALUES
(22, 'TEFA', 35, 'Wifi, Monitor, Papan Tulis, Proyektor', '1 x 2', 5, 1, '2025-03-03 21:26:52', '2025-04-17 08:08:31'),
(23, 'Lab Jaringan', 30, 'AC, Papan Tulis', '2x2', 8, 0, '2025-03-22 00:46:22', '2025-04-17 08:08:08'),
(24, 'Lab Pemrograman', 35, 'AC, Proyektor, Papan Tulis, PC', '3x3', 8, 1, '2025-03-24 03:19:19', '2025-04-17 08:07:42'),
(25, 'Mixed Reality', 35, 'AC, Monitor, PC, LCD', '2x2', 8, 1, '2025-04-01 10:31:36', '2025-04-17 08:07:08');

-- --------------------------------------------------------

--
-- Table structure for table `tipe`
--

CREATE TABLE `tipe` (
  `id_tipe` bigint UNSIGNED NOT NULL,
  `nama_tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tipe`
--

INSERT INTO `tipe` (`id_tipe`, `nama_tipe`, `created_at`, `updated_at`) VALUES
(1, 'Keyboard', '2025-03-04', '2025-04-17'),
(3, 'Mouse', '2025-03-19', '2025-04-17'),
(7, 'Monitor', '2025-04-17', '2025-04-17'),
(8, 'Laptop', '2025-04-17', '2025-04-17'),
(9, 'Printer', '2025-04-17', '2025-04-17'),
(10, 'Kursi', '2025-04-17', '2025-04-17'),
(11, 'Meja', '2025-04-17', '2025-04-17'),
(12, 'LCD 01', '2025-04-22', '2025-04-22'),
(14, 'Mouse 2', '2025-06-03', '2025-06-03'),
(16, 'HP', '2025-07-24', '2025-07-24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_email_unique` (`email`);

--
-- Indexes for table `bhps`
--
ALTER TABLE `bhps`
  ADD PRIMARY KEY (`id_bhp`);

--
-- Indexes for table `detail_peminjaman_inventaris`
--
ALTER TABLE `detail_peminjaman_inventaris`
  ADD PRIMARY KEY (`id_detail_peminjaman`),
  ADD KEY `id_peminjaman_inventaris` (`id_peminjaman_inventaris`),
  ADD KEY `id_inventaris` (`id_inventaris`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dosen_email_unique` (`email`);

--
-- Indexes for table `foto`
--
ALTER TABLE `foto`
  ADD PRIMARY KEY (`foto_id`),
  ADD KEY `id_jenis_inventaris` (`id_jenis_inventaris`),
  ADD KEY `id_ruang` (`id_ruang`),
  ADD KEY `foto_ibfk_2` (`id_bhp`);

--
-- Indexes for table `gedung`
--
ALTER TABLE `gedung`
  ADD PRIMARY KEY (`id_gedung`),
  ADD KEY `gedung_ibfk_1` (`id_lantai`);

--
-- Indexes for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id_inventaris`),
  ADD UNIQUE KEY `no_inventaris` (`no_inventaris`),
  ADD KEY `id_jenis_inventaris` (`id_jenis_inventaris`),
  ADD KEY `id_ruang` (`id_ruang`);

--
-- Indexes for table `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jabatan_dosen`
--
ALTER TABLE `jabatan_dosen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jabatan_dosen_dosen_id_foreign` (`dosen_id`),
  ADD KEY `jabatan_dosen_jabatan_id_foreign` (`jabatan_id`);

--
-- Indexes for table `jenis_inventaris`
--
ALTER TABLE `jenis_inventaris`
  ADD PRIMARY KEY (`id_jenis_inventaris`),
  ADD KEY `id_merk` (`id_merk`),
  ADD KEY `id_tipe` (`id_tipe`);

--
-- Indexes for table `lantai_gedung`
--
ALTER TABLE `lantai_gedung`
  ADD PRIMARY KEY (`id_lantai`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mahasiswa_nim_unique` (`nim`),
  ADD UNIQUE KEY `mahasiswa_email_unique` (`email`);

--
-- Indexes for table `merk`
--
ALTER TABLE `merk`
  ADD PRIMARY KEY (`id_merk`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `notifications_id_peminjam_foreign` (`id_peminjam`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `peminjaman_bhps`
--
ALTER TABLE `peminjaman_bhps`
  ADD PRIMARY KEY (`id_peminjaman_bhp`),
  ADD KEY `id_bhp` (`id_bhp`);

--
-- Indexes for table `peminjaman_inventaris`
--
ALTER TABLE `peminjaman_inventaris`
  ADD PRIMARY KEY (`id_peminjaman_inventaris`);

--
-- Indexes for table `peminjaman_ruangs`
--
ALTER TABLE `peminjaman_ruangs`
  ADD PRIMARY KEY (`id_peminjaman_ruang`),
  ADD UNIQUE KEY `no_reservasi` (`no_reservasi`),
  ADD KEY `id_jenis_acara` (`id_jenis_acara`),
  ADD KEY `id_periode` (`id_periode`),
  ADD KEY `id_ruang` (`id_ruang`),
  ADD KEY `fk_sesi_mulai` (`id_sesi_mulai`),
  ADD KEY `fk_sesi_selesai` (`id_sesi_selesai`),
  ADD KEY `id_peminjam` (`id_peminjam`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `ruangs`
--
ALTER TABLE `ruangs`
  ADD PRIMARY KEY (`id_ruang`),
  ADD KEY `id_gedung` (`id_gedung`);

--
-- Indexes for table `tipe`
--
ALTER TABLE `tipe`
  ADD PRIMARY KEY (`id_tipe`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bhps`
--
ALTER TABLE `bhps`
  MODIFY `id_bhp` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `detail_peminjaman_inventaris`
--
ALTER TABLE `detail_peminjaman_inventaris`
  MODIFY `id_detail_peminjaman` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `foto`
--
ALTER TABLE `foto`
  MODIFY `foto_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `gedung`
--
ALTER TABLE `gedung`
  MODIFY `id_gedung` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id_inventaris` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `jabatan`
--
ALTER TABLE `jabatan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jabatan_dosen`
--
ALTER TABLE `jabatan_dosen`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jenis_inventaris`
--
ALTER TABLE `jenis_inventaris`
  MODIFY `id_jenis_inventaris` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `lantai_gedung`
--
ALTER TABLE `lantai_gedung`
  MODIFY `id_lantai` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `merk`
--
ALTER TABLE `merk`
  MODIFY `id_merk` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id_notification` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `peminjaman_bhps`
--
ALTER TABLE `peminjaman_bhps`
  MODIFY `id_peminjaman_bhp` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `peminjaman_inventaris`
--
ALTER TABLE `peminjaman_inventaris`
  MODIFY `id_peminjaman_inventaris` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `peminjaman_ruangs`
--
ALTER TABLE `peminjaman_ruangs`
  MODIFY `id_peminjaman_ruang` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=275;

--
-- AUTO_INCREMENT for table `ruangs`
--
ALTER TABLE `ruangs`
  MODIFY `id_ruang` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tipe`
--
ALTER TABLE `tipe`
  MODIFY `id_tipe` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_peminjaman_inventaris`
--
ALTER TABLE `detail_peminjaman_inventaris`
  ADD CONSTRAINT `detail_peminjaman_inventaris_ibfk_1` FOREIGN KEY (`id_peminjaman_inventaris`) REFERENCES `peminjaman_inventaris` (`id_peminjaman_inventaris`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_peminjaman_inventaris_ibfk_2` FOREIGN KEY (`id_inventaris`) REFERENCES `inventaris` (`id_inventaris`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `foto`
--
ALTER TABLE `foto`
  ADD CONSTRAINT `foto_ibfk_1` FOREIGN KEY (`id_bhp`) REFERENCES `bhps` (`id_bhp`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `foto_ibfk_2` FOREIGN KEY (`id_jenis_inventaris`) REFERENCES `jenis_inventaris` (`id_jenis_inventaris`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `foto_ibfk_3` FOREIGN KEY (`id_ruang`) REFERENCES `ruangs` (`id_ruang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gedung`
--
ALTER TABLE `gedung`
  ADD CONSTRAINT `gedung_ibfk_1` FOREIGN KEY (`id_lantai`) REFERENCES `lantai_gedung` (`id_lantai`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD CONSTRAINT `inventaris_ibfk_1` FOREIGN KEY (`id_jenis_inventaris`) REFERENCES `jenis_inventaris` (`id_jenis_inventaris`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `inventaris_ibfk_2` FOREIGN KEY (`id_ruang`) REFERENCES `ruangs` (`id_ruang`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `jabatan_dosen`
--
ALTER TABLE `jabatan_dosen`
  ADD CONSTRAINT `jabatan_dosen_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`),
  ADD CONSTRAINT `jabatan_dosen_jabatan_id_foreign` FOREIGN KEY (`jabatan_id`) REFERENCES `jabatan` (`id`);

--
-- Constraints for table `jenis_inventaris`
--
ALTER TABLE `jenis_inventaris`
  ADD CONSTRAINT `jenis_inventaris_ibfk_1` FOREIGN KEY (`id_merk`) REFERENCES `merk` (`id_merk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jenis_inventaris_ibfk_2` FOREIGN KEY (`id_tipe`) REFERENCES `tipe` (`id_tipe`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `peminjaman_bhps`
--
ALTER TABLE `peminjaman_bhps`
  ADD CONSTRAINT `peminjaman_bhps_ibfk_1` FOREIGN KEY (`id_bhp`) REFERENCES `bhps` (`id_bhp`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ruangs`
--
ALTER TABLE `ruangs`
  ADD CONSTRAINT `ruangs_ibfk_1` FOREIGN KEY (`id_gedung`) REFERENCES `gedung` (`id_gedung`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
