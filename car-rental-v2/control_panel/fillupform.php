<!DOCTYPE html>
<html>
<head>
  <title>Booking Form</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
  <h1>Booking Form</h1>
  <form method="POST" action="insertbooking.php" id="bookingForm">
    <label for="firstname">First Name:</label>
    <input type="text" id="firstname" name="firstname" required><br><br>

    <label for="lastname">Last Name:</label>
    <input type="text" id="lastname" name="lastname" required><br><br>

    <label for="email">Email:</label>
    <input type="text" id="email" name="email" required><br><br>

    <label for="number">Phone Number:</label>
    <input type="text" id="number" name="number" required><br><br>

    <label for="cars">Select a Car:</label>
    <select id="cars" name="cars" required>
      <option value="">--Select a car--</option>
      <?php
      require_once('dbconn.php');
      $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

      if (!$conn) {
          die("Connection failed: " . mysqli_connect_error());
      }

      $sql = "SELECT DISTINCT car FROM booking_data WHERE status = 3";
      $result = mysqli_query($conn, $sql);

      while ($row = mysqli_fetch_assoc($result)) {
        $car = $row['car'];
        echo "<option value='" . $car . "'>" . $car . "</option>";
      }
      ?>
    </select><br><br>

    <label for="startdate">Start Date:</label>
    <input type="text" id="startdate" name="startdate" readonly><br><br>

    <label for="enddate">End Date:</label>
    <input type="text" id="enddate" name="enddate" readonly><br><br>

    <label for="miles">How far</label>
    <input type="text" id="miles" name="miles" required><br><br>

    <label for="note">Note</label>
    <input type="text" id="note" name="note" required><br><br>

    <input type="submit" value="Submit">
  </form>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
  document.addEventListener("DOMContentLoaded", function() {
    // Function to fetch booked dates from the server
    function fetchBookedDates(selectedCar) {
      return new Promise((resolve, reject) => {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_booked_dates.php?car=" + selectedCar, true);
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            resolve(response.bookedDates);
          }
        };
        xhr.onerror = function() {
          reject(xhr.statusText);
        };
        xhr.send();
      });
    }

    // Function to disable dates
    function disableDates(selectedCar) {
      var startDateInput = document.getElementById("startdate");
      var endDateInput = document.getElementById("enddate");

      fetchBookedDates(selectedCar)
        .then(function(bookedDates) {
          // Initialize flatpickr datepickers
          flatpickr(startDateInput, {
            minDate: "today",
            dateFormat: "Y-m-d",
            disable: bookedDates,
            onChange: function(selectedDates, dateStr, instance) {
              endDateInput._flatpickr.set("minDate", dateStr);
            }
          });

          flatpickr(endDateInput, {
            minDate: "today",
            dateFormat: "Y-m-d",
            disable: bookedDates,
            onChange: function(selectedDates, dateStr, instance) {
              startDateInput._flatpickr.set("maxDate", dateStr);
            }
          });
        })
        .catch(function(error) {
          console.error("Error fetching booked dates:", error);
        });
    }

    var carSelect = document.getElementById("cars");
    carSelect.addEventListener("change", function() {
      var selectedCar = carSelect.value;
      disableDates(selectedCar);
    });

    // Handle form submission
    var form = document.getElementById("bookingForm");
    form.addEventListener("submit", function(event) {
      var startDateInput = document.getElementById("startdate");
      var endDateInput = document.getElementById("enddate");

      if (!startDateInput.value || !endDateInput.value) {
        event.preventDefault();
        alert("Please select start and end dates.");
      }
    });
  });
</script>
</body>
</html>
