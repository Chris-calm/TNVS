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

// --- Handle Add Permission ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['addPermission'])) {
    $docId = intval($_POST['docId']);
    $userRole = $_POST['userRole'];

    $stmt = $conn->prepare("INSERT INTO document_permissions (document_id, user_role, permission_type) VALUES (?, ?, ?)");

    $viewPermission = 'View';
    $stmt->bind_param("iss", $docId, $userRole, $viewPermission);
    $stmt->execute();

    if (!empty($_POST['permissionTypes']) && is_array($_POST['permissionTypes'])) {
        foreach ($_POST['permissionTypes'] as $permissionType) {
            $cleanPermissionType = htmlspecialchars($permissionType);
            if ($cleanPermissionType !== 'View') {
                $stmt->bind_param("iss", $docId, $userRole, $cleanPermissionType);
                $stmt->execute();
            }
        }
    }
    $stmt->close();
    $_SESSION['permissions_success'] = "Document permissions have been added successfully.";
    header("Location: Document_Access_Permissions.php");
    exit;
}

// --- Handle Update Permission from Edit Modal ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['updatePermission'])) {
    $docId = intval($_POST['editDocId']);
    $userRole = $_POST['editUserRole'];

    // Log the edit action to document_actions table
    $actionStmt = $conn->prepare("INSERT INTO document_actions (document_id, user_role, action_type, action_description, ip_address, user_agent) VALUES (?, ?, 'Edit', ?, ?, ?)");
    $actionDescription = "Permissions updated for document ID: $docId";
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $actionStmt->bind_param("issss", $docId, $userRole, $actionDescription, $ipAddress, $userAgent);
    $actionStmt->execute();
    $actionStmt->close();

    $deleteStmt = $conn->prepare("DELETE FROM document_permissions WHERE document_id = ? AND user_role = ?");
    $deleteStmt->bind_param("is", $docId, $userRole);
    $deleteStmt->execute();
    $deleteStmt->close();

    $insertStmt = $conn->prepare("INSERT INTO document_permissions (document_id, user_role, permission_type) VALUES (?, ?, ?)");

    $viewPermission = 'View';
    $insertStmt->bind_param("iss", $docId, $userRole, $viewPermission);
    $insertStmt->execute();

    if (!empty($_POST['permissionTypes']) && is_array($_POST['permissionTypes'])) {
        foreach ($_POST['permissionTypes'] as $permissionType) {
            $cleanPermissionType = htmlspecialchars($permissionType);
            if ($cleanPermissionType !== 'View') {
                $insertStmt->bind_param("iss", $docId, $userRole, $cleanPermissionType);
                $insertStmt->execute();
            }
        }
    }
    $insertStmt->close();

    $_SESSION['permissions_success'] = "Document permissions have been updated successfully.";
    header("Location: Document_Access_Permissions.php");
    exit;
}


