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
    $parentsStatusArray = [
        'Kasal' => isset($_POST['Kasal']),
            'LivingTogether' => isset($_POST['LivingTogether']),
            'separated' => isset($_POST['separated']),
            'annulled' => isset($_POST['annulled']),
            'Biyuda_o' => isset($_POST['Biyuda_o']),
    ];
    
    $parentsMemberArray = [
        'SSS' => isset($_POST['SSS']),
            'PWD' => isset($_POST['PWD']),
            '4Ps' => isset($_POST['4Ps']),
            'SeniorCitizen' => isset($_POST['SeniorCitizen']),
            'PAGIBIG' => isset($_POST['PAGIBIG']),
            'GSIS' => isset($_POST['GSIS']),
            'SoloParent' => isset($_POST['SoloParent']),
    ];

    // Check if the "Other" checkbox is selected and get the input value
    if (isset($_POST['parents_status']['Other'])) {
        $parentsMemberArray['Other'] = true;
    } else {
        $parentsMemberArray['Other'] = false;
    }
    $otherInput = $_POST['other_input'] ?? ''; // Get the value of the 'Other' input

    // Encode to JSON
    $parentsStatusJson = json_encode($parentsStatusArray);
    $parentsMemberJson = json_encode($parentsMemberArray);

    $sql = "UPDATE profile1
            SET ina_name = :ina_name, ina_contact = :ina_contact, ina_job = :ina_job,
                ama_name = :ama_name, ama_contact = :ama_contact, ama_job = :ama_job,
                trabaho = :trabaho, guardian_name = :guardian_name, guardian_contact = :guardian_contact, guardian_job = :guardian_job,
                parents_status = :parents_status, parents_member = :parents_member, other_input = :other_input, kita = :kita, teletheraphy = :teletheraphy,
                status_years = :status_years, step_parents = :step_parents
            WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    
    try{
    // Bind form data to SQL statement
    $stmt->execute([
        ':ina_name' => $_POST['Ina'],
        ':ina_contact' => $_POST['no_Ina'],
        ':ina_job' => $_POST['job_Ina'],
        ':ama_name' => $_POST['Ama'],
        ':ama_contact' => $_POST['no_Ama'],
        ':ama_job' => $_POST['job_Ama'],
        ':trabaho' => $_POST['trabaho'],
        ':guardian_name' => $_POST['Tagapag-alaga_Name'],
        ':guardian_contact' => $_POST['Tagapag-alaga_Contact'],
        ':guardian_job' => $_POST['Tagapag-alaga_HB'],
        ':parents_status' => $parentsStatusJson,
        ':parents_member' => $parentsMemberJson,
        ':other_input' => $otherInput,
        ':status_years' => $_POST['estado_Taon'],
        ':step_parents' => $_POST['Step-parents'],
        ':kita' => $_POST['kita'], 
        ':teletheraphy' => $_POST['teletheraphy'],
        ':username' => $username,
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
                <h3 class="mb-0">Impormasyon ng Tagapangalaga</h3>
            </div>
            <div class="card-body">
                <form action="profile_page2.php" method="POST">

                    <div class="row">
                        <!-- Pangalan ng Ina -->
                        <div class="form-group col-md-4">
                            <label for="Ina">Buong Pangalan ng Ina</label>
                            <input type="text" id="Ina" name="Ina" class="form-control" required value="<?= htmlspecialchars($profile['ina_name'] ?? '') ?>">
                        </div>

                        <!-- Contact Number ng Ina -->
                        <div class="form-group col-md-3">
                            <label for="no_Ina">Contact Number ng Ina</label>
                            <input type="text" id="no_Ina" name="no_Ina" class="form-control" required value="<?= htmlspecialchars($profile['ina_contact'] ?? '') ?>">
                        </div>

                        <!-- Hanap Buhay ng Ina -->
                        <div class="form-group col-md-3">
                            <label for="job_Ina">Hanap Buhay ng Ina</label>
                            <input type="text" id="job_Ina" name="job_Ina" class="form-control" required value="<?= htmlspecialchars($profile['ina_job'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Pangalan ng Ama -->
                        <div class="form-group col-md-4">
                            <label for="Ama">Buong Pangalan ng Ama</label>
                            <input type="text" id="Ama" name="Ama" class="form-control" required value="<?= htmlspecialchars($profile['ama_name'] ?? '') ?>">
                        </div>

                        <!-- Contact Number ng Ama -->
                        <div class="form-group col-md-3">
                            <label for="no_Ama">Contact Number ng Ama</label>
                            <input type="text" id="no_Ama" name="no_Ama" class="form-control" required value="<?= htmlspecialchars($profile['ama_contact'] ?? '') ?>">
                        </div>

                        <!-- Hanap Buhay ng Ama -->
                        <div class="form-group col-md-3">
                            <label for="job_Ama">Hanap Buhay ng Ama</label>
                            <input type="text" id="job_Ama" name="job_Ama" class="form-control" required value="<?= htmlspecialchars($profile['ama_job'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Parehong magulang ay may Trabaho -->
                        <div class="form-group col-md-6">
                            <label>Parehong magulang ay may Trabaho</label><br>
                            <input type="radio" id="Oo" name="trabaho" value="Oo" <?= ($profile['trabaho'] == 'Oo') ? 'checked' : '' ?> required>
                            <label for="Oo">Oo</label><br>
                            <input type="radio" id="Hindi" name="trabaho" value="Hindi" <?= ($profile['trabaho'] == 'Hindi') ? 'checked' : '' ?> required>
                            <label for="Hindi">Hindi</label><br>
                            <input type="radio" id="SoloParent" name="trabaho" value="SoloParent" <?= ($profile['trabaho'] == 'SoloParent') ? 'checked' : '' ?> required>
                            <label for="SoloParent">Solo Parent</label><br>
                        </div>


                        <!-- Buong Pangalan ng Tagapag-alaga -->
                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_Name">(Sasagutin lamang kung ang mga magulang ang hindi pangunahing tagapag-alaga ng Kliyente).<br><br> Pangalan ng Tagapag-alaga</label>
                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" required value="<?= htmlspecialchars($profile['guardian_name'] ?? '') ?>">
                        </div>


                        <!-- Contact Number ng Tagapag-alaga -->
                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_Contact">(Sasagutin lamang kung ang mga magulang ang hindi pangunahing tagapag-alaga ng Kliyente).<br><br> Contact Number ng Tagapag-alaga</label>
                            <input type="text" id="Tagapag-alaga_Contact" name="Tagapag-alaga_Contact" class="form-control" required value="<?= htmlspecialchars($profile['guardian_contact'] ?? '') ?>">
                        </div>

                        <!-- Hanap Buhay ng Tagapag-alaga -->
                        <div class="form-group col-md-6">
                            <label for="Tagapag-alaga_HB">(Sasagutin lamang kung ang mga magulang ang hindi pangunahing tagapag-alaga ng Kliyente).<br><br> Hanap Buhay ng Tagapag-alaga</label>
                            <input type="text" id="Tagapag-alaga_HB" name="Tagapag-alaga_HB" class="form-control" required value="<?= htmlspecialchars($profile['guardian_job'] ?? '') ?>">
                        </div>

                         <div class="form-group col-md-6">
                            <label for="estado">Ano ang kasalukuyang estado ng relasyon ng mga biological na magulang?</label><br>

                            <?php
                            // Decode the JSON data into an associative array
                            $parentsStatus = json_decode($profile['parents_status'], true);
                            
                            // Define an array of checkbox options
                            $checkboxes = [
                                'Kasal' => 'Kasal',
                                'LivingTogether' => 'Living Together (Common law relation)',
                                'separated' => 'Separated',
                                'annulled' => 'Annulled',
                                'Biyuda_o' => 'Biyuda/Biyudo',
                            ];
                            
                            // Loop through the checkbox options
                            foreach ($checkboxes as $key => $label) {
                                $checked = isset($parentsStatus[$key]) && $parentsStatus[$key] ? 'checked' : '';
                                echo "<input type='checkbox' id='$key' name='$key' $checked>";
                                echo "<label for='$key'>$label</label><br>";
                            }
                            ?>
                        </div>
                        <!-- Taon ng Estado ng biological na magulang -->
                        <div class="form-group col-md-6">
                            <label for="estado_Taon">Ilang Taon ng nasa ganitong estado ang mga biological na magulang? </label>
                            <input type="text" id="estado_Taon" name="estado_Taon" class="form-control" required value="<?= htmlspecialchars($profile['status_years'] ?? '') ?>">
                        </div>

                        <!-- Step-parents -->
                        <div class="form-group col-md-6">
                            <label for="Step-parents">Kung hiwalay ang mga magulang, paki lista ng pangalan ng step-parents. </label>
                            <input type="text" id="Step-parents" name="Step-parents" class="form-control" required value="<?= htmlspecialchars($profile['step_parents'] ?? '') ?>">
                        </div>

                        <!-- Miyembro ng sumusunod -->
                        <div class="form-group col-md-6">
                            <label for="estado">Ang magulang/tagapangalaga ba ay miyembro ng mga sumusunod?</label><br>
                            <?php
                            // Decode the JSON data into an associative array
                            $parentsMember = json_decode($profile['parents_member'], true);
                            // Define an array of checkbox options
                            $checkboxes = [
                                'SSS' => 'SSS', 'PWD' => 'PWD',
                                '4Ps' => '4Ps',
                                'SeniorCitizen' => 'Senior Citizen',
                                'PAGIBIG' => 'PAG IBIG',
                                'GSIS' => 'GSIS',
                                'SoloParent' => 'Solo Parent',
                            ];
                            // Loop through the checkbox options
                            foreach ($checkboxes as $key => $label) {
                                $checked = isset($parentsMember[$key]) && $parentsMember[$key] ? 'checked' : '';
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

                         <!-- Buwanang Kita ng Pamilya -->
                         <div class="form-group col-md-6">
                            <label>Kabuuang Buwanang Kita ng Pamilya</label><br>
                            <input type="radio" id="L14k" name="kita" value="L14k" <?= ($profile['kita'] == 'L14k') ? 'checked' : '' ?>>
                            <label for="L14k">Lower Than P14,000</label><br>
                            <input type="radio" id="P14k-P19k" name="kita" value="P14k-P19k" <?= ($profile['kita'] == 'P14k-P19k') ? 'checked' : '' ?>>
                            <label for="P14k-P19k">P14,001 - P19,040</label><br>
                            <input type="radio" id="P19k-P38k" name="kita" value="P19k-P38k" <?= ($profile['kita'] == 'P19k-P38k') ? 'checked' : '' ?>>
                            <label for="P19k-P38k">P19,041-P38,080</label><br>
                            <input type="radio" id="P38k-P66k" name="kita" value="P38k-P66k" <?= ($profile['kita'] == 'P38k-P66k-') ? 'checked' : '' ?>>
                            <label for="P38k-P66k">P38,041-P66,640</label><br>
                            <input type="radio" id="P66k-P114k" name="kita" value="P66k-P114k" <?= ($profile['kita'] == 'P66k-P114k') ? 'checked' : '' ?>>
                            <label for="P66k-P114k">P66,541-P114,240</label><br>
                            <input type="radio" id="P114k-P190k" name="kita" value="P114k-P190k" <?= ($profile['kita'] == 'P114k-P190k') ? 'checked' : '' ?>>
                            <label for="P114k-P190k">P114,241-P190,400</label><br>
                            <input type="radio" id="P190k" name="kita" value="P190k" <?= ($profile['kita'] == 'P190k') ? 'checked' : '' ?>>
                            <label for="P190k">P190,401 and above</label><br>
                        </div>

                         <!-- SNED Allowance -->
                         <div class="form-group col-md-6">
                            <label>Kayo ba ay tumatanggap ng SNED allowance mula sa paaralan?</label><br>
                            <input type="radio" id="Oo" name="SNED" value="Oo" <?= ($profile['SNED'] == 'Oo') ? 'checked' : '' ?> required>
                            <label for="Oo">Oo</label><br>
                            <input type="radio" id="Hindi" name="SNED" value="Hindi" <?= ($profile['SNED'] == 'Hindi') ? 'checked' : '' ?> required>
                            <label for="Hindi">Hindi</label><br>
                        </div>
                        <!-- Teletherapy -->
                        <div class="form-group col-md-6">
                            <label>Resources para sa pagpapatupad ng teletheraphy</label><br>
                            <input type="radio" id="Wifi" name="teletheraphy" value="Wifi" <?= ($profile['teletheraphy'] == 'Wifi') ? 'checked' : '' ?> required>
                            <label for="Wifi">Wifi</label><br>
                            <input type="radio" id="PD" name="teletheraphy" value="PD" <?= ($profile['teletheraphy'] == 'PD') ? 'checked' : '' ?> required>
                            <label for="PD">Prepaid Data (Cellphone)</label><br>
                            <input type="radio" id="PW" name="teletheraphy" value="PW" <?= ($profile['teletheraphy'] == 'PW') ? 'checked' : '' ?> required>
                            <label for="PW">Pocket Wifi</label><br>
                            
                            <div style="display: flex; align-items: center;">
                                <input type='radio' id='otherRadio' name='teletheraphy' value='Other' <?= ($profile['teletheraphy'] == 'Other') ? 'checked' : '' ?> onclick="toggleOtherRadioInput()">
                                <label for='otherRadio'>Other</label>
                                <input type='text' id='otherRadioInput' name='otherRadioInput' style='display: none; margin-left: 10px;' placeholder='Please specify' value="<?= htmlspecialchars($profile['otherRadioInput'] ?? '') ?>">
                            </div>
                        </div>
                    <button type="submit" class="btn btn-primary btn-block">I-save ang Impormasyon</button>
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
        <a href="profile_page1.php" class="btn btn-secondary">← Back to Page 1</a>
        <a href="profile_page3.php" class="btn btn-secondary">Next Page 3</a>
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
document.addEventListener('DOMContentLoaded', function () {
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