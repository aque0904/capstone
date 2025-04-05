<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['finalize_payment'])) {
    error_log("Finalize Payment Form Submitted");
    error_log("ID: " . $_POST['id']);
    error_log("Payment Mode: " . $_POST['paymentMode']);
}

include 'database.php';
requireAuth();
date_default_timezone_set('Asia/Manila');

$today = date('Y-m-d');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_payment'])) {
        if (empty($_POST['id']) || empty($_POST['amount']) || empty($_POST['details'])) {
            $error = "All fields are required";
        } else {
            $id = intval($_POST['id']);
            $amount = floatval($_POST['amount']);
            $details = trim($_POST['details']).':'.$amount;
            
            // First get the existing payment record
            $getPaymentQuery = "SELECT * FROM payments WHERE id = ?";
            $stmt = $conn->prepare($getPaymentQuery);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $paymentRecord = $stmt->get_result()->fetch_assoc();
                
                if ($paymentRecord) {
                    // Calculate new values
                    $newAdditional = $paymentRecord['additionalCharges'] + $amount;
                    $newDetails = $paymentRecord['details'] 
                                     ? $paymentRecord['details']."|".$details 
                                     : $details;
                    
                    $updateQuery = "UPDATE payments SET 
                                  additionalCharges = ?,
                                  details = ?
                                  WHERE id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("dsi", $newAdditional, $newDetails, $id);
                    
                    if ($updateStmt->execute()) {
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Error updating payment: ".$conn->error;
                    }
                } else {
                    $error = "No payment record found";
                }
            } else {
                $error = "Database error: ".$conn->error;
            }
        }
    } elseif (isset($_POST['finalize_payment'])) {
        $id = intval($_POST['id']);
        $paymentMode = trim($_POST['paymentMode']);
        
        if (empty($id) || empty($paymentMode)) {
            $error = "Booking ID and Payment Mode are required";
        } else {
            $conn->begin_transaction();
            
            try {
                // Get payment details
                $stmt = $conn->prepare("SELECT Balance, additionalCharges FROM payments WHERE id = ?");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $payment = $stmt->get_result()->fetch_assoc();
                
                if (!$payment) {
                    throw new Exception("Payment record not found");
                }
                
                // Calculate total payment
                $totalPayment = $payment['Balance'] + $payment['additionalCharges'];
                
                // Update payment record
$updateStmt = $conn->prepare("UPDATE payments SET 
paymentStatus = 'completed',
totalPayment = ?,
paymentMode = ?,
paymentDate = NOW()
WHERE id = ?");
                if (!$updateStmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $updateStmt->bind_param("dsi", $totalPayment, $paymentMode, $id);
                
                if (!$updateStmt->execute()) {
                    throw new Exception("Failed to update payment record: " . $updateStmt->error);
                }
                
                // Update booking status
                $stmt = $conn->prepare("UPDATE booking SET status = 'completed' WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $conn->commit();
                header("Location: dashboard.php?payment_success=1");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error finalizing payment: " . $e->getMessage();
                error_log($error);
            }
        }
    }
}

// Move this outside the POST condition block
$todaysClientsQuery = "SELECT b.*, p.*, p.paymentStatus as payment_status 
                      FROM booking b
                      JOIN payments p ON b.id = p.id
                      WHERE DATE(b.checkInDate) = '$today'
                      AND b.status != 'checked_out'
                      ORDER BY b.checkInDate ASC";

$todaysClientsResult = $conn->query($todaysClientsQuery);

$today = new DateTime();
$bookedDatesFormatted = [];
$calendarEventsResult = $conn->query("SELECT checkInDate as start, checkOutDate as end, stayOption 
                                    FROM booking WHERE checkOutDate >= CURDATE()");

while($row = $calendarEventsResult->fetch_assoc()) {
    $start = new DateTime($row['start']);
    $end = new DateTime($row['end']);
    $stayOption = $row['stayOption'];
    
    // For each day in the booking range
    for ($date = clone $start; $date <= $end; $date->modify('+1 day')) {
        $dateStr = $date->format('Y-m-d');
        $isPastDate = $date < $today && $date->format('Y-m-d') != $today->format('Y-m-d');
        
        if (!isset($bookedDatesFormatted[$dateStr])) {
            $bookedDatesFormatted[$dateStr] = [
                'bookings' => [],
                'isPast' => $isPastDate
            ];
        }
        
        if ($stayOption === 'day') {
            $bookedDatesFormatted[$dateStr]['bookings'][] = "day";
        } elseif ($stayOption === 'night') {
            $bookedDatesFormatted[$dateStr]['bookings'][] = "night";
        } elseif ($stayOption === '21hoursDay') {
            $bookedDatesFormatted[$dateStr]['bookings'] = ["day", "night"];
        } elseif ($stayOption === '21hoursNight') {
            if ($dateStr === $start->format('Y-m-d')) {
                $bookedDatesFormatted[$dateStr]['bookings'][] = "night";
            } elseif ($dateStr === $end->format('Y-m-d')) {
                $bookedDatesFormatted[$dateStr]['bookings'][] = "day";
            } else {
                $bookedDatesFormatted[$dateStr]['bookings'] = ["day", "night"];
            }
        } elseif ($stayOption === 'staycationDay') {
            if ($dateStr === $start->format('Y-m-d')) {
                $bookedDatesFormatted[$dateStr]['bookings'][] = "day";
            } elseif ($dateStr === $end->format('Y-m-d')) {
                $bookedDatesFormatted[$dateStr]['bookings'][] = "night";
            } else {
                $bookedDatesFormatted[$dateStr]['bookings'] = ["day", "night"];
            }
        } elseif ($stayOption === 'staycationNight') {
            if ($dateStr === $start->format('Y-m-d')) {
                $bookedDatesFormatted[$dateStr]['bookings'][] = "night";
            } elseif ($dateStr === $end->format('Y-m-d')) {
                $bookedDatesFormatted[$dateStr]['bookings'][] = "day";
            } else {
                $bookedDatesFormatted[$dateStr]['bookings'] = ["day", "night"];
            }
        } else {
            $bookedDatesFormatted[$dateStr]['bookings'] = ["day", "night"];
        }
        
        $bookedDatesFormatted[$dateStr]['bookings'] = array_unique($bookedDatesFormatted[$dateStr]['bookings']);
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa Baleva - Admin Dashboard</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary-color: #008083;
            --secondary-color: #f8f8f8;
            --accent-color: #ff6b6b;
            --text-color: #333;
            --light-text: #777;
            --day-color: #F8FFDD;
            --night-color: #B2FBFF;
            --booked-day: red;
            --booked-night: red;
            --text-dark: #2B2D42;
            --text-light: #EDF2F4;
            --border-color: #8D99AE;
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
        
        .dashboard-container {
            display: flex;
            padding: 20px;
            gap: 20px;
        }
        
        .clients-section {
            flex: 1;
        }
        
        .calendar-section {
            width: 50%;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-header h2 {
            color: var(--primary-color);
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .client-cards {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .client-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .client-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .client-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary-color);
        }
        
        .client-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .checkin {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .checkout {
            background-color: #fff8e1;
            color: #ff8f00;
        }
        
        .client-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-group {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: var(--light-text);
            margin-bottom: 3px;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .payment-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .view-receipt {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
            display: inline-block;
        }
        
        .view-receipt:hover {
            background-color: #006669;
        }
        
        /* Diagonal partition calendar styles */
        .diagonal-calendar-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .diagonal-calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .diagonal-month-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .diagonal-nav-buttons {
            display: flex;
            gap: 10px;
        }
        
        .diagonal-nav-button {
            background: none;
            border: 1px solid var(--border-color);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .diagonal-nav-button:hover {
            background: #f1f1f1;
        }
        
        .diagonal-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
        }
        
        .diagonal-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }
        
        .diagonal-calendar-day {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            height: 80px;
        }
        
        .diagonal-day-header {
            position: absolute;
            top: 3px;
            right: 3px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.8rem;
            z-index: 2;
        }
        
        .diagonal-current-day .diagonal-day-header {
            background-color: var(--primary-color);
            color: white;
        }
        
        .diagonal-partition {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .diagonal-day-section, .diagonal-night-section {
            position: absolute;
            width: 100%;
            height: 100%;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .diagonal-day-section {
            background: linear-gradient(to bottom right, var(--day-color) 0%, var(--day-color) 50%, transparent 50%);
            clip-path: polygon(0 0, 100% 0, 0 100%);
        }
        
        .diagonal-night-section {
            background: linear-gradient(to top left, var(--night-color) 0%, var(--night-color) 50%, transparent 50%);
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }
        
        .diagonal-day-section.booked {
    background: linear-gradient(to bottom right, var(--booked-day) 0%, var(--booked-day) 50%, transparent 50%);
}

.diagonal-night-section.booked {
    background: linear-gradient(to top left, var(--booked-night) 0%, var(--booked-night) 50%, transparent 50%);
}
        
        .diagonal-day-section:not(.booked):hover, 
        .diagonal-night-section:not(.booked):hover {
            filter: brightness(90%);
            transform: scale(1.02);
        }
        
        .diagonal-empty-day {
            visibility: hidden;
        }
        
        .diagonal-legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .diagonal-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
        }
        
        .diagonal-legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }
        .diagonal-past-day {
            opacity: 0.6;
            pointer-events: none;
        }

        .diagonal-past-day .diagonal-day-section,
        .diagonal-past-day .diagonal-night-section {
            background: #f5f5f5 !important;
            cursor: not-allowed;
        }

        .diagonal-past-day .diagonal-day-section.booked,
        .diagonal-past-day .diagonal-night-section.booked {
            background: #cccccc !important;
        }

        .diagonal-past-day .diagonal-day-header {
            color: #999;
        }
        
        .diagonal-calendar-day.disabled {
            pointer-events: none;
            opacity: 0.6;
        }
        
        /* Modal for receipt image */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        
        .modal-content img {
            max-width: 80%;
            max-height: 80%;
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 30px;
            cursor: pointer;
        }
        
        /* Additional Payments Styles */
        .additional-payments {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .additional-payments h3 {
            font-size: 1rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .payment-list {
            list-style: none;
            padding: 0;
            margin: 0 0 15px 0;
        }
        
        .payment-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .payment-description {
            flex: 2;
        }
        
        .payment-amount {
            flex: 1;
            text-align: right;
            font-weight: 500;
        }
        
        .add-payment-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .add-payment-form input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex: 1;
        }
        
        .add-payment-form input[type="number"] {
            flex: 0.5;
        }
        
        .add-payment-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .add-payment-btn:hover {
            background-color: #e05555;
        }
        
        .total-balance {
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            text-align: right;
        }
        
        @media (max-width: 1200px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .calendar-section {
                width: 100%;
            }
            
            .client-details, .payment-info {
                grid-template-columns: 1fr;
            }
            
            .add-payment-form {
                flex-direction: column;
            }
            
            .diagonal-calendar-grid {
                grid-template-columns: repeat(1, 1fr);
            }
            
            .diagonal-weekdays {
                display: none;
            }
            
            .diagonal-calendar-day {
                margin-bottom: 10px;
            }
        }
        
        .finalize-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .finalize-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
        }

        .finalize-container h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .finalize-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .finalize-btn, .checkout-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }

        .finalize-btn:hover:not(:disabled),
        .checkout-btn:hover:not(:disabled) {
            background-color: #006669;
            transform: translateY(-2px);
        }

        .finalize-btn:disabled,
        .checkout-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .checkout-btn {
            margin-left: 10px;
            background-color: #4CAF50;
        }

        .checkout-btn:hover:not(:disabled) {
            background-color: #3e8e41;
        }
        
        .payment-summary {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 15px;
        }

        .payment-breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .payment-breakdown-table th, 
        .payment-breakdown-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .payment-breakdown-table .total-row {
            border-top: 2px solid #ddd;
        }

        .payment-mode-section {
            margin: 20px 0;
        }

        .payment-mode-section h3 {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .payment-option {
            flex: 1;
            min-width: 120px;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option .option-content {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option input[type="radio"]:checked + .option-content {
            border-color: var(--primary-color);
            background-color: rgba(0, 128, 131, 0.1);
        }

        .payment-option .option-content i {
            font-size: 24px;
            display: block;
            margin-bottom: 5px;
            color: var(--primary-color);
        }

        .payment-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            position: sticky;
            bottom: 0;
            background: white;
            z-index: 1;
        }

        .cancel-btn {
            background: #f1f1f1;
            color: #555;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .cancel-btn:hover {
            background: #e0e0e0;
        }

        .confirm-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .confirm-btn:hover {
            background: #006669;
        }
        
        .payment-table {
            width: 80%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .payment-table th, 
        .payment-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .payment-table th {
            background-color: #f8f8f8;
        }

        .payment-table tfoot th {
            border-top: 2px solid #ddd;
        }
        
        .additional-charges-details {
            margin: 15px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
        }

        .additional-charges-details h4 {
            margin-top: 0;
            color: var(--primary-color);
        }

        .charges-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .charges-details-table th, 
        .charges-details-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .charges-details-table tfoot th {
            border-top: 1px solid #ddd;
        }
        
        .client-card.checked-out {
            opacity: 0.6;
            background-color: #f9f9f9;
        }

        .client-card.checked-out .checkout-btn {
            display: none;
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

    <div class="dashboard-container">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="clients-section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> Today's Clients</h2>
            </div>
            
            <div class="client-cards">
                <?php if ($todaysClientsResult && $todaysClientsResult->num_rows > 0): ?>
                    <?php while($client = $todaysClientsResult->fetch_assoc()): 
                        $paymentItems = !empty($client['description']) ? explode("|", $client['description']) : [];
                        $currentBalance = $client['Balance'];
                        $additionalCharges = $client['additionalCharges'];
                    ?>
                        <div class="client-card">
                            <div class="client-header">
                                <span class="client-name">
                                    <?php echo htmlspecialchars($client['firstName'] . ' ' . htmlspecialchars($client['lastName'])); ?>
                                </span>
                                <span class="client-status <?php echo (date('Y-m-d') == date('Y-m-d', strtotime($client['checkInDate']))) ? 'checkin' : 'checkout'; ?>">
                                    <?php echo (date('Y-m-d') == date('Y-m-d', strtotime($client['checkInDate']))) ? 'Checking In' : 'Checking Out'; ?>
                                </span>
                            </div>
                            
                            <div class="client-details">
                                <div class="detail-group">
                                    <div class="detail-label">Stay Option</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($client['stayOption']); ?></div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="detail-label">Guest Count</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($client['guestCount']); ?></div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="detail-label">Check-In Date</div>
                                    <div class="detail-value"><?php echo date('M j, Y', strtotime($client['checkInDate'])); ?></div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="detail-label">Check-Out Date</div>
                                    <div class="detail-value"><?php echo date('M j, Y', strtotime($client['checkOutDate'])); ?></div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="detail-label">Rooms</div>
                                    <div class="detail-value">
                                        <?php 
                                            $roomsIncluded = $client['roomsIncluded'];
                                            switch ($roomsIncluded) {
                                                case "1":
                                                    echo "Pool Side Room";
                                                    break;
                                                case "2":
                                                    echo "Pool Side Room & Couple Room";
                                                    break;
                                                case "3":
                                                    echo "Pool Side Room, Couple Room & Family Room";
                                                    break;
                                                case "4":
                                                    echo "Pool Side Room, Couple Room, Family Room & Cabin";
                                                    break;
                                                default:
                                                    echo "Room details not available.";
                                            }
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="detail-label">Comments</div>
                                    <div class="detail-value"><?php echo !empty($client['comments']) ? htmlspecialchars($client['comments']) : 'None'; ?></div>
                                </div>
                            </div>
                            
                            <div class="payment-info">
                                <div class="detail-group">
                                    <div class="detail-label">Package Price</div>
                                    <div class="detail-value" data-field="packagePrice">₱<?= number_format($client['packagePrice'], 2) ?></div>
                                </div>
                                <div class="detail-group">
                                    <div class="detail-label">Down Payment</div>
                                    <div class="detail-value" data-field="downPayment">₱<?= number_format($client['downPayment'], 2) ?></div>
                                </div>
                                <div class="detail-group">
                                    <div class="detail-label">Balance</div>
                                    <div class="detail-value" data-field="Balance">₱<?= number_format($client['Balance'], 2) ?></div>
                                </div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Additional Charges</div>
                                <div class="detail-value" data-field="additionalCharges">₱<?= number_format($client['additionalCharges'], 2) ?></div>
                            </div>
                            
                            <?php
                            // Get the payment record
                            $additionalCharges = $client['additionalCharges'] ?? 0;
                            $details = $client['details'] ?? '';
                            
                            if ($additionalCharges > 0): ?>
                                <div class="payment-details">
                                    <?php if (!empty($details)): ?>
                                        <table class="payment-table">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $charges = explode("|", $details);
                                                $total = 0;
                                                
                                                foreach ($charges as $charge): 
                                                    if (!empty(trim($charge))):
                                                        $parts = explode(":", $charge);
                                                        if (count($parts) === 2):
                                                            $description = trim($parts[0]);
                                                            $amount = floatval($parts[1]);
                                                            $total += $amount;
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($description) ?></td>
                                                    <td>₱<?= number_format($amount, 2) ?></td>
                                                </tr>
                                                <?php   endif;
                                                    endif;
                                                endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>Total Additional Charges:</th>
                                                    <th>₱<?= number_format($total, 2) ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-charges">No additional charges</p>
                            <?php endif; ?>

                            <form class="add-payment-form" method="POST">
                                <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                                <input type="text" name="details" placeholder="Enter charge details (e.g., Extra bed, Late checkout)" required>
                                <input type="number" name="amount" min="0" step="1" placeholder="Amount" required>
                                <button type="submit" name="add_payment" class="add-payment-btn">
                                    <i class="fas fa-plus"></i> Add Charge
                                </button>
                            </form>
                            
                            <div class="payment-actions">
                                <div class="total-payment">
                                    Total Payment Due: ₱<strong><?= number_format(($client['Balance'] + $client['additionalCharges']), 2) ?></strong>
                                </div>
                                
                                <?php if (date('Y-m-d') == date('Y-m-d', strtotime($client['checkOutDate']))): ?>
                                    <button type="button" class="finalize-btn" data-id="<?= $client['id'] ?>"
                                        <?= ($client['payment_status'] ?? 'pending') === 'completed' ? 'disabled' : '' ?>>
                                        <i class="fas fa-calculator"></i> Finalize Payment
                                    </button>
                                    
                                    <button type="button" class="checkout-btn" data-id="<?= $client['id'] ?>"
                                        <?= ($client['payment_status'] ?? 'pending') === 'completed' ? '' : 'disabled' ?>>
                                        <i class="fas fa-sign-out-alt"></i> Check Out
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="client-card">
                        <p style="text-align: center; color: var(--light-text);">No clients today</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="calendar-section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-alt"></i> Booking Calendar</h2>
            </div>
            
            <div class="diagonal-calendar-container">
                <div class="diagonal-calendar-header">
                    <h2 class="diagonal-month-title" id="diagonal-month-title">March 2023</h2>
                    <div class="diagonal-nav-buttons">
                        <button class="diagonal-nav-button" id="diagonal-prev-month"><i class="fas fa-chevron-left"></i></button>
                        <button class="diagonal-nav-button" id="diagonal-today">Today</button>
                        <button class="diagonal-nav-button" id="diagonal-next-month"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <div class="diagonal-weekdays">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>

                <div class="diagonal-calendar-grid" id="diagonal-calendar-grid">
                    <!-- Calendar days will be generated here -->
                </div>

                <div class="diagonal-legend">
                    <div class="diagonal-legend-item">
                        <div class="diagonal-legend-color" style="background-color: var(--day-color);"></div>
                        <span>Day (9AM-6PM)</span>
                    </div>
                    <div class="diagonal-legend-item">
                        <div class="diagonal-legend-color" style="background-color: var(--night-color);"></div>
                        <span>Night (6PM-9AM)</span>
                    </div>
                    <div class="diagonal-legend-item">
                        <div class="diagonal-legend-color" style="background-color: var(--booked-day);"></div>
                        <span>BOOKED</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Finalize Payment Overlay -->
    <div id="finalizeOverlay" class="finalize-overlay">
        <div class="finalize-container">
            <div class="finalize-content">
                <h2><i class="fas fa-receipt"></i> Check-Out Payment</h2>
                <form id="finalizeForm" method="POST" action="dashboard.php">
                    <input type="hidden" name="id" id="finalize-id">
                    
                    <div class="payment-summary">
                        <table class="payment-breakdown-table">
                            <tr>
                                <th>Payment Breakdown</th>
                                <th>Amount</th>
                            </tr>
                            <tr>
                                <td>Balance:</td>
                                <td id="breakdown-balance">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Additional Charges:</td>
                                <td id="breakdown-additionalCharges">₱0.00</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding: 0;">
                                    <div class="additional-charges-details">
                                        <h4>Additional Charges Breakdown</h4>
                                        <table class="charges-details-table">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="charges-details-body">
                                                <!-- Will be populated by JavaScript -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>Total Additional Charges:</th>
                                                    <th id="charges-details-total">₱0.00</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr class="total-row">
                                <td><strong>Total Amount Due:</strong></td>
                                <td id="breakdown-totalPayment"><strong>₱0.00</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="payment-mode-section">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="paymentMode" value="cash" checked>
                                <div class="option-content">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Cash</span>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="paymentMode" value="gcash">
                                <div class="option-content">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>GCash</span>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="paymentMode" value="bank_transfer">
                                <div class="option-content">
                                    <i class="fas fa-university"></i>
                                    <span>Bank Transfer</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="payment-actions">
                        <button type="button" class="cancel-btn" onclick="closeFinalizeOverlay()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="finalize_payment" class="confirm-btn">
                            <i class="fas fa-check-circle"></i> Confirm Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Diagonal partition calendar functionality
        const diagonalCalendarGrid = document.getElementById('diagonal-calendar-grid');
        const diagonalMonthTitle = document.getElementById('diagonal-month-title');
        const diagonalPrevMonthBtn = document.getElementById('diagonal-prev-month');
        const diagonalNextMonthBtn = document.getElementById('diagonal-next-month');
        const diagonalTodayBtn = document.getElementById('diagonal-today');
        
        let diagonalCurrentDate = new Date();
        let diagonalCurrentMonth = diagonalCurrentDate.getMonth();
        let diagonalCurrentYear = diagonalCurrentDate.getFullYear();
        
        // Replace the hardcoded bookings with PHP data
        const diagonalBookings = <?php echo json_encode($bookedDatesFormatted); ?>;
            
        // Initialize calendar
        renderDiagonalCalendar(diagonalCurrentMonth, diagonalCurrentYear);
        
        // Event listeners
        diagonalPrevMonthBtn.addEventListener('click', () => {
            diagonalCurrentMonth--;
            if (diagonalCurrentMonth < 0) {
                diagonalCurrentMonth = 11;
                diagonalCurrentYear--;
            }
            renderDiagonalCalendar(diagonalCurrentMonth, diagonalCurrentYear);
        });
        
        diagonalNextMonthBtn.addEventListener('click', () => {
            diagonalCurrentMonth++;
            if (diagonalCurrentMonth > 11) {
                diagonalCurrentMonth = 0;
                diagonalCurrentYear++;
            }
            renderDiagonalCalendar(diagonalCurrentMonth, diagonalCurrentYear);
        });
        
        diagonalTodayBtn.addEventListener('click', () => {
            diagonalCurrentDate = new Date();
            diagonalCurrentMonth = diagonalCurrentDate.getMonth();
            diagonalCurrentYear = diagonalCurrentDate.getFullYear();
            renderDiagonalCalendar(diagonalCurrentMonth, diagonalCurrentYear);
        });
        
        // Render calendar function
        function renderDiagonalCalendar(month, year) {
            diagonalCalendarGrid.innerHTML = '';
            
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            diagonalMonthTitle.textContent = `${getMonthName(month)} ${year}`;
            
            // Add empty cells for days before the 1st
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'diagonal-empty-day';
                diagonalCalendarGrid.appendChild(emptyDay);
            }
            
            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                date.setHours(0, 0, 0, 0);
                const dateString = formatDate(date);
                const isToday = date.toDateString() === today.toDateString();
                const isPastDate = date < today && !isToday;
                const dayData = diagonalBookings[dateString] || {bookings: [], isPast: false};
                const dayBookings = dayData.bookings;
                const isPastFromData = dayData.isPast || isPastDate;
                
                const dayElement = document.createElement('div');
                dayElement.className = 'diagonal-calendar-day';
                if (isToday) dayElement.classList.add('diagonal-current-day');
                if (isPastFromData) dayElement.classList.add('diagonal-past-day');
                
                // Day header
                const dayHeader = document.createElement('div');
                dayHeader.className = 'diagonal-day-header';
                dayHeader.textContent = day;
                dayElement.appendChild(dayHeader);
                
                // Diagonal partition container
                const partition = document.createElement('div');
                partition.className = 'diagonal-partition';
                
                // Day section (top-left triangle)
                const daySection = document.createElement('div');
                daySection.className = 'diagonal-day-section';
                if (dayBookings.includes("day")) daySection.classList.add('booked');
                if (isPastFromData) daySection.classList.add('disabled');
                
                daySection.addEventListener('click', () => {
                    if (!isPastFromData) {
                        handleDiagonalSlotClick(date, 'day', dayBookings.includes("day"));
                    }
                });
                partition.appendChild(daySection);
                
                // Night section (bottom-right triangle)
                const nightSection = document.createElement('div');
                nightSection.className = 'diagonal-night-section';
                if (dayBookings.includes("night")) nightSection.classList.add('booked');
                if (isPastFromData) nightSection.classList.add('disabled');
                
                nightSection.addEventListener('click', () => {
                    if (!isPastFromData) {
                        handleDiagonalSlotClick(date, 'night', dayBookings.includes("night"));
                    }
                });
                partition.appendChild(nightSection);
                
                dayElement.appendChild(partition);
                diagonalCalendarGrid.appendChild(dayElement);
            }
        }
        
        function handleDiagonalSlotClick(date, timeSlot, isBooked) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (date < today) {
                return; // Prevent any interaction with past dates
            }
            
            if (isBooked) {
                alert('This time slot is already booked!');
                return;
            }
            
            const timeLabel = timeSlot === 'day' ? 'Day (9AM-6PM)' : 'Night (6PM-9AM)';
            if (confirm(`Book ${formatDateDisplay(date)} for ${timeLabel}?`)) {
                console.log(`Booking ${formatDate(date)} for ${timeSlot}`);
                alert(`Booking confirmed for ${formatDateDisplay(date)} (${timeLabel})`);
                renderDiagonalCalendar(diagonalCurrentMonth, diagonalCurrentYear);
            }
        }
        
        function getMonthName(month) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
            return months[month];
        }
        
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        
        function formatDateDisplay(date) {
            return `${getMonthName(date.getMonth())} ${date.getDate()}, ${date.getFullYear()}`;
        }

        // Finalize Payment button
        document.querySelectorAll('.finalize-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.disabled) {
                    const id = this.dataset.id;
                    const card = this.closest('.client-card');
                    
                    // Get payment details from the card
                    const balance = parseFloat(card.querySelector('[data-field="Balance"]').textContent.replace(/[^\d.-]/g, ''));
                    const additionalCharges = parseFloat(card.querySelector('[data-field="additionalCharges"]')?.textContent.replace(/[^\d.-]/g, '') || 0);
                    
                    // Get additional charges details
                    const chargesTable = card.querySelector('.payment-table');
                    const chargesDetails = [];
                    let chargesTotal = 0;
                    
                    if (chargesTable) {
                        const rows = chargesTable.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            const desc = row.cells[0].textContent.trim();
                            const amount = parseFloat(row.cells[1].textContent.replace(/[^\d.-]/g, ''));
                            chargesDetails.push({ description: desc, amount: amount });
                            chargesTotal += amount;
                        });
                    }
                    
                    // Populate the overlay
                    document.getElementById('finalize-id').value = id;
                    document.getElementById('breakdown-balance').textContent = '₱' + balance.toFixed(2);
                    document.getElementById('breakdown-additionalCharges').textContent = '₱' + additionalCharges.toFixed(2);
                    document.getElementById('breakdown-totalPayment').textContent = '₱' + (balance + additionalCharges).toFixed(2);
                    
                    // Populate charges details table
                    const chargesBody = document.getElementById('charges-details-body');
                    chargesBody.innerHTML = '';
                    
                    chargesDetails.forEach(charge => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${charge.description}</td>
                            <td>₱${charge.amount.toFixed(2)}</td>
                        `;
                        chargesBody.appendChild(row);
                    });
                    
                    document.getElementById('charges-details-total').textContent = '₱' + chargesTotal.toFixed(2);
                    
                    // Show the overlay
                    document.getElementById('finalizeOverlay').style.display = 'flex';
                }
            });
        });
        
        // Check Out button
        document.querySelectorAll('.checkout-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.disabled) {
                    const id = this.dataset.id;
                    if (confirm('Are you sure you want to check out this guest?')) {
                        fetch('checkout.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'id=' + id
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        });
                    }
                }
            });
        });
    });

    function closeFinalizeOverlay() {
        document.getElementById('finalizeOverlay').style.display = 'none';
    }
    </script>
</body>
</html>