<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - Casa Baleva</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --day-color: #FFFFC2;
            --night-color: #C4EBF1;
            --booked-day: rgba(239, 71, 111, 0.7);
            --booked-night: rgba(7, 59, 76, 0.7);
            --text-dark: #2B2D42;
            --text-light: #EDF2F4;
            --border-color: #8D99AE;
            --primary-color: #4361ee;
            --danger-color: #ef476f;
            --success-color: #06d6a0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: var(--text-dark);
        }
        
        header {
            background-color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
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
            color: var(--text-dark);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        nav a:hover {
            color: var(--primary-color);
        }

        .main-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content-container {
            display: flex;
            flex: 1;
            padding-top: 20px;
        }

        /* Booking List Styles */
        .booking-list {
            width: 35%;
            background: white;
            padding: 20px;
            overflow-y: auto;
            border-right: 1px solid #e0e0e0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        }

        .booking-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .booking-list-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .booking-search {
            padding: 8px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            width: 60%;
        }

        .booking-item {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .booking-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .booking-item.active {
            border-left: 4px solid var(--primary-color);
            background-color: #f8f9ff;
        }

        .booking-item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .booking-name {
            font-weight: 600;
            font-size: 18px;
        }

        .booking-status {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-confirmed {
            background-color: var(--success-color);
            color: white;
        }

        .status-pending {
            background-color: #ffd166;
            color: var(--text-dark);
        }

        .status-cancelled {
            background-color: var(--danger-color);
            color: white;
        }

        .booking-dates {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }

        .booking-option {
            display: inline-block;
            padding: 3px 8px;
            background-color: #f0f0f0;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 5px;
        }

        .booking-details {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }

        .booking-details-row {
            display: flex;
            margin-bottom: 5px;
        }

        .booking-details-label {
            font-weight: 500;
            width: 100px;
        }

        /* Calendar Styles */
        .calendar-section {
            width: 65%;
            padding: 20px;
            overflow-y: auto;
        }

        .calendar-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .month-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
        }

        .nav-button {
            background: none;
            border: 1px solid var(--border-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .nav-button:hover {
            background: #f1f1f1;
        }

        .weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .calendar-day {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            height: 120px;
        }

        .day-header {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .current-day .day-header {
            background-color: #4CC9F0;
            color: white;
        }

        /* Diagonal partition styles */
        .diagonal-partition {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .day-section, .night-section {
            position: absolute;
            width: 100%;
            height: 100%;
            transition: all 0.2s;
        }

        .day-section {
            background: linear-gradient(to bottom right, var(--day-color) 0%, var(--day-color) 50%, transparent 50%);
            clip-path: polygon(0 0, 100% 0, 0 100%);
        }

        .night-section {
            background: linear-gradient(to top left, var(--night-color) 0%, var(--night-color) 50%, transparent 50%);
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }

        .day-section.booked {
            background: linear-gradient(to bottom right, var(--booked-day) 0%, var(--booked-day) 50%, transparent 50%) !important;
            z-index: 1;
        }

        .night-section.booked {
            background: linear-gradient(to top left, var(--booked-night) 0%, var(--booked-night) 50%, transparent 50%) !important;
            z-index: 1;
        }

        .empty-day {
            visibility: hidden;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        /* Modal Styles */
        .booking-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a56d4;
        }

        .btn-danger:hover {
            background-color: #d2335b;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Responsive styles */
        @media (max-width: 1200px) {
            .content-container {
                flex-direction: column;
            }
            
            .booking-list, .calendar-section {
                width: 100%;
            }
            
            .booking-list {
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
            }
        }

        @media (max-width: 768px) {
            .calendar-grid {
                grid-template-columns: repeat(1, 1fr);
            }
            
            .weekdays {
                display: none;
            }
            
            .calendar-day {
                margin-bottom: 15px;
            }

            nav {
                gap: 15px;
            }

            .booking-search {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            header {
                flex-direction: column;
                padding: 15px;
            }

            .logo {
                margin-bottom: 10px;
            }

            nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
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
        
        <div class="content-container">
            <!-- Booking List Section -->
            <div class="booking-list">
                <div class="booking-list-header">
                    <h2 class="booking-list-title">Bookings</h2>
                    <input type="text" class="booking-search" placeholder="Search bookings...">
                </div>
                
                <div id="bookings-container">
                    <!-- Bookings will be loaded here -->
                </div>
            </div>
            
            <!-- Calendar Section -->
            <div class="calendar-section">
                <div class="calendar-container">
                    <div class="calendar-header">
                        <h2 class="month-title" id="month-title">March 2023</h2>
                        <div class="nav-buttons">
                            <button class="nav-button" id="prev-month"><i class="fas fa-chevron-left"></i></button>
                            <button class="nav-button" id="today">Today</button>
                            <button class="nav-button" id="next-month"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <div class="weekdays">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>

                    <div class="calendar-grid" id="calendar-grid">
                        <!-- Calendar days will be generated here -->
                    </div>

                    <div class="legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--day-color);"></div>
                            <span>Day (8AM-6PM)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--night-color);"></div>
                            <span>Night (6PM-8AM)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--booked-day);"></div>
                            <span>Booked Day</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--booked-night);"></div>
                            <span>Booked Night</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="booking-modal" id="booking-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Booking Details</h3>
                <button class="close-modal" id="close-modal">&times;</button>
            </div>
            <div id="modal-content">
                <!-- Booking details will be loaded here -->
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" id="close-modal-btn">Close</button>
                <button class="btn btn-danger" id="cancel-booking-btn" style="display: none;">Cancel Booking</button>
                <button class="btn btn-primary" id="confirm-booking-btn" style="display: none;">Confirm Booking</button>
            </div>
        </div>
    </div>

    <!-- Cancellation Reason Modal -->
    <div class="booking-modal" id="cancel-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Cancel Booking</h3>
                <button class="close-modal" id="close-cancel-modal">&times;</button>
            </div>
            <div class="form-group">
                <label for="cancellation-reason">Reason for Cancellation</label>
                <textarea id="cancellation-reason" class="form-control" rows="4" required></textarea>
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" id="cancel-cancel-btn">Back</button>
                <button class="btn btn-danger" id="confirm-cancel-btn">Confirm Cancellation</button>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Calendar elements
            const calendarGrid = document.getElementById('calendar-grid');
            const monthTitle = document.getElementById('month-title');
            const prevMonthBtn = document.getElementById('prev-month');
            const nextMonthBtn = document.getElementById('next-month');
            const todayBtn = document.getElementById('today');
            
            // Modal elements
            const bookingModal = document.getElementById('booking-modal');
            const closeModalBtn = document.getElementById('close-modal');
            const closeModalBtn2 = document.getElementById('close-modal-btn');
            const modalContent = document.getElementById('modal-content');
            const cancelBookingBtn = document.getElementById('cancel-booking-btn');
            const confirmBookingBtn = document.getElementById('confirm-booking-btn');
            
            // Cancellation modal elements
            const cancelModal = document.getElementById('cancel-modal');
            const closeCancelModal = document.getElementById('close-cancel-modal');
            const cancelCancelBtn = document.getElementById('cancel-cancel-btn');
            const confirmCancelBtn = document.getElementById('confirm-cancel-btn');
            const cancellationReason = document.getElementById('cancellation-reason');
            
            // Booking list elements
            const bookingsContainer = document.getElementById('bookings-container');
            const bookingSearch = document.querySelector('.booking-search');
            
            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();
            let bookings = [];
            let selectedBookingId = null;
            
            // Initialize page
            fetchBookings();
            renderCalendar(currentMonth, currentYear);
            
            // Event listeners for calendar navigation
            prevMonthBtn.addEventListener('click', () => {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                renderCalendar(currentMonth, currentYear);
            });
            
            nextMonthBtn.addEventListener('click', () => {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                renderCalendar(currentMonth, currentYear);
            });
            
            todayBtn.addEventListener('click', () => {
                currentDate = new Date();
                currentMonth = currentDate.getMonth();
                currentYear = currentDate.getFullYear();
                renderCalendar(currentMonth, currentYear);
            });
            
            // Event listeners for modals
            closeModalBtn.addEventListener('click', () => {
                bookingModal.style.display = 'none';
            });
            
            closeModalBtn2.addEventListener('click', () => {
                bookingModal.style.display = 'none';
            });
            
            closeCancelModal.addEventListener('click', () => {
                cancelModal.style.display = 'none';
            });
            
            cancelCancelBtn.addEventListener('click', () => {
                cancelModal.style.display = 'none';
                bookingModal.style.display = 'flex';
            });
            
            // Search functionality
            bookingSearch.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const filteredBookings = bookings.filter(booking => 
                    `${booking.firstName} ${booking.lastName}`.toLowerCase().includes(searchTerm) ||
                    booking.email.toLowerCase().includes(searchTerm) ||
                    booking.phone.toLowerCase().includes(searchTerm)
                );
                renderBookingList(filteredBookings);
            });
            
            // Cancel booking
            cancelBookingBtn.addEventListener('click', () => {
                bookingModal.style.display = 'none';
                cancelModal.style.display = 'flex';
            });
            
            confirmCancelBtn.addEventListener('click', () => {
                if (!cancellationReason.value.trim()) {
                    alert('Please provide a reason for cancellation');
                    return;
                }
                
                cancelBooking(selectedBookingId, cancellationReason.value);
            });
            
            // Confirm booking
            confirmBookingBtn.addEventListener('click', () => {
                confirmBooking(selectedBookingId);
            });
            
            // Fetch bookings from database
            function fetchBookings() {
                // In a real application, this would be an AJAX call to your server
                fetch('get_bookings.php')
                    .then(response => response.json())
                    .then(data => {
                        bookings = data;
                        renderBookingList(bookings);
                    })
                    .catch(error => {
                        console.error('Error fetching bookings:', error);
                        // For demo purposes, we'll use sample data if the fetch fails
                        bookings = getSampleBookings();
                        renderBookingList(bookings);
                    });
            }
            
            // Render booking list
            function renderBookingList(bookingsToRender) {
                bookingsContainer.innerHTML = '';
                
                if (bookingsToRender.length === 0) {
                    bookingsContainer.innerHTML = '<p>No bookings found</p>';
                    return;
                }
                
                bookingsToRender.forEach(booking => {
                    const bookingElement = document.createElement('div');
                    bookingElement.className = 'booking-item';
                    bookingElement.dataset.id = booking.id;
                    
                    const checkInDate = new Date(booking.checkInDate);
                    const checkOutDate = booking.checkOutDate ? new Date(booking.checkOutDate) : null;
                    
                    bookingElement.innerHTML = `
                        <div class="booking-item-header">
                            <div class="booking-name">${booking.firstName} ${booking.lastName}</div>
                            <div class="booking-status status-${booking.status.toLowerCase()}">${booking.status}</div>
                        </div>
                        <div class="booking-dates">
                            <div>${formatDateDisplay(checkInDate)}</div>
                            ${checkOutDate ? `<div>${formatDateDisplay(checkOutDate)}</div>` : ''}
                        </div>
                        <div>
                            <span class="booking-option">${booking.stayOption}</span>
                            <span class="booking-option">${booking.guestCount} guests</span>
                            <span class="booking-option">${booking.roomsIncluded}</span>
                        </div>
                        <div class="booking-details">
                            <div class="booking-details-row">
                                <div class="booking-details-label">Email:</div>
                                <div>${booking.email}</div>
                            </div>
                            <div class="booking-details-row">
                                <div class="booking-details-label">Phone:</div>
                                <div>${booking.phone}</div>
                            </div>
                        </div>
                    `;
                    
                    bookingElement.addEventListener('click', () => {
                        document.querySelectorAll('.booking-item').forEach(item => {
                            item.classList.remove('active');
                        });
                        bookingElement.classList.add('active');
                        showBookingDetails(booking.id);
                    });
                    
                    bookingsContainer.appendChild(bookingElement);
                });
            }
            
            // Show booking details in modal
            function showBookingDetails(bookingId) {
                selectedBookingId = bookingId;
                const booking = bookings.find(b => b.id == bookingId);
                
                if (!booking) return;
                
                const checkInDate = new Date(booking.checkInDate);
                const checkOutDate = booking.checkOutDate ? new Date(booking.checkOutDate) : null;
                
                let detailsHtml = `
                    <div class="booking-details">
                        <div class="booking-details-row">
                            <div class="booking-details-label">Name:</div>
                            <div>${booking.firstName} ${booking.lastName}</div>
                        </div>
                        <div class="booking-details-row">
                            <div class="booking-details-label">Email:</div>
                            <div>${booking.email}</div>
                        </div>
                        <div class="booking-details-row">
                            <div class="booking-details-label">Phone:</div>
                            <div>${booking.phone}</div>
                        </div>
                        <div class="booking-details-row">
                            <div class="booking-details-label">Address:</div>
                            <div>${booking.address || 'N/A'}</div>
                        </div>
                        <div class="booking-details-row">
                            <div class="booking-details-label">Status:</div>
                            <div>${booking.status}</div>
                        </div>
                        <div class="booking-details-row">
                            <div class="booking-details-label">Stay Option:</div>
                            <div>${booking.stayOption}</div>
                        </div>
                        <div class="booking-details-row">
                            <div class="booking-details-label">Check-in:</div>
                            <div>${formatDateDisplay(checkInDate)}</div>
                        </div>
                `;
                
                if (checkOutDate) {
                    detailsHtml += `
                        <div class="booking-details-row">
                            <div class="booking-details-label">Check-out:</div>
                            <div>${formatDateDisplay(checkOutDate)}</div>
                        </div>
                    `;
                }
                
                detailsHtml += `
                        <div class="booking-details-row">
                            <div class="booking-details-label">Guests:</div>
                            <div>${booking.guestCount}</div>
                        </div>
                        <div class="booking-details-row">
                            <div class="booking-details-label">Rooms:</div>
                            <div>${booking.roomsIncluded}</div>
                        </div>
                        <div class="booking-details-row">
                            <div class="booking-details-label">Comments:</div>
                            <div>${booking.comments || 'N/A'}</div>
                        </div>
                `;
                
                if (booking.cancellation_reason) {
                    detailsHtml += `
                        <div class="booking-details-row">
                            <div class="booking-details-label">Cancellation Reason:</div>
                            <div>${booking.cancellation_reason}</div>
                        </div>
                    `;
                }
                
                detailsHtml += `</div>`;
                
                modalContent.innerHTML = detailsHtml;
                
                // Show/hide action buttons based on status
                cancelBookingBtn.style.display = 'none';
                confirmBookingBtn.style.display = 'none';
                
                if (booking.status === 'PENDING') {
                    confirmBookingBtn.style.display = 'block';
                    cancelBookingBtn.style.display = 'block';
                } else if (booking.status === 'CONFIRMED') {
                    cancelBookingBtn.style.display = 'block';
                }
                
                bookingModal.style.display = 'flex';
            }
            
            // Cancel booking function
            function cancelBooking(bookingId, reason) {
                // In a real application, this would be an AJAX call to your server
                fetch('update_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: bookingId,
                        status: 'CANCELLED',
                        cancellation_reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Booking cancelled successfully');
                        cancelModal.style.display = 'none';
                        bookingModal.style.display = 'none';
                        fetchBookings();
                        renderCalendar(currentMonth, currentYear);
                    } else {
                        alert('Error cancelling booking: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error cancelling booking');
                });
            }
            
            // Confirm booking function
            function confirmBooking(bookingId) {
                // In a real application, this would be an AJAX call to your server
                fetch('update_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: bookingId,
                        status: 'CONFIRMED'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Booking confirmed successfully');
                        bookingModal.style.display = 'none';
                        fetchBookings();
                        renderCalendar(currentMonth, currentYear);
                    } else {
                        alert('Error confirming booking: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error confirming booking');
                });
            }
            
            // Render calendar function
            function renderCalendar(month, year) {
                calendarGrid.innerHTML = '';
                
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const today = new Date();
                
                monthTitle.textContent = `${getMonthName(month)} ${year}`;
                
                // Add empty cells for days before the 1st
                for (let i = 0; i < firstDay; i++) {
                    const emptyDay = document.createElement('div');
                    emptyDay.className = 'empty-day';
                    calendarGrid.appendChild(emptyDay);
                }
                
                // Add days of the month
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    const dateString = formatDate(date);
                    const isToday = date.toDateString() === today.toDateString();
                    
                    const dayElement = document.createElement('div');
                    dayElement.className = 'calendar-day';
                    if (isToday) dayElement.classList.add('current-day');
                    
                    // Day header
                    const dayHeader = document.createElement('div');
                    dayHeader.className = 'day-header';
                    dayHeader.textContent = day;
                    dayElement.appendChild(dayHeader);
                    
                    // Diagonal partition container
                    const partition = document.createElement('div');
                    partition.className = 'diagonal-partition';
                    
                    // Day section (top-left triangle)
                    const daySection = document.createElement('div');
daySection.className = 'day-section ' + (isDateBooked(date, 'day') ? 'booked' : '');
                    
                    // Check if day is booked
                    const isDayBooked = isDateBooked(date, 'day');
                    if (isDayBooked) daySection.classList.add('booked');
                    
                    partition.appendChild(daySection);
                    
                    // Night section (bottom-right triangle)
                    const nightSection = document.createElement('div');
nightSection.className = 'night-section ' + (isDateBooked(date, 'night') ? 'booked' : '');
                    
                    // Check if night is booked
                    const isNightBooked = isDateBooked(date, 'night');
                    if (isNightBooked) nightSection.classList.add('booked');
                    
                    partition.appendChild(nightSection);
                    
                    dayElement.appendChild(partition);
                    calendarGrid.appendChild(dayElement);
                }
            }
            
            // Check if a date is booked based on stay options
            function isDateBooked(date, timeSlot) {
    const dateString = formatDate(date);
    
    for (const booking of bookings) {
        // Change this line to check for lowercase 'confirmed'
        if (booking.status.toLowerCase() !== 'confirmed') continue;
        
        const checkInDate = new Date(booking.checkInDate);
        const checkOutDate = booking.checkOutDate ? new Date(booking.checkOutDate) : checkInDate;
        const checkInString = formatDate(checkInDate);
        const checkOutString = formatDate(checkOutDate);
        
        // Debugging line - add this temporarily
        console.log(`Checking booking ${booking.id}:`, { 
            status: booking.status, 
            stayOption: booking.stayOption,
            checkIn: checkInString,
            checkOut: checkOutString,
            currentDate: dateString,
            timeSlot: timeSlot
        });
        
        // Skip if date is outside booking period
        if (dateString < checkInString || dateString > checkOutString) continue;
        
        switch (booking.stayOption) {
            case 'day':
                if (dateString === checkInString && timeSlot === 'day') return true;
                break;
                
            case '21hoursDay':
                if (dateString === checkInString) return true;
                break;
                
            case 'staycationDay':
                if (timeSlot === 'day' && dateString !== checkOutString) return true;
                if (timeSlot === 'night' && dateString === checkInString) return true;
                break;
                
            case 'night':
                if (dateString === checkInString && timeSlot === 'night') return true;
                break;
                
            case '21hoursNight':
                if (dateString === checkInString && timeSlot === 'night') return true;
                if (dateString === checkOutString && timeSlot === 'day') return true;
                break;
                
            case 'staycationNight':
                if (timeSlot === 'night' && dateString !== checkOutString) return true;
                if (timeSlot === 'day' && dateString === checkInString) return true;
                break;
        }
    }
    return false;
}
            
            // Helper functions
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
                const options = { year: 'numeric', month: 'short', day: 'numeric' };
                return date.toLocaleDateString('en-US', options);
            }
            
            // Sample data for demo purposes
            function getSampleBookings() {
                return [
                    {
                        id: 1,
                        stayOption: 'staycationDay',
                        guestCount: 4,
                        roomsIncluded: '2 Bedrooms',
                        checkInDate: '2023-03-10',
                        checkOutDate: '2023-03-12',
                        firstName: 'John',
                        lastName: 'Doe',
                        email: 'john.doe@example.com',
                        phone: '09123456789',
                        address: '123 Main St, Manila',
                        comments: 'Early check-in requested',
                        receipt: '',
                        status: 'CONFIRMED',
                        version: 1,
                        cancellation_reason: ''
                    },
                    {
                        id: 2,
                        stayOption: 'night',
                        guestCount: 2,
                        roomsIncluded: '1 Bedroom',
                        checkInDate: '2023-03-15',
                        checkOutDate: null,
                        firstName: 'Jane',
                        lastName: 'Smith',
                        email: 'jane.smith@example.com',
                        phone: '09234567890',
                        address: '456 Oak Ave, Quezon City',
                        comments: '',
                        receipt: '',
                        status: 'PENDING',
                        version: 1,
                        cancellation_reason: ''
                    },
                    {
                        id: 3,
                        stayOption: '21hoursNight',
                        guestCount: 3,
                        roomsIncluded: '1 Bedroom, Living Room',
                        checkInDate: '2023-03-20',
                        checkOutDate: '2023-03-21',
                        firstName: 'Robert',
                        lastName: 'Johnson',
                        email: 'robert.j@example.com',
                        phone: '09345678901',
                        address: '789 Pine Rd, Makati',
                        comments: 'Will arrive late',
                        receipt: '',
                        status: 'CONFIRMED',
                        version: 1,
                        cancellation_reason: ''
                    },
                    {
                        id: 4,
                        stayOption: 'day',
                        guestCount: 5,
                        roomsIncluded: '2 Bedrooms, Living Room',
                        checkInDate: '2023-03-05',
                        checkOutDate: null,
                        firstName: 'Maria',
                        lastName: 'Garcia',
                        email: 'maria.g@example.com',
                        phone: '09456789012',
                        address: '321 Elm St, Pasig',
                        comments: 'Birthday celebration',
                        receipt: '',
                        status: 'CANCELLED',
                        version: 1,
                        cancellation_reason: 'Change of plans'
                    }
                ];
            }
        });
    </script>
</body>
</html>