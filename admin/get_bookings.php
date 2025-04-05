<?php
require_once 'database.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM booking ORDER BY checkInDate DESC");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($bookings);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>