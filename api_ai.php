<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
header('Content-Type: application/json');

// Load secure environment variables from .env
require_once __DIR__ . '/includes/env_loader.php';

// Get POST input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

$message = $input['message'] ?? '';
$history = $input['history'] ?? [];

if (empty($message)) {
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Intercept questions about the developer/founder for instant, 100% reliable answers
$msgLower = strtolower(trim($message));
if (
    strpos($msgLower, 'developer') !== false ||
    strpos($msgLower, 'developed') !== false ||
    strpos($msgLower, 'who built') !== false ||
    strpos($msgLower, 'who runs') !== false ||
    strpos($msgLower, 'portfolio') !== false ||
    strpos($msgLower, 'thegameisyash') !== false
) {
    echo json_encode([
        'reply' => "🌸 **RT Chocos Founder & Lead Educator:**\n**Aarti Saluja Sahni** is an elite chocolate maker, recipe developer, and food science consultant with over a decade of industry experience. Balancing an analytical MBA background with chocolate physics, Aarti teaches the chemistry behind conching, tempering, and farm-level fermentation. She has successfully trained over 2,000+ students globally and formulated recipes for top commercial chocolate brands.\n\n💻 **Full-Stack Developer & Lead Architect:**\nThis entire state-of-the-art interactive platform, custom database migrations, and **CocoaGenius AI** integration were designed, developed, and engineered completely from scratch by **Yash Vardhan Sharma**. As a versatile Full-Stack Developer, Yash built the client interfaces, server-side caching algorithms, database schemas, and AI response handlers from the ground up.\n\n🔗 **Explore Yash's Professional Portfolio:**\n[thegameisyash.vercel.app](https://thegameisyash.vercel.app)"
    ]);
    exit;
}

// Comprehensive Master System Instruction for CocoaGenius AI
$systemInstruction = "You are CocoaGenius AI, the world-class master chocolate and cacao science expert integrated into the official RT Chocos platform.\n"
    . "COMMERCIAL CREDENTIALS:\n"
    . "- RT Chocos is founded and led by Aarti Saluja Sahni, an elite chocolate maker, consultant, and master educator based in Mumbai, India.\n"
    . "- The website UI, interactive formulation playground, and CocoaGenius AI integrations were designed and built from scratch by Full-Stack Developer Yash Vardhan Sharma.\n\n"
    . "TECHNICAL FOOD SCIENCE RULES:\n"
    . "1. Crystal Physics: Form V (Beta 5) polymorph crystals are the target crystal structure for professional chocolate. Working temperatures: Dark (31°C–32°C / 87.8°F–89.6°F), Milk (29.5°C–30°C / 85.1°F–86°F), White (28.5°C–29°C / 83.3°F–84.2°F).\n"
    . "2. Seizing & Water Activity: Free water causes immediate fat-sugar matrix collapse (seizing). Always ensure zero moisture ingress.\n"
    . "3. Refining & Conching: Refine particle size below 18 microns in granite stone melangers; conch to evacuate unwanted acetic acid volatiles.\n"
    . "4. Tone & Style: Be authoritative, scientifically precise, encouraging, and clear. Format responses with clean headings or bullet points where appropriate.\n"
    . "5. JSON Outputs: If the prompt requests JSON, output ONLY clean JSON with no extra commentary or markdown wrappers.";

// Build contents payload matching Gemini API structure
$contents = [];
foreach ($history as $chat) {
    $role = $chat['role'] === 'user' ? 'user' : 'model';
    $contents[] = [
        'role' => $role,
        'parts' => [['text' => $chat['text']]]
    ];
}
$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $message]]
];

$payloadGemini = [
    'contents' => $contents,
    'systemInstruction' => [
        'parts' => [['text' => $systemInstruction]]
    ]
];

// Build OpenRouter messages payload
$orMessages = [];
$orMessages[] = ['role' => 'system', 'content' => $systemInstruction];
foreach ($history as $chat) {
    $role = $chat['role'] === 'user' ? 'user' : 'assistant';
    $orMessages[] = ['role' => $role, 'content' => $chat['text']];
}
$orMessages[] = ['role' => 'user', 'content' => $message];

