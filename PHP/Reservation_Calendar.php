<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// --- 1. HANDLE FACILITY ACTIONS ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facility_id']) && isset($_POST['action'])) {
    $facility_id = intval($_POST['facility_id']);
    $action = $_POST['action'];

    if ($action === 'delete') {
        // Delete the facility record permanently
        $stmt = $conn->prepare("DELETE FROM facilities WHERE facility_id=?");
        $stmt->bind_param("i", $facility_id);
    } elseif ($action === 'maintenance') {
        // Set facility to maintenance status
        $stmt = $conn->prepare("UPDATE facilities SET status='Under Maintenance' WHERE facility_id=?");
        $stmt->bind_param("i", $facility_id);
    } elseif ($action === 'reapprove') {
        // Move rejected facility back to pending for reconsideration
        $stmt = $conn->prepare("UPDATE facilities SET status='Pending' WHERE facility_id=?");
        $stmt->bind_param("i", $facility_id);
    }
    
    $stmt->execute();
    $stmt->close();

    // Set success message based on action
    if ($action === 'delete') {
        $_SESSION['calendar_success'] = "Facility has been deleted successfully.";
    } elseif ($action === 'maintenance') {
        $_SESSION['calendar_success'] = "Facility has been set to maintenance mode.";
    } elseif ($action === 'reapprove') {
        $_SESSION['calendar_success'] = "Facility has been sent back for reconsideration.";
    }

    // Redirect back to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
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
        
        <main class="max-w-7xl mx-auto px-4 py-8">
            <!-- Minimalist Header -->
            <div class="mb-12">
                <h1 class="text-2xl font-light text-gray-900">Facility Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage approved and rejected facilities</p>
            </div>
                
            <div class="grid lg:grid-cols-2 gap-8">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Approved Facilities <span class="text-sm font-normal text-gray-500">(<?php echo count($approvedFacilities); ?>)</span></h2>
                        <div class="space-y-3">
                            <?php if (empty($approvedFacilities)): ?>
                                <p class="text-gray-400 text-center py-8">No approved facilities</p>
                            <?php else: ?>
                                <?php foreach ($approvedFacilities as $row): 
                                    $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                ?>
                                    <div class='bg-white rounded-lg border border-gray-100 p-4 flex gap-4 items-center hover:border-gray-200 transition-colors'>
                                        <div class='w-16 h-16 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0'>
                                            <img src='<?php echo $picturePath; ?>' alt='<?php echo htmlspecialchars($row['name']); ?>' class='w-full h-full object-cover'>
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <h3 class='font-medium text-gray-900 truncate'><?php echo htmlspecialchars($row['name']); ?></h3>
                                            <p class='text-sm text-gray-500'><?php echo $row['capacity']; ?> people • <?php echo htmlspecialchars($row['location']); ?></p>
                                            <span class='inline-block mt-1 px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700'>Approved</span>
                                        </div>
                                        <div class="flex gap-1 flex-shrink-0">
                                            <form method='POST' onsubmit="return confirm('Set to maintenance?');">
                                                <input type='hidden' name='facility_id' value='<?php echo $row['facility_id']; ?>'>
                                                <input type='hidden' name='action' value='maintenance'>
                                                <button type='submit' class='p-2 text-gray-400 hover:text-yellow-600 transition-colors' title="Set to Maintenance">
                                                    <i class='bx bx-wrench text-lg'></i>
                                                </button>
                                            </form>
                                            <form method='POST' onsubmit="return confirm('Delete permanently?');">
                                                <input type='hidden' name='facility_id' value='<?php echo $row['facility_id']; ?>'>
                                                <input type='hidden' name='action' value='delete'>
                                                <button type='submit' class='p-2 text-gray-400 hover:text-red-600 transition-colors' title="Delete">
                                                    <i class='bx bx-trash text-lg'></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Rejected Facilities <span class="text-sm font-normal text-gray-500">(<?php echo count($rejectedFacilities); ?>)</span></h2>
                        <div class="space-y-3">
                            <?php if (empty($rejectedFacilities)): ?>
                                <p class="text-gray-400 text-center py-8">No rejected facilities</p>
                            <?php else: ?>
                                <?php foreach ($rejectedFacilities as $row): 
                                    $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                ?>
                                    <div class='bg-white rounded-lg border border-gray-100 p-4 flex gap-4 items-center hover:border-gray-200 transition-colors'>
                                        <div class='w-16 h-16 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0'>
                                            <img src='<?php echo $picturePath; ?>' alt='<?php echo htmlspecialchars($row['name']); ?>' class='w-full h-full object-cover'>
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <h3 class='font-medium text-gray-900 truncate'><?php echo htmlspecialchars($row['name']); ?></h3>
                                            <p class='text-sm text-gray-500'><?php echo $row['capacity']; ?> people • <?php echo htmlspecialchars($row['location']); ?></p>
                                            <span class='inline-block mt-1 px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700'>Rejected</span>
                                        </div>
                                        <div class="flex gap-1 flex-shrink-0">
                                            <form method='POST' onsubmit="return confirm('Reconsider for approval?');">
                                                <input type='hidden' name='facility_id' value='<?php echo $row['facility_id']; ?>'>
                                                <input type='hidden' name='action' value='reapprove'>
                                                <button type='submit' class='p-2 text-gray-400 hover:text-blue-600 transition-colors' title="Reconsider">
                                                    <i class='bx bx-refresh text-lg'></i>
                                                </button>
                                            </form>
                                            <form method='POST' onsubmit="return confirm('Delete permanently?');">
                                                <input type='hidden' name='facility_id' value='<?php echo $row['facility_id']; ?>'>
                                                <input type='hidden' name='action' value='delete'>
                                                <button type='submit' class='p-2 text-gray-400 hover:text-red-600 transition-colors' title="Delete">
                                                    <i class='bx bx-trash text-lg'></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            </main>
    </section>

    <!-- Include Success Modal -->
    <?php include 'partials/success_modal.php'; ?>

    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['calendar_success'])): ?>
            showSuccessModal('Action Completed!', '<?= addslashes($_SESSION['calendar_success']) ?>');
            <?php unset($_SESSION['calendar_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>