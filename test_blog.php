<?php
/**
 * RT Chocos Blog Test Suite
 * Automated backend verification script for blog layout, routing, and APIs.
 */

// Define color helper for CLI output
function color($text, $colorCode) {
    if (DIRECTORY_SEPARATOR === '\\') {
        return "\033[{$colorCode}m{$text}\033[0m";
    }
    return "\033[{$colorCode}m{$text}\033[0m";
}

$passed = 0;
$failed = 0;
$total = 0;

function assertContains($needle, $haystack, $testName) {
    global $passed, $failed, $total;
    $total++;
    if (strpos($haystack, $needle) !== false) {
        echo color("[PASS] ", "32") . $testName . "\n";
        $passed++;
        return true;
    } else {
        echo color("[FAIL] ", "31") . $testName . "\n";
        echo "       Expected output to contain: '$needle'\n";
        $failed++;
        return false;
    }
}

function assertNotContains($needle, $haystack, $testName) {
    global $passed, $failed, $total;
    $total++;
    if (strpos($haystack, $needle) === false) {
        echo color("[PASS] ", "32") . $testName . "\n";
        $passed++;
        return true;
    } else {
        echo color("[FAIL] ", "31") . $testName . "\n";
        echo "       Expected output NOT to contain: '$needle'\n";
        $failed++;
        return false;
    }
}

function assertEquals($expected, $actual, $testName) {
    global $passed, $failed, $total;
    $total++;
    if ($expected === $actual) {
        echo color("[PASS] ", "32") . $testName . "\n";
        $passed++;
        return true;
    } else {
        echo color("[FAIL] ", "31") . $testName . "\n";
        echo "       Expected: " . var_export($expected, true) . "\n";
        echo "       Actual:   " . var_export($actual, true) . "\n";
        $failed++;
        return false;
    }
}

// Subprocess script runner using stdin pipe to safely run scripts on Windows/Unix without escape/quoting hell
function runIsolatedScript($scriptRelativePath, $getParams = [], $serverOverrides = []) {
    $scriptPath = str_replace('\\', '/', __DIR__ . '/' . $scriptRelativePath);
    
    // Construct the PHP bootstrap code
    $bootstrap = "<?php\n";
    $bootstrap .= "error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);\n"; // suppress headers warnings in test output
    $bootstrap .= "\$_SERVER['HTTP_HOST'] = 'localhost:8080';\n";
    $bootstrap .= "\$_SERVER['REQUEST_METHOD'] = 'GET';\n";
    $bootstrap .= "\$_SERVER['HTTPS'] = 'off';\n";
    
    foreach ($serverOverrides as $k => $v) {
        $bootstrap .= "\$_SERVER['" . addslashes($k) . "'] = '" . addslashes($v) . "';\n";
    }
    foreach ($getParams as $k => $v) {
        $bootstrap .= "\$_GET['" . addslashes($k) . "'] = '" . addslashes($v) . "';\n";
    }
    
    $bootstrap .= "include '" . addslashes($scriptPath) . "';\n";
    $bootstrap .= "?>";

    // Execute php via proc_open and write the bootstrap to stdin
    $descriptors = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ];
    
    $process = proc_open('php', $descriptors, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], $bootstrap);
        fclose($pipes[0]);
        
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        proc_close($process);
        return $stdout . $stderr;
    }
    return '';
}

echo color("==================================================\n", "36");
echo color("          RT CHOCOS BLOG TEST SUITE               \n", "36");
echo color("==================================================\n", "36");

// --- Test 1: Verify blog.php rendering ---
echo "\n" . color("Running Test Group: Blog Listing Page Rendering", "33") . "\n";
$blogHtml = runIsolatedScript('blog.php');

assertContains('<div id="page-blog"', $blogHtml, "blog.php renders main wrapper");
assertContains('<div class="blog-header-bar"', $blogHtml, "blog.php renders new professional filter-header bar");
assertContains('class="blog-search"', $blogHtml, "blog.php renders search input element");
assertContains('<div class="grid-blog" id="blog-grid"', $blogHtml, "blog.php renders blog listing grid");
assertContains('onerror="this.style.display=\'none\'; if(this.nextElementSibling) this.nextElementSibling.style.display=\'flex\';"', $blogHtml, "blog.php contains image error fallback handlers");

// --- Test 2: Verify router.php clean URL mapping ---
echo "\n" . color("Running Test Group: Clean URL Router Mapping", "33") . "\n";

