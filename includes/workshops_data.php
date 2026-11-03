<?php
// includes/workshops_data.php - Shared static workshops data and renderer for RT Chocos Academy

$workshops = [
    [
        'id' => 1,
        'title' => "Artisan Chocolate Truffles & Ganache",
        'level' => "Beginner to Intermediate",
        'category' => "Practical",
        'duration' => "1 Day Intensive",
        'format' => "Studio & Online",
        'price' => "Admissions Open Soon",
        'status' => "coming-soon",
        'image' => "assets/truffle_workshop.png",
        'takehome' => "Handcrafted Truffle Box & Formulations",
        'outcomes' => [
            "Hand-rolling, enrobing & coating classic truffles",
            "Infusing ganache with botanicals, single-estate tea & spices",
            "Controlling fat-to-water emulsion stability in fillings",
            "Artisan piping, velvet spraying & gold leaf finishing"
        ]
    ],
    [
        'id' => 2,
        'title' => "The Science of Tempering & Crystal Kinetics",
        'level' => "Intermediate to Advanced",
        'category' => "Science",
        'duration' => "1 Day Masterclass",
        'format' => "Studio & Online",
        'price' => "Admissions Open Soon",
        'status' => "coming-soon",
        'image' => "assets/tempering_workshop.png",
        'takehome' => "Tempering Log Book & Crystal Guide",
        'outcomes' => [
            "Cocoa butter crystal polymorphism & Form-V lattice science",
            "Precise thermal curves: table tabling, seeding & induction",
            "Water activity (aw) thermodynamics & shelf-life safety",
            "Bloom diagnostics: fat bloom, sugar bloom & crystal defects"
        ]
    ],
    [
        'id' => 3,
        'title' => "Mastering Artisan Bonbons & Cocoa Painting",
        'level' => "Advanced Masterclass",
        'category' => "Artisan",
        'duration' => "3 Days Professional",
        'format' => "Mumbai Studio Exclusive",
        'price' => "Admissions Open Soon",
        'status' => "coming-soon",
        'image' => "assets/bonbon_workshop.png",
        'takehome' => "30+ Mirror Bonbons & Airbrush Master Guide",
        'outcomes' => [
            "Coloured cocoa butter crystallization & mirror polishing",
            "Mould casting, wafer-thin shells & acoustic snap testing",
            "Multi-layered architectures: fluid caramels, gelées & duos",
            "Commercial production efficiency & moisture barrier protection"
        ]
    ]
];

function renderWorkshopCard($w, $pathPrefix = "") {
    $title = htmlspecialchars($w['title'], ENT_QUOTES, 'UTF-8');
    $level = htmlspecialchars($w['level'], ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($w['category'], ENT_QUOTES, 'UTF-8');
    $duration = htmlspecialchars($w['duration'], ENT_QUOTES, 'UTF-8');
    $format = htmlspecialchars($w['format'] ?? 'Studio & Online', ENT_QUOTES, 'UTF-8');
    $price = htmlspecialchars($w['price'], ENT_QUOTES, 'UTF-8');
    $takehome = htmlspecialchars($w['takehome'] ?? 'Certificate & Compendium', ENT_QUOTES, 'UTF-8');

    $waText = urlencode("Hello Aarti & RT Chocos! I would like to inquire about the workshop: " . $w['title'] . " (" . $w['duration'] . ").");
    $waUrl = "https://wa.me/919140238741?text={$waText}";

    $imageSrc = $pathPrefix . $w['image'];
    $imageTag = ($w['image'] && strpos($w['image'], '/') !== false)
        ? "<img src=\"{$imageSrc}\" alt=\"{$title}\" width=\"600\" height=\"375\" loading=\"lazy\">"
        : "<div class=\"workshop-placeholder\"><span>🍫</span></div>";

    $outcomesHtml = "";
    foreach ($w['outcomes'] as $o) {
        $outcomesHtml .= "<li>" . htmlspecialchars($o, ENT_QUOTES, 'UTF-8') . "</li>";
    }

    return "
    <article class=\"card workshop-card\" data-category=\"{$category}\">
      <div class=\"workshop-card-img\">
        {$imageTag}
        <span class=\"workshop-card-tag\">{$level}</span>
      </div>
      <div class=\"workshop-card-body\">
        <div class=\"workshop-meta-bar\">
          <span class=\"workshop-meta-item duration\">
            <svg width=\"14\" height=\"14\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><polyline points=\"12 6 12 12 16 14\"></polyline></svg>
            {$duration} &bull; {$format}
          </span>
          <span class=\"workshop-price-badge\"><span class=\"pulse-dot\" aria-hidden=\"true\"></span>{$price}</span>
        </div>
        <h3 class=\"workshop-card-title\">{$title}</h3>
        <div class=\"workshop-takehome-highlight\">
          <svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><path d=\"M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4\"></path><path d=\"M4 6v12c0 1.1.9 2 2 2h14v-4\"></path><path d=\"M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z\"></path></svg>
          <span>{$takehome}</span>
        </div>
        <div class=\"workshop-curriculum-heading\">Key Curriculum Focus:</div>
        <ul class=\"workshop-outcomes\">
          {$outcomesHtml}
        </ul>
        <div class=\"workshop-card-actions\">
          <button type=\"button\" class=\"btn-workshop-primary\" onclick=\"triggerNewsletterAlert()\">
            <span>Pre-Register / Notify Me</span> &rarr;
          </button>
          <a href=\"{$waUrl}\" target=\"_blank\" rel=\"noopener\" class=\"btn-workshop-whatsapp\">
            <svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><path d=\"M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z\"></path></svg>
            Inquire via WhatsApp
          </a>
        </div>
      </div>
    </article>";
}
?>