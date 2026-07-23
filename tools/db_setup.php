<?php
// tools/db_setup.php - Database Initialization and Migration Script
// IMPORTANT: Delete or restrict this file before deploying to production!

error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = dirname(__DIR__);
require_once $rootDir . '/includes/db.php';
require_once $rootDir . '/includes/blog-data.php';

try {
    $pdo = get_db();
    echo "<h2>RT Chocos Database Setup</h2>";

    // 1. Create admins table
    echo "Creating 'admins' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // Insert default admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        echo "Creating default admin account... ";
        $hash = password_hash('Admin@rtchocos1', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)");
        $stmt->execute([$hash]);
        echo "OK (Username: admin, Password: Admin@rtchocos1)<br>";
    } else {
        echo "Default admin account already exists.<br>";
    }

    // 2. Create blogs table (correct column names matching admin panel)
    echo "Creating 'blogs' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(100) UNIQUE NOT NULL,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL DEFAULT 'Science',
        excerpt TEXT NULL,
        content LONGTEXT NULL,
        image_path VARCHAR(255) NULL,
        thumbnail_path VARCHAR(255) NULL,
        body_class VARCHAR(100) NULL,
        youtube_url VARCHAR(255) NULL,
        read_time VARCHAR(50) NULL,
        is_published TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // 3. Create subscribers table
    echo "Creating 'subscribers' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(150) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // 4. Create contacts table (with is_read flag for admin inbox)
    echo "Creating 'contacts' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        phone VARCHAR(50) NULL,
        subject VARCHAR(150) NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // Create comments table
    echo "Creating 'comments' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        blog_slug VARCHAR(100) NOT NULL,
        name VARCHAR(100) NOT NULL,
        comment TEXT NOT NULL,
        is_approved TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_blog_slug (blog_slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // 5. Migrate blogs from static array + markdown files
    echo "Migrating blog articles...<br>";
    foreach ($BLOGS as $slug => $meta) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM blogs WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() == 0) {
            $mdFile  = $rootDir . '/blog/posts/' . $slug . '.md';
            $content = '';
            if (file_exists($mdFile)) {
                $content = file_get_contents($mdFile);
            }

            echo "&nbsp;&nbsp;- Migrating '$slug'... ";
            $stmt = $pdo->prepare(
                "INSERT INTO blogs (slug, title, category, excerpt, content, image_path, thumbnail_path, body_class, read_time, is_published, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())"
            );
            $stmt->execute([
                $slug,
                $meta['title'],
                $meta['category'],
                $meta['excerpt'],
                $content,
                $meta['image'],
                $meta['image'],       // thumbnail fallback = header image
                $meta['bodyClass'] ?: null,
                $meta['read']
            ]);
            echo "OK<br>";
        } else {
            echo "&nbsp;&nbsp;- Blog '$slug' already exists in database.<br>";
        }
    }

    // 6. Migrate existing subscribers from CSV
    $csvFile = $rootDir . '/data/subscribers.csv';
    if (file_exists($csvFile)) {
        echo "Migrating subscribers from CSV...<br>";
        $file = fopen($csvFile, 'r');
        if ($file) {
            $header = fgetcsv($file); // skip header row
            while (($row = fgetcsv($file)) !== false) {
                if (isset($row[1]) && !empty(trim($row[1]))) {
                    $email = trim($row[1]);
                    $stmt  = $pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetchColumn() == 0) {
                        $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
                        $stmt->execute([$email]);
                        echo "&nbsp;&nbsp;- Imported: $email<br>";
                    }
                }
            }
            fclose($file);
        }
    }

    // 7. Add columns to blogs table if they don't exist
    $columns = $pdo->query("SHOW COLUMNS FROM blogs")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('scheduled_at', $columns)) {
        echo "Adding 'scheduled_at' column to 'blogs'... ";
        $pdo->exec("ALTER TABLE blogs ADD COLUMN scheduled_at TIMESTAMP NULL DEFAULT NULL;");
        echo "OK<br>";
    }
    if (!in_array('views', $columns)) {
        echo "Adding 'views' column to 'blogs'... ";
        $pdo->exec("ALTER TABLE blogs ADD COLUMN views INT NOT NULL DEFAULT 0;");
        echo "OK<br>";
    }

    // 8. Create tags and mapping tables
    echo "Creating 'blog_tags' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) UNIQUE NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    echo "Creating 'blog_tag_map' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_tag_map (
        blog_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (blog_id, tag_id),
        FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // 9. Create site_settings table
    echo "Creating 'site_settings' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // Populate default settings
    $defaultSettings = [
        'site_name' => 'RT Chocos',
        'site_tagline' => 'Real Taste, Real Chocolate',
        'meta_description' => 'Experience the real taste of premium chocolates crafted with love and science.',
        'contact_email' => 'info@rtchocos.com',
        'contact_phone' => '+1234567890',
        'social_instagram' => 'https://instagram.com/rtchocos',
        'social_facebook' => 'https://facebook.com/rtchocos',
        'social_youtube' => 'https://youtube.com/rtchocos',
        'social_linkedin' => 'https://linkedin.com/company/rtchocos',
        'newsletter_text' => 'Subscribe to our newsletter for exclusive recipes, scientific cocoa insights, and new product releases.'
    ];

    foreach ($defaultSettings as $key => $value) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    }
    echo "Settings initialized<br>";

    // 10. Create media table
    echo "Creating 'media' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS media (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        path VARCHAR(255) NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        size INT NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // 11. Create products table
    echo "Creating 'products' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(150) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        short_description TEXT NULL,
        long_description LONGTEXT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        sale_price DECIMAL(10,2) NULL,
        category VARCHAR(100) NOT NULL DEFAULT 'General',
        stock_quantity INT NOT NULL DEFAULT 0,
        image_main VARCHAR(255) NULL,
        image_gallery JSON NULL,
        meta_title VARCHAR(255) NULL,
        meta_description TEXT NULL,
        meta_keywords VARCHAR(500) NULL,
        is_featured TINYINT(1) NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_active_featured (is_active, is_featured)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // Insert mock products if none exist
    $prodCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($prodCount == 0) {
        echo "Seeding products table... ";
        $mockProducts = [
            [
                'slug' => 'signature-dark-chocolate-72',
                'name' => 'Signature Bean-to-Bar Dark Chocolate Bar (72% Cacao)',
                'short_description' => 'Artisanal dark chocolate bar handcrafted from single-origin Indian cacao beans sourced directly from Kerala.',
                'long_description' => "Indulge in our Signature 72% Dark Chocolate Bar, carefully crafted from single-origin cacao beans grown in the foothills of Kerala. We control the entire process — from sorting, light roasting, stone-grinding for 48 hours, to aging and tempering. Taste notes include red berries, citrus undertones, and a smooth, velvety cocoa finish. 100% vegan, gluten-free, and made with pure cocoa butter.",
                'price' => 295.00,
                'sale_price' => 250.00,
                'category' => 'Chocolates',
                'stock_quantity' => 50,
                'image_main' => 'assets/signature-dark-chocolate.png',
                'image_gallery' => json_encode([]),
                'meta_title' => 'Signature 72% Dark Chocolate Bar | Buy Bean-to-Bar | RT Chocos India',
                'meta_description' => 'Buy our signature 72% dark chocolate bar handcrafted in India. Single-origin Kerala cacao, vegan, gluten-free, with red berry and citrus notes.',
                'meta_keywords' => 'dark chocolate 72, buy dark chocolate india, artisan chocolate bar, single origin chocolate, kerala cacao',
                'is_featured' => 1,
                'is_active' => 1
            ],
            [
                'slug' => 'single-origin-cacao-nibs',
                'name' => 'Indian Single-Origin Cacao Nibs',
                'short_description' => 'Crunchy, antioxidant-rich roasted cacao nibs from direct-trade farms in Karnataka.',
                'long_description' => "Pure, minimally processed roasted cacao nibs sourced from Karnataka, India. These nibs are made by cracking and winnowing high-quality roasted cacao beans. Perfect for baking, sprinkling on smoothies, porridge, or eating raw as a healthy snack. Rich in minerals, antioxidants, and pure chocolate flavor without any added sugar.",
                'price' => 180.00,
                'sale_price' => null,
                'category' => 'Cacao',
                'stock_quantity' => 100,
                'image_main' => 'assets/cacao-nibs.png',
                'image_gallery' => json_encode([]),
                'meta_title' => 'Roasted Single-Origin Cacao Nibs | Buy Online India | RT Chocos',
                'meta_description' => 'Buy premium single-origin roasted cacao nibs from Karnataka. High antioxidant superfood, perfect for baking or healthy snacking.',
                'meta_keywords' => 'cacao nibs india, buy cacao nibs, roasted cocoa nibs, karnataka cacao, chocolate superfood',
                'is_featured' => 0,
                'is_active' => 1
            ],
            [
                'slug' => 'chocolate-makers-starter-kit',
                'name' => "Bean-to-Bar Chocolate Maker's Starter Kit",
                'short_description' => 'Everything a beginner needs to start making bean-to-bar chocolate at home.',
                'long_description' => "Start your chocolate making journey with our Maker's Starter Kit curated by Aarti Saluja Sahni. This kit includes 500g of raw single-origin Indian cacao beans, 100g of pure cocoa butter, 3 food-grade reusable chocolate molds, a digital tempering thermometer, and our step-by-step chocolate science formulation guide. Perfect for home bakers and chocolate enthusiasts.",
                'price' => 1499.00,
                'sale_price' => 1299.00,
                'category' => 'Kits',
                'stock_quantity' => 15,
                'image_main' => 'assets/starter-kit.png',
                'image_gallery' => json_encode([]),
                'meta_title' => 'Home Chocolate Maker Starter Kit | Learn Bean to Bar | RT Chocos',
                'meta_description' => 'Get everything you need to make bean-to-bar chocolate at home. Includes raw cacao beans, cocoa butter, molds, thermometer, and formulation guide.',
                'meta_keywords' => 'chocolate making kit, bean to bar starter kit, make chocolate at home, chocolate making tools, raw cacao beans india',
                'is_featured' => 1,
                'is_active' => 1
            ]
        ];

        $pStmt = $pdo->prepare("INSERT INTO products (slug, name, short_description, long_description, price, sale_price, category, stock_quantity, image_main, image_gallery, meta_title, meta_description, meta_keywords, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($mockProducts as $p) {
            $pStmt->execute([
                $p['slug'], $p['name'], $p['short_description'], $p['long_description'],
                $p['price'], $p['sale_price'], $p['category'], $p['stock_quantity'],
                $p['image_main'], $p['image_gallery'], $p['meta_title'], $p['meta_description'],
                $p['meta_keywords'], $p['is_featured'], $p['is_active']
            ]);
        }
        echo "OK<br>";
    }

    // 12. Create orders table
    echo "Creating 'orders' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        customer_name VARCHAR(150) NOT NULL,
        customer_email VARCHAR(150) NOT NULL,
        customer_phone VARCHAR(50) NULL,
        shipping_address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        pincode VARCHAR(10) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL,
        payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
        payment_id VARCHAR(255) NULL,
        order_status ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // 13. Create order_items table
    echo "Creating 'order_items' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // 14. Create faqs table
    echo "Creating 'faqs' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS faqs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question VARCHAR(500) NOT NULL,
        answer TEXT NOT NULL,
        category ENUM('general','workshops','shop','shipping','courses') NOT NULL DEFAULT 'general',
        display_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category_order (category, display_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // Populate FAQs if empty
    $faqCount = $pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
    if ($faqCount == 0) {
        echo "Seeding faqs table with 15 expert answers... ";
        $seedFaqs = [
            // General FAQs
            [
                'question' => 'What is RT Chocos?',
                'answer' => "RT Chocos is India's first chocolate blogging website and bean-to-bar learning academy. Founded by Aarti Saluja Sahni, we teach the science of cocoa processing, formulation, and tempering while offering professional workshops and premium craft chocolate tools.",
                'category' => 'general',
                'display_order' => 1
            ],
            [
                'question' => 'What does bean-to-bar mean?',
                'answer' => "Bean-to-bar refers to the process where a chocolate maker controls every step of production, starting from raw cacao beans to the finished chocolate bar. This includes sourcing, roasting, cracking, winnowing, grinding, conching, aging, tempering, and molding.",
                'category' => 'general',
                'display_order' => 2
            ],
            [
                'question' => 'Who is Aarti Saluja Sahni?',
                'answer' => "Aarti Saluja Sahni is the founder of RT Chocos, India's first chocolate educator and consulting expert. With over a decade of experience in bean-to-bar chocolate making, recipe formulation, and brand consulting, she has trained more than 2,000 students across India.",
                'category' => 'general',
                'display_order' => 3
            ],
            // Workshops FAQs
            [
                'question' => 'What workshops does RT Chocos offer?',
                'answer' => "We offer professional bean-to-bar workshops, tempering science masterclasses, and chocolate making courses in Mumbai and online. These sessions cover cacao selection, roasting profiles, tempering chemistry, and flavor formulation.",
                'category' => 'workshops',
                'display_order' => 1
            ],
            [
                'question' => 'Do I get a certificate after completing a workshop?',
                'answer' => "Yes, participants receive an official Certificate of Completion from RT Chocos Chocolate Academy. This certificate recognizes your training in professional bean-to-bar craft chocolate making and chocolate science.",
                'category' => 'workshops',
                'display_order' => 2
            ],
            [
                'question' => 'Can I attend workshops online?',
                'answer' => "Yes, we conduct live online interactive workshops via Zoom. We ship a curated ingredient and toolkit box to your address before the session so you can practice bean-to-bar and tempering science hands-on along with Aarti.",
                'category' => 'workshops',
                'display_order' => 3
            ],
            // Shop FAQs
            [
                'question' => 'What products does RT Chocos sell?',
                'answer' => "We sell premium artisan bean-to-bar dark chocolates, roasted single-origin cacao nibs, home chocolate making starter kits, and professional recipe formulation guides designed by chocolate consultant Aarti Saluja Sahni.",
                'category' => 'shop',
                'display_order' => 1
            ],
            [
                'question' => 'Are RT Chocos products vegetarian?',
                'answer' => "Yes, all products in our online shop are 100% vegetarian. Our dark chocolate bars are also vegan, dairy-free, gluten-free, and made using only organic cane sugar and pure single-origin Indian cocoa beans.",
                'category' => 'shop',
                'display_order' => 2
            ],
            [
                'question' => 'Can I return or exchange products?',
                'answer' => "Due to the perishable nature of artisanal chocolate, we do not accept returns or exchanges on food items once shipped. For tools, starter kits, or damaged items, contact hello@rtchocos.com within 7 days of delivery for a replacement.",
                'category' => 'shop',
                'display_order' => 3
            ],
            // Shipping FAQs
            [
                'question' => 'Do you ship across India?',
                'answer' => "Yes, we ship our chocolates, kits, and tools to all major cities and towns across India. We partner with express temperature-controlled courier networks to ensure your chocolate products arrive safely without melting.",
                'category' => 'shipping',
                'display_order' => 1
            ],
            [
                'question' => 'How are chocolates packed for shipping?',
                'answer' => "Every chocolate order is shipped in food-grade insulated boxes containing reusable ice gel packs. This protective packaging keeps the temperature cool throughout transit, maintaining the chocolate's tempered state.",
                'category' => 'shipping',
                'display_order' => 2
            ],
            [
                'question' => 'Is there free shipping?',
                'answer' => "We offer free standard shipping across India on all orders of ₹999 and above. For orders under ₹999, a flat shipping fee of ₹99 is applied at checkout to cover temperature-controlled packaging costs.",
                'category' => 'shipping',
                'display_order' => 3
            ],
            // Courses FAQs
            [
                'question' => 'How is a course different from a workshop?',
                'answer' => "Workshops are hands-on, single-session masterclasses focusing on a specific skill (e.g. tempering). Professional courses are comprehensive, multi-week programs covering everything from raw cacao chemistry to scale business setup.",
                'category' => 'courses',
                'display_order' => 1
            ],
            [
                'question' => 'What are the prerequisites for joining a course?',
                'answer' => "There are no prerequisites! Our bean-to-bar chocolate making courses are designed to take you from a complete beginner to a confident craft chocolate maker. No culinary background is required to enroll.",
                'category' => 'courses',
                'display_order' => 2
            ],
            [
                'question' => 'How much do courses cost?',
                'answer' => "Course fees vary depending on the depth, duration, and format (online vs. in-person). Standard online workshops start from ₹4,999, while professional certification programs range between ₹14,999 and ₹29,999.",
                'category' => 'courses',
                'display_order' => 3
            ]
        ];

        $faqStmt = $pdo->prepare("INSERT INTO faqs (question, answer, category, display_order, is_active) VALUES (?, ?, ?, ?, 1)");
        foreach ($seedFaqs as $f) {
            $faqStmt->execute([$f['question'], $f['answer'], $f['category'], $f['display_order']]);
        }
        echo "OK<br>";
    }

    echo "<h3>Setup Completed Successfully!</h3>";

} catch (Exception $e) {
    echo "<h3 style='color:red;'>Error: " . $e->getMessage() . "</h3>";
}
?>
