<?php
session_start(); // Added session start for consistency

include 'db_connect.php';

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
            // Handle upload error (e.g., file too large, wrong type, move failed)
            // For simplicity here, we'll just ignore the upload if it fails,
            // keeping the $picture_path as $existing_picture_path or null.
        }
    }

    if ($action === 'add' || $action === 'edit') {
        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO visitors (name, contact, purpose, visit_date, visit_time, person_to_visit, picture_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $contact, $purpose, $visit_date, $visit_time, $person_to_visit, $picture_path);
        } else {
            // For edit, if a new file wasn't uploaded, $picture_path retains the $existing_picture_path.
            $stmt = $conn->prepare("UPDATE visitors SET name=?, contact=?, purpose=?, visit_date=?, visit_time=?, person_to_visit=?, picture_path=? WHERE id=?");
            $stmt->bind_param("sssssssi", $name, $contact, $purpose, $visit_date, $visit_time, $person_to_visit, $picture_path, $id);
        }
        $stmt->execute();
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

        <main>
            <div class="max-w-6xl mx-auto p-6">
                <header class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-semibold">Visitor Pre-Registration</h1>
                    <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Visitor</button>
                </header>

                <div class="mb-4 flex items-center gap-2">
                    <input id="search" type="search" placeholder="Search visitors..." class="border rounded-md px-3 py-2 w-80" />
                </div>

                <section id="visitorGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if ($visitors->num_rows > 0): ?>
                        <?php while ($v = $visitors->fetch_assoc()): ?>
                            <article class="visitorCard bg-white rounded-xl p-4 shadow hover:shadow-md transition" 
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
                                        <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0 border-2 border-indigo-200">
                                            <?php if ($image_url !== 'https://via.placeholder.com/60?text=No+Pic'): ?>
                                                <img src="<?= htmlspecialchars($image_url) ?>" alt="Visitor Picture" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i class='bx bxs-user-circle text-6xl text-gray-400'></i>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div>
                                            <h3 class="visitorName text-lg font-semibold"><?= htmlspecialchars($v['name']) ?></h3>
                                            <p class="visitorPurpose text-sm text-slate-600"><?= htmlspecialchars($v['purpose']) ?></p>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 flex-shrink-0">
                                        <button class="btnEdit text-sm px-2 py-1 border rounded">Edit</button>
                                        <form method="POST" style="display:inline">
                                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btnDelete text-sm px-2 py-1 border rounded text-red-600">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="visitorDetails text-sm mt-2 text-slate-600">
                                    Visiting **<?= htmlspecialchars($v['person_to_visit']) ?>** on **<?= $v['visit_date'] ?>** at **<?= $v['visit_time'] ?>**<br>
                                    Contact: <?= htmlspecialchars($v['contact']) ?>
                                </p>
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
                });
            </script>
        </main>
    </section>

    <script src="../JS/script.js"></script>
</body>
</html>