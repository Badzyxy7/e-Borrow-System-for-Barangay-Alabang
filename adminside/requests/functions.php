<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Use absolute paths for PHPMailer - works from anywhere
$base_dir = dirname(dirname(__DIR__)); // Go to root directory
require_once $base_dir . '/src/Exception.php';
require_once $base_dir . '/src/PHPMailer.php';
require_once $base_dir . '/src/SMTP.php';

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'havenblaze3000@gmail.com';
        $mail->Password = 'sqmzqlsftwxcmzfz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('havenblaze3000@gmail.com', 'eBorrow System for Barangay Alabang');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

function getRequestCounts($conn) {
    $counts = [];
    $counts['pending'] = $conn->query("SELECT COUNT(*) as c FROM borrow_requests WHERE status='pending'")->fetch_assoc()['c'];
    $counts['approved'] = $conn->query("SELECT COUNT(*) as c FROM borrow_requests WHERE status='approved'")->fetch_assoc()['c'];
    $counts['delivered'] = $conn->query("SELECT COUNT(*) as c FROM borrow_requests br LEFT JOIN borrow_logs bl ON br.id=bl.request_id WHERE br.status='delivered' AND (bl.return_requested IS NULL OR bl.return_requested=0)")->fetch_assoc()['c'];
    $counts['return_requests'] = $conn->query("SELECT COUNT(*) as c FROM borrow_logs WHERE return_requested=1 AND (return_approved IS NULL OR return_approved=0)")->fetch_assoc()['c'];
    $counts['returned'] = $conn->query("SELECT COUNT(*) as c FROM borrow_requests br JOIN borrow_logs bl ON br.id=bl.request_id WHERE br.status='returned' AND (bl.is_damaged IS NULL OR bl.is_damaged=0)")->fetch_assoc()['c'];
    $counts['damaged'] = $conn->query("SELECT COUNT(*) as c FROM borrow_logs WHERE is_damaged=1")->fetch_assoc()['c'];
    return $counts;
}

