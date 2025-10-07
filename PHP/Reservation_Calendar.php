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

// --- 1. HANDLE DELETE ACTION (For Approved/Rejected Facilities Maintenance) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facility_id']) && $_POST['action'] === 'delete') {
    $facility_id = intval($_POST['facility_id']);

    // Delete the facility record
    $stmt = $conn->prepare("DELETE FROM facilities WHERE id=?");
    $stmt->bind_param("i", $facility_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to refresh the page
    header("Location: Facilities_Maintenance.php");
    exit();
}

// --- 2. FETCH ALL APPROVED AND REJECTED FACILITIES FOR DISPLAY ---
// We only fetch facilities that have been processed (Approved or Rejected)
$sql = "SELECT * FROM facilities WHERE status IN ('Approved', 'Rejected') ORDER BY name ASC";
$result = $conn->query($sql);

$approvedFacilities = [];
$rejectedFacilities = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'Approved') {
            $approvedFacilities[] = $row;
        } elseif ($row['status'] === 'Rejected') {
            $rejectedFacilities[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>TNVS Facility Maintenance</title>
    
    <?php include 'partials/styles.php'; ?>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        
        <main>
            <div class="w-[95%] md:w-[90%] lg:w-[80%] mx-auto py-10">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-semibold text-gray-800">Facilities Maintenance</h1>
                    </div>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h2 class="text-xl font-semibold text-green-700 mb-4">✅ Approved Facilities (<?php echo count($approvedFacilities); ?>)</h2>
                        <div id="approvedGrid" class="grid sm:grid-cols-1 gap-6">
                            <?php if (empty($approvedFacilities)): ?>
                                <p class="text-gray-500">No approved facilities found.</p>
                            <?php else: ?>
                                <?php foreach ($approvedFacilities as $row): 
                                    $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                ?>
                                    <div class='bg-white rounded-xl shadow-md p-4 flex gap-4 items-center'>
                                        <img src='<?php echo $picturePath; ?>' alt='<?php echo htmlspecialchars($row['name']); ?>' class='w-20 h-20 object-cover rounded-lg border'>
                                        <div class="flex-grow">
                                            <h3 class='text-lg font-semibold'><?php echo htmlspecialchars($row['name']); ?></h3>
                                            <p class='text-sm text-gray-600'>Status: <span class="text-green-600 font-medium">Approved</span></p>
                                        </div>
                                        <form method='POST' onsubmit="return confirm('Are you sure you want to delete this facility?');">
                                            <input type='hidden' name='facility_id' value='<?php echo $row['id']; ?>'>
                                            <input type='hidden' name='action' value='delete'>
                                            <button type='submit' class='text-red-500 hover:text-red-700 p-2 rounded-full transition'>
                                                <i class='bx bx-trash text-xl'></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-red-700 mb-4">❌ Rejected Facilities (<?php echo count($rejectedFacilities); ?>)</h2>
                        <div id="rejectedGrid" class="grid sm:grid-cols-1 gap-6">
                            <?php if (empty($rejectedFacilities)): ?>
                                <p class="text-gray-500">No rejected facilities found.</p>
                            <?php else: ?>
                                <?php foreach ($rejectedFacilities as $row): 
                                    $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                ?>
                                    <div class='bg-white rounded-xl shadow-md p-4 flex gap-4 items-center'>
                                        <img src='<?php echo $picturePath; ?>' alt='<?php echo htmlspecialchars($row['name']); ?>' class='w-20 h-20 object-cover rounded-lg border'>
                                        <div class="flex-grow">
                                            <h3 class='text-lg font-semibold'><?php echo htmlspecialchars($row['name']); ?></h3>
                                            <p class='text-sm text-gray-600'>Status: <span class="text-red-600 font-medium">Rejected</span></p>
                                        </div>
                                        <form method='POST' onsubmit="return confirm('Are you sure you want to permanently delete this facility?');">
                                            <input type='hidden' name='facility_id' value='<?php echo $row['id']; ?>'>
                                            <input type='hidden' name='action' value='delete'>
                                            <button type='submit' class='text-red-500 hover:text-red-700 p-2 rounded-full transition'>
                                                <i class='bx bx-trash text-xl'></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            </main>
    </section>

    <script src="../JS/script.js"></script>
</body>
</html>