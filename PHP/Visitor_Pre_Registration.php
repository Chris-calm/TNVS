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

// Define the directory where uploaded images will be stored
$target_dir = "uploads/visitor_pictures/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// --- Handle Add/Edit/Delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Sanitize and get common POST variables
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $purpose = $_POST['purpose'];
    $visit_date = $_POST['visit_date'];
    $visit_time = $_POST['visit_time'];
    $person_to_visit = $_POST['person_to_visit'];
    $existing_picture_path = $_POST['existing_picture_path'] ?? null; // For edit to keep existing path

    $picture_path = $existing_picture_path; // Default to existing path or null

    // Handle file upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['picture']['tmp_name'];
        $file_name = basename($_FILES["picture"]["name"]);
        $target_file = $target_dir . uniqid() . "_" . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Basic file validation (you should add more robust checks)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($file_tmp_name, $target_file)) {
            $picture_path = $target_file; // Update the path
        } else {
            // For simplicity here, we'll just ignore the upload if it fails,
            // keeping the $picture_path as $existing_picture_path or null.
        }
    }

    if ($action === 'add' || $action === 'edit') {
        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO visitors (name, contact, purpose, visit_date, visit_time, person_to_visit, picture_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $contact, $purpose, $visit_date, $visit_time, $person_to_visit, $picture_path);
            $stmt->execute();
            $_SESSION['visitor_success'] = "Visitor '$name' has been pre-registered successfully.";
        } else {
            // For edit, if a new file wasn't uploaded, $picture_path retains the $existing_picture_path.
            $stmt = $conn->prepare("UPDATE visitors SET name=?, contact=?, purpose=?, visit_date=?, visit_time=?, person_to_visit=?, picture_path=? WHERE id=?");
            $stmt->bind_param("sssssssi", $name, $contact, $purpose, $visit_date, $visit_time, $person_to_visit, $picture_path, $id);
            $stmt->execute();
            $_SESSION['visitor_success'] = "Visitor '$name' has been updated successfully.";
        }
    }

    if ($action === 'delete') {
        $id = $_POST['id'];
        // Optional: Get the file path before deleting the record and delete the file from the server
        $result = $conn->query("SELECT picture_path FROM visitors WHERE id='$id'");
        if ($result && $row = $result->fetch_assoc() && !empty($row['picture_path'])) {
            if (file_exists($row['picture_path'])) {
                unlink($row['picture_path']);
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM visitors WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['visitor_success'] = "Visitor has been deleted successfully.";
    }

    header("Location: Visitor_Pre_Registration.php");
    exit();
}

// --- Fetch visitors ---
$visitors = $conn->query("SELECT * FROM visitors ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Visitor Pre-Registration | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
    #content main {
        background-color: transparent; /* Ensures main background is correct */
    }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>

    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main class="max-w-7xl mx-auto px-4 py-8">
            <!-- Minimalist Header -->
            <div class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-light text-gray-900">Visitor Pre-Registration</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage visitor pre-registrations and appointments</p>
                    </div>
                    <button id="btnAdd" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Add Visitor
                    </button>
                </div>
                
                <!-- Search -->
                <div class="mb-6">
                    <input id="search" type="search" placeholder="Search visitors..." 
                           class="w-full max-w-md border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" />
                </div>
            </div>

                <section id="visitorGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if ($visitors->num_rows > 0): ?>
                        <?php while ($v = $visitors->fetch_assoc()): ?>
                            <article class="visitorCard bg-white rounded-lg border border-gray-100 p-4 hover:border-gray-200 transition-colors" 
                                    data-id="<?= $v['id'] ?>"
                                    data-name="<?= htmlspecialchars($v['name']) ?>"
                                    data-contact="<?= htmlspecialchars($v['contact']) ?>"
                                    data-purpose="<?= htmlspecialchars($v['purpose']) ?>"
                                    data-date="<?= $v['visit_date'] ?>"
                                    data-time="<?= $v['visit_time'] ?>"
                                    data-person="<?= htmlspecialchars($v['person_to_visit']) ?>"
                                    data-picture="<?= htmlspecialchars($v['picture_path'] ?? '') ?>"> 
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center space-x-3">
                                        <?php 
                                            $image_path = $v['picture_path'] ?? null;
                                            // The path logic here is based on the original file's use of a relative path from the script's location
                                            $image_url = !empty($image_path) && file_exists($image_path) ? $image_path : 'https://via.placeholder.com/60?text=No+Pic'; 
                                        ?>
                                        <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 bg-gray-100">
                                            <?php if ($image_url !== 'https://via.placeholder.com/60?text=No+Pic'): ?>
                                                <img src="<?= htmlspecialchars($image_url) ?>" alt="Visitor Picture" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i class='bx bxs-user-circle text-6xl text-gray-400'></i>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div>
                                            <h3 class="visitorName font-medium text-gray-900"><?= htmlspecialchars($v['name']) ?></h3>
                                            <p class="visitorPurpose text-sm text-gray-500"><?= htmlspecialchars($v['purpose']) ?></p>
                                        </div>
                                    </div>

                                    <div class="flex gap-1 flex-shrink-0">
                                        <button class="btnEdit p-2 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                            <i class='bx bx-edit text-lg'></i>
                                        </button>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this visitor?');">
                                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btnDelete p-2 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                                <i class='bx bx-trash text-lg'></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="mt-3 space-y-1 text-sm text-gray-500">
                                    <p><span class="font-medium">Visiting:</span> <?= htmlspecialchars($v['person_to_visit']) ?></p>
                                    <p><span class="font-medium">Date:</span> <?= date('M j, Y', strtotime($v['visit_date'])) ?> at <?= date('g:i A', strtotime($v['visit_time'])) ?></p>
                                    <p><span class="font-medium">Contact:</span> <?= htmlspecialchars($v['contact']) ?></p>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div id="emptyState" class="text-center py-10 text-slate-500 col-span-full">
                            No visitors pre-registered yet. Click "Add Visitor" to start.
                        </div>
                    <?php endif; ?>
                </section>

                <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
                    <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6 animate-fadeIn">
                        <header class="flex items-center justify-between mb-4">
                            <h2 id="modalTitle" class="text-lg font-medium">Add Visitor</h2>
                            <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
                        </header>

                        <form id="visitorForm" method="POST" class="space-y-4" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="visitorId">
                            <input type="hidden" name="action" id="formAction" value="add">
                            <input type="hidden" name="existing_picture_path" id="existingPicturePath">

                            <div>
                                <label class="block text-sm font-medium mb-1">Visitor Name</label>
                                <input name="name" id="visitorName" required class="w-full border rounded-md px-3 py-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Contact Number</label>
                                <input name="contact" id="contactNumber" required class="w-full border rounded-md px-3 py-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Purpose of Visit</label>
                                <input name="purpose" id="purpose" required class="w-full border rounded-md px-3 py-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Visitor Picture (Optional)</label>
                                <input name="picture" id="visitorPicture" type="file" accept="image/*" class="w-full border rounded-md px-3 py-2" />
                                <div id="currentPicture" class="mt-2 text-sm"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Date of Visit</label>
                                    <input name="visit_date" id="visitDate" type="date" required class="w-full border rounded-md px-3 py-2" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Time of Visit</label>
                                    <input name="visit_time" id="visitTime" type="time" required class="w-full border rounded-md px-3 py-2" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Person to Visit</label>
                                <input name="person_to_visit" id="personToVisit" required class="w-full border rounded-md px-3 py-2" />
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
                                <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Visitor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                const modal = document.getElementById('modal');
                const modalClose = document.getElementById('modalClose');
                const btnAdd = document.getElementById('btnAdd');
                const cancelBtn = document.getElementById('cancelBtn');
                const modalTitle = document.getElementById('modalTitle');
                const visitorForm = document.getElementById('visitorForm');
                const formAction = document.getElementById('formAction');
                const visitorId = document.getElementById('visitorId');
                const visitorName = document.getElementById('visitorName');
                const contactNumber = document.getElementById('contactNumber');
                const purpose = document.getElementById('purpose');
                const visitDate = document.getElementById('visitDate');
                const visitTime = document.getElementById('visitTime');
                const personToVisit = document.getElementById('personToVisit');
                const visitorPicture = document.getElementById('visitorPicture');
                const existingPicturePath = document.getElementById('existingPicturePath');
                const currentPicture = document.getElementById('currentPicture');

                function openModal(mode, data = null) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modalTitle.textContent = mode === 'add' ? 'Add Visitor' : 'Edit Visitor';
                    formAction.value = mode;
                    visitorPicture.value = ''; // Clear file input on modal open

                    if (data) {
                        visitorId.value = data.id;
                        visitorName.value = data.name;
                        contactNumber.value = data.contact;
                        purpose.value = data.purpose;
                        visitDate.value = data.date;
                        visitTime.value = data.time;
                        personToVisit.value = data.person;
                        existingPicturePath.value = data.picture;

                        if (data.picture) {
                            currentPicture.innerHTML = `Current Picture: <a href="${data.picture}" target="_blank" class="text-indigo-600 hover:underline">View Image</a> (Upload new to replace)`;
                        } else {
                            currentPicture.innerHTML = 'No current picture.';
                        }

                    } else {
                        visitorForm.reset();
                        visitorId.value = '';
                        existingPicturePath.value = '';
                        currentPicture.innerHTML = '';
                    }
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                btnAdd.addEventListener('click', () => openModal('add'));
                modalClose.addEventListener('click', closeModal);
                cancelBtn.addEventListener('click', (e) => { e.preventDefault(); closeModal(); });

                // Attach edit button events
                document.querySelectorAll('.btnEdit').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const card = btn.closest('.visitorCard');
                        openModal('edit', {
                            id: card.dataset.id,
                            name: card.dataset.name,
                            contact: card.dataset.contact,
                            purpose: card.dataset.purpose,
                            date: card.dataset.date,
                            time: card.dataset.time,
                            person: card.dataset.person,
                            picture: card.dataset.picture // Passed picture path
                        });
                    });
                });

                // Simple client-side search/filter
                const searchInput = document.getElementById('search');
                const visitorCards = document.querySelectorAll('.visitorCard');

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    visitorCards.forEach(card => {
                        const name = card.dataset.name.toLowerCase();
                        const purpose = card.dataset.purpose.toLowerCase();
                        const person = card.dataset.person.toLowerCase();
                        
                        if (name.includes(searchTerm) || purpose.includes(searchTerm) || person.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Handle empty state visibility after filtering
                    const visibleCards = Array.from(visitorCards).filter(card => card.style.display !== 'none');
                    const emptyState = document.getElementById('emptyState');
                    if (emptyState) {
                        emptyState.style.display = visibleCards.length === 0 ? 'block' : 'none';
                    }
            </script>
        </main>
    </section>

    <!-- Include Success Modal -->
    <?php include 'partials/success_modal.php'; ?>

    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['visitor_success'])): ?>
            showSuccessModal('Success!', '<?= addslashes($_SESSION['visitor_success']) ?>');
            <?php unset($_SESSION['visitor_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>