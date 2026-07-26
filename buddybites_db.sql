-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2026 at 11:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `buddybites_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `custom_meals`
--

CREATE TABLE `custom_meals` (
  `custom_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `base` varchar(100) NOT NULL,
  `protein` varchar(255) NOT NULL,
  `topping` varchar(255) NOT NULL,
  `toppings` varchar(255) NOT NULL,
  `sauce` varchar(100) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_meals`
--

INSERT INTO `custom_meals` (`custom_id`, `user_id`, `base`, `protein`, `topping`, `toppings`, `sauce`, `total_price`, `created_at`) VALUES
(1, 10, 'White Rice', 'Vegetables', 'Vegetables', '', 'Black Pepper', 4.00, '2026-06-28 02:10:02');

-- --------------------------------------------------------

--
-- Table structure for table `group_cart_items`
--

CREATE TABLE `group_cart_items` (
  `group_cart_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_type` varchar(50) NOT NULL DEFAULT 'menu',
  `restaurant_name` varchar(100) NOT NULL DEFAULT 'BuddyBites',
  `details` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `member_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`member_id`, `group_id`, `user_id`, `joined_at`) VALUES
(1, 1, 10, '2026-06-28 02:09:29'),
(2, 1, 9, '2026-06-28 02:09:42');

-- --------------------------------------------------------

--
-- Table structure for table `group_orders`
--

