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

// --- NEW: Handle Return from Archive ---
if (isset($_GET['return'])) {
    $docId = intval($_GET['return']);

    // Step 1: Delete the 'Archive' permission
    $deleteStmt = $conn->prepare("DELETE FROM document_permissions WHERE document_id = ? AND permission_type = 'Archive'");
    $deleteStmt->bind_param("i", $docId);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Step 2: Check if any permissions are left for this document
    $checkStmt = $conn->prepare("SELECT id FROM document_permissions WHERE document_id = ? LIMIT 1");
    $checkStmt->bind_param("i", $docId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $checkStmt->close();

    // Step 3: If no permissions are left, add a default 'View' permission to make it active again
    if ($result->num_rows === 0) {
        $role = "Admin"; // Default role
        $insertStmt = $conn->prepare("INSERT INTO document_permissions (document_id, user_role, permission_type) VALUES (?, ?, 'View')");
        $insertStmt->bind_param("is", $docId, $role);
        $insertStmt->execute();
        $insertStmt->close();
    }
    
    // Step 4: Redirect with success message
    $_SESSION['view_success'] = "Document has been returned from archive successfully.";
    header("Location: View_Records.php");
    exit;
}

// --- Query to fetch currently archived documents ---
$archivedDocs = $conn->query("
    SELECT DISTINCT d.id, d.title, d.filename, d.filepath, d.uploaded_at, dp.user_role 
    FROM documents d
    JOIN document_permissions dp ON d.id = dp.document_id
    WHERE dp.permission_type = 'Archive'
    ORDER BY d.uploaded_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Archived Documents | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95);} to { opacity: 1; transform: scale(1);} }
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
                        <h1 class="text-2xl font-light text-gray-900">Archived Documents</h1>
                        <p class="text-sm text-gray-500 mt-1">View and restore archived documents</p>
                    </div>
                    <a href="Document_Access_Permissions.php"
                        class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Back to Permissions
                    </a>
                </div>
            </div>

            <!-- Minimalist Table -->
            <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archived By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($archivedDocs && $archivedDocs->num_rows > 0): ?>
                      <?php while($row = $archivedDocs->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['title']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['filename']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['user_role']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M j, Y', strtotime($row['uploaded_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex gap-2">
                                    <button onclick="openPreview('<?= $row['filepath'] ?>','<?= $row['filename'] ?>')"
                                            class="text-gray-400 hover:text-blue-600 transition-colors" title="View">
                                        <i class='bx bx-show text-lg'></i>
                                    </button>
                                    <button onclick="openReturnModal(<?= $row['id'] ?>)"
                                            class="text-gray-400 hover:text-green-600 transition-colors" title="Return">
                                        <i class='bx bx-undo text-lg'></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr><td colspan="5" class="text-center py-6 text-gray-500">No archived documents found.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div id="viewModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1000]">
              <div class="bg-white w-[95%] max-w-4xl rounded-2xl shadow-xl p-6 relative animate-fadeIn">
                <h2 class="text-xl font-semibold mb-4" id="viewTitle">Document Preview</h2>
                <div class="overflow-auto max-h-[70vh]" id="viewContent"></div>
                <div class="flex justify-end mt-4 gap-2">
                  <button onclick="document.getElementById('viewModal').classList.add('hidden')" 
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">Close</button>
                </div>
              </div>
            </div>

            <div id="returnModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1001]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-undo text-6xl text-green-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Return Document</h2>
                  <p class="text-gray-600 mb-6">Are you sure you want to return this document to the active list? You'll be able to set new permissions for it.</p>
                  <div class="flex justify-center gap-4">
                    <button onclick="closeReturnModal()" 
                      class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">Cancel</button>
                    <button onclick="confirmReturn()" 
                      class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">Return</button>
                  </div>
                </div>
              </div>
            </div>

            <div id="archiveSuccessModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1002]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-check-circle text-6xl text-purple-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Document Archived</h2>
                  <p class="text-gray-600 mb-6">The document has been archived successfully!</p>
                  <button onclick="closeSuccessModal('archiveSuccessModal')" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">OK</button>
                </div>
              </div>
            </div>
        </main>
    </section>

    <script>
    function openPreview(src, name) {
      const ext = name.split('.').pop().toLowerCase();
      const viewModal = document.getElementById("viewModal");
      const viewContent = document.getElementById("viewContent");
      const viewTitle = document.getElementById("viewTitle");

      viewTitle.textContent = name;
      // Note: Adjusted the path based on your existing script's path handling
      const fullURL = window.location.origin + "/TNVS_PROJECT" + src.substring(2);

      if(["png","jpg","jpeg"].includes(ext)){
        viewContent.innerHTML = `<img src="${fullURL}" class="w-full h-auto object-contain">`;
      } else if(ext === "pdf"){
        viewContent.innerHTML = `<iframe src="${fullURL}" class="w-full h-[70vh]" frameborder="0"></iframe>`;
      } else if(["doc","docx","xls","xlsx","ppt","pptx"].includes(ext)){
        // Added encodeURIComponent for robustness with file paths
        const encodedURL = encodeURIComponent(fullURL);
        viewContent.innerHTML = `<iframe src="https://docs.google.com/gview?url=${encodedURL}&embedded=true" class="w-full h-[70vh]" frameborder="0"></iframe>`;
      } else {
        viewContent.innerHTML = `<p class="text-gray-700">Preview not supported for this file type. Please download it.</p>`;
      }

      viewModal.classList.remove("hidden");
    }

    // Return Modal Functions
    let currentReturnDocId = null;

    function openReturnModal(docId) {
        currentReturnDocId = docId;
        document.getElementById('returnModal').classList.remove('hidden');
    }

    function closeReturnModal() {
        currentReturnDocId = null;
        document.getElementById('returnModal').classList.add('hidden');
    }

    function confirmReturn() {
        if (currentReturnDocId) {
            window.location.href = `View_Records.php?return=${currentReturnDocId}`;
        }
    }

    // Success Modal Functions
    function closeSuccessModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        // Simple redirect to clear the URL parameters
        if (modalId === 'archiveSuccessModal') {
            window.location.href = 'View_Records.php';
        }
    }

    function showSuccessModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    // Check for success parameters on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const isArchived = urlParams.get('archived');

        if (isArchived) {
            showSuccessModal('archiveSuccessModal');
        }
    });
    </script>


    <!-- Include Success Modal -->
    <?php include 'partials/success_modal.php'; ?>

    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['view_success'])): ?>
            showSuccessModal('Success!', '<?= addslashes($_SESSION['view_success']) ?>');
            <?php unset($_SESSION['view_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>