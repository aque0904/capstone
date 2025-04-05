<?php
// Include your database connection and authentication
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'database.php';
requireAuth();
date_default_timezone_set('Asia/Manila');

// Add PHPMailer for email functionality
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Initialize variables
$error = '';
$success = '';
$bookings = [];
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$StatusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $bookingId = intval($_POST['id']);
    
    try {
        // First get booking details for email
        $stmt = $conn->prepare("SELECT * FROM booking WHERE id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Update booking status
        $stmt = $conn->prepare("UPDATE booking SET status = 'confirmed' WHERE id = ?");
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $bookingId);
        
        if ($stmt->execute()) {
            $success = "Booking #$bookingId has been confirmed successfully!";
            
            // Update payment status if balance is zero
            $paymentStmt = $conn->prepare("UPDATE payments SET paymentStatus = 'completed' WHERE id = ? AND balance <= 0");
            if ($paymentStmt === false) {
                throw new Exception("Payment prepare failed: " . $conn->error);
            }
            $paymentStmt->bind_param("i", $bookingId);
            $paymentStmt->execute();
            
            // Send confirmation email
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
                $mail->SMTPAuth   = true;
                $mail->Username   = 'aquesanjose0904@gmail.com'; // SMTP username
                $mail->Password   = 'viviencastiel0904'; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
                $mail->Port       = 587; // TCP port to connect to
                
                // Recipients
                $mail->setFrom('no-reply@casabaleva.com', 'Casa Baleva Garden Resort');
                $mail->addAddress($booking['email'], $booking['firstName'] . ' ' . $booking['lastName']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Booking at Casa Baleva has been Confirmed!';
                
                // Email body with booking details
                $mail->Body = '
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .header { color: #008083; font-size: 24px; margin-bottom: 20px; }
                            .details { margin: 15px 0; }
                            .label { font-weight: bold; color: #555; }
                            .footer { margin-top: 30px; font-size: 14px; color: #777; }
                        </style>
                    </head>
                    <body>
                        <div class="header">Booking Confirmation</div>
                        <p>Dear ' . htmlspecialchars($booking['firstName']) . ',</p>
                        <p>We are pleased to inform you that your booking at Casa Baleva Garden Resort has been confirmed!</p>
                        
                        <div class="details">
                            <div><span class="label">Booking ID:</span> CB-' . str_pad($bookingId, 3, '0', STR_PAD_LEFT) . '</div>
                            <div><span class="label">Check-in Date:</span> ' . date('F j, Y', strtotime($booking['checkInDate'])) . '</div>
                            <div><span class="label">Check-out Date:</span> ' . date('F j, Y', strtotime($booking['checkOutDate'])) . '</div>
                            <div><span class="label">Guest Count:</span> ' . $booking['guestCount'] . '</div>
                        </div>
                        
                        <p>If you have any questions or need to make changes to your booking, please don\'t hesitate to contact us.</p>
                        
                        <div class="footer">
                            <p>Thank you for choosing Casa Baleva Garden Resort!</p>
                            <p>Phone: +63 123 456 7890<br>
                            Email: info@casabaleva.com</p>
                        </div>
                    </body>
                    </html>
                ';
                
                // Plain text version for non-HTML email clients
                $mail->AltBody = "Booking Confirmation\n\n" .
                    "Dear " . $booking['firstName'] . ",\n\n" .
                    "We are pleased to inform you that your booking at Casa Baleva Garden Resort has been confirmed!\n\n" .
                    "Booking ID: CB-" . str_pad($bookingId, 3, '0', STR_PAD_LEFT) . "\n" .
                    "Check-in Date: " . date('F j, Y', strtotime($booking['checkInDate'])) . "\n" .
                    "Check-out Date: " . date('F j, Y', strtotime($booking['checkOutDate'])) . "\n" .
                    "Guest Count: " . $booking['guestCount'] . "\n\n" .
                    "If you have any questions or need to make changes to your booking, please don't hesitate to contact us.\n\n" .
                    "Thank you for choosing Casa Baleva Garden Resort!\n" .
                    "Phone: +63 123 456 7890\n" .
                    "Email: info@casabaleva.com";
                
                $mail->send();
                $success .= " A confirmation email has been sent to the guest.";
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Error confirming booking: " . $stmt->error;
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $bookingId = intval($_POST['id']);
    $reason = $conn->real_escape_string(trim($_POST['reason']));
    
    try {
        $stmt = $conn->prepare("UPDATE booking SET status = 'cancelled', cancellation_reason = ? WHERE id = ?");
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("si", $reason, $bookingId);
        
        if ($stmt->execute()) {
            $success = "Booking #$bookingId has been cancelled successfully!";
        } else {
            $error = "Error cancelling booking: " . $stmt->error;
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get bookings from database with search and filter
try {
    $query = "SELECT b.*, p.packagePrice, p.downPayment, p.balance, p.paymentStatus 
              FROM booking b
              LEFT JOIN payments p ON b.id = p.id
              WHERE 1=1";

    if (!empty($searchQuery)) {
        $query .= " AND (b.firstName LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                      OR b.lastName LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                      OR b.email LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                      OR b.phone LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
                      OR b.id LIKE '%" . $conn->real_escape_string($searchQuery) . "%')";
    }

    if ($StatusFilter !== 'all') {
        $query .= " AND b.status = '" . $conn->real_escape_string($StatusFilter) . "'";
    }

    $query .= " ORDER BY b.checkInDate DESC";

    $result = $conn->query($query);
    if ($result === false) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching bookings: " . $e->getMessage();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa Baleva - Booking Approvals</title>
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
        }
        
        .card-body {
            padding: 20px;
        }
        
        .booking-detail {
            display: flex;
            margin-bottom: 15px;
        }
        
        .booking-label {
            font-weight: 600;
            width: 180px;
            color: var(--primary-color);
        }
        
        .booking-value {
            flex: 1;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-confirm {
            background-color: #28a745;
            color: white;
        }
        
        .btn-confirm:hover {
            background-color: #218838;
        }
        
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-cancel:hover {
            background-color: #c82333;
        }
        
        .btn-edit {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #138496;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
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
        
        .receipt-container {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #eee;
            margin-bottom: 20px;
        }
        
        .receipt-image {
            max-height: 200px;
            width: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: block;
            margin: 0 auto;
        }
        
        .receipt-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .booking-detail {
                flex-direction: column;
            }
            
            .booking-label {
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
            
            .receipt-container {
                margin-top: 20px;
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
        <h2><i class="fas fa-check-circle"></i> Booking Approvals</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="GET" action="" class="search-container">
            <input type="text" class="search-input" name="search" placeholder="Search bookings..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <select class="filter-select" name="status">
                <option value="all" <?php echo $StatusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                <option value="pending" <?php echo $StatusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $StatusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="cancelled" <?php echo $StatusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            <?php if (!empty($searchQuery) || $StatusFilter !== 'all'): ?>
                <a href="bookingApprovals.php" class="btn btn-secondary">Clear Filters</a>
            <?php endif; ?>
        </form>
        
        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">No bookings found matching your criteria</div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="card">
                    <div class="card-header">
                        Booking #CB-<?php echo str_pad($booking['id'], 3, '0', STR_PAD_LEFT); ?>
                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column - Booking Details -->
                            <div class="col-md-8">
                                <div class="booking-detail">
                                    <div class="booking-label">Guest Name:</div>
                                    <div class="booking-value">
                                        <?php echo htmlspecialchars($booking['firstName'] . ' ' . $booking['lastName']); ?>
                                    </div>
                                </div>
                                <div class="booking-detail">
                                    <div class="booking-label">Contact:</div>
                                    <div class="booking-value">
                                        <?php echo htmlspecialchars($booking['email']); ?> | 
                                        <?php echo htmlspecialchars($booking['phone']); ?>
                                    </div>
                                </div>
                                <div class="booking-detail">
                                    <div class="booking-label">Stay Option:</div>
                                    <div class="booking-value">
                                        <?php 
                                            $stayOptions = [
                                                'day' => 'Day (9AM-6PM)',
                                                'night' => 'Night (6PM-9AM)',
                                                '21hoursDay' => '21 Hours (Day Start)',
                                                '21hoursNight' => '21 Hours (Night Start)',
                                                'staycationDay' => 'Staycation (Day Start)',
                                                'staycationNight' => 'Staycation (Night Start)'
                                            ];
                                            echo $stayOptions[$booking['stayOption']] ?? $booking['stayOption'];
                                        ?>
                                    </div>
                                </div>
                                <div class="booking-detail">
                                    <div class="booking-label">Dates:</div>
                                    <div class="booking-value">
                                        Check-in: <?php echo date('M j, Y \a\t g:i A', strtotime($booking['checkInDate'])); ?><br>
                                        Check-out: <?php echo date('M j, Y \a\t g:i A', strtotime($booking['checkOutDate'])); ?>
                                    </div>
                                </div>
                                <div class="booking-detail">
                                    <div class="booking-label">Guest Count:</div>
                                    <div class="booking-value"><?php echo $booking['guestCount']; ?></div>
                                </div>
                                <div class="booking-detail">
                                    <div class="booking-label">Rooms Included:</div>
                                    <div class="booking-value">
                                        <?php 
                                            $rooms = [
                                                '1' => 'Pool Side Room',
                                                '2' => 'Pool Side Room & Couple Room',
                                                '3' => 'Pool Side Room, Couple Room & Family Room',
                                                '4' => 'Pool Side Room, Couple Room, Family Room & Cabin'
                                            ];
                                            echo $rooms[$booking['roomsIncluded']] ?? $booking['roomsIncluded'];
                                        ?>
                                    </div>
                                </div>
                                <div class="booking-detail">
                                    <div class="booking-label">Payment:</div>
                                    <div class="booking-value">
                                        Package Price: ₱<?php echo number_format($booking['packagePrice'], 2); ?><br>
                                        Down Payment: ₱<?php echo number_format($booking['downPayment'], 2); ?><br>
                                        Balance: ₱<?php echo number_format($booking['balance'], 2); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column - Receipt -->
                            <div class="col-md-4">
                                <?php
                                $receiptFile = basename($booking['receipt'] ?? '');
                                $receiptPath = '/CasaBaleva/web/images/uploads/' . $receiptFile;
                                $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $receiptPath;
                                ?>
                                
                                <?php if (!empty($receiptFile)): ?>
                                    <?php if (file_exists($absolutePath)): ?>
                                        <div class="receipt-container">
                                        <h5 style="text-align: center; font-weight: bold; size: 10px; color: #008083; font-size: 1rem;">PAYMENT RECEIPT</h5>
                                            <img src="<?php echo $receiptPath; ?>" 
                                                 alt="Payment Receipt" 
                                                 class="img-fluid receipt-image"
                                                 onerror="this.style.display='none'">
                                            <div class="receipt-actions mt-2">
                                                <a href="<?php echo $receiptPath; ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-expand"></i> View Full
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            Receipt file not found
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        No receipt uploaded
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button class="btn btn-edit" onclick="editBooking(<?php echo $booking['id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            
                            <?php if ($booking['status'] !== 'cancelled'): ?>
                                <button class="btn btn-cancel" data-bs-toggle="modal" data-bs-target="#cancelModal" 
                                    data-booking-id="<?php echo $booking['id']; ?>">
                                    <i class="fas fa-times"></i> Cancel Booking
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] === 'pending'): ?>
                                <button class="btn btn-confirm" data-bs-toggle="modal" data-bs-target="#confirmModal" 
                                    data-booking-id="<?php echo $booking['id']; ?>">
                                    <i class="fas fa-check"></i> Confirm Booking
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="id" id="confirmBookingId">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Booking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to confirm this booking?</p>
                        <p id="confirmBookingDetails"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="confirm_booking" class="btn btn-confirm">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Cancellation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="id" id="cancelBookingId">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Booking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this booking?</p>
                        <p id="cancelBookingDetails"></p>
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label">Reason for cancellation:</label>
                            <textarea class="form-control" id="cancelReason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="cancel_booking" class="btn btn-cancel">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmModal = document.getElementById('confirmModal');
        const cancelModal = document.getElementById('cancelModal');
        
        if (confirmModal) {
            confirmModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const bookingId = button.getAttribute('data-booking-id');
                const bookingCard = button.closest('.card');
                const guestName = bookingCard.querySelector('.booking-value').textContent.trim();
                
                document.getElementById('confirmBookingId').value = bookingId;
                document.getElementById('confirmBookingDetails').innerHTML = 
                    `<strong>Booking #CB-${String(bookingId).padStart(3, '0')}</strong> for <strong>${guestName}</strong>`;
            });
        }
        
        if (cancelModal) {
            cancelModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const bookingId = button.getAttribute('data-booking-id');
                const bookingCard = button.closest('.card');
                const guestName = bookingCard.querySelector('.booking-value').textContent.trim();
                
                document.getElementById('cancelBookingId').value = bookingId;
                document.getElementById('cancelBookingDetails').innerHTML = 
                    `<strong>Booking #CB-${String(bookingId).padStart(3, '0')}</strong> for <strong>${guestName}</strong>`;
            });
        }

        window.editBooking = function(bookingId) {
            alert('Edit booking #' + bookingId);
        };
    });
    </script>
</body>
</html>