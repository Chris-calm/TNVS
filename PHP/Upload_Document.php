<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// --- 1. HANDLE UPLOAD ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["docFile"])) {
    // Check upload permission
    RBACMiddleware::requirePermission('upload_documents');
    $title = $_POST["docTitle"];
    
    // Use session username if available, otherwise default to "Admin"
    $uploaded_by = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin";
    
    $file = $_FILES["docFile"];

    // Directories
    $uploadDir = __DIR__ . "/../uploads/";  // server path
    $webDir = "../uploads/";                   // web path for browser (corrected to reflect path from current PHP directory)

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Unique filename
    $filename = time() . "_" . basename($file["name"]);
    $targetPath = $uploadDir . $filename;
    $webPath = $webDir . $filename;

    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO documents (title, filename, filepath, uploaded_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $filename, $webPath, $uploaded_by);
        $stmt->execute();
        $stmt->close();

        $_SESSION['upload_success'] = "Document '$title' has been uploaded successfully.";
        header("Location: Upload_Document.php");
        exit;
    } else {
        $_SESSION['upload_error'] = "Error uploading file. Please try again.";
        header("Location: Upload_Document.php");
        exit;
    }
}

// --- 2. FETCH RECORDS ---
$result = $conn->query("SELECT * FROM documents ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Upload Document | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Custom styles to prevent the nested body's gray background from interfering */
        #content main {
            background-color: transparent; 
        }
        /* Ensure the main container inside main covers the area correctly */
        .page-container {
            width: 100%;
            margin: auto;
            padding: 2.5rem 0; /* Tailwind py-10 equivalent */
        }
        /* Style for table rows to allow the preview to be clickable */
        .document-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .document-row:hover {
            background-color: #f7f7f7;
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
                        <h1 class="text-2xl font-light text-gray-900">Documents</h1>
                        <p class="text-sm text-gray-500 mt-1">Upload and manage your documents</p>
                    </div>
                    <?php if (RBACMiddleware::hasPermission('upload_documents')): ?>
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                        class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Upload Document
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Minimalist Table -->
            <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($result->num_rows > 0) { ?>
                                <?php while($row = $result->fetch_assoc()) { ?>
                                    <tr class="hover:bg-gray-50 cursor-pointer transition-colors" onclick="openPreview('<?= htmlspecialchars($row['filepath']) ?>', '<?= htmlspecialchars($row['filename']) ?>')">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['uploaded_by']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if (!empty($row['uploaded_at'])): ?>
                                                <?= date('M j, Y', strtotime($row['uploaded_at'])) ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            <i class='bx bx-show text-lg hover:text-blue-600 transition-colors'></i>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="4" class="text-center py-6 text-gray-500">No documents uploaded yet.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Minimalist Upload Modal -->
            <div id="uploadModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white w-[95%] max-w-md rounded-xl shadow-2xl p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-medium text-gray-900">Upload Document</h2>
                        <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="text" name="docTitle" placeholder="Document title" 
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" required>
                        <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center hover:border-gray-300 transition-colors">
                            <input type="file" name="docFile" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100" 
                                   accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required>
                            <p class="text-xs text-gray-400 mt-2">PDF, DOC, XLS, PPT, Images</p>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</button>
                            <button type="submit" class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2 rounded-lg text-sm font-medium transition-colors">Upload</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="viewModal" class="hidden fixed inset-0 bg-black/75 flex items-center justify-center z-[60]">
                <div class="bg-white w-[95%] max-w-5xl h-[90vh] rounded-2xl shadow-xl p-6 relative flex flex-col">
                    <div class="flex justify-between items-center mb-4 border-b pb-3">
                        <h2 id="viewTitle" class="text-xl font-semibold text-gray-800 truncate">Document Preview</h2>
                        <div class="flex gap-3">
                            <a id="downloadBtn" href="#" download class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition hidden">
                                <i class='bx bxs-download'></i> Download
                            </a>
                            <button type="button" onclick="document.getElementById('viewModal').classList.add('hidden')"
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                                <i class='bx bx-x'></i> Close
                            </button>
                        </div>
                    </div>
                    <div id="viewContent" class="flex-grow overflow-auto">
                        </div>
                </div>
            </div>

            </main>
    </section>

    <script>
        function openPreview(filepath, filename) {
          const ext = filename.split('.').pop().toLowerCase();
          const viewModal = document.getElementById("viewModal");
          const viewContent = document.getElementById("viewContent");
          const viewTitle = document.getElementById("viewTitle");
          const downloadBtn = document.getElementById("downloadBtn");
          
          viewTitle.textContent = filename;
          viewContent.innerHTML = ''; // Clear previous content

          // The filepath is already relative (e.g., '../uploads/...')
          const fullURL = filepath.startsWith('http') ? filepath : window.location.origin + filepath.substring(2); // Adjust for the '../' prefix

          if (["png", "jpg", "jpeg", "gif"].includes(ext)) {
            viewContent.innerHTML = `<img src="${filepath}" alt="Preview" class="w-full h-auto object-contain max-h-[75vh] mx-auto">`;
          } else if (ext === "pdf") {
            viewContent.innerHTML = `<iframe src="${filepath}" class="w-full h-[75vh]" frameborder="0"></iframe>`;
          } else if (["doc", "docx", "xls", "xlsx", "ppt", "pptx"].includes(ext)) {
            // Note: Google Viewer needs a publicly accessible URL, which 'filepath' might not be.
            // Using 'filepath' directly here might only work if it's served publicly.
            viewContent.innerHTML = `<iframe src="https://docs.google.com/gview?url=${encodeURIComponent(window.location.origin + filepath.substring(2))}&embedded=true" class="w-full h-[75vh]" frameborder="0"></iframe>`;
          } else {
            viewContent.innerHTML = `<p class="text-gray-700 p-8 text-center">Preview not supported for this file type (${ext}). Please use the download button.</p>`;
          }

          downloadBtn.href = filepath;
          downloadBtn.download = filename;
          downloadBtn.classList.remove("hidden");

          viewModal.classList.remove("hidden");
        }
    </script>

    <!-- Include Success Modal -->
    <?php include 'partials/success_modal.php'; ?>

    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['upload_success'])): ?>
            showSuccessModal('Upload Complete!', '<?= addslashes($_SESSION['upload_success']) ?>');
            <?php unset($_SESSION['upload_success']); ?>
        <?php elseif (isset($_SESSION['upload_error'])): ?>
            showSuccessModal('Upload Failed', '<?= addslashes($_SESSION['upload_error']) ?>');
            <?php unset($_SESSION['upload_error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>