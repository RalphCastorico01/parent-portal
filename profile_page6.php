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
                <h3 class="mb-0">Kasaysayan pang Sikolohikal</h3>
            </div>
            <div class="card-body">
                <form action="profile_page6.php" method="POST">

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="estado">Nagkaroon ba ng kahirapan ang iyong anak sa mga sumusunod?
                            </label><br>

                            <?php
                            // Decode the JSON data into an associative array
                            // $medDiagStatus = json_decode($profile['SS'], true);

                            // Define an array of checkbox options
                            $checkboxes = [
                                'AX' => 'Anxiety',
                                'DecM' => 'Decreased motivation',
                                'DepM' => 'Depressed mood',
                                'DZ' => 'Dizziness',
                                'FH' => 'Feeling helpless',
                                'OT' => 'Obsessive thoughts',
                                'RH' => 'Racing heart',
                                'SB' => 'Shortness of breath',
                                'S' => 'Stress',
                            ];

                            // Loop through the checkbox options
                            foreach ($checkboxes as $key => $label) {
                                $checked = isset($medDiag[$key]) && $medDiag[$key] ? 'checked' : '';
                                echo "<input type='checkbox' id='$key' name='$key' $checked>";
                                echo "<label for='$key'>$label</label><br>";
                            }
                            ?>
                            <!-- <div style="display: flex; align-items: center;">
                                <input type='checkbox' id='otherCheckbox' name='parents_member[Other]' onclick="toggleOtherCheckboxInput()" <?= isset($parentsMember['Other']) && $parentsMember['Other'] ? 'checked' : ''; ?>>
                                <label for='otherCheckbox'>Other</label>
                                <input type='text' id='other_input' name='other_input' style='display: none; margin-left: 10px;' placeholder='Please specify' value="<?= htmlspecialchars($otherInput) ?>">
                            </div> -->
                        </div>

                        <div class="form-group col-md-6">
                            <label for="Dok">Pakispecify kung kailan nagaganap ang kahirapang ito.
                            </label>
                            <input type="text" id="Dok" name="Dok" class="form-control" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="estado">Nagkaroon ba ng kahirapan ang iyong anak sa mga sumusunod?
                            </label><br>

                            <?php
                            // Decode the JSON data into an associative array
                            // $medDiagStatus = json_decode($profile['SS'], true);

                            // Define an array of checkbox options
                            $checkboxes = [
                                'repB' => 'Repetitive behaviors',
                                'repVo' => 'Repetitive vocalizations',
                                'ObB' => 'Obsessive behaviors',
                                'SiB' => 'Self-injurious behaviors',
                            ];

                            // Loop through the checkbox options
                            foreach ($checkboxes as $key => $label) {
                                $checked = isset($medDiag[$key]) && $medDiag[$key] ? 'checked' : '';
                                echo "<input type='checkbox' id='$key' name='$key' $checked>";
                                echo "<label for='$key'>$label</label><br>";
                            }
                            ?>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="Dok">Pakipaliwanag at ilarawan ang iyong sagot.
                            </label>
                            <input type="text" id="Dok" name="Dok" class="form-control" required>
                        </div>


                        <div class="form-group col-md-6">
                            <label><br>Nagkaroon ba ng anumang delay ang iyong anak sa pag-abot ng mga developmental milestone?
                                <br><br>How often does she or he have difficulty staying organized?</label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he have problems remembering things?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he fidget or squirm when required to stay seated?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>
                            
                            <label>How often does she or he make careless mistakes?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he have difficulty paying attention during boring or repetitive tasks?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he misplace items?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he distracted?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he interrupt others who are talking?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he have trouble unwinding after an activity or day?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he have trouble waiting his/her turn?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
                            </div>

                            <label>How often does she or he appear to "space out"?
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Never
                                </label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Rarely</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Often</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Always</label>
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
            <a href="profile_page5.php" class="btn btn-secondary">← Back to Page 5</a>
            <button type="submit" class="btn btn-primary">I-save ang Impormasyon</button>
            <a href="profile_page7.php" class="btn btn-secondary">Next Page 7</a>
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