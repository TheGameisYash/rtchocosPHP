<?php
// admin/contacts.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';
$csrfToken = generate_csrf();

// Handle AJAX mark_read before layout sends HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajax_mark_read') {
    $msgId = (int)($_POST['message_id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';
    
    if (verify_csrf($token) && $msgId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
            $stmt->execute([$msgId]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Handle Form POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token.';
    } else {
        try {
            if ($action === 'delete') {
                $msgId = (int)($_POST['message_id'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
                $stmt->execute([$msgId]);
                $success = 'Message deleted successfully.';
            } elseif ($action === 'toggle_read') {
                $msgId = (int)($_POST['message_id'] ?? 0);
                $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 - is_read WHERE id = ?");
                $stmt->execute([$msgId]);
                $success = 'Message status updated.';
            } elseif ($action === 'bulk_delete') {
                $ids = $_POST['message_ids'] ?? [];
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $success = 'Selected messages deleted successfully.';
                }
            } elseif ($action === 'bulk_read') {
                $ids = $_POST['message_ids'] ?? [];
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $success = 'Selected messages marked as read.';
                }
            }
        } catch (Exception $e) {
            $error = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Filters & Search Setup
$search = trim($_GET['search'] ?? '');
$filter = trim($_GET['filter'] ?? 'all'); // 'all', 'unread', 'read'

$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter === 'unread') {
    $whereClauses[] = "is_read = 0";
} elseif ($filter === 'read') {
    $whereClauses[] = "is_read = 1";
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

try {
    $stmt = $pdo->prepare("SELECT * FROM contacts $whereSql ORDER BY created_at DESC LIMIT 150");
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching messages: " . $e->getMessage());
}

render_admin_header("Contact Inbox", "contacts");
?>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<!-- Bulk Actions Slider Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar" style="background: var(--green-900);">
    <div class="bulk-info"><span id="bulkSelectedCount">0</span> messages selected</div>
    <div class="bulk-actions-btns">
        <form action="contacts.php" method="POST" id="bulkForm" style="display: flex; gap: 12px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" id="bulkActionField" value="">
            <div id="bulkIdsContainer"></div>
            
            <button type="button" class="btn btn-outline btn-sm" onclick="submitBulk('bulk_read')" style="border-color: rgba(255,255,255,0.4); color: white;">Mark Read</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="submitBulk('bulk_delete')">Delete</button>
        </form>
    </div>
</div>

<!-- Filters & Search Header Bar -->
<div class="action-bar" style="margin-bottom: 24px;">
    <div class="action-bar-left">
        <form action="contacts.php" method="GET" class="topbar-search" style="display:block; width: 280px;">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" name="search" placeholder="Search by name, subject, text..." value="<?php echo htmlspecialchars($search); ?>" style="width:100%; border-radius:8px; padding-left:36px; padding-top:10px; padding-bottom:10px;">
            <?php if ($filter !== 'all'): ?><input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>"><?php endif; ?>
        </form>

        <a href="contacts.php?search=<?php echo urlencode($search); ?>" class="filter-chip <?php echo $filter === 'all' ? 'active' : ''; ?>">All Messages</a>
        <a href="contacts.php?filter=unread&search=<?php echo urlencode($search); ?>" class="filter-chip <?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread</a>
        <a href="contacts.php?filter=read&search=<?php echo urlencode($search); ?>" class="filter-chip <?php echo $filter === 'read' ? 'active' : ''; ?>">Read</a>
    </div>
    
    <div class="action-bar-right" style="font-size: 13.5px; color: var(--text-light); font-weight: 500;">
        Showing <?php echo count($messages); ?> items
    </div>
</div>

<!-- Two-Pane Layout container -->
<div class="contacts-pane-layout">
    <!-- Left Scroll Panel -->
    <div class="contacts-list-pane">
        <div class="contacts-search-header" style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:12px; color:var(--text-light); font-weight:600; cursor:pointer;">
                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" style="width:15px; height:15px; cursor:pointer; accent-color:var(--green-900);">
                <span>SELECT ALL</span>
            </label>
            <button type="button" class="btn btn-outline btn-sm" onclick="resetSelections()" style="padding:4px 8px; font-size:11px;">Clear</button>
        </div>
        
        <div class="contacts-list-scroll">
            <?php if (empty($messages)): ?>
                <div style="padding: 40px; text-align: center; color: var(--text-light); font-size: 13.5px;">
                    No messages in this folder.
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): 
                    $excerpt = strlen($msg['message']) > 80 ? substr($msg['message'], 0, 77) . '...' : $msg['message'];
                ?>
                    <div class="contact-list-item <?php echo !$msg['is_read'] ? 'unread' : ''; ?>" 
                         data-id="<?php echo $msg['id']; ?>" 
                         onclick="selectMessage(<?php echo $msg['id']; ?>)">
                        
                        <!-- Checkbox wrapper -->
                        <div style="position: absolute; right: 16px; top: 16px; z-index:10;" onclick="event.stopPropagation();">
                            <input type="checkbox" class="message-select-checkbox" value="<?php echo $msg['id']; ?>" onchange="updateBulkSelection()" style="width:16px; height:16px; cursor:pointer; accent-color:var(--green-900);">
                        </div>
                        
                        <div class="contact-item-meta" style="padding-right: 24px;">
                            <span class="contact-item-name"><?php echo htmlspecialchars($msg['name']); ?></span>
                            <span class="contact-item-date"><?php echo date('M d', strtotime($msg['created_at'])); ?></span>
                        </div>
                        
                        <div class="contact-item-subject" style="padding-right: 24px;">
                            <?php echo htmlspecialchars($msg['subject'] ?: '(No Subject)'); ?>
                        </div>
                        <div class="contact-item-excerpt">
                            <?php echo htmlspecialchars($excerpt); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Detail Panel -->
    <div class="contacts-detail-pane" id="messageDetailPane">
        <!-- Default Empty State -->
        <div class="contact-detail-empty" id="detailEmptyState">
            <svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                <path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
            </svg>
            <p style="font-size: 16px; font-weight: 500; color: var(--text-main);">No Message Selected</p>
            <p style="font-size: 13px; max-width: 280px; margin: 0 auto;">Select an inbox entry from the left-hand pane to review sender details, attachments, and respond.</p>
        </div>

        <!-- Populated Message Content -->
        <div id="detailPopulatedState" style="display:none; flex-direction:column; height:100%; overflow:hidden;">
            <div class="contact-detail-header">
                <div class="contact-detail-subject" id="msgSubject">Subject Placeholder</div>
                
                <div class="contact-sender-info">
                    <div class="contact-sender-details">
                        <span class="sender-name" id="msgName">Name</span>
                        <span class="sender-email" id="msgEmail">Email</span>
                        <span class="sender-phone" id="msgPhone">Phone</span>
                        <span style="font-size: 11px; color: var(--text-light); margin-top:4px;" id="msgDate">Date</span>
                    </div>
                    
                    <div class="contact-detail-actions">
                        <a href="#" class="btn btn-primary btn-sm" id="msgReplyBtn">
                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:14px;height:14px;"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                            Reply
                        </a>
                        
                        <form action="contacts.php" method="POST" id="msgStatusForm" style="display:inline-block;">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="toggle_read">
                            <input type="hidden" name="message_id" id="msgStatusId" value="">
                            <button type="submit" class="btn btn-outline btn-sm">Toggle Unread</button>
                        </form>
                        
                        <form action="contacts.php" method="POST" id="msgDeleteForm" style="display:inline-block;" onsubmit="return confirm('Permanently delete this message?');">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="message_id" id="msgDeleteId" value="">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="contact-detail-body" id="msgBody">
                Message content goes here...
            </div>
        </div>
    </div>
</div>

<!-- Print database contacts array as JS JSON object -->
<script>
    const contactsData = <?php echo json_encode($messages); ?>;
</script>

<script>
    let activeMessageId = null;

    function selectMessage(id) {
        activeMessageId = id;
        
        // Find matching message object
        const msg = contactsData.find(item => item.id == id);
        if (!msg) return;

        // Toggle UI panels
        document.getElementById('detailEmptyState').style.display = 'none';
        document.getElementById('detailPopulatedState').style.display = 'flex';

        // Populate detail panels
        document.getElementById('msgSubject').innerText = msg.subject || '(No Subject)';
        document.getElementById('msgName').innerText = msg.name;
        document.getElementById('msgEmail').innerText = '<' + msg.email + '>';
        document.getElementById('msgEmail').href = 'mailto:' + msg.email;
        document.getElementById('msgPhone').innerText = msg.phone ? 'Phone: ' + msg.phone : '';
        document.getElementById('msgDate').innerText = 'Received: ' + new Date(msg.created_at).toLocaleString();
        document.getElementById('msgBody').innerText = msg.message;

        // Reply mailto configuration
        const replySub = encodeURIComponent('Re: ' + (msg.subject || 'RT Chocos Contact'));
        document.getElementById('msgReplyBtn').href = `mailto:${msg.email}?subject=${replySub}`;

        // ID forms mapping
        document.getElementById('msgStatusId').value = msg.id;
        document.getElementById('msgDeleteId').value = msg.id;

        // Visual Active focus styling on list
        const items = document.querySelectorAll('.contact-list-item');
        items.forEach(el => {
            el.classList.remove('active');
            if (el.dataset.id == id) {
                el.classList.add('active');
            }
        });

        // Trigger AJAX mark-as-read background update if message was unread
        const listItem = document.querySelector(`.contact-list-item[data-id="${id}"]`);
        if (listItem && listItem.classList.contains('unread')) {
            markAsReadAJAX(id, listItem);
        }
    }

    function markAsReadAJAX(id, listItem) {
        const fd = new FormData();
        fd.append('action', 'ajax_mark_read');
        fd.append('message_id', id);
        fd.append('csrf_token', '<?php echo $csrfToken; ?>');

        fetch('contacts.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                listItem.classList.remove('unread');
                
                // Dynamically decrement notification counts in header and sidebar if present
                const sideMsgUnreadBadge = document.querySelector('nav.sidebar-nav a[href="contacts.php"] .status-badge');
                if (sideMsgUnreadBadge) {
                    let count = parseInt(sideMsgUnreadBadge.innerText) - 1;
                    if (count > 0) {
                        sideMsgUnreadBadge.innerText = count;
                    } else {
                        sideMsgUnreadBadge.parentNode.removeChild(sideMsgUnreadBadge);
                    }
                }
                
                const bellBadge = document.querySelector('#notifBell .badge-dot');
                if (bellBadge && !document.querySelector('nav.sidebar-nav a[href="contacts.php"] .status-badge')) {
                    bellBadge.parentNode.removeChild(bellBadge);
                }
            }
        })
        .catch(err => console.error('Error marking as read:', err));
    }

    // Bulk action triggers
    function resetSelections() {
        const checkboxes = document.querySelectorAll('.message-select-checkbox');
        checkboxes.forEach(c => c.checked = false);
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) selectAll.checked = false;
        updateBulkSelection();
    }

    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.message-select-checkbox');
        checkboxes.forEach(c => c.checked = selectAllCheckbox.checked);
        updateBulkSelection();
    }

    function updateBulkSelection() {
        const checkboxes = document.querySelectorAll('.message-select-checkbox:checked');
        const bar = document.getElementById('bulkActionsBar');
        const countSpan = document.getElementById('bulkSelectedCount');
        
        countSpan.innerText = checkboxes.length;
        
        if (checkboxes.length > 0) {
            bar.classList.add('show');
        } else {
            bar.classList.remove('show');
        }
    }

    function submitBulk(action) {
        const confirmMsg = action === 'bulk_delete' 
            ? 'Are you sure you want to delete all selected messages permanently?'
            : 'Mark all selected messages as read?';
            
        if (!confirm(confirmMsg)) return;

        const checkedBoxes = document.querySelectorAll('.message-select-checkbox:checked');
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = ''; // Clear prior entries
        
        checkedBoxes.forEach(box => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'message_ids[]';
            input.value = box.value;
            container.appendChild(input);
        });

        document.getElementById('bulkActionField').value = action;
        document.getElementById('bulkForm').submit();
    }

    // Auto load message if ID passed in url
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const autoId = params.get('id');
        if (autoId) {
            selectMessage(autoId);
        }
    });
</script>

<?php
render_admin_footer();
?>