CREATE TABLE `group_orders` (
  `group_id` int(11) NOT NULL,
  `group_code` varchar(6) NOT NULL,
  `host_user_id` int(11) NOT NULL,
  `status` varchar(30) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_orders`
--

INSERT INTO `group_orders` (`group_id`, `group_code`, `host_user_id`, `status`, `created_at`) VALUES
(1, '957171', 10, 'active', '2026-06-28 02:09:29');

-- --------------------------------------------------------

--
-- Table structure for table `menu_feedback`
--

CREATE TABLE `menu_feedback` (
  `feedback_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `liked` tinyint(1) NOT NULL DEFAULT 0,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_feedback`
--

INSERT INTO `menu_feedback` (`feedback_id`, `item_id`, `user_id`, `rating`, `liked`, `review`, `created_at`, `updated_at`) VALUES
(1, 13, 3, 5, 0, 'Delicious', '2026-06-28 02:58:05', '2026-06-28 02:58:05'),
(2, 1, 3, 5, 0, 'Delicious', '2026-06-28 04:17:28', '2026-06-28 04:17:28'),
(3, 3, 3, 5, 0, 'Delicious', '2026-06-28 04:39:15', '2026-06-28 04:39:15');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `restaurant_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `availability` varchar(20) NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `item_name`, `restaurant_name`, `category`, `price`, `image`, `availability`) VALUES
(1, 'Anchovies Fried Rice', 'Campus Cafe', 'Rice', 5.80, 'anchovies-fried-rice.JPG', 'Available'),
(2, 'Black Pepper Chicken Rice', 'Chicken Corner', 'Rice', 7.00, 'black-pepper-chicken-rice.jpg', 'Available'),
(3, 'Char Kuey Teow', 'Mamak Corner', 'Noodles', 6.80, 'char-kuey-teow.jpg', 'Available'),
(4, 'Cheese Sandwich', 'Snack Hub', 'Snacks', 4.20, 'cheese-sandwich.jpg', 'Available'),
(5, 'Cheesy Fries', 'Snack Hub', 'Snacks', 4.80, 'cheesy-fries.jpg', 'Available'),
(6, 'Chicken Chop Fried Rice', 'Campus Cafe', 'Rice', 8.50, 'chicken-chop-fried-rice.jpg', 'Available'),
(7, 'Chicken Chop Rice', 'Student Kitchen', 'Rice', 8.00, 'chicken-chop-rice.jpg', 'Available'),
(8, 'Chicken Fried Rice', 'Student Kitchen', 'Rice', 6.50, 'chicken-fried-rice.jpg', 'Available'),
(9, 'Chicken Meatball', 'Snack Hub', 'Snacks', 5.50, 'chicken-meatball.jpg', 'Available'),
(10, 'Chicken Popcorn', 'Snack Hub', 'Snacks', 5.00, 'chicken-popcorn.jpg', 'Available'),
(11, 'Chicken Roll', 'Wrap House', 'Snacks', 4.50, 'chicken-roll.jpg', 'Available'),
(12, 'Chicken Sandwich', 'Sandwich Bar', 'Snacks', 4.80, 'chicken-sandwich.jpg', 'Available'),
(13, 'Chicken Tortilla', 'Wrap House', 'Snacks', 6.50, 'chicken-tortilla.jpg', 'Available'),
(14, 'Chicken Wrap', 'Wrap House', 'Snacks', 5.80, 'chicken-wrap.jpg', 'Available'),
(15, 'Club Sandwich', 'Sandwich Bar', 'Snacks', 5.50, 'club-sandwich.jpg', 'Available'),
(16, 'Curry Chicken Rice', 'Campus Cafe', 'Rice', 7.00, 'curry-chicken-rice.jpg', 'Available'),
(17, 'Double Chicken Wrap', 'Wrap House', 'Snacks', 7.50, 'double-chicken-wrap.jpg', 'Available'),
(18, 'Egg Fried Rice', 'Student Kitchen', 'Rice', 5.80, 'egg-fried-rice.jpg', 'Available'),
(19, 'Egg Mayo Wrap', 'Wrap House', 'Snacks', 4.80, 'egg-mayo-wrap.jpg', 'Available'),
(20, 'Egg Sandwich', 'Sandwich Bar', 'Snacks', 3.80, 'egg-sandwich.jpg', 'Available'),
(21, 'Fish Fillet Rice', 'Campus Cafe', 'Rice', 7.20, 'fish-fillet-rice.jpg', 'Available'),
(22, 'Fried Chicken Rice', 'Student Kitchen', 'Rice', 7.00, 'fried-chicken-rice.jpg', 'Available'),
(23, 'Fried Chicken', 'Snack Hub', 'Snacks', 6.50, 'fried-chicken.jpg', 'Available'),
(24, 'Fried Fish', 'Student Kitchen', 'Snacks', 6.00, 'fried-fish.jpg', 'Available'),
(25, 'Fried Rice Egg', 'Campus Cafe', 'Rice', 5.80, 'fried-rice-egg.jpg', 'Available'),
(26, 'Fried Rice', 'Student Kitchen', 'Rice', 5.50, 'fried-rice.jpg', 'Available'),
(27, 'Fried Wantan', 'Snack Hub', 'Snacks', 4.50, 'fried-wantan.jpg', 'Available'),
(28, 'Fries', 'Snack Hub', 'Snacks', 3.80, 'fries.jpg', 'Available'),
(29, 'Kampung Fried Rice', 'Campus Cafe', 'Rice', 6.50, 'kampung-fried-rice.jpg', 'Available'),
(31, 'Mee Goreng Mamak', 'Mamak Corner', 'Noodles', 6.20, 'mee-goreng-mamak.jpg', 'Available'),
(33, 'Mini Hotdog', 'Snack Hub', 'Snacks', 3.50, 'mini-hotdog.jpg', 'Available'),
(34, 'Nasi Lemak', 'Canteen Abi', 'Rice', 6.50, 'nasi-lemak.jpg', 'Available'),
(35, 'Nuggets', 'Snack Hub', 'Snacks', 4.80, 'nuggets.jpg', 'Available'),
(36, 'Onion Rings', 'Snack Hub', 'Snacks', 4.50, 'onion-rings.jpg', 'Available'),
(37, 'Pattaya Fried Rice', 'Campus Cafe', 'Rice', 7.00, 'pattaya-fried-rice.jpg', 'Available'),
(38, 'Rice Bowl', 'Muk Kitchen', 'Rice', 7.50, 'rice-bowl.jpg', 'Available'),
(39, 'Sambal Fried Rice', 'Campus Cafe', 'Rice', 6.80, 'sambal-fried-rice.jpg', 'Available'),
(40, 'Sandwich', 'Sandwich Bar', 'Snacks', 3.80, 'sandwich.jpg', 'Available'),
(41, 'Sausage Roll', 'Snack Hub', 'Snacks', 3.80, 'sausage-roll.jpg', 'Available'),
(42, 'Seafood Fried Rice', 'Campus Cafe', 'Rice', 7.80, 'seafood-fried-rice.jpg', 'Available'),
(43, 'Sweet Sour Chicken Rice', 'Campus Cafe', 'Rice', 7.00, 'sweet-sour-chicken-rice.jpg', 'Available'),
(44, 'Tomyam Fried Rice', 'Mamak Corner', 'Rice', 7.50, 'tomyam-fried-rice.jpg', 'Available'),
(45, 'Tomyam Mee Hoon', 'Mamak Corner', 'Noodles', 7.50, 'tomyam-meehoon.jpg', 'Available'),
(46, 'Tuna Sandwich', 'Sandwich Bar', 'Snacks', 4.80, 'tuna-sandwich.jpg', 'Available'),
(47, 'Tuna Wrap', 'Wrap House', 'Snacks', 5.80, 'tuna-wrap.jpg', 'Available'),
(48, 'White Rice Fried Egg', 'Budget Bowl', 'Rice', 4.50, 'white-rice-fried-egg.jpg', 'Available'),
(49, 'White Rice Vegetables', 'Budget Bowl', 'Rice', 5.00, 'white-rice-vegetables.jpg', 'Available'),
(50, 'Apple Juice', 'Drink Station', 'Drinks', 3.50, 'apple-juice.jpg', 'Available'),
(51, 'Green Tea', 'Drink Station', 'Drinks', 2.80, 'green-tea.jpg', 'Available'),
(52, 'Ice Lemon Tea', 'Drink Station', 'Drinks', 2.80, 'ice-lemon-tea.jpg', 'Available'),
(53, 'Ice Milk Tea', 'Drink Station', 'Drinks', 3.80, 'ice-milk-tea.jpg', 'Available'),
(54, 'Iced Black Tea', 'Drink Station', 'Drinks', 2.80, 'iced-black-tea.jpg', 'Available'),
(55, 'Iced Chocolate', 'Drink Station', 'Drinks', 4.20, 'iced-chocolate.jpg', 'Available'),
(56, 'Iced Coffee', 'Drink Station', 'Drinks', 3.80, 'iced-coffee.jpg', 'Available'),
(57, 'Iced Lemon Tea', 'Drink Station', 'Drinks', 2.80, 'iced-lemon-tea.jpg', 'Available'),
(58, 'Iced Milk Tea', 'Drink Station', 'Drinks', 3.80, 'iced-milk-tea.jpg', 'Available'),
(59, 'Iced Milo', 'Drink Station', 'Drinks', 4.20, 'iced-milo.jpg', 'Available'),
(60, 'Mango Smoothie', 'Drink Station', 'Drinks', 5.50, 'mango-smoothie.jpg', 'Available'),
(63, 'Mineral Water', 'Drink Station', 'Drinks', 2.00, 'mineral-water.jpg', 'Available'),
(64, 'Orange Juice', 'Drink Station', 'Drinks', 3.50, 'orange-juice.jpg', 'Available'),
(65, 'Rose Milk Drink', 'Drink Station', 'Drinks', 3.80, 'rose-milk-drink.jpg', 'Available'),
(66, 'Strawberry Milkshake', 'Drink Station', 'Drinks', 5.50, 'strawberry-milkshake.jpg', 'Available'),
(67, 'Vanilla Milkshake', 'Drink Station', 'Drinks', 5.50, 'vanilla-milkshake.jpg', 'Available'),
(68, 'Build Your Own Meal', 'Buddy Bites Kitchen', 'Custom meal', 5.00, 'rice-bowl.jpg', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Preparing',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `status`, `order_date`) VALUES
(1, 9, 7.30, 'Ready', '2026-06-28 02:04:47'),
(2, 10, 14.80, 'Ready', '2026-06-28 02:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `restaurant_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_name`, `restaurant_name`, `quantity`, `price`) VALUES
(1, 1, 'Anchovies Fried Rice', 'Campus Cafe', 1, 5.80),
(2, 2, 'Anchovies Fried Rice', 'Campus Cafe', 1, 5.80),
(3, 2, 'Build Your Own Meal', 'Custom Meal Builder', 1, 4.00);

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `restaurant_id` int(11) NOT NULL,
  `restaurant_name` varchar(100) NOT NULL,
  `owner_name` varchar(100) DEFAULT '',
  `phone` varchar(30) DEFAULT '',
  `opening_hours` varchar(100) DEFAULT '8:00 AM - 10:00 PM',
  `status` varchar(30) DEFAULT 'Open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`restaurant_id`, `restaurant_name`, `owner_name`, `phone`, `opening_hours`, `status`) VALUES
(1, 'Campus Cafe', '', '', '8:00 AM - 10:00 PM', 'Open'),
(2, 'Chicken Corner', '', '', '8:00 AM - 10:00 PM', 'Open'),
(3, 'Mamak Corner', '', '', '8:00 AM - 10:00 PM', 'Open'),
(4, 'Snack Hub', '', '', '8:00 AM - 10:00 PM', 'Open'),
(5, 'Student Kitchen', '', '', '8:00 AM - 10:00 PM', 'Open'),
(6, 'Wrap House', '', '', '8:00 AM - 10:00 PM', 'Open'),
(7, 'Sandwich Bar', '', '', '8:00 AM - 10:00 PM', 'Open'),
(8, 'Canteen Abi', '', '', '8:00 AM - 10:00 PM', 'Open'),
(9, 'Muk Kitchen', '', '', '8:00 AM - 10:00 PM', 'Open'),
(10, 'Budget Bowl', '', '', '8:00 AM - 10:00 PM', 'Open'),
(11, 'Drink Station', '', '', '8:00 AM - 10:00 PM', 'Open'),
(12, 'Buddy Bites Kitchen', '', '', '8:00 AM - 10:00 PM', 'Open');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_code` varchar(20) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','restaurant','rider','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(30) DEFAULT '',
  `status` varchar(30) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_code`, `full_name`, `email`, `password`, `role`, `created_at`, `phone`, `status`) VALUES
(2, 'ADM001', 'System Admin', 'admin@buddybites.com', '$2y$10$.mgdVFf6iDMrI40HYdV8ke5rBvdlhU57kvqshDfTDIIeCYlEXYhNm', 'admin', '2026-06-27 04:48:21', '', 'Pending'),
(3, 'STU002', 'Puteri Normarissa Fatihah', 'puterinormarissa@gmail.com', '$2y$10$wbV9mo9/PNaM4qN1PEaM9.rQsiPUD/TwqnXRUz5ZS6r0keXwlV2TO', 'student', '2026-06-27 05:20:19', '', 'Pending'),
(4, 'RES001', 'Ali bin Abu', 'restaurant@buddybites.com', '$2y$10$lQ08IVvvzttuDQeHKrvjquxDFCs6SYofnLqdmGoj1AyNQPlElAJp6', 'restaurant', '2026-06-27 07:59:00', '', 'Pending'),
(5, 'RES002', 'Mamak Corner', 'mamak@buddybites.com', '$2y$10$Le9FjVcZgrFzozGmdCqgquj2kF95s6peRijoqPZnNzkONdqm0Rj0W', 'restaurant', '2026-06-27 08:03:52', '', 'Pending'),
(6, 'RES003', 'Campus Cafe', 'campus@buddybites.com', '$2y$10$4hLUnJoIk2mSmM04ayXmqeYDUeBNeHPmNeDQTjRg9AfAVM4Q8R46S', 'restaurant', '2026-06-27 08:04:57', '', 'Pending'),
(7, 'RID001', 'Ahmad Rider', 'rider1@gmail.com', '$2y$10$jzqnCVnTFn1hqqKPdwF.CeSLuKFoM22.yRJVSg6WGEOLpzvR0yyka', 'rider', '2026-06-27 11:10:57', '', 'Approved'),
(8, 'RES004', 'Chicken Corner', 'chickencorner@gmail.com', '$2y$10$ed9qTPTGwVAMUXxY87la0er/857DrDDn5/tKwNIq3A7SKd/aCoC..', 'restaurant', '2026-06-27 13:07:06', '', 'Pending'),
(9, 'STU003', 'Diana Edwin', 'dianaedwin@gmail.com', '$2y$10$JI7aHDQ0qb/zeDNuWu1oquoXItLuMkXEccKzSZPXlMNuExFJfMlEm', 'student', '2026-06-28 02:03:48', '', 'Pending'),
(10, 'STU004', 'Muhammad Hazim', 'hazim@gmail.com', '$2y$10$8twzviJlw.mzw0vBAfJUlOXs4iNuJg6.z3drjlBexJ9gDjueEq.N2', 'student', '2026-06-28 02:08:22', '', 'Pending'),
(15, 'STU005', 'Sherie Nuradzza', 'sherie@gmail.com', '$2y$10$gcwGkrsSHvECAeou9YRioeKrAv9qBDFfDV/.c1zjavfUsq7UofuCK', 'student', '2026-06-28 09:58:46', '', 'Pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `custom_meals`
--
ALTER TABLE `custom_meals`
  ADD PRIMARY KEY (`custom_id`);

--
-- Indexes for table `group_cart_items`
--
ALTER TABLE `group_cart_items`
  ADD PRIMARY KEY (`group_cart_id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `group_orders`
--
ALTER TABLE `group_orders`
  ADD PRIMARY KEY (`group_id`),
  ADD UNIQUE KEY `group_code` (`group_code`);

--
-- Indexes for table `menu_feedback`
--
ALTER TABLE `menu_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD UNIQUE KEY `unique_user_item` (`user_id`,`item_id`),
  ADD KEY `idx_menu_feedback_item` (`item_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`restaurant_id`),
  ADD UNIQUE KEY `restaurant_name` (`restaurant_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_code` (`user_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `custom_meals`
--
ALTER TABLE `custom_meals`
  MODIFY `custom_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `group_cart_items`
--
ALTER TABLE `group_cart_items`
  MODIFY `group_cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `group_orders`
--
ALTER TABLE `group_orders`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu_feedback`
--
ALTER TABLE `menu_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `restaurant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
