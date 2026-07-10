-- SQL Schema for Learts Handmade Store database
CREATE DATABASE IF NOT EXISTS `learts_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `learts_db`;

-- Admins table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-insert default admin (username: admin, password: admin123)
INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$W5SgYTe63t5qwwq05kYRpebjQioK2gt/cS695RIxn75YISTCbtF1q')
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`);

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-insert categories
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Gift ideas'),
(2, 'Home Decor'),
(3, 'Kids & Babies'),
(4, 'Kitchen'),
(5, 'Knitting & Sewing')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `stock` INT NOT NULL,
  `category_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-insert 12 products referencing the downloaded images
INSERT INTO `products` (`id`, `name`, `description`, `price`, `image_url`, `stock`, `category_id`) VALUES
(1, 'Boho Beard Mug', 'A beautiful rustic ceramic mug featuring a bohemian beard design. Perfect for your morning coffee or tea. Handcrafted with clay and finished with a durable, food-safe glaze.', 39.00, 'assets/images/product/s328/product-1.webp', 15, 4),
(2, 'Motorized Tricycle', 'A vintage-style decorative wooden motorized tricycle model. Hand-carved from teak wood by master craftsmen. A wonderful accent piece for kids rooms or display shelves.', 35.00, 'assets/images/product/s328/product-2.webp', 8, 3),
(3, 'Walnut Cutting Board', 'Heavy-duty cutting board handcrafted from premium American Walnut wood. Sanded smooth and treated with natural mineral oils. Features side handles for easy carrying.', 100.00, 'assets/images/product/s328/product-3.webp', 10, 4),
(4, 'Pizza Plate Tray', 'Handcrafted wooden pizza serving tray, complete with pre-cut grooves for perfect slicing. Elegant kitchen accessory that doubles as a rustic charcuterie board.', 22.00, 'assets/images/product/s328/product-4.webp', 25, 4),
(5, 'Abstract Folded Pots', 'Contemporary ceramic pots with an abstract folded design. Ideal for succulents, small plants, or as unique standalone decorative accents. Hand-molded and fired at high temperatures.', 50.00, 'assets/images/product/s328/product-5.webp', 12, 2),
(6, 'Brush & Dustpan Set', 'Eco-friendly cleaning set featuring a beechwood hand brush with natural bristles and a sturdy metal dustpan. Practical yet aesthetically pleasing home accessory.', 9.00, 'assets/images/product/s328/product-6.webp', 30, 4),
(7, 'Boho Wall Hanging', 'A beautiful, hand-knotted macrame wall hanging. Made from 100% natural cotton cord on a rustic wooden dowel. Adds texture and warmth to any living space.', 45.00, 'assets/images/product/s328/product-7.webp', 5, 2),
(8, 'Scented Soy Candle', 'Hand-poured soy wax candle infused with lavender and amber essential oils. Comes in a reusable handmade ceramic jar with a wooden wick that crackles softly when lit.', 12.50, 'assets/images/product/s328/product-8.webp', 40, 2),
(9, 'Leather Journal Diary', 'Handbound diary featuring a genuine leather cover with an embossed compass design. Contains 200 pages of handmade, unlined parchment paper. Secured with a leather wrap cord.', 32.00, 'assets/images/product/s328/product-9.webp', 18, 1),
(10, 'Woven Tote Bag', 'Spacious beach and shopping tote bag handwoven from sustainable seagrass straw. Features comfortable leather shoulder straps and a fabric inner lining with a zipper.', 24.00, 'assets/images/product/s328/product-10.webp', 14, 1),
(11, 'Wool Knit Scarf', 'Super soft, warm scarf hand-knitted from 100% alpaca wool. Features a classic cable knit pattern in a versatile oatmeal color. Perfect for chilly autumn and winter days.', 35.00, 'assets/images/product/s328/product-11.webp', 7, 1),
(12, 'Beaded Flower Bracelet', 'Delicate handmade bracelet made with high-quality colorful glass seed beads arranged in flower patterns. Adjustable chain closure fits most wrist sizes.', 8.00, 'assets/images/product/s328/product-12.webp', 50, 1)
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`), 
  `description` = VALUES(`description`), 
  `price` = VALUES(`price`), 
  `image_url` = VALUES(`image_url`), 
  `stock` = VALUES(`stock`), 
  `category_id` = VALUES(`category_id`);

-- Orders table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `customer_email` VARCHAR(100) NOT NULL,
  `customer_address` TEXT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pending', 'Processing', 'Completed', 'Cancelled') DEFAULT 'Pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
