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

    echo "<h3>Setup Completed Successfully!</h3>";
    echo "<p style='color:red;'><b>IMPORTANT: Delete db_setup.php before deploying to production!</b></p>";

} catch (Exception $e) {
    echo "<h3 style='color:red;'>Error: " . $e->getMessage() . "</h3>";
}
?>
