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
<body style="background-color: #eeeeee;" class="bg-custom flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        
        <main class="w-full px-6 py-6">
            <!-- Minimalist Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-light text-gray-900">Facility Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage approved and rejected facilities</p>
            </div>
                
            <div class="space-y-8">
                <!-- Approved Facilities Section -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Approved Facilities <span class="text-sm font-normal text-gray-500">(<?php echo count($approvedFacilities); ?>)</span></h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                            <?php if (empty($approvedFacilities)): ?>
                                <p class="text-gray-400 text-center py-6">No approved facilities</p>
                            <?php else: ?>
                                <?php foreach ($approvedFacilities as $row): 
                                    $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                ?>
                                    <div class='bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition-colors overflow-hidden'>
                                        <div class='aspect-video overflow-hidden'>
                                            <img src='<?php echo $picturePath; ?>' alt='<?php echo htmlspecialchars($row['name']); ?>' class='w-full h-full object-cover'>
                                        </div>
                                        <div class="p-4">
                                            <div class="flex items-start justify-between mb-2">
                                                <h3 class='font-medium text-gray-900 truncate'><?php echo htmlspecialchars($row['name']); ?></h3>
                                                <span class='px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 ml-2 flex-shrink-0'>Approved</span>
                                            </div>
                                            <p class='text-sm text-gray-500 mb-3'><?php echo $row['capacity']; ?> people • <?php echo htmlspecialchars($row['location']); ?></p>
                                            <div class="flex gap-2">
                                                <form method='POST' onsubmit="return confirm('Set to maintenance?');" class="flex-1">
                                                    <input type='hidden' name='facility_id' value='<?php echo $row['facility_id']; ?>'>
                                                    <input type='hidden' name='action' value='maintenance'>
                                                    <button type='submit' class='w-full bg-yellow-50 hover:bg-yellow-100 text-yellow-700 px-3 py-2 rounded-md text-xs font-medium transition-colors'>
                                                        <i class='bx bx-wrench mr-1'></i>Maintenance
                                                    </button>
                                                </form>
                                                <form method='POST' onsubmit="return confirm('Delete permanently?');" class="flex-1">
                                                    <input type='hidden' name='facility_id' value='<?php echo $row['facility_id']; ?>'>
                                                    <input type='hidden' name='action' value='delete'>
                                                    <button type='submit' class='w-full bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-md text-xs font-medium transition-colors'>
                                                        <i class='bx bx-trash mr-1'></i>Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- Rejected Facilities Section -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Rejected Facilities <span class="text-sm font-normal text-gray-500">(<?php echo count($rejectedFacilities); ?>)</span></h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                            <?php if (empty($rejectedFacilities)): ?>
                                <p class="text-gray-400 text-center py-6">No rejected facilities</p>
                            <?php else: ?>
                                <?php foreach ($rejectedFacilities as $row): 
                                    $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                ?>
                                    <div class='bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition-colors overflow-hidden'>
                                        <div class='aspect-video overflow-hidden'>
                                            <img src='<?php echo $picturePath; ?>' alt='<?php echo htmlspecialchars($row['name']); ?>' class='w-full h-full object-cover'>
                                        </div>
                                        <div class="p-4">
                                            <div class="flex items-start justify-between mb-2">
                                                <h3 class='font-medium text-gray-900 truncate'><?php echo htmlspecialchars($row['name']); ?></h3>
                                                <span class='px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 ml-2 flex-shrink-0'>Rejected</span>
                                            </div>
                                            <p class='text-sm text-gray-500 mb-3'><?php echo $row['capacity']; ?> people • <?php echo htmlspecialchars($row['location']); ?></p>
                                            <div class="flex gap-2">
                                                <form method='POST' onsubmit="return confirm('Reconsider for approval?');" class="flex-1">
                                                    <input type='hidden' name='facility_id' value='<?php echo $row['facility_id']; ?>'>
                                                    <input type='hidden' name='action' value='reapprove'>
                                                    <button type='submit' class='w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-2 rounded-md text-xs font-medium transition-colors'>
                                                        <i class='bx bx-refresh mr-1'></i>Reconsider
                                                    </button>
                                                </form>
                                                <form method='POST' onsubmit="return confirm('Delete permanently?');" class="flex-1">
                                                    <input type='hidden' name='facility_id' value='<?php echo $row['facility_id']; ?>'>
                                                    <input type='hidden' name='action' value='delete'>
                                                    <button type='submit' class='w-full bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-md text-xs font-medium transition-colors'>
                                                        <i class='bx bx-trash mr-1'></i>Delete
                                                    </button>
                                                </form>
                                            </div>
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
