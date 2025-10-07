<?php
// Note: This file assumes 'db_connect.php' properly establishes a database connection 
// using $conn (mysqli object).

include 'db_connect.php'; 

// --- 1. HANDLE APPROVE / REJECT ACTIONS (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facility_id']) && isset($_POST['action'])) {
    // Basic Input Validation and Sanitization
    $facility_id = intval($_POST['facility_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        // ACTION: Approve - Set status to 'Approved'
        $stmt = $conn->prepare("UPDATE facilities SET status='Approved' WHERE facility_id=?");
        $stmt->bind_param("i", $facility_id);
    } elseif ($action === 'reject') {
        // ACTION: Reject - CHANGED: Update status to 'Rejected' instead of deleting
        $stmt = $conn->prepare("UPDATE facilities SET status='Rejected' WHERE facility_id=?");
        $stmt->bind_param("i", $facility_id);
    } else {
        // Stop execution if action is invalid
        header("Location: Approval_Rejection_Requests.php");
        exit();
    }
    
    // Execute the prepared statement
    $stmt->execute();
    $stmt->close();

    // Redirect back to the approval requests page to refresh the list
    header("Location: Approval_Rejection_Requests.php");
    exit();
}

// --- 2. FETCH PENDING REQUESTS ---
// Fetch facilities that are either 'Pending' or 'Maintenance Request'
$sql = "SELECT facility_id, name, description, image_path, status, created_at 
        FROM facilities 
        WHERE status = 'Pending' OR status = 'Maintenance Request' 
        ORDER BY created_at DESC";

$pending_requests = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Approval Requests | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    #content main {
        background-color: transparent; /* Ensures main background is correct */
    }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>

    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Approval/Rejection Requests</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="Dashboard.php">Dashboard</a>
                        </li>
                        <li><i class='bx bx-chevron-right' ></i></li>
                        <li>
                            <a class="active" href="Approval_Rejection_Requests.php">Requests</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="max-w-7xl mx-auto p-6">
                <header class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Facility Approval Queue</h1>
                    <p class="text-gray-600">Review and manage pending facility and maintenance requests.</p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    if ($pending_requests && $pending_requests->num_rows > 0) {
                        while($row = $pending_requests->fetch_assoc()) {
                            $is_maintenance = $row['status'] === 'Maintenance Request';
                            $card_border_color = $is_maintenance ? 'border-yellow-500' : 'border-blue-500';
                            $status_text = $is_maintenance ? 'Maintenance Request' : 'New Facility Request';
                            $status_class = $is_maintenance ? 'text-yellow-700 bg-yellow-100' : 'text-blue-700 bg-blue-100';

                            echo "
                            <div class='bg-white rounded-xl shadow-lg overflow-hidden border-t-4 {$card_border_color} flex flex-col'>
                                ".(!empty($row['image_path']) ? "
                                <img src='../uploads/{$row['image_path']}' alt='{$row['name']}' class='w-full h-40 object-cover'>
                                " : "")."
                                <div class='p-5 flex-grow'>
                                    <h3 class='text-xl font-semibold text-gray-900 mb-1'>".htmlspecialchars($row['name'])."</h3>
                                    <span class='inline-block px-3 py-1 text-xs font-medium rounded-full {$status_class} mb-3'>
                                        {$status_text}
                                    </span>
                                    <p class='text-sm text-gray-600 mb-2'><strong>ID:</strong> ".htmlspecialchars($row['facility_id'])."</p>
                                    <p class='text-sm text-gray-600 mb-4'>".substr(htmlspecialchars($row['description']), 0, 100)."...</p>
                                </div>
                                
                                <div class='p-5 pt-0 flex space-x-3'>
                                    <form method='POST' class='w-1/2'>
                                        <input type='hidden' name='facility_id' value='".htmlspecialchars($row['facility_id'])."'>
                                        <input type='hidden' name='action' value='approve'>
                                        <button type='submit' class='w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition'>Approve</button>
                                    </form>
                                    
                                    <form method='POST' onsubmit=\"return confirm('Reject facility: ".addslashes($row['name'])."?');\" class='w-1/2'>
                                        <input type='hidden' name='facility_id' value='".htmlspecialchars($row['facility_id'])."'>
                                        <input type='hidden' name='action' value='reject'>
                                        <button type='submit' class='w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition'>Reject</button>
                                    </form>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<p class='text-gray-500'>No pending facility requests at this time.</p>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </section>

    <script src="../JS/script.js"></script>
    <script src="../JS/modal.js"></script> 
</body>
</html>