// --- Handle Deleting a Document Permanently ---
if (isset($_GET['deleteDocument'])) {
    $docId = intval($_GET['deleteDocument']);

    // Get document info and user role for logging
    $docInfoStmt = $conn->prepare("SELECT title, filepath FROM documents WHERE id = ?");
    $docInfoStmt->bind_param("i", $docId);
    $docInfoStmt->execute();
    $docResult = $docInfoStmt->get_result();
    $docInfo = $docResult->fetch_assoc();
    $docInfoStmt->close();

    // Log the delete action to document_actions table
    $actionStmt = $conn->prepare("INSERT INTO document_actions (document_id, user_role, action_type, action_description, ip_address, user_agent) VALUES (?, ?, 'Delete', ?, ?, ?)");
    $userRole = "Admin"; // Assuming admin role for delete operations
    $actionDescription = "Document permanently deleted: " . ($docInfo['title'] ?? "Unknown Document");
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $actionStmt->bind_param("issss", $docId, $userRole, $actionDescription, $ipAddress, $userAgent);
    $actionStmt->execute();
    $actionStmt->close();

    $pathStmt = $conn->prepare("SELECT filepath FROM documents WHERE id = ?");
    $pathStmt->bind_param("i", $docId);
    $pathStmt->execute();
    $result = $pathStmt->get_result();
    if ($fileRow = $result->fetch_assoc()) {
        // Correct path assumption based on Upload_Document.php using "uploads/"
        $filePath = '../' . $fileRow['filepath']; 
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    $pathStmt->close();

    $permStmt = $conn->prepare("DELETE FROM document_permissions WHERE document_id = ?");
    $permStmt->bind_param("i", $docId);
    $permStmt->execute();
    $permStmt->close();

    $docStmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
    $docStmt->bind_param("i", $docId);
    $docStmt->execute();
    $docStmt->close();

    $_SESSION['permissions_success'] = "Document has been deleted successfully.";
    header("Location: Document_Access_Permissions.php");
    exit;
}


// --- Handle Archive (Cleanly archives the document) ---
if (isset($_GET['archive'])) {
    $docId = intval($_GET['archive']);
    $role = "Admin"; 

    $deleteStmt = $conn->prepare("DELETE FROM document_permissions WHERE document_id = ?");
    $deleteStmt->bind_param("i", $docId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $insertStmt = $conn->prepare("INSERT INTO document_permissions (document_id, user_role, permission_type) VALUES (?, ?, 'Archive')");
    $insertStmt->bind_param("is", $docId, $role);
    $insertStmt->execute();
    $insertStmt->close();
    
    $_SESSION['permissions_success'] = "Document has been archived successfully.";
    header("Location: Document_Access_Permissions.php");
    exit;
}


// --- Fetch documents for the modal dropdown ---
$documents = $conn->query("SELECT * FROM documents ORDER BY uploaded_at DESC");

// --- Fetch permissions, hiding documents that are PURELY archived ---
$permissions = $conn->query("
    SELECT 
        d.id AS doc_id, 
        d.title, 
        d.filename, 
        d.filepath, 
        dp.user_role, 
        GROUP_CONCAT(DISTINCT dp.permission_type ORDER BY dp.permission_type) AS granted_permissions
    FROM document_permissions dp 
    JOIN documents d ON dp.document_id = d.id 
    GROUP BY d.id, dp.user_role
    HAVING granted_permissions != 'Archive' AND granted_permissions NOT LIKE 'Archive,View'
    ORDER BY d.title ASC, dp.user_role ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Document Access Permissions | TNVS Dashboard</title>

    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95);} to { opacity: 1; transform: scale(1);} }
        .animate-fadeIn { animation: fadeIn 0.25s ease-out; }

        .permission-box {
          border: 2px solid #d1d5db;
          border-radius: 0.5rem;
          padding: 1rem;
          cursor: pointer;
          transition: all 0.2s;
          text-align: center;
          font-weight: 500;
          background-color: white;
        }
        .permission-box:hover {
          border-color: #60a5fa;
          background-color: #eff6ff;
        }
        .permission-box.selected {
          border-color: #10b981;
          background-color: #dcfce7;
          color: #166534;
        }
        .permission-box.selected:before {
          content: '✔ '; 
          color: #059669;
          font-weight: bold;
        }
        #content main {
            background-color: transparent; 
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
                        <h1 class="text-2xl font-light text-gray-900">Document Permissions</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage document access and permissions</p>
                    </div>
                    <button onclick="document.getElementById('permissionModal').classList.remove('hidden')"
                        class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Add Permission
                    </button>
                </div>
            </div>

            <!-- Minimalist Table -->
            <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-100">
                    <tr>
                      <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Document Title</th>
                      <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">User/Role</th>
                      <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Assigned Permissions</th>
                      <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <?php if ($permissions && $permissions->num_rows > 0): ?>
                      <?php while($row = $permissions->fetch_assoc()): ?>
                        <?php $permissionList = explode(',', $row['granted_permissions']); ?>
                        <tr>
                          <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['title']) ?></td>
                          <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['user_role']) ?></td>
                          <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars(str_replace(',', ', ', $row['granted_permissions'])) ?></td>
                          <td class="px-6 py-4 text-sm text-gray-700 flex flex-wrap gap-2">
                            <?php if(in_array('View', $permissionList)): ?>
                              <button onclick="openPreview('<?= $row['filepath'] ?>','<?= $row['filename'] ?>')"
                                      class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">View</button>
                            <?php endif; ?>
                            <?php if(in_array('Download', $permissionList)): ?>
                              <a href="<?= $row['filepath'] ?>" download
                                 class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">Download</a>
                            <?php endif; ?>
                            <button onclick="openEditModal(<?= $row['doc_id'] ?>, '<?= htmlspecialchars(addslashes($row['user_role'])) ?>', '<?= htmlspecialchars(addslashes($row['title'])) ?>', '<?= $row['granted_permissions'] ?>')"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">Edit</button>
                            <?php if(in_array('Archive', $permissionList)): ?>
                                <button onclick="openArchiveModal(<?= $row['doc_id'] ?>)"
                                        class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-xs">Archive</button>
                            <?php endif; ?>
                            <button onclick="openDeleteModal(<?= $row['doc_id'] ?>)"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Delete Doc</button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr><td colspan="4" class="text-center py-6 text-gray-500">No active permissions found.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div id="permissionModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[999]">
              <div class="bg-white w-[95%] max-w-lg rounded-2xl shadow-xl p-8 animate-fadeIn">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Add Document Permission</h2>
                <form method="POST" class="space-y-5">
                  <select name="docId" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5">
                    <option value="" disabled selected>Select Document</option>
                    <?php 
                      $documents->data_seek(0);
                      while($doc = $documents->fetch_assoc()): 
                    ?>
                      <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['title']) ?></option>
                    <?php endwhile; ?>
                  </select>

                  <input type="text" name="userRole" placeholder="User or Role (e.g., Admin, Student)" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5" required>

                  <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Additional Permissions (View is granted automatically):</label>
                    <div class="grid grid-cols-2 gap-4 p-4">
                        <div class="permission-box" data-permission="Download" onclick="togglePermission(this)">
                            <i class='bx bx-download text-xl mb-2'></i>
                            <div>Download</div>
                            <input type="checkbox" name="permissionTypes[]" value="Download" class="hidden">
                        </div>
                        <div class="permission-box" data-permission="Archive" onclick="togglePermission(this)">
                            <i class='bx bx-archive text-xl mb-2'></i>
                            <div>Archive</div>
                            <input type="checkbox" name="permissionTypes[]" value="Archive" class="hidden">
                        </div>
                    </div>
                  </div>

                  <div class="flex justify-end gap-4 pt-5">
                    <button type="button" onclick="document.getElementById('permissionModal').classList.add('hidden')"
                      class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg">Cancel</button>
                    <button type="submit" name="addPermission"
                      class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">Add Permissions</button>
                  </div>
                </form>
              </div>
            </div>

            <div id="editPermissionModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[999]">
              <div class="bg-white w-[95%] max-w-lg rounded-2xl shadow-xl p-8 animate-fadeIn">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edit Permissions</h2>
                <form method="POST" class="space-y-5">
                  <input type="hidden" name="editDocId" id="editDocId">
                  <input type="hidden" name="editUserRole" id="editUserRole">

                  <div class="mb-2">
                    <p class="text-sm text-gray-500">Document:</p>
                    <p class="font-semibold text-gray-800" id="editDocTitle"></p>
                  </div>
                  <div class="mb-4">
                    <p class="text-sm text-gray-500">User/Role:</p>
                    <p class="font-semibold text-gray-800" id="editUserRoleDisplay"></p>
                  </div>

                  <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Permissions (View is always granted):</label>
                    <div class="grid grid-cols-2 gap-4 p-4" id="editCheckboxes">
                        <div class="permission-box" data-permission="Download" onclick="togglePermission(this)">
                            <i class='bx bx-download text-xl mb-2'></i>
                            <div>Download</div>
                            <input type="checkbox" name="permissionTypes[]" value="Download" class="hidden">
                        </div>
                        <div class="permission-box" data-permission="Archive" onclick="togglePermission(this)">
                            <i class='bx bx-archive text-xl mb-2'></i>
                            <div>Archive</div>
                            <input type="checkbox" name="permissionTypes[]" value="Archive" class="hidden">
                        </div>
                    </div>
                  </div>

                  <div class="flex justify-end gap-4 pt-5">
                    <button type="button" onclick="document.getElementById('editPermissionModal').classList.add('hidden')"
                      class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg">Cancel</button>
                    <button type="submit" name="updatePermission"
                      class="bg-yellow-600 hover:bg-yellow-700 text-white px-5 py-2 rounded-lg">Save Changes</button>
                  </div>
                </form>
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

            <div id="archiveModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1001]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-archive text-6xl text-purple-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Archive Document</h2>
                  <p class="text-gray-600 mb-6">Are you sure you want to archive this document? This will remove all other permissions and move it to the archive.</p>
                  <div class="flex justify-center gap-4">
                    <button onclick="closeArchiveModal()" 
                      class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">Cancel</button>
                    <button onclick="confirmArchive()" 
                      class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">Archive</button>
                  </div>
                </div>
              </div>
            </div>

            <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1001]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-trash text-6xl text-red-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Delete Document</h2>
                  <p class="text-gray-600 mb-6">Are you sure you want to <strong>PERMANENTLY DELETE</strong> this document? This action cannot be undone.</p>
                  <div class="flex justify-center gap-4">
                    <button onclick="closeDeleteModal()" 
                      class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">Cancel</button>
                    <button onclick="confirmDelete()" 
                      class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">Delete</button>
                  </div>
                </div>
              </div>
            </div>

            <div id="deleteSuccessModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1002]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-check-circle text-6xl text-green-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Document Deleted</h2>
                  <p class="text-gray-600 mb-6">The document has been permanently deleted successfully.</p>
                  <button onclick="closeSuccessModal('deleteSuccessModal')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">OK</button>
                </div>
              </div>
            </div>
            
            <div id="editSuccessModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1002]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-check-circle text-6xl text-blue-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Permissions Updated</h2>
                  <p class="text-gray-600 mb-6">The document permissions have been updated successfully.</p>
                  <button onclick="closeSuccessModal('editSuccessModal')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">OK</button>
                </div>
              </div>
            </div>

            <div id="returnSuccessModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1002]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-check-circle text-6xl text-green-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Document Returned</h2>
                  <p class="text-gray-600 mb-6">The document has been returned to the active list successfully. You can now use the <strong>Edit button</strong> to modify permissions or add new ones.</p>
                  <button onclick="closeSuccessModal('returnSuccessModal')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">OK</button>
                </div>
              </div>
            </div>

            <div id="addSuccessModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1002]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-check-circle text-6xl text-green-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Permission Added</h2>
                  <p class="text-gray-600 mb-6">The document permission has been added successfully.</p>
                  <button onclick="closeSuccessModal('addSuccessModal')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">OK</button>
                </div>
              </div>
            </div>

            <div id="archiveSuccessModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[1002]">
              <div class="bg-white w-[95%] max-w-md rounded-2xl shadow-xl p-6 animate-fadeIn">
                <div class="text-center">
                  <i class='bx bx-check-circle text-6xl text-purple-500 mb-4'></i>
                  <h2 class="text-xl font-semibold text-gray-800 mb-2">Document Archived</h2>
                  <p class="text-gray-600 mb-6">The document has been archived successfully and moved to the archive list. Click OK to view all archived documents.</p>
                  <button onclick="redirectToViewRecords()" 
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
      
      // Construct the correct URL path
      // Remove leading "./" or "../" and build proper URL
      let cleanPath = src.replace(/^\.\.?\//, '');
      const fullURL = window.location.origin + "/TNVS/" + cleanPath;
      
      console.log('Preview URL:', fullURL); // Debug log to check URL

      if(["png","jpg","jpeg","gif","bmp","webp"].includes(ext)){
        viewContent.innerHTML = `
          <div class="flex justify-center items-center">
            <img src="${fullURL}" 
                 class="max-w-full max-h-[70vh] object-contain rounded shadow-lg" 
                 onerror="this.parentElement.innerHTML='<p class=\\"text-red-500\\>Error loading image preview.\\</p>'">
          </div>`;
      } else if(ext === "pdf"){
        viewContent.innerHTML = `
          <iframe src="${fullURL}" 
                  class="w-full h-[70vh] border rounded" 
                  frameborder="0"
                  onerror="console.log('PDF load error')">
            <p class="text-red-500">Unable to display PDF. <a href="${fullURL}" target="_blank" class="text-blue-500 underline">Open in new tab</a></p>
          </iframe>`;
      } else if(["doc","docx","xls","xlsx","ppt","pptx"].includes(ext)){
        // Encode the URL for Google Docs viewer
        const encodedURL = encodeURIComponent(fullURL);
        viewContent.innerHTML = `
          <iframe src="https://docs.google.com/gview?url=${encodedURL}&embedded=true" 
                  class="w-full h-[70vh] border rounded" 
                  frameborder="0">
            <p class="text-red-500">Unable to preview document. <a href="${fullURL}" target="_blank" class="text-blue-500 underline">Download file</a></p>
          </iframe>`;
      } else {
        viewContent.innerHTML = `
          <div class="text-center py-8">
            <i class='bx bx-file text-6xl text-gray-400 mb-4'></i>
            <p class="text-gray-700 mb-4">Preview not supported for .${ext} files</p>
            <a href="${fullURL}" download class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Download File</a>
          </div>`;
      }

      viewModal.classList.remove("hidden");
    }


    // Function to toggle permission boxes
    function togglePermission(box) {
        const checkbox = box.querySelector('input[type="checkbox"]');
        
        if (box.classList.contains('selected')) {
            box.classList.remove('selected');
            checkbox.checked = false;
        } else {
            box.classList.add('selected');
            checkbox.checked = true;
        }
    }

    function openEditModal(docId, userRole, docTitle, permissionsString) {
        document.getElementById('editDocId').value = docId;
        document.getElementById('editUserRole').value = userRole;
        document.getElementById('editDocTitle').textContent = docTitle;
        document.getElementById('editUserRoleDisplay').textContent = userRole;

        const currentPermissions = permissionsString.split(',');
        const permissionBoxes = document.querySelectorAll('#editCheckboxes .permission-box');
        
        permissionBoxes.forEach(box => {
            const permission = box.dataset.permission;
            const checkbox = box.querySelector('input[type="checkbox"]');
            
            if (currentPermissions.includes(permission)) {
                box.classList.add('selected');
                checkbox.checked = true;
            } else {
                box.classList.remove('selected');
                checkbox.checked = false;
            }
        });

        document.getElementById('editPermissionModal').classList.remove('hidden');
    }

    // NEW SCRIPT to handle opening the modal on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const docIdToOpen = urlParams.get('openModalForDoc');
        const isReturned = urlParams.get('returned');
        const isSuccess = urlParams.get('success');
        const isUpdated = urlParams.get('updated');
        const isDocDeleted = urlParams.get('docDeleted');
        const isArchived = urlParams.get('archived');

        // Show success modals based on URL parameters
        if (isSuccess) {
            showSuccessModal('addSuccessModal');
        } else if (isUpdated) {
            showSuccessModal('editSuccessModal');
        } else if (isDocDeleted) {
            showSuccessModal('deleteSuccessModal');
        } else if (isArchived) {
            showSuccessModal('archiveSuccessModal');
        } else if (isReturned) {
            // Show return success modal when document is returned
            showSuccessModal('returnSuccessModal');
        }

        if (docIdToOpen) {
            const permissionModal = document.getElementById('permissionModal');
            const docSelect = permissionModal.querySelector('select[name="docId"]');
            
            if(docSelect){
                // Set the dropdown to the correct document
                docSelect.value = docIdToOpen;
                
                // Show the "Add Permission" modal
                permissionModal.classList.remove('hidden');
                
                // If this is a returned document, show helpful message
                if (isReturned) {
                    const modalTitle = permissionModal.querySelector('h2');
                    if (modalTitle) {
                        modalTitle.innerHTML = '<i class="bx bx-undo text-green-600 mr-2"></i>Set Permissions for Returned Document';
                        modalTitle.className = 'text-2xl font-semibold text-green-800 mb-6';
                    }
                }
            }
        }
    });

    // Archive Modal Functions
    let currentArchiveDocId = null;

    function openArchiveModal(docId) {
        currentArchiveDocId = docId;
        document.getElementById('archiveModal').classList.remove('hidden');
    }

    function closeArchiveModal() {
        currentArchiveDocId = null;
        document.getElementById('archiveModal').classList.add('hidden');
    }

    function confirmArchive() {
        if (currentArchiveDocId) {
            window.location.href = `Document_Access_Permissions.php?archive=${currentArchiveDocId}`;
        }
    }

    // Delete Modal Functions
    let currentDeleteDocId = null;

    function openDeleteModal(docId) {
        currentDeleteDocId = docId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        currentDeleteDocId = null;
        document.getElementById('deleteModal').classList.add('hidden');
    }

    function confirmDelete() {
        if (currentDeleteDocId) {
            window.location.href = `?deleteDocument=${currentDeleteDocId}`;
        }
    }

    // Success Modal Functions
    function closeSuccessModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        // Simple redirect to clear the URL parameters, if we're closing a success modal
        if (['addSuccessModal', 'editSuccessModal', 'deleteSuccessModal', 'returnSuccessModal'].includes(modalId)) {
            window.location.href = 'Document_Access_Permissions.php';
        }
    }

    function showSuccessModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function redirectToViewRecords() {
        window.location.href = 'View_Records.php';
    }
    </script>

    <script src="../JS/script.js"></script>
</body>
</html>