-- ========================================================
-- QR CODE DIGITAL MENU SYSTEM - COMPLETE DATABASE SCHEMA
-- ========================================================
-- For XAMPP localhost environment
-- Import via phpMyAdmin or: mysql -u root -p restaurant_menu < database.sql
-- ========================================================

-- Restaurant menu database dump
-- Generated: Jun 16, 2026

CREATE DATABASE IF NOT EXISTS `restaurant_menu`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `restaurant_menu`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_menu`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'utensils',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Drinks', 'Refreshing beverages, juices, smoothies, and hot drinks', 'images/category-drinks.jpg', 'glass-water', 1, 1, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(2, 'Desserts', 'Sweet treats, cakes, ice cream, and pastries', 'images/category-desserts.jpg', 'cake', 2, 1, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(3, 'Main Meals', 'Hearty main courses, steaks, seafood, and pasta', 'images/category-main-meals.jpg', 'chef-hat', 3, 1, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(4, 'Fast Food', 'Quick bites, burgers, tacos, wings, and fries', 'images/category-fast-food.jpg', 'sandwich', 4, 1, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(5, 'Local Dishes', 'Traditional and cultural specialties', 'images/category-local-dishes.jpg', 'soup', 5, 1, '2026-06-15 11:01:20', '2026-06-15 11:01:20');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `long_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `allergens` varchar(255) DEFAULT NULL,
  `preparation_time` int(11) DEFAULT 15,
  `calories` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_spicy` tinyint(1) DEFAULT 0,
  `is_vegetarian` tinyint(1) DEFAULT 0,
  `is_vegan` tinyint(1) DEFAULT 0,
  `is_gluten_free` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `long_description`, `price`, `image`, `ingredients`, `allergens`, `preparation_time`, `calories`, `is_available`, `is_featured`, `is_spicy`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `view_count`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 3, 'Grilled Salmon Fillet', 'Premium Atlantic salmon with lemon butter sauce', 'Fresh Atlantic salmon fillet, perfectly grilled and served with asparagus, cherry tomatoes, and our signature lemon butter sauce.', '24.99', 'images/dish-grilled-salmon.jpg', 'Salmon, Butter, Lemon, Asparagus, Cherry Tomatoes', 'Fish, Dairy', 25, 480, 1, 1, 0, 0, 0, 0, 15, 0, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(2, 3, 'Chicken Alfredo Pasta', 'Creamy fettuccine with grilled chicken strips', 'Fettuccine pasta tossed in rich Alfredo sauce with Parmesan cheese, topped with perfectly grilled chicken breast strips.', '18.99', 'images/dish-chicken-alfredo.jpg', '0', 'Gluten, Dairy', 20, 720, 1, 1, 0, 0, 0, 0, 14, 0, '2026-06-15 11:01:20', '2026-06-16 17:58:08'),
(3, 3, 'Grilled Chicken Breast', 'Healthy grilled chicken with roasted vegetables', 'Juicy grilled chicken breast seasoned with herbs, served with roasted baby potatoes and fresh green beans.', '19.99', 'images/dish-grilled-chicken.jpg', 'Chicken Breast, Baby Potatoes, Green Beans, Olive Oil', 'None', 22, 520, 1, 0, 0, 0, 0, 0, 7, 0, '2026-06-15 11:01:20', '2026-06-16 12:53:13'),
(4, 4, 'Classic Cheeseburger', 'Juicy beef patty with melted cheddar cheese', 'Our signature beef patty topped with melted cheddar cheese, fresh lettuce, tomato, pickles, and special sauce on a toasted sesame bun.', '14.99', 'images/dish-cheeseburger.jpg', 'Beef Patty, Cheddar Cheese, Lettuce, Tomato, Pickles', 'Gluten, Dairy', 15, 850, 1, 1, 0, 0, 0, 0, 14, 0, '2026-06-15 11:01:20', '2026-06-15 14:16:39'),
(5, 4, 'Buffalo Chicken Wings', 'Crispy wings with spicy buffalo sauce', 'Crispy fried chicken wings tossed in our signature spicy buffalo sauce. Served with celery sticks and ranch dressing.', '16.99', 'images/dish-chicken-wings.jpg', 'Chicken Wings, Buffalo Sauce, Flour, Celery', 'None', 18, 650, 1, 0, 1, 0, 0, 0, 9, 0, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(6, 4, 'Beef Tacos', 'Three crispy tacos with seasoned ground beef', 'Three crispy corn tortilla tacos filled with seasoned ground beef, shredded lettuce, diced tomatoes, cheddar cheese.', '13.99', 'images/dish-beef-tacos.jpg', 'Corn Tortillas, Ground Beef, Lettuce, Tomato, Cheese', 'Gluten, Dairy', 12, 580, 1, 0, 1, 0, 0, 0, 7, 0, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(7, 2, 'Chocolate Lava Cake', 'Warm molten chocolate center with ice cream', 'Decadent chocolate cake with a warm, gooey molten center. Served with vanilla ice cream and fresh raspberries.', '12.99', 'images/dish-lava-cake.jpg', 'Dark Chocolate, Butter, Eggs, Sugar, Flour', 'Eggs, Gluten, Dairy', 18, 450, 1, 1, 0, 0, 0, 0, 12, 0, '2026-06-15 11:01:20', '2026-06-15 14:14:59'),
(8, 2, 'Strawberry Cheesecake', 'Creamy cheesecake with strawberry topping', 'Classic New York-style cheesecake on a graham cracker crust, topped with fresh strawberry sauce.', '11.99', 'images/dish-strawberry-cheesecake.jpg', 'Cream Cheese, Sugar, Eggs, Graham Crackers, Strawberries', 'Gluten, Dairy, Eggs', 15, 380, 1, 1, 0, 0, 0, 0, 8, 0, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(9, 2, 'Classic Tiramisu', 'Italian coffee-flavored dessert', 'Traditional Italian tiramisu with layers of coffee-soaked ladyfingers and rich mascarpone cream.', '13.99', 'images/dish-tiramisu.jpg', 'Mascarpone Cheese, Espresso, Ladyfingers, Eggs, Sugar', 'Eggs, Gluten, Dairy', 20, 420, 1, 1, 0, 0, 0, 0, 7, 0, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(10, 1, 'Fresh Orange Juice', 'Freshly squeezed orange juice', '100% freshly squeezed orange juice, packed with Vitamin C. Served chilled over ice.', '6.99', 'images/dish-orange-juice.jpg', 'Fresh Oranges, Ice', 'None', 5, 120, 1, 0, 0, 0, 0, 0, 8, 0, '2026-06-15 11:01:20', '2026-06-15 14:16:03'),
(11, 1, 'Iced Caramel Latte', 'Espresso with milk and caramel over ice', 'Rich espresso combined with creamy milk and sweet caramel sauce, poured over ice.', '7.99', 'images/dish-iced-latte.jpg', 'Espresso, Milk, Caramel Sauce, Ice', 'Dairy', 8, 280, 1, 1, 0, 0, 0, 0, 8, 0, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(12, 1, 'Mango Smoothie Bowl', 'Thick tropical smoothie with toppings', 'Creamy mango smoothie base topped with fresh mango slices, crunchy granola, coconut flakes.', '9.99', 'images/dish-mango-smoothie.jpg', 'Mango, Banana, Coconut Milk, Granola, Chia Seeds', 'None', 10, 340, 1, 1, 0, 0, 0, 0, 6, 0, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(13, 5, 'Margherita Pizza', 'Classic wood-fired pizza with fresh basil', 'Traditional Italian Margherita pizza with San Marzano tomato sauce, fresh mozzarella, and basil.', '15.99', 'images/dish-margherita-pizza.jpg', 'Pizza Dough, Tomato Sauce, Fresh Mozzarella, Basil', 'Gluten, Dairy', 20, 700, 1, 1, 0, 0, 0, 0, 10, 0, '2026-06-15 11:01:20', '2026-06-15 11:01:20'),
(14, 1, 'coke', '', '', '100.00', '', '0', '', 15, NULL, 1, 0, 0, 0, 0, 0, 1, 0, '2026-06-16 14:27:07', '2026-06-16 14:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `menu_views`
--

CREATE TABLE `menu_views` (
  `id` int(11) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_views`
