<?php
// includes/ingredient_spotlight.php - Curated Content Library & AI Ingredient Spotlight Service
require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/db.php';

/**
 * Return the authoritative Curated Content Library of chocolate ingredients.
 * Formatted strictly according to the RT CHOCOS Ingredient Spotlight guidelines:
 * - Beginner-friendly, technically grounded explanations (25–35 words)
 * - Triplet structure: [what it is] • [what it does] • [where it matters]
 * - Categorized by Content Hierarchy (Levels 1, 2, and 3)
 *
 * @return array List of curated ingredient cards
 */
function get_ingredient_spotlight_library(): array {
    static $library = null;
    if ($library !== null) {
        return $library;
    }

    $library = [
        // ==========================================
        // LEVEL 1 — CHOCOLATE FUNDAMENTALS
        // ==========================================
        [
            'id' => 'cocoa-butter',
            'ingredient_name' => 'Cocoa Butter',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'The natural fat extracted from cocoa beans. It gives chocolate its smooth texture, clean melt and characteristic snap.',
            'what_it_is' => 'natural fat',
            'what_it_does' => 'texture',
            'where_it_matters' => 'tempering',
            'triplet' => 'natural fat • texture • tempering',
            'flavor_notes' => 'natural fat, texture, tempering',
            'flavor_notes_list' => ['natural fat', 'texture', 'tempering'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Cocoa butter is polymorphic and melts sharply at 34–36°C (human body temperature). When tempered into stable Form V (Beta-V) crystals, it contracts cleanly from moulds and delivers a silky, melt-in-the-mouth sensation without greasiness.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'cocoa-mass',
            'ingredient_name' => 'Cocoa Mass',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'Made by grinding roasted cocoa nibs into a thick paste. It contains both cocoa solids and cocoa butter — the foundation of real chocolate.',
            'what_it_is' => 'cacao',
            'what_it_does' => 'cocoa solids',
            'where_it_matters' => 'foundation',
            'triplet' => 'cacao • cocoa solids • foundation',
            'flavor_notes' => 'cacao, cocoa solids, foundation',
            'flavor_notes_list' => ['cacao', 'cocoa solids', 'foundation'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Also called cocoa liquor, cocoa mass consists of approximately 52–56% natural cocoa butter and 44–48% non-fat cocoa solids. It forms the primary building block for all genuine dark and milk chocolate.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'cocoa-nibs',
            'ingredient_name' => 'Cocoa Nibs',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'Small pieces of roasted cocoa beans. They bring concentrated cocoa flavour, bitterness and crunch without adding sugar.',
            'what_it_is' => 'roasted',
            'what_it_does' => 'crunch',
            'where_it_matters' => 'cocoa',
            'triplet' => 'roasted • crunch • cocoa',
            'flavor_notes' => 'roasted, crunch, cocoa',
            'flavor_notes_list' => ['roasted', 'crunch', 'cocoa'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Produced by cracking roasted cocoa beans and winnowing away the outer husk. Nibs are the purest unadulterated edible form of cacao, loaded with polyphenols, theobromine, and natural crunch.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'cocoa-powder',
            'ingredient_name' => 'Cocoa Powder',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'Made by pressing much of the cocoa butter out of cocoa mass and grinding the remaining cocoa solids. It adds deep cocoa flavour without much fat.',
            'what_it_is' => 'cocoa solids',
            'what_it_does' => 'flavour',
            'where_it_matters' => 'baking',
            'triplet' => 'cocoa solids • flavour • baking',
            'flavor_notes' => 'cocoa solids, flavour, baking',
            'flavor_notes_list' => ['cocoa solids', 'flavour', 'baking'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Hydraulic pressing extracts most of the fat, leaving a concentrated cocoa cake containing 10–24% fat. Grinding this cake yields cocoa powder, ideal for baking and dustings where excess melted fat would compromise recipe structure.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'sugar',
            'ingredient_name' => 'Sugar',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'In chocolate, sugar does more than add sweetness. Its amount strongly affects how we perceive cocoa bitterness, acidity and overall flavour.',
            'what_it_is' => 'sweetness',
            'what_it_does' => 'balance',
            'where_it_matters' => 'flavour',
            'triplet' => 'sweetness • balance • flavour',
            'flavor_notes' => 'sweetness, balance, flavour',
            'flavor_notes_list' => ['sweetness', 'balance', 'flavour'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Finely pulverized sucrose acts as a structural bulking agent. During refining and conching, sugar crystals must be milled below 20 microns so the human tongue perceives complete silkiness rather than grittiness.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'lecithin',
            'ingredient_name' => 'Lecithin',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'A small amount of lecithin can help chocolate flow more easily by reducing the friction between its particles. This is why it is often used during refining and conching.',
            'what_it_is' => 'emulsifier',
            'what_it_does' => 'flow',
            'where_it_matters' => 'formulation',
            'triplet' => 'emulsifier • flow • formulation',
            'flavor_notes' => 'emulsifier, flow, formulation',
            'flavor_notes_list' => ['emulsifier', 'flow', 'formulation'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Typically added at just 0.2%–0.5% toward the end of conching. As an amphiphilic emulsifier, it coats sugar particles, dramatically lowering plastic viscosity so chocolate pours effortlessly into delicate moulds.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'milk-powder',
            'ingredient_name' => 'Milk Powder',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'A key ingredient in milk chocolate. It contributes milk flavour, creaminess and body while influencing the way chocolate melts on the palate.',
            'what_it_is' => 'milk chocolate',
            'what_it_does' => 'creaminess',
            'where_it_matters' => 'texture',
            'triplet' => 'milk chocolate • creaminess • texture',
            'flavor_notes' => 'milk chocolate, creaminess, texture',
            'flavor_notes_list' => ['milk chocolate', 'creaminess', 'texture'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Dehydrated dairy solids provide proteins and lactose without adding water, which would cause molten chocolate to seize. Roller-dried powder introduces free dairy fat for a softer melt, while spray-dried powder offers a firmer snap.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'vanilla',
            'ingredient_name' => 'Vanilla',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'Vanilla doesn\'t make chocolate taste like vanilla alone. Used carefully, it can round harsh notes and make the overall chocolate profile feel smoother and more complete.',
            'what_it_is' => 'flavour',
            'what_it_does' => 'aroma',
            'where_it_matters' => 'balance',
            'triplet' => 'flavour • aroma • balance',
            'flavor_notes' => 'flavour, aroma, balance',
            'flavor_notes_list' => ['flavour', 'aroma', 'balance'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Natural vanillin acts as an aromatic harmonizer. It softens intense astringency and sharp volatile acids from dark cocoa beans, rounding the flavor profile into a unified sensory experience.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'cocoa-solids',
            'ingredient_name' => 'Cocoa Solids',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'The non-fat portion of cocoa beans. They carry much of chocolate\'s colour, aroma and flavour — including its bitterness and roasted notes.',
            'what_it_is' => 'cocoa',
            'what_it_does' => 'flavour',
            'where_it_matters' => 'bitterness',
            'triplet' => 'cocoa • flavour • bitterness',
            'flavor_notes' => 'cocoa, flavour, bitterness',
            'flavor_notes_list' => ['cocoa', 'flavour', 'bitterness'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Rich in polyphenols, antioxidants, theobromine, and minerals. Cocoa solids determine a chocolate bar\'s color depth, roasted notes, and perceived bitterness.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'cocoa-bean',
            'ingredient_name' => 'Cocoa Bean',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 1 — Chocolate Fundamentals',
            'level_number' => 1,
            'short_desc' => 'Every chocolate begins here. Inside the cocoa pod are seeds surrounded by pulp; after fermentation, drying and roasting, these seeds become the raw material for chocolate.',
            'what_it_is' => 'origin',
            'what_it_does' => 'cacao',
            'where_it_matters' => 'bean-to-bar',
            'triplet' => 'origin • cacao • bean-to-bar',
            'flavor_notes' => 'origin, cacao, bean-to-bar',
            'flavor_notes_list' => ['origin', 'cacao', 'bean-to-bar'],
            'origin_region' => 'Level 1 — Fundamentals',
            'detailed_notes' => 'Harvested from Theobroma cacao pods. Post-harvest fermentation in wooden sweat-boxes creates chemical flavor precursors that develop into characteristic chocolate aromas during precision roasting.',
            'modal_key' => 'ingredient-spotlight'
        ],

        // ==========================================
        // LEVEL 2 — PROCESSING & SUPPORTING INGREDIENTS
        // ==========================================
        [
            'id' => 'cocoa-pulp',
            'ingredient_name' => 'Cocoa Pulp',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 2 — Processing & Supporting Ingredients',
            'level_number' => 2,
            'short_desc' => 'The juicy white pulp surrounding cocoa beans inside the pod. It is naturally sweet and acidic and plays an important role during cocoa fermentation.',
            'what_it_is' => 'cacao pod',
            'what_it_does' => 'fermentation',
            'where_it_matters' => 'flavour',
            'triplet' => 'cacao pod • fermentation • flavour',
            'flavor_notes' => 'cacao pod, fermentation, flavour',
            'flavor_notes_list' => ['cacao pod', 'fermentation', 'flavour'],
            'origin_region' => 'Level 2 — Processing & Supporting',
            'detailed_notes' => 'Composed of water, sugars, citric acid, and pectin. Yeasts and lactic acid bacteria feed on the pulp sugars during fermentation, generating heat up to 50°C and vital organic acids.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'cocoa-shell',
            'ingredient_name' => 'Cocoa Shell',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 2 — Processing & Supporting Ingredients',
            'level_number' => 2,
            'short_desc' => 'The thin outer layer removed from cocoa beans during winnowing. Although usually separated from the nib, cocoa shells can still contain useful cocoa aroma.',
            'what_it_is' => 'winnowing',
            'what_it_does' => 'aroma',
            'where_it_matters' => 'cocoa',
            'triplet' => 'winnowing • aroma • cocoa',
            'flavor_notes' => 'winnowing, aroma, cocoa',
            'flavor_notes_list' => ['winnowing', 'aroma', 'cocoa'],
            'origin_region' => 'Level 2 — Processing & Supporting',
            'detailed_notes' => 'Represents 10–14% of the dry cocoa bean. Removed during winnowing to protect stone grinders and maintain purity, but frequently brewed into fragrant, antioxidant-rich cacao tea.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'jaggery',
            'ingredient_name' => 'Jaggery',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 2 — Processing & Supporting Ingredients',
            'level_number' => 2,
            'short_desc' => 'An unrefined sugar traditionally made from sugarcane or other plant sources. In chocolate, it brings sweetness along with its characteristic caramel-like flavour.',
            'what_it_is' => 'sweetener',
            'what_it_does' => 'flavour',
            'where_it_matters' => 'Indian',
            'triplet' => 'sweetener • flavour • Indian',
            'flavor_notes' => 'sweetener, flavour, Indian',
            'flavor_notes_list' => ['sweetener', 'flavour', 'Indian'],
            'origin_region' => 'Level 2 — Processing & Supporting',
            'detailed_notes' => 'Traditional Indian unrefined sweetener containing molasses and natural minerals. Must be thoroughly dehydrated before stone refining, as moisture levels above 1% cause chocolate mass to seize.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'milk-fat',
            'ingredient_name' => 'Milk Fat',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 2 — Processing & Supporting Ingredients',
            'level_number' => 2,
            'short_desc' => 'The fat naturally present in dairy ingredients. In milk chocolate, it contributes richness, creaminess and a softer melting character.',
            'what_it_is' => 'milk chocolate',
            'what_it_does' => 'fat',
            'where_it_matters' => 'mouthfeel',
            'triplet' => 'milk chocolate • fat • mouthfeel',
            'flavor_notes' => 'milk chocolate, fat, mouthfeel',
            'flavor_notes_list' => ['milk chocolate', 'fat', 'mouthfeel'],
            'origin_region' => 'Level 2 — Processing & Supporting',
            'detailed_notes' => 'Milk fat exerts a eutectic softening effect on cocoa butter, lowering its crystallization point. This creates a softer, more luxurious bite and inhibits surface fat bloom during storage.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'coconut-oil',
            'ingredient_name' => 'Coconut Oil',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 2 — Processing & Supporting Ingredients',
            'level_number' => 2,
            'short_desc' => 'A plant-based fat that melts relatively easily. When used in chocolate spreads or formulations, it can influence texture, spreadability and melting behaviour.',
            'what_it_is' => 'plant fat',
            'what_it_does' => 'texture',
            'where_it_matters' => 'spreads',
            'triplet' => 'plant fat • texture • spreads',
            'flavor_notes' => 'plant fat, texture, spreads',
            'flavor_notes_list' => ['plant fat', 'texture', 'spreads'],
            'origin_region' => 'Level 2 — Processing & Supporting',
            'detailed_notes' => 'Rich in medium-chain lauric acid, coconut oil liquefies at approximately 24°C. It is particularly valued in artisan spreads, meltaways, and ice cream dips for immediate, cooling melt.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'hazelnut',
            'ingredient_name' => 'Hazelnut',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 2 — Processing & Supporting Ingredients',
            'level_number' => 2,
            'short_desc' => 'A classic chocolate pairing for a reason. Roasting develops nutty, caramelised aromas that complement cocoa\'s roasted character.',
            'what_it_is' => 'nuts',
            'what_it_does' => 'roasting',
            'where_it_matters' => 'flavour',
            'triplet' => 'nuts • roasting • flavour',
            'flavor_notes' => 'nuts, roasting, flavour',
            'flavor_notes_list' => ['nuts', 'roasting', 'flavour'],
            'origin_region' => 'Level 2 — Processing & Supporting',
            'detailed_notes' => 'Roasting initiates Maillard reactions producing alkylpyrazines that synergize with cocoa volatiles. Finely stone-milled with cocoa and sugar, hazelnut paste produces legendary Italian Gianduja.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'salt',
            'ingredient_name' => 'Salt',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 2 — Processing & Supporting Ingredients',
            'level_number' => 2,
            'short_desc' => 'Used in very small quantities, salt can make chocolate flavours feel more pronounced and can help balance sweetness and bitterness.',
            'what_it_is' => 'flavour',
            'what_it_does' => 'balance',
            'where_it_matters' => 'formulation',
            'triplet' => 'flavour • balance • formulation',
            'flavor_notes' => 'flavour, balance, formulation',
            'flavor_notes_list' => ['flavour', 'balance', 'formulation'],
            'origin_region' => 'Level 2 — Processing & Supporting',
            'detailed_notes' => 'Trace sodium ions bind to tongue taste receptors, suppressing bitter cacao perception and enhancing sweetness perception, elevating the multi-layered nuances of single-origin beans.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'invert-sugar',
            'ingredient_name' => 'Invert Sugar',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 2 — Processing & Supporting Ingredients',
            'level_number' => 2,
            'short_desc' => 'A mixture of glucose and fructose formed when sucrose is split. In chocolate fillings and confectionery, it can influence sweetness, moisture retention and texture.',
            'what_it_is' => 'confectionery',
            'what_it_does' => 'sugar',
            'where_it_matters' => 'texture',
            'triplet' => 'confectionery • sugar • texture',
            'flavor_notes' => 'confectionery, sugar, texture',
            'flavor_notes_list' => ['confectionery', 'sugar', 'texture'],
            'origin_region' => 'Level 2 — Processing & Supporting',
            'detailed_notes' => 'Because split glucose and fructose resist crystallization, invert sugar maintains silky softness in ganaches, binds free water, and extends shelf life without graininess.',
            'modal_key' => 'ingredient-spotlight'
        ],

        // ==========================================
        // LEVEL 3 — MORE TECHNICAL INGREDIENTS
        // ==========================================
        [
            'id' => 'skimmed-milk-powder',
            'ingredient_name' => 'Skimmed Milk Powder',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 3 — More Technical Ingredients',
            'level_number' => 3,
            'short_desc' => 'Milk powder with the fat removed. It contributes dairy solids, protein and milky flavour to milk and white chocolate without adding extra dairy fat.',
            'what_it_is' => 'dairy solids',
            'what_it_does' => 'flavour',
            'where_it_matters' => 'formulation',
            'triplet' => 'dairy solids • flavour • formulation',
            'flavor_notes' => 'dairy solids, flavour, formulation',
            'flavor_notes_list' => ['dairy solids', 'flavour', 'formulation'],
            'origin_region' => 'Level 3 — Technical',
            'detailed_notes' => 'Provides essential casein and whey proteins and lactose while keeping fat content strictly controllable, enabling chocolatiers to maintain a firm snap in milk chocolate recipes.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'anhydrous-milk-fat',
            'ingredient_name' => 'Anhydrous Milk Fat',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 3 — More Technical Ingredients',
            'level_number' => 3,
            'short_desc' => 'Pure butterfat with nearly all water removed. In chocolate recipes, it softens cocoa butter crystal hardness and helps inhibit chocolate fat bloom.',
            'what_it_is' => 'butterfat',
            'what_it_does' => 'anti-bloom',
            'where_it_matters' => 'mouthfeel',
            'triplet' => 'butterfat • anti-bloom • mouthfeel',
            'flavor_notes' => 'butterfat, anti-bloom, mouthfeel',
            'flavor_notes_list' => ['butterfat', 'anti-bloom', 'mouthfeel'],
            'origin_region' => 'Level 3 — Technical',
            'detailed_notes' => 'Consisting of 99.8% pure dairy fat, AMF interrupts pure cocoa butter crystal matrix formation, slowing the polymorphic transformation into defective Form VI bloom crystals.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'pgpr',
            'ingredient_name' => 'PGPR',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 3 — More Technical Ingredients',
            'level_number' => 3,
            'short_desc' => 'A specialized emulsifier derived from castor oil. It dramatically lowers the yield stress of melted chocolate, allowing thin, bubble-free enrobing and complex mould filling.',
            'what_it_is' => 'emulsifier',
            'what_it_does' => 'flow',
            'where_it_matters' => 'moulding',
            'triplet' => 'emulsifier • flow • moulding',
            'flavor_notes' => 'emulsifier, flow, moulding',
            'flavor_notes_list' => ['emulsifier', 'flow', 'moulding'],
            'origin_region' => 'Level 3 — Technical',
            'detailed_notes' => 'Polyglycerol polyricinoleate works synergistically with lecithin. While lecithin lowers plastic viscosity, PGPR nearly eliminates yield stress, letting chocolate flow into intricate shapes.',
            'modal_key' => 'ingredient-spotlight'
        ],
        [
            'id' => 'cocoa-butter-alternatives',
            'ingredient_name' => 'Cocoa Butter Alternatives',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'clean_tag' => 'INGREDIENT SPOTLIGHT',
            'level' => 'Level 3 — More Technical Ingredients',
            'level_number' => 3,
            'short_desc' => 'Fats formulated to replicate or substitute cocoa butter properties. Used in specific confectioneries to manage melting points, heat stability and ingredient cost.',
            'what_it_is' => 'specialty fats',
            'what_it_does' => 'stability',
            'where_it_matters' => 'confectionery',
            'triplet' => 'specialty fats • stability • confectionery',
            'flavor_notes' => 'specialty fats, stability, confectionery',
            'flavor_notes_list' => ['specialty fats', 'stability', 'confectionery'],
            'origin_region' => 'Level 3 — Technical',
            'detailed_notes' => 'Includes Cocoa Butter Equivalents (CBEs, chemically compatible with cocoa butter) and Cocoa Butter Substitutes (CBSs, lauric fats requiring no tempering but intolerant to cocoa butter blending).',
            'modal_key' => 'ingredient-spotlight'
        ]
    ];

    return $library;
}

/**
 * Fetch the currently active ingredient spotlight or generate a fresh one via OpenRouter AI if expired (> 6 hours).
 *
 * @param bool $forceRefresh Force an immediate AI generation or advance
 * @return array Spotlight data array
 */
function get_current_ingredient_spotlight($forceRefresh = false) {
    $cacheHours = 6;
    $cacheSeconds = $cacheHours * 3600;
    $cacheFile = __DIR__ . '/../data/cache/ingredient_spotlight.json';
    $library = get_ingredient_spotlight_library();

    // 1. Fast file cache check
    if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheSeconds)) {
        $cachedRaw = @file_get_contents($cacheFile);
        if ($cachedRaw !== false) {
            $cached = json_decode($cachedRaw, true);
            if (is_array($cached) && !empty($cached['ingredient_name']) && !empty($cached['triplet'])) {
                return $cached;
            }
        }
    }

    $pdo = null;
    try {
        $pdo = get_db();
    } catch (Exception $e) {
        // Continue with library fallback
    }

    // 2. If force refresh requested, try AI generation with strict rules
    if ($forceRefresh && $pdo) {
        $generated = generate_spotlight_from_ai();
        if ($generated && !empty($generated['ingredient_name']) && !empty($generated['short_desc'])) {
            try {
                $ins = $pdo->prepare("INSERT INTO ai_ingredient_spotlights 
                    (ingredient_name, tag, short_desc, detailed_notes, flavor_notes, origin_region, modal_key, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $ins->execute([
                    $generated['ingredient_name'],
                    $generated['tag'] ?? '🌱 INGREDIENT SPOTLIGHT',
                    $generated['short_desc'],
                    $generated['detailed_notes'] ?? '',
                    $generated['flavor_notes'] ?? '',
                    $generated['origin_region'] ?? ($generated['level'] ?? 'Level 1 — Chocolate Fundamentals'),
                    $generated['modal_key'] ?? 'ingredient-spotlight'
                ]);

                // Retain only latest 25 spotlights
                $pdo->exec("DELETE FROM ai_ingredient_spotlights WHERE id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM ai_ingredient_spotlights ORDER BY id DESC LIMIT 25
                    ) as temp
                )");

                $output = format_spotlight_output($generated, $cacheSeconds, false);
                save_spotlight_cache($cacheFile, $output);
                return $output;
            } catch (Exception $e) {
                $output = format_spotlight_output($generated, $cacheSeconds, false);
                save_spotlight_cache($cacheFile, $output);
                return $output;
            }
        }
    }

    // 3. Check latest spotlight from database
    if ($pdo && !$forceRefresh) {
        try {
            $stmt = $pdo->query("SELECT * FROM ai_ingredient_spotlights ORDER BY id DESC LIMIT 1");
            $latest = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($latest && !empty($latest['ingredient_name'])) {
                // Verify if it conforms to our curated library
                $curatedMatch = find_in_curated_library($latest['ingredient_name']);
                if ($curatedMatch) {
                    $age = !empty($latest['created_at']) ? (time() - strtotime($latest['created_at'])) : 0;
                    $merged = array_merge($curatedMatch, $latest);
                    $output = format_spotlight_output($merged, max(0, $cacheSeconds - $age), true);
                    save_spotlight_cache($cacheFile, $output);
                    return $output;
                }
            }
        } catch (Exception $e) {
            // DB query fallback
        }
    }

    // 4. Default: Deterministically rotate across the 18 curated items every 6 hours
    $curated = get_curated_spotlight_fallback();
    $output = format_spotlight_output($curated, $cacheSeconds, true);
    save_spotlight_cache($cacheFile, $output);
    return $output;
}

/**
 * Save output to cache file safely
 */
function save_spotlight_cache(string $cacheFile, array $output): void {
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    @file_put_contents($cacheFile, json_encode($output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

/**
 * Lookup an ingredient name in the curated library
 */
function find_in_curated_library(string $name): ?array {
    $library = get_ingredient_spotlight_library();
    $lower = strtolower(trim($name));
    foreach ($library as $item) {
        if (strtolower($item['ingredient_name']) === $lower || strtolower($item['id']) === $lower) {
            return $item;
        }
    }
    return null;
}

/**
 * Normalizes spotlight entry for consistent frontend consumption
 */
function format_spotlight_output(array $item, int $expiresIn = 21600, bool $isCached = true): array {
    $library = get_ingredient_spotlight_library();
    
    // Attempt to match with verified library for complete canonical data
    $matched = find_in_curated_library($item['ingredient_name'] ?? '');
    if ($matched) {
        // Prefer curated definitions for scientific accuracy and beginner-friendly tone
        $item = array_merge($matched, array_filter($item, fn($v) => !empty($v)));
    }

    $tag = trim($item['tag'] ?? 'INGREDIENT SPOTLIGHT');
    $cleanTag = preg_replace('/^[🌱\s\-_]+/u', '', $tag);
    if (empty($cleanTag)) {
        $cleanTag = 'INGREDIENT SPOTLIGHT';
    }

    // Determine Level
    $level = $item['level'] ?? ($item['origin_region'] ?? 'Level 1 — Chocolate Fundamentals');
    if (strpos($level, 'Level') === false) {
        $level = 'Level 1 — Chocolate Fundamentals';
    }

    // Determine triplet [what it is] • [what it does] • [where it matters]
    $whatItIs = $item['what_it_is'] ?? '';
    $whatItDoes = $item['what_it_does'] ?? '';
    $whereItMatters = $item['where_it_matters'] ?? '';

    // If triplet not explicitly set, derive from flavor_notes
    if (empty($whatItIs) || empty($whatItDoes) || empty($whereItMatters)) {
        $rawNotes = $item['flavor_notes'] ?? '';
        $parts = [];
        if (is_string($rawNotes)) {
            if (strpos($rawNotes, '•') !== false) {
                $parts = array_map('trim', explode('•', $rawNotes));
            } else {
                $parts = array_map('trim', explode(',', $rawNotes));
            }
        } elseif (is_array($rawNotes)) {
            $parts = $rawNotes;
        }

        $parts = array_values(array_filter($parts));
        $whatItIs = $parts[0] ?? 'cacao ingredient';
        $whatItDoes = $parts[1] ?? 'flavour';
        $whereItMatters = $parts[2] ?? 'formulation';
    }

    $triplet = trim("{$whatItIs} • {$whatItDoes} • {$whereItMatters}");
    $flavorList = [$whatItIs, $whatItDoes, $whereItMatters];

    // Construct highlights for modal
    $highlights = [
        'What It Is: ' . ucfirst($whatItIs),
        'What It Does: ' . ucfirst($whatItDoes),
        'Where It Matters: ' . ucfirst($whereItMatters)
    ];

    if (!empty($item['detailed_notes'])) {
        $highlights[] = 'Established Science: ' . $item['detailed_notes'];
    }

    $item['tag'] = '🌱 ' . $cleanTag;
    $item['clean_tag'] = $cleanTag;
    $item['level'] = $level;
    $item['what_it_is'] = $whatItIs;
    $item['what_it_does'] = $whatItDoes;
    $item['where_it_matters'] = $whereItMatters;
    $item['triplet'] = $triplet;
    $item['flavor_notes_list'] = $flavorList;
    $item['modal_highlights'] = $highlights;
    $item['is_cached'] = $isCached;
    $item['expires_in'] = $expiresIn;

    return $item;
}

/**
 * Generate a new ingredient spotlight using OpenRouter AI or Gemini fallback,
 * strictly abiding by the 9 Recommended AI Content Rules from RT CHOCOS.
 */
function generate_spotlight_from_ai() {
    $orApiKey = getenv('OPENROUTER_API_KEY') ?: ($_ENV['OPENROUTER_API_KEY'] ?? '');
    $geminiApiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');

    $systemInstruction = "You are CocoaGenius AI, the artisan chocolate educator and food scientist for RT Chocos.\n"
        . "Generate an entry for our rotating Ingredient Spotlight section. Each card teaches one useful chocolate fact in a few seconds.\n\n"
        . "STRICT RECOMMENDED AI CONTENT RULES:\n"
        . "1. Write for a curious beginner, not a chocolate expert.\n"
        . "2. Explain one ingredient accurately in simple language.\n"
        . "3. Start with what the ingredient is, then explain one important role it plays in chocolate.\n"
        . "4. Avoid exaggerated sensory claims, unsupported health claims, poetic descriptions, obscure terminology and invented facts.\n"
        . "5. Do not use technical terminology unless it is immediately explained.\n"
        . "6. Keep the card copy to approximately 25–35 words.\n"
        . "7. Every statement should be technically defensible from established chocolate science.\n"
        . "8. If an ingredient has multiple functions, mention only the most important beginner-friendly function.\n"
        . "9. Select ONLY from this controlled, verified ingredient database:\n"
        . "   - Level 1 — Chocolate Fundamentals: Cocoa bean, Cocoa nibs, Cocoa mass, Cocoa butter, Cocoa solids, Cocoa powder, Sugar, Milk powder, Lecithin, Vanilla\n"
        . "   - Level 2 — Processing & Supporting Ingredients: Cocoa shell, Cocoa pulp, Milk fat, Jaggery, Invert sugar, Glucose syrup, Coconut oil, Hazelnut, Almond, Salt\n"
        . "   - Level 3 — More Technical Ingredients: Anhydrous milk fat, Whey powder, Skimmed milk powder, PGPR, Maltodextrin, Sorbitol, Freeze-dried fruit, Cocoa butter equivalents, Cocoa butter substitutes\n\n"
        . "SUGGESTED CARD STRUCTURE:\n"
        . "tag: \"INGREDIENT SPOTLIGHT\"\n"
        . "ingredient_name: [Exact Name from database]\n"
        . "level: [e.g. \"Level 1 — Chocolate Fundamentals\"]\n"
        . "short_desc: [25–35 word scientifically accurate explanation]\n"
        . "what_it_is: [1-3 words: what it is]\n"
        . "what_it_does: [1-3 words: what it does]\n"
        . "where_it_matters: [1-3 words: where it matters]\n"
        . "detailed_notes: [1-2 sentences of beginner-friendly established chocolate science]\n\n"
        . "You MUST output ONLY a valid, raw JSON object (without markdown code blocks or backticks) with this exact schema:\n"
        . "{\n"
        . "  \"ingredient_name\": \"Cocoa Butter\",\n"
        . "  \"tag\": \"🌱 INGREDIENT SPOTLIGHT\",\n"
        . "  \"level\": \"Level 1 — Chocolate Fundamentals\",\n"
        . "  \"short_desc\": \"The natural fat extracted from cocoa beans. It gives chocolate its smooth texture, clean melt and characteristic snap.\",\n"
        . "  \"what_it_is\": \"natural fat\",\n"
        . "  \"what_it_does\": \"texture\",\n"
        . "  \"where_it_matters\": \"tempering\",\n"
        . "  \"detailed_notes\": \"Cocoa butter melts sharply at human body temperature and crystallizes into stable Beta-V crystals during tempering.\"\n"
        . "}";

    // 1. Try OpenRouter Free Models
    if (!empty($orApiKey)) {
        $orUrl = 'https://openrouter.ai/api/v1/chat/completions';
        $orHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $orApiKey,
            "HTTP-Referer: http://localhost:8000",
            "X-Title: RT Chocos Ingredient Spotlight"
        ];

        $models = [
            'openrouter/free',
            'google/gemma-2-9b-it:free',
            'meta-llama/llama-3.3-70b-instruct:free'
        ];

        foreach ($models as $model) {
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemInstruction],
                    ['role' => 'user', 'content' => 'Pick one ingredient from the controlled database and generate a fresh ingredient spotlight card in valid JSON format adhering strictly to the 25-35 word and beginner-friendly rules.']
                ],
                'temperature' => 0.7
            ];

            $res = makeSpotlightPostRequest($orUrl, $orHeaders, $payload, 10);
            if ($res['code'] === 200 && !empty($res['body'])) {
                $data = json_decode($res['body'], true);
                $rawContent = $data['choices'][0]['message']['content'] ?? '';
                $parsed = parseJsonFromAiResponse($rawContent);
                if ($parsed && !empty($parsed['ingredient_name']) && !empty($parsed['short_desc'])) {
                    return sanitizeSpotlightData($parsed);
                }
            }
        }
    }

    // 2. Failover to direct Gemini API
    if (!empty($geminiApiKey)) {
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $geminiApiKey;
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => 'Pick one ingredient from the controlled database and generate a fresh ingredient spotlight card in valid JSON format adhering strictly to the 25-35 word and beginner-friendly rules.']]]
            ],
            'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.75
            ]
        ];

        $res = makeSpotlightPostRequest($apiUrl, ["Content-Type: application/json"], $payload, 8);
        if ($res['code'] === 200 && !empty($res['body'])) {
            $data = json_decode($res['body'], true);
            $rawContent = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $parsed = parseJsonFromAiResponse($rawContent);
            if ($parsed && !empty($parsed['ingredient_name']) && !empty($parsed['short_desc'])) {
                return sanitizeSpotlightData($parsed);
            }
        }
    }

    // 3. Fallback: Return next item from Curated Content Library
    return get_curated_spotlight_fallback();
}

/**
 * Sanitize and validate AI spotlight response against verified rules
 */
function sanitizeSpotlightData(array $data): array {
    $name = trim($data['ingredient_name'] ?? 'Cocoa Butter');
    
    // Check if the AI returned a known ingredient from our curated library
    $matched = find_in_curated_library($name);
    if ($matched) {
        // If matched, use authoritative technical foundation
        return [
            'ingredient_name' => $matched['ingredient_name'],
            'tag'             => '🌱 INGREDIENT SPOTLIGHT',
            'level'           => $matched['level'],
            'short_desc'      => !empty($data['short_desc']) && strlen($data['short_desc']) > 20 ? trim($data['short_desc']) : $matched['short_desc'],
            'what_it_is'      => trim($data['what_it_is'] ?? $matched['what_it_is']),
            'what_it_does'    => trim($data['what_it_does'] ?? $matched['what_it_does']),
            'where_it_matters' => trim($data['where_it_matters'] ?? $matched['where_it_matters']),
            'flavor_notes'    => trim($data['what_it_is'] ?? $matched['what_it_is']) . ', ' . trim($data['what_it_does'] ?? $matched['what_it_does']) . ', ' . trim($data['where_it_matters'] ?? $matched['where_it_matters']),
            'detailed_notes'  => !empty($data['detailed_notes']) ? trim($data['detailed_notes']) : $matched['detailed_notes'],
            'origin_region'   => $matched['level'],
            'modal_key'       => 'ingredient-spotlight'
        ];
    }

    $whatItIs = trim($data['what_it_is'] ?? 'cacao ingredient');
    $whatItDoes = trim($data['what_it_does'] ?? 'texture');
    $whereItMatters = trim($data['where_it_matters'] ?? 'formulation');

    return [
        'ingredient_name' => htmlspecialchars_decode($name),
        'tag'             => '🌱 INGREDIENT SPOTLIGHT',
        'level'           => htmlspecialchars_decode(trim($data['level'] ?? 'Level 1 — Chocolate Fundamentals')),
        'short_desc'      => htmlspecialchars_decode(trim($data['short_desc'] ?? 'The foundation ingredient giving craft chocolate its distinctive character.')),
        'what_it_is'      => htmlspecialchars_decode($whatItIs),
        'what_it_does'    => htmlspecialchars_decode($whatItDoes),
        'where_it_matters' => htmlspecialchars_decode($whereItMatters),
        'flavor_notes'    => "{$whatItIs}, {$whatItDoes}, {$whereItMatters}",
        'detailed_notes'  => htmlspecialchars_decode(trim($data['detailed_notes'] ?? '')),
        'origin_region'   => htmlspecialchars_decode(trim($data['level'] ?? 'Level 1 — Chocolate Fundamentals')),
        'modal_key'       => 'ingredient-spotlight'
    ];
}

/**
 * Extract clean JSON array from raw AI output string
 */
function parseJsonFromAiResponse($content) {
    if (empty($content)) return null;
    $trimmed = trim($content);

    // Remove markdown code blocks ```json ... ```
    if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $trimmed, $matches)) {
        $trimmed = $matches[1];
    } elseif (preg_match('/(\{.*\})/s', $trimmed, $matches)) {
        $trimmed = $matches[1];
    }

    $decoded = json_decode($trimmed, true);
    return is_array($decoded) ? $decoded : null;
}

