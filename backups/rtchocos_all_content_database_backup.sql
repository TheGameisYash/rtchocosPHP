-- ========================================================
-- RT Chocos: Full Content Database Backup
-- Tables: products, product_categories, blogs, blog_tags, blog_tag_map, media, site_settings, faqs, ai_ingredient_spotlights, ai_insights, ai_class_facts
-- Generated: 2026-09-28 19:42:37
-- Host: srv1875.hstgr.io | Database: u219698334_RTchocos
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `long_description` longtext DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'General',
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image_main` varchar(255) DEFAULT NULL,
  `image_gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image_gallery`)),
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_active_featured` (`is_active`,`is_featured`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `products` (11 rows)
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (6, 'almond-halwa-spread', 'Almond Halwa Spread', 'The richness of Indian almond halwa, reinvented into a clean, creamy and versatile spread made with minimal ingredients for pure, modern indulgence.', 'India’s beloved almond halwa, reinvented as a clean, luxurious spread.

Made with minimal, carefully chosen ingredients, this smooth and naturally nutty spread brings together the richness of almonds and the delicate warmth of cardamom without unnecessary additives. Clean-label, wholesome and crafted for modern indulgence, it is as versatile as it is delicious.

Spread it on toast, croissants and pancakes, swirl it into desserts, or use it to create bonbon fillings, chocolate confections and pastry creations.

A traditional favourite. A modern innovation. One irresistibly versatile jar.', '350.00', '299.00', 'Spreads & Nut Butters', 50, 'assets/products/prod-almond-halwa-spread-1790077418.png', '[]', 'Almond Halwa Spread | Clean Label | RT Chocos | Innovative recipe', 'Discover Almond Halwa Spread by RT Chocos—a creamy, nutty, clean-label indulgence made with minimal ingredients. Perfect for toast, desserts & bonbons.', 'almond halwa spread, almond spread, halwa spread, Indian almond spread, clean label spread, gourmet almond spread, almond spread India, RT Chocos, Innovative recipe, bestdiwaligifting, corporategifting,', 1, 1, '2026-09-05 09:07:25', '2026-09-22 11:43:38');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (7, 'cashew-coconut-spread', 'Cashew Coconut Spread', 'A silky-smooth blend of lightly roasted cashews and coconut, stone-ground in small batches for a rich, creamy texture. Clean, indulgent and endlessly versatile from toast and smoothies to desserts, pastries and bonbons.', 'A tropical indulgence, crafted in small batches.

Lightly roasted whole cashews and coconut are slowly stone-ground in small batches, creating an exceptionally silky, creamy texture with rich cashew notes and a delicate coconut finish. Made with minimal ingredients, with no palm oil or artificial preservatives.

Perfect on toast, croissants and fresh fruit, or elevate smoothie bowls, dessert frostings, cakes, pastries, bonbon fillings and chocolate creations.

Slow-crafted. Silky-smooth. Naturally indulgent.', '390.00', '360.00', 'Spreads & Nut Butters', 45, 'assets/products/prod-cashew-coconut-spread-1790015793.png', '[]', 'Cashew Coconut Spread | Innovative  Recipe | Gourmet Diwali & Corporate Gifts', 'Discover RT Chocos Cashew Coconut Spread, stone-ground in small batches for a silky texture. Perfect for toast, desserts, bonbons, Diwali & corporate gifting.', 'cashew coconut spread, cashew spread, cashew butter, coconut spread, gourmet nut butter, clean label spread, premium Diwali gifts, Diwali gifting, corporate gifting, corporate gifts India, gourmet gifting, premium food gifts, RT Chocos', 1, 1, '2026-09-05 09:07:25', '2026-09-21 18:36:33');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (9, 'almond-spread', 'Crunchy Almond Spread', 'A clean-label, minimally processed almond spread, stone-ground in small batches from premium almonds for a rich, creamy texture and irresistible crunch. Perfect for everyday indulgence, desserts, bonbons, and premium Diwali & corporate gifting.', 'Pure almond indulgence, crafted with a delicious crunch. Made from premium almonds and minimal ingredients, this clean-label, minimally processed spread is stone-ground and crafted in small batches for exceptional creaminess with an irresistible almond crunch.

Perfect for toast, croissants, desserts, pastries and bonbons and beautifully suited for premium Diwali and corporate gifting.

Clean. Crafted. Crunchy. Unmistakably indulgent.', '360.00', '325.00', 'Spreads & Nut Butters', 70, 'assets/products/prod-almond-spread-1790015745.png', '[]', 'Crunchy Almond Spread | Premium Nut Butter | RT Chocos', 'RT Chocos Crunchy Almond Spread ,premium almonds, stone-ground in small batches for creamy richness and crunch. A perfect choice for Diwali & corporate gifting.', 'crunchy almond spread, almond spread, almond butter, premium almond spread, almond spread India, clean label spread, stone ground almond spread, gourmet nut butter, Diwali gifting, Diwali gifts, Diwali corporate gifting, corporate gifting, corporate gifts India, premium corporate gifts, gourmet Diwali gifts, festive gifting, premium food gifts, RT Chocos', 1, 1, '2026-09-05 09:07:25', '2026-09-21 18:35:45');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (14, 'dates-powder', 'Dates Powder', 'Natural dates transformed into a versatile powder with rich, caramel-like notes.', '100% premium sun-ripened dates, dehydrated and micro-milled into a fine, nutrient-rich natural sweetener. A diabetic-friendly, high-fiber substitute for refined sugar.', '250.00', '220.00', 'Ingredients', 90, 'assets/products/dates_powder.jpg', NULL, NULL, NULL, NULL, 1, 1, '2026-09-05 09:07:25', '2026-09-05 09:07:25');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (15, 'handcrafted-chocolates', 'Handcrafted Chocolates', 'Artisan chocolates designed for celebrations, gifting and special occasions.', 'A curated box of 12 hand-painted ganache and praline bonbons. Flavours include Salted Caramel, Hazelnut Gianduja, Dark Espresso, and Cardamom Truffle.', '850.00', '799.00', 'Corporate & Gifting', 35, 'assets/products/handcrafted_chocolates.jpg', NULL, NULL, NULL, NULL, 1, 1, '2026-09-05 09:07:26', '2026-09-05 09:07:26');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (17, 'customized-chocolate-inclusion-bar', 'Customized Chocolate Inclusion Bar', 'raft your own chocolate experience. Our Customized Inclusion Bars combine fine chocolate with your choice of nuts, fruits, spices, seeds and other real ingredients crafted to your taste, occasion and vision.', 'Chocolate, made your way. Create a bespoke inclusion bar with fine chocolate and carefully selected ingredients of your choice. From nuts and dried fruits to seeds, spices and unique inclusions, every bar is crafted to make your idea deliciously distinctive.', '180.00', '150.00', 'Chocolates', 50, 'assets/products/custom_branding_showcase.jpg', '[]', 'Customized Chocolate Inclusion Bar | RT Chocos', 'Choose from a wide range of nuts, fruits, seeds, spices and other real ingredients to create a bespoke inclusion bar, crafted with fine chocolate and RT Chocos\' chocolate expertise.', 'customized chocolate bar, custom chocolate bar, chocolate inclusion bar, customized chocolate, personalized chocolate bar, inclusion chocolate, custom chocolate India, artisan chocolate India, custom chocolate gifting, corporate chocolate gifting', 0, 1, '2026-09-14 18:14:09', '2026-09-15 19:29:32');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (18, 'hazelnut-spread-clean-label-product-healthy-gifting', 'Hazelnut Spread | Clean Label product | Healthy Gifting', 'Luxuriously roasted hazelnuts, stone-ground to velvety perfection. Rich, refined and irresistibly smooth made for spreading, creating and gifting.', 'A rich, velvety spread with the unmistakable warmth of roasted hazelnuts and a beautifully lingering finish. Made with premium ingredients, minimal processing and small-batch stone grinding for an exceptionally smooth mouthfeel.

Sublime on toast and croissants, decadent in cakes, pastries, ice cream and desserts, and effortlessly indulgent in bonbons, pralines and chocolate fillings.

A little jar of hazelnut luxury made for spreading, creating and gifting.', '350.00', '299.00', 'Spreads', 50, 'assets/products/prod-hazelnut-spread-clean-label-product-healthy-gifting-1790015567.png', '[]', 'Hazelnut Spread | Premium Diwali & Corporate Gifting | RT Chocos', 'Discover RT Chocos Hazelnut Spread premium roasted hazelnuts, stone-ground in small batches for silky richness. Perfect for desserts, bonbons, Diwali & corporate gifting.', 'hazelnut spread, hazelnut butter, premium hazelnut spread, hazelnut spread India, gourmet hazelnut spread, clean label hazelnut spread, stone ground hazelnut spread, premium nut butter, Diwali gifting, Diwali gifts, corporate gifting, corporate gifts India, premium corporate gifts, gourmet gifting, premium food gifts, RT Chocos', 0, 1, '2026-09-14 18:36:32', '2026-09-21 18:32:47');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (19, 'gourmet-stuffed-dates-gift-box', 'Gourmet Stuffed Dates Gift Box', 'Plump, naturally sweet dates handcrafted with indulgent fillings and premium nuts-an elegant, wholesome treat made for festive celebrations and thoughtful gifting.', 'A little box of natural sweetness, beautifully handcrafted.

Premium dates are carefully selected, delicately stuffed and finished with an assortment of premium nuts and artisanal toppings, creating a luxurious bite with contrasting textures and flavours. Naturally rich and satisfying, each piece brings together the deep caramel-like sweetness of dates with the richness and crunch of carefully chosen ingredients.

Beautifully presented in an elegant gift box, it makes a sophisticated choice for Diwali gifting, corporate gifting, festive hampers and premium celebrations.

Enjoy them as an indulgent snack, serve alongside coffee or tea, or add them to dessert platters and festive spreads.

Naturally indulgent. Handcrafted with care. Made to be gifted.', '500.00', '450.00', 'Chocolates', 50, 'assets/products/prod-gourmet-stuffed-dates-gift-box-1790015134.png', '[]', 'Gourmet Stuffed Dates | Diwali & Corporate Gifting | RT Chocos  SEO Meta Description', 'Discover premium stuffed dates crafted with indulgent fillings and nuts. An elegant choice for Diwali gifting, corporate gifts, festive hampers and celebrations.', 'stuffed dates, gourmet stuffed dates, stuffed dates gift box, premium dates gift box, stuffed dates India, gourmet dates, luxury dates gift, Diwali gifting, Diwali gifts, Diwali gift hampers, corporate gifting, corporate gifts India, premium corporate gifts, gourmet gifting, festive gifting, premium food gifts, luxury gifting, RT Chocos', 0, 1, '2026-09-14 18:40:26', '2026-09-21 18:25:34');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (20, 'the-rocher-collection', 'The Rocher Collection', 'Handcrafted chocolate Rochers in two irresistible creations Almond and Mixed Fruit made for indulgence, celebration and elegant gifting.', 'The Rocher Collection brings together two handcrafted favourites Almond Rocher with roasted almonds and Mixed Fruit Rocher with a delightful blend of fruits and nuts, all enveloped in rich chocolate.

Made in small batches with premium ingredients, these elegant Rochers are perfect for festive celebrations, sharing and premium Diwali & corporate gifting.', '300.00', '250.00', 'Chocolates', 50, 'assets/products/prod-the-rocher-collection-1790015004.png', '[]', 'Rocher Chocolate Collection | Diwali & Corporate Gifting', 'Discover RT Chocos Rocher Collection handcrafted Almond & Mixed Fruit Rochers made with premium ingredients. Perfect for Diwali gifting, corporate gifts & celebrations.', 'rocher chocolate, chocolate rocher, rocher collection, almond rocher, mixed fruit rocher, premium chocolates, gourmet chocolate gifting, chocolate gift box, Diwali gifting, Diwali gifts, Diwali chocolate gifts, corporate gifting, corporate gifts India, premium corporate gifts, corporate chocolate gifts, festive gifting, luxury chocolate gifts, RT Chocos', 0, 1, '2026-09-15 10:54:08', '2026-09-21 18:23:24');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (21, 'almond-bark', 'Almond Bark', 'Premium chocolate meets generously roasted almonds in this elegant, indulgent bark—rich, crisp and irresistibly satisfying. Perfect for gifting, sharing, or elevating your festive celebrations.', 'A beautifully crafted chocolate bark studded generously with premium roasted almonds, bringing together the smoothness of fine chocolate and the irresistible crunch of perfectly roasted nuts. Each bite offers a delicious contrast of silky chocolate, deep roasted flavour and satisfying texture.

Perfect for gifting, festive indulgence and everyday chocolate cravings—a thoughtful addition to Diwali hampers and premium corporate gifting.', '420.00', '390.00', 'Chocolates', 100, 'assets/products/prod-almond-bark-1790015356.png', '[]', 'Almond Bark Chocolate | Diwali & Corporate Gifting | RT Chocos', 'Discover RT Chocos Almond Bark—premium chocolate generously layered with roasted almonds for a rich, satisfying crunch. Perfect for Diwali & corporate gifting.', 'almond bark, almond bark chocolate, chocolate almond bark, premium almond chocolate, almond chocolate India, gourmet chocolate, premium chocolates India, Diwali gifting, Diwali gifts, corporate gifting, corporate gifts India, premium corporate gifts, chocolate gifting, gourmet Diwali gifts, festive chocolate gifts, premium food gifts, RT Chocos', 1, 1, '2026-09-15 19:31:02', '2026-09-21 18:29:16');
INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (25, 'classic-almond-spread', 'Classic Almond Butter Spread', 'Classic, creamy and crafted around the richness of premium slow-roasted almonds.', 'Stone-ground for 24 hours to achieve an ultra-smooth, creamy texture. Made solely with high-grade California almonds and a touch of sea salt. Free from hydrogenated oils and artificial preservatives.', '350.00', '325.00', 'Spreads & Nut Butters', 100, 'assets/products/prod-classic-almond-spread-1790078880.png', '[]', 'Classic Almond Spread | Premium Nut Butter | RT Chocos', 'Discover RT Chocos Classic Almond Spread made with premium almonds and minimal ingredients for rich, creamy indulgence. Perfect for toast, desserts & gifting.', 'classic almond spread, almond spread, almond butter, premium almond spread, almond butter India, gourmet almond spread, clean label almond spread, premium nut butter, almond spread India, Diwali gifting, Diwali gifts, corporate gifting, corporate gifts India, premium corporate gifts, gourmet gifting, RT Chocos', 0, 1, '2026-09-15 19:31:02', '2026-09-22 12:08:00');

-- --------------------------------------------------------
-- Table structure for `product_categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `product_categories` (10 rows)
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (1, 'Chocolates', 'chocolates', NULL, '2026-09-04 16:23:50', '2026-09-04 16:23:50');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (2, 'Bonbons', 'bonbons', NULL, '2026-09-04 16:23:50', '2026-09-04 16:23:50');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (3, 'Cacao', 'cacao', NULL, '2026-09-04 16:23:50', '2026-09-04 16:23:50');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (5, 'Gifting', 'gifting', NULL, '2026-09-04 16:23:51', '2026-09-04 16:23:51');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (6, 'Spreads', 'spreads', NULL, '2026-09-04 16:23:51', '2026-09-04 16:23:51');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (8, 'Vegan Truffles', 'vegan-truffles', '', '2026-09-04 16:28:46', '2026-09-04 16:28:46');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (9, 'Uncategorized', 'uncategorized', NULL, '2026-09-04 16:29:59', '2026-09-04 16:29:59');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (10, 'Spreads & Nut Butters', 'spreads-nut-butters', NULL, '2026-09-05 09:07:24', '2026-09-05 09:07:24');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (12, 'Ingredients', 'ingredients', NULL, '2026-09-05 09:07:24', '2026-09-05 09:07:24');
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES (13, 'Corporate & Gifting', 'corporate-gifting', NULL, '2026-09-05 09:07:24', '2026-09-05 09:07:24');

-- --------------------------------------------------------
-- Table structure for `blogs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Science',
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `body_class` varchar(100) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `read_time` varchar(50) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `blogs` (14 rows)
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (1, 'cocoa-ph', 'Why pH is the Most Underrated Factor in Cocoa Powder', 'Science', 'How cocoa powder pH shapes colour, flavour, leavening, solubility and flavanol retention in chocolate work.', 'Most people pick up a packet of cocoa powder without a second thought. Dark or light. Branded or generic. But there is one number printed nowhere on the label that quietly controls everything: the colour of your ganache, the rise of your cake, the depth of your hot chocolate, and even the health benefits in every sip.

That number is pH.

## Let\'s Start Simple: What Is pH?

pH is a scale from 0 to 14 that measures how acidic or alkaline a substance is.

Below 7 = acidic. 7 = neutral. Above 7 = alkaline.

Pure water sits at 7. Lemon juice sits around 2. Cocoa powder, depending on how it is processed, can sit anywhere between pH 5 and pH 8. That 3-point difference sounds small. In practice, it changes everything.

## Why Does Cocoa Have a pH at All?

It starts at the bean. Fresh cacao beans are naturally acidic because fermentation produces acetic acid, lactic acid and other organic acids. These acids are part of what creates the fruity, complex flavour notes associated with fine chocolate.

When the beans are roasted, dried and processed into cocoa powder, much of that acidity remains. The result is natural cocoa powder: acidic, light brown and flavour-forward.

Then came Dutch processing. Cocoa is treated with an alkaline solution, commonly potassium carbonate, to neutralise those natural acids. That produces Dutch-process cocoa powder: darker, smoother and dramatically different in behaviour.

## Natural Cocoa vs Dutch-Process Cocoa

**Natural cocoa powder** typically sits around pH 5.0 to 6.5. It is lighter in colour, sharper in flavour and keeps more of cocoa\'s natural aromatic complexity.

**Dutch-process cocoa powder** usually sits around pH 7.0 to 8.5. It is deeper in colour, smoother in taste and less acidic.

**Black cocoa** pushes even further, often between pH 8.0 and 9.0. It delivers dramatic colour, but very little natural cocoa complexity.

## Six Things pH Actually Controls

**1. Colour.** As pH rises, the pigments in cacao shift from reddish-brown to deep brown and eventually near black. This is why red velvet recipes rely on natural cocoa, and why black sandwich cookies use heavily alkalised cocoa.

**2. Flavour.** Acidity preserves bright top notes and fruity esters. Alkalisation smooths those edges out. Neither is inherently better, but they serve different purposes.

**3. Baking chemistry.** Natural cocoa is acidic and reacts with baking soda to release carbon dioxide. Dutch-process cocoa has already been neutralised, so it usually pairs with baking powder instead. Swapping them carelessly can flatten a cake or throw off texture.

**4. Solubility and dispersibility.** Dutch-process cocoa wets and disperses more easily in milk or water, which is why it is common in instant drinking chocolate mixes and milkshake powders.

**5. Antioxidant retention.** Cocoa is rich in flavanols, but alkalisation can reduce them heavily. Natural cocoa retains the most. Black cocoa retains the least.

**6. Microbial stability.** Cocoa powder is already low-moisture, but pH still affects how hospitable it is to spoilage organisms. In some formulations, a slightly higher pH can offer modest stability benefits.

## Quick Reference

Natural cocoa: pH 5.0 to 6.5, light brown, tangy, pairs with baking soda, high flavanol retention.

Lightly Dutched: pH 6.5 to 7.5, medium brown, balanced, pairs with baking powder, moderate flavanol retention.

Dark Dutch: pH 7.5 to 8.5, deep brown, earthy and smooth, pairs with baking powder, lower flavanol retention.

Black cocoa: pH 8.5 to 9.0, near black, mild flavour, pairs with baking powder, very low flavanol retention.

## What This Means for Product Development

If you are developing a cake mix, drinking chocolate, protein snack or chocolate coating, pH is not a tiny technical note. It is a formulation choice that affects colour, flavour, leavening, process behaviour and nutrition positioning.

Ask your cocoa supplier for the pH specification before you finalise a recipe. Even a small shift can visibly change the finished product and influence consumer acceptance.

## The Bottom Line

pH is not just a classroom term. In cocoa powder, it is a production decision, a flavour decision, a baking science decision and a nutrition decision all at once.

Natural or Dutch. Acidic or alkaline. Bright or smooth. Light or dark. Every chocolate cake, brownie, ganache and cup of hot chocolate is shaped in part by the pH of the cocoa powder behind it.', 'assets/blogs/cocoa-ph-header-1784800805.jpeg', 'assets/blogs/thumbnails/cocoa-ph-thumb-1784800805.jpeg', NULL, NULL, '7 min', 1, '2026-06-11 06:55:09', '2026-09-28 17:09:48', NULL, 4084);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (2, 'milkfat-chocolate', 'What Really Happens When Milk Fat Enters Chocolate?', 'Science', 'Why milk chocolate behaves so differently from dark, and what milk fat changes at the molecular level.', '<img src=\"../assets/milkfatbloginside.jpg\" alt=\"Milk fat structure inside chocolate\" style=\"width:100%;border-radius:20px;box-shadow:0 18px 50px rgba(59,42,34,0.12);margin:0 0 22px;display:block;\">

## What Really Happens When Milk Fat Enters Chocolate?

**Two fats walk into a bar of chocolate.** Why milk chocolate behaves so differently from dark, and what is actually happening at the molecular level.

Dark chocolate is built on cocoa butter, a remarkably well-organized fat. Three triglycerides, POP, POS, and SOS, dominate its structure and give it that sharp snap and clean 37C melt. Highly symmetrical. Highly predictable.

Milk fat is the opposite. It contains more than 400 different fatty acids across short-chain, medium, and long-chain structures, both saturated and unsaturated. Chaotic by design.

## The Six Forms Problem

Cocoa butter crystallizes into six distinct polymorphs, Forms I to VI. Only Form V gives you the gloss, snap, and melt you want. Everything else is a problem.

When milk fat enters the picture, its irregular molecules insert into the cocoa butter crystal lattice. That reduces packing efficiency and slows down Form V formation. The result is softer, less brittle, creamier chocolate.

## Tempering Tip

**Lower your seed temperature by 1 to 2C for milk chocolate versus dark.** In practice that means around 27 to 28C instead of 28 to 29C.

## Why It Matters for Snap

Milk fat interferes with Form V crystal formation, so it needs a slightly cooler environment to cooperate. That is one reason milk chocolate feels softer and less snappy than dark.

## The Eutectic Effect

Here is the counterintuitive part: mixing two fats can produce a blend that melts at a lower temperature than either fat alone. Milk fat and cocoa butter form a partial eutectic system, which is a major reason milk chocolate melts so readily on the tongue.

## The Hidden Bonus: Phospholipids

Milk fat also brings phospholipids such as phosphatidylcholine and phosphatidylinositol. These amphiphilic molecules naturally coat sugar crystals and cocoa particles in the melt, acting like built-in emulsifiers.

The practical effect is better particle dispersion and a smoother texture, even before lecithin is added. In other words, milk fat can do part of your emulsifier\'s job for free.

## What This Means for Flavor

Fat carries aroma compounds. Because milk fat is more complex, it offers more opportunity for flavor binding. It softens cocoa bitterness, rounds acidity, and contributes its own notes, especially buttery and creamy lactones.

The result is not just a texture change. It is a perception change.', 'assets/blogs/milkfat-chocolate-header-1784800931.jpeg', 'assets/blogs/thumbnails/milkfat-chocolate-thumb-1784800931.jpeg', NULL, NULL, '5 min', 1, '2026-06-11 06:55:09', '2026-09-28 05:51:48', NULL, 74);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (3, 'flavor-chocolate', 'How to Flavor Chocolate Correctly', 'Beginner Guide', 'A practical guide to adding oils, extracts and infusions without seizing, splitting or dulling your chocolate.', 'Chocolate is an unforgiving matrix. Add the wrong type of flavor, at the wrong moment, in the wrong amount, and it either disappears or ruins the batch. Here is what you need to know to get it right every time.

## 1. Always Use Oil-Based Flavors

Chocolate is a fat-continuous system. Oil-based flavors dissolve directly into cocoa butter and disperse cleanly throughout the mass. Water-based or alcohol-based flavors do the opposite: they can seize the chocolate, spike viscosity, and increase bloom risk in the finished product.

The rule is simple: if the flavor carrier is water, propylene glycol, or ethanol, it does not belong in chocolate. Use oil-soluble essential oils, oleoresins, or flavors dissolved in a food-grade triglyceride carrier.

## 2. Add at the Right Temperature

The best window for adding oil-based flavors is post-conching, before tempering, when the chocolate mass is fluid at around 45 to 50C and no longer under aeration. At this stage, the fat is fully mobile, mixing gives even distribution, and volatile loss stays relatively low.

Adding flavor during conching strips many top notes before they ever reach the consumer. Adding during or after tempering risks uneven dispersion as the chocolate thickens.

## 3. Get the Dosage Right

Oil-based flavors are concentrated. Most essential oils and flavor compounds work in chocolate at roughly 0.1% to 0.5% by weight of the finished mass. Start low, taste in the actual chocolate matrix, and adjust.

Dark chocolate can usually carry higher loadings than milk or white chocolate because of its stronger cocoa base. Over-dosing often produces sharpness and chemical off-notes that cannot be corrected later.

## 4. Mix Thoroughly: Dispersion Is Everything

Even a compatible oil-based flavor will create hot spots if mixing is inadequate. Add the flavor while the chocolate is still fluid and allow enough agitation time before tempering. Thick oleoresins should be pre-diluted in a small amount of warm cocoa butter or carrier oil before addition.

This helps the flavor enter as a fine, easily dispersible liquid rather than a dense droplet that resists even distribution.

## 5. Watch Oxidative Stability

Once dispersed in the fat phase, your flavor is exposed to the same oxidative conditions as the cocoa butter. Citrus flavors, especially those high in limonene and other terpenes, are particularly vulnerable and can oxidize into cardboard or turpentine-like off-notes within shelf life.

Use deterpenated or folded citrus oils for better stability. For any flavor-forward product targeting a 6 to 12 month shelf life, run accelerated shelf-life validation before launch, not after a consumer complaint.

## Common Mistakes to Avoid

- **Do not use water-based flavor concentrates.** Even a small amount of free water can cause irreversible textural damage.
- **Do not add flavor during conching.** High temperature and continuous aeration will volatilize much of the flavor before conching ends.
- **Do not taste in carrier oil instead of chocolate.** Flavor perception in cocoa butter at body temperature is different, so validate in the final matrix.
- **Do not skip shelf-life validation.** A flavor that smells perfect on day one can become rancid or chemical by week eight if stability has not been tested.

## The Bottom Line

Use oil-based flavors. Add them at the right moment. Disperse them properly. Validate the shelf life. That is the whole game.', 'assets/blogs/flavor-chocolate-header-1784800898.jpeg', 'assets/blogs/thumbnails/flavor-chocolate-thumb-1784800898.jpeg', NULL, NULL, '6 min', 1, '2026-06-11 06:55:09', '2026-09-27 09:46:33', NULL, 71);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (4, 'fat-bloom-sugar-bloom', 'Fat Bloom vs Sugar Bloom in Chocolate: A Practical Diagnosis Guide', 'Science', 'Learn to diagnose, prevent and fix the two most common chocolate surface defects — with science explained simply.', 'You unwrap a chocolate bar that\'s been sitting in your pantry for a few weeks. Instead of a glossy, inviting surface, you see dull white patches or a dusty grey film. Your first instinct: Has this gone bad?

The answer is almost certainly no. What you\'re looking at is chocolate bloom — one of the most misunderstood surface defects in the chocolate world.

## What Is Chocolate Bloom?

Bloom is a visible white or greyish discolouration on the surface of chocolate. It\'s either fat or sugar that has migrated to the surface and recrystallised — making chocolate look pale, hazy, or dusty instead of smooth and glossy. Bloom is not mould. It is not contamination. It does not make chocolate unsafe to eat.

## Fat Bloom

Fat bloom occurs when stable Form V cocoa butter crystals transform into Form VI over time, or when unstable crystal forms were present from the start due to poor tempering. Liquid cocoa butter migrates to the surface and recrystallises, producing a white-grey haze.

**Common causes:** Poor tempering, temperature fluctuations, incompatible fats from fillings or nut oils, and extended storage.

**How it looks and feels:** Soft, slightly greasy whitish film. Smears when rubbed. Chocolate loses its snap and becomes slightly waxy.

## Sugar Bloom

Sugar bloom is moisture-driven. When the surface is exposed to humidity, a thin film of water dissolves surface sugar. As that moisture evaporates, the sugar recrystallises in rough, irregular crystals that scatter light.

**Common causes:** Humidity exposure, taking cold chocolate out of the fridge into a warm room, inadequate moisture-barrier packaging.

**How it looks and feels:** Dry, rough, gritty, powdery texture. Does not smear when rubbed. Tastes slightly grainy.

## How to Diagnose in Real Life

- **Touch test:** Smooth and greasy = fat bloom. Rough and sandy = sugar bloom.
- **Smear test:** Fat bloom smears under your thumb. Sugar bloom does not respond to pressure.
- **Storage history:** Came from the fridge? Suspect sugar bloom. Stored somewhere warm or made with nut filling? Suspect fat bloom.

## Can Bloomed Chocolate Be Fixed?

**Fat bloom:** Yes — remelt and re-temper properly to restore gloss and snap. Flavour is unaffected.

**Sugar bloom:** Not really — the surface sugar structure is permanently altered. Still perfectly usable in ganaches, baking, or hot chocolate.

## Prevention

For professionals: control tempering precisely, maintain storage at 15–18°C below 50% humidity, manage fat compatibility in filled products, and use moisture-barrier packaging.

For home users: store chocolate in a cool dry place, keep it sealed, never refrigerate unless necessary, and if you must refrigerate, let it return to room temperature while still wrapped before opening.

## The Bottom Line

Bloom is a quality defect, not a safety hazard. For professionals, distinguishing fat from sugar bloom determines whether you fix your tempering process or your storage protocol. For home users — don\'t throw away bloomed chocolate. It\'s still perfectly good.

> \"The white on your chocolate isn\'t mould, and it isn\'t age — it\'s chemistry, asking to be understood.\"', 'assets/blogs/fat-bloom-sugar-bloom-header-1784800954.jpeg', 'assets/blogs/thumbnails/fat-bloom-sugar-bloom-thumb-1784800954.jpeg', 'fat-bloom-article', NULL, '8 min', 1, '2026-06-11 06:55:09', '2026-09-28 13:05:15', NULL, 130);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (5, 'intimacy-chocolate', 'Intimacy Chocolate — What It Is, What\'s In It, and Whether It Actually Works', 'Science', 'A deep, honest guide to intimacy chocolate — the ingredients, the science, the psychology, and the truth behind the fastest-growing niche in functional confectionery.', '## What is intimacy chocolate, really?

Strip away the packaging and branding, and it is simple: high-quality dark chocolate infused with Ayurvedic herbs and natural plant extracts — ingredients that traditional medicine has associated with relaxation, warmth, and vitality for centuries. It is not a drug. It is not a supplement in disguise. At its most honest, it is chocolate designed to create a moment of intentional slowness between two people. The chocolate is the vehicle. The ingredients are the support. The experience is the product.

> \"You are not buying a drug. You are buying a ritual. And the brands that understand this difference are the ones making genuinely good chocolate.\"

## Why dark chocolate itself matters

Before a single herb is added, the chocolate is already doing real work. Dark chocolate (65%+ cacao) is rich in flavonols — plant compounds that improve blood flow and gently lower blood pressure. It contains theobromine, a mild natural stimulant that gives you calm, steady alertness instead of a caffeine-style spike and crash. It also triggers a gentle release of phenylethylamine (the same \"love chemical\" your brain produces during attraction), serotonin, and endorphins. A well-made square of 70% dark chocolate is already a mood-altering food. The herbs layered on top are additions to a base that is already doing something real.

## The ingredients: what goes in, and what each one does

### Adaptogens — stress reduction

**Ashwagandha:** The most important ingredient in the formulation. Used in Ayurveda for over 3,000 years as a rejuvenating tonic, ashwagandha lowers cortisol — the body\'s stress hormone. When cortisol stays high, it quietly kills desire, disrupts sleep, and flattens emotion. Ashwagandha does not \"boost\" anything — it removes the brake that stress puts on your body. Clinical studies have shown meaningful cortisol reduction with daily use over 8–12 weeks.

**Maca Root:** A Peruvian root used for centuries as an energy and fertility food. Unlike ashwagandha, maca works by gently nudging the body\'s hormonal signalling — helping it communicate better without artificially changing hormone levels. The effects are subtle and build over weeks, not hours.

### Ayurvedic tonics — vitality and balance

**Shatavari:** Its Sanskrit name means \"she who possesses a hundred husbands.\" Shatavari is one of Ayurveda\'s most valued herbs for women\'s health, supporting hormonal balance, reproductive wellness, and emotional calm.

**Shilajit:** A dark, mineral-rich resin from Himalayan rock formations containing over 80 trace minerals. It acts as a carrier — helping the body absorb other ingredients more effectively. Traditionally used for energy, stamina, and physical strength.

### Mood and sensory botanicals

**Saffron:** The world\'s most expensive spice — and one of the most genuinely effective natural mood-support ingredients available. Multiple studies show its active compounds can lift emotional flatness and low spirits. In chocolate, saffron does double duty: it lifts your mood and adds a warm, luxurious depth to the flavour.

**Rose:** Used in Ayurveda as a cooling, heart-opening herb for emotional heaviness and nervous tension. In chocolate, rose softens the bitterness of cacao and smooths out the earthiness of the herbs.

### Warming spices and calm-focus compounds

**Cinnamon, Cardamom &amp; Nutmeg:** Ceylon cinnamon improves blood flow and stabilises energy. Cardamom warms the body and freshens breath. Nutmeg, in small doses, gently relaxes the nervous system. Together they create a warm, grounding flavour long associated with comfort and romance.

**L-Theanine:** A natural compound found in green tea that puts your mind in a state of calm focus — relaxed but alert, present but not wired. One of the most well-studied ingredients in the functional food space.

## The real question: does it actually work?

Here is the honest answer — and it is more nuanced than the packaging suggests.

A proper ashwagandha study uses 300–600 mg of extract daily for 8–12 weeks. A single chocolate square might contain 50–100 mg. That gap is real. None of these ingredients produce an immediate, drug-like effect from a single serving. Any brand claiming otherwise is selling you a story, not a science.

But that does not make the product useless. It makes it a different kind of product than the marketing implies. Intimacy is not a chemical reaction you can trigger with the right dose — it is a state of mind. Stress, distraction, and self-consciousness are its biggest enemies. And anything that reduces these barriers improves the conditions for connection.

Consider what happens when two people share intimacy chocolate with intention. They pause. The phone goes down. The pace changes. When you expect to relax, your body begins to relax. When you share food with someone you care about, your body releases bonding hormones. This is the well-documented psychology of ritual — and it may be doing more heavy lifting than all the herbs combined.

The best intimacy chocolates are not selling you a drug. They are invitations — to slow down, to be present, and to remember that the most powerful aphrodisiac in human history has always been attention. Put your phone in another room, sit close enough to touch, and give the moment the one ingredient no formulation can provide: your full, undivided presence.', 'assets/blogs/intimacy-chocolate-header-1784800971.jpeg', 'assets/blogs/thumbnails/intimacy-chocolate-thumb-1784800971.jpeg', 'intimacy-article', NULL, '9 min', 1, '2026-06-11 06:55:09', '2026-09-28 15:29:55', NULL, 128);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (6, 'lecithin-chocolate', 'The Invisible Ingredient That Makes Chocolate Smooth', 'Science', 'Meet lecithin — the quiet emulsifier behind every velvety bite of chocolate you have ever loved.', 'Open any chocolate wrapper and flip it over. Somewhere in that ingredients list, you will spot it — **soya lecithin** or simply **lecithin**. It sits there quietly, used in tiny amounts, and most people scroll right past it. But without it, making chocolate at scale would be a completely different story.

> \"Lecithin does not change how chocolate tastes. It changes how chocolate behaves — and that changes everything.\"

## What exactly is lecithin?

Lecithin is a naturally occurring fat-like substance called a *phospholipid*. Do not let the science word scare you. Think of it as a molecule that has two personalities — one side loves water, the other loves fat. This dual nature makes it an outstanding **emulsifier**: it gets along with both water-based and fat-based ingredients, helping them mix smoothly instead of separating.

In the food world, lecithin is most commonly extracted from **soy**. You will also find it from sunflower seeds (preferred in allergen-free or non-GMO products) and, classically, from egg yolks.

** Soy lecithin (most common)
** Sunflower lecithin (allergen-free)
** Egg yolk (traditional)

## Why does chocolate need an emulsifier at all?

Chocolate is essentially a mixture of cocoa solids, cocoa butter, sugar, and milk solids (in milk chocolate). The problem? Cocoa solids and sugar carry tiny amounts of moisture — and moisture and fat (cocoa butter) do not like each other.

When you are manufacturing chocolate and melting it down to pour into moulds, the batter — called *chocolate mass* — can become thick, sticky, and difficult to work with. This stickiness is measured as **viscosity**. High viscosity means the chocolate resists flowing. It clogs pipes, it does not coat evenly, and it takes more energy (and more cocoa butter, which is expensive) to keep it fluid.

Enter lecithin.

## What lecithin actually does in chocolate

Lecithin molecules position themselves at the boundary between the tiny sugar and cocoa particles and the surrounding cocoa butter. They coat these particles, reducing friction between them. The result: the same chocolate flows more easily without adding more fat.

**
Primary role
Reduces viscosity
Makes the chocolate mass more fluid at the same fat content — critical for enrobing and moulding.

**
Economic benefit
Replaces cocoa butter
Adding 0.3–0.5% lecithin can replace 3–5% extra cocoa butter — a significant cost saving.

**
Process control
Improves workability
Helps chocolate flow through machines consistently, reducing defects in the final product.

## How much is actually used?

Very little. Lecithin is effective at levels as low as **0.3% to 0.5%** of the total chocolate mass. Interestingly, more is not always better — beyond 0.5%, additional lecithin can actually start to *increase* viscosity again, working against its own purpose. Chocolatiers and manufacturers need to get this dosage right.

Technical quick reference
Typical usage level0.3% – 0.5% of chocolate mass
Effect beyond 0.5%Viscosity increases (counterproductive)
Cocoa butter it replaces~3–5% per 0.5% lecithin added
Taste impactNeutral — does not alter flavour
Regulatory max (most regions)1% of finished product

## Does it affect taste or texture?

At the levels used in chocolate, lecithin is **flavour-neutral**. You will not taste it. What you do notice — without knowing why — is the smoothness of a well-made bar. That easy melt, that silky coating on your tongue, is partly lecithin\'s contribution to how uniformly the chocolate was processed.

However, bean-to-bar craft chocolate makers sometimes choose to skip lecithin entirely. They prefer to rely on longer *conching* (the process of continuously agitating melted chocolate for hours) to develop smoothness and fluidity naturally. This is a deliberate philosophical choice — not a technical necessity.

## Lecithin vs. PGPR — the controversy

In the early 2000s, some large manufacturers switched partially from lecithin to a synthetic emulsifier called **PGPR** (polyglycerol polyricinoleate). PGPR is even more powerful at reducing viscosity — specifically the *yield value*, meaning it makes chocolate start flowing with less force applied.

The controversy? PGPR is used to cut cocoa butter costs further, and critics argued it changed the mouthfeel of some mass-market chocolates — making them feel slightly waxy or less rich. PGPR remains legal and widely used, but premium and craft manufacturers tend to stick with lecithin as the more trusted option.

## Is it safe? What about soy allergies?

Lecithin is recognised as safe by food regulatory bodies worldwide. Soy lecithin is highly refined, and during extraction, the soy proteins that trigger allergies are almost entirely removed. Most allergists consider soy lecithin safe even for soy-sensitive individuals — but anyone with severe soy allergies should consult their doctor. If you want to avoid soy entirely, look for **sunflower lecithin** on the label — it performs similarly and comes with no allergen concerns.

> \"Craft makers who skip lecithin are not making better chocolate. They are making a different choice — and both choices can lead to exceptional results.\"

## The craft chocolate perspective

Many artisan bean-to-bar makers wear the absence of lecithin as a badge of purity. It is a fair claim — using extended conching and carefully sourced cocoa butter to achieve fluidity naturally is a more labour-intensive process. But it is worth understanding that lecithin\'s absence does not automatically make a bar superior. The quality of the cacao, the fermentation, the roast profile — these matter far more to the flavour in your cup than whether the maker used 0.4% soy lecithin.

What lecithin does is make the craft of chocolate manufacturing more consistent, more accessible, and more economical — which is why it has earned its place in almost every commercial chocolate bar on the planet.

Next time you eat a chocolate bar, you now know a little more about the invisible hand that made it smooth. That is what chocolate education is for.', 'assets/blogs/lecithin-chocolate-header-1784800782.jpeg', 'assets/blogs/thumbnails/lecithin-chocolate-thumb-1784800782.jpeg', 'blog-wrap', NULL, '6 min', 1, '2026-06-11 06:55:10', '2026-09-28 07:14:22', NULL, 133);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (7, 'freeze-dried-fruits-chocolate', 'Freeze-Dried Fruits in Chocolate: What I Discovered on My Development Bench', 'Science', 'An honest, science-backed look at what freeze-dried fruit inclusions can — and cannot — do inside chocolate systems.', 'How This Started
I recently received samples of freeze-dried fruit cubes in six varieties — Chikoo (Sapota), Guava, Pineapple, Jackfruit, Banana, and Strawberry. Rather than simply tasting them, I decided to evaluate each one across real chocolate applications: moulded bars, bonbons, and enrobed chocolates. I also pre-coated every cube in cocoa butter before use — a deliberate step to protect crunchiness inside the chocolate system. This article shares what I observed, what worked, and what challenges you should know about before working with these ingredients.

## First Impressions: What You Notice Immediately

The first thing that strikes you is the colour. These cubes are far more vivid than any conventionally dried fruit — the strawberry is a deep, saturated red, the guava retains its pink blush, and the pineapple holds a clean golden tone without any browning. This colour preservation is not just visual appeal. It tells you that the drying process has kept the fruit’s natural chemistry largely intact.

The aroma is equally telling. Freeze-dried guava smells sharp, green, and genuinely like fresh guava — not the flat, muted note you get from sun-dried or oven-dried fruit. Pineapple carries its bright, sweet-acidic character. These aromas survive because the fruit was never exposed to the kind of aggressive heat used in conventional drying. For a chocolate maker, this matters enormously, because aroma is a huge part of how we perceive flavour.

The texture is crisp and light. The cubes shatter cleanly under pressure, almost dissolving on the tongue. They feel nearly weightless — a sign that almost all the moisture has been removed while the fruit’s internal structure remains intact.

## Why Freeze-Drying Is Fundamentally Different

In regular drying, hot air evaporates the water at high temperatures. This works, but the intense heat damages flavour compounds, browns the sugars, collapses the fruit’s cell structure, and leaves you with something chewy and muted. The dried fruit you get is a distant version of the original.

Freeze-drying takes a completely different approach. The fruit is first frozen solid, then placed under deep vacuum. Gentle, controlled heat is then applied — just enough to provide the energy needed for the frozen ice inside the fruit to turn directly into vapour, skipping the liquid stage entirely. This process is called sublimation. The temperatures involved are far lower than in conventional drying, and no liquid water phase ever exists during the process. That is the critical difference. Because the heat is gentle and carefully managed, the fruit keeps its shape, its colour, its aroma compounds, and its natural flavour. The final product has very low moisture (water activity typically below 0.25), a porous crisp structure, and concentrated flavour that tastes remarkably close to fresh fruit.

Inside chocolate, this combination is powerful. The low moisture means the inclusion will not disrupt the chocolate. The crisp texture creates a satisfying crunch that contrasts with chocolate’s smooth melt. And the concentrated flavour delivers a genuine fruit experience — not a hint, but a clear, recognisable burst.

## A Critical Step: Pre-Coating with Cocoa Butter

Before using any freeze-dried cube in my chocolate trials, I coated each piece with a thin layer of cocoa butter. This was not an afterthought — it was a deliberate technical decision based on how these ingredients behave inside chocolate systems.

Freeze-dried fruit is extremely porous. That porosity gives it the crisp, light texture we value, but it also makes the fruit highly vulnerable to moisture. When an uncoated cube comes in contact with warm chocolate during moulding or enrobing, or sits near a moist ganache in a bonbon, it can begin absorbing moisture almost immediately. The cocoa butter pre-coat acts as a thin, food-grade moisture barrier that seals the porous surface. It slows down moisture migration significantly, buys valuable time during processing, and helps the inclusion retain its crunch over the product’s shelf life.

The coating also improves how the cubes bond with the surrounding chocolate. An uncoated, porous surface can trap tiny air pockets at the interface, which weakens adhesion and can cause visible gaps when a bar is snapped. A thin cocoa butter layer creates a smoother fat-to-fat contact between the inclusion and the chocolate matrix, resulting in a clean break, better visual cross-section, and a more professional finished product.

I would strongly recommend this step to any chocolatier or product developer working with freeze-dried inclusions. It is simple, inexpensive, and makes a measurable difference to both crunchiness retention and product appearance.

## What I Found Across Chocolate Applications

**Moulded Bars** were the easiest format to work with. The cocoa butter-coated cubes distributed well in the mould, and when bars were snapped, the colourful fruit fragments in the cross-section looked immediately premium. Pineapple and Strawberry performed best here — their acidity cut through the sweetness of milk chocolate and created a genuine flavour conversation. The crunch added a textural layer that plain chocolate simply cannot offer on its own.

**Bonbons** were more demanding, but also where I got some of the most interesting results. The approach I took was to place the cocoa butter-coated freeze-dried cube in the centre of the bonbon on its own — without any ganache surrounding it. This gave the consumer a direct, immediate crunch the moment they bit through the chocolate shell, which is a sensory experience most bonbons simply do not offer. Equally important, removing the ganache from the equation eliminates the biggest moisture risk entirely. There is no wet filling to drive moisture into the fruit, and the chocolate shell combined with the cocoa butter pre-coat provides a strong double barrier against external humidity. For makers who still want a ganache element alongside the fruit, I would suggest formulating it with anhydrous milk fat instead of cream. Anhydrous milk fat carries virtually no free water, so the risk of moisture migration into the freeze-dried inclusion is reduced dramatically. This opens up the possibility of a bonbon that delivers both a creamy ganache layer and a crunchy fruit centre — a combination that is rare and genuinely premium.

**Enrobed Chocolates** were the format where everything I know about moisture management got tested at once. The concept is simple — take a cocoa butter-coated freeze-dried fruit cube and run it through an enrober so it gets fully covered in tempered chocolate. But the execution demands precision. The chocolate coating is your primary defence against humidity, and its quality determines whether the product works or fails. Two things matter most: thickness and temper. A coating that is too thin — below roughly 1.5 mm — will not hold up under Indian humidity conditions. A coating that is poorly tempered will develop bloom and micro-cracks over time, and those tiny fractures become open doors for moisture. When done correctly, however, the result is outstanding. The consumer bites through a clean, well-tempered chocolate shell, hits a concentrated burst of fruit flavour, and gets a crisp, satisfying crunch — all in one bite. Among all my trials, Guava enrobed in 65–70% dark chocolate stood out as the strongest pairing. Its sharp, slightly bitter green character worked beautifully with the tannins in dark couverture, creating a flavour combination that felt sophisticated rather than sweet.

The inclusion only elevates the product when the system around it — formulation, process, packaging — is designed to protect it.

## Why Freeze-Dried Fruits Premiumise Chocolate

The effect is multi-dimensional. You get crunch in a product category where texture is usually uniform. You get vivid colour fragments that make a bar look handcrafted and intentional. You get concentrated real fruit flavour — not a flavouring, not a paste, but actual fruit. And you get a story to tell: real Allahabad guava, real Chikoo from the Konkan, freeze-dried to preserve everything that makes the fruit special.

A plain moulded chocolate bar and the same bar with well-placed freeze-dried strawberry are not perceived as the same product by the consumer. The inclusion moves the product from everyday to gift-worthy. But this perception only holds if the fruit is still crisp and flavourful when the consumer bites into it. A soft, stale inclusion communicates the opposite of premium — which is exactly why steps like cocoa butter pre-coating and proper barrier packaging are not optional extras. They are what make premiumisation real rather than just a label claim.

## The Challenges You Must Understand

This is where many product developers stumble, so I want to be direct.

**Moisture is the enemy.** Freeze-dried fruit is hygroscopic — it absorbs moisture from the air rapidly. In Indian conditions, where humidity regularly exceeds 65–75%, an exposed cube can lose its crispness within an hour. Once the moisture is absorbed, the crisp texture is gone and cannot be recovered.

**Your product architecture matters.** If a freeze-dried cube sits next to a ganache or a moist filling, moisture will migrate from the filling into the fruit regardless of how good your external packaging is. This is an internal design problem. You need to think about moisture barriers within your product — cocoa butter pre-coating, shell thickness, ingredient placement — not just the outer wrapper.

**Packaging is not optional — it is technical.** Standard chocolate packaging will not protect these inclusions in Indian ambient conditions. You need genuine barrier packaging: metallised films, foil laminates, or sealed pouches with desiccant. Products must be validated under real-world distribution conditions, not just laboratory ideals.

**Processing environment matters.** Your production room humidity during the incorporation step needs to be controlled. Freeze-dried cubes should stay sealed until the moment they are used. Pre-coat them with cocoa butter quickly, and add them to the chocolate system as fast as the process allows. Every minute of exposure in a humid room costs you crunch in the final product.

**Cost is real.** Freeze-dried fruit costs several times more than conventionally dried fruit. The cubes are fragile and breakage during handling adds to waste. Your cost model must account for the ingredient premium, the cocoa butter needed for pre-coating, handling losses, and the better packaging these products demand.

## My Advice for Chocolate Makers

Start with one fruit and one application. Strawberry in a moulded dark chocolate bar is the most forgiving combination and delivers strong results. Get comfortable with the moisture management before you expand your range.

Always pre-coat your freeze-dried inclusions with cocoa butter. It is the single most effective step you can take to protect crunchiness, and it requires no special equipment — just melted cocoa butter and a gentle hand.

Invest in a water activity meter before you invest in new moulds or packaging. Understanding exactly how moisture behaves in your specific product will guide every decision that follows — shelf-life claims, packaging choices, and which distribution channels your product can survive.

Treat your chocolate shell or coating as a functional barrier, not just a carrier. Its thickness, temper quality, and surface integrity directly determine whether the inclusion will still be crisp when the consumer opens the pack weeks later.

Talk to your ingredient supplier about specifications. Establish clear standards for water activity, cube size consistency, and colour. Inconsistent incoming material will create inconsistent finished products.

Freeze-dried fruits are not decorative additions. They are functional ingredients with real technical requirements and remarkable sensory potential. The difference between a product that uses them well and one that merely contains them is understanding — understanding moisture, understanding flavour release, and understanding how the ingredient interacts with the chocolate system from production to the consumer’s palate.
RT Chocos — Exploring chocolate from the inside out', 'assets/blogs/freeze-dried-fruits-chocolate-header-1784800721.jpeg', 'assets/blogs/thumbnails/freeze-dried-fruits-chocolate-thumb-1784800721.jpeg', 'freeze-dried-fruits-article', NULL, '8 min', 1, '2026-06-17 18:19:28', '2026-09-27 11:24:04', NULL, 170);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (8, 'does-a2-milk-really-matter-in-chocolate', 'Does A2 Milk Really Matter in Chocolate?', 'Science', 'Does A2 Milk Really Matter in Chocolate?', '**Does A2 Milk Really Matter in Chocolate?**

*A chocolate maker\'s honest take on the newest premium ingredient trend.*

Walk into any chocolate expo today and you\'ll see a new star on the shelf&nbsp;**A2 milk chocolate**. In India especially, it\'s everywhere. Gir cow A2 chocolate. Desi A2 milk bars. Prices that are 30–50% higher than regular chocolate.

The big question is simple:

**Does A2 milk actually make chocolate better - or just more expensive?

**What is A2 milk, really?**

Cow\'s milk has a mix of proteins. One of the main proteins comes in two versions&nbsp;**A1** and **A2**. Think of them as two slightly different flavors of the same protein.

- **A1** is found in most Western cow breeds like Holstein and Friesian.
- **A2** is more common in Jersey and Guernsey cows, and in many indigenous Indian breeds like Gir, Sahiwal, and Tharparkar but not every cow in these breeds automatically produces only A2 milk.

This is one of the most common misunderstandings in the industry. A chocolate brand cannot simply buy \"Jersey milk\" and call it A2. Genuine A2 milk comes from cows that have been **individually DNA tested** to confirm they carry two copies of the A2 gene. That test is what separates real A2 sourcing from loose breed-based claims.

The difference between A1 and A2 is very small just one tiny building block inside the protein is swapped. But that small swap changes what happens when your body digests it.

During digestion, enzymes can release a small peptide called **beta-casomorphin-7 (BCM-7)** from A1 beta-casein. Some studies suggest this peptide may contribute to digestive discomfort in certain individuals although the evidence remains mixed. A2 beta-casein does not release BCM-7 in the same way. That\'s it. That\'s the entire science behind the A2 story.
Everything else the \"healthier,\" \"more natural,\" \"ancestral\" claims is built on top of this one small fact.

**What actually matters when making milk chocolate?**

Here\'s the truth from inside a chocolate factory. When we make milk chocolate, we don\'t lose sleep over A1 vs A2. We lose sleep over things like:

- **How much fat is in the milk powder** — richer milk means creamier chocolate.
- **How fresh and dry the milk powder is** — old or damp milk powder can ruin the flavor.
- **How the milk sugar behaves** when we heat and mix the batch.
- **How well the milk mixes into the cocoa butter** — this decides how smooth it feels on your tongue.

That **caramel, cooked-milk flavor** you love in milk chocolate? It comes from a slow cooking-and-mixing stage where milk sugars and milk proteins react together over hours. This reaction has almost nothing to do with A1 or A2. It has everything to do with heat, time, and the freshness of the milk.

So on the factory floor, A1 vs A2 changes **almost nothing** about how the chocolate flows, melts, sets, or tastes.

**One thing most people don\'t realise: chocolate doesn\'t use fresh milk.**

This is perhaps the most overlooked fact in the entire A2 debate. Most chocolate manufacturers from craft bars to global brands do not use fresh liquid milk. They use **whole milk powder, skim milk powder, milk fat, or milk crumb**, all of which are carefully standardised so that every batch behaves consistently.

Once milk is converted into a standardised dairy ingredient, factors such as **fat level, moisture, freshness, and processing quality** have a much greater influence on your chocolate than the A1/A2 protein variant itself. The protein remains A1 or A2 inside the powder&nbsp; that biology doesn\'t change. But from a chocolate-making perspective, it is simply outweighed by everything else.

**Here\'s the interesting part.

******Does A2 actually taste different?****

There is currently **no strong scientific evidence** that A2 milk alone produces a consistently detectable flavour difference in chocolate when all other variables milk fat, powder quality, processing&nbsp; are held constant.

When people do notice a difference in chocolate made with A2 milk, the reason is almost never the A1/A2 protein itself. It is far more likely to come from the **composition and quality of the dairy ingredient**&nbsp; things like the milk fat content, the freshness of the milk powder, and how carefully it was processed. These are the variables that actually move the needle in a chocolate recipe.

******Is A2 chocolate actually healthier?****

**Honest answer: probably not in any big, meaningful way.
Here\'s why:

The health claim behind A2 is still being debated. European food safety experts reviewed the evidence and did not find a clear link between A1 milk and any disease.

Even if A1 milk does affect some people, a typical milk chocolate bar contains only **a small fraction of the milk protein found in a glass of milk**. So even if the effect is real, the dose from a bar of chocolate is far lower than from a glass of milk.

**A2 does NOT help lactose-intolerant people.** The milk sugar is exactly the same in both A1 and A2 milk.
A2 does NOT help people with a milk allergy either. Milk allergies react to a completely different part of the milk.

If A2 provides a benefit, it is likely limited to a subset of consumers who tolerate lactose but may experience digestive symptoms associated with A1 beta-casein. For the majority of people, there is no evidence they would notice any difference at all.

**So when does A2 make sense for a chocolate brand?**

There are three genuinely good reasons a chocolate business might choose A2:

1. **It tells a great story.** \"Made with A2 milk from Gir cows\" sounds premium,pure and traditional. Consumers love it. it\'s not a lie - it\'s smart positioning, and it works especially well in India.
2. **It comes with a more traceable supply chain.&nbsp;****A2 milk is often sourced through dedicated, carefully managed supply chains because getting genuine A2 certification requires genetic testing at the farm level. That traceability aligns well with premium chocolate brands that value knowing exactly where their ingredients come from.
3. **It signals ingredient intentionality.&nbsp;****Choosing A2 milk powder signals to buyers and retailers that you\'ve thought carefully about every ingredient in your recipe. In a crowded premium market, that kind of visible care matters even when the functional difference is small.

The verdict
A2 milk in chocolate is a **big marketing story built on a small piece of science**.

The science is real but modest one small protein swap, one possible digestive benefit for a small group of people. The marketing is much bigger than the science actually supports.

If you\'re a chocolate maker, the honest advice is this: **don\'t switch to A2 thinking it will automatically make better chocolate**. The protein variant alone won\'t do that. Switch to it because:

- It lets you tell a premium, credible story to the right customer, and
- The traceability and certification that comes with genuine A2 sourcing is itself a mark of quality.

What actually makes great milk chocolate is still what it has always been:

- Fresh, rich, dry milk powder
- Sourcing you can trust
- Careful, patient mixing to build deep flavor
- Honest labels

******A2 is a nice line on the wrapper. But great milk chocolate is still made the old-fashioned way with great milk, great cocoa, and great craftsmanship.****

**
The molecule is not the story. The craft is.', 'assets/blogs/does-a2-milk-really-matter-in-chocolate-header-1783978968.jpeg', 'assets/blogs/thumbnails/does-a2-milk-really-matter-in-chocolate-thumb-1783978968.jpeg', NULL, NULL, NULL, 1, '2026-07-03 19:13:20', '2026-09-26 23:13:27', '2026-07-04 00:38:00', 151);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (9, 'why-did-investors-bet-9-million-on-this-chocolate-brand', 'Why Did Investors Bet $9 Million on This Chocolate Brand?', 'Industry Insights', 'Manam Chocolate raised $9 million — not from a consumer brand fund, but from Omnivore, an investor that backs farms and food systems. Five years of building a fermentary before opening a single store. 17 international awards within 100 days of launch. This isn\'t just a funding story. It\'s the moment Indian craft chocolate became an investable category.', '## Manam Chocolate Just Raised $9 Million. Here\'s Why That Matters Far Beyond One Brand.

In June 2026, a Hyderabad-based chocolate company called Manam raised $9 million in Series A funding. The round was led by Omnivore, one of India\'s most respected agri-tech and food-systems investors, with participation from the Turner Morrison consortium.

On paper, it\'s a fundraise. In practice, it\'s one of the strongest signals yet that Indian craft chocolate has graduated from passion project to investable category.

Let\'s unpack why.

## First, Who Is Manam?

Manam Chocolate was founded in 2021 by Chaitanya Muppala, a second-generation entrepreneur whose family runs Almond House — Hyderabad\'s iconic mithai chain, in business since 1989. Muppala is a graduate of the University of British Columbia\'s Sauder School of Business and a Stanford Seed Programme alumnus. He also happens to be India\'s first Level-3 Certified Chocolate Taster, credentialed by the International Institute of Chocolate and Cacao Tasting.

But Manam didn\'t start with a store. It started with a fermentary.

Years before launching its first chocolate bar, Muppala\'s parent company — Distinct Origins Private Limited — built a fine-flavour cacao fermentation facility in Tadikalapudi, in Andhra Pradesh\'s West Godavari district. The team spent three years working directly with farmers, identifying genetic limitations in Indian cacao, and developing post-harvest processing techniques to compensate for those gaps.

Today, Distinct Origins works with over 250 cacao farmers across Andhra Pradesh. The brand\'s retail arm, Manam Chocolate, launched in August 2023 — five years after that foundational work began.

That timeline matters. This isn\'t a chocolate brand that added a farm story for marketing. This is a farm operation that eventually became a chocolate brand.

## A Track Record That Speaks Loudly

Within 100 days of launch, Manam won 17 awards at the 2023 Academy of Chocolate Awards in the UK — one Gold, ten Silver, five Bronze — and was named Overall Winner in the Brand Experience category, from among 1,400 global entries. The following year, it won again, across multiple categories for Indian-origin and international-origin tablets.

In 2024, TIME magazine named its flagship Karkhana in Hyderabad\'s Banjara Hills one of the World\'s Greatest Places.

These aren\'t vanity metrics. They indicate genuine consumer pull — the kind that makes investors pay attention.

## Where the $9 Million Will Go

The money is going where the brand wants to be seen, touched, and tasted — new retail stores across India, starting with Delhi-NCR. The company has already opened its first outlet outside Hyderabad and plans to scale significantly over the next couple of years.

Beyond stores, the capital will support new product development, stronger manufacturing operations, and deeper investment in the supply chain that feeds it all — the farmer network and fermentation infrastructure in Andhra Pradesh.

Gifting has become the single biggest revenue driver for the brand, spanning corporate, festive, and personal occasions. The company also sells through its own website and quick-commerce platforms, but physical retail remains the backbone. The strategy is clear: let people experience the chocolate first, then scale distribution around that pull.

## Why Omnivore Leading This Round Is Significant

Omnivore doesn\'t invest in lifestyle trends. It invests in food systems, agricultural supply chains, and climate resilience. Its portfolio is built around the conviction that lasting food brands must be anchored in defensible, farmer-partnered supply infrastructure.

Reihem Roy, Partner at Omnivore, framed it plainly: premium brands built on strong farmer partnerships demonstrate how origin-led value addition can improve farm-level livelihoods while reducing exposure to commodity-price volatility. And as climate pressure reshapes global cacao supply, investing in high-quality alternative origins like India is both a commercial opportunity and a contribution to a more resilient food system.

That framing is important. It means Omnivore isn\'t just betting on a consumer brand. It\'s betting on Indian cacao as a viable agricultural asset class — at a moment when the world\'s dominant cacao supply chain is under serious strain.

## India\'s Chocolate Market: Small, But Moving Fast

According to estimates from Mordor Intelligence, IMARC Group, and Research and Markets, India\'s broader chocolate market is valued at roughly $2.5–3 billion as of 2025, growing at approximately 7–8% annually. Projections place it in the $4–5.5 billion range by the early 2030s — though estimates vary depending on how broadly each firm defines the category.

But the numbers that matter for Manam are in the premium and artisanal segment, which is still tiny — and growing much faster than the mass market. Indian consumers, especially in urban metros, are increasingly moving beyond basic milk chocolate toward higher cocoa percentages, single-origin bars, ethical sourcing, and experience-led purchases. Chocolates are progressively replacing traditional sweets as the preferred gifting choice during Diwali, Raksha Bandhan, and corporate occasions.

In the craft space, Manam competes with Kerala\'s Paul &amp; Mike, Tamil Nadu\'s Mason &amp; Co, and Rebel Foods-backed Smoor. Each has carved a distinct position. But none has attracted this level of capital from an agri-focused fund — a signal that Manam\'s backward integration into farming and fermentation is being valued as structural infrastructure, not just brand storytelling.

## What Makes This Different From Most Food Fundraises

Three things stand out.

**The investment thesis starts at the farm, not the shelf.** Most food-brand funding rounds are evaluated on brand metrics — Instagram following, D2C conversion rates, repeat purchase frequency. This one was evaluated on farmer partnerships, fermentation IP, and supply-chain resilience. That\'s a fundamentally different lens, and it raises the bar for what craft-food founders need to show investors going forward.

**The brand refused borrowed prestige.** Muppala has been unusually direct about the fact that Indian cacao doesn\'t need to imitate European chocolate to be taken seriously. The \"Belgian chocolate\" mystique, he has argued, is a legacy of colonial commodity control — not an inherent quality marker. Manam\'s positioning is unapologetically Indian: Indian cacao, Indian farmers, Indian craft, global standards.

**Big, immersive stores can actually work.** Many people assume experiential retail is just an expensive way to look good. Manam\'s flagship earned a TIME \"World\'s Greatest Places\" nod within a year of opening, and gifting revenue far outpaced expectations. If the chocolate is worth the visit, the store pays for itself.

## Honest Questions Ahead

No funding story should be read as a foregone conclusion. A few things worth watching:

What worked in Hyderabad may not work the same way in Delhi. Different city, different consumers, different costs. Replicating a destination store in a new market is never straightforward.

Global cocoa volatility is a double-edged sword. It makes Indian cacao strategically appealing — but it also means raw material costs remain unpredictable, even for a vertically integrated player.

Scaling from a beloved Hyderabad institution to a national premium brand requires different operational muscles — talent, logistics, consistency across locations — that have tripped up many food brands before.

The next 18 months, during which those 18 stores are supposed to materialise, will be the real proof point.

## The Bigger Picture

For decades, \"world-class chocolate\" meant European. The supply chain was designed that way — cacao grown in the Global South, value captured in the Global North. India, despite being a cacao-growing country, was barely a footnote in the craft chocolate conversation.

That\'s changing. Not because of marketing. Because someone spent five years building a fermentary before opening a store. Because Indian-origin cacao is now winning at the same international competitions that were once dominated by European makers. And because a serious agri-tech investor just put $9 million behind the thesis that Indian cacao is a real, resilient, and scalable agricultural asset.

Manam\'s raise is a milestone — not because of the number, but because of what it validates.

The Indian chocolate story is no longer just beginning. It\'s underway.', 'assets/blogs/why-did-investors-bet-9-million-on-this-chocolate-brand-header-1784009411.png', 'assets/blogs/thumbnails/why-did-investors-bet-9-million-on-this-chocolate-brand-thumb-1784009411.png', NULL, NULL, NULL, 1, '2026-07-13 11:11:37', '2026-09-27 04:12:42', NULL, 133);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (10, '5-ingredients-that-should-never-be-in-your-chocolate', '5 ingredients that should never be in your chocolate', 'Beginner Guide', 'That chocolate you love? Flip the label. From PGPR to vanillin, here are 5 ingredients hiding in your chocolate — and what they reveal about the brand that put them there.', '## 5 Ingredients That Should Never Be in Your Chocolate

Pick up any chocolate bar. Flip it over.

If you can read and understand every ingredient on that label — you\'re holding real chocolate. If you can\'t — you\'re likely holding something the industry *calls* chocolate, but science tells a different story.

After a decade in chocolate formulation and bean-to-bar production, I\'ve learned to read labels the way a doctor reads a prescription. Here are five ingredients I never want to see in my chocolate — or yours.

## 1. Vegetable Fat (In Place of Cocoa Butter)

The most common offender in India, and the most important one to know.

Real chocolate has exactly one fat: **cocoa butter** — naturally extracted from the cocoa bean. It gives chocolate its signature melt, clean snap, and smooth texture. It\'s also expensive.

Vegetable fat — palm oil, shea stearin, or hydrogenated vegetable oil — is the cheaper substitute. When brands replace cocoa butter with vegetable fat, the product is technically no longer chocolate. It\'s **compound** — a confectionery that mimics chocolate while replacing its most critical ingredient.

Spot it on the label: *\"vegetable fat,\" \"edible vegetable oil,\" \"palm oil,\" \"hydrogenated fat.\"*

Real chocolate melts on your tongue. Compound coats it. That difference is the fat.

## 2. PGPR — Polyglycerol Polyricinoleate (E476)

Rarely discussed in Indian food conversations. It should be.

PGPR is a synthetic emulsifier derived from castor oil, used in chocolate to reduce viscosity — which allows manufacturers to use **less cocoa butter** in the formulation. Less cocoa butter, lower cost. PGPR is the workaround.

Regulatory bodies permit it within limits, so you\'ll find it in some well-known global brands. But \"approved\" and \"desirable\" are not the same thing. Properly made chocolate — with the right cocoa butter ratio and adequate conching — doesn\'t need PGPR to flow.

Its presence on a label is not a safety issue. It\'s a quality signal.

Look for **E476.** In serious artisanal chocolate, you won\'t find it.

## 3. Vanillin (Artificial Vanilla Flavouring)

**Vanilla** and **vanillin** are not the same ingredient — and this distinction matters specifically in chocolate.

Vanilla extract comes from vanilla beans. Vanillin is a **synthetic compound** that mimics a single molecule from vanilla\'s complex flavour profile, produced industrially from wood pulp or petrochemical sources at a fraction of the cost.

Why does this matter? Because vanillin is routinely used to **mask the flavour of poor-quality cocoa.** Under-fermented, over-roasted, or generally inferior cocoa has off-notes that synthetic vanilla conveniently papers over. You taste warmth and sweetness — not the cocoa underneath, which isn\'t worth tasting.

High-quality chocolate either uses real vanilla or none at all, because the cocoa stands on its own.

*\"Artificial vanilla flavouring,\" \"vanillin,\"* or an unspecified *\"flavouring\"* — all red flags for what\'s beneath.

## 4. Artificial Colours

Real chocolate — from ivory white to 100% dark — gets its colour entirely from its ingredients. No assistance needed.

Artificially coloured chocolates, common in moulded novelties and children\'s bars, use synthetic dyes like **Tartrazine (E102), Allura Red (E129), Sunset Yellow (E110),** and **Brilliant Blue (E133).** These add nothing to flavour, texture, or nutrition.

Several azo dyes have been linked in European regulatory studies to hyperactivity in children — enough for the EU to mandate warning labels on products containing them. India\'s FSSAI currently requires no such disclosure.

When chocolate needs artificial colour to look appealing, quality couldn\'t do the job.

## 5. Multiple Emulsifiers Beyond Soy Lecithin

This one needs nuance.

A small amount of **soy lecithin** — a natural emulsifier from soybeans — is widely accepted in chocolate production. At 0.1–0.5%, it aids texture consistency and has been standard practice for over a century. No issue there.

The concern is when labels list **multiple emulsifiers in combination** — lecithin plus PGPR, or lecithin plus polyglycerol esters (E475), or other stacks. Each addition is almost always a signal: lower-grade cocoa butter, insufficient conching, substandard cocoa mass, or aggressive cost reduction.

Well-made chocolate, processed correctly, emulsifies naturally. A long emulsifier list is a formulation workaround — not a quality feature.

## The Label Test

Next time you pick up a chocolate, ask five questions:

1. Is **cocoa butter** the fat, or is there a vegetable fat?
2. Do you see **E476 or PGPR**?
3. Does it say **vanillin** or unspecified \"flavouring\"?
4. Are there **colour codes** — E102, E110, E129?
5. Are there **more than two emulsifiers** listed?

If any answer is yes — you know what you\'re holding. And you can choose accordingly.

Real chocolate has a short ingredient list: cocoa mass, cocoa butter, sugar, and perhaps milk solids and real vanilla. Everything beyond that deserves a question.

The goal isn\'t to make chocolate complicated. The goal is to make it honest.', 'assets/blogs/5-ingredients-that-should-never-be-in-your-chocolate-header-1785085758.png', 'assets/blogs/thumbnails/5-ingredients-that-should-never-be-in-your-chocolate-thumb-1785085758.png', NULL, NULL, NULL, 1, '2026-07-26 17:09:18', '2026-09-26 23:08:16', NULL, 77);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (11, 'why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts', 'Why Foodstories Is a Must-Visit for Bean-to-Bar Chocolate Enthusiasts', 'Industry Insights', 'What makes one bean-to-bar chocolate different from another?
A visit to Foodstories, Andheri, reminded me that choosing a chocolate is no longer just about the brand or cocoa percentage. As India\'s bean-to-bar movement gains momentum, consumers are discovering a world of origins, craftsmanship, and flavour. Here\'s why that matters.', '**<font size=\"5\" color=\"#7b1fa2\">Why Foodstories Is a Must-Visit for Bean-to-Bar Chocolate Enthusiasts</font>**

As someone working in chocolate research and product development, I rarely visit a food store simply to shop. I visit to observe, understand emerging trends, and discover products that are shaping the future of chocolate.

During a recent visit to **Foodstories, Andheri**, one section immediately drew my attention their remarkable collection of **bean-to-bar chocolates**.

What impressed me wasn\'t just the size of the collection, but the thought behind it.

In most premium retail stores, chocolate shelves are largely occupied by globally recognized brands such as **Lindt, Godiva, Whittaker\'s,** or **Venchi**. While these brands have earned their reputation over decades, their presence often leaves little room for discovering craft chocolate makers.

Foodstories has taken a different approach.

Instead of relying solely on commercial luxury brands, they have curated a diverse selection of bean-to-bar chocolates from across the world. During my visit, I came across makers such as **Manam Chocolate, Paul And Mike, Mason &amp; Co., La Folie, Bonfiction, Beyond Good, Markham &amp; Fitz, Compartés Chocolatier, Taza Chocolate, Fine &amp; Raw, Heidi**, **Sol Cacao&nbsp;**alongside established names like **Venchi**. Seeing so many craft makers displayed together is still uncommon in India, making the experience genuinely exciting for anyone interested in fine chocolate.

Standing in front of this shelf, I realized something interesting.

The challenge wasn\'t finding a good chocolate it was deciding **which one to choose**.

Every bar represented a different origin, a different fermentation approach, a unique roasting profile, and a distinct philosophy of chocolate making. Two chocolates with the same cocoa percentage can deliver completely different sensory experiences because chocolate is influenced by far more than a number printed on the wrapper.

This is exactly why **bean-to-bar chocolate is an education in itself**.

As consumers become more aware of cacao origins, ingredient transparency, ethical sourcing, and flavour complexity, they are beginning to look beyond conventional chocolate. The Indian bean-to-bar movement has grown significantly over the last few years, with homegrown makers producing chocolates that are now recognized internationally for their quality and craftsmanship. More importantly, consumers are becoming curious they want to know where their chocolate comes from, how it was made, and why one origin tastes different from another.

For me, this shift is perhaps the most encouraging development in the Indian chocolate industry.

As an R&amp;D professional, I believe the future of premium chocolate in India will not be defined only by new products, but by **better-informed consumers**. When people begin appreciating fermentation, origin, roast profiles, and craftsmanship, they also begin appreciating the extraordinary work that goes into producing exceptional chocolate.

My visit to Foodstories reminded me that a thoughtfully curated shelf can do much more than sell chocolate, it can introduce consumers to an entirely new world of flavour, craftsmanship, and discovery.

And perhaps that\'s the real beauty of bean-to-bar chocolate.

It doesn\'t simply ask, **\"Which chocolate would you like to buy?\"**

It asks, **\"Which story would you like to taste?\"**', 'assets/blogs/why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-header-1785237485.png', 'assets/blogs/thumbnails/why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-thumb-1785237485.png', 'beantobarchocolate', NULL, NULL, 1, '2026-07-28 11:14:45', '2026-09-28 14:48:50', NULL, 70);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (12, 'why-is-the-chocolate-industry-dominated-by-nuts', 'Why is the Chocolate industry dominated by Nuts?', 'Industry Insights', 'Why are nuts found in almost every chocolate product? Discover the science behind chocolate\'s most successful partnership—from flavour chemistry and texture to cocoa butter compatibility, roasting, sensory science and product development.', '## Why Is the Chocolate Industry Dominated by Nuts?

Walk into any supermarket, chocolate boutique or airport duty-free store anywhere in the world, and you\'ll notice one thing almost immediately. Nuts are everywhere. Hazelnuts. Almonds. Pistachios. Peanuts. Cashews. Macadamias. They\'re inside bars, pralines, spreads, truffles, dragées and premium gifting boxes.

Have you ever wondered why? With thousands of ingredients available to product developers, why has the chocolate industry continued to rely on nuts for more than a hundred years?

The answer has surprisingly little to do with tradition. It has everything to do with science.

## Chocolate Doesn\'t Need More Flavour. It Needs the Right Flavour.

Chocolate is already one of the most flavour-complex foods on Earth. Depending on its origin and roasting profile, cocoa can develop notes of caramel, coffee, honey, berries, flowers, wood and even dried fruits. Adding another ingredient can easily disturb this delicate balance.

Nuts are one of the very few ingredients that don\'t compete with chocolate — they strengthen it. That\'s why chocolate still remains the hero, even in a hazelnut chocolate bar.

## The Secret Lies in Roasting

This is where things become fascinating. When cocoa beans are roasted, hundreds of aroma compounds are formed through the Maillard reaction. Exactly the same thing happens when you roast almonds or hazelnuts.

Both ingredients begin producing similar families of flavour molecules — particularly pyrazines, furans and Strecker aldehydes — which are responsible for roasted, caramelised and cocoa-like aromas. This is why roasted hazelnuts naturally feel as though they belong inside chocolate. They\'re speaking the same flavour language.

## Chocolate Is a Fat System — and Nuts Fit Perfectly

Many people think chocolate is mostly cocoa and sugar. From an R&amp;D perspective, that\'s only half the story. Chocolate is actually a carefully engineered fat-based suspension, where cocoa butter forms the continuous phase. Any ingredient added to chocolate must behave well inside this fat network.

Nuts do exactly that. With an oil content ranging from 45% to over 75%, they blend naturally into chocolate without introducing moisture — something fruits and many other inclusions constantly struggle with.

Fat understands fat. That\'s one of the biggest reasons nuts perform so beautifully in chocolate.

## Crunch Is More Important Than We Think

Consumers often say they love nut chocolates because they\'re crunchy. They\'re right — but not for the reason they think. Texture creates excitement.

Plain chocolate gives you two sensations: snap and melt. Add a roasted almond or hazelnut, and suddenly every bite becomes dynamic. First comes the clean snap of tempered chocolate. Then the crisp fracture of the nut. Finally, the gradual release of roasted oils and aromas.

That changing texture keeps the brain engaged. It\'s one of the reasons nut chocolates feel far more satisfying than plain chocolate.

## Roasting Creates an Entirely New Ingredient

A raw hazelnut and a roasted hazelnut may come from the same tree, but inside a chocolate factory they\'re almost treated as two different ingredients. Roasting reduces moisture, increases crunch, develops sweetness and unlocks hundreds of new aroma compounds.

That\'s why premium manufacturers spend enormous effort perfecting roasting profiles. Just as cocoa roasting defines the personality of chocolate, nut roasting often defines the personality of the product.

## Nuts Quietly Improve Chocolate

One of the least discussed advantages of nuts is that they improve chocolate without consumers even realising it. Their roasted flavour softens bitterness. Their buttery notes round off sharp edges. Their oils improve flavour release.

Even visually, whole nuts instantly create the impression of generosity and craftsmanship. Before taking the first bite, the brain has already decided: *\"This looks premium.\"*

## Why R&amp;D Teams Love Nuts

From a product development perspective, nuts are incredibly versatile. The same ingredient can be used whole, chopped, sliced, caramelised, coated, converted into praline, refined into gianduja or processed into smooth nut butter. Few ingredients offer this level of flexibility while remaining compatible with moulding, enrobing, panning, fillings and spreads.

Of course, nuts aren\'t without challenges. Oil migration, oxidation and moisture control require careful attention. But compared to many other inclusions, they remain one of the most reliable ingredients a chocolate technologist can work with.

## Can Anything Replace Nuts?

The industry has experimented with freeze-dried fruits, cookies, cereals, seeds, spices and countless innovative inclusions. Many are excellent. None have matched the complete package that nuts offer.

Because nuts don\'t excel in just one area. They perform brilliantly in flavour, texture, processing, visual appeal, consumer acceptance and premium perception — all at the same time. That\'s an exceptionally rare combination.

## Final Thoughts

Chocolate and nuts weren\'t designed for each other. Nature simply gave them remarkably compatible chemistry. One contributes complexity, smoothness and melt. The other contributes crunch, roasted aroma, richness and visual appeal.

Together, they create an eating experience that has stood the test of time. Perhaps that\'s why, after more than a century of innovation, the chocolate industry still keeps returning to the same trusted ingredient.

Not because it\'s traditional. Because very few ingredients understand chocolate as naturally as a perfectly roasted nut.', 'assets/blogs/why-is-the-chocolate-industry-dominated-by-nuts-header-1785913213.png', 'assets/blogs/thumbnails/why-is-the-chocolate-industry-dominated-by-nuts-thumb-1785913213.png', NULL, NULL, NULL, 1, '2026-08-05 07:00:13', '2026-09-28 16:52:28', NULL, 130);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (13, 'difference-between-dutch-vs-natual-cocoa-powder', 'Difference between Dutch Vs Natural Cocoa Powder', 'Science', 'Natural and Dutch-process cocoa powders may look similar on a label, but they can behave very differently in a formulation. This article explores how alkalization changes cocoa’s pH, colour, flavour and functional properties—and why understanding these differences matters when choosing cocoa for chocolate, baking, beverages and other food applications.', 'Not all cocoa powders behave the same way.

Two cocoa powders may both be labelled simply *\"cocoa powder\"*, yet one may be lighter and more acidic, while the other is deep brown, smoother and much less acidic. One may work beautifully in a cake, while the other performs better in a dark coating or beverage.

The reason is **processing**.

The most important difference between natural cocoa powder and Dutch-process cocoa powder is **alkalization**, a treatment that changes the cocoa’s pH and, along the way, its colour, flavour, composition and functional behaviour.

Understanding this difference is important not only for bakers and chocolatiers, but for anyone developing chocolate, bakery, beverage, confectionery or dairy products.

## What Is Natural Cocoa Powder?

Think of natural cocoa powder as cocoa in its most straightforward form. Cocoa beans are fermented, dried, roasted, and cracked open to get the inner nibs. Those nibs are ground into a thick paste called cocoa liquor, which is then pressed hard to squeeze out most of the fat (cocoa butter). What is left behind is a dry cake, which gets crushed into the fine powder we know as cocoa. No chemicals are added to change its character — what you get is essentially what the bean, the fermentation, and the roast gave you.

Because fermentation naturally produces acids — mainly acetic acid (the same acid in vinegar) and citric acid (the same acid in lemons) — natural cocoa powder is inherently acidic. Its **pH typically falls somewhere between 5.0 and 5.8**, though this varies depending on where the beans were grown, how they were fermented, and how they were roasted.

In terms of taste and appearance, natural cocoa is lighter in colour — ranging from tan to warm reddish-brown — with a flavour that leans sharp, bright, sometimes fruity, slightly mouth-drying, and distinctly \"cocoa-forward.\"

## What Is Dutch-Process Cocoa Powder?

Dutch-process cocoa — also called alkalized cocoa — has been treated with an alkalising agent, most commonly potassium carbonate, though sodium carbonate, sodium hydroxide, and ammonium carbonate are also used in industry. The process was pioneered in the early 19th century by Coenraad Johannes van Houten in the Netherlands, hence the name.

Alkalization can be applied at different stages: to the cocoa nibs before grinding, to the cocoa liquor, or to the pressed cocoa cake. Each approach produces somewhat different results, and experienced cocoa processors choose the stage and intensity carefully to achieve a target colour, flavour, and pH.

The resulting powder typically has a **pH in the range of about 6.8 to 8.1**, depending on how aggressively the process is applied. Industry practice often classifies alkalized cocoa into light (roughly pH 6.5–7.2), medium (roughly pH 7.2–7.6), and heavy (pH above 7.6) alkalization levels.

The colour deepens. The flavour softens. The chemistry changes in ways that ripple through every application.

## What Actually Happens During Alkalization?

Alkalization is not a single change; it triggers a cascade of chemical shifts all at once. Here is what is happening inside that cocoa:

- **The sharp acids get neutralised:** Remember the vinegar-like and citrus-like acids from fermentation? The alkaline solution cancels them out, partially or almost entirely, depending on how much is used. This is why Dutch cocoa tastes less sharp.
- **The natural antioxidants transform:** Cocoa is rich in compounds called flavanols — the same family of antioxidants that gets tea and red wine their health headlines. Alkalization changes some of the natural compounds in cocoa, especially polyphenols. During this process, these compounds are broken down and changed into new compounds, some of which contribute to the deeper brown and red-brown colours we see in alkalized cocoa.
- **Browning reactions speed up:** You know how bread crusts turn golden-brown in the oven? That is the Maillard reaction, a reaction between sugars and amino acids that creates colour and flavour. Alkaline conditions accelerate this reaction in cocoa, pushing it toward deeper browns and subtly shifting the taste.
- **Aroma compounds fade or change:** The fruity, bright, tangy scent notes that natural cocoa carries are partly lost during alkalization. The aroma becomes smoother, simpler and pleasant, but with fewer high notes.
- **The physical structure of the powder changes:** The alkaline treatment loosens the internal cell walls of cocoa particles, which changes how the powder absorbs water and interacts with fat. This is one reason Dutch cocoa can behave differently when you mix it into a drink or a batter.

The end result? A powder that is darker, mellower, less acidic, and functionally quite different from its natural counterpart — even though it started from the same bean.

## Why Does Alkalized Cocoa Become Darker?

Alkalized cocoa becomes darker because the alkaline treatment changes the way cocoa’s natural colour compounds behave.

Natural cocoa is usually light to medium brown, sometimes with reddish tones. When an alkaline ingredient is added, it raises the cocoa’s pH and alters its phenolic compounds — the same family of compounds that contribute to cocoa’s colour, bitterness and astringency. As these compounds react and transform, the powder can move from reddish brown to deep brown, and eventually to the almost black colour associated with black cocoa.

The final colour depends on more than alkalization alone. The cocoa’s origin, fermentation, roasting conditions, type of alkali and strength of treatment all play a role. This is why two cocoa powders can both be labelled \"Dutch-process\" yet look and taste completely different.

*For a chocolatier, the key point is simple:*

> **Darkness is a specification, not a measure of quality.**

A very dark cocoa may be exactly right for sandwich biscuits, dark cakes or products where strong visual contrast is important. But darker cocoa does not automatically mean deeper, richer or more complex chocolate flavour. Heavy alkalization can soften acidity and change the balance of aroma, bitterness and astringency.

So the darkest cocoa is not necessarily the best cocoa. It is simply cocoa that has undergone a more pronounced processing treatment — and the right choice depends on what you want the finished product to look and taste like.

## The Real Difference Is Not Natural vs Dutch

Natural and Dutch-process cocoa are often presented as two interchangeable choices: one acidic and light, the other darker and less acidic. For anyone working seriously with cocoa, that description is far too narrow.

Alkalization is a deliberate processing decision that changes the chemical, sensory and functional identity of cocoa. It can alter acidity, colour, flavour and the way the powder behaves in a formulation. The significance of that change depends entirely on where the cocoa is going next — into a cake, a beverage, a filling, a compound coating or a chocolate system.

That is why experienced chocolate makers do not select cocoa by colour alone, or by the word *Dutch* on a specification sheet.

They define the product first, establish the required flavour, colour, acidity and processing behaviour, and then select the cocoa that delivers those parameters consistently.

Natural cocoa is not inherently better. Dutch-process cocoa is not inherently better. The better cocoa is the one that gives the formulation exactly what it needs without compromising the character of the finished product.', 'assets/blogs/difference-between-dutch-vs-natual-cocoa-powder-header-1788547637.png', 'assets/blogs/thumbnails/difference-between-dutch-vs-natual-cocoa-powder-thumb-1788547637.png', NULL, NULL, NULL, 1, '2026-09-04 18:31:35', '2026-09-28 13:05:40', NULL, 88);
INSERT INTO `blogs` (`id`, `slug`, `title`, `category`, `excerpt`, `content`, `image_path`, `thumbnail_path`, `body_class`, `youtube_url`, `read_time`, `is_published`, `created_at`, `updated_at`, `scheduled_at`, `views`) VALUES (20, 'ajax-test-draft-blog', 'AJAX Toggle Test Blog', 'Science', 'Test excerpt', '<p><div class=\"table-responsive-wrapper\" contenteditable=\"false\"><table class=\"blog-custom-table blog-table-artisan\" contenteditable=\"true\"><thead><tr><th>Header 1</th><th>Header 2</th><th>Header 3</th><th>Header 4</th><th>Header 5</th><th>Header 6</th></tr></thead><tbody><tr><td>Data 1.1</td><td>Data 1.2</td><td>Data 1.3</td><td>Data 1.4</td><td>Data 1.5</td><td>Data 1.6</td></tr><tr><td>Data 2.1</td><td>Data 2.2</td><td>Data 2.3</td><td>Data 2.4</td><td>Data 2.5</td><td>Data 2.6</td></tr><tr><td>Data 3.1</td><td>Data 3.2</td><td>Data 3.3</td><td>Data 3.4</td><td>Data 3.5</td><td>Data 3.6</td></tr><tr><td>Data 4.1</td><td>Data 4.2</td><td>Data 4.3</td><td>Data 4.4</td><td>Data 4.5</td><td>Data 4.6</td></tr><tr><td>Data 5.1</td><td>Data 5.2</td><td>Data 5.3</td><td>Data 5.4</td><td>Data 5.5</td><td>Data 5.6</td></tr><tr><td>Data 6.1</td><td>Data 6.2</td><td>Data 6.3</td><td>Data 6.4</td><td>Data 6.5</td><td>Data 6.6</td></tr></tbody></table></div><p><br></p>Test content</p>', NULL, NULL, NULL, NULL, '1 min', 0, '2026-09-28 17:15:14', '2026-09-28 19:33:10', NULL, 1);

-- --------------------------------------------------------
-- Table structure for `blog_tags`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `blog_tags`;
CREATE TABLE `blog_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `blog_tags` (18 rows)
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (1, 'a2milkchocolate', 'a2milkchocolate');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (2, 'manamchocolate', 'manamchocolate');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (3, 'indianchocolate', 'indianchocolate');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (4, 'funding', 'funding');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (5, 'chocolate', 'chocolate');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (6, 'foodstories', 'foodstories');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (7, 'beantobarchocolate', 'beantobarchocolate');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (8, 'indianbeantobar', 'indianbeantobar');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (9, 'chocolateblogger', 'chocolateblogger');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (11, 'chocolateindustry', 'chocolateindustry');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (12, 'nuts', 'nuts');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (13, 'chocolateformulation', 'chocolateformulation');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (14, 'chocolatescience', 'chocolatescience');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (15, 'chocolateR&d', 'chocolaterd');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (16, 'premiumchocolate', 'premiumchocolate');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (17, 'cocoapowder', 'cocoapowder');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (18, 'dutchvsnaturalcocoapowder', 'dutchvsnaturalcocoapowder');
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES (19, 'chocolateblog', 'chocolateblog');

-- --------------------------------------------------------
-- Table structure for `blog_tag_map`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `blog_tag_map`;
CREATE TABLE `blog_tag_map` (
  `blog_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`blog_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `blog_tag_map_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blog_tag_map_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `blog_tag_map` (19 rows)
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (8, 1);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (9, 2);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (9, 3);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (9, 4);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (10, 5);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (12, 5);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (11, 6);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (11, 7);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (11, 8);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (11, 9);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (12, 11);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (12, 12);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (12, 13);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (12, 14);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (12, 15);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (12, 16);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (13, 17);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (13, 18);
INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES (13, 19);

-- --------------------------------------------------------
-- Table structure for `media`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size` int(11) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `media` (122 rows)
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (1, 'freeze-dried-fruits-chocolate-header-1782581893.png', 'assets/blogs/freeze-dried-fruits-chocolate-header-1782581893.png', 'image/png', 2161338, '2026-06-27 17:38:13');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (2, 'freeze-dried-fruits-chocolate-thumb-1782581893.png', 'assets/blogs/thumbnails/freeze-dried-fruits-chocolate-thumb-1782581893.png', 'image/png', 2161338, '2026-06-27 17:38:13');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (3, 'lecithin-chocolate-header-1782581944.png', 'assets/blogs/lecithin-chocolate-header-1782581944.png', 'image/png', 1795952, '2026-06-27 17:39:04');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (4, 'lecithin-chocolate-thumb-1782581944.png', 'assets/blogs/thumbnails/lecithin-chocolate-thumb-1782581944.png', 'image/png', 1795952, '2026-06-27 17:39:04');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (5, 'does-a2-milk-really-matter-in-chocolate-header-1783106000.png', 'assets/blogs/does-a2-milk-really-matter-in-chocolate-header-1783106000.png', 'image/png', 1657411, '2026-07-03 19:13:20');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (6, 'does-a2-milk-really-matter-in-chocolate-thumb-1783106000.png', 'assets/blogs/thumbnails/does-a2-milk-really-matter-in-chocolate-thumb-1783106000.png', 'image/png', 1657411, '2026-07-03 19:13:20');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (7, 'does-a2-milk-really-matter-in-chocolate-header-1783347144.png', 'assets/blogs/does-a2-milk-really-matter-in-chocolate-header-1783347144.png', 'image/png', 1657411, '2026-07-06 14:12:24');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (8, 'does-a2-milk-really-matter-in-chocolate-thumb-1783347144.png', 'assets/blogs/thumbnails/does-a2-milk-really-matter-in-chocolate-thumb-1783347144.png', 'image/png', 1657411, '2026-07-06 14:12:24');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (9, 'freeze-dried-fruits-chocolate-header-1783442531.png', 'assets/blogs/freeze-dried-fruits-chocolate-header-1783442531.png', 'image/png', 1219727, '2026-07-07 16:42:11');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (10, 'freeze-dried-fruits-chocolate-thumb-1783442531.png', 'assets/blogs/thumbnails/freeze-dried-fruits-chocolate-thumb-1783442531.png', 'image/png', 1219727, '2026-07-07 16:42:11');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (11, 'lecithin-chocolate-header-1783442921.png', 'assets/blogs/lecithin-chocolate-header-1783442921.png', 'image/png', 1517129, '2026-07-07 16:48:41');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (12, 'lecithin-chocolate-thumb-1783442921.png', 'assets/blogs/thumbnails/lecithin-chocolate-thumb-1783442921.png', 'image/png', 1517129, '2026-07-07 16:48:41');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (13, 'lecithin-chocolate-header-1783443271.png', 'assets/blogs/lecithin-chocolate-header-1783443271.png', 'image/png', 1753771, '2026-07-07 16:54:31');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (14, 'lecithin-chocolate-thumb-1783443271.png', 'assets/blogs/thumbnails/lecithin-chocolate-thumb-1783443271.png', 'image/png', 1753771, '2026-07-07 16:54:31');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (15, 'milkfat-chocolate-header-1783444085.png', 'assets/blogs/milkfat-chocolate-header-1783444085.png', 'image/png', 1306087, '2026-07-07 17:08:05');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (16, 'milkfat-chocolate-thumb-1783444085.png', 'assets/blogs/thumbnails/milkfat-chocolate-thumb-1783444085.png', 'image/png', 1306087, '2026-07-07 17:08:05');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (17, 'fat-bloom-sugar-bloom-header-1783446365.png', 'assets/blogs/fat-bloom-sugar-bloom-header-1783446365.png', 'image/png', 1763790, '2026-07-07 17:46:05');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (18, 'fat-bloom-sugar-bloom-thumb-1783446365.png', 'assets/blogs/thumbnails/fat-bloom-sugar-bloom-thumb-1783446365.png', 'image/png', 1763790, '2026-07-07 17:46:05');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (19, 'flavor-chocolate-header-1783446507.png', 'assets/blogs/flavor-chocolate-header-1783446507.png', 'image/png', 1545796, '2026-07-07 17:48:27');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (20, 'flavor-chocolate-thumb-1783446507.png', 'assets/blogs/thumbnails/flavor-chocolate-thumb-1783446507.png', 'image/png', 1545796, '2026-07-07 17:48:27');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (21, 'intimacy-chocolate-header-1783446566.png', 'assets/blogs/intimacy-chocolate-header-1783446566.png', 'image/png', 1403460, '2026-07-07 17:49:26');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (22, 'intimacy-chocolate-thumb-1783446566.png', 'assets/blogs/thumbnails/intimacy-chocolate-thumb-1783446566.png', 'image/png', 1403460, '2026-07-07 17:49:26');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (23, 'cocoa-ph-header-1783446779.png', 'assets/blogs/cocoa-ph-header-1783446779.png', 'image/png', 1399991, '2026-07-07 17:52:59');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (24, 'cocoa-ph-thumb-1783446811.png', 'assets/blogs/thumbnails/cocoa-ph-thumb-1783446811.png', 'image/png', 1399991, '2026-07-07 17:53:31');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (25, 'freeze-dried-fruits-chocolate-header-1783516980.png', 'assets/blogs/freeze-dried-fruits-chocolate-header-1783516980.png', 'image/png', 1219727, '2026-07-08 13:23:00');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (26, 'freeze-dried-fruits-chocolate-thumb-1783516980.png', 'assets/blogs/thumbnails/freeze-dried-fruits-chocolate-thumb-1783516980.png', 'image/png', 1219727, '2026-07-08 13:23:00');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (27, 'lecithin-chocolate-header-1783517005.png', 'assets/blogs/lecithin-chocolate-header-1783517005.png', 'image/png', 1753771, '2026-07-08 13:23:25');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (28, 'lecithin-chocolate-thumb-1783517005.png', 'assets/blogs/thumbnails/lecithin-chocolate-thumb-1783517005.png', 'image/png', 1753771, '2026-07-08 13:23:25');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (29, 'cocoa-ph-header-1783517030.png', 'assets/blogs/cocoa-ph-header-1783517030.png', 'image/png', 1399991, '2026-07-08 13:23:50');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (30, 'cocoa-ph-thumb-1783517030.png', 'assets/blogs/thumbnails/cocoa-ph-thumb-1783517030.png', 'image/png', 1399991, '2026-07-08 13:23:50');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (31, 'milkfat-chocolate-header-1783517085.png', 'assets/blogs/milkfat-chocolate-header-1783517085.png', 'image/png', 1306087, '2026-07-08 13:24:45');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (32, 'milkfat-chocolate-thumb-1783517085.png', 'assets/blogs/thumbnails/milkfat-chocolate-thumb-1783517085.png', 'image/png', 1306087, '2026-07-08 13:24:45');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (33, 'flavor-chocolate-header-1783517119.png', 'assets/blogs/flavor-chocolate-header-1783517119.png', 'image/png', 1545796, '2026-07-08 13:25:19');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (34, 'flavor-chocolate-thumb-1783517119.png', 'assets/blogs/thumbnails/flavor-chocolate-thumb-1783517119.png', 'image/png', 1545796, '2026-07-08 13:25:19');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (35, 'fat-bloom-sugar-bloom-header-1783517161.png', 'assets/blogs/fat-bloom-sugar-bloom-header-1783517161.png', 'image/png', 1763790, '2026-07-08 13:26:01');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (36, 'fat-bloom-sugar-bloom-thumb-1783517161.png', 'assets/blogs/thumbnails/fat-bloom-sugar-bloom-thumb-1783517161.png', 'image/png', 1763790, '2026-07-08 13:26:01');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (37, 'intimacy-chocolate-header-1783517187.png', 'assets/blogs/intimacy-chocolate-header-1783517187.png', 'image/png', 1403460, '2026-07-08 13:26:27');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (38, 'intimacy-chocolate-thumb-1783517187.png', 'assets/blogs/thumbnails/intimacy-chocolate-thumb-1783517187.png', 'image/png', 1403460, '2026-07-08 13:26:27');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (39, 'does-a2-milk-really-matter-in-chocolate-header-1783928060.jpeg', 'assets/blogs/does-a2-milk-really-matter-in-chocolate-header-1783928060.jpeg', 'image/jpeg', 183140, '2026-07-13 07:34:20');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (40, 'does-a2-milk-really-matter-in-chocolate-thumb-1783928060.jpeg', 'assets/blogs/thumbnails/does-a2-milk-really-matter-in-chocolate-thumb-1783928060.jpeg', 'image/jpeg', 183140, '2026-07-13 07:34:20');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (41, 'cocoa-ph-header-1783928110.jpeg', 'assets/blogs/cocoa-ph-header-1783928110.jpeg', 'image/jpeg', 175458, '2026-07-13 07:35:10');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (42, 'cocoa-ph-thumb-1783928110.jpeg', 'assets/blogs/thumbnails/cocoa-ph-thumb-1783928110.jpeg', 'image/jpeg', 175458, '2026-07-13 07:35:10');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (43, 'milkfat-chocolate-header-1783928152.jpeg', 'assets/blogs/milkfat-chocolate-header-1783928152.jpeg', 'image/jpeg', 146984, '2026-07-13 07:35:52');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (44, 'milkfat-chocolate-thumb-1783928152.jpeg', 'assets/blogs/thumbnails/milkfat-chocolate-thumb-1783928152.jpeg', 'image/jpeg', 146984, '2026-07-13 07:35:52');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (45, 'flavor-chocolate-header-1783928218.jpeg', 'assets/blogs/flavor-chocolate-header-1783928218.jpeg', 'image/jpeg', 203960, '2026-07-13 07:36:58');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (46, 'flavor-chocolate-thumb-1783928218.jpeg', 'assets/blogs/thumbnails/flavor-chocolate-thumb-1783928218.jpeg', 'image/jpeg', 203960, '2026-07-13 07:36:58');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (47, 'lecithin-chocolate-header-1783928248.jpeg', 'assets/blogs/lecithin-chocolate-header-1783928248.jpeg', 'image/jpeg', 178572, '2026-07-13 07:37:28');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (48, 'lecithin-chocolate-thumb-1783928248.jpeg', 'assets/blogs/thumbnails/lecithin-chocolate-thumb-1783928248.jpeg', 'image/jpeg', 178572, '2026-07-13 07:37:28');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (49, 'fat-bloom-sugar-bloom-header-1783928274.jpeg', 'assets/blogs/fat-bloom-sugar-bloom-header-1783928274.jpeg', 'image/jpeg', 266673, '2026-07-13 07:37:54');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (50, 'fat-bloom-sugar-bloom-thumb-1783928274.jpeg', 'assets/blogs/thumbnails/fat-bloom-sugar-bloom-thumb-1783928274.jpeg', 'image/jpeg', 266673, '2026-07-13 07:37:54');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (51, 'freeze-dried-fruits-chocolate-header-1783928296.jpeg', 'assets/blogs/freeze-dried-fruits-chocolate-header-1783928296.jpeg', 'image/jpeg', 150611, '2026-07-13 07:38:16');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (52, 'freeze-dried-fruits-chocolate-thumb-1783928296.jpeg', 'assets/blogs/thumbnails/freeze-dried-fruits-chocolate-thumb-1783928296.jpeg', 'image/jpeg', 150611, '2026-07-13 07:38:16');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (53, 'intimacy-chocolate-header-1783928314.jpeg', 'assets/blogs/intimacy-chocolate-header-1783928314.jpeg', 'image/jpeg', 147688, '2026-07-13 07:38:34');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (54, 'intimacy-chocolate-thumb-1783928314.jpeg', 'assets/blogs/thumbnails/intimacy-chocolate-thumb-1783928314.jpeg', 'image/jpeg', 147688, '2026-07-13 07:38:34');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (55, 'why-did-investors-bet-9-million-on-this-chocolate-brand-header-1783941097.png', 'assets/blogs/why-did-investors-bet-9-million-on-this-chocolate-brand-header-1783941097.png', 'image/png', 2150813, '2026-07-13 11:11:37');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (56, 'why-did-investors-bet-9-million-on-this-chocolate-brand-thumb-1783941097.png', 'assets/blogs/thumbnails/why-did-investors-bet-9-million-on-this-chocolate-brand-thumb-1783941097.png', 'image/png', 2150813, '2026-07-13 11:11:37');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (57, 'does-a2-milk-really-matter-in-chocolate-header-1783978968.jpeg', 'assets/blogs/does-a2-milk-really-matter-in-chocolate-header-1783978968.jpeg', 'image/jpeg', 183140, '2026-07-13 21:42:48');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (58, 'does-a2-milk-really-matter-in-chocolate-thumb-1783978968.jpeg', 'assets/blogs/thumbnails/does-a2-milk-really-matter-in-chocolate-thumb-1783978968.jpeg', 'image/jpeg', 183140, '2026-07-13 21:42:48');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (59, 'freeze-dried-fruits-chocolate-header-1783979005.jpeg', 'assets/blogs/freeze-dried-fruits-chocolate-header-1783979005.jpeg', 'image/jpeg', 150611, '2026-07-13 21:43:25');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (60, 'freeze-dried-fruits-chocolate-thumb-1783979005.jpeg', 'assets/blogs/thumbnails/freeze-dried-fruits-chocolate-thumb-1783979005.jpeg', 'image/jpeg', 150611, '2026-07-13 21:43:25');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (61, 'lecithin-chocolate-header-1783979034.jpeg', 'assets/blogs/lecithin-chocolate-header-1783979034.jpeg', 'image/jpeg', 178572, '2026-07-13 21:43:54');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (62, 'lecithin-chocolate-thumb-1783979034.jpeg', 'assets/blogs/thumbnails/lecithin-chocolate-thumb-1783979034.jpeg', 'image/jpeg', 178572, '2026-07-13 21:43:54');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (63, 'cocoa-ph-header-1783979050.jpeg', 'assets/blogs/cocoa-ph-header-1783979050.jpeg', 'image/jpeg', 175458, '2026-07-13 21:44:10');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (64, 'cocoa-ph-thumb-1783979050.jpeg', 'assets/blogs/thumbnails/cocoa-ph-thumb-1783979050.jpeg', 'image/jpeg', 175458, '2026-07-13 21:44:10');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (65, 'milkfat-chocolate-header-1783979065.jpeg', 'assets/blogs/milkfat-chocolate-header-1783979065.jpeg', 'image/jpeg', 146984, '2026-07-13 21:44:25');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (66, 'milkfat-chocolate-thumb-1783979065.jpeg', 'assets/blogs/thumbnails/milkfat-chocolate-thumb-1783979065.jpeg', 'image/jpeg', 146984, '2026-07-13 21:44:25');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (67, 'fat-bloom-sugar-bloom-header-1783979111.jpeg', 'assets/blogs/fat-bloom-sugar-bloom-header-1783979111.jpeg', 'image/jpeg', 266673, '2026-07-13 21:45:11');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (68, 'fat-bloom-sugar-bloom-thumb-1783979111.jpeg', 'assets/blogs/thumbnails/fat-bloom-sugar-bloom-thumb-1783979111.jpeg', 'image/jpeg', 266673, '2026-07-13 21:45:11');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (69, 'intimacy-chocolate-header-1783979124.jpeg', 'assets/blogs/intimacy-chocolate-header-1783979124.jpeg', 'image/jpeg', 147688, '2026-07-13 21:45:24');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (70, 'intimacy-chocolate-thumb-1783979124.jpeg', 'assets/blogs/thumbnails/intimacy-chocolate-thumb-1783979124.jpeg', 'image/jpeg', 147688, '2026-07-13 21:45:24');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (71, 'flavor-chocolate-header-1783979142.jpeg', 'assets/blogs/flavor-chocolate-header-1783979142.jpeg', 'image/jpeg', 203960, '2026-07-13 21:45:42');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (72, 'flavor-chocolate-thumb-1783979142.jpeg', 'assets/blogs/thumbnails/flavor-chocolate-thumb-1783979142.jpeg', 'image/jpeg', 203960, '2026-07-13 21:45:42');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (73, 'why-did-investors-bet-9-million-on-this-chocolate-brand-header-1784009411.png', 'assets/blogs/why-did-investors-bet-9-million-on-this-chocolate-brand-header-1784009411.png', 'image/png', 2150813, '2026-07-14 06:10:11');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (74, 'why-did-investors-bet-9-million-on-this-chocolate-brand-thumb-1784009411.png', 'assets/blogs/thumbnails/why-did-investors-bet-9-million-on-this-chocolate-brand-thumb-1784009411.png', 'image/png', 2150813, '2026-07-14 06:10:11');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (75, 'freeze-dried-fruits-chocolate-header-1784800721.jpeg', 'assets/blogs/freeze-dried-fruits-chocolate-header-1784800721.jpeg', 'image/jpeg', 150611, '2026-07-23 09:58:41');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (76, 'freeze-dried-fruits-chocolate-thumb-1784800721.jpeg', 'assets/blogs/thumbnails/freeze-dried-fruits-chocolate-thumb-1784800721.jpeg', 'image/jpeg', 150611, '2026-07-23 09:58:41');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (77, 'lecithin-chocolate-header-1784800782.jpeg', 'assets/blogs/lecithin-chocolate-header-1784800782.jpeg', 'image/jpeg', 178572, '2026-07-23 09:59:42');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (78, 'lecithin-chocolate-thumb-1784800782.jpeg', 'assets/blogs/thumbnails/lecithin-chocolate-thumb-1784800782.jpeg', 'image/jpeg', 178572, '2026-07-23 09:59:42');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (79, 'cocoa-ph-header-1784800805.jpeg', 'assets/blogs/cocoa-ph-header-1784800805.jpeg', 'image/jpeg', 175458, '2026-07-23 10:00:05');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (80, 'cocoa-ph-thumb-1784800805.jpeg', 'assets/blogs/thumbnails/cocoa-ph-thumb-1784800805.jpeg', 'image/jpeg', 175458, '2026-07-23 10:00:05');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (81, 'milkfat-chocolate-header-1784800853.jpeg', 'assets/blogs/milkfat-chocolate-header-1784800853.jpeg', 'image/jpeg', 266673, '2026-07-23 10:00:53');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (82, 'milkfat-chocolate-thumb-1784800853.jpeg', 'assets/blogs/thumbnails/milkfat-chocolate-thumb-1784800853.jpeg', 'image/jpeg', 266673, '2026-07-23 10:00:53');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (83, 'flavor-chocolate-header-1784800898.jpeg', 'assets/blogs/flavor-chocolate-header-1784800898.jpeg', 'image/jpeg', 203960, '2026-07-23 10:01:38');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (84, 'flavor-chocolate-thumb-1784800898.jpeg', 'assets/blogs/thumbnails/flavor-chocolate-thumb-1784800898.jpeg', 'image/jpeg', 203960, '2026-07-23 10:01:38');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (85, 'milkfat-chocolate-header-1784800931.jpeg', 'assets/blogs/milkfat-chocolate-header-1784800931.jpeg', 'image/jpeg', 146984, '2026-07-23 10:02:11');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (86, 'milkfat-chocolate-thumb-1784800931.jpeg', 'assets/blogs/thumbnails/milkfat-chocolate-thumb-1784800931.jpeg', 'image/jpeg', 146984, '2026-07-23 10:02:11');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (87, 'fat-bloom-sugar-bloom-header-1784800954.jpeg', 'assets/blogs/fat-bloom-sugar-bloom-header-1784800954.jpeg', 'image/jpeg', 266673, '2026-07-23 10:02:34');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (88, 'fat-bloom-sugar-bloom-thumb-1784800954.jpeg', 'assets/blogs/thumbnails/fat-bloom-sugar-bloom-thumb-1784800954.jpeg', 'image/jpeg', 266673, '2026-07-23 10:02:34');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (89, 'intimacy-chocolate-header-1784800971.jpeg', 'assets/blogs/intimacy-chocolate-header-1784800971.jpeg', 'image/jpeg', 147688, '2026-07-23 10:02:51');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (90, 'intimacy-chocolate-thumb-1784800971.jpeg', 'assets/blogs/thumbnails/intimacy-chocolate-thumb-1784800971.jpeg', 'image/jpeg', 147688, '2026-07-23 10:02:51');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (91, '5-ingredients-that-should-never-be-in-your-chocolate-header-1785085758.png', 'assets/blogs/5-ingredients-that-should-never-be-in-your-chocolate-header-1785085758.png', 'image/png', 1677306, '2026-07-26 17:09:18');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (92, '5-ingredients-that-should-never-be-in-your-chocolate-thumb-1785085758.png', 'assets/blogs/thumbnails/5-ingredients-that-should-never-be-in-your-chocolate-thumb-1785085758.png', 'image/png', 1677306, '2026-07-26 17:09:18');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (93, 'why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-header-1785237285.png', 'assets/blogs/why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-header-1785237285.png', 'image/png', 2363873, '2026-07-28 11:14:45');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (94, 'why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-thumb-1785237285.png', 'assets/blogs/thumbnails/why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-thumb-1785237285.png', 'image/png', 2363873, '2026-07-28 11:14:45');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (95, 'why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-header-1785237485.png', 'assets/blogs/why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-header-1785237485.png', 'image/png', 1864520, '2026-07-28 11:18:05');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (96, 'why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-thumb-1785237485.png', 'assets/blogs/thumbnails/why-foodstories-is-a-must-visit-for-bean-to-bar-chocolate-enthusiasts-thumb-1785237485.png', 'image/png', 1864520, '2026-07-28 11:18:05');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (97, 'why-is-the-chocolate-industry-dominated-by-nuts-header-1785913213.png', 'assets/blogs/why-is-the-chocolate-industry-dominated-by-nuts-header-1785913213.png', 'image/png', 1701027, '2026-08-05 07:00:13');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (98, 'why-is-the-chocolate-industry-dominated-by-nuts-thumb-1785913213.png', 'assets/blogs/thumbnails/why-is-the-chocolate-industry-dominated-by-nuts-thumb-1785913213.png', 'image/png', 1701027, '2026-08-05 07:00:13');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (99, 'difference-between-dutch-vs-natual-cocoa-powder-header-1788546695.png', 'assets/blogs/difference-between-dutch-vs-natual-cocoa-powder-header-1788546695.png', 'image/png', 1961474, '2026-09-04 18:31:35');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (100, 'difference-between-dutch-vs-natual-cocoa-powder-thumb-1788546695.png', 'assets/blogs/thumbnails/difference-between-dutch-vs-natual-cocoa-powder-thumb-1788546695.png', 'image/png', 1961474, '2026-09-04 18:31:35');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (101, 'difference-between-dutch-vs-natual-cocoa-powder-header-1788547637.png', 'assets/blogs/difference-between-dutch-vs-natual-cocoa-powder-header-1788547637.png', 'image/png', 1894891, '2026-09-04 18:47:17');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (102, 'difference-between-dutch-vs-natual-cocoa-powder-thumb-1788547637.png', 'assets/blogs/thumbnails/difference-between-dutch-vs-natual-cocoa-powder-thumb-1788547637.png', 'image/png', 1894891, '2026-09-04 18:47:17');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (103, 'Almond halwa spread.png', 'assets/products/prod-almond-halwa-spread-1789236468.png', 'image/png', 2249232, '2026-09-12 18:07:48');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (104, 'cashew coconut spread.png', 'assets/products/prod-cashew-coconut-spread-1789237418.png', 'image/png', 2071618, '2026-09-12 18:23:38');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (105, 'crunchy almond spread.png', 'assets/products/prod-almond-spread-1789238078.png', 'image/png', 2284192, '2026-09-12 18:34:38');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (106, 'hazelnut spread.png', 'assets/products/prod-bean-to-bar-dark-chocolate-1789238479.png', 'image/png', 2087970, '2026-09-12 18:41:19');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (107, 'almond bark.png', 'assets/products/prod-almond-bark-1789238647.png', 'image/png', 2010925, '2026-09-12 18:44:07');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (108, '44390d15-5118-45d7-bbb1-1bfa68ad56dd.png', 'assets/products/prod-customized-chocolate-inclusion-bar-1789409649.png', 'image/png', 2416857, '2026-09-14 18:14:09');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (109, '5521abf2-2970-4708-a66b-78ceef2c1187.png', 'assets/products/prod-customized-chocolate-inclusion-bar-1789410234.png', 'image/png', 2197749, '2026-09-14 18:23:54');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (110, 'hazelnut spread.png', 'assets/products/prod-hazelnut-spread-clean-label-product-healthy-gifting-1789410992.png', 'image/png', 2087970, '2026-09-14 18:36:32');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (111, 'fe5f2c4e-b17c-4658-a252-91178344d743 (1).png', 'assets/products/prod-gourmet-stuffed-dates-gift-box-1789411226.png', 'image/png', 2507514, '2026-09-14 18:40:26');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (112, 'rocher.png', 'assets/products/prod-the-rocher-collection-1789469648.png', 'image/png', 2313179, '2026-09-15 10:54:08');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (113, 'rocher.png', 'assets/products/prod-the-rocher-collection-1790015004.png', 'image/png', 2313179, '2026-09-21 18:23:24');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (114, 'fe5f2c4e-b17c-4658-a252-91178344d743.png', 'assets/products/prod-gourmet-stuffed-dates-gift-box-1790015134.png', 'image/png', 2507514, '2026-09-21 18:25:34');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (115, 'almond bark.png', 'assets/products/prod-almond-bark-1790015356.png', 'image/png', 2010925, '2026-09-21 18:29:16');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (116, 'hazelnut spread.png', 'assets/products/prod-hazelnut-spread-clean-label-product-healthy-gifting-1790015567.png', 'image/png', 2087970, '2026-09-21 18:32:47');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (117, 'crunchy almond spread.png', 'assets/products/prod-almond-spread-1790015745.png', 'image/png', 2284192, '2026-09-21 18:35:45');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (118, 'cashew coconut spread.png', 'assets/products/prod-cashew-coconut-spread-1790015793.png', 'image/png', 2071618, '2026-09-21 18:36:33');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (119, 'Almond halwa spread.png', 'assets/products/prod-almond-halwa-spread-1790077418.png', 'image/png', 2249232, '2026-09-22 11:43:38');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (120, 'classic almond spread.png', 'assets/products/prod-classic-almond-spread-1790078880.png', 'image/png', 2167501, '2026-09-22 12:08:00');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (121, 'inline-1790444541-8227.png', 'assets/blogs/inline-1790444541-8227.png', 'image/png', 1496439, '2026-09-26 17:42:21');
INSERT INTO `media` (`id`, `filename`, `path`, `mime_type`, `size`, `uploaded_at`) VALUES (122, 'inline-1790444631-9492.png', 'assets/blogs/inline-1790444631-9492.png', 'image/png', 1496439, '2026-09-26 17:43:51');

-- --------------------------------------------------------
-- Table structure for `site_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `site_settings` (35 rows)
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('admin_session_salt', '78433b42673600cb18dc6a6c62ccbd02');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('beta_url', 'https://www.rtchocos.com/beta');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('bulk_enquiry_phone', '+919140238741');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('bulk_min_order_qty', '50 Units');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('contact_email', 'hello@rtchocos.com');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('contact_phone', '+919140238741');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('custom_css', '');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('custom_head_scripts', '');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('default_site_theme', 'theme-teal-sage');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_bypass_key', 'rtdev2026');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_eta', '2026-09-08 12:50:34');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_image', 'assets/ph.png');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_media_type', 'both');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_message', 'We are fine-tuning our handcrafted batches and platform to bring you an even more delightful bean-to-bar experience. We will be back online shortly!');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_mode', '0');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_notify_enabled', '0');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_subtitle', 'Our chocolate laboratory is currently undergoing planned improvements.');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_title', 'We\'re Perfecting Something Delicious');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_video', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('meta_description', 'Experience the real taste of premium chocolates crafted with love and science.');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('newsletter_text', 'Subscribe to our newsletter for exclusive recipes, scientific cocoa insights, and new product releases.');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('retail_free_shipping_min', '1499');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('show_announcement_banner', '0');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('show_theme_tester', '1');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('site_name', 'RT Chocos');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('site_tagline', 'Real Taste, Real Chocolate');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('site_url', 'https://www.rtchocos.com');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('social_facebook', 'https://facebook.com/rtchocos');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('social_instagram', 'https://instagram.com/rtchocos');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('social_linkedin', 'https://linkedin.com/company/rtchocos');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('social_youtube', 'https://youtube.com/rtchocos');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('store_announcement_link_text', 'Get Instant Quote →');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('store_announcement_link_url', '/shop.php#bulk-enquiry');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('store_announcement_text', 'Phase 2 Test Announcement Bar');
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('store_mode', 'bulk');

