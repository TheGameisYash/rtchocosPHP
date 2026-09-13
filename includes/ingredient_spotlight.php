<?php
// includes/ingredient_spotlight.php - AI-Powered Ingredient Spotlight Service
require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/db.php';

/**
 * Fetch the currently active ingredient spotlight or generate a fresh one via OpenRouter AI if expired (> 6 hours).
 *
 * @param bool $forceRefresh Force an immediate AI generation regardless of age
 * @return array Spotlight data array
 */
function get_current_ingredient_spotlight($forceRefresh = false) {
    $cacheHours = 6;
    $cacheSeconds = $cacheHours * 3600;
    $pdo = get_db();

    // 1. Fetch latest spotlight from database
    $latest = null;
    try {
        $stmt = $pdo->query("SELECT * FROM ai_ingredient_spotlights ORDER BY id DESC LIMIT 1");
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // DB error fallback
    }

    $isFresh = false;
    if ($latest && !empty($latest['created_at'])) {
        $age = time() - strtotime($latest['created_at']);
        if ($age < $cacheSeconds && !$forceRefresh) {
            $isFresh = true;
        }
    }

    // Return cached entry if fresh
    if ($isFresh && $latest) {
        return format_spotlight_output($latest, max(0, $cacheSeconds - (time() - strtotime($latest['created_at']))), true);
    }

    // 2. Generate a new spotlight via OpenRouter AI
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
                $generated['origin_region'] ?? '',
                $generated['modal_key'] ?? 'ingredient-spotlight'
            ]);

            $newId = $pdo->lastInsertId();
            $generated['id'] = $newId;
            $generated['created_at'] = date('Y-m-d H:i:s');

            // Retain only latest 20 spotlights
            $pdo->exec("DELETE FROM ai_ingredient_spotlights WHERE id NOT IN (
                SELECT id FROM (
                    SELECT id FROM ai_ingredient_spotlights ORDER BY id DESC LIMIT 20
                ) as temp
            )");

            return format_spotlight_output($generated, $cacheSeconds, false);
        } catch (Exception $e) {
            return format_spotlight_output($generated, $cacheSeconds, false);
        }
    }

    // 3. Fallback to existing or curated list
    if ($latest) {
        return format_spotlight_output($latest, 0, true);
    }

    return format_spotlight_output(get_curated_spotlight_fallback(), $cacheSeconds, true);
}

/**
 * Normalizes spotlight entry for consistent frontend consumption
 */
function format_spotlight_output(array $item, int $expiresIn = 21600, bool $isCached = true): array {
    $tag = trim($item['tag'] ?? 'INGREDIENT SPOTLIGHT');
    $cleanTag = preg_replace('/^[🌱\s\-_]+/u', '', $tag);
    if (empty($cleanTag)) {
        $cleanTag = 'INGREDIENT SPOTLIGHT';
    }

    // Process flavor notes into array
    $rawNotes = $item['flavor_notes'] ?? '';
    if (is_array($rawNotes)) {
        $flavorList = $rawNotes;
    } elseif (is_string($rawNotes) && (str_starts_with(trim($rawNotes), '[') || str_starts_with(trim($rawNotes), '{'))) {
        $decoded = json_decode($rawNotes, true);
        $flavorList = is_array($decoded) ? $decoded : [$rawNotes];
    } elseif (is_string($rawNotes)) {
        $flavorList = array_values(array_filter(array_map('trim', explode(',', $rawNotes))));
    } else {
        $flavorList = ['Rich Cacao', 'Silky Melt'];
    }

    // Construct highlights for modal
    $highlights = [];
    if (!empty($item['origin_region'])) {
        $highlights[] = 'Estate Terroir: ' . $item['origin_region'];
    }
    if (!empty($flavorList)) {
        $highlights[] = 'Sensory Profile: ' . implode(', ', array_slice($flavorList, 0, 4));
    }
    if (!empty($item['detailed_notes'])) {
        $highlights[] = 'Craftsmanship: ' . $item['detailed_notes'];
    } else {
        $highlights[] = 'Bean-to-bar precision formulation and slow conching.';
    }

    $item['tag'] = '🌱 ' . $cleanTag;
    $item['clean_tag'] = $cleanTag;
    $item['flavor_notes_list'] = $flavorList;
    $item['modal_highlights'] = $highlights;
    $item['is_cached'] = $isCached;
    $item['expires_in'] = $expiresIn;

    return $item;
}

/**
 * Generate a new ingredient spotlight using OpenRouter AI with Gemini fallback
 */
