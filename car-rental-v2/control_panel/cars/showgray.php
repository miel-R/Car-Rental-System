<?php
// Assuming you have already established a database connection
require_once('dbconn.php');
$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the selected car from the query string
$selectedCar = $_GET['car'] ?? '';

// Fetch the bookings data for the selected car from the database
$query = "SELECT DISTINCT car, start_date, end_date FROM booking_data WHERE status = 3 AND car = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $selectedCar);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if the query executed successfully
if (!$result) {
    die('Query Error: ' . mysqli_error($conn));
}

// Create an array to store the bookings data
$bookings = array();

// Loop through the results and store the bookings data in the array
while ($row = mysqli_fetch_assoc($result)) {
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
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Car Booking Calendar</title>
    <link rel="stylesheet" href="control_panel/style.css">
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
        }
        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-gap: 5px;
        }
        .day {
            text-align: center;
            padding: 10px;
            border: 1px solid #ccc;
        }
        .booked {
            background-color: gray;
        }
    </style>
</head>
<body>
    <?php
    // Render the calendar for the selected car
    echo '<div class="calendar">';
    echo '<div class="month">' . $selectedCar . '</div>';

    // Get the current month and year
    $currentMonth = date('n');
    $currentYear = date('Y');

    // Check if a month and year are provided in the query string
    if (isset($_GET['month']) && isset($_GET['year'])) {
        $currentMonth = $_GET['month'];
        $currentYear = $_GET['year'];
    }

    // Get the start and end dates of the current month
    $startDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-01"));
    $endDate = date('Y-m-t', strtotime("$currentYear-$currentMonth-01"));

    // Render the month and year navigation
    echo '<div class="navigation">';
    echo '<a href="?car=' . urlencode($selectedCar) . '&month=' . (intval($currentMonth) - 1) . '&year=' . $currentYear . '">&lt;</a> ';
    echo date('F Y', strtotime("$currentYear-$currentMonth-01"));
    echo ' <a href="?car=' . urlencode($selectedCar) . '&month=' . (intval($currentMonth) + 1) . '&year=' . $currentYear . '">&gt;</a>';
    echo '</div>';

    // Render the weekdays
    echo '<div class="weekdays container">';
    echo '<div class="weekday">Sun</div>';
    echo '<div class="weekday">Mon</div>';
    echo '<div class="weekday">Tue</div>';
    echo '<div class="weekday">Wed</div>';
    echo '<div class="weekday">Thu</div>';
    echo '<div class="weekday">Fri</div>';
    echo '<div class="weekday">Sat</div>';
    echo '</div>';

    // Render the days
    echo '<div class="days">';
    $currentDate = $startDate;
    while ($currentDate <= $endDate) {
        $day = date('j', strtotime($currentDate));
        $isBooked = false;

        // Check if the current day is booked
        foreach ($bookings as $booking) {
            $bookingStartDate = date('Y-m-d', strtotime($booking['start_date']));
            $bookingEndDate = date('Y-m-d', strtotime($booking['end_date']));
            if ($currentDate >= $bookingStartDate && $currentDate <= $bookingEndDate) {
                $isBooked = true;
                break;
            }
        }

        // Add the booked class if the day is booked
        $bookedClass = $isBooked ? ' booked' : '';

        // Render the day
        echo '<div class="day' . $bookedClass . '">' . $day . '</div>';

        // Move to the next day
        $currentDate = date('Y-m-d', strtotime("$currentDate +1 day"));
    }

    echo '</div>'; // Close the days container
    echo '</div>'; // Close the calendar container
    ?>
</body>
</html>