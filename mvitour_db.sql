-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2025 at 12:55 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mvitour_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `password`) VALUES
(1, 'admin1', 'admin1@gmail.com', '$2y$10$hJCExAvQPAMD4ZvJ8wzN9Ol/54WqdANQ0sShX.NlwYMwfJXL8UFiG'),
(2, 'admin2', 'admin2@gmail.com', '$2y$10$SmkMme7mcBgw3r62tgMc/uCbMQ.8KmvaBYev0i8qtMcULAmFSZ4Di'),
(4, 'admin 3', 'admin3@gmail.com', '$2y$10$02cUvdAlAqiAx8IO2GelvuRAdcIAV/1.B2NLvgCMIkteECljm4kJG');

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `business_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `destination_thumbnail` varchar(255) DEFAULT NULL,
  `images` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `is_open` tinyint(1) DEFAULT 1,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`business_id`, `name`, `destination_thumbnail`, `images`, `description`, `location`, `barangay`, `category`, `budget`, `latitude`, `longitude`, `is_open`, `opening_time`, `closing_time`, `status`) VALUES
(2, 'Paracad View', '1731919574-317796844_512675677548763_7494077781320984853_n.jpg', '1732625029-2021-06-19.jpg,1732625029-2023-01-10.jpg', 'Amazing restaurant located in Caoayan, Ilocos Sur', 'Caoayan', 'Pantay Tamurong', 'Restaurant', 100.00, 17.531211, 120.397114, 1, NULL, NULL, 'active'),
(3, 'McDo', '1732020559-beyond-2560x1440.jpg', '6730b78e15aa0_IMG_20221107_114711.jpg,6730b78e15c13_wallpaperflare.com_wallpaper (1).jpg', 'Yummy Burger', 'Vigan', 'Barangay I (Poblacion)', 'Restaurant', 50.00, 17.574657, 120.388088, 1, NULL, NULL, 'archived'),
(4, 'One Vittoria Hotel Inc.', '1731966235-one-vittoria-hotel.jpg', '1732625268-SUPERIOR DELUXE_final.jpg,1732625268-2019-10-21.jpg', 'One Vittoria Hotel, a luxurious located in the heart of Ilocos Sur, Philippines.', 'Bantay', 'Cabalanggan', 'Hotel', 1000.00, 17.589480, 120.418173, 1, NULL, NULL, 'active'),
(5, 'Hidden Garden', '1731966281-Hidden Garden of Vigan Ilocos Sur.jpg', '1732625347-2024-09-20.jpg,1732625347-2023-05-01.jpg', 'No one would anticipate that a beautiful garden is situated at the heart of Vigan where tourist keep on discovering and kept amazed. It is more than a beautiful garden and superb potteries, HIDDEN GARDEN, owned by Mr. & Mrs Francis Flores, is a place where Filipino hospitality is felt. The garden has a restaurant that serves the famous Vigan empanada and other local delicacies and Filipino food.', 'Vigan', 'Salindeg', 'Restaurant', 150.00, 17.560357, 120.365260, 1, NULL, NULL, 'active'),
(6, 'Café Leona', '1731966944-Untitled-design-2021-05-03T182014.454.jpg', '6736d2c1dd58a_Untitled-design-2021-05-03T181907.501.jpg,6736d2c1dd679_images (3).jpg,6736d2c1dd7fb_Untitled-design-2021-05-03T181023.790.jpg', 'Step into a world of warmth and flavor at Café Leona. Our quaint café offers a delightful escape from the hustle and bustle, inviting you to unwind and savor the moment. Indulge in our handcrafted beverages, from aromatic coffee to refreshing teas, and pair them with our delectable pastries and light bites. Whether you\'re seeking a quiet moment to yourself or a cozy spot to catch up with friends, Café Leona is your perfect haven.', 'Vigan', 'Barangay II (Poblacion)', 'Restaurant', 100.00, 17.573620, 120.389290, 1, NULL, NULL, 'active'),
(7, 'Pinakbet Farm', '1732625475-2022-08-15.jpg', '1732625502-DSC_1614.JPG,1732625502-2022-08-15 (1).jpg,1732625502-20240225_211133.jpg', 'This is Pinakbet Farm. For an authentic Filipino dining experience complementing the rural landscape and the fresh air, diners are required to eat with their bare hands.', 'Caoayan', 'Nansuagao', 'Restaurant', 175.00, 17.543696, 120.394997, 1, NULL, NULL, 'active'),
(8, 'test 3', '1731727135-thumbnail-breach-1920x1080.jpg', '1731758580-0-33d8d250703bf55a33a0e34caa99206c.jpg,1731758580-1-beyond-2560x1440.jpg,1731758580-2-breach-1920x1080.jpg,1731758580-3-pexels-veeterzy-39811.jpg,1731758580-4-the-storm-thefatrat-2560x1440 - 2.jpg', 'hesrthfdghsrth1235675680-=][;/./[.,>?|', 'Vigan', 'Ayusan Norte', 'Restaurant', 300.00, 17.582457, 120.397114, 1, NULL, NULL, 'archived'),
(9, 'test 4', '1731845320-the-storm-thefatrat-2560x1440.jpg', '1731845510-IMG_20221107_114711.jpg,1731845510-pexels-michał-osiński-3454270(2).jpg,1731845510-the-storm-thefatrat-2560x1440 - 2.jpg,1731845510-Wallpaper.png', 'sehsrthrth', 'Vigan', 'Cabaroan Laud', 'Resort', 1500.00, 17.582457, 120.397114, 1, NULL, NULL, 'archived'),
(10, 'test 5', '1731816303-thumbnail-wp10165109-genshin-impact-scenery-wallpapers.png', '1731816303-0-33d8d250703bf55a33a0e34caa99206c.jpg,1731816303-1-beyond-2560x1440.jpg,1731816303-2-breach-1920x1080.jpg,1731816303-3-erenyon-2560x1440.jpg,1731816303-4-IMG_20221107_114711.jpg', 'rhrhrt', 'San Vicente', 'Bayubay Norte', 'Restaurant', 54.00, 17.582457, 120.397114, 1, NULL, NULL, 'archived'),
(11, 'Irene\'s Empanada', '1732657049-thumbnail-2023-02-03.jpg', '1732657049-0-2024-03-16.jpg', 'A Taste of Vigan: The Vigan Empanada\'s golden crust, signature crunch and hearty filling that typically consists of minced longganisa, raw egg and shredded cabbages.', 'Vigan', 'Barangay I (Poblacion)', 'Restaurant', 100.00, 17.571390, 120.389574, 1, NULL, NULL, 'active'),
(12, 'Kusina Felicitas', '1732657377-thumbnail-2017-10-27.jpg', '1732657377-0-2024-11-05.jpg,1732657377-1-2024-03-15.jpg,1732657377-2-DSC_0181.JPG', 'The food is delicious and perfect for a family or friends\' night out. The ambience is also cozy and the staffs are very approachable.', 'Vigan', 'Barangay V (Poblacion)', 'Restaurant', 70.00, 17.572852, 120.390185, 1, NULL, NULL, 'active'),
(13, 'Lilong and Lilang Restaurant', '1732657580-thumbnail-one-long-table-for-one.jpg', '1732657580-0-lilong-and-lilang-coffee.jpg,1732657580-1-lilong-and-lilang-restaurant.jpg', 'Good sampling for Filipino, Ilokano and/or Vigan delicacies. The garden and the restaurant has a very relaxing atmosphere. Even the restroom here is a must try.', 'Vigan', 'Bulala', 'Restaurant', 100.00, 17.561068, 120.366004, 1, NULL, NULL, 'active'),
(14, 'Ciudad Fernandina Hotel Corporation', '1732658132-thumbnail-869010_16093011480047162063.jpg', '1732658132-0-34008902.jpg,1732658132-1-869010_16093011480047162058.jpg', 'Immerse in Vigan\'s rich history and cultural charm at Ciudad Fernandina Hotel. Stay in the heart of the city, near attractions, museums, and parks.', 'Vigan', 'Barangay I (Poblacion)', 'Hotel', 2000.00, 17.570988, 120.388127, 1, NULL, NULL, 'active'),
(15, 'Ergo Hotel', '1732658305-thumbnail-LOBBY (5).jpg', '1732658305-0-unnamed.jpg,1732658305-1-FAMILY ROOM (7).jpg', 'ERGO Hotel is your home in the historic city of Vigan. Here, you can enjoy convenience, comfort, security, safety, and parking -- for a very fair price.', 'Vigan', 'Barangay VIII (Poblacion)', 'Hotel', 2000.00, 17.569448, 120.383862, 1, NULL, NULL, 'active'),
(16, 'Hotel Felicidad', '1732658613-thumbnail-20240308_163904.jpg', '1732658613-0-Felicidad-foe-web.jpg,1732658613-1-IMG20171228083754.jpg', 'Hotel Felicidad is a top choice boutique hotel in Vigan. It is strategically located in the heart of the world heritage city of Vigan, Ilocos Sur.', 'Vigan', 'Barangay II (Poblacion)', 'Hotel', 2600.00, 17.574581, 120.389654, 1, NULL, NULL, 'active'),
(17, 'NSCC Plaza', '1732669255-thumbnail-54d17598b933c494535a83d241670be7.jpg', '1732669255-0-afc74df832663217b7a1bff6c2f781b6.jpg,1732669255-1-9634220820e18a8106eafc96ebbcb498.jpg', 'Get your trip off to a great start with a stay at this property, which offers free Wi-Fi in all rooms. Conveniently situated in the Vigan part of Ilocos Sur, this property puts you close to attractions and interesting dining options. Don\'t leave before paying a visit to the famous Mindoro Airport. Massage and outdoor pool are among the special facilities that will enhance your stay with on-site convenience.', 'Caoayan', 'Puro', 'Hotel', 1500.00, 17.546830, 120.382256, 1, NULL, NULL, 'active'),
(18, 'Restaurant Bantay', '1733660148-thumbnail-pexels-veeterzy-39811.jpg', '1733660148-0-wp2737780-nature-night-hd-wallpaper.jpg,1733660148-1-wp2737823-nature-night-hd-wallpaper.jpg,1733660148-2-wp9230740-genshin-impact-scenery-wallpapers(2).png', 'eghrshteyhjy', 'Bantay', 'Guimod', 'Restaurant', 200.00, 17.582457, 120.397114, 0, '07:00:00', '18:00:00', 'active'),
(19, 'Casa Hotel', '1736852808-wp10165109-genshin-impact-scenery-wallpapers.png', '1736852808-0-beyond-2560x1440.jpg,1736852808-1-pexels-michał-osiński-3454270(2).jpg,1736852808-2-pexels-valdemaras-d-1647962.jpg', 'No description.', 'Bantay', 'Naguiddayan', 'Transient House', 1800.00, 17.590565, 120.442113, 1, NULL, NULL, 'active'),
(20, 'Test 6', '1738372209-thumbnail-wp2737780-nature-night-hd-wallpaper.jpg', '1738372209-0-pexels-michał-osiński-3454270.jpg,1738372209-1-pexels-quang-nguyen-vinh-6346494.jpg,1738372209-2-pexels-valdemaras-d-1647962.jpg,1738372209-3-pexels-veeterzy-39811.jpg', 'asdfghty', 'San Vicente', 'Bantaoay', 'Inn', 3000.00, 17.582457, 120.397114, 0, '05:00:00', '00:00:00', 'archived');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `destination_type` varchar(20) NOT NULL,
  `username` varchar(100) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `user_id`, `destination_id`, `destination_type`, `username`, `comment_text`, `created_at`) VALUES
(1, 16, 2, 'tourist', 'John Bryan', 'This is a sample comment! 😍😊😁', '2025-01-16 08:24:38'),
(6, 13, 2, 'tourist', 'JB', 'Qwertyuiop', '2025-01-16 08:59:43'),
(7, 20, 1, 'tourist', 'user5', 'Sample comment!', '2025-01-19 00:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `featured_attractions`
--

CREATE TABLE `featured_attractions` (
  `attraction_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `destination_thumbnail` varchar(255) DEFAULT NULL,
  `images` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `featured_attractions`
