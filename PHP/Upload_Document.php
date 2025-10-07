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

// --- 1. HANDLE UPLOAD ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["docFile"])) {
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
        // Ensure you have an 'uploaded_at' column with a default of CURRENT_TIMESTAMP in your DB
        $stmt = $conn->prepare("INSERT INTO documents (title, filename, filepath, uploaded_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $filename, $webPath, $uploaded_by);
        $stmt->execute();
        $stmt->close();

        header("Location: Upload_Document.php?success=1");
        exit;
    } else {
        header("Location: Upload_Document.php?error=upload");
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
        
        <main>
            <div class="head-title">
            <div class="page-container w-[95%] md:w-[90%] lg:w-[80%]">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-semibold text-gray-800">📂 Upload Documents</h1>
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl shadow-md text-sm font-medium transition">
                        + Upload Document
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">✅ Document uploaded successfully!</div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">❌ Error uploading file. Try again.</div>
                <?php endif; ?>

                <div class="overflow-x-auto bg-white rounded-xl shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Document Title</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Uploaded By</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if ($result->num_rows > 0) { ?>
                                <?php while($row = $result->fetch_assoc()) { ?>
                                    <tr class="document-row" onclick="openPreview('<?= htmlspecialchars($row['filepath']) ?>', '<?= htmlspecialchars($row['filename']) ?>')">
                                        <td class="px-6 py-4 text-sm text-gray-700 font-medium hover:text-blue-600 transition">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['uploaded_by']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <?php if (!empty($row['uploaded_at'])): ?>
                                                <?= date('M j, Y h:i A', strtotime($row['uploaded_at'])) ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <i class='bx bx-search-alt text-xl text-blue-500 hover:text-blue-700'></i>
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

            <div id="uploadModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                <div class="bg-white w-[95%] max-w-lg rounded-2xl shadow-xl p-8 relative">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Upload Document</h2>
                    <form method="POST" enctype="multipart/form-data" class="space-y-5">
                        <input type="text" name="docTitle" placeholder="Document Title" class="w-full border border-gray-300 rounded-lg px-4 py-2.5" required>
                        <input type="file" name="docFile" class="w-full" accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required>
                        <div class="flex justify-end gap-4 pt-5">
                            <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm transition">Cancel</button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm transition">Upload</button>
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
    <script src="../JS/script.js"></script>
</body>
</html>