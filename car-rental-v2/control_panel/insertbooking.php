<!DOCTYPE html>
<html>
<head>
  <title>Loading...</title>
  <style>
    /* CSS for the loading box */
    .loading-box {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      font-size: 24px;
      font-weight: bold;
    }
  </style>
  <script>
    setTimeout(function() {
      window.location.href = "../index.html";
    }, 10000); // Delay in milliseconds before redirecting
  </script>
</head>
<body>
  <div class="loading-box">
    Loading...
  </div>

  <?php
require_once('dbconn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve form data
  $firstname = $_POST['firstname'];
  $lastname = $_POST['lastname'];
  $email = $_POST['email'];
  $number = $_POST['number'];
  $car = $_POST['cars'];
  $startdate = $_POST['startdate'];
  $enddate = $_POST['enddate'];
  $miles = $_POST['miles'];
  $note = $_POST['note'];

  // Create a new database connection
  $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Prepare the SQL statement
  $sql = "INSERT INTO booking_data (firstname, lastname, email, number, car, start_date, end_date, miles, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "sssssssss", $firstname, $lastname, $email, $number, $car, $startdate, $enddate, $miles, $note);

  // Execute the SQL statement
  if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Booking successfully submitted!'); window.location.href = '../index.html';</script>";
    exit();
  } else {
    echo "Error submitting booking: " . mysqli_error($conn);
  }

  // Close the database connection
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
} else {
  echo "Invalid request.";
}
?>

</body>
</html>