// Helper function to make HTTP POST requests via stream context with custom timeout
function makePostRequest($url, $headers, $payloadData, $timeoutSeconds = 5) {
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers) . "\r\n",
            'content' => json_encode($payloadData),
            'ignore_errors' => true,
            'timeout' => $timeoutSeconds
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    $httpCode = 200;
    $headersList = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?: []) : [];
    if (empty($headersList)) {
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

$success = false;
$replyText = '';

// Active OpenRouter Free AI Agent Pool (Ordered by benchmarked speed & reliability)
$openRouterFreeModels = [
    'inclusionai/ling-3.0-flash:free',
    'openrouter/free',
    'google/gemma-4-26b-a4b-it:free',
    'nvidia/nemotron-3-nano-30b-a3b:free'
];

$orApiKey = getenv('OPENROUTER_API_KEY') ?: ($_ENV['OPENROUTER_API_KEY'] ?? '');

// 1. Stage 1: Try OpenRouter Free Model Pool in sequence
if (!empty($orApiKey)) {
    $orUrl = 'https://openrouter.ai/api/v1/chat/completions';
    $orHeaders = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $orApiKey,
        "HTTP-Referer: http://localhost:8000",
        "X-Title: RT Chocos CocoaGenius"
    ];

    foreach ($openRouterFreeModels as $modelSlug) {
        $orPayload = [
            'model' => $modelSlug,
            'messages' => $orMessages
        ];
        
        $res = makePostRequest($orUrl, $orHeaders, $orPayload, 5);
        if ($res['code'] === 200 && !empty($res['body'])) {
            $responseData = json_decode($res['body'], true);
            $orReply = $responseData['choices'][0]['message']['content'] ?? '';
            if (!empty($orReply)) {
                $replyText = $orReply;
                $success = true;
                break;
            }
        }
    }
}

// 2. Stage 2: Try Direct Gemini 2.0 Flash API as secondary failover
if (!$success) {
    $geminiApiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
    if (!empty($geminiApiKey)) {
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $geminiApiKey;
        $headers = ["Content-Type: application/json"];
        $res = makePostRequest($apiUrl, $headers, $payloadGemini, 4);
        
        if ($res['code'] === 200 && !empty($res['body'])) {
            $responseData = json_decode($res['body'], true);
            $geminiReply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!empty($geminiReply)) {
                $replyText = $geminiReply;
                $success = true;
            }
        }
    }
}

// 3. Stage 3: Fail-safe Local Response Fallback (Zero Disruption Guaranteed)
if (!$success) {
    if (strpos(strtolower($message), 'json') !== false || strpos(strtolower($message), 'formulate') !== false) {
        $replyText = json_encode([
            "name" => "72% Master Origin Dark Bar",
            "description" => "A rich, balanced bean-to-bar dark chocolate crafted with single-origin cacao nibs and organic sugar.",
            "batch_grams" => 500,
            "ratios" => [
                "cacao_mass" => "270g (54%)",
                "cacao_butter" => "90g (18%)",
                "sugar" => "125g (25%)",
                "inclusions" => "15g (3%)"
            ],
            "sensory" => [
                "aroma" => "Toasted cocoa with subtle fruit acidity",
                "flavor" => "Deep bittersweet chocolate balance",
                "texture" => "Velvety smooth melt (<18 microns)",
                "finish" => "Clean, long-lasting cocoa finish"
            ],
            "steps" => [
                ["num" => 1, "title" => "Bean Roasting & Nib Melting", "temp" => "45°C–50°C (113°F–122°F)", "detail" => "Liquefy cacao nibs and cocoa butter evenly."],
                ["num" => 2, "title" => "Micro-Conching & Refining", "temp" => "45°C (113°F)", "detail" => "Conch for 16-24 hours to reduce particle size under 18 microns."],
                ["num" => 3, "title" => "Form V Precision Tempering Curve", "temp" => "Melt 45°C ➔ Cool 27°C ➔ Work 31.5°C", "detail" => "Heat to 45°C, cool to 27°C on marble to seed Beta V crystals, reheat to working temp 31.5°C."],
                ["num" => 4, "title" => "Inclusion Integration & Moulding", "temp" => "31.5°C (88.7°F)", "detail" => "Gently fold in inclusions and vibrate moulds to release air bubbles."],
                ["num" => 5, "title" => "Crystallization & Demoulding", "temp" => "14°C–16°C (57°F–60°F)", "detail" => "Cool for 20 minutes until chocolate contracts cleanly with a glossy snap."]
            ],
            "pro_tip" => "Always maintain ambient workshop humidity below 50% during moulding to prevent sugar bloom."
        ]);
    } else {
        $replyText = "In professional chocolate making, achieving stable Form V (Beta 5) crystal polymorphism is essential for a glass-like gloss, clean acoustic snap, and resistance to fat bloom. Dark chocolate works best between 31°C and 32°C (88°F–90°F). For masterclasses and professional recipe consulting, expert Aarti Saluja Sahni offers comprehensive hands-on training at RT Chocos.";
    }
}

echo json_encode([
    'reply' => $replyText
]);