function generate_spotlight_from_ai() {
    $orApiKey = getenv('OPENROUTER_API_KEY') ?: ($_ENV['OPENROUTER_API_KEY'] ?? '');
    $geminiApiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');

    $systemInstruction = "You are CocoaGenius AI, the artisan chocolate maker and cacao food science expert for RT Chocos.\n"
        . "Generate a single, compelling, sensory featured ingredient spotlight for our artisan chocolate atelier.\n"
        . "Select an authentic, luxurious confectionery or bean-to-bar ingredient (e.g. Single-Origin Kerala Cocoa Butter, Roasted Idukki Cacao Nibs, Bourbon Madagascar Vanilla, Cold-Pressed Hazelnut Gianduja, Freeze-Dried Raspberries, Toasted Cardamom Seeds, Organic Coconut Blossom Sugar, Smoked Fleur de Sel, or Criollo Cacao Liquor).\n"
        . "You MUST output ONLY a valid, raw JSON object (without markdown code blocks, backticks, or preamble) with this exact schema:\n"
        . "{\n"
        . "  \"ingredient_name\": \"Name of ingredient (2 to 4 words)\",\n"
        . "  \"tag\": \"🌱 INGREDIENT SPOTLIGHT\",\n"
        . "  \"short_desc\": \"A captivating, sensory 1-sentence description under 18 words on how it elevates chocolate.\",\n"
        . "  \"detailed_notes\": \"2 short sentences of craftsmanship or food science insight.\",\n"
        . "  \"flavor_notes\": \"3 or 4 comma-separated flavor descriptors\",\n"
        . "  \"origin_region\": \"Origin region (e.g. Idukki Valley, Kerala)\",\n"
        . "  \"modal_key\": \"bean-to-bar\"\n"
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
                    ['role' => 'user', 'content' => 'Generate a fresh new ingredient spotlight for RT Chocos in valid JSON format.']
                ],
                'temperature' => 0.8
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
                ['role' => 'user', 'parts' => [['text' => 'Generate a fresh new ingredient spotlight for RT Chocos in valid JSON format.']]]
            ],
            'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.85
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

    return null;
}

/**
 * Sanitize and validate AI spotlight response
 */
function sanitizeSpotlightData($data) {
    $allowedKeys = ['bean-to-bar', 'knowledge-hub', 'chocolate-lab', 'recipes-formulations', 'techniques', 'origins-atlas', 'workshops-academy'];
    $modalKey = $data['modal_key'] ?? 'bean-to-bar';
    if (!in_array($modalKey, $allowedKeys)) {
        $modalKey = 'bean-to-bar';
    }

    return [
        'ingredient_name' => htmlspecialchars_decode(trim($data['ingredient_name'] ?? 'Cocoa Butter')),
        'tag'             => htmlspecialchars_decode(trim($data['tag'] ?? '🌱 INGREDIENT SPOTLIGHT')),
        'short_desc'      => htmlspecialchars_decode(trim($data['short_desc'] ?? 'The golden fat that gives artisan chocolate its smoothness and soul.')),
        'detailed_notes'  => htmlspecialchars_decode(trim($data['detailed_notes'] ?? '')),
        'flavor_notes'    => htmlspecialchars_decode(trim($data['flavor_notes'] ?? '')),
        'origin_region'   => htmlspecialchars_decode(trim($data['origin_region'] ?? 'India')),
        'modal_key'       => $modalKey
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
 * Curated fallback when API or DB is unavailable
 */
function get_curated_spotlight_fallback() {
    $pool = [
        [
            'ingredient_name' => 'Single-Origin Cocoa Butter',
            'tag' => '🌱 INGREDIENT SPOTLIGHT',
            'short_desc' => 'The golden fat that gives artisan chocolate its velvet melt and glossy snap.',
            'detailed_notes' => 'Pure cold-pressed cocoa butter crystallized into Beta-V polymorphs for superior thermodynamic stability.',
            'flavor_notes' => 'Velvety, subtle white floral, warm cacao',
            'origin_region' => 'Idukki Valley, Kerala',
            'modal_key' => 'bean-to-bar',
            'created_at' => date('Y-m-d H:i:s'),
            'is_cached' => true,
            'expires_in' => 21600
        ],
        [
            'ingredient_name' => 'Roasted Idukki Cacao Nibs',
            'tag' => '🍫 CACAO SPOTLIGHT',
            'short_desc' => 'Crunchy, unadulterated cacao bean centers brimming with antioxidants and deep fruity acidity.',
            'detailed_notes' => 'Slow-roasted at 115°C to preserve naturally occurring polyphenols and fruity tannins.',
            'flavor_notes' => 'Citrus zest, dried cranberries, dark roast nut',
            'origin_region' => 'Malabar Coast Foothills, Kerala',
            'modal_key' => 'bean-to-bar',
            'created_at' => date('Y-m-d H:i:s'),
            'is_cached' => true,
            'expires_in' => 21600
        ],
        [
            'ingredient_name' => 'Bourbon Vanilla Bean Pods',
            'tag' => '✨ ARTISAN SPOTLIGHT',
            'short_desc' => 'Sun-cured heirloom pods that soften dark chocolate bitterness with buttery aromatic warmth.',
            'detailed_notes' => 'Hand-split and scraped into stone melangers during conching so whole vanillin crystals bond seamlessly.',
            'flavor_notes' => 'Sweet cream, woody caramel, amber floral',
            'origin_region' => 'Western Ghats Agroforests',
            'modal_key' => 'chocolate-lab',
            'created_at' => date('Y-m-d H:i:s'),
            'is_cached' => true,
            'expires_in' => 21600
        ]
    ];

    // Pick based on 6-hour segment of the day
    $index = (int)(floor(time() / (6 * 3600)) % count($pool));
    return $pool[$index];
}
