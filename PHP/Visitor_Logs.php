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

// --- Handle CSV Export (Function preserved) ---
if (isset($_GET['export_csv']) && $_GET['export_csv'] == 1) {
    // 1. Check connection first
    if (isset($conn) && !$conn->connect_error) {
        // Set headers for file download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="visitor_logs_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Output CSV Headers (Adjusted to include all relevant fields)
        fputcsv($output, ['ID', 'Name', 'Contact', 'Purpose', 'Visit Date', 'Time In', 'Time Out', 'Person to Visit', 'Status', 'Pass ID', 'Picture Path']);
        
        // Fetch ALL data for export
        $sql = "SELECT 
                    id, name, contact, purpose, visit_date, 
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
        $pass_id = $_POST['pass_id'];
        $stmt = $conn->prepare("UPDATE visitors SET status='Checked In', checkin=NOW(), pass_id=? WHERE id=?");
        $stmt->bind_param("si", $pass_id, $id);
        $stmt->execute();
    } elseif (isset($_POST['checkout'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE visitors SET status='Checked Out', checkout=NOW() WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM visitors WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['editLog'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $purpose = $_POST['purpose'];
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

        $stmt = $conn->prepare("UPDATE visitors SET name=?, purpose=?, checkin=?, checkout=?, picture_path=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $purpose, $checkin, $checkout, $picture_path, $id);
        $stmt->execute();
    }
    header("Location: Visitor_Logs.php");
    exit();
}

// --- Fetch all visitor logs ---
$sql = "SELECT id, name, contact, purpose, DATE(visit_date) as visit_date, TIME(checkin) as time_in, TIME(checkout) as time_out, 
               person_to_visit, status, pass_id, picture_path, checkin, checkout 
        FROM visitors 
        ORDER BY id DESC";

$result = $conn->query($sql);
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
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>

    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Visitor Logs</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="Dashboard.php">Dashboard</a>
                        </li>
                        <li><i class='bx bx-chevron-right' ></i></li>
                        <li>
                            <a class="active" href="Visitor_Logs.php">Visitor Logs</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="max-w-7xl mx-auto p-6">
                <header class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-semibold">Visitor Logs</h1>
                    <div class="flex space-x-2">
                        <a href="?export_csv=1" class="bg-green-600 text-white px-4 py-2 rounded-md shadow hover:bg-green-700">Export to CSV</a>
                        <a href="Visitor_Pre_Registration.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700">Pre-Registration</a>
                    </div>
                </header>

                <div class="overflow-x-auto bg-white rounded-xl shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Purpose</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Pass ID</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Time In</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Time Out</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Status (Click to Action)</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Other Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="logsTableBody">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="logRow">
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['name']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['purpose']) ?></td>
                                        <td class="px-6 py-4 text-sm font-mono text-gray-800"><?= htmlspecialchars($row['pass_id'] ?? '-') ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['time_in'] ?? '-') ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['time_out'] ?? '-') ?></td>
                                        
                                        <td class="px-6 py-4">
                                            <?php
                                                $status = htmlspecialchars($row['status']);
                                                // Use json_encode for safe JavaScript string literals, especially for names with quotes
                                                $js_name = json_encode($row['name']); 
                                                $id = $row['id'];
                                                
                                                $status_class = '';
                                                $action_html = '';
                                                
                                                if ($status == 'Checked In') {
                                                    $status_class = 'text-green-700 bg-green-100 cursor-pointer hover:bg-green-200';
                                                    // Hidden form for Check Out submission
                                                    $action_html = "
                                                        <form id='form_checkout_{$id}' method='POST' onsubmit='return confirm(\"Check out " . htmlspecialchars($row['name']) . "?\")' style='display:none;'>
                                                            <input type='hidden' name='id' value='{$id}'>
                                                            <input type='hidden' name='checkout' value='1'>
                                                        </form>
                                                        <span onclick='document.getElementById(\"form_checkout_{$id}\").submit()' class='inline-block px-3 py-1 text-xs font-medium rounded-full {$status_class}' title='Click to Check Out'>
                                                            {$status}
                                                        </span>
                                                    ";
                                                } elseif ($status == 'Pre-Registered') {
                                                    $status_class = 'text-yellow-700 bg-yellow-100 cursor-pointer hover:bg-yellow-200';
                                                    // JS function prompts for Pass ID and submits Check In
                                                    $action_html = "
                                                        <span onclick='promptForCheckin({$id}, {$js_name})' class='inline-block px-3 py-1 text-xs font-medium rounded-full {$status_class}' title='Click to Check In'>
                                                            {$status}
                                                        </span>
                                                    ";
                                                } else { // Checked Out (not clickable)
                                                    $status_class = 'text-blue-700 bg-blue-100';
                                                    $action_html = "
                                                        <span class='inline-block px-3 py-1 text-xs font-medium rounded-full {$status_class}'>
                                                            {$status}
                                                        </span>
                                                    ";
                                                }
                                                echo $action_html;
                                            ?>
                                        </td>
                                        
                                        <td class="px-6 py-4 text-sm flex gap-2">
                                            <button 
                                                onclick="openEditModal(
                                                    '<?= $row['id'] ?>',
                                                    '<?= htmlspecialchars(addslashes($row['name'])) ?>',
                                                    '<?= htmlspecialchars(addslashes($row['purpose'])) ?>',
                                                    '<?= $row['checkin'] ?>',
                                                    '<?= $row['checkout'] ?>',
                                                    '<?= htmlspecialchars($row['picture_path'] ?? '') ?>'
                                                )"
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">Edit</button>

                                            <form method="POST" onsubmit="return confirm('Delete log for <?= htmlspecialchars($row['name']) ?>?')">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="delete" value="1">
                                                <button type="submit" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded text-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-6 text-gray-500">No visitor logs found.</td></tr>
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
                            <label class="block text-sm font-medium mb-1">Purpose</label>
                            <input name="purpose" id="editPurpose" required class="w-full border rounded-md px-3 py-2" />
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
                            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                const modal = document.getElementById('editModal');
                const editId = document.getElementById('editId');
                const editName = document.getElementById('editName');
                const editPurpose = document.getElementById('editPurpose');
                const editCheckin = document.getElementById('editCheckin');
                const editCheckout = document.getElementById('editCheckout');
                const picturePathHidden = document.getElementById('picturePathHidden');
                const picturePreviewContainer = document.getElementById('picturePreviewContainer');
                const modalPicturePreview = document.getElementById('modalPicturePreview');


                function openEditModal(id, name, purpose, checkin, checkout, picture_path) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    
                    editId.value = id;
                    editName.value = name;
                    editPurpose.value = purpose;
                    
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

    <script src="../JS/script.js"></script>
</body>
</html>