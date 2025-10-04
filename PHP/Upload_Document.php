<?php
include 'db_connect.php';

// --- 1. HANDLE UPLOAD ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["docFile"])) {
    $title = $_POST["docTitle"];
    $uploaded_by = "Admin"; // Replace with $_SESSION['username'] if you have login
    $file = $_FILES["docFile"];

    // Directories
    $uploadDir = __DIR__ . "/../uploads/";  // server path
    $webDir = "uploads/";                   // web path for browser

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
    <title>TNVS Dashboard</title>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <section id="sidebar">
        <a href="" class="brand">
    <img src="../PICTURES/Black and White Circular Art & Design Logo.png" alt="Trail Ad Corporation Logo" class="brand-logo">
    <span class="text">TNVS</span>
</a>

        <ul class="side-menu top">
            <li class="active">
                <a href="../PHP/Dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="dropdown">
    <a href="#" class="dropdown-toggle">
       <i class='bx bxs-store-alt'></i>
       <span class="text">Facilities Reservation</span>
       <i class='bx bx-chevron-down arrow'></i>
    </a>
    <ul class="dropdown-menu">
        <li><a href="../PHP/Reserve_Room.php"><span class="text">Reserve Room</span></a></li>
        <li><a href="../PHP/Approval_Rejection_Requests.php"><span class="text">Approval/Rejection Request</span></a></li>
        <li><a href="../PHP/Reservation_Calendar.php"><span class="text">Reservation Calendar</span></a></li>
        <li><a href="../PHP/Facilities_Maintenance.php"><span class="text">Facilities Maintenance</span></a></li>
    </ul>
</li>

<li class="dropdown">
    <a href="#" class="dropdown-toggle">
        <i class='bx bxs-archive'></i>
        <span class="text">Documents Management</span>
        <i class='bx bx-chevron-down arrow'></i>
    </a>
    <ul class="dropdown-menu">
        <li><a href="../PHP/Upload_Document.php"><span class="text">Upload Document</span></a></li>
        <li><a href="../PHP/Document_Access_Permissions.php"><span class="text">Document Access Permission</span></a></li>
        <li><a href="../PHP/View_Records.php"><span class="text">View Records</span></a></li>
    </ul>
</li>

<li class="dropdown">
    <a href="#" class="dropdown-toggle">
        <i class='bx bxs-landmark'></i>
        <span class="text">Legal Management</span>
        <i class='bx bx-chevron-down arrow'></i>
    </a>
    <ul class="dropdown-menu">
        <li><a href="../PHP/Contracts.php"><span class="text">Contracts</span></a></li>
        <li><a href="../PHP/Policies.php"><span class="text">Policies</span></a></li>
        <li><a href="../PHP/Case_Records.php"><span class="text">Case Records</span></a></li>
    </ul>
</li>

<li class="dropdown">
    <a href="#" class="dropdown-toggle">
        <i class='bx bxs-universal-access'></i>
        <span class="text">Visitor Management</span>
        <i class='bx bx-chevron-down arrow'></i>
    </a>
    <ul class="dropdown-menu">
        <li><a href="../PHP/Visitor_Pre_Registration.php"><span class="text">Visitor Pre-Registration</span></a></li>
        <li><a href="../PHP/Visitor_Logs.php"><span class="text">Visitor Logs</span></a></li>
        <li><a href="../PHP/Pass_Requests.php"><span class="text">Pass Requests</span></a></li>
        <li><a href="../PHP/Blacklist_Watchlist.php"><span class="text">Blacklist/Watchlist</span></a></li>
    </ul>
</li>

<li class="dropdown">
    <a href="#" class="dropdown-toggle">
        <i class='bx bxs-circle-three-quarter'></i>
        <span class="text">Statistics</span>
        <i class='bx bx-chevron-down arrow'></i>
    </a>
    <ul class="dropdown-menu">
        <li><a href="../PHP/Yearly_Reports.php"><span class="text">Yearly Reports</span></a></li>
        <li><a href="../PHP/Monthly_Reports.php"><span class="text">Monthly Reports</span></a></li>
        <li><a href="../PHP/Weekly_Reports.php"><span class="text">Weekly Reports</span></a></li>
        <li><a href="../PHP/Daily_Reports.php"><span class="text">Daily Reports</span></a></li>
    </ul>
</li>

        </ul>
        <ul class="side-menu">
              <li>
                <a href="#">
                    <i class='bx bxs-cog' ></i>
                    <span class="text">Settings</span>
                </a>
            </li>
             <li>
                <a href="../PHP/index.php" class="logout">
                    <i class='bx bxs-log-out-circle' ></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <section id="content">
        <nav>
            <i class='bx bx-menu' ></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
                </div>
            </form>
            <a href="#" class="notification">
                <i class='bx bxs-bell' ></i>
                <span class="num">8</span>
            </a>
            <a href="#" class="profile">
                <img src="../PICTURES/Ser.jpg">
            </a>
        </nav>

        <main>
         <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TNVS - Upload Documents</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="w-[95%] md:w-[90%] lg:w-[80%] mx-auto py-10">
  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-semibold text-gray-800">📂 Document Management</h1>
    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
      class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl shadow-md text-sm font-medium transition">
      + Upload Document
    </button>
  </div>

  <!-- Success / Error Messages -->
  <?php if (isset($_GET['success'])): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">✅ Document uploaded successfully!</div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">❌ Error uploading file. Try again.</div>
  <?php endif; ?>

  <!-- Table -->
  <div class="overflow-x-auto bg-white rounded-xl shadow-md">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Document Title</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Uploaded By</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Date</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php if ($result->num_rows > 0) { ?>
          <?php while($row = $result->fetch_assoc()) { ?>
            <tr>
              <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['title']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['uploaded_by']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?= $row['uploaded_at'] ?></td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr><td colspan="3" class="text-center py-6 text-gray-500">No documents uploaded yet.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Upload Modal -->
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

</body>
</html>

<script>
function openPreview(src, name) {
  const ext = name.split('.').pop().toLowerCase();
  const viewModal = document.getElementById("viewModal");
  const viewContent = document.getElementById("viewContent");
  const viewTitle = document.getElementById("viewTitle");
  const downloadBtn = document.getElementById("downloadBtn");

  viewTitle.textContent = name;

  // make src absolute
  const fullURL = window.location.origin + "/" + src;

  if(["png","jpg","jpeg"].includes(ext)){
    viewContent.innerHTML = `<img src="${fullURL}" alt="Preview" class="w-full h-auto object-contain">`;
  } else if(ext==="pdf"){
    viewContent.innerHTML = `<iframe src="${fullURL}" class="w-full h-[70vh]" frameborder="0"></iframe>`;
  } else if(["doc","docx","xls","xlsx","ppt","pptx"].includes(ext)){
    viewContent.innerHTML = `<iframe src="https://docs.google.com/gview?url=${fullURL}&embedded=true" class="w-full h-[70vh]" frameborder="0"></iframe>`;
  } else {
    viewContent.innerHTML = `<p class="text-gray-700">Preview not supported for this file type. Please download it.</p>`;
  }

  downloadBtn.href = fullURL;
  downloadBtn.download = name;
  downloadBtn.classList.remove("hidden");

  viewModal.classList.remove("hidden");
}
</script>

</body>
</html>
        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>