<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

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

// --- 2. FETCH ONLY PENDING FACILITIES FOR DISPLAY on this page ---
// This ensures that only items awaiting review appear in the approval queue.
$sql = "SELECT * FROM facilities WHERE status='Pending' ORDER BY created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/crud.css">
    <title>TNVS Facility Approval Requests</title>
    
    <?php include 'partials/styles.php'; ?>
    
    <style>
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main class="p-6">
            <h1 class="text-3xl font-semibold text-gray-800 mb-6">Pending Facility Approval Requests</h1>

            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        
                        // Set image path (assuming 'uploads' is parallel to 'PHP')
                        $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                        
                        // Status class is set based on the current status
                        $statusClass = "bg-yellow-100 text-yellow-700 border border-yellow-300";

                        echo "
                        <div class='bg-white rounded-xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1 overflow-hidden animate-fadeIn'>
                            <img src='{$picturePath}' onerror=\"this.onerror=null;this.src='https://via.placeholder.com/300x200?text=Image+Not+Found';\" alt='Facility Image' class='w-full h-48 object-cover'>
                            <div class='p-5'>
                                <h3 class='text-lg font-semibold text-gray-800'>".htmlspecialchars($row['name'])."</h3>
                                <p class='text-sm text-gray-600 mt-1'>Capacity: ".htmlspecialchars($row['capacity'])."</p>
                                <p class='text-sm text-gray-600'>Location: ".htmlspecialchars($row['location'])."</p>
                                <p class='text-sm mt-2'><span class='px-3 py-1 rounded-full text-xs font-medium {$statusClass}'>".htmlspecialchars($row['status'])."</span></p>
                                <p class='text-sm text-gray-600 mt-2'>Date: ".htmlspecialchars($row['available_date'])."</p>
                                <p class='text-sm text-gray-600'>Time: ".htmlspecialchars($row['available_time'])."</p>
                                
                                <div class='flex justify-between items-center mt-4 gap-2'>
                                    <form method='POST' onsubmit=\"return confirm('Approve facility: ".addslashes($row['name'])."?');\" class='w-1/2'>
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
                            </div>
                        </div>";
                    }
                } else {
                    echo "<p class='text-gray-500'>No pending facility requests at this time.</p>";
                }
                ?>
            </div>
        </main>
    </section>

    <script src="../JS/script.js"></script>
    <script src="../JS/modal.js"></script> 
</body>
</html>