--

INSERT INTO `menu_views` (`id`, `menu_item_id`, `category_id`, `ip_address`, `viewed_at`) VALUES
(1, 7, 2, '127.0.0.1', '2026-06-15 14:14:59'),
(2, 10, 1, '127.0.0.1', '2026-06-15 14:15:25'),
(3, 4, 4, '127.0.0.1', '2026-06-15 14:15:35'),
(4, 10, 1, '127.0.0.1', '2026-06-15 14:16:03'),
(5, 4, 4, '127.0.0.1', '2026-06-15 14:16:39'),
(6, 2, 3, '127.0.0.1', '2026-06-15 14:20:57'),
(7, 2, 3, '127.0.0.1', '2026-06-15 15:19:43'),
(8, 2, 3, '98.97.76.16', '2026-06-16 12:53:00'),
(9, 3, 3, '98.97.76.16', '2026-06-16 12:53:13'),
(10, 14, 1, '98.97.76.16', '2026-06-16 14:28:31'),
(11, 2, 3, '105.112.106.231', '2026-06-16 17:58:00'),
(12, 2, 3, '105.112.106.231', '2026-06-16 17:58:01'),
(13, 2, 3, '105.112.106.231', '2026-06-16 17:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `table_number` varchar(20) DEFAULT NULL,
  `table_uid` varchar(64) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT 'pay_on_receipt',
  `payment_status` varchar(20) DEFAULT 'pending',
  `tracking_id` varchar(20) DEFAULT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `total` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `qr_type` enum('restaurant','table','category') NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `table_number` int(11) DEFAULT NULL,
  `qr_image` varchar(255) DEFAULT NULL,
  `qr_data` text NOT NULL,
  `scan_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_logs`
--

CREATE TABLE `search_logs` (
  `id` int(11) NOT NULL,
  `search_term` varchar(255) NOT NULL,
  `results_count` int(11) DEFAULT 0,
  `searched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`) VALUES
(1, 'restaurant_name', 'Gourmet Bites Restaurant', 'text', 'Restaurant display name'),
(2, 'restaurant_tagline', 'Savor Every Bite', 'text', 'Tagline'),
(3, 'restaurant_address', '123 Culinary Avenue, Food District', 'text', 'Address'),
(4, 'restaurant_phone', '+1 (555) 123-4567', 'text', 'Phone'),
(5, 'restaurant_email', 'info@gourmetbites.com', 'text', 'Email'),
(6, 'currency_symbol', '$', 'text', 'Currency symbol'),
(7, 'currency_code', 'USD', 'text', 'Currency code'),
(8, 'tax_rate', '8.5', 'number', 'Tax rate %'),
(9, 'theme_color', '#dc3545', 'text', 'Primary theme color'),
(10, 'enable_dark_mode', '1', 'boolean', 'Allow dark mode'),
(11, 'enable_search', '1', 'boolean', 'Enable search'),
(12, 'items_per_page', '12', 'number', 'Items per page'),
(13, 'allow_analytics', '1', 'boolean', 'Track views'),
(14, 'bank_name', 'Your Bank Name', 'text', 'Bank name shown on payment page'),
(15, 'bank_account_number', '0000000000', 'text', 'Account number shown on payment page'),
(16, 'bank_account_name', 'Gourmet Bites Restaurant', 'text', 'Account holder name on payment page');

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `table_number` varchar(20) NOT NULL,
  `table_uid` varchar(64) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `qr_image` text DEFAULT NULL,
  `qr_data` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','manager') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 1, '2026-06-16 05:34:37', '2026-06-15 11:01:19'),
(2, 'manager', 'manager@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Restaurant Manager', 'manager', 1, NULL, '2026-06-15 11:01:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `menu_views`
--
ALTER TABLE `menu_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_item_id` (`menu_item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_id` (`tracking_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `search_logs`
--
ALTER TABLE `search_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_uid` (`table_uid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `menu_views`
--
ALTER TABLE `menu_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `search_logs`
--
ALTER TABLE `search_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_views`
--
ALTER TABLE `menu_views`
  ADD CONSTRAINT `menu_views_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `menu_views_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