function simulateRoute($uri) {
    $routerPath = str_replace('\\', '/', __DIR__ . '/router.php');
    
    // Construct isolated execution script
    $bootstrap = "<?php\n";
    $bootstrap .= "error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);\n";
    $bootstrap .= "\$_SERVER['HTTP_HOST'] = 'localhost:8080';\n";
    $bootstrap .= "\$_SERVER['REQUEST_URI'] = '" . addslashes($uri) . "';\n";
    $bootstrap .= "\$_SERVER['REQUEST_METHOD'] = 'GET';\n";
    $bootstrap .= "\$_SERVER['HTTPS'] = 'off';\n";
    $bootstrap .= "\$result = include '" . addslashes($routerPath) . "';\n";
    $bootstrap .= "echo '---RESULT---' . (\$result ? '1' : '0') . '---OUTPUT---';\n";
    $bootstrap .= "?>";

    $descriptors = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ];
    
    $process = proc_open('php', $descriptors, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], $bootstrap);
        fclose($pipes[0]);
        
        $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        $routed = false;
        $html = $output;
        if (preg_match('/---RESULT---([01])---OUTPUT---/', $output, $matches)) {
            $routed = ($matches[1] === '1');
            $html = str_replace($matches[0], '', $output);
        } else {
            // If the script exited early but produced output, it means routing was successful (e.g. 404 page)
            $routed = (trim($output) !== '');
        }
        
        return [
            'routed' => $routed,
            'html' => $html
        ];
    }
    
    return ['routed' => false, 'html' => ''];
}

$routePH = simulateRoute('/blog/cocoa-ph');
assertEquals(true, $routePH['routed'], "Router maps clean URL /blog/cocoa-ph successfully");
assertContains('Why pH is the Most Underrated Factor in Cocoa Powder', $routePH['html'], "Routed article content contains correct title");
assertContains('<div class="toc-box">', $routePH['html'], "Routed article content contains the professional sidebar TOC");
assertContains('id="blog-article-image"', $routePH['html'], "Routed article content contains main image container");
assertContains('class="blog-article-image-fallback"', $routePH['html'], "Routed article content contains image fallback placeholder");

$routeInvalid = simulateRoute('/blog/invalid-article-slug-xyz');
assertEquals(true, $routeInvalid['routed'], "Router handles non-existent slugs by routing them to template");
assertContains('Article or Page Not Found', $routeInvalid['html'], "Non-existent slug renders error 404 page correctly");

$routeStaticFile = simulateRoute('/style.css');
assertEquals(false, $routeStaticFile['routed'], "Router ignores actual files (like /style.css) so they are served normally");

// --- Test 3: Verify API endpoints ---
echo "\n" . color("Running Test Group: API Endpoints Validation", "33") . "\n";

// Test api_blogs.php
$apiBlogsJson = runIsolatedScript('api_blogs.php');
$blogsData = json_decode($apiBlogsJson, true);
assertEquals(JSON_ERROR_NONE, json_last_error(), "api_blogs.php returns valid JSON format");
if (json_last_error() === JSON_ERROR_NONE) {
    $firstBlog = $blogsData[0] ?? null;
    if ($firstBlog) {
        assertEquals(true, isset($firstBlog['title']), "API blog item has 'title' key");
        assertEquals(true, isset($firstBlog['slug']), "API blog item has 'slug' key");
        assertEquals(true, isset($firstBlog['category']), "API blog item has 'category' key");
    }
}

// Test api_comments.php GET without slug
$apiCommentsJson = runIsolatedScript('api_comments.php');
assertContains('Article slug parameter is required.', $apiCommentsJson, "api_comments.php validates missing slug");

// --- Test 4: CSS Layout verification ---
echo "\n" . color("Running Test Group: CSS Styling Layout Rules", "33") . "\n";
$cssContent = file_get_contents(__DIR__ . '/style.css');

assertContains('#page-blog > .section', $cssContent, "CSS contains wide section width rule for blog page (#page-blog > .section)");
assertContains('max-width: 1400px', $cssContent, "CSS sets professional layout width of 1400px for blog section");
assertContains('.grid-blog .card:first-child', $cssContent, "CSS contains featured magazine layout styles for the first card");
assertContains('object-fit: fill', $cssContent, "CSS implements object-fit: fill for standard image card fitting");
assertContains('.blog-card-img img', $cssContent, "CSS defines properties for blog card images");

// --- Final Results ---
echo "\n" . color("==================================================\n", "36");
echo "                  TEST SUMMARY                    \n";
echo color("==================================================\n", "36");
echo "Total Tests Run: {$total}\n";
echo "Passed: " . color("{$passed}", "32") . "\n";
if ($failed > 0) {
    echo "Failed: " . color("{$failed}", "31") . "\n";
    exit(1);
} else {
    echo "Failed: 0\n";
    echo "\n" . color("ALL TESTS PASSED SUCCESSFULLY! ✅", "32") . "\n";
    exit(0);
}
?>
