<?php
require_once 'database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
    if ($data['status'] === 'CANCELLED') {
        $stmt = $pdo->prepare("UPDATE booking SET status = :status, cancellation_reason = :reason WHERE id = :id");
        $stmt->execute([
            ':status' => $data['status'],
            ':reason' => $data['cancellation_reason'],
            ':id' => $data['id']
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE booking SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $data['status'],
            ':id' => $data['id']
        ]);
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>