--

INSERT INTO `featured_attractions` (`attraction_id`, `name`, `destination_thumbnail`, `images`, `description`, `location`, `category`, `latitude`, `longitude`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Seaside Beach', '1739002176-thumbnail-beyond-2560x1440.jpg', '1739002176-0-33d8d250703bf55a33a0e34caa99206c.jpg,1739002176-2-breach-1920x1080.jpg,1739002176-3-erenyon-2560x1440.jpg', 'san juan, ilocos sur beach', 'Solot Solot, San Juan', 'Natural Wonders', 17.752917, 120.430683, 'active', '2025-02-08 08:09:36', '2025-02-08 23:38:06'),
(2, 'Bell Tower', '1739058222-thumbnail-wp10165109-genshin-impact-scenery-wallpapers.png', '1739058222-0-wp2737780-nature-night-hd-wallpaper.jpg,1739058222-1-wp2737823-nature-night-hd-wallpaper.jpg,1739058222-2-wp9230740-genshin-impact-scenery-wallpapers(2).png', 'asd', 'Bantay', 'Heritage Sites', 17.582457, 120.397114, 'active', '2025-02-08 23:43:42', '2025-02-09 04:16:57'),
(3, 'featured 1', '1739100498-thumbnail-IMG_20221107_114711.jpg', '1739100498-0-pexels-michał-osiński-3454270.jpg,1739100498-1-wp2737780-nature-night-hd-wallpaper.jpg,1739100498-2-wp10165109-genshin-impact-scenery-wallpapers.png', 'wefew', 'San Ildefonso', 'Local Delicacies/Food Spots', 17.582457, 120.397114, 'active', '2025-02-09 11:28:18', '2025-02-09 11:28:18'),
(4, 'featured 2', '1739100543-thumbnail-pexels-valdemaras-d-1647962.jpg', '1739100543-0-the-storm-thefatrat-2560x1440 - 2.jpg,1739100543-1-Wallpaper.png,1739100543-2-wallpaperflare.com_wallpaper (1).jpg', 'qwdqwd', 'Vigan', 'Festivals & Events', 17.582457, 120.397114, 'active', '2025-02-09 11:29:03', '2025-02-09 11:29:03'),
(5, 'featured 3', '1739100614-thumbnail-wallpaperflare.com_wallpaper (1).jpg', '1739100614-0-the-storm-thefatrat-2560x1440 - 3.jpg,1739100614-1-wp2737732-nature-night-hd-wallpaper.jpg,1739100614-2-wp2737823-nature-night-hd-wallpaper.jpg,1739100614-3-wp2737837-nature-night-hd-wallpaper.jpg', 'eqd dq', 'Cabugao', 'Traditional Crafts', 17.582457, 120.397114, 'active', '2025-02-09 11:30:14', '2025-02-09 11:30:14');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `destination_type` varchar(20) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rating_id`, `user_id`, `destination_id`, `destination_type`, `rating`, `created_at`, `updated_at`) VALUES
(1, 16, 2, 'tourist', 4, '2025-01-16 08:17:55', '2025-01-17 02:28:38'),
(2, 13, 2, 'tourist', 5, '2025-01-16 08:45:56', '2025-01-16 09:00:19'),
(3, 13, 1, 'tourist', 5, '2025-01-16 09:00:32', '2025-01-16 09:00:32'),
(4, 16, 16, 'business', 4, '2025-01-17 02:55:19', '2025-01-17 02:55:19'),
(5, 20, 1, 'tourist', 4, '2025-01-19 00:56:20', '2025-01-19 00:56:20');

-- --------------------------------------------------------

--
-- Table structure for table `saveditineraries`
--

CREATE TABLE `saveditineraries` (
  `itinerary_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `itinerary_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saveditineraries`
--

INSERT INTO `saveditineraries` (`itinerary_id`, `user_id`, `itinerary_data`, `created_at`) VALUES
(4, 16, '{\"people\":\"2\",\"destination\":\"Vigan\",\"start_date\":\"2025-01-18\",\"end_date\":\"2025-01-19\",\"duration\":\"2\",\"budget\":\"10000\",\"activities\":\"Cultural,Outdoor and Nature,Food and Drink\",\"sights\":\"Religious Site,Museum,Parks,Restaurant\",\"need_accommodation\":\"yes\",\"accommodation\":\"Hotel\"}', '2025-01-18 03:23:18'),
(5, 16, '{\"people\":\"1\",\"destination\":\"Bantay\",\"start_date\":\"2025-01-18\",\"end_date\":\"2025-01-18\",\"duration\":\"1\",\"budget\":\"5000\",\"activities\":\"Cultural,Historical,Outdoor and Nature,Food and Drink\",\"sights\":\"Religious Site,Structures and Buildings,Nature Trail,Camping Ground,Restaurant\",\"need_accommodation\":\"no\",\"accommodation\":\"\"}', '2025-01-18 11:09:27'),
(6, 20, '{\"people\":\"2\",\"destination\":\"Vigan\",\"start_date\":\"2025-01-19\",\"end_date\":\"2025-01-20\",\"duration\":\"2\",\"budget\":\"10000\",\"activities\":\"Cultural,Historical,Outdoor and Nature,Food and Drink\",\"sights\":\"Religious Site,Museum,Parks,Restaurant\",\"need_accommodation\":\"yes\",\"accommodation\":\"Hotel\"}', '2025-01-19 00:54:58'),
(7, 21, '{\"people\":\"2\",\"destination\":\"Vigan\",\"start_date\":\"2025-01-19\",\"end_date\":\"2025-01-20\",\"duration\":\"2\",\"budget\":\"10000\",\"activities\":\"Cultural,Historical,Outdoor and Nature,Food and Drink\",\"sights\":\"Religious Site,Museum,Parks,Restaurant\",\"need_accommodation\":\"yes\",\"accommodation\":\"Hotel\"}', '2025-01-19 01:13:02'),
(8, 21, '{\"people\":\"1\",\"destination\":\"Bantay\",\"start_date\":\"2025-01-19\",\"end_date\":\"2025-01-19\",\"duration\":\"1\",\"budget\":\"5000\",\"activities\":\"Adventure,Outdoor and Nature,Relaxation,Food and Drink\",\"sights\":\"Nature Trail,Camping Ground,Parks,Restaurant\",\"need_accommodation\":\"no\",\"accommodation\":\"\"}', '2025-01-19 01:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `tourist_spots`
--

CREATE TABLE `tourist_spots` (
  `tourist_spot_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `destination_thumbnail` varchar(255) DEFAULT NULL,
  `images` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `entrance_fee` decimal(10,2) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `is_open` tinyint(1) DEFAULT 1,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tourist_spots`
--

INSERT INTO `tourist_spots` (`tourist_spot_id`, `name`, `destination_thumbnail`, `images`, `description`, `location`, `barangay`, `category`, `entrance_fee`, `latitude`, `longitude`, `is_open`, `opening_time`, `closing_time`, `status`) VALUES
(1, 'Bell Tower', '1732621578-11097921823_f52608b025_k.jpg', '1731646555-999e823fa5ab662fbf267921da24f087.jpg,1732621829-Bantay_Church_Bell_Tower_Ilocos_Sur.jpg', 'The picturesque, rustic and durable BELFRY (or the famous Bantay bell tower) sits on a hilltop (atop the Calvario Hill) overlooking a vivid green vast of pastureland and the mountain view of the Province of Abra. It was used as a watchtower and hide-out for invading enemy forces during World Wars I (during the invasion of Zambals) and II (on December 19, 1941, the bells rang incessantly when Japanese ships were sighted at Mindoro, Vigan). On April 15, 1945, the bell tower rigorously rang announcing immediate evacuation of the people as several bombs were dropped by the American forces at the church and bell tower vicinity because Japanese in here quartered and took refuge. Evidently, the scenic Bantay Church and bell tower are monumental witnesses to various atrocities, uprisings and staged revolts.', 'Bantay', 'Barangay 1 (Poblacion)', 'Religious Site', 100.00, 17.582210, 120.391570, 1, NULL, NULL, 'active'),
(2, 'Baluarte', '1731903953-Baluarte-Ilocos-Sur-091-2-1080x721.jpg', '1731646809-blog_dsc_9166.webp,1731646809-20151024-083628-01-largejpg (1).jpg', 'The residence of Former Gov. Chavit Singson. It has a mini zoo, a butterfly garden and an animal encounter show. The Baluarte Zoo had various wild animals under captivity. This includes ostriches, Bengal tigers and lions. The zoo also has a butterfly sanctuary.', 'Vigan', 'Barangay V (Poblacion)', 'Parks', 200.00, 17.551472, 120.377198, 1, NULL, NULL, 'active'),
(3, 'Santa Catalina Church', '1731904732-p0.jpg', '1732623140-qwe.jpg,1732623140-rty.jpg,1732623140-uio.jpg', 'The Santa Catalina Church Parish Church or St. Catherine de Alexandria is located in Poblacion, Santa Catalina, Ilocos Sur. The Church facade is painted in white and is semi-triangle-shaped. The main entrance of the church is a roman type arch, with its voussoirs made of brown bricks, and the speaker of the church is placed on top of the keystone. The elders said that the church was built with apog or crushed limestone and anay or sand. The cement used before was made of tagapulot or sugar. The bobeda or ceiling of the church before was said to be made of Bamboo. The parish finance council (Marcelina Refuerzo) for 43 years stated that the church initially had a kuro (choir), located in the upper western part of the church, that served as the area provided for the clergy and church. It was done under the supervision of Rev. Amador Foz who served as the parsih priest from 1983-1987. The next renovations were done under the supervision of Msgr. Venecio ACas, the parish priest from 1993-1998. The initial ceiling of the church, made of lawanit or plywood, was changed to a spandrel. The present parish priest, Rev. Robert Somera facilitated the enhancement of the retablo or altarpiece wherein he changed the accent colors to red, blue, and gold-the colors assoicated with St. Catherine de Alexandria. Further records of the renovations were kept in the Archdiocese of Nueva Segovia, located in Vigan City.', 'Santa Catalina', 'Poblacion', 'Religious Site', 0.00, 17.589465, 120.362882, 0, '08:00:00', '17:00:00', 'active'),
(4, 'Calle Crisologo', '1731965470-calle-crisologo.jpg', '1732623362-calle-crisologo-1.jpg,1732623362-calle-crisologo-vigan.jpg', 'One of the most beautiful streets in the Philippines. It boasts centuries-old stone houses, lovely tungsten lamps, and antique cobblestone, where horse-drawn carriages or kalesas are still used for transport. In fact, the street is a pedestrian-only zone, save for kalesas favored for touring the historical sites around town.', 'Vigan', 'Barangay I (Poblacion)', 'Historical Road', 0.00, 17.571591, 120.388752, 1, NULL, NULL, 'active'),
(5, 'Conversion of St. Paul Metropolitan Cathedral', '1731965591-CEM2663217_f3cecf09-615e-4429-8ca0-7794e69188d4.jpeg', '1732623565-Vigan city street-37.jpg,1732623565-2023-02-28.jpg', 'The Conversion of St. Paul Metropolitan Cathedral also known as the Vigan Metropolitan Cathedral was built by the Augustinians in 1790-1800 in a distinctive “Earthquake Baroque” architecture. It symbolizes Vigan as the center of ecclesiastical influence in the north as the seat of the Archdiocese of Nueva Segovia.', 'Vigan', 'Barangay I (Poblacion)', 'Religious Site', 0.00, 17.574972, 120.388551, 1, NULL, NULL, 'active'),
(6, 'Mt. Tupira', '1732623738-Mt_ Tupira, Bantay, Ilocos Sur.jpg', '1732623913-asd.jpg', 'Situated on top of Mount Caniao, (where Victoria Park or Caniao Heritage and Eco tourism park is located), at Brgy. Taleb, the entirety of Bantay and nearby towns could be viewed from this site, even as far as the Abra and the Cordillera mountains. It is 8.5 kilometers road from the national highway, with an elevation of 1,200 meters above sea level, and part of the Northern Luzon Heroes Hill National Park (NLHHNP) that extends up to the towns of Santa and Narvacan. Its topmost deck is often referred to simply as “radar” because in the early 60’s, a lofty twin metallic-sheet satellite towers (military communication ITs) were prominently seen radiating during the day and beaming at night. It is situated in such an elevated position that gives access and advantage in telecommunications and antennae relay stations. Transmission lines, telecoms cellular network facilities are found atop its rugged slopes... Untamed animals such as wild deer (‘ogsa’), wild pig (‘alingo’), python snakes (‘bet-lat’), wildfowl (‘abuyo’), monkeys and other variety of birds could still be caught from this mountainside. Climbing its pinnacle is a toiling task for the now all-paved cemented road, which is steeper and more precarious than Baguio’s Kennon road, but upon reaching its peak, one gets rewarding prize- a cool breeze of air, smell of pine trees, bounteous flora and fauna and breathe-taking natural scenery at high altitude. A perfect destination for mountain climbers, hunters, hikers and thrill seekers. Our bounteous natural-endowed treasure. A sight- seeing structure is being presently built at its zenith to overview neighboring towns and the Cordillera mountains.', 'Bantay', 'Taleb', 'Nature Trail', 0.00, 17.602298, 120.486275, 1, NULL, NULL, 'active'),
(7, 'Quirino Bridge', 'a66d760d69d571d9056e4aa54f83bc63.jpg', '1732624506-awse.jpg,1732624506-asdf.jpg', 'This grandiose four-span metallic bridge is named after the late former President Elpidio Quirino, an Ilocano, and spreads across the Abra river connecting the rocky mountain hills of the town of Santa and the tail-end of Bantay. Also referred to as ‘Banaoang bridge’, it majestically connects and separates two transcending barangays (likewise named Banaoang) and mountainous terrain of the LGUs of Bantay and Santa, Ilocos Sur. It is widely praised because of its marvelous engineering and grand architectural design as glorified by its splendid panoramic beauty, strength and durability when it survived the bombings of World War II. It was once partly destroyed by Super Typhoon Feria, hence a different-looking third quarter portion. On December 2007, Chinese engineers and a local construction company started to build a new, 456-meter-long replacement, a stone\'s throw from the original bridge. It was officially opened by then-President Gloria Macapagal Arroyo on December 30, 2009. The old bridge, an iconic symbol of Ilocos Sur, is currently preserved as a tourist attraction, doubling as a backup in case the main bridge is damaged by typhoons.', 'Bantay', 'Banaoang', 'Historical Road', 0.00, 17.577100, 120.387620, 1, NULL, NULL, 'active'),
(8, 'The Archdiocesan Shrine of Saint Vincent Ferrer', '1732624885-2023-04-09 (1).jpg', '1732624932-panoramio-71481382.jpg,1732624932-2019-04-19.jpg', 'The Archdiocesan Shrine of Saint Vincent Ferrer, also known as Saint Vincent Ferrer Parish Church, is a Roman Catholic church in the municipality of San Vicente, Ilocos Sur, Philippines. It is under the jurisdiction of the Archdiocese of Nueva Segovia. The church enshrines a miraculous image of Saint Vincent Ferrer in the main retablo. The present church is built in 1795 after the main town was founded. The town was formerly a barrio of Vigan known as Barrio Tuanong.', 'San Vicente', 'Poblacion', 'Religious Site', 0.00, 17.594539, 120.374194, 1, NULL, NULL, 'active'),
(10, 'testedit', '1731841091-Wallpaper.png', '1731843862-33d8d250703bf55a33a0e34caa99206c.jpg,1731843862-beyond-2560x1440.jpg,1731843862-breach-1920x1080.jpg', 'etewtaeta edit', 'Bantay', 'Aggay', 'Museum', 500.00, 17.531211, 120.391622, 1, NULL, NULL, 'archived'),
(11, 'test 2', '1731727135-thumbnail-breach-1920x1080.jpg', '1731727135-1-pexels-michał-osiński-3454270(2).jpg,1731727135-2-the-storm-thefatrat-2560x1440 - 2.jpg,1731727135-3-wp2737732-nature-night-hd-wallpaper.jpg', 'efewfewf', 'Bantay', 'Aggay', 'Religious Site', 150.00, 17.582457, 120.397114, 1, NULL, NULL, 'archived'),
(14, 'Arzobispado De Nueva Segovia', '1732625822-thumbnail-BluPrint-Palacio-de-Arzobispado-de-Nueva-Segovia-header (1).jpg', '1732625822-0-Arzobispado-Palacio-De-Nueva-Segovia-1024x538.jpg', 'Built in 1783 and the only surviving 18th century Arzobispado in the country. The Arzobispado of Vigan is the official residence of the Archbishop of Nueva Segovia. It served as the headquarters of Gen. Manuel Tinio in 1898 and the invading American forces under Col. James Parker in 1899. Its Museo Nueva Segovia showcases ecclesiastical artifacts, antique portraits of bishops, a throne room, archdiocesan and other religious articles gathered from various colonial churches all over Ilocos Sur.', 'Vigan', 'Barangay VII (Poblacion)', 'Parks', 0.00, 17.575488, 120.388414, 1, '08:00:00', '17:00:00', 'active'),
(15, 'Mira Hills Fil-Spanish Friendship Park', '1732626153-thumbnail-IMG_20180305_113100.jpg', '1732626153-0-2017-09-17.jpg,1732626153-1-2017-10-06.jpg', 'Mira Hills is considered as the lung of the city. Trees are abundant in this place where visitors can relax and savor fresh air. It has a swimming pool, an open amphitheater and situated in the park is Buridek Children’s Museum. First of its kind in Northern Luzon and the 3rd to be established in the country, the Buridek Museum is a perfect venue for visitors to interactively learn the culture and history of Vigan. Added attraction is the ChEERZONE (Children’s Ecology & Energy Recreational Zone) – Kids will have a great time in this adventure area consisting of a Zipline, Monkey Trail, Basang’s Ladder, Parallel Wire Cross and Ballong’s Crawling Net.', 'Vigan', 'Barangay VII (Poblacion)', 'Parks', 100.00, 17.570542, 120.381159, 1, NULL, NULL, 'active'),
(16, 'St. Augustine Parish Church', '1732626451-thumbnail-29405_393715169868_4212779_n.jpg', '1732626451-1-20180223_084841.jpg,1732626514-qgytj.JPG', 'One of the oldest churches of Ilocos Sur, was built in 1590 with Fr. Juan Bautista de Montoya, as the first parish priest. The Augustinian missionaries named it St. Augustine, after their religious congregation which, of course, soon became the Patron Saint. Originally it was just a chapel or temple for adoration made of cogon grass and splitted bamboos, built by Bantay natives, where the earlier-found Image of Our Lady of Charity was placed and venerated.', 'Bantay', 'Barangay 5 (Poblacion)', 'Religious Site', 0.00, 17.581350, 120.391653, 1, NULL, NULL, 'active'),
(17, 'Caniaw Heritage and Forest Park', '1732626858-thumbnail-2019-10-24.jpg', '1732626858-0-cntyjr.jpg', 'This square was established in 1962 and was named in honor of one of the daughters of former President Elpidio Quirino. Herein can be found water falls, a crystal clear natural spring water source (supplying our town, local water district and adjacent Vigan), an herbarium, botanical nursery and DENR field Office. It is connected to the MNR by a 2.2 kil. road situated on the middle foot of Mount Caniaw located at Brgy Taleb, it is part of the so-called Caniao Reforestation Project and Wildlife Sanctuary. Gigantic varieties of mountain trees and various species of flora and fauna could be found and waiting to be discovered. A favorite venue for lovers of nature, particularly wildlife hunters, trekkers and hikers, mountain trailers, campers and as picnic groove to residents and vacationers. On April 12, 2019, Caniao Heritage and Eco Tourism Park was opened and blessed in place of Victoria Park, under the auspices of the Provincial Tourism Office, where a forest park and adventure zone (camping site, picnic groove, recreational outings, hunting ground, hiking & trail chasing) were launched and established.', 'Bantay', 'Taleb', 'Nature Trail', 0.00, 17.583097, 120.462870, 1, NULL, NULL, 'active'),
(18, 'Don Dimas Mansion', '1732627449-thumbnail-house.jpg', '1732627449-0-Don_Dimas_Querubin_House.jpg', 'ILOCOS SUR -- The 157 year-old Querubin ancestral house situated in the town of Caoayan, Ilocos Sur was declared an important cultural property (ICP) by the National Museum. The ancestral house, which served as residence for Ilocano patriots and iconic public servants, was built in 1860 by Don Tomas Querubin, a philanthropist and an elected gobernadorcillo (mayor) for two terms in the town. The house was later inherited by Don Dimas Querubin.', 'Caoayan', 'Don Dimas Querubin (Poblacion)', 'Structures and Buildings', 120.00, 13.390606, 123.401977, 1, NULL, NULL, 'active'),
(19, 'Fuerte Beach', '1732627809-thumbnail-2024-06-19.jpg', '1732627809-0-2023-11-08.jpg,1732627809-1-2024-06-19 (1).jpg', 'Nestled in the heart of Caoayan, Philippines, Fuerte Beach Boardwalk stretches with golden sands along its expansive length.', 'Caoayan', 'Fuerte', 'Beach', 0.00, 17.534397, 120.367512, 1, NULL, NULL, 'active'),
(20, 'Dinosaur Island', '1732628108-thumbnail-2022-12-19.jpg', '1732628108-0-PXL_20230827_032830106.jpg,1732628108-1-L1001369.JPG', 'Dinosaurs Island Ilocos is a learning site for school children to develop their personal and social interaction while experiencing a quality educational tour.', 'San Vicente', 'Poblacion', 'Parks', 100.00, 17.590030, 120.377843, 1, NULL, NULL, 'active'),
(21, 'Crisologo Museum', '1737247315-38934659721_504925dca2_c.jpg', '1737247315-0-IMG_2190.jpg,1737247315-1-38219060474_1aeabecdc5_c-800x445.jpg', 'This former residence of politician Floro S. Crisologo is now a museum with memorabilia & furniture.', 'Vigan', 'Barangay VIII (Poblacion)', 'Museum', 100.00, 17.570710, 120.386869, 1, NULL, NULL, 'active'),
(22, 'Sample 1', '1736650923-thumbnail-beyond-2560x1440.jpg', '1736650923-0-IMG_20221107_114711.jpg,1736650923-1-pexels-michał-osiński-3454270.jpg,1736650923-2-pexels-quang-nguyen-vinh-6346494.jpg,1736650923-3-pexels-veeterzy-39811.jpg', 'This a text. 😊😍😁', 'Santa Catalina', 'Sinabaan', 'Recreational Activities', 270.00, 17.561495, 120.382702, 1, NULL, NULL, 'archived'),
(23, 'Sample 2', '1736805982-pexels-quang-nguyen-vinh-6346494.jpg', '1736805982-0-33d8d250703bf55a33a0e34caa99206c.jpg,1736805982-1-beyond-2560x1440.jpg,1736806041-0-IMG_20221107_114711.jpg', 'This a text. asdfg', 'San Vicente', 'Bantaoay', 'Nature Trail', 50.00, 17.561495, 120.382702, 1, NULL, NULL, 'archived'),
(24, 'Sample 3', '1736668953-thumbnail-breach-1920x1080.jpg', '1736668953-0-33d8d250703bf55a33a0e34caa99206c.jpg,1736668953-1-breach-1920x1080.jpg,1736668953-2-pexels-quang-nguyen-vinh-6346494.jpg,1736668953-3-pexels-valdemaras-d-1647962.jpg', 'asdjtyjtyjty', 'Vigan', 'Ayusan Norte', 'Structures and Buildings', 30.00, 17.582457, 120.397114, 1, NULL, NULL, 'archived'),
(25, 'Sample 4', '1736669177-thumbnail-pexels-veeterzy-39811.jpg', '1736669177-0-beyond-2560x1440.jpg,1736669177-1-pexels-michał-osiński-3454270(2).jpg,1736669177-2-wp2737780-nature-night-hd-wallpaper.jpg,1736669177-3-wp10165109-genshin-impact-scenery-wallpapers.png', 'frgsrthsrth', 'Bantay', 'Aggay', 'Museum', 56.00, 17.531211, 120.391622, 1, NULL, NULL, 'archived'),
(26, 'Sample 10', '1737249599-breach-1920x1080.jpg', '1737249464-3-pexels-quang-nguyen-vinh-6346494.jpg,1737249464-4-pexels-valdemaras-d-1647962.jpg,1737249599-0-the-storm-thefatrat-2560x1440 - 3.jpg,1737249599-1-Wallpaper.png,1737249599-2-wallpaperflare.com_wallpaper (1).jpg', 'This is a sample textssss.', 'Bantay', 'Bulag', 'Nature Trail', 300.00, 17.582457, 120.397114, 1, NULL, NULL, 'active'),
(27, 'Test 7', '1738372443-thumbnail-beyond-2560x1440.jpg', '1738372443-0-pexels-michał-osiński-3454270.jpg,1738372443-1-pexels-quang-nguyen-vinh-6346494.jpg,1738372443-2-pexels-valdemaras-d-1647962.jpg', 'awan', 'Santa Catalina', 'Sinabaan', 'Camping Ground', 0.00, 17.582457, 120.397114, 0, '00:00:00', '00:00:00', 'archived');

-- --------------------------------------------------------

--
-- Table structure for table `userpreferences`
--

CREATE TABLE `userpreferences` (
  `preference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preference_type` enum('activity','category') NOT NULL,
  `preference_value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userpreferences`
--

INSERT INTO `userpreferences` (`preference_id`, `user_id`, `preference_type`, `preference_value`, `created_at`, `updated_at`) VALUES
(8, 18, 'activity', 'Cultural', '2025-01-05 08:19:34', '2025-01-05 08:19:34'),
(9, 18, 'activity', 'Historical', '2025-01-05 08:19:34', '2025-01-05 08:19:34'),
(10, 18, 'activity', 'Educational', '2025-01-05 08:19:34', '2025-01-05 08:19:34'),
(11, 18, 'category', 'Parks', '2025-01-05 08:19:34', '2025-01-05 08:19:34'),
(12, 18, 'category', 'Beaches', '2025-01-05 08:19:34', '2025-01-05 08:19:34'),
(13, 18, 'category', 'Restaurant', '2025-01-05 08:19:34', '2025-01-05 08:19:34'),
(14, 19, 'activity', 'Historical', '2025-01-06 04:21:21', '2025-01-06 04:21:21'),
(15, 19, 'category', 'Religious Site', '2025-01-06 04:21:21', '2025-01-06 04:21:21'),
(16, 19, 'category', 'Restaurant', '2025-01-06 04:21:21', '2025-01-06 04:21:21'),
(22, 16, 'activity', 'Historical', '2025-01-08 01:56:17', '2025-01-08 01:56:17'),
(23, 16, 'activity', 'Adventure', '2025-01-08 01:56:17', '2025-01-08 01:56:17'),
(24, 16, 'activity', 'Nature', '2025-01-08 01:56:17', '2025-01-08 01:56:17'),
(25, 16, 'category', 'Parks', '2025-01-08 01:56:17', '2025-01-08 01:56:17'),
(26, 16, 'category', 'Beaches', '2025-01-08 01:56:17', '2025-01-08 01:56:17'),
(27, 16, 'category', 'Restaurant', '2025-01-08 01:56:17', '2025-01-08 01:56:17'),
(28, 20, 'activity', 'Cultural', '2025-01-19 00:49:42', '2025-01-19 00:49:42'),
(29, 20, 'activity', 'Adventure', '2025-01-19 00:49:42', '2025-01-19 00:49:42'),
(30, 20, 'activity', 'Nature', '2025-01-19 00:49:42', '2025-01-19 00:49:42'),
(31, 20, 'category', 'Museum', '2025-01-19 00:49:42', '2025-01-19 00:49:42'),
(32, 20, 'category', 'Parks', '2025-01-19 00:49:42', '2025-01-19 00:49:42'),
(33, 20, 'category', 'Beaches', '2025-01-19 00:49:42', '2025-01-19 00:49:42'),
(41, 21, 'activity', 'Nature', '2025-01-19 01:06:45', '2025-01-19 01:06:45'),
(42, 21, 'category', 'Religious Site', '2025-01-19 01:06:45', '2025-01-19 01:06:45'),
(43, 21, 'category', 'Museum', '2025-01-19 01:06:45', '2025-01-19 01:06:45'),
(44, 21, 'category', 'Parks', '2025-01-19 01:06:45', '2025-01-19 01:06:45'),
(45, 21, 'category', 'Restaurant', '2025-01-19 01:06:45', '2025-01-19 01:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Bryan', 'johnbryanrafael25@gmail.com', '$2y$10$Wa8I87BtYusGu3.XaOKoLuJNYNV.jH.h7.ui9YwiJrguskrxbN9Iq', '2025-01-05 03:53:28', '2025-01-05 03:53:28'),
(2, 'Dos', 'nanatata500@gmail.com', '$2y$10$YrdskNnivZaqsbCFIrdzuOiYJWXssDiFQ6lwkhVQIDhhWzs8fBPbm', '2025-01-05 03:53:28', '2025-01-05 03:53:28'),
(4, 'Yere', 'johnmichaelyere09@gmail.com', '$2y$10$n/RtGMsKIkLN.nKSEX5kDeLvzkVkcbOZuCqnirvuQiWiGQBM4ZGOu', '2025-01-05 03:53:28', '2025-01-05 03:53:28'),
(7, 'user1', 'user1@gmail.com', '$2y$10$FIcmef6cFg3T1GJEazY9nOFiOoeOkOEvKtyjUvuW1OkVqw2SXMYZC', '2025-01-05 03:53:28', '2025-01-05 03:53:28'),
(8, 'Dex', 'dexleyanne@gmail.com', '$2y$10$oI6pxaR2ZPDjCLmixmcwFeWBMWk996XXABrWaut.oT3iUrLYdxxgK', '2025-01-05 03:53:28', '2025-01-05 03:53:28'),
(9, 'user2', 'user2@gmail.com', '$2y$10$rDrJt3kGvhKDW/YICKok.OdN0nkz.TToVcp7XGDs9KHTrGnpxN7iC', '2025-01-05 03:53:28', '2025-01-05 03:53:28'),
(12, 'user3', 'user3@gmail.com', '$2y$10$RfWwR2OjiGgnlvbjDL32ZuLPamHzrzwbpbv/fk1Sp.NkJHkWZi8ya', '2025-01-05 03:53:28', '2025-01-05 03:53:28'),
(13, 'JB', 'user4@gmail.com', '$2y$10$6E0.SILvfSACh75SQbR6lOlUgVh494io611UcaIru6v3moyrw2/Ti', '2025-01-05 03:53:28', '2025-01-05 03:53:28'),
(16, 'John Bryan', 'jbryan@gmail.com', '$2y$10$IC7BMsIcfBUU7UIgBnBJ.OZfPIx4V3/Pcd7v1z/Jxsto/B2s2LWvW', '2025-01-05 08:05:22', '2025-01-05 08:05:22'),
(17, 'admin1', 'admin1@gmail.com', '$2y$10$kKwtWMmEQu0qqXPuPtN.xekhjV7kJP7U10lc4ocG49PvuiMhcMSwO', '2025-01-05 08:07:56', '2025-01-05 08:07:56'),
(18, 'admin2', 'admin2@gmail.com', '$2y$10$7J1uRq5h8RflAWhPIGahu./g4O5HmhyMOvOf4zYeTwLPtJy997mnu', '2025-01-05 08:19:34', '2025-01-05 08:19:34'),
(19, 'admin3', 'admin3@gmail.com', '$2y$10$0sfwQcQIwR59RfIsxci0ounBN1iUSxCHhlNfqza6KLEE7etkJ36fq', '2025-01-06 04:21:21', '2025-01-06 04:21:21'),
(20, 'user5', 'user5@gmail.com', '$2y$10$kya3j2/HNzHVRcA1HU/83OjWojHD6Lal1JqJkhcbUGvGlLvHO4Q8m', '2025-01-19 00:49:42', '2025-01-19 00:49:42'),
(21, 'user6', 'user6@gmail.com', '$2y$10$F/Pd1y4STzfQcApn8IEb6ObjRMW8IJKUz9oVMI5bYJh5zeURJeeLO', '2025-01-19 01:05:44', '2025-01-19 01:05:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`business_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `featured_attractions`
--
ALTER TABLE `featured_attractions`
  ADD PRIMARY KEY (`attraction_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_user_rating` (`user_id`,`destination_id`,`destination_type`);

--
-- Indexes for table `saveditineraries`
--
ALTER TABLE `saveditineraries`
  ADD PRIMARY KEY (`itinerary_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  ADD PRIMARY KEY (`tourist_spot_id`);

--
-- Indexes for table `userpreferences`
--
ALTER TABLE `userpreferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD KEY `idx_user_preferences` (`user_id`,`preference_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `business_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `featured_attractions`
--
ALTER TABLE `featured_attractions`
  MODIFY `attraction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `saveditineraries`
--
ALTER TABLE `saveditineraries`
  MODIFY `itinerary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  MODIFY `tourist_spot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `userpreferences`
--
ALTER TABLE `userpreferences`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `saveditineraries`
--
ALTER TABLE `saveditineraries`
  ADD CONSTRAINT `saveditineraries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `userpreferences`
--
ALTER TABLE `userpreferences`
  ADD CONSTRAINT `userpreferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
