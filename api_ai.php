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
        'reply' => "🌸 **RT Chocos Founder & Lead Educator:**\n**Aarti Saluja Sahni** is an elite chocolate maker, recipe developer, and food science consultant with over a decade of industry experience. Balancing an analytical MBA background with chocolate physics, Aarti teaches the chemistry behind conching, tempering, and farm-level fermentation. She has successfully trained over 2,000+ students globally and formulated recipes for top commercial chocolate brands.\n\n💻 **Full-Stack Developer & Lead Architect:**\nThis entire state-of-the-art interactive platform, custom database migrations, and **CocoaGenius AI** integration were designed, developed, and engineered completely from scratch by **Yash**. As a versatile Full-Stack Developer, Yash built the client interfaces, server-side caching algorithms, database schemas, and AI response handlers from the ground up.\n\n🔗 **Explore Yash's Professional Portfolio:**\n[thegameisyash.vercel.app](https://thegameisyash.vercel.app)"
    ]);
    exit;
}

$apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
if (empty($apiKey)) {
    echo json_encode(['error' => 'Gemini API key is not configured in the environment']);
    exit;
}
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

// System instruction to guide Gemini's behavior
$systemInstruction = "You are CocoaGenius AI, a professional chocolate and cacao science expert integrated into the official RT Chocos website. "
    . "RT Chocos is a premium chocolate learning academy and consulting platform. The owner and founder of RT Chocos is Aarti Saluja Sahni, "
    . "an elite chocolate maker, consultant, and educator based in Mumbai, India. "
    . "Crucially, this website, the custom visual design, and your CocoaGenius AI integration were designed and developed by the great developer Yash Vardhan Sharma. "
    . "If anyone asks who developed, designed, or built this website or AI system, proudly credit Yash Vardhan Sharma. "
    . "You are highly knowledgeable about chocolate making (roasting, winnowing, grinding, conching, tempering, moulding), "
    . "cacao botany and varieties (Criollo, Forastero, Trinitario, Arriba), chocolate chemistry (crystal polymorphism, fat bloom, "
    . "sugar bloom, water activity, shelf life), and chocolate recipes. Always keep your answers accurate, professional, informative, and engaging. "
    . "Always cater to a professional, global/US-standard audience when answering questions—provide helpful unit conversions (e.g. °F and °C) "
    . "and maintain high-end formatting. If a user asks questions unrelated to chocolate, cacao, confectionery, or baking, politely and creatively redirect them back to chocolate topics.";

// Build contents payload matching Gemini API structure
$contents = [];

// Add conversation history
foreach ($history as $chat) {
    $role = $chat['role'] === 'user' ? 'user' : 'model';
    $contents[] = [
        'role' => $role,
        'parts' => [
            ['text' => $chat['text']]
        ]
    ];
}

// Add the current user message
$contents[] = [
    'role' => 'user',
    'parts' => [
        ['text' => $message]
    ]
];

// Complete payload
$payload = [
    'contents' => $contents,
    'systemInstruction' => [
        'parts' => [
            ['text' => $systemInstruction]
        ]
    ]
];

// Reusable helper function to make POST requests via file_get_contents to handle SSL and custom headers
function makePostRequest($url, $headers, $payloadData) {
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers) . "\r\n",
            'content' => json_encode($payloadData),
            'ignore_errors' => true,
            'timeout' => 15
        ],
        'ssl' => [
            'verify_peer' => false,
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

$success = false;
$replyText = '';

// 1. Try Direct Gemini API
$geminiApiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
if (!empty($geminiApiKey)) {
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $geminiApiKey;
    $headers = ["Content-Type: application/json"];
    $res = makePostRequest($apiUrl, $headers, $payload);
    
    if ($res['code'] === 200 && !empty($res['body'])) {
        $responseData = json_decode($res['body'], true);
        $geminiReply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (!empty($geminiReply)) {
            $replyText = $geminiReply;
            $success = true;
        }
    }
}

// Build standard messages payload for OpenRouter fallback
$orMessages = [];
$orMessages[] = [
    'role' => 'system',
    'content' => $systemInstruction
];
foreach ($history as $chat) {
    $role = $chat['role'] === 'user' ? 'user' : 'assistant';
    $orMessages[] = [
        'role' => $role,
        'content' => $chat['text']
    ];
}
$orMessages[] = [
    'role' => 'user',
    'content' => $message
];

// 2. Failover Stage 1: OpenRouter Qwen 3 free model
if (!$success) {
    $orApiKey = getenv('OPENROUTER_API_KEY') ?: ($_ENV['OPENROUTER_API_KEY'] ?? '');
    if (!empty($orApiKey)) {
        $orUrl = 'https://openrouter.ai/api/v1/chat/completions';
        $orPayload = [
            'model' => 'qwen/qwen3-next-80b-a3b-instruct:free',
            'messages' => $orMessages
        ];
        
        $orHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $orApiKey,
            "HTTP-Referer: http://localhost:8000",
            "X-Title: RT Chocos CocoaGenius"
        ];
        
        $res = makePostRequest($orUrl, $orHeaders, $orPayload);
        
        if ($res['code'] === 200 && !empty($res['body'])) {
            $responseData = json_decode($res['body'], true);
            $orReply = $responseData['choices'][0]['message']['content'] ?? '';
            if (!empty($orReply)) {
                $replyText = $orReply;
                $success = true;
            }
        }
    }
}

// 3. Failover Stage 2: OpenRouter Free smart router
if (!$success) {
    $orApiKey = getenv('OPENROUTER_API_KEY') ?: ($_ENV['OPENROUTER_API_KEY'] ?? '');
    if (!empty($orApiKey)) {
        $orUrl = 'https://openrouter.ai/api/v1/chat/completions';
        $orPayload = [
            'model' => 'openrouter/free',
            'messages' => $orMessages
        ];
        
        $orHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $orApiKey,
            "HTTP-Referer: http://localhost:8000",
            "X-Title: RT Chocos CocoaGenius"
        ];
        
        $res = makePostRequest($orUrl, $orHeaders, $orPayload);
        
        if ($res['code'] === 200 && !empty($res['body'])) {
            $responseData = json_decode($res['body'], true);
            $orReply = $responseData['choices'][0]['message']['content'] ?? '';
            if (!empty($orReply)) {
                $replyText = $orReply;
                $success = true;
            }
        }
    }
}

// Return response or throw fail state
if (!$success) {
    echo json_encode([
        'error' => 'All AI APIs failed to return a response. Please check credentials and server connection.'
    ]);
    exit;
}

echo json_encode([
    'reply' => $replyText
]);