function getRequestDetails($conn, $id) {
    return $conn->query("SELECT br.*, 
        e.name AS equipment_name, 
        e.description AS equipment_desc, 
        e.image AS equipment_photo,
        u.name AS user_name, 
        u.email AS user_email, 
        CONCAT_WS(', ', u.street, u.landmark, u.barangay) AS address,
        
        approved_admin.name AS approved_by_name,
        rejected_admin.name AS rejected_by_name,
        delivered_admin.name AS delivered_by_name,
        returned_admin.name AS returned_by_name,
        damaged_admin.name AS damaged_by_name
        
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        LEFT JOIN users approved_admin ON br.approved_by = approved_admin.id
        LEFT JOIN users rejected_admin ON br.rejected_by = rejected_admin.id
        LEFT JOIN users delivered_admin ON br.delivered_by = delivered_admin.id
        LEFT JOIN users returned_admin ON br.returned_by = returned_admin.id
        LEFT JOIN users damaged_admin ON br.marked_damaged_by = damaged_admin.id
        WHERE br.id=$id")->fetch_assoc();
}


function uploadPhoto($file, $type, $id) {
    $dirs = [
        'delivery' => '../photos/delivery_proofs/',
        'return' => '../photos/return_proofs/',
        'payment' => '../photos/payment_proofs/'
    ];
    $dir = $dirs[$type] ?? '../photos/';
    if (!file_exists($dir)) mkdir($dir, 0777, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "{$type}_{$id}_" . time() . ".{$ext}";
    $path = $dir . $filename;
    move_uploaded_file($file['tmp_name'], $path);
    return $path;
}

function handleRequestAction($conn, $post) {
    $id = intval($post['id']);
    $action = $post['action'];
    $req = getRequestDetails($conn, $id);
    if (!$req) return;
    
    // Get the current admin/staff user ID from session
    $admin_id = $_SESSION['user_id'];
    $timestamp = date('Y-m-d H:i:s');

    switch ($action) {
        case 'approve':
            $conn->query("UPDATE borrow_requests SET 
                status='approved',
                approved_by=$admin_id,
                approved_at='$timestamp'
                WHERE id=$id");
            $conn->query("UPDATE equipment SET status='unavailable' WHERE id={$req['equipment_id']}");
            $body = emailTemplate('approved', $req);
            sendEmail($req['user_email'], "🎉 Borrow Request Approved", $body);
            break;

        case 'decline':
            $reason = $conn->real_escape_string($post['reason']);
            $conn->query("UPDATE borrow_requests SET 
                status='declined', 
                rejection_reason='$reason',
                rejected_by=$admin_id,
                rejected_at='$timestamp'
                WHERE id=$id");
            $body = emailTemplate('declined', $req, $reason);
            sendEmail($req['user_email'], "Request Declined", $body);
            break;

        case 'delivered':
            $photo = isset($_FILES['delivery_photo']) && $_FILES['delivery_photo']['error'] == 0
                ? uploadPhoto($_FILES['delivery_photo'], 'delivery', $id) : null;
            $conn->query("UPDATE borrow_requests SET 
                status='delivered', 
                delivery_photo='$photo',
                delivered_by=$admin_id,
                delivered_at='$timestamp'
                WHERE id=$id");
            $conn->query("INSERT INTO borrow_logs (request_id, user_id, equipment_id, qty, borrow_date, actual_pickup_date, expected_return_date, log_created_at)
                VALUES ({$req['id']}, {$req['user_id']}, {$req['equipment_id']}, {$req['qty']}, '{$req['borrow_date']}', NOW(), '{$req['return_date']}', NOW())");
            $body = emailTemplate('delivered', $req);
            sendEmail($req['user_email'], "Equipment Delivered Successfully", $body);
            break;

        case 'approve_return':
            $conn->query("UPDATE borrow_logs SET return_approved=1 WHERE request_id=$id");
            $body = emailTemplate('return_approved', $req);
            sendEmail($req['user_email'], "Return Request Approved - Prepare for Pickup", $body);
            break;

        case 'returned':
            $is_damaged = isset($post['is_damaged']) ? 1 : 0;
            $damage_fee = floatval($post['damage_fee'] ?? 0);
            $damage_notes = $conn->real_escape_string($post['damage_notes'] ?? '');
            $return_photo = isset($_FILES['return_photo']) && $_FILES['return_photo']['error'] == 0
                ? uploadPhoto($_FILES['return_photo'], 'return', $id) : null;
            $payment_photo = ($is_damaged && isset($_FILES['payment_photo']) && $_FILES['payment_photo']['error'] == 0)
                ? uploadPhoto($_FILES['payment_photo'], 'payment', $id) : null;

            // Track who marked it as damaged (if applicable)
            $damaged_tracking = $is_damaged ? ", marked_damaged_by=$admin_id, marked_damaged_at='$timestamp'" : "";
            
            $conn->query("UPDATE borrow_requests SET 
                status='returned',
                returned_by=$admin_id,
                returned_at='$timestamp'
                $damaged_tracking
                WHERE id=$id");
            
            $conn->query("UPDATE borrow_logs SET actual_return_date=NOW(), staff_checked_condition='checked',
                is_damaged=$is_damaged, damage_fee=$damage_fee, damage_notes='$damage_notes',
                return_photo='$return_photo', payment_photo='$payment_photo' WHERE request_id=$id");
            $conn->query("UPDATE equipment SET status='available' WHERE id={$req['equipment_id']}");
            
            $template = $is_damaged ? 'returned_damaged' : 'returned';
            $body = emailTemplate($template, $req, $damage_notes, $damage_fee);
            sendEmail($req['user_email'], "Equipment Return Confirmed", $body);
            break;
    }
    header("Location: requests_tab.php?tab=" . getCurrentTab($action));
    exit();
}

function getCurrentTab($action) {
    $map = ['approve' => 'approved', 'decline' => 'pending', 'delivered' => 'delivered',
            'approve_return' => 'return_requests', 'returned' => 'returned'];
    return $map[$action] ?? 'pending';
}

function emailTemplate($type, $req, $extra = '', $fee = 0) {
    $header = "<html><body style='font-family:Arial,sans-serif;'><div style='max-width:600px;margin:0 auto;padding:20px;background:#f5f5f5;'><div style='background:white;padding:30px;border-radius:10px;'>";
    $footer = "<p style='color:#666;font-size:14px;margin-top:30px;'>Thank you for using eBorrow System!</p></div></div></body></html>";
    
    // Get admin names from the request data
    $approved_by = !empty($req['approved_by_name']) ? $req['approved_by_name'] : 'Staff';
    $delivered_by = !empty($req['delivered_by_name']) ? $req['delivered_by_name'] : 'Staff';
    $returned_by = !empty($req['returned_by_name']) ? $req['returned_by_name'] : 'Staff';
    
    $templates = [
        'approved' => "<h2 style='color:#16a34a;'>✅ Request Approved</h2>
            <p>Dear <strong>{$req['user_name']}</strong>,</p><p>Your borrow request has been approved!</p>
            <div style='background:#f0fdf4;padding:20px;border-radius:8px;margin:20px 0;'>
            <p><strong>Equipment:</strong> {$req['equipment_name']}</p>
            <p><strong>Quantity:</strong> {$req['qty']}</p>
            <p><strong>Period:</strong> " . date('M d, Y', strtotime($req['borrow_date'])) . " to " . date('M d, Y', strtotime($req['return_date'])) . "</p>
            <p><strong>Address:</strong> {$req['address']}</p></div>
            <div style='background:#dbeafe;padding:15px;border-radius:8px;border-left:4px solid #1e3a8a;'>
            <p style='margin:0;color:#1e40af;'><strong>📦 Delivery:</strong> Between 9:00 AM - 5:00 PM</p>
            <p style='margin:5px 0 0 0;color:#1e40af;'><strong>Delivered by:</strong> {$approved_by}</p></div>",

        'declined' => "<h2 style='color:#dc2626;'>❌ Request Declined</h2>
            <p>Dear <strong>{$req['user_name']}</strong>,</p><p>Your request has been declined.</p>
            <div style='background:#fef2f2;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #dc2626;'>
            <p><strong>Equipment:</strong> {$req['equipment_name']}</p>
            <p><strong>Reason:</strong> {$extra}</p></div>",

        'delivered' => "<h2 style='color:#2563eb;'>📦 Equipment Delivered</h2>
            <p>Dear <strong>{$req['user_name']}</strong>,</p><p>Your equipment has been delivered!</p>
            <div style='background:#eff6ff;padding:20px;border-radius:8px;margin:20px 0;'>
            <p><strong>Equipment:</strong> {$req['equipment_name']}</p>
            <p><strong>Delivered by:</strong> {$delivered_by}</p>
            <p><strong>Return Date:</strong> " . date('M d, Y', strtotime($req['return_date'])) . "</p></div>
            <div style='background:#fef3c7;padding:15px;border-radius:8px;border-left:4px solid #f59e0b;'>
            <p style='margin:0;color:#92400e;'><strong>⚠️</strong> Please return on time to avoid penalties.</p></div>",

        'return_approved' => "<h2 style='color:#7c3aed;'>✅ Return Request Approved</h2>
            <p>Dear <strong>{$req['user_name']}</strong>,</p><p>Your return request is approved. Please prepare the item.</p>
            <div style='background:#dbeafe;padding:15px;border-radius:8px;border-left:4px solid #1e3a8a;'>
            <p style='margin:0;color:#1e40af;'><strong>🚚 Pickup:</strong> Staff will arrive shortly to collect the item</p>
            <p style='margin:5px 0 0 0;color:#1e40af;'><strong>Pickup by:</strong> {$returned_by}</p></div>
            <div style='background:#fef3c7;padding:15px;border-radius:8px;border-left:4px solid #f59e0b;margin-top:15px;'>
            <p style='margin:0;color:#92400e;'><strong>📍 Pickup Address:</strong> {$req['address']}</p></div>",

        'returned' => "<h2 style='color:#16a34a;'>✅ Equipment Returned</h2>
            <p>Dear <strong>{$req['user_name']}</strong>,</p><p>Your equipment has been returned successfully.</p>
            <div style='background:#f0fdf4;padding:20px;border-radius:8px;margin:20px 0;'>
            <p><strong>Equipment:</strong> {$req['equipment_name']}</p>
            <p><strong>Received by:</strong> {$returned_by}</p>
            <p style='color:#16a34a;'><strong>Status:</strong> No damage ✓</p></div>",

        'returned_damaged' => "<h2 style='color:#dc2626;'>⚠️ Equipment Returned - Damage Fee</h2>
            <p>Dear <strong>{$req['user_name']}</strong>,</p><p>Your equipment has been returned with damage.</p>
            <div style='background:#fef2f2;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #dc2626;'>
            <p><strong>Equipment:</strong> {$req['equipment_name']}</p>
            <p><strong>Received by:</strong> {$returned_by}</p>
            <p><strong>Damage Fee:</strong> ₱" . number_format($fee, 2) . "</p>
            <p><strong>Reason:</strong> {$extra}</p></div>"
    ];
    
    return $header . ($templates[$type] ?? '') . $footer;
}

function renderBorrowerCard($row) {
    ?>
    <div class="bg-gray-50 rounded-xl p-4 mb-4">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i data-feather="user" class="w-4 h-4"></i> Borrower Details
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <p><span class="font-medium">Name:</span> <?= htmlspecialchars($row['user_name']) ?></p>
            <p><span class="font-medium">Email:</span> <?= htmlspecialchars($row['user_email']) ?></p>
            <p><span class="font-medium">Phone:</span> <?= htmlspecialchars($row['phone'] ?? 'N/A') ?></p>
            <p><span class="font-medium">Address:</span> <?= htmlspecialchars($row['address']) ?></p>
        </div>
        <?php if (!empty($row['valid_id'])): ?>
        <div class="mt-3">
            <button onclick="viewPhoto('<?= htmlspecialchars($row['valid_id']) ?>', 'Valid ID')" 
                    class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                <i data-feather="id-card" class="w-4 h-4"></i> View Valid ID
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

function renderEquipmentCard($row) {
    ?>
    <div class="bg-blue-50 rounded-xl p-4 mb-4">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i data-feather="package" class="w-4 h-4"></i> Equipment Details
        </h4>
        <div class="flex gap-4">
            <?php if (!empty($row['equipment_photo'])): ?>
                <?php 
                // Since we're in staffside folder, need to go up one level to access photos
                $imagePath = '../photos/' . basename($row['equipment_photo']);
                ?>
                <img src="<?= htmlspecialchars($imagePath) ?>" 
                     alt="Equipment" 
                     class="w-20 h-20 object-cover rounded-lg cursor-pointer"
                     onerror="this.src='../photos/placeholder.png'; this.onerror=null;"
                     onclick="viewPhoto('<?= htmlspecialchars($imagePath) ?>', '<?= htmlspecialchars($row['equipment_name']) ?>')">
            <?php endif; ?>
            <div class="text-sm">
                <p class="font-semibold text-lg"><?= htmlspecialchars($row['equipment_name']) ?></p>
                <p><span class="font-medium">Quantity:</span> <?= $row['qty'] ?></p>
                <p><span class="font-medium">Period:</span> <?= date('M d', strtotime($row['borrow_date'])) ?> - <?= date('M d, Y', strtotime($row['return_date'])) ?></p>
                <?php if (!empty($row['equipment_desc'])): ?>
                <p class="text-gray-600 mt-1"><?= htmlspecialchars($row['equipment_desc']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

// Add this JavaScript function for button loading states
function renderLoadingScript() {
    ?>
    <script>
    // Global function to handle button loading state
    function setButtonLoading(button, isLoading) {
        if (isLoading) {
            // Store original content
            button.dataset.originalContent = button.innerHTML;
            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-not-allowed');
            
            // Add loading spinner
            const spinnerHTML = `
                <svg class="animate-spin inline-block w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;
            button.innerHTML = spinnerHTML;
        } else {
            button.disabled = false;
            button.classList.remove('opacity-60', 'cursor-not-allowed');
            if (button.dataset.originalContent) {
                button.innerHTML = button.dataset.originalContent;
            }
        }
    }

    // Handle form submissions with loading states
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Find submit button that was clicked
        const submitButton = form.querySelector('button[type="submit"]:focus, input[type="submit"]:focus') 
                          || form.querySelector('button[type="submit"], input[type="submit"]');
        
        if (submitButton) {
            setButtonLoading(submitButton, true);
        }
    });

    // Handle button clicks with data-action attribute
    document.addEventListener('click', function(e) {
        const button = e.target.closest('button[data-action]');
        if (button && !button.disabled) {
            setButtonLoading(button, true);
        }
    });

    // Optional: Auto-reset loading state if page doesn't redirect (for AJAX calls)
    window.addEventListener('pageshow', function(event) {
        // Reset all loading buttons when navigating back
        document.querySelectorAll('button[disabled]').forEach(btn => {
            if (btn.dataset.originalContent) {
                setButtonLoading(btn, false);
            }
        });
    });
    </script>
    
    <style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    </style>
    <?php
}
// Function to render audit trail information
function renderAuditTrail($row) {
    $audit_items = [];
    
    if (!empty($row['approved_by_name']) && !empty($row['approved_at'])) {
        $audit_items[] = [
            'icon' => 'check-circle',
            'color' => 'green',
            'label' => 'Approved by',
            'name' => $row['approved_by_name'],
            'date' => date('M d, Y g:i A', strtotime($row['approved_at']))
        ];
    }
    
    if (!empty($row['rejected_by_name']) && !empty($row['rejected_at'])) {
        $audit_items[] = [
            'icon' => 'x-circle',
            'color' => 'red',
            'label' => 'Rejected by',
            'name' => $row['rejected_by_name'],
            'date' => date('M d, Y g:i A', strtotime($row['rejected_at']))
        ];
    }
    
    if (!empty($row['delivered_by_name']) && !empty($row['delivered_at'])) {
        $audit_items[] = [
            'icon' => 'truck',
            'color' => 'blue',
            'label' => 'Delivered by',
            'name' => $row['delivered_by_name'],
            'date' => date('M d, Y g:i A', strtotime($row['delivered_at']))
        ];
    }
    
    if (!empty($row['returned_by_name']) && !empty($row['returned_at'])) {
        $audit_items[] = [
            'icon' => 'corner-down-left',
            'color' => 'purple',
            'label' => 'Returned by',
            'name' => $row['returned_by_name'],
            'date' => date('M d, Y g:i A', strtotime($row['returned_at']))
        ];
    }
    
    if (!empty($row['damaged_by_name']) && !empty($row['marked_damaged_at'])) {
        $audit_items[] = [
            'icon' => 'alert-triangle',
            'color' => 'red',
            'label' => 'Marked Damaged by',
            'name' => $row['damaged_by_name'],
            'date' => date('M d, Y g:i A', strtotime($row['marked_damaged_at']))
        ];
    }
    
    if (empty($audit_items)) {
        return '';
    }
    
    ?>
    <div class="bg-indigo-50 rounded-xl p-4 mb-4 border-l-4 border-indigo-500">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i data-feather="activity" class="w-4 h-4 text-indigo-600"></i> Audit Trail
        </h4>
        <div class="space-y-2">
            <?php foreach ($audit_items as $item): ?>
            <div class="flex items-start gap-2 text-sm">
                <i data-feather="<?= $item['icon'] ?>" class="w-4 h-4 text-<?= $item['color'] ?>-600 mt-0.5"></i>
                <div>
                    <span class="font-medium text-gray-700"><?= $item['label'] ?>:</span>
                    <span class="text-gray-900"><?= htmlspecialchars($item['name']) ?></span>
                    <div class="text-xs text-gray-500"><?= $item['date'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

// Function to get current admin name for modal preview
function getCurrentAdminName($conn) {
    $admin_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT name FROM users WHERE id=$admin_id");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['name'];
    }
    return 'Unknown Admin';
}
?>