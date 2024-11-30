<?php
require_once '../MAIN/database/database.class.php';
$conn = (new Database())->connect();
$student_id = $_SESSION['user_id'];



// Fetch registered sports
$query = $conn->prepare("SELECT s.sport_name, r.status FROM sports s JOIN registrations r ON s.sport_id = r.sport_id WHERE r.student_id = :student_id");
$query->bindParam(':student_id', $student_id);
$query->execute();
$registrations = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch available sports
$sportsQuery = $conn->prepare("SELECT * FROM sports");
$sportsQuery->execute();
$sports = $sportsQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch all events
$eventsQuery = $conn->prepare("SELECT * FROM events");
$eventsQuery->execute();
$events = $eventsQuery->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    // Handle AJAX form submission
    $sport_id = $_POST['sport_id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $contact_info = $_POST['contact_info'];
    $age = $_POST['age'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $bmi = $_POST['bmi'];
    $medcert = $_FILES['medcert']['name'];
    $photo_2x2 = $_FILES['photo_2x2']['name'];
    $cor_pic = $_FILES['cor_pic']['name'];
    $sex = $_POST['sex'];
    $course = $_POST['course'];
    $section = $_POST['section'];

    // Move uploaded files to a designated directory
    $uploadDir = '../uploads/';
    move_uploaded_file($_FILES['medcert']['tmp_name'], $uploadDir . $medcert);
    move_uploaded_file($_FILES['photo_2x2']['tmp_name'], $uploadDir . $photo_2x2);
    move_uploaded_file($_FILES['cor_pic']['tmp_name'], $uploadDir . $cor_pic);

    // Insert into database
    $registerQuery = $conn->prepare("INSERT INTO registrations (student_id, sport_id, last_name, first_name, contact_info, age, height, weight, bmi, medcert, photo_2x2, cor_pic, sex, course, section) 
                                     VALUES (:student_id, :sport_id, :last_name, :first_name, :contact_info, :age, :height, :weight, :bmi, :medcert, :photo_2x2, :cor_pic, :sex, :course, :section)");
    
    $registerQuery->bindParam(':student_id', $student_id);
    $registerQuery->bindParam(':sport_id', $sport_id);
    $registerQuery->bindParam(':last_name', $last_name);
    $registerQuery->bindParam(':first_name', $first_name);
    $registerQuery->bindParam(':contact_info', $contact_info);
    $registerQuery->bindParam(':age', $age);
    $registerQuery->bindParam(':height', $height);
    $registerQuery->bindParam(':weight', $weight);
    $registerQuery->bindParam(':bmi', $bmi);
    $registerQuery->bindParam(':medcert', $medcert);
    $registerQuery->bindParam(':photo_2x2', $photo_2x2);
    $registerQuery->bindParam(':cor_pic', $cor_pic);
    $registerQuery->bindParam(':sex', $sex);
    $registerQuery->bindParam(':course', $course);
    $registerQuery->bindParam(':section', $section);

    if ($registerQuery->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to register.']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<!-- Search Bar -->
<div id="sports_section" class="dashboard-section">
    <h2 class="my-4">Available Sports</h2>

    <!-- Search Bar -->
    <div class="mb-4">
        <input type="text" id="searchSports" class="form-control" placeholder="Search for sports" onkeyup="filterSports()">
    </div>

    <!-- Sports Cards -->
    <div class="row" id="sportsContainer">
        <?php foreach ($sports as $sport): ?>
            <div class="col-md-4 mb-4 sport-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($sport['sport_name']) ?></h5>
                        <button class="btn btn-primary" onclick="showRegistrationForm(<?= $sport['sport_id'] ?>)">Register</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registrationModalLabel">Register for Sport</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="registrationForm" enctype="multipart/form-data">
          <input type="hidden" id="sport_id" name="sport_id">
          
          <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" id="first_name" name="first_name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="contact_info" class="form-label">Contact Info</label>
            <input type="text" id="contact_info" name="contact_info" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="age" class="form-label">Age</label>
            <input type="number" id="age" name="age" class="form-control">
          </div>

          <div class="mb-3">
            <label for="height" class="form-label">Height (in cm)</label>
            <input type="text" id="height" name="height" class="form-control" placeholder="in cm">
          </div>

          <div class="mb-3">
            <label for="weight" class="form-label">Weight (in kg)</label>
            <input type="text" name="weight" id="weight" class="form-control" placeholder="in kg">
          </div>

          <div class="mb-3">
            <label for="bmi" class="form-label">BMI</label>
            <input type="text" name="bmi" id="bmi" class="form-control">
          </div>

          <div class="mb-3">
            <label for="medcert" class="form-label">Medical Certificate</label>
            <input type="file" name="medcert" id="medcert" class="form-control">
          </div>

          <div class="mb-3">
            <label for="photo_2x2" class="form-label">Photo (2x2)</label>
            <input type="file" id="photo_2x2" name="photo_2x2" class="form-control">
          </div>

          <div class="mb-3">
            <label for="cor_pic" class="form-label">COR Pic</label>
            <input type="file" id="cor_pic" name="cor_pic" class="form-control">
          </div>

          <div class="mb-3">
            <label for="sex" class="form-label">Sex</label>
            <select id="sex" name="sex" class="form-select" required>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="course" class="form-label">Course</label>
            <select id="course" name="course" class="form-select" onchange="updateSections()">
              <option value="CS">CS</option>
              <option value="IT">IT</option>
              <option value="ACT">ACT</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <select id="section" name="section" class="form-select"></select>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('registrationForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    var formData = new FormData(this); // Gather form data
    formData.append('ajax', '1'); // Add an AJAX indicator

    // Log form data to the console for debugging
    console.log([...formData]);

    // Send the data using fetch
    fetch('sports_stud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())  // Expect JSON response
    .then(data => {
        if (data.status === 'success') {
            alert(data.message); // Show success message
            var modal = bootstrap.Modal.getInstance(document.getElementById('registrationModal'));
            modal.hide();
        } else {
            alert(data.message); // Show error message
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while registering.');
    });
});

//function for section
function updateSections() {
            var course = document.getElementById('course').value;
            var section = document.getElementById('section');
            section.innerHTML = '';
            if (course === 'CS' || course === 'IT') {
                var options = ['1A', '2A', '2B', '2C', '3A', '3B', '4A'];
                for (var i = 0; i < options.length; i++) {
                    var option = document.createElement('option');
                    option.value = options[i];
                    option.text = options[i];
                    section.appendChild(option);
                }
            } else {
                var option = document.createElement('option');
                option.value = '';
                option.text = 'N/A';
                section.appendChild(option);
            }
        }

        function showRegistrationForm(sport_id) {
            document.getElementById('sport_id').value = sport_id;
            document.getElementById('registration_form').style.display = 'block';
        }

        function showSection(sectionId) {
            var sections = document.getElementsByClassName('dashboard-section');
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = 'none';
            }
            document.getElementById(sectionId).style.display = 'block';
        }
// search bar
// Filter sports by name
function filterSports() {
    var input = document.getElementById("searchSports").value.toLowerCase();
    var cards = document.getElementsByClassName("sport-card");
    
    for (var i = 0; i < cards.length; i++) {
        var sportName = cards[i].getElementsByClassName("card-title")[0].textContent.toLowerCase();
        if (sportName.includes(input)) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}

function showRegistrationForm(sportId) {
    // Set sport ID for registration
    $('#sport_id').val(sportId);
    $('#registration_form').show();
}


function showRegistrationForm(sport_id) {
    // Set the sport_id value to the hidden input in the form
    document.getElementById('sport_id').value = sport_id;
    
    // Show the modal
    var modal = new bootstrap.Modal(document.getElementById('registrationModal'));
    modal.show();
}

</script>
</body>
</html>
  
