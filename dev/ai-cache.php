<?php
// dev/ai-cache.php - AI Cacao Insights & Workshop Facts Cache Studio
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $errorMsg = 'Security token invalid.';
    } else {
        try {
            if ($action === 'add_insight') {
                $text = trim($_POST['insight_text'] ?? '');
                $type = $_POST['fact_type'] ?? 'insight';
                if (!empty($text)) {
                    $table = ($type === 'class_fact') ? 'ai_class_facts' : 'ai_insights';
                    $col = ($type === 'class_fact') ? 'fact_text' : 'insight_text';
                    $stmt = $pdo->prepare("INSERT INTO `{$table}` (`{$col}`) VALUES (?)");
                    $stmt->execute([$text]);
                    $successMsg = 'New AI insight saved into server-side cache!';
                }
            } elseif ($action === 'delete_item') {
                $itemId = (int)($_POST['item_id'] ?? 0);
                $type = $_POST['fact_type'] ?? 'insight';
                $table = ($type === 'class_fact') ? 'ai_class_facts' : 'ai_insights';
                $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
                $stmt->execute([$itemId]);
                $successMsg = 'AI cache entry removed.';
            }
        } catch (Exception $e) {
            $errorMsg = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch insights and class facts
$insights = [];
$classFacts = [];
try {
    $insights = $pdo->query("SELECT * FROM ai_insights ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $classFacts = $pdo->query("SELECT * FROM ai_class_facts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // non-fatal
}

$csrfToken = generate_csrf();
render_dev_header("AI Insights Studio", "ai-cache");
?>

<?php if (!empty($successMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<div class="dev-page-header">
    <div class="dev-page-title">
        <h2>AI Insights & Science Facts Cache Studio</h2>
        <p>Manage server-side pre-cached cocoa science insights, bean-to-bar knowledge, and workshop curriculum facts</p>
    </div>
</div>

<!-- Add New Fact Form -->
<div class="dev-card" style="margin-bottom: 24px;">
    <div class="dev-card-header">
        <h3>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
            <span>Add Knowledge Entry into AI Cache</span>
        </h3>
    </div>

    <form action="ai-cache.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="action" value="add_insight">

        <div class="dev-grid-2" style="margin-bottom: 14px;">
            <div class="dev-form-group" style="margin-bottom: 0;">
                <label for="fact_type">Knowledge Channel</label>
                <select id="fact_type" name="fact_type" class="dev-select">
                    <option value="insight">General Cacao Science Insight (ai_insights)</option>
                    <option value="class_fact">Chocolate Workshop & Academy Fact (ai_class_facts)</option>
                </select>
            </div>

            <div class="dev-form-group" style="margin-bottom: 0;">
                <label for="insight_text">Fact / Scientific Insight</label>
                <input type="text" id="insight_text" name="insight_text" class="dev-input" required placeholder="e.g. Criollo cacao represents less than 5% of world production...">
            </div>
        </div>

        <button type="submit" class="dev-btn dev-btn-primary dev-btn-sm">
            <span>Commit Entry to Server Cache</span>
        </button>
    </form>
</div>

<div class="dev-grid-2">
    <!-- Cacao Insights List -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <span>Cacao Science Insights (<?php echo count($insights); ?>)</span>
            </h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($insights as $item): ?>
                <div style="background: rgba(7, 10, 16, 0.6); border: 1px solid var(--dev-border-subtle); border-radius: 8px; padding: 12px; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                    <div style="font-size: 13px; color: #f1f5f9; line-height: 1.5;">
                        <?php echo htmlspecialchars($item['insight_text']); ?>
                    </div>
                    <form action="ai-cache.php" method="POST" style="margin: 0; flex-shrink: 0;">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="fact_type" value="insight">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        <button type="submit" class="dev-btn dev-btn-danger dev-btn-sm" style="padding: 2px 7px;">&times;</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Class Facts List -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Workshop Facts (<?php echo count($classFacts); ?>)</span>
            </h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($classFacts as $item): ?>
                <div style="background: rgba(7, 10, 16, 0.6); border: 1px solid var(--dev-border-subtle); border-radius: 8px; padding: 12px; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                    <div style="font-size: 13px; color: #f1f5f9; line-height: 1.5;">
                        <?php echo htmlspecialchars($item['fact_text']); ?>
                    </div>
                    <form action="ai-cache.php" method="POST" style="margin: 0; flex-shrink: 0;">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="fact_type" value="class_fact">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        <button type="submit" class="dev-btn dev-btn-danger dev-btn-sm" style="padding: 2px 7px;">&times;</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
render_dev_footer();
?>
