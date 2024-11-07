<?php
require 'db_connection.php';
session_start(); // Ensure session is started to use session variables

$updateMessage = ""; // Initialize the update message variable

// Fetch existing profile data for the logged-in user
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM profile1 WHERE username = :username");
$stmt->execute([':username' => $username]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission for updating profile information
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medDiagArray = [
        'ADHD' =>  isset($_POST['ADHD']),
        'ASD' =>  isset($_POST['ASD']),
        'CP' =>  isset($_POST['CP']),
        'DS' =>  isset($_POST['DS']),
        'E' =>  isset($_POST['E']),
        'GDD' =>  isset($_POST['GDD']),
        'HL' =>  isset($_POST['HL']),
        'Hydro' =>  isset($_POST['HYDRO']),
        'ID' =>  isset($_POST['ID']),
        'LangD' =>  isset($_POST['LangD']),
        'LearnD' => isset($_POST['LearnD']),
        'SD' =>  isset($_POST['SD'])
    ];

    // Check if the "Other" checkbox is selected and get the input value
    // if (isset($_POST['parents_status']['Other'])) {
    //     $parentsMemberArray['Other'] = true;
    // } else {
    //     $parentsMemberArray['Other'] = false;
    // }
    // $otherInput = $_POST['other_input'] ?? ''; // Get the value of the 'Other' input

    // Encode to JSON
    $medDiagJson = json_encode($medDiagArray);
    // $parentsMemberJson = json_encode($parentsMemberArray);

    if (isset($_FILES["ass_anak"]) && $_FILES["ass_anak"]["error"] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Ensure this directory exists and is writable
        $target_file = $target_dir . basename($_FILES["ass_anak"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file size (limit to 5MB)
        if ($_FILES["ass_anak"]["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats (e.g., jpg, png, pdf)
        if ($fileType != "jpg" && $fileType != "png" && $fileType != "pdf") {
            echo "Sorry, only JPG, PNG & PDF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            // If everything is ok, try to upload the file
            if (move_uploaded_file($_FILES["ass_anak"]["tmp_name"], $target_file)) {
                $file_path = $target_file; // Store file path for database insertion
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    $sql = "UPDATE profile1
            SET dok_name = :dok_name, ospi_name = :ospi_name, no_doc = :no_doc, med_test = :med_test, ass_anak = :ass_anak, med_diag = :med_diag
            WHERE username = :username";
    $stmt = $pdo->prepare($sql);

    try {
        // Bind form data to SQL statement
        $stmt->execute([
            ':dok_name' => $_POST['Dok'],
            ':ospi_name' => $_POST['Ospi_Name'],
            ':no_doc' => $_POST['no_doc'],
            ':med_test' => $_POST['med_test'],
            ':ass_anak' => $_POST['ass_anak'],
            ':med_diag' => $medDiagJson,
        ]);
        $updateMessage = $stmt->rowCount() ? "Profile updated successfully!" : "Error updating profile.";
    } catch (PDOException $e) {
        $updateMessage = "Error updating profile: " . $e->getMessage();
    }
}

// Fetch announcements
try {
    $stmt = $pdo->query("SELECT title, body, date FROM announcements ORDER BY date DESC");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching announcements: " . $e->getMessage();
}

// Fetch requests for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM requests WHERE username = :username ORDER BY date DESC");
$stmt->execute([':username' => $_SESSION['username']]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming events
try {
    $stmt = $pdo->query("SELECT eventid, title, description, date, time, location, type, max_slots FROM events ORDER BY date ASC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching events: " . $e->getMessage();
}

// Sort requests by date
usort($requests, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Limit displayed requests
$displayedRequests = array_slice($requests, 0, 3);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impormasyon ng Tagapangalaga</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Your custom CSS -->
</head>

<body>
    <?php include 'parent_sidebar.php'; ?>

    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-white text-dark">
                <h3 class="mb-0">Kasaysayan ng Pamilya</h3>
            </div>
            <div class="card-body">
                <form action="profile_page4.php" method="POST">

                    <div class="row">
                     
                        <div class="form-group col-md-6">
                            <label for="Dok">Sino ang araw-araw na tagapag-alaga ng iyong anak?
                            </label>
                            <input type="text" id="Dok" name="Dok" class="form-control" required>
                        </div>

                     
                        <div class="form-group col-md-6">
                            <label for="Ospi_Name">Pakilista ang lahat ng tao na kasalukuyang nakatira sa parehong tahanan ng iyong anak (Pangalan-Edad-Relasyon sa bata)</label>
                            <input type="text" id="Ospi_Name" name="Ospi_Name" class="form-control" required>
                        </div>

                    
                        <div class="form-group col-md-6">
                            <label for="no_doc">May kasaysayan ba ng mga neurodevelopmental na disorder sa malapit na pamilya (mga magulang o kapatid)? Kung oo, pakipaliwanag.</label>
                            <input type="text" id="no_doc" name="no_doc" class="form-control" required>
                        </div>
                    </div>

                    </form>
                    <?php if (!empty($updateMessage)): ?>
                        <div class="alert alert-info mt-3">
                            <?= $updateMessage; ?>
                        </div>
                    <?php endif; ?>
            </div>
        </div>
        <div class="container mt-3 text-center">
            <a href="profile_page3.php" class="btn btn-secondary">← Back to Page 3</a>
            <button type="submit" class="btn btn-primary">I-save ang Impormasyon</button>
            <a href="profile_page5.php" class="btn btn-secondary">Next Page 5</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function toggleOtherCheckboxInput() {
            var otherCheckbox = document.getElementById('otherCheckbox');
            var otherCheckboxInput = document.getElementById('other_input');

            // Show the text input if the "Other" checkbox is selected
            otherCheckboxInput.style.display = otherCheckbox.checked ? 'block' : 'none';
            if (!otherCheckbox.checked) {
                otherCheckboxInput.value = ''; // Clear input if "Other" is not selected
            }
        }

        function toggleOtherRadioInput() {
            var otherRadio = document.getElementById('otherRadio');
            var otherRadioInput = document.getElementById('otherRadioInput');

            // Show the text input if the "Other" radio button is selected
            otherRadioInput.style.display = otherRadio.checked ? 'block' : 'none';
            if (!otherRadio.checked) {
                otherRadioInput.value = ''; // Clear input if "Other" is not selected
            }
        }

        // Ensure the text inputs are shown if "Other" was selected previously
        document.addEventListener('DOMContentLoaded', function() {
            var otherCheckbox = document.getElementById('otherCheckbox');
            var otherCheckboxInput = document.getElementById('other_input');
            if (otherCheckbox.checked) {
                otherCheckboxInput.style.display = 'block';
            }

            var otherRadio = document.getElementById('otherRadio');
            var otherRadioInput = document.getElementById('otherRadioInput');
            if (otherRadio.checked) {
                otherRadioInput.style.display = 'block';
            }
        });
    </script>
</body>

</html>