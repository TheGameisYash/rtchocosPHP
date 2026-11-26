<?php
// includes/workshops_data.php - Shared static workshops data and renderer

$workshops = [
    [
        'id' => 1,
        'title' => "Artisan Chocolate Truffles & Ganache",
        'level' => "Beginner to Intermediate",
        'category' => "Practical",
        'duration' => "1 Day",
        'price' => "Coming Soon",
        'status' => "coming-soon",
        'image' => "assets/truffle_workshop.png",
        'outcomes' => [
            "Hand-rolling & coating classic truffles",
            "Infusing ganache with spices, herbs & citrus",
            "Controlling fat & water emulsion in fillings",
            "Piping & decoration for artisan finish"
        ]
    ],
    [
        'id' => 2,
        'title' => "The Science of Tempering & Cocoa Crystallization",
        'level' => "Intermediate to Advanced",
        'category' => "Science",
        'duration' => "1 Day (Masterclass)",
        'price' => "Coming Soon",
        'status' => "coming-soon",
        'image' => "assets/tempering_workshop.png",
        'outcomes' => [
            "Cocoa butter crystal polymorphism (Forms I-VI)",
            "Reading & controlling seed tempering curves",
            "Water activity (aw) & shelf-life thermodynamics",
            "Emulsification ratio math for silky ganache"
        ]
    ],
    [
        'id' => 3,
        'title' => "Mastering Artisan Bonbons & Cocoa Painting",
        'level' => "Advanced Masterclass",
        'category' => "Artisan",
        'duration' => "3 Days",
        'price' => "Coming Soon",
        'status' => "coming-soon",
        'image' => "assets/bonbon_workshop.png",
        'outcomes' => [
            "Colored cocoa butter & airbrushing basics",
            "Moulding & achieving flawless glossy shells",
            "Layered fillings: caramels, gelées & duos",
            "Troubleshooting cracks, dullness & defects"
        ]
    ]
];

function renderWorkshopCard($w, $pathPrefix = "") {
    $buttonLabel = $w['status'] === 'coming-soon' ? 'Notify Me When Open' : 'Book Now';
    $safeTitle = htmlspecialchars($w['title'], ENT_QUOTES, 'UTF-8');
    $safeDuration = htmlspecialchars($w['duration'], ENT_QUOTES, 'UTF-8');
    $safeLevel = htmlspecialchars($w['level'], ENT_QUOTES, 'UTF-8');

    $buttonOnClick = $w['status'] === 'coming-soon'
        ? "data-workshop-title=\"{$safeTitle}\" data-workshop-duration=\"{$safeDuration}\" data-workshop-level=\"{$safeLevel}\" onclick=\"openWorkshopNotifyModal(this.dataset.workshopTitle, this.dataset.workshopDuration, this.dataset.workshopLevel)\""
        : 'onclick="addToCart()"';

    $imageFile = __DIR__ . '/../' . $w['image'];
    $cacheBust = file_exists($imageFile) ? '?v=' . filemtime($imageFile) : '';
    $imageSrc = $pathPrefix . $w['image'] . $cacheBust;
    $imageTag = ($w['image'] && strpos($w['image'], '/') !== false)
        ? "<img src=\"{$imageSrc}\" alt=\"{$safeTitle}\" loading=\"lazy\">"
        : "<span>🍫</span>";

    $outcomesHtml = "";
    foreach ($w['outcomes'] as $o) {
        $outcomesHtml .= "<li>" . htmlspecialchars($o) . "</li>";
    }

    return "
    <div class=\"card workshop-card\" data-category=\"{$w['category']}\">
      <div class=\"workshop-card-img\">
        {$imageTag}
        <span class=\"tag workshop-card-tag\">{$w['level']}</span>
      </div>
      <div class=\"workshop-card-body\">
        <h3>" . htmlspecialchars($w['title']) . "</h3>
        <div class=\"workshop-meta\">
          <span>🕒 {$w['duration']}</span>
          <span class=\"price\">{$w['price']}</span>
        </div>
        <ul class=\"workshop-outcomes\">
          {$outcomesHtml}
        </ul>
        <button class=\"btn-primary\" {$buttonOnClick} style=\"width:100%;justify-content:center;margin-top:auto;\">{$buttonLabel}</button>
      </div>
    </div>";
}
?>
