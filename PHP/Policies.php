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

// --- ADD or EDIT Policy ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['savePolicy'])) {
    $id = $_POST['policyId'] ?? '';
    $title = $_POST['title'];
    $role = $_POST['role'];
    $short = $_POST['short'];
    $details = $_POST['details'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE policies SET title=?, role=?, short_desc=?, full_policy=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $role, $short, $details, $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO policies (title, role, short_desc, full_policy) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $role, $short, $details);
        $stmt->execute();
    }

    echo "<script>window.location.href='Policies.php';</script>";
    exit;
}

// --- DELETE Policy ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM policies WHERE id=$id");
    echo "<script>window.location.href='Policies.php';</script>";
    exit;
}

// --- GET Policies ---
$result = $conn->query("SELECT * FROM policies ORDER BY id DESC");
$policies = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Policies Management | TNVS Dashboard</title>
    
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
      

            <div class="max-w-7xl mx-auto p-6">
              <header class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold">Policies Management</h1>
                <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Policy</button>
              </header>

              <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if (count($policies) > 0): ?>
                  <?php foreach ($policies as $p): ?>
                    <article class="bg-white rounded-xl p-4 shadow hover:shadow-md transition">
                      <div class="flex items-start justify-between">
                        <div>
                          <h3 class="text-lg font-semibold"><?= htmlspecialchars($p['title']) ?></h3>
                          <p class="text-xs mt-1 rounded-full px-2 py-1 inline-block 
                            <?= $p['role'] == 'admin-only' ? 'bg-rose-100 text-rose-700' : ($p['role'] == 'administrative' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700') ?>">
                            <?= strtoupper($p['role']) ?>
                          </p>
                        </div>
                        <div class="flex gap-2">
                          <button onclick="editPolicy('<?= $p['id'] ?>','<?= htmlspecialchars(addslashes($p['title'])) ?>','<?= $p['role'] ?>','<?= htmlspecialchars(addslashes($p['short_desc'])) ?>','<?= htmlspecialchars(addslashes($p['full_policy'])) ?>')" class="text-sm px-2 py-1 border rounded">Edit</button>
                          <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this policy?')" class="text-sm px-2 py-1 border rounded text-red-600">Delete</a>
                        </div>
                      </div>
                      <p class="text-sm mt-3 text-slate-600"><?= htmlspecialchars($p['short_desc']) ?></p>
                    </article>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="text-center py-10 text-slate-500 col-span-full">No policies found.</div>
                <?php endif; ?>
              </section>
            </div>
            <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-[999]">
              <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6 animate-fadeIn">
                <header class="flex items-center justify-between mb-4">
                  <h2 id="modalTitle" class="text-lg font-medium">Add Policy</h2>
                  <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
                </header>

                <form id="policyForm" method="POST" class="space-y-4">
                  <input type="hidden" name="policyId" id="policyId">
                  <div>
                    <label class="block text-sm font-medium mb-1">Title</label>
                    <input id="title" name="title" required class="w-full border rounded-md px-3 py-2" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1">Role</label>
                    <select id="role" name="role" class="w-full border rounded-md px-3 py-2">
                      <option value="administrative">Administrative</option>
                      <option value="student">Student</option>
                      <option value="admin-only">Admin-only</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1">Short Description</label>
                    <input id="short" name="short" class="w-full border rounded-md px-3 py-2" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1">Full Policy (details)</label>
                    <textarea id="details" name="details" rows="6" class="w-full border rounded-md px-3 py-2"></textarea>
                  </div>
                  <div class="flex justify-end gap-2">
                    <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
                    <button type="submit" name="savePolicy" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Policy</button>
                  </div>
                </form>
              </div>
            </div>
        </main>
    </section>

    <script>
      const modal = document.getElementById('modal');
      const btnAdd = document.getElementById('btnAdd');
      const modalClose = document.getElementById('modalClose');
      const cancelBtn = document.getElementById('cancelBtn');
      const modalTitle = document.getElementById('modalTitle');
      const idInput = document.getElementById('policyId');
      const titleInput = document.getElementById('title');
      const roleInput = document.getElementById('role');
      const shortInput = document.getElementById('short');
      const detailsInput = document.getElementById('details');
      const policyForm = document.getElementById('policyForm');

      function openModal(mode) {
        modalTitle.textContent = mode;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('policyForm').reset();
        idInput.value = '';
      }

      btnAdd.addEventListener('click', () => openModal('Add Policy'));
      modalClose.addEventListener('click', closeModal);
      cancelBtn.addEventListener('click', closeModal);

      function editPolicy(id, title, role, short, details) {
        idInput.value = id;
        titleInput.value = title;
        roleInput.value = role;
        shortInput.value = short;
        detailsInput.value = details;
        openModal('Edit Policy');
      }

        // NOTE: The rest of the original JavaScript (from 'var editingId = null;' onwards) 
        // contained client-side rendering logic that appears unfinished or conflicting with the PHP server-side implementation.
        // It has been commented out or removed from the final structure for correctness, but the essential functions 
        // (openModal, closeModal, editPolicy, and the form listener) are retained as requested.
        
      policyForm.addEventListener('submit', function() {
        // Let PHP handle it — no JS processing needed
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      });
    </script>
    <script src="../JS/script.js"></script>
</body>
</html>