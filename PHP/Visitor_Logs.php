<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// --- Handle CSV Export (Function preserved) ---
if (isset($_GET['export_csv']) && $_GET['export_csv'] == 1) {
    // 1. Check connection first
    if (isset($conn) && !$conn->connect_error) {
        // Set headers for file download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="visitor_logs_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Output CSV Headers (Adjusted to include all relevant fields)
        fputcsv($output, ['ID', 'Name', 'Contact', 'Purpose', 'Visit Date', 'Visit Time', 'Person to Visit', 'Time In', 'Time Out', 'Status', 'Picture Path']);
        
        // Fetch ALL visitor data for export
        $sql = "SELECT 
                    id, name, contact, purpose, visit_date, visit_time,
                    TIME(checkin) as time_in, TIME(checkout) as time_out, 
                    person_to_visit, status, pass_id, picture_path 
                FROM visitors 
                ORDER BY id DESC";
        
        $result = $conn->query($sql);
        
        if ($result) {
            // Output data rows
            while ($row = $result->fetch_assoc()) {
                // Ensure array keys match the header row above
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}

// --- Handle Check-in/Check-out/Delete/Edit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['checkin'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE visitors SET status='Visiting', checkin=NOW() WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['logs_success'] = "Visitor has been checked in successfully.";
    } elseif (isset($_POST['checkout'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE visitors SET status='Visit Complete', checkout=NOW() WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['logs_success'] = "Visitor has been checked out successfully.";
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM visitors WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['logs_success'] = "Visitor log has been deleted successfully.";
    } elseif (isset($_POST['editLog'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $contact = $_POST['contact'];
        $purpose = $_POST['purpose'];
        $visit_date = $_POST['visit_date'];
        $visit_time = $_POST['visit_time'];
        $person_to_visit = $_POST['person_to_visit'];
        $checkin = $_POST['checkin'];
        $checkout = $_POST['checkout'];
        $current_picture_path = $_POST['current_picture_path'];

        $picture_path = $current_picture_path;

        // Handle file upload for picture replacement
        if (isset($_FILES['new_picture']) && $_FILES['new_picture']['error'] == UPLOAD_ERR_OK) {
             // Define the directory where uploaded images are stored
            $target_dir = "uploads/visitor_pictures/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_tmp_name = $_FILES['new_picture']['tmp_name'];
            $file_name = basename($_FILES["new_picture"]["name"]);
            $target_file = $target_dir . uniqid() . "_" . $file_name;
            
            if (move_uploaded_file($file_tmp_name, $target_file)) {
                $picture_path = $target_file; 
            }
        }

        $stmt = $conn->prepare("UPDATE visitors SET name=?, contact=?, purpose=?, visit_date=?, visit_time=?, person_to_visit=?, checkin=?, checkout=?, picture_path=? WHERE id=?");
        $stmt->bind_param("sssssssssi", $name, $contact, $purpose, $visit_date, $visit_time, $person_to_visit, $checkin, $checkout, $picture_path, $id);
        $stmt->execute();
        $_SESSION['logs_success'] = "Visitor log for '$name' has been updated successfully.";
    }
    header("Location: Visitor_Logs.php");
    exit();
}

// --- Fetch all visitor records (remove restrictive filter for now) ---
$sql = "SELECT id, name, contact, purpose, DATE(visit_date) as visit_date, visit_time, TIME(checkin) as time_in, TIME(checkout) as time_out, 
               person_to_visit, status, pass_id, picture_path, checkin, checkout 
        FROM visitors 
        ORDER BY id DESC";

$result = $conn->query($sql);

// Debug: Check what we found
if ($result) {
    $total_count = $result->num_rows;
    echo "<!-- Debug: Found $total_count total visitors in database -->";
    // Reset the result pointer to the beginning for the display loop
    $result->data_seek(0);
} else {
    echo "<!-- Debug: Query failed: " . $conn->error . " -->";
}

// Additional debug: Check if table exists and show structure
$table_check = $conn->query("SHOW TABLES LIKE 'visitors'");
$table_exists = $table_check && $table_check->num_rows > 0;
echo "<!-- Debug: Visitors table exists: " . ($table_exists ? "YES" : "NO") . " -->";

if ($table_exists) {
    $structure_result = $conn->query("DESCRIBE visitors");
    if ($structure_result) {
        echo "<!-- Debug: Table structure: ";
        while ($field = $structure_result->fetch_assoc()) {
            echo $field['Field'] . " (" . $field['Type'] . "), ";
        }
        echo " -->";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Visitor Logs | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
    #content main {
        background-color: transparent; /* Ensures main background is correct */
    }
    .cursor-pointer {
        transition: background-color 0.15s ease-in-out;
    }
    
    /* Page-specific white background override */
    body {
        background-color: #eeeeee !important;
    }
    .bg-custom {
        background-color: white !important;
    }
    </style>
</head>
<body class="flex h-screen overflow-hidden" style="background-color: white;">
    
    <?php include 'partials/sidebar.php'; ?>

    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main>
            <div class="w-full px-6 py-6">
                <header class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold">Visitor Logs</h1>
                            <p class="text-sm text-gray-600 mt-1">Manage pre-registered visitors and their check-in/check-out status</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="?export_csv=1" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">Export to CSV</a>
                            <a href="Visitor_Pre_Registration.php" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">Add Pre-Registration</a>
                        </div>
                    </div>
                </header>


                <div class="overflow-x-auto bg-white rounded-xl shadow-md w-full">
                    <table class="w-full divide-y divide-gray-200">
                        <thead style="background-color: white;" class="bg-custom">
                            <tr>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Name</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Contact</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Purpose</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Visit Date</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Visit Time</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Person to Visit</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Time In</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Time Out</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Status</th>
                                <th class="px-8 py-4 text-left text-sm font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="logsTableBody">
                            <?php 
                            // Create a fresh query for display to avoid pointer issues
                            $display_sql = "SELECT id, name, contact, purpose, DATE(visit_date) as visit_date, visit_time, TIME(checkin) as time_in, TIME(checkout) as time_out, 
                                           person_to_visit, status, pass_id, picture_path, checkin, checkout 
                                    FROM visitors 
                                    ORDER BY id DESC";
                            $display_result = $conn->query($display_sql);
                            
                            if ($display_result && $display_result->num_rows > 0): 
                                echo "<!-- Debug: Fresh query found " . $display_result->num_rows . " rows for display -->";
                                $row_count = 0;
                                while ($row = $display_result->fetch_assoc()): 
                                    $row_count++;
                                    echo "<!-- Debug: Processing row $row_count: " . htmlspecialchars($row['name'] ?? 'NO_NAME') . " (ID: " . ($row['id'] ?? 'NO_ID') . ") -->";
                                ?>
                                    <tr class="logRow">
                                        <td class="px-8 py-5 text-sm text-gray-700"><?= htmlspecialchars($row['name']) ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-700"><?= htmlspecialchars($row['contact'] ?? '-') ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-700"><?= htmlspecialchars($row['purpose']) ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-700"><?= htmlspecialchars($row['visit_date'] ?? '-') ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-700"><?= htmlspecialchars($row['visit_time'] ?? '-') ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-700"><?= htmlspecialchars($row['person_to_visit'] ?? '-') ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-700"><?= htmlspecialchars($row['time_in'] ?? '-') ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-700"><?= htmlspecialchars($row['time_out'] ?? '-') ?></td>
                                        
                                        <td class="px-8 py-5">
                                            <?php
                                                $has_checkin = !empty($row['checkin']);
                                                $has_checkout = !empty($row['checkout']);
                                                
                                                if (!$has_checkin) {
                                                    // Not visited yet
                                                    echo '<span class="inline-block px-3 py-1 text-xs font-medium rounded-full text-yellow-700 bg-yellow-100">Scheduled</span>';
                                                } elseif ($has_checkin && !$has_checkout) {
                                                    // Currently visiting
                                                    echo '<span class="inline-block px-3 py-1 text-xs font-medium rounded-full text-green-700 bg-green-100">Visiting</span>';
                                                } else {
                                                    // Visit completed
                                                    echo '<span class="inline-block px-3 py-1 text-xs font-medium rounded-full text-red-700 bg-red-100">Visit Complete</span>';
                                                }
                                            ?>
                                        </td>
                                        
                                        <td class="px-8 py-5 text-sm">
                                            <div class="flex flex-wrap gap-1">
                                                <?php
                                                $current_status = $row['status'] ?? 'Pre-Registered';
                                                $has_checkin = !empty($row['checkin']);
                                                $has_checkout = !empty($row['checkout']);
                                                ?>
                                                
                                                <!-- Time In Button -->
                                                <?php if (!$has_checkin): ?>
                                                    <form method="POST" style="display:inline" onsubmit="return confirm('Check in <?= htmlspecialchars($row['name']) ?>?')">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="checkin" value="1">
                                                        <button type="submit" class="text-black hover:text-gray-600 px-3 py-2.5 rounded text-sm" title="Time In">
                                                            <i class='bx bx-check-circle text-lg ml-2'></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <!-- Time Out Button -->
                                                <?php if ($has_checkin && !$has_checkout): ?>
                                                    <form method="POST" style="display:inline" onsubmit="return confirm('Check out <?= htmlspecialchars($row['name']) ?>?')">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="checkout" value="1">
                                                        <button type="submit" class="text-black hover:text-gray-600 px-3 py-2.5 rounded text-sm" title="Time Out">
                                                            <i class='bx bx-exit text-lg ml-2'></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <!-- Edit Button -->
                                                <button 
                                                    onclick="openEditModal(
                                                        '<?= $row['id'] ?>',
                                                        '<?= htmlspecialchars(addslashes($row['name'])) ?>',
                                                        '<?= htmlspecialchars(addslashes($row['contact'] ?? '')) ?>',
                                                        '<?= htmlspecialchars(addslashes($row['purpose'])) ?>',
                                                        '<?= $row['visit_date'] ?? '' ?>',
                                                        '<?= $row['visit_time'] ?? '' ?>',
                                                        '<?= htmlspecialchars(addslashes($row['person_to_visit'] ?? '')) ?>',
                                                        '<?= $row['checkin'] ?>',
                                                        '<?= $row['checkout'] ?>',
                                                        '<?= htmlspecialchars($row['picture_path'] ?? '') ?>'
                                                    )"
                                                    class="text-black hover:text-gray-600 px-3 py-2.5 rounded text-sm" title="Edit">
                                                    <i class='bx bx-pencil text-lg ml-2'></i>
                                                </button>

                                                <!-- Delete Button -->
                                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete log for <?= htmlspecialchars($row['name']) ?>?')">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="delete" value="1">
                                                    <button type="submit" class="text-black hover:text-gray-600 px-3 py-2.5 rounded text-sm" title="Delete">
                                                        <i class='bx bx-trash text-lg ml-2'></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <?php echo "<!-- Debug: No rows found in display query -->";  ?>
                                <tr><td colspan="10" class="text-center py-6 text-gray-500">No pre-registered visitors found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="editModal" class="hidden fixed inset-0 bg-black/40 items-center justify-center p-4 z-[999]">
                <div class="bg-white rounded-xl w-full max-w-lg shadow-lg p-6 animate-fadeIn">
                    <header class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium">Edit Visitor Log</h2>
                        <button id="modalClose" onclick="closeModal()" class="text-slate-500 hover:text-slate-700">✕</button>
                    </header>

                    <form method="POST" enctype="multipart/form-data" id="editForm" class="space-y-4">
                        <input type="hidden" name="id" id="editId">
                        <input type="hidden" name="editLog" value="1">
                        <input type="hidden" name="current_picture_path" id="picturePathHidden">

                        <div>
                            <label class="block text-sm font-medium mb-1">Visitor Name</label>
                            <input name="name" id="editName" required class="w-full border rounded-md px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Contact Number</label>
                            <input name="contact" id="editContact" class="w-full border rounded-md px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Purpose</label>
                            <input name="purpose" id="editPurpose" required class="w-full border rounded-md px-3 py-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Visit Date</label>
                                <input name="visit_date" id="editVisitDate" type="date" class="w-full border rounded-md px-3 py-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Visit Time</label>
                                <input name="visit_time" id="editVisitTime" type="time" class="w-full border rounded-md px-3 py-2" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Person to Visit</label>
                            <input name="person_to_visit" id="editPersonToVisit" class="w-full border rounded-md px-3 py-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Check In Time</label>
                                <input name="checkin" id="editCheckin" type="datetime-local" class="w-full border rounded-md px-3 py-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Check Out Time</label>
                                <input name="checkout" id="editCheckout" type="datetime-local" class="w-full border rounded-md px-3 py-2" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Replace Picture (Optional)</label>
                            <div id="picturePreviewContainer" class="mb-2 hidden">
                                <span class="text-xs text-gray-500 block mb-1">Current Picture:</span>
                                <img id="modalPicturePreview" src="" alt="Visitor Picture" class="w-20 h-20 object-cover rounded-md border">
                            </div>
                            <input name="new_picture" type="file" accept="image/*" class="w-full border rounded-md px-3 py-2" />
                        </div>

                        <div class="flex justify-end gap-2 pt-3">
                            <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-md border">Cancel</button>
                            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                const modal = document.getElementById('editModal');
                const editId = document.getElementById('editId');
                const editName = document.getElementById('editName');
                const editContact = document.getElementById('editContact');
                const editPurpose = document.getElementById('editPurpose');
                const editVisitDate = document.getElementById('editVisitDate');
                const editVisitTime = document.getElementById('editVisitTime');
                const editPersonToVisit = document.getElementById('editPersonToVisit');
                const editCheckin = document.getElementById('editCheckin');
                const editCheckout = document.getElementById('editCheckout');
                const picturePathHidden = document.getElementById('picturePathHidden');
                const picturePreviewContainer = document.getElementById('picturePreviewContainer');
                const modalPicturePreview = document.getElementById('modalPicturePreview');


                function openEditModal(id, name, contact, purpose, visit_date, visit_time, person_to_visit, checkin, checkout, picture_path) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    
                    editId.value = id;
                    editName.value = name;
                    editContact.value = contact;
                    editPurpose.value = purpose;
                    editVisitDate.value = visit_date;
                    editVisitTime.value = visit_time;
                    editPersonToVisit.value = person_to_visit;
                    
                    // Format datetime strings for datetime-local input
                    editCheckin.value = checkin ? checkin.replace(" ", "T") : "";
                    editCheckout.value = checkout ? checkout.replace(" ", "T") : "";
                    
                    picturePathHidden.value = picture_path; // Store the current path

                    // Display picture preview in modal
                    if (picture_path) {
                        modalPicturePreview.src = picture_path;
                        picturePreviewContainer.classList.remove('hidden');
                    } else {
                        picturePreviewContainer.classList.add('hidden');
                    }
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
                
                // --- NEW FUNCTIONS FOR STATUS CLICK ---
                
                // Function to handle Check In (requires Pass ID input)
                function promptForCheckin(id, name) {
                    // Name is safely passed as a JavaScript string via JSON.parse
                    const passId = prompt(`Enter Pass ID for ${name} to Check In:`);
                    
                    if (passId !== null && passId.trim() !== '') {
                        // Dynamically create and submit the Check In form
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.style.display = 'none';

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        idInput.value = id;

                        const checkinInput = document.createElement('input');
                        checkinInput.type = 'hidden';
                        checkinInput.name = 'checkin';
                        checkinInput.value = '1';

                        const passIdInput = document.createElement('input');
                        passIdInput.type = 'hidden';
                        passIdInput.name = 'pass_id';
                        passIdInput.value = passId.trim();

                        form.appendChild(idInput);
                        form.appendChild(checkinInput);
                        form.appendChild(passIdInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    } else if (passId !== null) {
                        alert("Pass ID is required for Check In.");
                    }
                }
            </script>
        </main>
    </section>

    <!-- Include Success Modal -->
    <?php include 'partials/success_modal.php'; ?>

    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['logs_success'])): ?>
            showSuccessModal('Success!', '<?= addslashes($_SESSION['logs_success']) ?>');
            <?php unset($_SESSION['logs_success']); ?>
        <?php endif; ?>
    </script>
</body>

</html>