-- --------------------------------------------------------
-- Table structure for `faqs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `category` enum('general','workshops','shop','shipping','courses') NOT NULL DEFAULT 'general',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category_order` (`category`,`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `faqs` (15 rows)
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (1, 'What is RT Chocos?', 'RT Chocos is India\'s first chocolate blogging website and bean-to-bar learning academy. Founded by Aarti Saluja Sahni, we teach the science of cocoa processing, formulation, and tempering while offering professional workshops and premium craft chocolate tools.', 'general', 1, 1, '2026-07-13 21:26:01', '2026-07-13 21:26:01');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (2, 'What does bean-to-bar mean?', 'Bean-to-bar refers to the process where a chocolate maker controls every step of production, starting from raw cacao beans to the finished chocolate bar. This includes sourcing, roasting, cracking, winnowing, grinding, conching, aging, tempering, and molding.', 'general', 2, 1, '2026-07-13 21:26:01', '2026-07-13 21:26:01');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (3, 'Who is Aarti Saluja Sahni?', 'Aarti Saluja Sahni is the founder of RT Chocos, India\'s first chocolate educator and consulting expert. With over a decade of experience in bean-to-bar chocolate making, recipe formulation, and brand consulting, she has trained more than 2,000 students across India.', 'general', 3, 1, '2026-07-13 21:26:01', '2026-07-13 21:26:01');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (4, 'What workshops does RT Chocos offer?', 'We offer professional bean-to-bar workshops, tempering science masterclasses, and chocolate making courses in Mumbai and online. These sessions cover cacao selection, roasting profiles, tempering chemistry, and flavor formulation.', 'workshops', 1, 1, '2026-07-13 21:26:01', '2026-07-13 21:26:01');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (5, 'Do I get a certificate after completing a workshop?', 'Yes, participants receive an official Certificate of Completion from RT Chocos Chocolate Academy. This certificate recognizes your training in professional bean-to-bar craft chocolate making and chocolate science.', 'workshops', 2, 1, '2026-07-13 21:26:01', '2026-07-13 21:26:01');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (6, 'Can I attend workshops online?', 'Yes, we conduct live online interactive workshops via Zoom. We ship a curated ingredient and toolkit box to your address before the session so you can practice bean-to-bar and tempering science hands-on along with Aarti.', 'workshops', 3, 1, '2026-07-13 21:26:01', '2026-07-13 21:26:01');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (7, 'What products does RT Chocos sell?', 'We sell premium artisan bean-to-bar dark chocolates, roasted single-origin cacao nibs, home chocolate making starter kits, and professional recipe formulation guides designed by chocolate consultant Aarti Saluja Sahni.', 'shop', 1, 1, '2026-07-13 21:26:01', '2026-07-13 21:26:01');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (8, 'Are RT Chocos products vegetarian?', 'Yes, all products in our online shop are 100% vegetarian. Our dark chocolate bars are also vegan, dairy-free, gluten-free, and made using only organic cane sugar and pure single-origin Indian cocoa beans.', 'shop', 2, 1, '2026-07-13 21:26:02', '2026-07-13 21:26:02');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (9, 'Can I return or exchange products?', 'Due to the perishable nature of artisanal chocolate, we do not accept returns or exchanges on food items once shipped. For tools, starter kits, or damaged items, contact hello@rtchocos.com within 7 days of delivery for a replacement.', 'shop', 3, 1, '2026-07-13 21:26:02', '2026-07-13 21:26:02');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (10, 'Do you ship across India?', 'Yes, we ship our chocolates, kits, and tools to all major cities and towns across India. We partner with express temperature-controlled courier networks to ensure your chocolate products arrive safely without melting.', 'shipping', 1, 1, '2026-07-13 21:26:02', '2026-07-13 21:26:02');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (11, 'How are chocolates packed for shipping?', 'Every chocolate order is shipped in food-grade insulated boxes containing reusable ice gel packs. This protective packaging keeps the temperature cool throughout transit, maintaining the chocolate\'s tempered state.', 'shipping', 2, 1, '2026-07-13 21:26:02', '2026-07-13 21:26:02');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (12, 'Is there free shipping?', 'We offer free standard shipping across India on all orders of ₹999 and above. For orders under ₹999, a flat shipping fee of ₹99 is applied at checkout to cover temperature-controlled packaging costs.', 'shipping', 3, 1, '2026-07-13 21:26:02', '2026-07-13 21:26:02');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (13, 'How is a course different from a workshop?', 'Workshops are hands-on, single-session masterclasses focusing on a specific skill (e.g. tempering). Professional courses are comprehensive, multi-week programs covering everything from raw cacao chemistry to scale business setup.', 'courses', 1, 1, '2026-07-13 21:26:02', '2026-07-13 21:26:02');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (14, 'What are the prerequisites for joining a course?', 'There are no prerequisites! Our bean-to-bar chocolate making courses are designed to take you from a complete beginner to a confident craft chocolate maker. No culinary background is required to enroll.', 'courses', 2, 1, '2026-07-13 21:26:02', '2026-07-13 21:26:02');
INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES (15, 'How much do courses cost?', 'Course fees vary depending on the depth, duration, and format (online vs. in-person). Standard online workshops start from ₹4,999, while professional certification programs range between ₹14,999 and ₹29,999.', 'courses', 3, 1, '2026-07-13 21:26:02', '2026-07-13 21:26:02');

-- --------------------------------------------------------
-- Table structure for `ai_ingredient_spotlights`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `ai_ingredient_spotlights`;
CREATE TABLE `ai_ingredient_spotlights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(150) NOT NULL,
  `tag` varchar(100) NOT NULL DEFAULT '? INGREDIENT SPOTLIGHT',
  `short_desc` varchar(255) NOT NULL,
  `detailed_notes` text DEFAULT NULL,
  `flavor_notes` varchar(150) DEFAULT NULL,
  `origin_region` varchar(100) DEFAULT NULL,
  `modal_key` varchar(50) NOT NULL DEFAULT 'bean-to-bar',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `ai_ingredient_spotlights` (20 rows)
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (13, 'Single-Origin Madagascar Vanilla', '🌱 INGREDIENT SPOTLIGHT', 'Luxuriously aromatic Madagascar vanilla beans infuse chocolate with deep, velvety vanillin richness.', 'Hand-pollinated Bourbon vanilla pods are slow-infused to preserve delicate phenolic compounds. Our cold-conching process amplifies its natural sweetness and complexity.', 'spicy, sweet, creamy, floral', 'Sambatche Madagascar', 'bean-to-bar', '2026-09-19 07:02:20');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (14, 'Smoked Fleur de Sel', '🌱 INGREDIENT SPOTLIGHT', 'A whisper of brine and smoke transforms chocolate into a richer, more magnetic tasting experience.', 'Hand-sifted in tiny pinches, it sharpens sweetness and amplifies cocoa’s natural depth. Its slow melt releases a savory finish that keeps the palate engaged.', 'Sea Salt, Smoked Oak, Caramel, Dark Cocoa', 'Biarritz Coast, France', 'bean-to-bar', '2026-09-20 13:29:40');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (15, 'Cold-Pressed Hazelnut Gianduja', '🌱 INGREDIENT SPOTLIGHT', 'Transforms chocolate with an intoxicatingly smooth, nutty richness that melts like silk on the tongue.', 'We stone-grind toasted Piemonte hazelnuts to release their natural oils, creating a luscious emulsion without added palm oil. The cold-pressing preserves delicate aromatics and maintains the raw enzymatic integrity of the nut.', 'toasted hazelnut, caramelized butter, dark cocoa, roasted vanilla', 'Piedmont, Italy', 'bean-to-bar', '2026-09-21 06:29:17');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (16, 'Cold-Pressed Hazelnut Gianduja', '🌱 INGREDIENT SPOTLIGHT', 'Silky roasted hazelnuts wrap dark chocolate in plush richness and warm, nutty elegance.', 'Milled into a fluid paste, the nuts emulsify naturally with cocoa butter. Slow conching creates velvety texture while preserving toasted depth.', 'Toasted hazelnut, caramelized cream, roasted nuts, warm praline', 'Piedmont, Italy', 'bean-to-bar', '2026-09-21 17:56:13');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (17, 'Criollo Cacao Nibs', '🌱 INGREDIENT SPOTLIGHT', 'Their intense, fruity crunch transforms dark chocolate into a vibrant, antioxidant-rich masterpiece.', 'Stone-ground slowly to preserve delicate flavanols. Cold-processing locks in the bean\'s native fruit esters.', 'fruity, earthy, wine-like, crunchy', 'Cusco Valley, Peru', 'bean-to-bar', '2026-09-22 10:02:46');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (18, 'Roasted Idukki Cacao Nibs', '🌱 INGREDIENT SPOTLIGHT', 'Toasty cacao fragments add crackling crunch and wild, roasted depth to dark chocolate.', 'Nibs are gently roasted to amplify aroma without burning their delicate oils. Their varied particle sizes create lively texture across the bar.', 'Toasted cacao, espresso, hazelnut, earthy spice', 'Idukki Valley, Kerala, India', 'bean-to-bar', '2026-09-22 16:47:51');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (19, 'Smoked Fleur de Sel', '🌱 INGREDIENT SPOTLIGHT', 'Flaky sea salt kissed by applewood smoke amplifies chocolate\'s deep, complex cocoa notes.', 'Hand-harvested Guérande crystals undergo cold smoking to preserve delicate mineral structure. The smoky saline crystals dissolve slowly, creating dynamic flavor contrasts.', 'smoky, briny, mineral, caramel', 'Guérande, France', 'bean-to-bar', '2026-09-24 06:05:20');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (20, 'Toasted Tonka Bean', '🌱 INGREDIENT SPOTLIGHT', 'A warm, vanilla-kissed treasure that adds smoky depth and aromatic complexity to dark chocolate.', 'Slow-toasted to release coumarin-rich oils without bitterness. Its warm spice notes bridge vanilla and caramel in ganache.', 'warm vanilla, smoky almond, cinnamon, caramel', 'Amazonian Rainforest', 'bean-to-bar', '2026-09-24 12:15:15');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (21, 'Peruvian Cold-Pressed Cacao Butter', '🌱 INGREDIENT SPOTLIGHT', 'Silky fat that lends unmatched gloss and a whisper of floral sweetness.', 'Cold-pressed below 40°C to retain delicate volatile aromatics and pale golden hue. Its triglyceride structure yields a flawless snap and melt.', 'Buttery, floral, nutty, sweet', 'Peruvian Andes', 'bean-to-bar', '2026-09-24 19:56:40');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (22, 'Toasted Idukki Cacao Nibs', '🌱 INGREDIENT SPOTLIGHT', 'Roasted to unlock deep, fruity intensity that transforms dark chocolate into a velvety symphony.', 'Slow-roasted in small batches to develop complex Maillard reactions. The nibs are stone-ground to preserve delicate volatile aromatics.', 'roasted coffee, dried cherry, dark cocoa, smoky caramel', 'Idukki Valley, Kerala', 'bean-to-bar', '2026-09-25 02:33:58');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (23, 'Single-Origin Kerala Cocoa Butter', '🌱 INGREDIENT SPOTLIGHT', 'Velvety notes deepen chocolate\'s silkiness with a creamy, tropical finish.', 'Cold-pressed and gently refined, it creates a luxurious mouthfeel. Stable fat crystals produce a glossy snap and slow flavor release.', 'Creamy, tropical, floral, clean', 'Idukki Valley, Kerala', 'bean-to-bar', '2026-09-25 10:10:07');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (24, 'Smoked Fleur de Sel', '🌱 INGREDIENT SPOTLIGHT', 'A delicate, smoky sea salt amplifying deep cocoa notes with a mesmerizing sweet-savory finish.', 'Hand-harvested from pristine coastal marshes, the salt is cold-smoked over aged oak to preserve its mineral purity. This process creates micro-crystals that instantly dissolve on the tongue, enhancing the chocolate\'s aromatic complexity.', 'smoky, briny, mineral, caramel', 'Guérande Marshes', 'bean-to-bar', '2026-09-25 17:23:06');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (25, 'Toasted Pistachio Praline', '🌱 INGREDIENT SPOTLIGHT', 'Adds a silky, buttery crunch that deepens chocolate’s richness with nutty caramel elegance.', 'Crafted by slowly caramelizing premium Sicilian pistachios with pure cane sugar, then folding the warm praline into tempered chocolate for a seamless blend. The Maillard reaction creates complex toasty notes while preserving the pistachio’s delicate oil.', 'buttery, nutty, caramel, sea salt', 'Sicily, Italy', 'bean-to-bar', '2026-09-26 03:47:32');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (26, 'Bourbon Madagascar Vanilla', '🌱 INGREDIENT SPOTLIGHT', 'Velvety vanilla whispers transform chocolate into aromatic silk.', 'Our vanilla beans are slow‑cured in Bourbon barrels, infusing deep caramel nuances. This gentle extraction preserves delicate phenols, enhancing aroma without bitterness.', 'sweet, creamy, smoky, floral', 'Madagascar', 'bean-to-bar', '2026-09-26 03:47:35');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (27, 'Roasted Idukki Cacao Nibs', '🌱 INGREDIENT SPOTLIGHT', 'Crunchy, tannic nibs add rustic depth and bright fruit tension to silky dark chocolate.', 'Slow-roasted at 118°C to develop Maillard complexity while preserving volatile esters. Their fibrous structure creates micro-textural contrast that prolongs flavor release on the palate.', 'Tamarind, toasted almond, green banana, dark cherry', 'Idukki Valley, Kerala', 'bean-to-bar', '2026-09-26 13:22:03');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (28, 'Roasted Chuao Cacao Nibs', '🌱 INGREDIENT SPOTLIGHT', 'Roasted Chuao Cacao Nibs unveil deep fruit tannins and smoky sweetness that elevate dark chocolate.', 'Slow-roasted at low temperatures to preserve volatile aromatics. Stone-ground to retain delicate tannin structure.', 'dark fruit, smoke, almond, earth', 'Chuao Valley, Venezuela', 'bean-to-bar', '2026-09-26 17:15:33');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (29, 'Roasted Idukki Cacao Nibs', '🌱 INGREDIENT SPOTLIGHT', 'Tiny roasted nibs add textured crunch and deep earthy bitterness that transforms dark chocolate.', 'We hand-select ripe pods from the Idukki Valley for optimal flavor development. The slow roasting preserves complex nutty notes while enhancing natural bitterness.', 'earthy, smoky, bitter, subtle coffee', 'Idukki Valley, Kerala', 'bean-to-bar', '2026-09-27 13:18:24');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (30, 'Organic Coconut Blossom Sugar', '🌱 INGREDIENT SPOTLIGHT', 'Its caramel‑like sweetness deepens chocolate’s richness while adding a delicate tropical nuance.', 'Derived from the sap of coconut palm blossoms, it undergoes minimal heating to preserve its natural fructose and mineral profile. When blended into chocolate, its low glycemic index and subtle molasses notes create a smoother melt and lingering finish.', 'caramel, toasted coconut, mild molasses, hint of citrus', 'Philippines, Visayas region', 'bean-to-bar', '2026-09-28 02:02:01');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (31, 'Single-Origin Kerala Cocoa Butter', '🌱 INGREDIENT SPOTLIGHT', 'Silky Kerala cocoa butter adds a buttery melt and subtle tropical aroma to chocolate.', 'Extracted from hand‑picked Kerala cacao beans using cold‑press techniques, it preserves delicate fatty‑acid profiles. Its high stearic‑oleic ratio yields a glossy snap and lingering creaminess.', 'creamy, nutty, faint coconut, hint of spice', 'Idukki Valley, Kerala', 'bean-to-bar', '2026-09-28 12:35:19');
INSERT INTO `ai_ingredient_spotlights` (`id`, `ingredient_name`, `tag`, `short_desc`, `detailed_notes`, `flavor_notes`, `origin_region`, `modal_key`, `created_at`) VALUES (32, 'Roasted Idukki Cacao Nibs', '🌱 INGREDIENT SPOTLIGHT', 'Crunchy, nutty nibs deepen chocolate with earthy intensity and vibrant fruit undertones.', 'Slow-roasted at low temperatures to preserve delicate polyphenols and develop complex Maillard notes. Each nib is hand-sorted from single-estate Criollo beans for consistent texture.', 'Earthy, Nutty, Bright Berry, Toasted Cocoa', 'Idukki Valley, Kerala', 'bean-to-bar', '2026-09-28 19:10:24');

-- --------------------------------------------------------
-- Table structure for `ai_insights`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `ai_insights`;
CREATE TABLE `ai_insights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `insight_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=494 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `ai_insights` (5 rows)
INSERT INTO `ai_insights` (`id`, `insight_text`, `created_at`) VALUES (489, 'Precise tempering crystallizes cocoa butter into stable Form V, giving artisan chocolate its signature satisfying snap.', '2026-09-28 16:13:31');
INSERT INTO `ai_insights` (`id`, `insight_text`, `created_at`) VALUES (490, 'Conching transforms gritty paste into silky chocolate, unlocking complex flavors through patient artistry.', '2026-09-28 16:13:33');
INSERT INTO `ai_insights` (`id`, `insight_text`, `created_at`) VALUES (491, 'Ancient Mayans brewed cacao as the sacred drink of the gods, believing it granted wisdom and eternal life.', '2026-09-28 17:12:26');
INSERT INTO `ai_insights` (`id`, `insight_text`, `created_at`) VALUES (492, 'Cacao\'s ancient roots meet modern precision - master the tempering curve for perfect snap and shine', '2026-09-28 17:32:35');
INSERT INTO `ai_insights` (`id`, `insight_text`, `created_at`) VALUES (493, 'Ferment cacao beans 5‑7 days; the evolving aroma unlocks deep chocolate notes before roasting.', '2026-09-28 19:36:29');

-- --------------------------------------------------------
-- Table structure for `ai_class_facts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `ai_class_facts`;
CREATE TABLE `ai_class_facts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fact_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table `ai_class_facts` (5 rows)
INSERT INTO `ai_class_facts` (`id`, `fact_text`, `created_at`) VALUES (1, 'Tempering cocoa butter requires precisely forming Type V crystals for that satisfying snap and glossy finish.', '2026-07-07 21:05:43');
INSERT INTO `ai_class_facts` (`id`, `fact_text`, `created_at`) VALUES (2, 'Under-roasting cacao beans leads to high acidity and lack of deep chocolate flavor notes in the final bar.', '2026-07-07 21:05:43');
INSERT INTO `ai_class_facts` (`id`, `fact_text`, `created_at`) VALUES (3, 'The conching process reduces volatile acids (like acetic acid) and coats solid particles with cocoa butter for a smooth mouthfeel.', '2026-07-07 21:05:43');
INSERT INTO `ai_class_facts` (`id`, `fact_text`, `created_at`) VALUES (4, 'Adding lecithin (an emulsifier) reduces chocolate viscosity significantly, making it easier to mould or coat.', '2026-07-07 21:05:43');
INSERT INTO `ai_class_facts` (`id`, `fact_text`, `created_at`) VALUES (5, 'Roasting temperature profiles are varied: high-temperature short-time (HTST) for floral profiles, low-temperature long-time (LTLT) for earthy profiles.', '2026-07-07 21:05:43');

SET FOREIGN_KEY_CHECKS = 1;
