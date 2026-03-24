<?php
// Assuming you have already established a database connection
require_once('dbconn.php');
$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the bookings data from the database
$query = "SELECT car, start_date, end_date FROM booking_data";
$result = $conn->query($query);

// Check if the query executed successfully
if (!$result) {
    die('Query Error: ' . $conn->error);
}

// Create an array to store the bookings data
$bookings = array();

// Loop through the results and store the bookings data in the array
while ($row = $result->fetch_assoc()) {
    $car = $row['car'];
    $startdate = $row['start_date'];
    $enddate = $row['end_date'];

    // Store the booking data in the array
    $bookings[] = array(
        'car' => $car,
        'start_date' => $startdate,
        'end_date' => $enddate
    );
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Car Booking Calendar</title>
    <style>
        .calendar {
            font-family: Arial, sans-serif;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            margin-bottom: 20px;
        }
        .month {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .weekdays {
            display: flex;
            justify-content: space-between;
        }
        .weekday {
            text-align: center;
            background-color: #f1f1f1;
            padding: 10px;
            width: calc(100% / 7);
        }
        .days {
            display: flex;
            flex-wrap: wrap;
        }
        .day {
            text-align: center;
            padding: 10px;
            border: 1px solid #ccc;
            width: calc(100% / 7);
        }
        .booked {
            background-color: gray;
        }
        .prev-month,
        .next-month {
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <?php
    // Group bookings by car
    $bookingsByCar = array();
    foreach ($bookings as $booking) {
        $car = $booking['car'];
        if (!isset($bookingsByCar[$car])) {
            $bookingsByCar[$car] = array();
        }
        $bookingsByCar[$car][] = $booking;
    }

    // Render the calendars for each car
    foreach ($bookingsByCar as $car => $carBookings) {
        ?>
        <div class="calendar">
            <div class="car-name"><?php echo $car; ?></div>
            <div class="month"><?php echo date('F Y'); ?></div>
            <div class="weekdays">
                <?php
                $weekdays = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
                foreach ($weekdays as $weekday) {
                    echo '<div class="weekday">' . $weekday . '</div>';
                }
                ?>
            </div>
            <div class="days">
                <?php
                // Get the current month and year
                $currentMonth = date('m');
                $currentYear = date('Y');

                // Get the first day of the current month
                $firstDayOfMonth = date('N', strtotime("$currentYear-$currentMonth-01"));

                // Calculate the previous month and year
                $prevMonth = date('m', strtotime("-1 month", strtotime("$currentYear-$currentMonth-01")));
                $prevYear = date('Y', strtotime("-1 month", strtotime("$currentYear-$currentMonth-01")));

                // Calculate the next month and year
                $nextMonth = date('m', strtotime("+1 month", strtotime("$currentYear-$currentMonth-01")));
                $nextYear = date('Y', strtotime("+1 month", strtotime("$currentYear-$currentMonth-01")));

                // Get the total number of days in the current month
                $totalDays = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

                // Create an empty array to store the bookings for each day
                $bookedDays = array_fill(1, $totalDays, array());

                // Loop through the bookings and populate the bookedDays array
                foreach ($carBookings as $booking) {
                    $bookingStartDate = $booking['start_date'];
                    $bookingEndDate = $booking['end_date'];

                    $startDate = date('j', strtotime($bookingStartDate));
                    $endDate = date('j', strtotime($bookingEndDate));

                    for ($day = $startDate; $day <= $endDate; $day++) {
                        $bookedDays[$day][] = $booking['car'];
                    }
                }

                // Render the calendar days for the previous month
                $prevMonthTotalDays = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);
                $prevMonthStartDay = $prevMonthTotalDays - $firstDayOfMonth + 2;
                for ($i = $prevMonthStartDay; $i <= $prevMonthTotalDays; $i++) {
                    echo '<div class="day prev-month">' . $i . '</div>';
                }

                // Render the calendar days for the current month
                $dayCounter = 1;
                $bookedClass = '';
                for ($i = 1; $i <= $totalDays; $i++) {
                    // Check if the current day is booked for any car
                    if (!empty($bookedDays[$dayCounter])) {
                        $bookedClass = ' booked';
                    } else {
                        $bookedClass = '';
                    }

                    echo '<div class="day' . $bookedClass . '">' . $dayCounter . '</div>';
                    $dayCounter++;
                }

                // Render the calendar days for the next month
                $nextMonthStartDay = ($firstDayOfMonth + $totalDays - 1) % 7 + 1;
                for ($i = 1; $i <= 7 - $nextMonthStartDay; $i++) {
                    echo '<div class="day next-month">' . $i . '</div>';
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>
</body>
</html>
