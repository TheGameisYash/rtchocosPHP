<?php
/**
 * includes/faq-block.php — Reusable FAQ Accordion Block
 * 
 * Usage: Set $faqCategory and optionally $faqLimit before including this file.
 *   $faqCategory = 'shop';  // general, workshops, shop, shipping, courses
 *   $faqLimit = 5;
 *   include 'includes/faq-block.php';
 * 
 * This will render an accordion of FAQs and populate $faqSchemaItems 
 * for automatic FAQPage JSON-LD schema generation in header.php.
 */

if (empty($faqCategory)) return;
if (empty($faqLimit)) $faqLimit = 5;

// Initialize global schema array if not set
if (!isset($GLOBALS['faqSchemaItems'])) {
    $GLOBALS['faqSchemaItems'] = [];
}

try {
    require_once __DIR__ . '/db.php';
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT id, question, answer FROM faqs WHERE category = ? AND is_active = 1 ORDER BY display_order ASC, id ASC LIMIT ?");
    $stmt->execute([$faqCategory, (int)$faqLimit]);
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $faqs = [];
}

if (!empty($faqs)):
    // Generate a unique block ID to avoid conflicts when multiple blocks exist on one page
    $blockId = 'faq-block-' . $faqCategory . '-' . mt_rand(1000, 9999);
    
    $categoryLabels = [
        'general' => 'General Questions',
        'workshops' => 'Workshop FAQs',
        'shop' => 'Shopping FAQs',
        'shipping' => 'Shipping & Delivery',
        'courses' => 'Course FAQs'
    ];
    $label = $categoryLabels[$faqCategory] ?? 'Frequently Asked Questions';
?>

<div class="section" style="padding-top: 32px; padding-bottom: 32px;">
  <div class="section-label"><?php echo htmlspecialchars($label); ?></div>
  <h3 style="font-family:'Cormorant Garamond',serif; font-size:28px; font-weight:700; color:var(--brown); margin-bottom:8px;">Frequently Asked Questions</h3>
  <div class="divider" style="margin-bottom:24px;"></div>
  
  <div id="<?php echo $blockId; ?>" style="max-width:800px; margin:0 auto;">
    <?php foreach ($faqs as $faq): 
        $faqItemId = $blockId . '-item-' . $faq['id'];
        // Push to global schema array
        $GLOBALS['faqSchemaItems'][] = [
            'question' => $faq['question'],
            'answer' => strip_tags($faq['answer'])
        ];
    ?>
    <div style="background:var(--cream); border-radius:16px; padding:20px 24px; margin-bottom:12px; box-shadow:0 2px 8px rgba(59,42,34,0.06); cursor:pointer; transition:box-shadow 0.2s ease;" 
         onclick="toggleFaqItem('<?php echo $faqItemId; ?>', this)" 
         role="button" 
         aria-expanded="false" 
         aria-controls="<?php echo $faqItemId; ?>-answer">
      <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <h4 style="font-family:'Jost',sans-serif; font-size:15px; font-weight:600; color:var(--brown); margin:0; line-height:1.5;">
          <?php echo htmlspecialchars($faq['question']); ?>
        </h4>
        <span id="<?php echo $faqItemId; ?>-icon" style="font-size:20px; color:var(--brown-light); transition:transform 0.3s ease; flex-shrink:0;">+</span>
      </div>
      <div id="<?php echo $faqItemId; ?>-answer" style="display:none; margin-top:12px; padding-top:12px; border-top:1px solid rgba(59,42,34,0.08);">
        <p style="font-family:'Jost',sans-serif; font-size:14px; line-height:1.7; color:var(--brown-light); font-weight:300; margin:0;">
          <?php echo $faq['answer']; ?>
        </p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
function toggleFaqItem(id, el) {
  var answer = document.getElementById(id + '-answer');
  var icon = document.getElementById(id + '-icon');
  if (!answer) return;
  var isOpen = answer.style.display !== 'none';
  answer.style.display = isOpen ? 'none' : 'block';
  icon.textContent = isOpen ? '+' : '−';
  icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(45deg)';
  el.setAttribute('aria-expanded', !isOpen);
}
</script>

<?php endif; ?>
