<?php
// Run this script from CLI (cron) or web to create notifications for warranties
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/error_handler.php';

// Ensure Notifications table exists
$create = "CREATE TABLE IF NOT EXISTS Notifications (
    notifications_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    message TEXT,
    warranty_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);";
$conn->query($create);

// Find warranties expiring within next 30 days
$query = "SELECT w.warranty_id, w.warranty_duration, w.purchase_date, u.user_id, u.email, u.name
    FROM Warranty w
    JOIN User u ON u.warranty_id = w.warranty_id
    WHERE DATE_ADD(w.purchase_date, INTERVAL w.warranty_duration MONTH) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $expiry = date('Y-m-d', strtotime($row['purchase_date'] . ' +' . $row['warranty_duration'] . ' months'));
        $user_id = $row['user_id'];
        $warranty_id = $row['warranty_id'];
        $message = "Your warranty (ID: $warranty_id) will expire on $expiry. Please take action if needed.";

        // Avoid duplicate notifications in last 30 days
        $chk = $conn->prepare('SELECT COUNT(*) FROM Notifications WHERE user_id = ? AND warranty_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        if ($chk) {
            $chk->bind_param('ii', $user_id, $warranty_id);
            $chk->execute();
            $chk->bind_result($count);
            $chk->fetch();
            $chk->close();
            if (intval($count) === 0) {
                $ins = $conn->prepare('INSERT INTO Notifications (user_id, message, warranty_id) VALUES (?, ?, ?)');
                if ($ins) {
                    $ins->bind_param('isi', $user_id, $message, $warranty_id);
                    $ins->execute();
                    $ins->close();
                }
            }
        }
    }
}

// Optionally, notify about already expired warranties (graceful message)
$expired_q = "SELECT w.warranty_id, w.purchase_date, w.warranty_duration, u.user_id FROM Warranty w JOIN User u ON u.warranty_id=w.warranty_id WHERE DATE_ADD(w.purchase_date, INTERVAL w.warranty_duration MONTH) < CURDATE()";
$er = $conn->query($expired_q);
if ($er) {
    while ($r = $er->fetch_assoc()) {
        $expiry = date('Y-m-d', strtotime($r['purchase_date'] . ' +' . $r['warranty_duration'] . ' months'));
        $user_id = $r['user_id'];
        $warranty_id = $r['warranty_id'];
        $message = "Your warranty (ID: $warranty_id) expired on $expiry.";

        $chk = $conn->prepare('SELECT COUNT(*) FROM Notifications WHERE user_id = ? AND warranty_id = ? AND message LIKE ?');
        if ($chk) {
            $like = '%expired on%';
            $chk->bind_param('iis', $user_id, $warranty_id, $like);
            $chk->execute();
            $chk->bind_result($count);
            $chk->fetch();
            $chk->close();
            if (intval($count) === 0) {
                $ins = $conn->prepare('INSERT INTO Notifications (user_id, message, warranty_id) VALUES (?, ?, ?)');
                if ($ins) {
                    $ins->bind_param('isi', $user_id, $message, $warranty_id);
                    $ins->execute();
                    $ins->close();
                }
            }
        }
    }
}

// If run via web, show a basic summary
if (php_sapi_name() !== 'cli') {
    echo "Warranty check completed.";
}
