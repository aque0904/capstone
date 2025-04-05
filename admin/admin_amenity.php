<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa Baleva Garden Resort</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <style>
        header {
            position: relative;
            background-color: #ffffff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        header img {
            height: 60px;
        }
        nav {
            display: flex;
            gap: 15px;
        }
        nav a {
            position: relative;
            top: 20px;
            left: -20px;
            text-decoration: none;
            color: #333;
            font-family: Century Gothic;
        }
        nav a:hover {
            color: #008083;
            font-size: 17px;
        }
        footer {
            text-align: center;
            background-color: #f8f8f8;
            padding: 10px 0;
            margin-top: 20px;
            clear: both;
        }
    </style>
</head>
<body>
    <header>
        <img src="casa.jpg" alt="Casa Baleva Garden Resort">
        <nav>
            <a href="dashboard.php">DASHBOARD&nbsp;&nbsp;</a>
            <a href="admin_booking.php">BOOKINGS&nbsp;&nbsp;</a>
            <a href="admin_rooms.php">ROOMS&nbsp;&nbsp;</a>
            <a href="admin_amenity.php">AMENITIES&nbsp;&nbsp;</a>
            <a href="messages.php">MESSAGES&nbsp;&nbsp;</a>
        </nav>
    </header>
    <footer>
        <p>&copy; 2025 Casa Baleva Garden Resort. All rights reserved.</p>
    </footer>
    </body>
    </html>