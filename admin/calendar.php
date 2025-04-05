<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagonal Partition Booking Calendar</title>
    <style>
        :root {
            --day-color: #FFFFC2;
            --night-color: #C4EBF1;
            --booked-day: rgba(239, 71, 111, 0.7);
            --booked-night: rgba(7, 59, 76, 0.7);
            --text-dark: #2B2D42;
            --text-light: #EDF2F4;
            --border-color: #8D99AE;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .calendar-container {
            max-width: 1000px;
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
            cursor: pointer;
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
            background: linear-gradient(to bottom right, var(--booked-day) 0%, var(--booked-day) 50%, transparent 50%);
        }

        .night-section.booked {
            background: linear-gradient(to top left, var(--booked-night) 0%, var(--booked-night) 50%, transparent 50%);
        }

        .day-section:not(.booked):hover, .night-section:not(.booked):hover {
            filter: brightness(90%);
            transform: scale(1.02);
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
            max-width: 400px;
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

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 16px;
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
            background-color: #4361ee;
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #3a56d4;
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
        }
    </style>
</head>
<body>
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

    <div class="booking-modal" id="booking-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Booking</h3>
                <button class="close-modal" id="close-modal">&times;</button>
            </div>
            <form id="booking-form">
                <div class="form-group">
                    <label>Date</label>
                    <input type="text" id="modal-date" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="text" id="modal-time" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Book Now</button>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarGrid = document.getElementById('calendar-grid');
            const monthTitle = document.getElementById('month-title');
            const prevMonthBtn = document.getElementById('prev-month');
            const nextMonthBtn = document.getElementById('next-month');
            const todayBtn = document.getElementById('today');
            const bookingModal = document.getElementById('booking-modal');
            const closeModalBtn = document.getElementById('close-modal');
            const bookingForm = document.getElementById('booking-form');
            const modalDate = document.getElementById('modal-date');
            const modalTime = document.getElementById('modal-time');
            
            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();
            
            // Sample booking data (format: "YYYY-MM-DD": ["day", "night"])
            const bookings = {
                '2023-03-05': ["day"],
                '2023-03-12': ["night"],
                '2023-03-15': ["day", "night"],
                '2023-03-20': ["night"],
                '2023-03-25': ["day"]
            };
            
            // Initialize calendar
            renderCalendar(currentMonth, currentYear);
            
            // Event listeners
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
            
            closeModalBtn.addEventListener('click', () => {
                bookingModal.style.display = 'none';
            });
            
            bookingForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const date = modalDate.value;
                const time = modalTime.value;
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                
                // In a real app, you would send this data to your server
                console.log('Booking submitted:', { date, time, name, email });
                
                alert(`Booking confirmed for ${date} (${time})`);
                bookingModal.style.display = 'none';
                bookingForm.reset();
                
                // Refresh calendar
                renderCalendar(currentMonth, currentYear);
            });
            
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
                    const dayBookings = bookings[dateString] || [];
                    
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
                    daySection.className = 'day-section';
                    if (dayBookings.includes("day")) daySection.classList.add('booked');
                    
                    daySection.addEventListener('click', () => handleSlotClick(date, 'day', dayBookings.includes("day")));
                    partition.appendChild(daySection);
                    
                    // Night section (bottom-right triangle)
                    const nightSection = document.createElement('div');
                    nightSection.className = 'night-section';
                    if (dayBookings.includes("night")) nightSection.classList.add('booked');
                    
                    nightSection.addEventListener('click', () => handleSlotClick(date, 'night', dayBookings.includes("night")));
                    partition.appendChild(nightSection);
                    
                    dayElement.appendChild(partition);
                    calendarGrid.appendChild(dayElement);
                }
            }
            
            function handleSlotClick(date, timeSlot, isBooked) {
                if (isBooked) {
                    alert('This time slot is already booked!');
                    return;
                }
                
                const dateStr = `${date.getDate()} ${getMonthName(date.getMonth())} ${date.getFullYear()}`;
                modalDate.value = dateStr;
                modalTime.value = timeSlot === 'day' ? 'Day (8AM-6PM)' : 'Night (6PM-8AM)';
                bookingModal.style.display = 'flex';
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
        });
    </script>
</body>
</html>