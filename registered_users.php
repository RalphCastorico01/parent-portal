<?php
include 'db_connection.php';

// users variable indexes
$users = [];

// search variable
$searchValue = '';

// Check if a search term is submitted
if (isset($_POST['search'])) {
    $searchValue = $_POST['search_value'];

    // Fetch users from the database based on the search term
    $query = "SELECT id, username, email, role FROM users WHERE id LIKE :search_value OR username LIKE :search_value OR email LIKE :search_value OR role LIKE :search_value";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['search_value' => '%' . $searchValue . '%']);
} else {
    // Fetch all users if no search term is provided
    $query = "SELECT id, username, email, role FROM users";
    $stmt = $pdo->query($query);
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update the username if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $new_username = $_POST['username'];

    // Check if the new username already exists
    $checkQuery = "SELECT COUNT(*) FROM users WHERE username = :username AND id != :id";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute(['username' => $new_username, 'id' => $user_id]);
    $count = $checkStmt->fetchColumn();

    if ($count == 0) {
        // Update the username in the database
        $updateQuery = "UPDATE users SET username = :username WHERE id = :id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute(['username' => $new_username, 'id' => $user_id]);
        echo "<div class='alert alert-success'>Username updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>This username is already taken.</div>";
    }

    // Refresh the list of users after the update
    $stmt = $pdo->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="style_modal.css">
</head>

<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="container-fluid">
        <div class="content p-4">
            <h1 class="display-6 mb-4">Registered Users</h1>

            <!-- Button Container -->
            <div class="d-flex justify-content-between align-items-center mb-4 w-100">
                <!-- Refresh Button -->
                <form method="POST" class="mb-0">
                    <button type="submit" class="btn btn-outline-secondary" name="refresh">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </form>

                <!-- Search Form Container -->
                <div class="ms-auto">
                    <form method="POST" class="mb-0">
                        <div class="input-group" style="width: 250px;">
                            <input type="text" name="search_value" value="<?= htmlspecialchars($searchValue); ?>" class="form-control" placeholder="Search by Acc. ID, Username, Email, or Account Type" aria-label="Search">
                            <button type="submit" name="search" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"></th>
                            <th class="text-center">Username</th>
                            <th class="text-center">Parent Email</th>
                            <th class="text-center">Account Type</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($user['id']); ?></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" class="form-control me-2" readonly disabled style="width: 150px;">
                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateModal-<?= htmlspecialchars($user['id']); ?>">
                                            Update
                                        </button>
                                    </div>
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']); ?>">
                                </td>
                                <td class="text-center"><?= htmlspecialchars($user['email']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($user['role']); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#viewModal-<?= htmlspecialchars($user['id']); ?>">
                                        View Profile
                                    </button>
                                </td>
                            </tr>
            </div>

            <!-- Modal for Viewing User Profile -->
            <div class="modal fade" id="viewModal-<?= htmlspecialchars($user['id']); ?>" tabindex="-1" aria-labelledby="viewModalLabel-<?= htmlspecialchars($user['id']); ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewModalLabel-<?= htmlspecialchars($user['id']); ?>">User Profile: <?= htmlspecialchars($user['username']); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php
                            // Fetch profile data for this user
                            $username = $user['username'];
                            $profileStmt = $pdo->prepare("SELECT * FROM profile1 WHERE username = :username");
                            $profileStmt->execute([':username' => $username]);
                            $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);
                            ?>

                            <div id="modalPage1-<?= htmlspecialchars($user['id']); ?>" style="display: block;">
                                <form>
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Last Name:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['last_name'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>First Name:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['first_name'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Middle Name:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['middle_name'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Birthdate:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['birthdate'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-1">
                                            <label>Age:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['age'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Nationality:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['nationality'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Gender:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['gender'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Religion:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['religion'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>PWD ID:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['pwd_id'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-8">
                                            <label>Address:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['address'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Barangay:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['barangay'] ?? ''); ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Diagnosis:</label>
                                        <textarea class="form-control" readonly><?= htmlspecialchars($profile['diagnosis'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Petsa Ng Diagnosis:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['petsaNgDiagnosis'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label>Pangalan ng Developmental Pediatrician:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['DevelopPedia'] ?? ''); ?>" readonly>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Pangalan at Edad ng Kapatid:</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['Kapatid'] ?? ''); ?>" readonly>
                                        </div>

                                    </div>
                                </form>
                            </div>

                            <div id="modalPage2-<?= htmlspecialchars($user['id']); ?>" style="display: none;">
                                <form>
                                    <div class="row">
                                        <!-- Pangalan ng Ina -->
                                        <div class="form-group col-md-4">
                                            <label for="Ina">Buong Pangalan ng Ina</label>
                                            <input type="text" id="Ina" name="Ina" class="form-control" value="<?= htmlspecialchars($profile['ina_name'] ?? '') ?>" disabled>
                                        </div>

                                        <!-- Contact Number ng Ina -->
                                        <div class="form-group col-md-4">
                                            <label for="no_Ina">Contact Number ng Ina</label>
                                            <input type="text" id="no_Ina" name="no_Ina" class="form-control" value="<?= htmlspecialchars($profile['ina_contact'] ?? '') ?>" disabled>
                                        </div>

                                        <!-- Hanap Buhay ng Ina -->
                                        <div class="form-group col-md-3">
                                            <label for="job_Ina">Hanap Buhay ng Ina</label>
                                            <input type="text" id="job_Ina" name="job_Ina" class="form-control" value="<?= htmlspecialchars($profile['ina_job'] ?? '') ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Pangalan ng Ama -->
                                        <div class="form-group col-md-4">
                                            <label for="Ama">Buong Pangalan ng Ama</label>
                                            <input type="text" id="Ama" name="Ama" class="form-control" value="<?= htmlspecialchars($profile['ama_name'] ?? '') ?>" disabled>
                                        </div>

                                        <!-- Contact Number ng Ama -->
                                        <div class="form-group col-md-4">
                                            <label for="no_Ama">Contact Number ng Ama</label>
                                            <input type="text" id="no_Ama" name="no_Ama" class="form-control" value="<?= htmlspecialchars($profile['ama_contact'] ?? '') ?>" disabled>
                                        </div>

                                        <!-- Hanap Buhay ng Ama -->
                                        <div class="form-group col-md-3">
                                            <label for="job_Ama">Hanap Buhay ng Ama</label>
                                            <input type="text" id="job_Ama" name="job_Ama" class="form-control" value="<?= htmlspecialchars($profile['ama_job'] ?? '') ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Parehong magulang ay may Trabaho -->
                                        <div class="form-group col-md-6">
                                            <label>Parehong magulang ay may Trabaho</label><br>
                                            <input type="radio" id="Oo" name="trabaho" value="Oo" <?= ($profile['trabaho'] ?? '') == 'Oo' ? 'checked' : '' ?> disabled>
                                            <label for="Oo">Oo</label><br>
                                            <input type="radio" id="Hindi" name="trabaho" value="Hindi" <?= ($profile['trabaho'] ?? '') == 'Hindi' ? 'checked' : '' ?> disabled>
                                            <label for="Hindi">Hindi</label><br>
                                            <input type="radio" id="SoloParent" name="trabaho" value="SoloParent" <?= ($profile['trabaho'] ?? '') == 'SoloParent' ? 'checked' : '' ?> disabled>
                                            <label for="SoloParent">Solo Parent</label><br>
                                        </div>

                                        <!-- Buong Pangalan ng Tagapag-alaga -->
                                        <div class="form-group col-md-6">
                                            <label for="Tagapag_alaga_Name">(Sasagutin lamang kung ang mga magulang ang hindi pangunahing tagapag-alaga ng Kliyente).<br><br> Pangalan ng Tagapag-alaga</label>
                                            <input type="text" id="Tagapag_alaga_Name" name="Tagapag_alaga_Name" class="form-control" value="<?= htmlspecialchars($profile['guardian_name'] ?? '') ?>" disabled>
                                        </div>

                                        <!-- Contact Number ng Tagapag-alaga -->
                                        <div class="form-group col-md-6">
                                            <label for="Tagapag_alaga_Contact">(Sasagutin lamang kung ang mga magulang ang hindi pangunahing tagapag-alaga ng Kliyente).<br><br> Contact Number ng Tagapag-alaga</label>
                                            <input type="text" id="Tagapag_alaga_Contact" name="Tagapag_alaga_Contact" class="form-control" value="<?= htmlspecialchars($profile['guardian_contact'] ?? '') ?>" disabled>
                                        </div>

                                        <!-- Hanap Buhay ng Tagapag-alaga -->
                                        <div class="form-group col-md-6">
                                            <label for="Tagapag_alaga_HB">(Sasagutin lamang kung ang mga magulang ang hindi pangunahing tagapag-alaga ng Kliyente).<br><br> Hanap Buhay ng Tagapag-alaga</label>
                                            <input type="text" id="Tagapag_alaga_HB" name="Tagapag_alaga_HB" class="form-control" value="<?= htmlspecialchars($profile['guardian_job'] ?? '') ?>" disabled>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Ano ang kasalukuyang estado ng relasyon ng mga biological na magulang?</label><br>
                                            <?php
                                            // Decode the JSON data into an associative array
                                            $parentsStatus = json_decode($profile['parents_status'] ?? '[]', true);

                                            // Define an array of checkbox options
                                            $checkboxes = [
                                                'Kasal' => 'Kasal',
                                                'LivingTogether' => 'Living Together (Common law relation)',
                                                'Separated' => 'Separated',
                                                'Annulled' => 'Annulled',
                                                'Biyuda_o' => 'Biyuda/Biyudo',
                                            ];

                                            // Loop through the checkbox options
                                            foreach ($checkboxes as $key => $label) {
                                                $checked = (isset($parentsStatus[$key]) && $parentsStatus[$key]) ? 'checked' : '';
                                                echo "<input type='checkbox' id='$key' name='parents_status[]' value='$key' $checked disabled>";
                                                echo "<label for='$key'>$label</label><br>";
                                            }
                                            ?>
                                        </div>

                                        <!-- Taon ng Estado ng biological na magulang -->
                                        <div class="form-group col-md-6">
                                            <label for="estado_Taon">Ilang Taon ng nasa ganitong estado ang mga biological na magulang?</label>
                                            <input type="text" id="estado_Taon" name="estado_Taon" class="form-control" value="<?= htmlspecialchars($profile['status_years'] ?? '') ?>" disabled>
                                        </div>

                                        <!-- Step-parents -->
                                        <div class="form-group col-md-6">
                                            <label for="Step_parents">Kung hiwalay ang mga magulang, paki lista ng pangalan ng step-parents.</label>
                                            <input type="text" id="Step_parents" name="Step_parents" class="form-control" value="<?= htmlspecialchars($profile['step_parents'] ?? '') ?>" disabled>
                                        </div>

                                        <!-- Miyembro ng sumusunod -->
                                        <div class="form-group col-md-6">
                                            <label for="estado">Ang magulang/tagapangalaga ba ay miyembro ng mga sumusunod?</label><br>
                                            <?php
                                            // Decode the JSON data into an associative array
                                            $parentsMember = json_decode($profile['parents_member'] ?? '[]', true);
                                            // Define an array of checkbox options
                                            $checkboxes = [
                                                'SSS' => 'SSS',
                                                'PWD' => 'PWD',
                                                '4Ps' => '4Ps',
                                                'SeniorCitizen' => 'Senior Citizen',
                                                'PAGIBIG' => 'PAG IBIG',
                                                'GSIS' => 'GSIS',
                                                'SoloParent' => 'Solo Parent',
                                            ];
                                            // Loop through the checkbox options
                                            foreach ($checkboxes as $key => $label) {
                                                $checked = isset($parentsMember[$key]) && $parentsMember[$key] ? 'checked' : '';
                                                echo "<input type='checkbox' id='$key' name='$key' $checked disabled>";
                                                echo "<label for='$key'>$label</label><br>";
                                            }
                                            ?>
                                            <div style="display: flex; align-items: center;">
                                                <input type='checkbox' id='otherCheckbox' name='parents_member[Other]' onclick="toggleOtherCheckboxInput()" <?= isset($parentsMember['Other']) && $parentsMember['Other'] ? 'checked' : ''; ?> disabled>
                                                <label for='otherCheckbox'>Other</label>
                                                <input type='text' id='other_input' name='other_input' class='form-control' value='<?= htmlspecialchars($parentsMember['Other'] ?? '') ?>' disabled style='display: <?= isset($parentsMember['Other']) && $parentsMember['Other'] ? "block" : "none"; ?>;'>
                                            </div>
                                        </div>

                                        <!-- Buwanang Kita ng Pamilya -->
                                        <div class="form-group col-md-6">
                                            <label>Kabuuang Buwanang Kita ng Pamilya</label><br>
                                            <input type="radio" id="L14k" name="kita" value="L14k" <?= ($profile['kita'] ?? '') == 'Oo' ? 'L14k' : '' ?> disabled>
                                            <label for="L14k">Lower Than P14,000</label><br>
                                            <input type="radio" id="P14k-P19k" name="kita" value="P14k-P19k" <?= ($profile['kita'] ?? '') == 'P14k-P19k' ? 'checked' : '' ?> disabled>
                                            <label for="P14k-P19k">P14,001 - P19,040</label><br>
                                            <input type="radio" id="P19k-P38k" name="kita" value="P19k-P38k" <?= ($profile['kita'] ?? '') == 'P19k-P38k' ? 'checked' : '' ?> disabled>
                                            <label for="P19k-P38k">P19,041 - P38,080</label><br>
                                            <input type="radio" id="P38k-P66k" name="kita" value="P38k-P66k" <?= ($profile['kita'] ?? '') == 'P38k-P66k' ? 'checked' : '' ?> disabled>
                                            <label for="P38k-P66k">P38,041 - P66,640</label><br>
                                            <input type="radio" id="P66k-P114k" name="kita" value="P66k-P114k" <?= ($profile['kita'] ?? '') == 'P66k-P114k' ? 'checked' : '' ?> disabled>
                                            <label for="P66k-P114k">P66,541 - P114,240</label><br>
                                            <input type="radio" id="P114k-P190k" name="kita" value="P114k-P190k" <?= ($profile['kita'] ?? '') == 'P114k-P190k' ? 'checked' : '' ?> disabled>
                                            <label for="P114k-P190k">P114,241 - P190,400</label><br>
                                            <input type="radio" id="P190k" name="kita" value="P190k" <?= ($profile['kita'] ?? '') == 'P190k' ? 'checked' : '' ?> disabled>
                                            <label for="P190k">P190,401 and above</label><br>
                                        </div>

                                        <!-- SNED Allowance -->
                                        <div class="form-group col-md-6">
                                            <label>Kayo ba ay tumatanggap ng SNED allowance mula sa paaralan?</label><br>
                                            <input type="radio" id="Oo" name="SNED" value="Oo" <?= ($profile['SNED'] ?? '')  == 'Oo' ? 'checked' : '' ?> disabled>
                                            <label for="Oo">Oo</label><br>
                                            <input type="radio" id="Hindi" name="SNED" value="Hindi" <?= ($profile['SNED'] ?? '') == 'Hindi' ? 'checked' : '' ?> disabled>
                                            <label for="Hindi">Hindi</label><br>
                                        </div>

                                        <!-- Teletherapy -->
                                        <div class="form-group col-md-6">
                                            <label>Resources para sa pagpapatupad ng teletherapy</label><br>
                                            <input type="radio" id="Wifi" name="teletheraphy" value="Wifi" <?= ($profile['teletheraphy'] ?? '') == 'Wifi' ? 'checked' : '' ?> disabled>
                                            <label for="Wifi">Wifi</label><br>
                                            <input type="radio" id="PD" name="teletheraphy" value="PD" <?= ($profile['teletheraphy'] ?? '') == 'PD' ? 'checked' : '' ?> disabled>
                                            <label for="PD">Prepaid Data (Cellphone)</label><br>
                                            <input type="radio" id="PW" name="teletheraphy" value="PW" <?= ($profile['teletheraphy'] ?? '') == 'PW' ? 'checked' : '' ?> disabled>
                                            <label for="PW">Pocket Wifi</label><br>

                                            <div style="display: flex; align-items: center;">
                                                <input type="radio" id="otherRadio" name="teletheraphy" value="Other" <?= ($profile['teletheraphy'] ?? '') == 'Other' ? 'checked' : '' ?> onclick="toggleOtherRadioInput()" disabled>
                                                <label for="otherRadio">Other</label>
                                                <input type="text" id="otherRadioInput" name="otherRadioInput" style="display: none; margin-left: 10px;" placeholder="Please specify" value="<?= htmlspecialchars($profile['otherRadioInput'] ?? '') ?>">
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>

                            <div id="modalPage3-<?= htmlspecialchars($user['id']); ?>" style="display: none;">
                                <form>
                                    <div class="row">
                                        <!-- Pangalan ng Doktor -->
                                        <div class="form-group col-md-4">
                                            <label for="Dok">Pangalan ng doktor ng iyong anak</label>
                                            <input type="text" id="Dok" name="Dok" class="form-control" disabled>
                                        </div>

                                        <!-- Pangalan ng Ospital -->
                                        <div class="form-group col-md-3">
                                            <label for="Ospi_Name">Pangalan ng Ospital</label>
                                            <input type="text" id="Ospi_Name" name="Ospi_Name" class="form-control" disabled>
                                        </div>

                                        <!-- Contact Number ng Doktor -->
                                        <div class="form-group col-md-3">
                                            <label for="no_doc">Contact Number ng Doktor</label>
                                            <input type="text" id="no_doc" name="no_doc" class="form-control" disabled>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Test - Date - Results -->
                                        <div class="form-group col-md-4">
                                            <label for="med_test">Pakilista ang anumang psychological or medical testing na tapos ng iyong anak (Test-Date-Results)</label>
                                            <input type="text" id="med_test" name="med_test" class="form-control" disabled>
                                        </div>

                                        <!-- Assesment ng Anak -->
                                        <div class="form-group col-md-3">
                                            <label for="ass_anak">Note: Ilagay ang kopya ng assesment ng iyong anak.</label>
                                            <input type="text" id="ass_anak" name="ass_anak" class="form-control" disabled>
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
                                                echo "<input type='checkbox' id='$key' name='$key' $checked disabled>";
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
                                                echo "<input type='checkbox' id='$key' name='$key' $checked disabled>";
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
                                            <input type="text" id="issue_kalusugan" name="issue_kalusugan" class="form-control" disabled>
                                        </div>

                                        <!-- Prenatal -->
                                        <div class="form-group col-md-6">
                                            <label><br>Prenatal
                                                <br>Nagkaroon ba ng regular na check-up ang ina?</label><br>
                                            <input type="radio" id="Oo" name="ina_check" value="Oo" <?= ($profile['ina_check']  ?? '') == 'Oo' ? 'checked' : '' ?> disabled>
                                            <label for="Oo">Oo</label><br>
                                            <input type="radio" id="Hindi" name="ina_check" value="Hindi" <?= ($profile['ina_check'] ?? '') == 'Hindi' ? 'checked' : '' ?> disabled>
                                            <label for="Hindi">Hindi</label><br>
                                        </div>

                                        <!-- Aktibidad nung panahon ng nagbubuntis -->
                                        <div class="form-group col-md-6">
                                            <label for="Tagapag-alaga_Name"><br>Prenatal
                                                <br>Ano ang mga aktibidad ng ina sa panahon ng pagbubuntis? (nagtratrabaho)</label>
                                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                        </div>

                                        <!-- Komplikasyhon sa gamot -->
                                        <div class="form-group col-md-6">
                                            <label for="Tagapag-alaga_Name"><br>Prenatal
                                                <br>Nagkaroon ba ng mga komplikasyon, gamot, o anumang iba pang mahalagang pangyayari sa panahon ng pagbubuntis</label>
                                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                        </div>

                                        <!-- Edad ng ina nang isinilang ang bata-->
                                        <div class="form-group col-md-6">
                                            <label for="Tagapag-alaga_Name"><br>Delivery
                                                <br>Edad ng ina nang isinilang ang bata</label>
                                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label><br>Delivery
                                                <br>Age of gestation</label><br>
                                            <input type="radio" id="Premature" name="gestation" value="Premature" <?= ($profile['gestation'] ?? '') == 'Premature' ? 'checked' : '' ?> disabled>
                                            <label for="Premature">Premature (less than 37 weeks gestation)</label><br>
                                            <input type="radio" id="Fullterm" name="gestation" value="Fullterm" <?= ($profile['gestation'] ?? '') == 'Fullterm' ? 'checked' : '' ?> disabled>
                                            <label for="Fullterm">Full-term(37 to 42 weeks gestation)</label><br>
                                            <input type="radio" id="Postterm" name="gestation" value="Postterm" <?= ($profile['gestation'] ?? '') == 'Postterm' ? 'checked' : '' ?> disabled>
                                            <label for="Postterm">Postterm (born after 42 weeks gestation)</label><br>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Delivery</label><br>
                                            <input type="radio" id="norm_delivery" name="delivery" value="norm_delivery" <?= ($profile['delivery'] ?? '') == 'norm_delivery' ? 'checked' : '' ?> disabled>
                                            <label for="norm_delivery">Normal Delivery</label><br>
                                            <input type="radio" id="Cesarean" name="delivery" value="Cesarean" <?= ($profile['delivery'] ?? '') == 'Cesarean' ? 'checked' : '' ?> disabled>
                                            <label for="Cesarean">Cesarean Delivery</label><br>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="Tagapag-alaga_Name"><br>Delivery
                                                <br>Nagkaroon ba ng mga komplikasyon, gamot, o anumang iba pang mahalagang pangyayari sa panahon ng panganak?</label>
                                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                        </div>

                                        <!-- Post-Natal -->
                                        <div class="form-group col-md-6">
                                            <label><br>Postnatal
                                                <br>Na-admit ba ang bata sa Neonatal Intensive Care Unit (NICU)</label><br>
                                            <input type="radio" id="Oo" name="PN_admit" value="Oo" <?= ($profile['PN_admit'] ?? '') == 'Oo' ? 'checked' : '' ?> disabled>
                                            <label for="Oo">Oo</label><br>
                                            <input type="radio" id="Hindi" name="PN_admit" value="Hindi" <?= ($profile['PN_admit'] ?? '') == 'Hindi' ? 'checked' : '' ?> disabled>
                                            <label for="Hindi">Hindi</label><br>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="Tagapag-alaga_Name">Kung oo, ilarawan ang dahilan ng pag-admit (Tagal-Dahilan-Interbensyon)</label>
                                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="Tagapag-alaga_Name">May mga komplikasyon, gamot, o iba pang mahalagang pangyayari ba a panahon pagkatapos ng panganganak? Kung oo, pakilarawan ang mga ito.
                                            </label>
                                            <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label><br>Nagkaroon ba ng anumang delay ang iyong anak sa pag-abot ng mga developmental milestone?
                                                <br>Rolled over consistently</label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Sat up unsupported
                                            </label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Stood up
                                            </label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Crawled
                                            </label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Walked unassisted
                                            </label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Said 1st intelligible words
                                            </label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Said 2-3 word phrases</label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Used sentences regularly</label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Potty trained</label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Dressed self independently</label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Oo</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Hindi</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="Tagapag-alaga_Name">Anong mga bakuna/immunization ang mayroon ang iyong anak? (ex. Covid vaccine, Hepatites A&B, Polio, Influenza, Measles, Chickenpox)
                                        </label>
                                        <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Umiinom ba ng anumang gamot ang iyong anak?</label><br>
                                        <input type="radio" id="Oo" name="a_gamot" value="Oo" <?= ($profile['a_gamot'] ?? '') == 'Oo' ? 'checked' : '' ?> disabled>
                                        <label for="Oo">Oo</label><br>
                                        <input type="radio" id="Hindi" name="a_gamot" value="Hindi" <?= ($profile['a_gamot'] ?? '') == 'Hindi' ? 'checked' : '' ?> disabled>
                                        <label for="Hindi">Hindi</label><br>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="Tagapag-alaga_Name">Pakilista ang anumang gamot, bitamina, o suplemento na kasalukuyang iniinom ng iyong anak (Pangalan ng Gamot-Dosage-Tagal ng Panahon na iinumin ang gamot)
                                        </label>
                                        <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="Tagapag-alaga_Name">Ano ang dahilan ng gamot at gaano katagal nang umiinom ng gamot ang iyong anak?
                                        </label>
                                        <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="Tagapag-alaga_Name">Nakakatanggap ba ng therapy ang iyong anak dati? Kung oo, saan, gaano katagal, at bakit siya tumigil sa therapy?

                                        </label>
                                        <input type="text" id="Tagapag-alaga_Name" name="Tagapag-alaga_Name" class="form-control" disabled>
                                    </div>
                                </form>
                            </div>

                            <div id="modalPage4-<?= htmlspecialchars($user['id']); ?>" style="display: none;">
                                <form>
                                    <div class="row">

                                        <div class="form-group col-md-6">
                                            <label for="Dok">Sino ang araw-araw na tagapag-alaga ng iyong anak?
                                            </label>
                                            <input type="text" id="Dok" name="Dok" class="form-control" disabled>
                                        </div>


                                        <div class="form-group col-md-6">
                                            <label for="Ospi_Name">Pakilista ang lahat ng tao na kasalukuyang nakatira sa parehong tahanan ng iyong anak (Pangalan-Edad-Relasyon sa bata)</label>
                                            <input type="text" id="Ospi_Name" name="Ospi_Name" class="form-control" disabled>
                                        </div>


                                        <div class="form-group col-md-6">
                                            <label for="no_doc">May kasaysayan ba ng mga neurodevelopmental na disorder sa malapit na pamilya (mga magulang o kapatid)? Kung oo, pakipaliwanag.</label>
                                            <input type="text" id="no_doc" name="no_doc" class="form-control" disabled>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div id="modalPage5-<?= htmlspecialchars($user['id']); ?>" style="display: none;">
                                <form>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Pumapasok ba sa paaralan ang iyong anak?</label><br>
                                            <input type="radio" id="Oo" name="ina_chec" value="Oo" disabled>
                                            <label for="Oo">Oo</label><br>
                                            <input type="radio" id="Hindi" name="ina_check" value="Hindi" disabled>
                                            <label for="Hindi">Hindi</label><br>
                                        </div>

                                        <!-- Pangalan ng Doktor -->
                                        <div class="form-group col-md-6">
                                            <label for="Dok">Pangalan ng Paaralan
                                            </label>
                                            <input type="text" id="Dok" name="Dok" class="form-control" disabled>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Programa</label><br>
                                            <input type="radio" id="Oo" name="ina_chec" value="Oo" disabled>
                                            <label for="Oo">Mainstream</label><br>
                                            <input type="radio" id="Hindi" name="ina_check" value="Hindi" disabled>
                                            <label for="Hindi">Kombinasyon ng Mainstream at SPED</label><br>
                                            <input type="radio" id="Hindi" name="ina_check" value="Hindi" disabled>
                                            <label for="Hindi">SPED</label><br>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="Dok">School Schedule
                                            </label>
                                            <input type="text" id="Dok" name="Dok" class="form-control" disabled>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="estado">Nakaranas ba ang iyong anak ng alinman sa mga sumusunod na problema sa paaralan?
                                            </label><br>

                                            <?php
                                            // Decode the JSON data into an associative array
                                            // $medDiagStatus = json_decode($profile['SS'], true);

                                            // Define an array of checkbox options
                                            $checkboxes = [
                                                'BING' => 'Bullying',
                                                'EFA' => 'Exclusion from activities',
                                                'Frs' => 'Fears',
                                                'FF' => 'Few Friends',
                                                'Fing' => 'Fighting',
                                                'INCW' => 'Incomplete Works',
                                                'PA' => 'Poor Attendance',
                                                'PG' => 'Poor Grades',
                                                'PB' => 'Problem Behaviors',
                                                'S/E' => 'Suspension/expulsion',
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
                                    </div>
                                </form>
                            </div>

                            <div id="modalPage6-<?= htmlspecialchars($user['id']); ?>" style="display: none;">
                                <form>
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
                                            
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="Dok">Pakispecify kung kailan nagaganap ang kahirapang ito.
                                            </label>
                                            <input type="text" id="Dok" name="Dok" class="form-control" disabled>
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
                                            <input type="text" id="Dok" name="Dok" class="form-control" disabled>
                                        </div>


                                        <div class="form-group col-md-6">
                                            <label><br>Nagkaroon ba ng anumang delay ang iyong anak sa pag-abot ng mga developmental milestone?
                                                <br><br>How often does she or he have difficulty staying organized?</label>
                                            <div>
                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                <label for="rolled_over_oo">Never
                                                </label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Rarely</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Often</label>
                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                <label for="rolled_over_hindi">Always</label>
                                            </div>

                                            <label>How often does she or he have problems remembering things?
                                                <div>
                                                    <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                    <label for="rolled_over_oo">Never
                                                    </label>
                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                    <label for="rolled_over_hindi">Rarely</label>
                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                    <label for="rolled_over_hindi">Often</label>
                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                    <label for="rolled_over_hindi">Always</label>
                                                </div>

                                                <label>How often does she or he fidget or squirm when disabled to stay seated?
                                                    <div>
                                                        <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                        <label for="rolled_over_oo">Never
                                                        </label>
                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                        <label for="rolled_over_hindi">Rarely</label>
                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                        <label for="rolled_over_hindi">Often</label>
                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                        <label for="rolled_over_hindi">Always</label>
                                                    </div>

                                                    <label>How often does she or he make careless mistakes?
                                                        <div>
                                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                            <label for="rolled_over_oo">Never
                                                            </label>
                                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                            <label for="rolled_over_hindi">Rarely</label>
                                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                            <label for="rolled_over_hindi">Often</label>
                                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                            <label for="rolled_over_hindi">Always</label>
                                                        </div>

                                                        <label>How often does she or he have difficulty paying attention during boring or repetitive tasks?
                                                            <div>
                                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                                <label for="rolled_over_oo">Never
                                                                </label>
                                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                <label for="rolled_over_hindi">Rarely</label>
                                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                <label for="rolled_over_hindi">Often</label>
                                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                <label for="rolled_over_hindi">Always</label>
                                                            </div>

                                                            <label>How often does she or he misplace items?
                                                                <div>
                                                                    <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                                    <label for="rolled_over_oo">Never
                                                                    </label>
                                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                    <label for="rolled_over_hindi">Rarely</label>
                                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                    <label for="rolled_over_hindi">Often</label>
                                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                    <label for="rolled_over_hindi">Always</label>
                                                                </div>

                                                                <label>How often does she or he distracted?
                                                                    <div>
                                                                        <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                                        <label for="rolled_over_oo">Never
                                                                        </label>
                                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                        <label for="rolled_over_hindi">Rarely</label>
                                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                        <label for="rolled_over_hindi">Often</label>
                                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                        <label for="rolled_over_hindi">Always</label>
                                                                    </div>

                                                                    <label>How often does she or he interrupt others who are talking?
                                                                        <div>
                                                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                                            <label for="rolled_over_oo">Never
                                                                            </label>
                                                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                            <label for="rolled_over_hindi">Rarely</label>
                                                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                            <label for="rolled_over_hindi">Often</label>
                                                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                            <label for="rolled_over_hindi">Always</label>
                                                                        </div>

                                                                        <label>How often does she or he have trouble unwinding after an activity or day?
                                                                            <div>
                                                                                <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                                                <label for="rolled_over_oo">Never
                                                                                </label>
                                                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                <label for="rolled_over_hindi">Rarely</label>
                                                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                <label for="rolled_over_hindi">Often</label>
                                                                                <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                <label for="rolled_over_hindi">Always</label>
                                                                            </div>

                                                                            <label>How often does she or he have trouble waiting his/her turn?
                                                                                <div>
                                                                                    <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                                                    <label for="rolled_over_oo">Never
                                                                                    </label>
                                                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                    <label for="rolled_over_hindi">Rarely</label>
                                                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                    <label for="rolled_over_hindi">Often</label>
                                                                                    <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                    <label for="rolled_over_hindi">Always</label>
                                                                                </div>

                                                                                <label>How often does she or he appear to "space out"?
                                                                                    <div>
                                                                                        <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" disabled>
                                                                                        <label for="rolled_over_oo">Never
                                                                                        </label>
                                                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                        <label for="rolled_over_hindi">Rarely</label>
                                                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                        <label for="rolled_over_hindi">Often</label>
                                                                                        <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" disabled>
                                                                                        <label for="rolled_over_hindi">Always</label>
                                                                                    </div>


                                        </div>
                                </form>
                            </div>

                            <div id="modalPage7-<?= htmlspecialchars($user['id']); ?>" style="display: none;">
                                <form>
                                <div class="row">

                                    <div class="form-group col-md-6">
                                        <label for="Dok">Pakilarawan ng pang-araw-araw na rutina ng iyong anak.
                                        </label>
                                        <input type="text" id="Dok" name="Dok" class="form-control" required>
                                    </div>


                                    <div class="form-group col-md-6">
                                        <label for="Ospi_Name">Pakilarawan ang kakayahan sa wika ng iyong anak.
                                        </label>
                                        <input type="text" id="Ospi_Name" name="Ospi_Name" class="form-control" required>
                                    </div>


                                    <div class="form-group col-md-6">
                                        <label for="no_doc">Pakilarawan ang mga libangan ng iyong anak (toys, videos, movies, activities)</label>
                                        <input type="text" id="no_doc" name="no_doc" class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="no_doc">Ano ang mga pinakakaraniwang problema ng iyong anak? (pagpalo, pagkagat, pagsigaw, etc)</label>
                                        <input type="text" id="no_doc" name="no_doc" class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="estado">Anong mga antecedents (posibling sanhi) ang pinaka-malamang na nagiging dahilan ng pag-uugali ng iyong anak?
                                        </label><br>

                                        <?php
                                        // Decode the JSON data into an associative array
                                        // $medDiagStatus = json_decode($profile['SS'], true);

                                        // Define an array of checkbox options
                                        $checkboxes = [
                                            'Alone' => 'Alone (automatic/sensory)',
                                            'cannot' => 'Can not communicate a need',
                                            'dem' => 'Demands',
                                            'IA' => 'Interrupted activities',
                                            'Tra' => 'Transitions',
                                            'Told' => 'Told No',
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
                                        <label for="Dok">Pakilarawan ang ga antecedents (posibleng sanhi) ng pag-uugali ng iyong anak.
                                        </label>
                                        <input type="text" id="Dok" name="Dok" class="form-control" required>
                                    </div>


                                    <div class="form-group col-md-6">
                                        <label for="Dok">Ano ang mga inaasahan at layunin mo para sa iyong anak habang tumtanggap ng serbisyo ng theraphy sa CSN?
                                        </label>
                                        <input type="text" id="Dok" name="Dok" class="form-control" required>
                                    </div>


                                    <div class="form-group col-md-6">
                                        <label for="Dok">Ano ang mga itinuturing mong lakas ng iyong anak?
                                        </label>
                                        <input type="text" id="Dok" name="Dok" class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="Dok">Pakisulat ang mga pagkain o meryenda na gusto ng iyong anak:
                                        </label>
                                        <input type="text" id="Dok" name="Dok" class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="Dok">Mayroon bang ibang impormasyon tungkol sa iyong anak na makakatulong sa pagsusuri o theraphy?
                                        </label>
                                        <input type="text" id="Dok" name="Dok" class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label><br>Skill Assessment
                                            <br><br>Responds to name</label>
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                        </div>

                                        <label> Attends to adult voices
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                        </div>

                                            <label>Performs 4 different motor action on command
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Holds items with thumb and index finger

                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Places items onto a container, ring on a peg
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Matches 10 identical pictures or objects
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Manipulates toys for at least 1 consecutive minute
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Indicates that he/she wants to be held
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Makes eye contact with children
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Engages in parallel play with peers
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Imitates 2 gross motor movements
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Imitates 4 gross motor movements
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Imitates other behavior spontaneously
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Looks at books
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Plays with at least 5 toys
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Makes eye contact when asking for something
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Plays with cause and effect toys
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Imaginative play
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Play games with rules
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Kicks ball
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Throws ball
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Sleeps through the night
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Drinks from a cup
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Eats with utensils
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Identifies shapes
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Identifies colors
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Identifies letters
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Identifies numbers
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Writes name
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Traces letters/numbers
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Write letters/numbers
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Rote counts to 10
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>

                                            <label>Rote counts to 25
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            <input type="radio" id="rolled_over_hindi" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_hindi">Hindi</label>
                                            </div>
                                    </div>
                                    

                                    <div class="form-group col-md-6">
                                        <label><br>Handa at available akong sundin ang theraphy home program/rekomendasyon para sa aking anak.</label>
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Hindi" required>
                                            <label for="rolled_over_oo">Hindi
                                            </label>
                                            </div>
                                            

                                            <label><br>Naintindihan ko na mahalaga ang pagbibigay ng tama na impormasyon upang maangkop ang paggamot sa pangangailangan ng aking anak. Ang impormasyong ito ay maaaring gamitin bilang karagdagang impormasyon para sa dokumentasyon ng therapy. Ang impormasyong ito ay tama ayon sa aking paglalarawan.</label>
                                        <div>
                                            <input type="radio" id="rolled_over_oo" name="rolled_over" value="Oo" required>
                                            <label for="rolled_over_oo">Oo
                                            </label>
                                            </div>
                                            
                                    </div>
                                </form>
                            </div>
                        </div>
                        </div>
                        </div>
                        
                        <div class="modal-footer d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-info" onclick="showModalPage('modalPage1-<?= htmlspecialchars($user['id']); ?>')">1</button>
                                <button type="button" class="btn btn-info" onclick="showModalPage('modalPage2-<?= htmlspecialchars($user['id']); ?>')">2</button>
                                <button type="button" class="btn btn-info" onclick="showModalPage('modalPage3-<?= htmlspecialchars($user['id']); ?>')">3</button>
                                <button type="button" class="btn btn-info" onclick="showModalPage('modalPage4-<?= htmlspecialchars($user['id']); ?>')">4</button>
                                <button type="button" class="btn btn-info" onclick="showModalPage('modalPage5-<?= htmlspecialchars($user['id']); ?>')">5</button>
                                <button type="button" class="btn btn-info" onclick="showModalPage('modalPage6-<?= htmlspecialchars($user['id']); ?>')">6</button>
                                <button type="button" class="btn btn-info" onclick="showModalPage('modalPage7-<?= htmlspecialchars($user['id']); ?>')">7</button>

                            </div>
                            <button type="button" class="btn btn-danger ml-auto" onclick="showModalPage('modalPage1-<?= htmlspecialchars($user['id']); ?>');" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
        </div>

            <!-- Modal for Updating User Profile -->
            <div class="modal fade" id="updateModal-<?= htmlspecialchars($user['id']); ?>" tabindex="-1" aria-labelledby="updateModalLabel-<?= htmlspecialchars($user['id']); ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel-<?= htmlspecialchars($user['id']); ?>">Update Username for <?= htmlspecialchars($user['username']); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="current_username">Current Username:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="new_username">New Username</label>
                                    <input type="text" class="form-control" name="username" required>
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']); ?>">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                        </div>
                        </div>
                        </div>
                        <!-- </div> -->
        <?php endforeach; ?>
        </tbody>
        </table>
    </div>
    </div>
    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Optional: Include jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function showModalPage(pageId) {
            // Hide all modal pages
            const pages = document.querySelectorAll('[id^="modalPage"]');
            pages.forEach(page => {
                page.style.display = 'none';
            });

            // Show the specified page
            document.getElementById(pageId).style.display = 'block';
        }
    </script>
</body>

</html>