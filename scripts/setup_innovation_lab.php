<?php
// scripts/setup_innovation_lab.php
// Creates Innovation Lab tables and populates initial data

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = get_db();
    echo "Connecting to database...\n";

    // 1. lab_experiments table
    echo "Creating 'lab_experiments' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS lab_experiments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        category VARCHAR(100) NOT NULL DEFAULT 'formulation-lab',
        status_badge VARCHAR(100) NOT NULL DEFAULT 'IN PROGRESS',
        status_color VARCHAR(50) NOT NULL DEFAULT 'green',
        image_path VARCHAR(255) NULL,
        description TEXT NOT NULL,
        tags VARCHAR(255) NULL,
        hypothesis TEXT NULL,
        methodology TEXT NULL,
        findings TEXT NULL,
        next_steps TEXT NULL,
        full_content LONGTEXT NULL,
        is_featured TINYINT(1) NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_lab_cat (category),
        INDEX idx_lab_active (is_active, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. lab_failed_batches table
    echo "Creating 'lab_failed_batches' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS lab_failed_batches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        badge_label VARCHAR(100) NOT NULL DEFAULT 'BECAUSE FAILURE IS DATA.',
        image_path VARCHAR(255) NULL,
        hypothesis TEXT NOT NULL,
        what_happened TEXT NOT NULL,
        why_it_happened TEXT NOT NULL,
        what_next TEXT NOT NULL,
        full_story LONGTEXT NULL,
        is_featured TINYINT(1) NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_fail_active (is_active, is_featured)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. lab_what_if_questions table
    echo "Creating 'lab_what_if_questions' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS lab_what_if_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        num_label VARCHAR(20) NOT NULL,
        question TEXT NOT NULL,
        subtext TEXT NULL,
        link_url VARCHAR(255) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_whatif_active (is_active, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 4. lab_ingredient_categories table
    echo "Creating 'lab_ingredient_categories' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS lab_ingredient_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        slug VARCHAR(150) UNIQUE NOT NULL,
        image_path VARCHAR(255) NULL,
        description TEXT NULL,
        link_url VARCHAR(255) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ing_active (is_active, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 5. lab_future_pillars table
    echo "Creating 'lab_future_pillars' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS lab_future_pillars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        description TEXT NULL,
        link_url VARCHAR(255) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pillar_active (is_active, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Tables created successfully!\n";

    // Seed default settings into site_settings
    echo "Seeding site_settings...\n";
    $labSettings = [
        'lab_hero_eyebrow' => 'IDEAS × EXPERIMENTS × EVIDENCE',
        'lab_hero_title' => 'Innovation Lab',
        'lab_hero_subtitle' => 'A working space where curious minds come together to explore new ingredients, techniques and ideas to shape a more thoughtful chocolate future.',
        'lab_hero_cta_text' => 'STEP INTO THE LAB →',
        'lab_hero_cta_link' => '#currently-in-lab',
        'lab_sticky_note' => "Better\nIngredients\nBigger Questions\nBrighter Chocolate",
        'lab_hero_image' => 'assets/images/lab-hero.jpg',
        'lab_failed_batch_eyebrow' => 'BECAUSE FAILURE IS DATA.',
        'lab_failed_batch_heading' => 'From a Failed Batch to a New Insight',
        'lab_what_if_eyebrow' => 'IDEAS WE ARE EXPLORING NEXT.',
        'lab_what_if_heading' => 'What if?',
        'lab_what_if_image' => 'assets/images/lab-what-if.jpg',
        'lab_ingredient_eyebrow' => 'NEW INGREDIENTS. NEW BEHAVIOURS. NEW POSSIBILITIES.',
        'lab_ingredient_heading' => 'Ingredient Innovation',
        'lab_future_eyebrow' => 'BOLDER QUESTIONS. BIGGER IMPACT.',
        'lab_future_heading' => 'Future of Chocolate',
        'lab_future_text' => 'Exploring ideas and technologies that can make chocolate more inclusive, resilient and regenerative — for people, communities and the planet.',
        'lab_future_cta_text' => 'EXPLORE FUTURE IDEAS →',
        'lab_future_cta_link' => '#future-ideas',
        'lab_future_image' => 'assets/images/lab-future.jpg',
    ];

    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
    $insertStmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($labSettings as $k => $v) {
        $checkStmt->execute([$k]);
        if ($checkStmt->fetchColumn() == 0) {
            $insertStmt->execute([$k, $v]);
        }
    }

    // Seed lab_experiments if empty
    $expCount = $pdo->query("SELECT COUNT(*) FROM lab_experiments")->fetchColumn();
    if ($expCount == 0) {
        echo "Seeding default lab experiments...\n";
        $seedExperiments = [
            [
                'title' => 'CBE × Bean-to-Bar',
                'slug' => 'cbe-bean-to-bar',
                'category' => 'formulation-lab',
                'status_badge' => 'IN PROGRESS',
                'status_color' => 'green',
                'image_path' => 'assets/images/lab-cbe.jpg',
                'description' => 'Can cocoa butter equivalents deliver great texture, snap and flavour in high-cocoa chocolate?',
                'tags' => 'FAT SYSTEMS | FORMULATION',
                'hypothesis' => 'Non-lauric cocoa butter equivalents (CBE) blended at 5% will maintain Beta-V crystal stability while boosting heat tolerance in tropical ambient temperatures without compromising snap.',
                'methodology' => 'Formulated 70% single-origin Kerala cacao with 5% CBE fat replacement. Monitored temper curves on Selmi tempering unit, differential scanning calorimetry (DSC), and snap test after 30 days.',
                'findings' => 'Temper curves showed sharp Beta-V peaks. Melting point increased by +1.8°C with zero noticeable waxiness on the palate. Flavour profile remained authentic.',
                'next_steps' => 'Conduct 60-day fat bloom cycling tests at 28°C-32°C.',
                'full_content' => "<h3>Executive Summary</h3>\n<p>In tropical climates like India, managing fat bloom and ambient melting point has traditionally pushed mass producers toward hydrogenated fats, severely degrading flavor. Our goal is to explore clean-label, non-hydrogenated plant-derived Cocoa Butter Equivalents (CBEs) that integrate directly into the polymorphic crystal grid of pure cocoa butter.</p>\n<h3>Key Observations</h3>\n<ul>\n<li><strong>Crystallization Speed:</strong> Melanger conching time was maintained at 36 hours. Cooling curves show crisp crystal formation at 28.5°C.</li>\n<li><strong>Snap & Texture:</strong> Blind sensory panels rated the snap 9.2/10 compared to 9.4/10 on the pure control bar.</li>\n<li><strong>Thermal Stability:</strong> Resisted slump tests up to 33.5°C before softening.</li>\n</ul>",
                'is_featured' => 1,
                'sort_order' => 1
            ],
            [
                'title' => 'Freeze-Dried Indian Fruits',
                'slug' => 'freeze-dried-indian-fruits',
                'category' => 'ingredient-innovation',
                'status_badge' => 'SENSORY TRIALS',
                'status_color' => 'amber',
                'image_path' => 'assets/images/lab-fruits.jpg',
                'description' => 'Can Indian fruits become a premium inclusion? Studying flavour retention, texture and moisture behaviour.',
                'tags' => 'INCLUSIONS | SHELF LIFE',
                'hypothesis' => 'Freeze-dried Indian heritage fruits (Alphonso mango, pink guava, sun-ripened jamun) retain tart volatile esters better when fat-coated before stone melanging.',
                'methodology' => 'Pre-treated freeze-dried fruit crunchies with micro-thin cocoa butter barrier spray to inhibit moisture migration from ambient humidity.',
                'findings' => 'Crunch retention improved by 340% over 6 weeks. Moisture activity (Aw) remained below 0.35, preventing chocolate sugar-bloom.',
                'next_steps' => 'Sensory panel testing across 30 chocolate enthusiasts for acidity-fat equilibrium.',
                'full_content' => "<h3>Background</h3>\n<p>India grows some of the world's most aromatic fruits, yet most chocolate bars rely on imported freeze-dried strawberries and raspberries. We set out to investigate indigenous cultivars: Ratnagiri Alphonso mango, Allahabad pink guava, and Konkan jamun.</p>\n<h3>Challenges & Solutions</h3>\n<p>The primary enemy of freeze-dried fruit inclusions is moisture hygroscopy: fruit absorbs moisture from the atmosphere and turns chewy or rubbery, releasing free water that causes sugar bloom in chocolate. Applying a 5-micron cocoa butter airbrush coat created an impenetrable barrier while preserving sharp fruit acidity.</p>",
                'is_featured' => 1,
                'sort_order' => 2
            ],
            [
                'title' => 'Khandsari Sugar in Dark Chocolate',
                'slug' => 'khandsari-sugar-in-dark-chocolate',
                'category' => 'formulation-lab',
                'status_badge' => 'FORMULATION',
                'status_color' => 'green',
                'image_path' => 'assets/images/lab-sugar.jpg',
                'description' => 'What changes when traditional Indian sugar meets modern chocolate processing?',
                'tags' => 'SUGAR SYSTEMS | TEXTURE',
                'hypothesis' => 'Unrefined indigenous Khandsari cane sugar will add warm molasses and caramel complexity to high-percentage dark chocolate without increasing grit.',
                'methodology' => 'Stone ground Khandsari crystals to particle size < 20 microns over 48 hours in Santha granite melangers. Calibrated conching shear to evaporate moisture.',
                'findings' => 'Viscosity remained fluid at 45°C. The natural mineral profile in Khandsari softened the intense tannin bite of South Indian cacao.',
                'next_steps' => 'Standardize drying moisture parameters for seasonal monsoon batches.',
                'full_content' => "<h3>Heritage Meets Food Science</h3>\n<p>Khandsari is an artisanal, liquid-clarified, unbleached raw cane sugar prepared traditionally without synthetic sulphur. Unlike modern refined white sugar, it carries trace minerals (potassium, iron, magnesium) and subtle malt notes.</p>\n<h3>Particle Micronization</h3>\n<p>Due to residual moisture (0.8% vs 0.04% in refined sugar), conching temperatures were raised incrementally to 58°C with forced dry-air ventilation to vent moisture without seizing the cocoa butter matrix.</p>",
                'is_featured' => 1,
                'sort_order' => 3
            ],
            [
                'title' => 'Coconut-Cashew Spread',
                'slug' => 'coconut-cashew-spread',
                'category' => 'stability-shelf-life',
                'status_badge' => 'STABILITY STUDY',
                'status_color' => 'amber',
                'image_path' => 'assets/images/lab-spread.jpg',
                'description' => 'Can we create a clean label, long shelf-life spread without preservatives?',
                'tags' => 'EMULSION | SHELF LIFE',
                'hypothesis' => 'Slow-milled cold-pressed roasted cashew and dehydrated coconut butter will create a shelf-stable emulsion without hydrogenated oils or soy lecithin.',
                'methodology' => 'Conched at 50°C for 24 hours to micro-emulsify cashew natural oils with coconut fat. Packaged under inert nitrogen seal.',
                'findings' => 'Oil separation index was < 2% over 90 days. Spreadability maintained at room temperature (22°C - 30°C) with rich mouthfeel.',
                'next_steps' => 'Shelf-life acceleration study under 38°C humidity chamber.',
                'full_content' => "<h3>Clean Label Formulation</h3>\n<p>Commercial chocolate nut spreads rely on palm oil fractions and chemical stabilizers to stop oil separation. By balancing the fatty acid chain lengths of roasted cashew lipids (primarily oleic acid) with lauric and myristic fats from desiccated coconut, we achieved natural polymorphic lattice immobilization.</p>",
                'is_featured' => 1,
                'sort_order' => 4
            ]
        ];

        $insExp = $pdo->prepare("INSERT INTO lab_experiments (title, slug, category, status_badge, status_color, image_path, description, tags, hypothesis, methodology, findings, next_steps, full_content, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($seedExperiments as $e) {
            $insExp->execute([
                $e['title'], $e['slug'], $e['category'], $e['status_badge'], $e['status_color'],
                $e['image_path'], $e['description'], $e['tags'], $e['hypothesis'], $e['methodology'],
                $e['findings'], $e['next_steps'], $e['full_content'], $e['is_featured'], $e['sort_order']
            ]);
        }
    }

    // Seed lab_failed_batches if empty
    $failCount = $pdo->query("SELECT COUNT(*) FROM lab_failed_batches")->fetchColumn();
    if ($failCount == 0) {
        echo "Seeding default failed batch...\n";
        $insFail = $pdo->prepare("INSERT INTO lab_failed_batches (title, slug, badge_label, image_path, hypothesis, what_happened, why_it_happened, what_next, full_story, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insFail->execute([
            'THE GUAVA EXPERIMENT',
            'the-guava-experiment',
            'BECAUSE FAILURE IS DATA.',
            'assets/images/lab-failed-batch.jpg',
            'Freeze-dried guava will create an intense natural fruit note.',
            'Flavour disappeared after incorporation.',
            'Possible volatile loss and fat interaction.',
            'Test cocoa butter pre-coating.',
            "<p>During our seasonal fruit inclusion series, we formulated a 65% dark chocolate bar infused with concentrated freeze-dried pink guava chunks. On paper, the high acidity and tropical aroma should have cut right through the dark chocolate astringency. However, upon 48-hour stone melanging, the guava fruit flavor completely vanished on the palate, leaving only a faint sour aftertaste.</p>\n<p>After chromatography and moisture checks, we identified that delicate fruit terpene volatiles migrated into the cacao fat matrix, getting encapsulated and suppressed. Furthermore, residual hygroscopic fruit sugars pulled moisture from the air during unsealing.</p>\n<p><strong>Lesson Learned:</strong> Hydrophilic fruit compounds must be shielded with an ultra-thin barrier of tempered cocoa butter before introducing them to the stone refiner. Failure transformed our understanding of inclusion kinetics!",
            1,
            1
        ]);
    }

    // Seed lab_what_if_questions if empty
    $whatIfCount = $pdo->query("SELECT COUNT(*) FROM lab_what_if_questions")->fetchColumn();
    if ($whatIfCount == 0) {
        echo "Seeding default what-if questions...\n";
        $seedQuestions = [
            ['num_label' => '01', 'question' => 'Can we reduce sugar without losing melt?', 'link_url' => '#experiments', 'sort_order' => 1],
            ['num_label' => '02', 'question' => 'Can Indian spices create a new flavour language?', 'link_url' => '#experiments', 'sort_order' => 2],
            ['num_label' => '03', 'question' => 'Can chocolate be more regenerative?', 'link_url' => '#experiments', 'sort_order' => 3],
            ['num_label' => '04', 'question' => 'Can fruit co-products find a place in chocolate?', 'link_url' => '#experiments', 'sort_order' => 4],
            ['num_label' => '05', 'question' => 'Can we design a climate-resilient Indian chocolate future?', 'link_url' => '#experiments', 'sort_order' => 5],
        ];
        $insQ = $pdo->prepare("INSERT INTO lab_what_if_questions (num_label, question, link_url, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($seedQuestions as $q) {
            $insQ->execute([$q['num_label'], $q['question'], $q['link_url'], $q['sort_order']]);
        }
    }

    // Seed lab_ingredient_categories if empty
    $ingCount = $pdo->query("SELECT COUNT(*) FROM lab_ingredient_categories")->fetchColumn();
    if ($ingCount == 0) {
        echo "Seeding default ingredient categories...\n";
        $seedIngs = [
            ['title' => 'INDIAN FRUITS', 'slug' => 'indian-fruits', 'image_path' => 'assets/images/lab-ing-fruits.jpg', 'description' => 'Exploring Alphonso mango, sun-dried Kokum, Amla, and pink guava.', 'link_url' => '#category-fruits', 'sort_order' => 1],
            ['title' => 'NUTS & SEEDS', 'slug' => 'nuts-seeds', 'image_path' => 'assets/images/lab-ing-nuts.jpg', 'description' => 'Roasted Malabar cashews, Mamra almonds, melon seeds and charoli.', 'link_url' => '#category-nuts', 'sort_order' => 2],
            ['title' => 'ALTERNATIVE SUGARS', 'slug' => 'alternative-sugars', 'image_path' => 'assets/images/lab-ing-sugars.jpg', 'description' => 'Khandsari, Palmyra palm jaggery, coconut blossom nectar, and wild honey.', 'link_url' => '#category-sugars', 'sort_order' => 3],
            ['title' => 'DAIRY & PLANT-BASED', 'slug' => 'dairy-plant-based', 'image_path' => 'assets/images/lab-ing-plant.jpg', 'description' => 'A2 Vedic milk solids, coconut cream emulsions, and oat milk powders.', 'link_url' => '#category-dairy', 'sort_order' => 4],
            ['title' => 'BOTANICALS & SPICES', 'slug' => 'botanicals-spices', 'image_path' => 'assets/images/lab-ing-spices.jpg', 'description' => 'Wayanad black pepper, Idukki green cardamom, star anise, and mace.', 'link_url' => '#category-spices', 'sort_order' => 5],
        ];
        $insIng = $pdo->prepare("INSERT INTO lab_ingredient_categories (title, slug, image_path, description, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($seedIngs as $i) {
            $insIng->execute([$i['title'], $i['slug'], $i['image_path'], $i['description'], $i['link_url'], $i['sort_order']]);
        }
    }

    // Seed lab_future_pillars if empty
    $pillarCount = $pdo->query("SELECT COUNT(*) FROM lab_future_pillars")->fetchColumn();
    if ($pillarCount == 0) {
        echo "Seeding default future pillars...\n";
        $seedPillars = [
            ['title' => 'REGENERATIVE CACAO', 'description' => 'Agroforestry farming that enriches biodiversity in the Western Ghats.', 'link_url' => '#future-ideas', 'sort_order' => 1],
            ['title' => 'CIRCULAR INGREDIENTS', 'description' => 'Upcycling cocoa pulp juice and bean husks into prebiotic syrups and infusions.', 'link_url' => '#future-ideas', 'sort_order' => 2],
            ['title' => 'CLIMATE RESILIENCE', 'description' => 'Heat-tolerant varietal breeding and sustainable shade canopy practices.', 'link_url' => '#future-ideas', 'sort_order' => 3],
            ['title' => 'ALTERNATIVE RAW MATERIALS', 'description' => 'Cultivating indigenous fat systems and natural sugar alternatives.', 'link_url' => '#future-ideas', 'sort_order' => 4],
            ['title' => 'MORE INCLUSIVE CHOCOLATE', 'description' => 'Direct farmer compensation models and community-driven chocolate craft.', 'link_url' => '#future-ideas', 'sort_order' => 5],
        ];
        $insPillar = $pdo->prepare("INSERT INTO lab_future_pillars (title, description, link_url, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($seedPillars as $p) {
            $insPillar->execute([$p['title'], $p['description'], $p['link_url'], $p['sort_order']]);
        }
    }

    echo "Innovation Lab setup completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
