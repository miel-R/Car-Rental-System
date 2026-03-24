<?php
require_once('dbconn.php');
$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$selectedCar = $_GET['car'];
$sql = "SELECT start_date, end_date FROM booking_data WHERE car = ? AND status = 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selectedCar);

$stmt->execute();
$result = $stmt->get_result();

$bookedDates = [];

while ($row = mysqli_fetch_assoc($result)) {
  $startDate = $row['start_date'];
  $endDate = $row['end_date'];

  // Format the dates in YYYY-MM-DD format
  $formattedStartDate = date("Y-m-d", strtotime($startDate));
  $formattedEndDate = date("Y-m-d", strtotime($endDate));

  // Generate an array of disabled date objects
  $disabledDateRange = ["from" => $formattedStartDate, "to" => $formattedEndDate];
  $bookedDates[] = $disabledDateRange;
}

$response = ["bookedDates" => $bookedDates];
echo json_encode($response);
?>