<?php
// Assuming you have already established a database connection
require_once('dbconn.php');
$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the list of cars from the database
$query = "SELECT DISTINCT car FROM booking_data WHERE status = 3";
$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query Error: ' . mysqli_error($conn));
}

$cars = array();
while ($row = mysqli_fetch_assoc($result)) {
    $cars[] = $row['car'];
}

// Check if a car is selected
$selectedCar = isset($_GET['car']) ? $_GET['car'] : '';

// Fetch the calendar data for the selected car
$calendarData = '';
if (!empty($selectedCar)) {
    $query = "SELECT DISTINCT start_date, end_date FROM booking_data WHERE car = '$selectedCar' AND status = 3";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die('Query Error: ' . mysqli_error($conn));
    }

    $bookedDates = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $startDate = date('Y-m-d', strtotime($row['start_date']));
        $endDate = date('Y-m-d', strtotime($row['end_date']));

        $bookedDates[] = array(
            'start_date' => $startDate,
            'end_date' => $endDate
        );
    }

    // Generate the calendar HTML based on the booked dates
    $calendarData = generateCalendar($bookedDates);
}

// Close the database connection
mysqli_close($conn);
 

// Function to generate the calendar HTML based on the booked dates
function generateCalendar($bookedDates) {
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

    // Get the previous month and year
    $prevMonth = date('n', strtotime("-1 month", strtotime($startDate)));
    $prevYear = date('Y', strtotime("-1 month", strtotime($startDate)));

    // Get the next month and year
    $nextMonth = date('n', strtotime("+1 month", strtotime($endDate)));
    $nextYear = date('Y', strtotime("+1 month", strtotime($endDate)));

    // Get the number of days in the previous month
    $prevMonthDays = date('t', strtotime("-1 month", strtotime($startDate)));

    // Render the calendar
    $calendarHTML = '<div class="calendar">';
    $calendarHTML .= '<div class="month">' . date('F Y', strtotime("$currentYear-$currentMonth-01")) . '</div>';

    // Render the navigation
    $calendarHTML .= '<div class="navigation">';
    $calendarHTML .= '<a href="?car=' . urlencode($_GET['car']) . '&month=' . $prevMonth . '&year=' . $prevYear . '"><</a>';
    $calendarHTML .= '<a href="?car=' . urlencode($_GET['car']) . '&month=' . $nextMonth . '&year=' . $nextYear . '">></a>';
    $calendarHTML .= '</div>';

    // Render the weekdays
    $calendarHTML .= '<div class="weekdays">';
    $calendarHTML .= '<div class="weekday">Sun</div>';
    $calendarHTML .= '<div class="weekday">Mon</div>';
    $calendarHTML .= '<div class="weekday">Tue</div>';
    $calendarHTML .= '<div class="weekday">Wed</div>';
    $calendarHTML .= '<div class="weekday">Thu</div>';
    $calendarHTML .= '<div class="weekday">Fri</div>';
    $calendarHTML .= '<div class="weekday">Sat</div>';
    $calendarHTML .= '</div>';

    // Render the days
    $calendarHTML .= '<div class="days">';

    // Render the previous month's days
    for ($i = $prevMonthDays - date('w', strtotime($startDate)) + 1; $i <= $prevMonthDays; $i++) {
        $calendarHTML .= '<div class="day prev-month">' . $i . '</div>';
    }

    // Render the current month's days
    $currentDate = $startDate;
    while ($currentDate <= $endDate) {
        $day = date('j', strtotime($currentDate));
        $isBooked = false;
        $isLate = false;

        // Check if the current day is booked and/or late
        foreach ($bookedDates as $booking) {
            $bookingStartDate = date('Y-m-d', strtotime($booking['start_date']));
            $bookingEndDate = date('Y-m-d', strtotime($booking['end_date']));
            if ($currentDate >= $bookingStartDate && $currentDate <= $bookingEndDate) {
                $isBooked = true;

                // Check if the current day is late
                if ($currentDate > $bookingEndDate) {
                    $isLate = true;
                }

                break;
            }
        }

        // Add the booked and late classes if applicable
        $bookedClass = $isBooked ? ' booked' : '';
        $lateClass = $isLate ? ' late' : '';

        $calendarHTML .= '<div class="day' . $bookedClass . $lateClass . '">' . $day . '</div>';

        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    }

    // Render the remaining days from the next month
    for ($i = 1; $i <= 6 - date('w', strtotime($endDate)); $i++) {
        $calendarHTML .= '<div class="day next-month">' . $i . '</div>';
    }

    $calendarHTML .= '</div>'; // Close the days container
    $calendarHTML .= '</div>'; // Close the calendar container

    return $calendarHTML;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Car Booking Calendar</title>
    <link rel="stylesheet" href="test.css">
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <h2>Select a Car:</h2>
        <ul id="car-list" class="horizontal-list">
    <?php foreach ($cars as $car) {
        $imageSrc = ''; // Initialize the image source variable

        // Set the image source based on the car's name
        if ($car === 'Mitsubishi_Infrared') {
            $imageSrc = 'images/mitsubishi-mirage-g4-infrared.jpg';
        } elseif ($car === 'Mitsubishi_gray') {
            $imageSrc = 'images/mitsubishi-mirage-g4-infrared.jpg';
        } elseif ($car === 'Hyundai_white') {
            $imageSrc = 'images/mitsubishi-mirage-g4-infrared.jpg';
        }
    ?>
        <li>
            <a href="?car=<?php echo urlencode($car); ?>">
            <img src="<?php echo $imageSrc; ?>" alt="<?php echo $car; ?>" style="max-width: 200px; max-height: 150px;">
                <?php echo $car; ?>
            </a>
        </li>
    <?php } ?>
</ul>
                <?php echo $calendarData; ?>
            </div>
        </div>
    </div>
</body>
</html>