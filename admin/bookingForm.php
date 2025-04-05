<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 1170px; /* Adjusted width for table */
            height: 530px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            margin-top: 0;
        }
        .form-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .form-container table td {
            padding: 10px;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: -80px;
        }
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: -20px;
        }
        .form-container button {
            background: #5cb85c;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="overlay">
        <div class="form-container">
            <h2>Booking Form</h2>
            <form action="submitBooking.php" method="post">
                <table>
                    <tr>
                        <td colspan="2"><input type="text" name="firstName" placeholder="First Name" required></td>
                        <td><input type="text" name="lastName" placeholder="Last Name" required></td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td colspan="3"><input type="text" name="address" placeholder="Your Address" required></td>
                    </tr>
                    <tr>
                        <td><input type="email" name="email" placeholder="Your Email" required></td>
                        <td><input type="text" name="phone" placeholder="Your Phone Number" required></td>
                        <td><input type="number" id="guest-count" name="guestCount" placeholder="Guest Count" required></td>
                    </tr>
                    <tr>
                        <td>
                            <select name="stayOption" id="stay-option" required>
                                <option value="">Select Stay Option</option>
                                <option value="day">Day</option>
                                <option value="night">Night</option>
                                <option value="21hours">21 Hours</option>
                                <option value="staycation">Staycation</option>
                            </select>
                        </td>
                        <td><input type="date" name="checkInDate" placeholder="Check-In Date" required></td>
                        <td><input type="date" name="checkOutDate" placeholder="Check-Out Date" required></td>
                    </tr>
                    <tr>
                        <td colspan="3"><textarea name="comments" placeholder="Comments" rows="4"></textarea></td>
                    </tr>
                    <tr>
                        <td>
                            <label for="packagePrice">Package Price</label>
                            <input type="number" id="packagePrice" name="packagePrice" placeholder="Package Price" required>
                        </td>
                        <td>
                            <label for="downPayment">Down Payment</label>
                            <input type="number" id="downPayment" name="downPayment" placeholder="Down Payment" required>
                        </td>
                        <td>
                            <label for="balance">Balance</label>
                            <input type="number" id="balance" name="balance" placeholder="Balance" required>
                        </td>
                    </tr>
                    <tr> 
                        <td colspan="3"><input type="text" name="receipt" placeholder="Receipt" required></td>
                    </tr>
                    <tr>
                        <td colspan="3"><button type="submit">Submit</button></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $("#stay-option, #guest-count").change(function () {
                validateGuestCount();
                updateCheckoutDate();
                updatePackageDetails();
            });

            function validateGuestCount() {
                const guestCountInput = document.getElementById('guest-count');
                if (guestCountInput.value > 40) {
                    alert("The maximum guest count is 40.");
                    guestCountInput.value = 40;
                }
            }

            function updateCheckoutDate() {
                let stayType = $("#stay-option").val();
                let checkInDate = $("#check-in").datepicker("getDate");

                if (!checkInDate) return;

                let checkOutDate = new Date(checkInDate);

                if (stayType === "day") {
                    $("#check-out").val($.datepicker.formatDate("yy-mm-dd", checkOutDate)).prop("readonly", true).prop("disabled", true);
                } else if (stayType === "night" || stayType === "21hours") {
                    checkOutDate.setDate(checkOutDate.getDate() + 1);
                    $("#check-out").val($.datepicker.formatDate("yy-mm-dd", checkOutDate)).prop("readonly", true).prop("disabled", true);
                } else if (stayType === "staycation") {
                    $("#check-out").val("").prop("readonly", false).prop("disabled", false);
                    $("#check-out").datepicker("option", "minDate", checkInDate);
                } else {
                    $("#check-out").val("").prop("readonly", false).prop("disabled", false);
                    $("#check-out").datepicker("option", "minDate", checkInDate);
                }
            }

            function formatPrice(price) {
                return "₱" + parseFloat(price).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function updatePackageDetails() {
                let stayType = $("#stay-option").val();
                let guestCount = parseInt($("#guest-count").val(), 10);
                let packageDetails = "";
                let packagePrice = "";
                let roomsIncluded = "";
                let amenities = "Standard amenities included";
                let roomTitle = "Casa Baleva Villa";
                let roomRate = "Best Available Rate 2024";
                let roomImage = "room-image.jpg";

                let checkInDate = $("#check-in").datepicker("getDate");
                let month = checkInDate ? checkInDate.getMonth() + 1 : null;

                if (month === 2 || month === 6 || month === 7 || month === 8 || month === 9 || month === 10 || month === 11) {
                    // February, June, July, August, September, October, November
                    if (stayType === "day") {
                        if (guestCount <= 10) {
                            packageDetails = "₱9,500.00 - 1 Room";
                            packagePrice = "9500";
                        } else if (guestCount >= 11 && guestCount <= 20) {
                            packageDetails = "₱10,000.00 - 1 Room";
                            packagePrice = "10000";
                        } else if (guestCount >= 21 && guestCount <= 30) {
                            packageDetails = "₱11,000.00 - 1 Room";
                            packagePrice = "11000";
                        } else if (guestCount >= 31 && guestCount <= 40) {
                            packageDetails = "₱12,500.00 - 1 Room";
                            packagePrice = "12500";
                        }
                    } else if (stayType === "night") {
                        if (guestCount <= 10) {
                            packageDetails = "₱10,000.00 - 1 Room";
                            packagePrice = "10000";
                        } else if (guestCount >= 11 && guestCount <= 20) {
                            packageDetails = "₱10,500.00 - 1 Room";
                            packagePrice = "10500";
                        } else if (guestCount >= 21 && guestCount <= 30) {
                            packageDetails = "₱12,000.00 - 1 Room";
                            packagePrice = "12000";
                        } else if (guestCount >= 31 && guestCount <= 40) {
                            packageDetails = "₱13,500.00 - 1 Room";
                            packagePrice = "13500";
                        }
                    } else if (stayType === "21hours") {
                        if (guestCount <= 10) {
                            packageDetails = "₱15,500.00 - 1 Room";
                            packagePrice = "15500";
                        } else if (guestCount >= 11 && guestCount <= 20) {
                            packageDetails = "₱17,500.00 - 1 Room";
                            packagePrice = "17500";
                        } else if (guestCount >= 21 && guestCount <= 30) {
                            packageDetails = "₱19,500.00 - 1 Room";
                            packagePrice = "19500";
                        } else if (guestCount >= 31 && guestCount <= 40) {
                            packageDetails = "₱24,500.00 - 1 Room";
                            packagePrice = "24500";
                        }
                    }
                } else if (month === 12 || month === 1 || month === 3 || month === 4 || month === 5) {
                    // December, January, March, April, May
                    if (stayType === "day") {
                        if (guestCount <= 20) {
                            packageDetails = "₱11,000.00 - 1 Room";
                            packagePrice = "11000";
                        } else if (guestCount >= 21 && guestCount <= 30) {
                            packageDetails = "₱13,000.00 - 1 Room";
                            packagePrice = "13000";
                        } else if (guestCount >= 31 && guestCount <= 40) {
                            packageDetails = "₱15,000.00 - 1 Room";
                            packagePrice = "15000";
                        }
                    } else if (stayType === "night") {
                        if (guestCount <= 20) {
                            packageDetails = "₱13,000.00 - 1 Room";
                            packagePrice = "13000";
                        } else if (guestCount >= 21 && guestCount <= 30) {
                            packageDetails = "₱15,000.00 - 1 Room";
                            packagePrice = "15000";
                        } else if (guestCount >= 31 && guestCount <= 40) {
                            packageDetails = "₱17,500.00 - 1 Room";
                            packagePrice = "17500";
                        }
                    } else if (stayType === "21hours") {
                        if (guestCount <= 20) {
                            packageDetails = "₱20,000.00 - 1 Room";
                            packagePrice = "20000";
                        } else if (guestCount >= 21 && guestCount <= 30) {
                            packageDetails = "₱25,000.00 - 1 Room";
                            packagePrice = "25000";
                        } else if (guestCount >= 31 && guestCount <= 40) {
                            packageDetails = "₱30,000.00 - 1 Room";
                            packagePrice = "30000";
                        }
                    }
                }

                if (stayType === "staycation") {
                    let checkInDate = $("#check-in").datepicker("getDate");
                    let checkOutDate = $("#check-out").datepicker("getDate");

                    if (checkInDate && checkOutDate) {
                        let daysOfStay = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

                        if (month === 2 || month === 6 || month === 7 || month === 8 || month === 9 || month === 10 || month === 11) {
                            // February, June, July, August, September, October, November
                            if (guestCount <= 10) {
                                packageDetails = formatPrice(15500 * daysOfStay) + " - 1 Room";
                                packagePrice = (15500 * daysOfStay).toString();
                            } else if (guestCount >= 11 && guestCount <= 20) {
                                packageDetails = formatPrice(17500 * daysOfStay) + " - 1 Room";
                                packagePrice = (17500 * daysOfStay).toString();
                            } else if (guestCount >= 21 && guestCount <= 30) {
                                packageDetails = formatPrice(19500 * daysOfStay) + " - 1 Room";
                                packagePrice = (19500 * daysOfStay).toString();
                            } else if (guestCount >= 31 && guestCount <= 40) {
                                packageDetails = formatPrice(24500 * daysOfStay) + " - 1 Room";
                                packagePrice = (24500 * daysOfStay).toString();
                            }
                        } else if (month === 12 || month === 1 || month === 3 || month === 4 || month === 5) {
                            // December, January, March, April, May
                            if (guestCount <= 20) {
                                packageDetails = formatPrice(20000 * daysOfStay) + " - 1 Room";
                                packagePrice = (20000 * daysOfStay).toString();
                            } else if (guestCount >= 21 && guestCount <= 30) {
                                packageDetails = formatPrice(25000 * daysOfStay) + " - 1 Room";
                                packagePrice = (25000 * daysOfStay).toString();
                            } else if (guestCount >= 31 && guestCount <= 40) {
                                packageDetails = formatPrice(30000 * daysOfStay) + " - 1 Room";
                                packagePrice = (30000 * daysOfStay).toString();
                            }
                        }
                    }
                }

                $("#package-details").text(packageDetails);
                $("#room-title").text(roomTitle);
                $("#room-rate").text(roomRate);
                $("#room-image").attr("src", roomImage);

                // Update the form inputs with the package details
                $("#form-stay-option").val(stayType);
                $("#form-check-in-date").val($("#check-in").val());
                $("#form-check-out-date").val($("#check-out").val());
                $("#form-guest-count").val(guestCount);
                $("#form-package-price").val(packagePrice);
                $("#form-rooms-included").val(roomsIncluded);
                $("#form-amenities").val(amenities);
            }

            function validateAndSubmit() {
                let stayOption = $("#stay-option").val();
                let guestCount = $("#guest-count").val();
                let checkInDate = $("#check-in").val();
                let checkOutDate = $("#check-out").val();

                if (!stayOption || !guestCount || !checkInDate || !checkOutDate) {
                    alert("Please fill in all required fields.");
                    return false;
                }
            }
        });
    </script>
</body>
</html>