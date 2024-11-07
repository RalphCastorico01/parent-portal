<?php
session_start();
require 'db_connection.php'; // Include your database connection script

$updateMessage = ""; // Initialize the update message variable

// Fetch existing profile data for the logged-in user
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM profile1 WHERE username = :username");
$stmt->execute([':username' => $username]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input data
    $gender = $_POST['gender'];
    $religion = htmlspecialchars(trim($_POST['religion']));
    $pwd_id = htmlspecialchars(trim($_POST['pwd_id']));
    $address = htmlspecialchars(trim($_POST['address']));
    $barangay = htmlspecialchars(trim($_POST['barangay']));
    $diagnosis = htmlspecialchars(trim($_POST['diagnosis']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $middle_name = htmlspecialchars(trim($_POST['middle_name']));
    $birthdate = $_POST['birthdate'];
    $age = (int)$_POST['age'];
    $nationality = htmlspecialchars(trim($_POST['nationality']));
    $petsaNgDiagnosis = $_POST['petsaNgDiagnosis'];
    $DevelopPedia = $_POST['DevelopPedia'];
    $Kapatid = $_POST['Kapatid'];
    
    // Check if the age is within a valid range
    if ($age < 0 || $age > 120) {
        $updateMessage = "Please enter a valid age.";
    } else {
        // Update profile information in the database
        $sql = "UPDATE profile1
                SET gender = :gender, religion = :religion, pwd_id = :pwd_id, address = :address, barangay = :barangay, diagnosis = :diagnosis,
                    last_name = :last_name, first_name = :first_name, middle_name = :middle_name, birthdate = :birthdate, age = :age, nationality = :nationality,
                    petsaNgDiagnosis = :petsaNgDiagnosis, DevelopPedia = :DevelopPedia, Kapatid = :Kapatid
                WHERE username = :username";
        $stmt = $pdo->prepare($sql);
    
        // Bind form data to SQL statement
        try {
            $stmt->execute([
                ':gender' => $gender,
                ':religion' => $religion,
                ':pwd_id' => $pwd_id,
                ':address' => $address,
                ':barangay' => $barangay,
                ':diagnosis' => $diagnosis,
                ':last_name' => $last_name,
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':birthdate' => $birthdate,
                ':age' => $age,
                ':nationality' => $nationality,
                ':username' => $username,
                'petsaNgDiagnosis'=> $petsaNgDiagnosis,
                'DevelopPedia'=> $DevelopPedia,
                'Kapatid'=> $Kapatid
            ]);

            // Check if any rows were affected
            if ($stmt->rowCount() > 0) {
                $updateMessage = "Profile updated successfully!";
            } else {
                // No rows updated, perform an INSERT
                $insertSql = "INSERT INTO profile1 (username, gender, religion, pwd_id, address, barangay, diagnosis,
                    last_name, first_name, middle_name, birthdate, age, nationality, petsaNgDiagnosis, DevelopPedia, Kapatid) 
                    VALUES (:username, :gender, :religion, :pwd_id, :address, :barangay, :diagnosis,
                    :last_name, :first_name, :middle_name, :birthdate, :age, :nationality, :petsaNgDiagnosis, :DevelopPedia, :Kapatid)";
                $insertStmt = $pdo->prepare($insertSql);
                
                $insertStmt->execute([
                    ':username' => $username,
                    ':gender' => $gender,
                    ':religion' => $religion,
                    ':pwd_id' => $pwd_id,
                    ':address' => $address,
                    ':barangay' => $barangay,
                    ':diagnosis' => $diagnosis,
                    ':last_name' => $last_name,
                    ':first_name' => $first_name,
                    ':middle_name' => $middle_name,
                    ':birthdate' => $birthdate,
                    ':age' => $age,
                    ':nationality' => $nationality,
                    'petsaNgDiagnosis'=> $petsaNgDiagnosis,
                    'DevelopPedia'=> $DevelopPedia,
                    'Kapatid'=> $Kapatid
                ]);

                $updateMessage = "Profile updated successfully!";
            }
        } catch (PDOException $e) {
            $updateMessage = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Fetch announcements from the database
try {
    $stmt = $pdo->query("SELECT title, body, date FROM announcements ORDER BY date DESC");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching announcements: " . $e->getMessage();
}

// Fetch requests for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM requests WHERE username = :username ORDER BY date DESC");
$stmt->execute([':username' => $username]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming events
try {
    $stmt = $pdo->query("SELECT eventid, title, description, date, time, location, type, max_slots FROM events ORDER BY date ASC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching events: " . $e->getMessage();
}

// Limit the number of displayed requests to a maximum of 3
$displayedRequests = array_slice($requests, 0, 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Edit</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Your custom CSS -->
</head>
<body>

<?php include 'parent_sidebar.php'; ?>

 <!-- Modal -->
 <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">CSN Center Paranaque</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                Ipinapahayag ang aking pagsang-ayon sa CSN Center Paranaque na kolektahin, irekord, o itapon ang aking personal na impormasyon bilang bahagi ng aking datos alinsunod sa ga probisyon ng Republic Act No. 10173 ng Pilipinas, Data Privacy Act of 2012, at mga kaukulang Implementing Rules and Regulations nito.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelButton" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="proceedButton">Proceed</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-white text-dark">
                <h3 class="mb-0">Impormasyon ng Kliyente</h3>
            </div>
            <div class="card-body">
                <form action="profile_page1.php" method="POST">
                    <div class="row">
                        <!-- Last Name -->
                        <div class="form-group col-md-4">
                            <label for="last_name">Apelyido ng Bata</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required value="<?= $profile['last_name'] ?? ''; ?>">
                        </div>
                        <!-- First Name -->
                        <div class="form-group col-md-4">
                            <label for="first_name">Pangalan ng Bata</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required value="<?= $profile['first_name'] ?? ''; ?>">
                        </div>

                        <!-- Middle Name -->
                        <div class="form-group col-md-4">
                            <label for="middle_name">Gitnang Pangalan ng Bata</label>
                            <input type="text" id="middle_name" name="middle_name" class="form-control" required value="<?= $profile['middle_name'] ?? ''; ?>">
                        </div>
                        <!-- Birthdate -->
                        <div class="form-group col-md-4">
                            <label for="birthdate">Kaarawan (Araw, Buwan, Taon)</label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control" required value="<?= $profile['birthdate'] ?? ''; ?>">
                        </div>
      
                        <!-- Age -->
                        <div class="form-group col-md-1">
                            <label for="age">Edad</label>
                            <input type="number" id="age" name="age" class="form-control" required value="<?= $profile['age'] ?? ''; ?>">
                        </div>
                        <!-- Nationality -->
                        <div class="form-group col-md-3">
                            <label for="nationality">Nasyonalidad</label>
                            <input type="text" id="nationality" name="nationality" class="form-control" required value="<?= $profile['nationality'] ?? ''; ?>">
                        </div>

                    <!-- Kasarian -->
                        <div class="form-group col-md-4">
                            <label for="gender">Kasarian</label><br>
                            <input type="radio" id="male" name="gender" value="Lalaki" required <?= (isset($profile['gender']) && $profile['gender'] == 'Lalaki') ? 'checked' : ''; ?>>
                            <label for="male">Lalaki</label>
                            <input type="radio" id="female" name="gender" value="Babae" required <?= (isset($profile['gender']) && $profile['gender'] == 'Babae') ? 'checked' : ''; ?>>
                            <label for="female">Babae</label>
                        </div>

                    <!-- Relihiyon -->
                        <div class="form-group col-md-4">
                            <label for="religion">Relihiyon</label>
                            <input type="text" id="religion" name="religion" class="form-control" required value="<?= $profile['religion'] ?? ''; ?>">
                        </div>
                        <!-- PWD -->
                        <div class="form-group col-md-4">
                            <label for="pwd_id">PWD ID</label>
                            <input type="text" id="pwd_id" name="pwd_id" class="form-control" value="<?= $profile['pwd_id'] ?? ''; ?>">
                        </div>

                    <!-- Tirahan -->
                        <div class="form-group col-md-8">
                            <label for="address">Tirahan</label>
                            <input type="text" id="address" name="address" class="form-control" required value="<?= $profile['address'] ?? ''; ?>">
                        </div>
                        <!-- Barangay -->
                        <div class="form-group col-md-4">
                            <label for="barangay">Barangay</label>
                            <select id="barangay" name="barangay" class="form-control" required>
                                <option value="">Choose</option>
                                <option value="Barangay 1" <?= (isset($profile['barangay']) && $profile['barangay'] == 'Barangay 1') ? 'selected' : ''; ?>>Barangay 1</option>
                                <option value="Barangay 2" <?= (isset($profile['barangay']) && $profile['barangay'] == 'Barangay 2') ? 'selected' : ''; ?>>Barangay 2</option>
                                <!-- Add more barangays as needed -->
                            </select>
                        </div>
                    </div>

                    <!-- Diagnosis -->
                    <div class="form-group">
                        <label for="diagnosis">Diagnosis</label>
                        <textarea id="diagnosis" name="diagnosis" class="form-control" required><?= $profile['diagnosis'] ?? ''; ?></textarea>
                    </div>

                    <div class="row">
                        <!-- Petsa Ng Diagnosis -->
                        <div class="form-group col-md-4">
                            <label for="petsaNgDiagnosis">Petsa Ng Diagnosis (Buwan, Araw, Taon)</label>
                            <input type="date" id="petsaNgDiagnosis" name="petsaNgDiagnosis" class="form-control" required value="<?= $profile['petsaNgDiagnosis'] ?? ''; ?>">
                        </div>

                        <!-- Pangalan ng Developmental Pediatrician -->
                        <div class="form-group col-md-4">
                            <label for="DevelopPedia">Pangalan ng Developmental Pediatrician</label>
                            <input type="text" id="DevelopPedia" name="DevelopPedia" class="form-control" required value="<?= $profile['DevelopPedia'] ?? ''; ?>">
                        </div>

                        <!-- Pangalan at Edad ng Kapatid -->
                        <div class="form-group col-md-4">
                            <label for="Kapatid">Pangalan at Edad ng Kapatid (Pangalan-Edad)</label>
                            <input type="text" id="Kapatid" name="Kapatid" class="form-control" required value="<?= $profile['Kapatid'] ?? ''; ?>">
                        </div>

                        <div class="container mt-3 text-right">
                        <button type="submit" class="btn btn-primary">I-save ang Impormasyon</button>
                        <a href="profile_page2.php" class="btn btn-secondary">Next Page</a>

                    </div>                    
                </form>


                <?php if (!empty($updateMessage)): ?>
                    <div class="alert alert-info mt-3">
                        <?= $updateMessage; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
       
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show the modal when the page loads
            $('#confirmModal').modal('show');

            // Disable form inputs until confirmation
            $('form :input').prop('disabled', true);

            // Enable form inputs if the user clicks "Proceed"
            $('#proceedButton').click(function() {
                $('form :input').prop('disabled', false);
                $('#confirmModal').modal('hide');
            });

            // Optional: Handle the "Cancel" button click (redirect if needed)
            $('#cancelButton').click(function() {
                window.location.href = 'parent_dashboard.php'; // Change to your desired redirect URL
            });
        });
    </script>

</body>
</html>
