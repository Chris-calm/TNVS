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

    // Set success message
    $actionText = ($action === 'approve') ? 'approved' : 'rejected';
    $_SESSION['approval_success'] = "Facility has been $actionText successfully.";

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

        <main class="max-w-7xl mx-auto px-4 py-8">
            <!-- Minimalist Header -->
            <div class="mb-12">
                <h1 class="text-2xl font-light text-gray-900">Pending Approvals</h1>
                <p class="text-sm text-gray-500 mt-1">Review and approve facility requests</p>
            </div>

            <!-- Minimalist Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        
                        // Set image path (assuming 'uploads' is parallel to 'PHP')
                        $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                        
                        // Status class is set based on the current status
                        $statusClass = "bg-yellow-100 text-yellow-700 border border-yellow-300";

                        echo "
                        <div class='bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition-colors group'>
                            <div class='aspect-video overflow-hidden rounded-t-lg'>
                                <img src='{$picturePath}' onerror=\"this.onerror=null;this.src='https://via.placeholder.com/300x200?text=Image+Not+Found';\" alt='Facility Image' class='w-full h-full object-cover group-hover:scale-105 transition-transform duration-300'>
                            </div>
                            <div class='p-4'>
                                <div class='flex items-start justify-between mb-2'>
                                    <h3 class='font-medium text-gray-900'>".htmlspecialchars($row['name'])."</h3>
                                    <span class='px-2 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700'>Pending</span>
                                </div>
                                <div class='space-y-1 text-sm text-gray-500 mb-4'>
                                    <p>".htmlspecialchars($row['capacity'])." people • ".htmlspecialchars($row['location'])."</p>
                                    <p>".htmlspecialchars($row['available_date'])." at ".htmlspecialchars($row['available_time'])."</p>
                                </div>
                                
                                <div class='flex gap-2'>
                                    <form method='POST' onsubmit=\"return confirm('Approve this facility?');\" class='flex-1'>
                                        <input type='hidden' name='facility_id' value='".htmlspecialchars($row['facility_id'])."'>
                                        <input type='hidden' name='action' value='approve'>
                                        <button type='submit' class='w-full bg-green-50 hover:bg-green-100 text-green-700 px-3 py-2 rounded-md text-xs font-medium transition-colors'>Approve</button>
                                    </form>
                                    
                                    <form method='POST' onsubmit=\"return confirm('Reject this facility?');\" class='flex-1'>
                                        <input type='hidden' name='facility_id' value='".htmlspecialchars($row['facility_id'])."'>
                                        <input type='hidden' name='action' value='reject'>
                                        <button type='submit' class='w-full bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-md text-xs font-medium transition-colors'>Reject</button>
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

    <!-- Include Success Modal -->
    <?php include 'partials/success_modal.php'; ?>

    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['approval_success'])): ?>
            showSuccessModal('Action Completed!', '<?= addslashes($_SESSION['approval_success']) ?>');
            <?php unset($_SESSION['approval_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>