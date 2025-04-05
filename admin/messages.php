<?php
// Include your database connection and authentication
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'database.php';
requireAuth();
date_default_timezone_set('Asia/Manila');

// Initialize variables
$error = '';
$success = '';
$messages = [];
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Handle message status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $messageId = intval($_POST['id']);
    $newStatus = $conn->real_escape_string(trim($_POST['status']));
    
    try {
        $stmt = $conn->prepare("UPDATE messages SET status = ? WHERE id = ?");
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("si", $newStatus, $messageId);
        
        if ($stmt->execute()) {
            $success = "Message status updated successfully!";
        } else {
            $error = "Error updating message: " . $stmt->error;
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $messageId = intval($_POST['id']);
    
    try {
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $messageId);
        
        if ($stmt->execute()) {
            $success = "Message deleted successfully!";
        } else {
            $error = "Error deleting message: " . $stmt->error;
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get messages from database with search and filter
try {
    $query = "SELECT * FROM messages WHERE 1=1";

    if (!empty($searchQuery)) {
        $query .= " AND (name LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                      OR email LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                      OR subject LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                      OR message LIKE '%" . $conn->real_escape_string($searchQuery) . "%')";
    }

    if ($statusFilter !== 'all') {
        $query .= " AND status = '" . $conn->real_escape_string($statusFilter) . "'";
    }

    $query .= " ORDER BY created_at DESC";

    $result = $conn->query($query);
    if ($result === false) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $messages = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching messages: " . $e->getMessage();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa Baleva - Messages</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #008083;
            --secondary-color: #f8f8f8;
            --accent-color: #ff6b6b;
            --text-color: #333;
            --light-text: #777;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: var(--text-color);
        }
        
        header {
            background-color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo img {
            height: 50px;
        }
        
        nav {
            display: flex;
            gap: 25px;
        }
        
        nav a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        nav a:hover {
            color: var(--primary-color);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .message-detail {
            display: flex;
            margin-bottom: 15px;
        }
        
        .message-label {
            font-weight: 600;
            width: 120px;
            color: var(--primary-color);
        }
        
        .message-value {
            flex: 1;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .btn-reply {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-reply:hover {
            background-color: #138496;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
        }
        
        .btn-mark-read {
            background-color: #28a745;
            color: white;
        }
        
        .btn-mark-read:hover {
            background-color: #218838;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-unread {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-read {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-archived {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .search-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0 20px;
            cursor: pointer;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .message-content {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid var(--primary-color);
            margin: 15px 0;
        }
        
        .message-meta {
            display: flex;
            justify-content: space-between;
            color: var(--light-text);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .message-detail {
                flex-direction: column;
            }
            
            .message-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
            
            .message-meta {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/casa.jpg" alt="Casa Baleva Garden Resort">
        </div>
        <nav>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_booking.php"><i class="fas fa-calendar-check"></i> Bookings</a>
            <a href="bookingApprovals.php"><i class="fas fa-check-circle"></i> Booking Approvals</a>
            <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
        </nav>
    </header>

    <div class="container">
        <h2><i class="fas fa-envelope"></i> Guest Messages</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="GET" action="" class="search-container">
            <input type="text" class="search-input" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <select class="filter-select" name="status">
                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                <option value="unread" <?php echo $statusFilter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                <option value="read" <?php echo $statusFilter === 'read' ? 'selected' : ''; ?>>Read</option>
                <option value="archived" <?php echo $statusFilter === 'archived' ? 'selected' : ''; ?>>Archived</option>
            </select>
            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            <?php if (!empty($searchQuery) || $statusFilter !== 'all'): ?>
                <a href="messages.php" class="btn btn-secondary">Clear Filters</a>
            <?php endif; ?>
        </form>
        
        <?php if (empty($messages)): ?>
            <div class="alert alert-info">No messages found matching your criteria</div>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="card">
                    <div class="card-header">
                        <?php echo htmlspecialchars($message['subject']); ?>
                        <span class="status-badge status-<?php echo $message['status']; ?>">
                            <?php echo ucfirst($message['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="message-meta">
                            <div>
                                <strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?> 
                                &lt;<?php echo htmlspecialchars($message['email']); ?>&gt;
                            </div>
                            <div>
                                <strong>Received:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($message['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                        
                        <div class="message-detail">
                            <div class="message-label">Phone:</div>
                            <div class="message-value">
                                <?php echo !empty($message['phone']) ? htmlspecialchars($message['phone']) : 'Not provided'; ?>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn btn-reply" onclick="replyToMessage('<?php echo htmlspecialchars($message['email']); ?>', '<?php echo htmlspecialchars($message['subject']); ?>')">
                                <i class="fas fa-reply"></i> Reply
                            </button>
                            
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $message['status'] === 'unread' ? 'read' : 'unread'; ?>">
                                <button type="submit" name="update_status" class="btn btn-mark-read">
                                    <i class="fas fa-<?php echo $message['status'] === 'unread' ? 'envelope-open' : 'envelope'; ?>"></i> 
                                    <?php echo $message['status'] === 'unread' ? 'Mark as Read' : 'Mark as Unread'; ?>
                                </button>
                            </form>
                            
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                <input type="hidden" name="status" value="archived">
                                <button type="submit" name="update_status" class="btn btn-secondary">
                                    <i class="fas fa-archive"></i> Archive
                                </button>
                            </form>
                            
                            <button class="btn btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                data-message-id="<?php echo $message['id']; ?>"
                                data-message-subject="<?php echo htmlspecialchars($message['subject']); ?>">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="id" id="deleteMessageId">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Message</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this message?</p>
                        <p id="deleteMessageDetails"></p>
                        <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_message" class="btn btn-delete">Delete Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const messageId = button.getAttribute('data-message-id');
                const messageSubject = button.getAttribute('data-message-subject');
                
                document.getElementById('deleteMessageId').value = messageId;
                document.getElementById('deleteMessageDetails').innerHTML = 
                    `<strong>"${messageSubject}"</strong> will be permanently deleted.`;
            });
        }

        window.replyToMessage = function(email, subject) {
            // This would open the default mail client with pre-filled fields
            window.location.href = `mailto:${email}?subject=Re: ${encodeURIComponent(subject)}`;
        };
    });
    </script>
</body>
</html>