/**
 * Helper to make HTTP POST requests via stream_context
 */
function makeSpotlightPostRequest($url, $headers, $payloadData, $timeout = 8) {
    $options = [
        'http' => [
            'method'        => 'POST',
            'header'        => implode("\r\n", $headers) . "\r\n",
            'content'       => json_encode($payloadData),
            'ignore_errors' => true,
            'timeout'       => $timeout
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    $httpCode = 200;
    $headersList = [];
    if (function_exists('http_get_last_response_headers')) {
        $headersList = http_get_last_response_headers() ?: [];
    } else {
        $definedVars = get_defined_vars();
        $headersList = isset($definedVars['http_response_header']) && is_array($definedVars['http_response_header']) ? $definedVars['http_response_header'] : [];
    }

    foreach ($headersList as $header) {
        if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/i', $header, $matches)) {
            $httpCode = (int)$matches[1];
            break;
        }
    }
    return ['code' => $httpCode, 'body' => $response];
}

/**
 * Curated fallback when API or DB is unavailable, rotating through the 18 library items
 */
function get_curated_spotlight_fallback(?int $index = null): array {
    $library = get_ingredient_spotlight_library();
    if ($index === null) {
        // Rotate every 6 hours across the library
        $index = (int)(floor(time() / (6 * 3600)) % count($library));
    }
    $index = $index % count($library);
    if ($index < 0) $index += count($library);
    return $library[$index];
}
