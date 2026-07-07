<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
header('Content-Type: application/json');

// Load environment variables and database connection
require_once __DIR__ . '/includes/env_loader.php';
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = get_db();
} catch (Exception $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// 1. Check if the latest insight is fresh (within 10 minutes)
$stmt = $pdo->query("SELECT * FROM ai_insights ORDER BY id DESC LIMIT 1");
$latest = $stmt->fetch();

$throttle_minutes = 10;
$should_generate = true;

if ($latest) {
    $last_time = strtotime($latest['created_at']);
    $diff = time() - $last_time;
    if ($diff < ($throttle_minutes * 60)) {
        $should_generate = false;
    }
}

if (!$should_generate && $latest) {
    echo json_encode([
        'new_generated' => false,
        'insight' => $latest['insight_text']
    ]);
    exit;
}

// 2. Setup AI request credentials and prompt
$geminiApiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
$orApiKey = getenv('OPENROUTER_API_KEY') ?: ($_ENV['OPENROUTER_API_KEY'] ?? '');

$systemInstruction = "You are CocoaGenius AI, a professional chocolate and cacao science expert. Generate a single, short, inspiring cacao fact, recipe tip, or chocolate making wisdom (strictly under 30 words) for a homepage banner. Focus on cacao history, tempering curves, chemistry, or bean-to-bar secrets. Return ONLY the plain text of the insight, no quotes, no markdown, no conversational filler.";

// Request helper function
function makePostRequest($url, $headers, $payloadData) {
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers) . "\r\n",
            'content' => json_encode($payloadData),
            'ignore_errors' => true,
            'timeout' => 12
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
$newInsight = '';

// Pipeline Stage 1: Try Direct Gemini API
if (!empty($geminiApiKey)) {
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $geminiApiKey;
    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => 'Generate a new daily cacao insight banner phrase.']
                ]
            ]
        ],
        'systemInstruction' => [
            'parts' => [
                ['text' => $systemInstruction]
            ]
        ]
    ];
    
    $headers = ["Content-Type: application/json"];
    $res = makePostRequest($apiUrl, $headers, $payload);
    
    if ($res['code'] === 200 && !empty($res['body'])) {
        $responseData = json_decode($res['body'], true);
        $geminiReply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (!empty($geminiReply)) {
            $newInsight = trim($geminiReply);
            $success = true;
        }
    }
}

// Pipeline Stage 2: Failover to OpenRouter Qwen 3 Free Model
if (!$success && !empty($orApiKey)) {
    $orUrl = 'https://openrouter.ai/api/v1/chat/completions';
    $payload = [
        'model' => 'qwen/qwen3-next-80b-a3b-instruct:free',
        'messages' => [
            ['role' => 'system', 'content' => $systemInstruction],
            ['role' => 'user', 'content' => 'Generate a new daily cacao insight banner phrase.']
        ]
    ];
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $orApiKey,
        "HTTP-Referer: http://localhost:8000",
        "X-Title: RT Chocos CocoaGenius"
    ];
    
    $res = makePostRequest($orUrl, $headers, $payload);
    if ($res['code'] === 200 && !empty($res['body'])) {
        $responseData = json_decode($res['body'], true);
        $orReply = $responseData['choices'][0]['message']['content'] ?? '';
        if (!empty($orReply)) {
            $newInsight = trim($orReply);
            $success = true;
        }
    }
}

// Pipeline Stage 3: Failover to OpenRouter Free Smart Router
if (!$success && !empty($orApiKey)) {
    $orUrl = 'https://openrouter.ai/api/v1/chat/completions';
    $payload = [
        'model' => 'openrouter/free',
        'messages' => [
            ['role' => 'system', 'content' => $systemInstruction],
            ['role' => 'user', 'content' => 'Generate a new daily cacao insight banner phrase.']
        ]
    ];
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $orApiKey,
        "HTTP-Referer: http://localhost:8000",
        "X-Title: RT Chocos CocoaGenius"
    ];
    
    $res = makePostRequest($orUrl, $headers, $payload);
    if ($res['code'] === 200 && !empty($res['body'])) {
        $responseData = json_decode($res['body'], true);
        $orReply = $responseData['choices'][0]['message']['content'] ?? '';
        if (!empty($orReply)) {
            $newInsight = trim($orReply);
            $success = true;
        }
    }
}

// 3. Save to database if generation succeeded
if ($success && !empty($newInsight)) {
    // Strip surrounding quotes if the AI output them
    $newInsight = trim($newInsight, " \t\n\r\0\x0B\"'");
    
    try {
        $insStmt = $pdo->prepare("INSERT INTO ai_insights (insight_text) VALUES (?)");
        $insStmt->execute([$newInsight]);
        
        // Clean up entries keeping ONLY the latest 5 as requested by the user
        $pdo->exec("DELETE FROM ai_insights WHERE id NOT IN (
            SELECT id FROM (
                SELECT id FROM ai_insights ORDER BY id DESC LIMIT 5
            ) as temp
        )");
        
        echo json_encode([
            'new_generated' => true,
            'insight' => $newInsight
        ]);
        exit;
    } catch (Exception $dbEx) {
        // Log error and return fail state with db fallback
    }
}

// Fallback to database latest entry if generation failed or skipped
if ($latest) {
    echo json_encode([
        'new_generated' => false,
        'insight' => $latest['insight_text']
    ]);
} else {
    echo json_encode([
        'new_generated' => false,
        'insight' => "The chemistry of cacao tempering is the secret key to glossy, snap-worthy chocolate."
    ]);
}
