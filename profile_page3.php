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
                <h3 class="mb-0">Kasaysayan ng Medikal</h3>
            </div>
            <div class="card-body">
                <form action="profile_page3.php" method="POST">

                    <div class="row">
                        <!-- Pangalan ng Doktor -->
                        <div class="form-group col-md-4">
                            <label for="Dok">Pangalan ng doktor ng iyong anak</label>
                            <input type="text" id="Dok" name="Dok" class="form-control" required>
                        </div>

                        <!-- Pangalan ng Ospital -->
                        <div class="form-group col-md-3">
                            <label for="Ospi_Name">Pangalan ng Ospital</label>
                            <input type="text" id="Ospi_Name" name="Ospi_Name" class="form-control" required>
                        </div>

                        <!-- Contact Number ng Doktor -->
                        <div class="form-group col-md-3">
                            <label for="no_doc">Contact Number ng Doktor</label>
                            <input type="text" id="no_doc" name="no_doc" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Test - Date - Results -->
                        <div class="form-group col-md-4">
                            <label for="med_test">Pakilista ang anumang psychological or medical testing na tapos ng iyong anak (Test-Date-Results)</label>
                            <input type="text" id="med_test" name="med_test" class="form-control" required>
                        </div>

                        <!-- Assesment ng Anak -->
                        <div class="form-group col-md-3">
                            <label for="ass_anak">Note: Ilagay ang kopya ng assesment ng iyong anak.</label>
                            <input type="file" id="ass_anak" name="ass_anak" class="form-control-file" required>
                            <!-- <button type="submit" class="btn btn-primary btn-block">Submit File</button> -->
                        </div>

                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="estado">Medical diagnosis/clinical impression ng iyong anak:</label><br>

                            <?php
                            // Decode the JSON data into an associative array
                            $medDiagStatus = json_decode($profile['med_diag'] ?? '[]', true);

                            // Define an array of checkbox options
                            $checkboxes = [
                                'ADHD' => 'ADHD',
                                'ASD' => 'Autism Spectrum Disorder',
                                'CP' => 'CelebralPalsy',
                                'DS' => 'DownSyndrome',
                                'E' => 'Epilepsy',
                                'GDD' => 'Global Developmental Delay',
                                'HL' => 'Hearing Loss',
                                'Hydro' => 'Hydrocephalus',
                                'ID' => 'Intellectual Disability',
                                'LangD' => 'Language Disorder',
                                'LearnD' => 'Learning Disability',
                                'SD' => 'Speech Delay',
                            ];

                            // Loop through the checkbox options
                            foreach ($checkboxes as $key => $label) {
                                $checked = isset($medDiag[$key]) && $medDiag[$key] ? 'checked' : '';
                                echo "<input type='checkbox' id='$key' name='$key' $checked>";
                                echo "<label for='$key'>$label</label><br>";
                            }
                            ?>
                            <div style="display: flex; align-items: center;">
                                <input type='checkbox' id='otherCheckbox' name='parents_member[Other]' onclick="toggleOtherCheckboxInput()" <?= isset($parentsMember['Other']) && $parentsMember['Other'] ? 'checked' : ''; ?>>
                                <label for='otherCheckbox'>Other</label>
                                <input type='text' id='other_input' name='other_input' style='display: none; margin-left: 10px;' placeholder='Please specify' value="<?= htmlspecialchars($otherInput) ?>">
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="estado">Naranasan ba ng iyong anak ang alinman sa mga sumusunod na problema sa kalusugan?</label><br>

                            <?php
                            // Decode the JSON data into an associative array
                            $parentsStatus = json_decode($profile['kalusugan'] ?? '[]', true);

                            // Define an array of checkbox options
                            $checkboxes = [
                                'AL' => 'Allergies',
                                'CV/S' => 'Convulsions/Seizures',
                                'HI' => 'Head Injury',
                                'HearingP' => 'Hearing Problem',
                                'HeartP' => 'Heart Problem',
                                'LD' => 'Lung condition (ex. asthma, pneumonia',
                                'MI' => 'Mental Illness',
                                'PI' => 'Physical Illness',
                                'S' => 'Surgery',
                                'V/BI' => 'Viral/Bacterial infections',
                                'VP' => 'Vision Problem'
                            ];

                            // Loop through the checkbox options
                            foreach ($checkboxes as $key => $label) {
                                // change the parentsStatus into another array sa taas
                                $checked = isset($parentsStatus[$key]) && $parentsStatus[$key] ? 'checked' : '';
                                echo "<input type='checkbox' id='$key' name='$key' $checked>";
                                echo "<label for='$key'>$label</label><br>";
                            }
                            ?>
                            <div style="display: flex; align-items: center;">
                                <input type='checkbox' id='otherCheckbox' name='parents_member[Other]' onclick="toggleOtherCheckboxInput()" <?= isset($parentsMember['Other']) && $parentsMember['Other'] ? 'checked' : ''; ?>>
                                <label for='otherCheckbox'>Other</label>
                                <input type='text' id='other_input' name='other_input' style='display: none; margin-left: 10px;' placeholder='Please specify' value="<?= htmlspecialchars($otherInput) ?>">
                            </div>
                        </div>

                        <!-- Nabanggit na problema sa kalusugan -->
                        <div class="form-group col-md-6">
                            <label for="issue_kalusugan">Pakipaliwanag ang anumang mga nabanggit na problema sa kalusugan: </label>
                            <input type="text" id="issue_kalusugan" name="issue_kalusugan" class="form-control" required>
                        </div>

                        <!-- Prenatal -->
                        <div class="form-group col-md-6">
                            <label><br>Prenatal
                                <br>Nagkaroon ba ng regular na check-up ang ina?</label><br>
                            <input type="radio" id="Oo" name="ina_check" value="Oo" <?= ($profile['ina_check'] ?? '' ) == 'Oo' ? 'checked' : '' ?> required>
                            <label for="Oo">Oo</label><br>
                            <input type="radio" id="Hindi" name="ina_check" value="Hindi" <?= ($profile['ina_check'] ?? '' ) == 'Hindi' ? 'checked' : '' ?> required>
                            <label for="Hindi">Hindi</label><br>
                        </div>

                        <!-- Aktibidad nung panahon ng nagbubuntis -->
                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_Name"><br>Prenatal
                                <br>Ano ang mga aktibidad ng ina sa panahon ng pagbubuntis? (nagtratrabaho)</label>
                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                        </div>

                        <!-- Komplikasyhon sa gamot -->
                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_Name"><br>Prenatal
                                <br>Nagkaroon ba ng mga komplikasyon, gamot, o anumang iba pang mahalagang pangyayari sa panahon ng pagbubuntis</label>
                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                        </div>

                        <!-- Edad ng ina nang isinilang ang bata-->
                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_Name"><br>Delivery
                                <br>Edad ng ina nang isinilang ang bata</label>
                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label><br>Delivery
                                <br>Age of gestation</label><br>
                            <input type="radio" id="Premature" name="gestation" value="Premature" <?= ($profile['gestation'] ?? '' ) == 'Premature' ? 'checked' : '' ?> required>
                            <label for="Premature">Premature (less than 37 weeks gestation)</label><br>
                            <input type="radio" id="Fullterm" name="gestation" value="Fullterm" <?= ($profile['gestation'] ?? '' ) == 'Fullterm' ? 'checked' : '' ?> required>
                            <label for="Fullterm">Full-term(37 to 42 weeks gestation)</label><br>
                            <input type="radio" id="Postterm" name="gestation" value="Postterm" <?= ($profile['gestation'] ?? '' ) == 'Postterm' ? 'checked' : '' ?> required>
                            <label for="Postterm">Postterm (born after 42 weeks gestation)</label><br>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Delivery</label><br>
                            <input type="radio" id="norm_delivery" name="delivery" value="norm_delivery" <?= ($profile['delivery']?? '' ) == 'norm_delivery' ? 'checked' : '' ?> required>
                            <label for="norm_delivery">Normal Delivery</label><br>
                            <input type="radio" id="Cesarean" name="delivery" value="Cesarean" <?= ($profile['delivery'] ?? '' ) == 'Cesarean' ? 'checked' : '' ?> required>
                            <label for="Cesarean">Cesarean Delivery</label><br>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_Name"><br>Delivery
                                <br>Nagkaroon ba ng mga komplikasyon, gamot, o anumang iba pang mahalagang pangyayari sa panahon ng panganak?</label>
                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                        </div>

                        <!-- Post-Natal -->
                        <div class="form-group col-md-6">
                            <label><br>Postnatal
                                <br>Na-admit ba ang bata sa Neonatal Intensive Care Unit (NICU)</label><br>
                            <input type="radio" id="Oo" name="PN_admit" value="Oo" <?= ($profile['PN_admit']?? '' ) == 'Oo' ? 'checked' : '' ?> required>
                            <label for="Oo">Oo</label><br>
                            <input type="radio" id="Hindi" name="PN_admit" value="Hindi" <?= ($profile['PN_admit'] ?? '' ) == 'Hindi' ? 'checked' : '' ?> required>
                            <label for="Hindi">Hindi</label><br>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_Name">Kung oo, ilarawan ang dahilan ng pag-admit (Tagal-Dahilan-Interbensyon)</label>
                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_Name">May mga komplikasyon, gamot, o iba pang mahalagang pangyayari ba a panahon pagkatapos ng panganganak? Kung oo, pakilarawan ang mga ito.
                            </label>
                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label><br>Nagkaroon ba ng anumang delay ang iyong anak sa pag-abot ng mga developmental milestone?
                                <br>Rolled over consistently</label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Sat up unsupported
                            </label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Stood up
                            </label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Crawled
                            </label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Walked unassisted
                            </label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Said 1st intelligible words
                            </label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Said 2-3 word phrases</label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Used sentences regularly</label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Potty trained</label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>

                            <label>Dressed self independently</label>
                            <div>
                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                <label for="rolled_over_oo">Oo</label>
                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                <label for="rolled_over_hindi">Hindi</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="Tagapag-alaga_Name">Anong mga bakuna/immunization ang mayroon ang iyong anak? (ex. Covid vaccine, Hepatites A&B, Polio, Influenza, Measles, Chickenpox)
                        </label>
                        <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Umiinom ba ng anumang gamot ang iyong anak?</label><br>
                        <input type="radio" id="Oo" name="a_gamot" value="Oo" <?= ($profile['a_gamot'] ?? '' ) == 'Oo' ? 'checked' : '' ?> required>
                        <label for="Oo">Oo</label><br>
                        <input type="radio" id="Hindi" name="a_gamot" value="Hindi" <?= ($profile['a_gamot'] ?? '' ) == 'Hindi' ? 'checked' : '' ?> required>
                        <label for="Hindi">Hindi</label><br>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="Tagapag-alaga_Name">Pakilista ang anumang gamot, bitamina, o suplemento na kasalukuyang iniinom ng iyong anak (Pangalan ng Gamot-Dosage-Tagal ng Panahon na iinumin ang gamot)
                        </label>
                        <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="Tagapag-alaga_Name">Ano ang dahilan ng gamot at gaano katagal nang umiinom ng gamot ang iyong anak?
                        </label>
                        <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="Tagapag-alaga_Name">Nakakatanggap ba ng therapy ang iyong anak dati? Kung oo, saan, gaano katagal, at bakit siya tumigil sa therapy?

                        </label>
                        <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required>
                    </div>

                    <div class="container mt-3 text-center">
                        <a href="profile_page2.php" class="btn btn-secondary">← Back to Page 2</a>
                        <button type="submit" class="btn btn-primary">I-save ang Impormasyon</button>
                        <a href="profile_page4.php" class="btn btn-secondary">Next Page 4</a>
                    </div>
                    <!-- </div> -->

                </form>
                <?php if (!empty($updateMessage)): ?>
                    <div class="alert alert-info mt-3">
                        <?= $updateMessage; ?>
                    </div>
                <?php endif; ?>
            </div>
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