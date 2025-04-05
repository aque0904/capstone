<?php
include 'database.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = intval($_POST['id']);
    
    try {
        // Update booking status or add verification flag
        $stmt = $conn->prepare("UPDATE booking SET receipt_verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $bookingId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    $conn->close();
}
?>