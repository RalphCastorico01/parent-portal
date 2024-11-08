<?php 
// Start the session
session_start();

// Include database connection
include 'db_connection.php';

// // Check if the username is set in the session
// if (!isset($_SESSION['username'])) {
//     echo "<div class='alert alert-danger'>You are not logged in.</div>";
//     exit; // Stop the script if the user is not logged in
// }

// // Assuming username is stored in session after login
// $username = $_SESSION['username'];

// Fetch pending requests for the logged-in user
try {
   
    $sql = "SELECT request_form_id, username, request_date, request_type, description, status FROM requests_form";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error fetching requests: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit; // Stop further execution if there's an error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Pending Requests</h1>

        <?php if ($requests && count($requests) > 0): ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th class="text-center"></th> <!-- Added header for automatic numbering -->
                        <th class="text-center">Username</th> <!-- Added header for Username -->
                        <th class="text-center">Date</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Description</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php $index = 1; // Initialize the index variable ?>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td class="text-center"><?php echo $index++; ?></td> <!-- Display the automatic number -->
                        <td class="text-center"><?php echo htmlspecialchars($request['username']); ?></td> <!-- Display the username -->
                        <td class="text-center"><?php echo htmlspecialchars($request['request_date']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($request['request_type']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($request['description']); ?></td>
                        <td class="text-center">
                            <a href="generate_request.php?request_form_id=<?php echo htmlspecialchars($request['request_form_id']); ?>" class="btn btn-primary">
                                <i class="fas fa-file-alt"></i> Generate Request
                            </a>
                            <form method="POST" class="d-inline" action="delete_request.php">
                                <input type="hidden" name="request_form_id" value="<?php echo htmlspecialchars($request['request_form_id']); ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this request?');" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending requests found.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.2.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
