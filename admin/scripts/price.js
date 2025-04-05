function calculatePackagePrice(month, stayType, guestCount, checkInDate, checkOutDate) {
    let packagePrice = 0;
    let roomsIncluded = "1 Room";
    let daysOfStay = 1; // Default for non-staycation
    
    // Calculate days of stay for staycation
    if (stayType === "staycationDay" || stayType === "staycationNight") {
        const checkIn = new Date(checkInDate);
        const checkOut = new Date(checkOutDate);
        daysOfStay = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
    }

    if (month === 2 || month === 6 || month === 7 || month === 8 || month === 9 || month === 10 || month === 11) {
        // February, June, July, August, September, October, November
        if (stayType === "day") {
            if (guestCount <= 10) packagePrice = 9500;
            else if (guestCount == 11) packagePrice = 9800;
            else if (guestCount >= 12 && guestCount <= 20) packagePrice = 10000;
            else if (guestCount == 21) packagePrice = 10300;
            else if (guestCount == 22) packagePrice = 10600;
            else if (guestCount == 23) packagePrice = 10900;
            else if (guestCount >= 24 && guestCount <= 30) packagePrice = 11000;
            else if (guestCount == 31) packagePrice = 11300;
            else if (guestCount == 32) packagePrice = 11600;
            else if (guestCount == 33) packagePrice = 11900;
            else if (guestCount == 34) packagePrice = 12200;
            else if (guestCount >= 35 && guestCount <= 40) packagePrice = 12500;
            
        } else if (stayType === "night") {
            if (guestCount <= 10) {
                packagePrice = 10000;
                roomsIncluded = "1 Room";
            } else if (guestCount == 11) {
                packagePrice = 10300;
                roomsIncluded = "1 Room";
            } else if (guestCount >= 12 && guestCount <= 20) {
                packagePrice = 10500;
                roomsIncluded = "1 Room";
            } else if (guestCount == 21) {
                packagePrice = 10800;
                roomsIncluded = "1 Room";
            } else if (guestCount == 22) {
                packagePrice = 11100;
                roomsIncluded = "1 Room";
            } else if (guestCount == 23) {
                packagePrice = 11400;
                roomsIncluded = "1 Room";
            } else if (guestCount == 24) {
                packagePrice = 11700;
                roomsIncluded = "1 Room";
            } else if (guestCount >= 25 && guestCount <= 30) {
                packagePrice = 12000;
                roomsIncluded = "2 Room";
            } else if (guestCount == 31) {
                packagePrice = 12300;
                roomsIncluded = "2 Room";
            } else if (guestCount == 32) {
                packagePrice = 12600;
                roomsIncluded = "2 Room";
            } else if (guestCount == 33) {
                packagePrice = 12900;
                roomsIncluded = "2 Room";
            } else if (guestCount == 34) {
                packagePrice = 13200;
                roomsIncluded = "2 Room";
            } else if (guestCount >= 35 && guestCount <= 40) {
                packagePrice = 13500;
                roomsIncluded = "4 Room";
            }
            
        } else if (stayType === "21hoursDay" || stayType === "21hoursNight") {
            if (guestCount <= 10) packagePrice = 15500;
            else if (guestCount == 11) packagePrice = 15800;
            else if (guestCount == 12) packagePrice = 16100;
            else if (guestCount == 13) packagePrice = 16400;
            else if (guestCount == 14) packagePrice = 16700;
            else if (guestCount == 15) packagePrice = 17000;
            else if (guestCount >= 16 && guestCount <= 20) packagePrice = 17500;
            else if (guestCount == 21) packagePrice = 17800;
            else if (guestCount == 22) packagePrice = 18100;
            else if (guestCount == 23) packagePrice = 18400;
            else if (guestCount == 24) packagePrice = 18700;
            else if (guestCount == 25) packagePrice = 19000;
            else if (guestCount >= 26 && guestCount <= 30) {
                packagePrice = 19500;
                roomsIncluded = "3 Room";
            } else if (guestCount == 31) packagePrice = 19800;
            else if (guestCount == 32) packagePrice = 20100;
            else if (guestCount == 33) packagePrice = 20400;
            else if (guestCount == 34) packagePrice = 20700;
            else if (guestCount == 35) packagePrice = 21000;
            else if (guestCount >= 36 && guestCount <= 40) {
                packagePrice = 24500;
                roomsIncluded = "4 Room";
            }
            
        } else if (stayType === "staycationDay" || stayType === "staycationNight") {
            if (guestCount <= 10) {
                packagePrice = 15500 * daysOfStay;
                roomsIncluded = "1 Room";
            } else if (guestCount >= 11 && guestCount <= 20) {
                packagePrice = 17500 * daysOfStay;
                roomsIncluded = "2 Room";
            } else if (guestCount >= 21 && guestCount <= 30) {
                packagePrice = 19500 * daysOfStay;
                roomsIncluded = "3 Room";
            } else if (guestCount >= 31 && guestCount <= 40) {
                packagePrice = 24500 * daysOfStay;
                roomsIncluded = "4 Room";
            }
        }
        
    } else if (month === 12 || month === 1 || month === 3 || month === 4 || month === 5) {
        // December, January, March, April, May
        if (stayType === "day") {
            if (guestCount <= 20) packagePrice = 11000;
            else if (guestCount == 21) packagePrice = 11300;
            else if (guestCount == 22) packagePrice = 11600;
            else if (guestCount == 23) packagePrice = 11900;
            else if (guestCount == 24) packagePrice = 12200;
            else if (guestCount == 25) packagePrice = 12500;
            else if (guestCount >= 26 && guestCount <= 30) packagePrice = 13000;
            else if (guestCount == 31) packagePrice = 13300;
            else if (guestCount == 32) packagePrice = 13600;
            else if (guestCount == 33) packagePrice = 13900;
            else if (guestCount == 34) packagePrice = 14200;
            else if (guestCount == 35) packagePrice = 14500;
            else if (guestCount >= 36 && guestCount <= 40) packagePrice = 15000;
            
        } else if (stayType === "night") {
            if (guestCount <= 20) {
                packagePrice = 13000;
                roomsIncluded = "2 Room";
            } else if (guestCount == 21) {
                packagePrice = 13300;
                roomsIncluded = "2 Room";
            } else if (guestCount == 22) {
                packagePrice = 13600;
                roomsIncluded = "2 Room";
            } else if (guestCount == 23) {
                packagePrice = 13900;
                roomsIncluded = "2 Room";
            } else if (guestCount == 24) {
                packagePrice = 14200;
                roomsIncluded = "2 Room";
            } else if (guestCount == 25) {
                packagePrice = 14500;
                roomsIncluded = "2 Room";
            } else if (guestCount >= 26 && guestCount <= 30) {
                packagePrice = 15000;
                roomsIncluded = "3 Room";
            } else if (guestCount == 31) {
                packagePrice = 15300;
                roomsIncluded = "3 Room";
            } else if (guestCount == 32) {
                packagePrice = 15600;
                roomsIncluded = "3 Room";
            } else if (guestCount == 33) {
                packagePrice = 15900;
                roomsIncluded = "3 Room";
            } else if (guestCount == 34) {
                packagePrice = 16200;
                roomsIncluded = "3 Room";
            } else if (guestCount == 35) {
                packagePrice = 16500;
                roomsIncluded = "3 Room";
            } else if (guestCount >= 36 && guestCount <= 40) {
                packagePrice = 17500;
                roomsIncluded = "4 Room";
            }
            
        } else if (stayType === "21hoursDay" || stayType === "21hoursNight") {
            if (guestCount <= 20) {
                packagePrice = 20000;
                roomsIncluded = "2 Room";
            } else if (guestCount == 21) packagePrice = 20300;
            else if (guestCount == 22) packagePrice = 20600;
            else if (guestCount == 23) packagePrice = 20900;
            else if (guestCount == 24) packagePrice = 21200;
            else if (guestCount == 25) packagePrice = 21500;
            else if (guestCount >= 26 && guestCount <= 30) {
                packagePrice = 25000;
                roomsIncluded = "3 Room";
            } else if (guestCount == 31) packagePrice = 25300;
            else if (guestCount == 32) packagePrice = 25600;
            else if (guestCount == 33) packagePrice = 25900;
            else if (guestCount == 34) packagePrice = 26200;
            else if (guestCount == 35) packagePrice = 26500;
            else if (guestCount >= 31 && guestCount <= 40) {
                packagePrice = 30000;
                roomsIncluded = "4 Room";
            }
        } else if (stayType === "staycationDay" || stayType === "staycationNight") {
            if (guestCount <= 20) {
                packagePrice = 20000 * daysOfStay;
                roomsIncluded = "2 Room";
            } else if (guestCount >= 21 && guestCount <= 30) {
                packagePrice = 25000 * daysOfStay;
                roomsIncluded = "3 Room";
            } else if (guestCount >= 31 && guestCount <= 40) {
                packagePrice = 30000 * daysOfStay;
                roomsIncluded = "4 Room";
            }
        }
    }

    return {
        price: packagePrice,
        rooms: roomsIncluded,
        days: daysOfStay
    };
}