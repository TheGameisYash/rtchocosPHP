<?php
// db_setup.php - Database Initialization and Migration Script
// IMPORTANT: Delete this file before deploying to production!

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/blog-data.php';

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
            $mdFile  = __DIR__ . '/blog/posts/' . $slug . '.md';
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
    $csvFile = __DIR__ . '/data/subscribers.csv';
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

    // 11. Create ai_insights table for server-side cache
    echo "Creating 'ai_insights' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_insights (
        id INT AUTO_INCREMENT PRIMARY KEY,
        insight_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // Seed default insights if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM ai_insights");
    if ($stmt->fetchColumn() == 0) {
        echo "Seeding default cacao insights... ";
        $defaults = [
            "Cacao beans contain over 600 flavor compounds, making them chemically more complex than red wine.",
            "The ideal temperature for dark chocolate tempering is between 88°F and 90°F (31°C - 32°C).",
            "Criollo cacao is highly prized for its delicate, aromatic flavor profile and low bitterness.",
            "Water is chocolate's biggest enemy; even a single drop can cause a batch to seize.",
            "Roasting cacao beans sterilizes them, reduces moisture, and develops crucial chocolate aroma precursors."
        ];
        $insStmt = $pdo->prepare("INSERT INTO ai_insights (insight_text) VALUES (?)");
        foreach ($defaults as $d) {
            $insStmt->execute([$d]);
        }
        echo "OK<br>";
    }

    // 12. Create ai_class_facts table for workshops facts
    echo "Creating 'ai_class_facts' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_class_facts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fact_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK<br>";

    // Seed default class facts if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM ai_class_facts");
    if ($stmt->fetchColumn() == 0) {
        echo "Seeding default chocolate workshop facts... ";
        $classDefaults = [
            "Tempering cocoa butter requires precisely forming Type V crystals for that satisfying snap and glossy finish.",
            "Under-roasting cacao beans leads to high acidity and lack of deep chocolate flavor notes in the final bar.",
            "The conching process reduces volatile acids (like acetic acid) and coats solid particles with cocoa butter for a smooth mouthfeel.",
            "Adding lecithin (an emulsifier) reduces chocolate viscosity significantly, making it easier to mould or coat.",
            "Roasting temperature profiles are varied: high-temperature short-time (HTST) for floral profiles, low-temperature long-time (LTLT) for earthy profiles."
        ];
        $insStmt = $pdo->prepare("INSERT INTO ai_class_facts (fact_text) VALUES (?)");
        foreach ($classDefaults as $fd) {
            $insStmt->execute([$fd]);
        }
        echo "OK<br>";
    }

    echo "<h3>Setup Completed Successfully!</h3>";
    echo "<p style='color:red;'><b>IMPORTANT: Delete db_setup.php before deploying to production!</b></p>";

} catch (Exception $e) {
    echo "<h3 style='color:red;'>Error: " . $e->getMessage() . "</h3>";
}
?>
