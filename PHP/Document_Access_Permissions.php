<?php
include 'db_connect.php';

// --- Handle Add Permission ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['addPermission'])) {
    $docId = intval($_POST['docId']);
    $userRole = $_POST['userRole'];
    $permissionType = $_POST['permissionType'];

    $stmt = $conn->prepare("INSERT INTO document_permissions (document_id, user_role, permission_type) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $docId, $userRole, $permissionType);
    $stmt->execute();
    $stmt->close();

    header("Location: Document_Access_Permissions.php?success=1");
    exit;
}

// --- Handle Delete Permission ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM document_permissions WHERE id=$id");
    header("Location: Document_Access_Permissions.php?deleted=1");
    exit;
}

// --- Handle Archive (via GET) ---
if (isset($_GET['archive'])) {
    $docId = intval($_GET['archive']);
    $role = "Admin"; // or pull from session/user if available

    $stmt = $conn->prepare("INSERT INTO document_permissions (document_id, user_role, permission_type) VALUES (?, ?, 'Archive')");
    $stmt->bind_param("is", $docId, $role);

    if ($stmt->execute()) {
        header("Location: Document_Access_Permissions.php?archived=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// --- Fetch documents & permissions ---
$documents = $conn->query("SELECT * FROM documents ORDER BY uploaded_at DESC");
$permissions = $conn->query("SELECT dp.id, d.id AS doc_id, d.title, d.filename, d.filepath, dp.user_role, dp.permission_type 
                             FROM document_permissions dp 
                             JOIN documents d ON dp.document_id = d.id 
                             ORDER BY dp.id DESC");

// --- Fetch archived docs ---
$archivedDocs = $conn->query("
    SELECT d.id, d.title, d.filename, d.filepath, d.uploaded_at, dp.user_role 
    FROM document_permissions dp
    JOIN documents d ON dp.document_id = d.id
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
  <title>Document Access Permissions</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <style>
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95);} to { opacity: 1; transform: scale(1);} }
    .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="w-[95%] md:w-[90%] lg:w-[80%] mx-auto py-10">

  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-semibold text-gray-800">Document Access Permissions</h1>
    <button onclick="document.getElementById('permissionModal').classList.remove('hidden')"
      class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl shadow-md text-sm font-medium transition">
      + Add Permission
    </button>
  </div>

  <!-- Messages -->
  <?php if(isset($_GET['success'])): ?>
    <div class="p-3 mb-4 bg-green-100 text-green-700 rounded">✅ Permission added successfully</div>
  <?php elseif(isset($_GET['deleted'])): ?>
    <div class="p-3 mb-4 bg-yellow-100 text-yellow-700 rounded">🗑️ Permission deleted</div>
  <?php elseif(isset($_GET['archived'])): ?>
    <div class="p-3 mb-4 bg-blue-100 text-blue-700 rounded">📦 Document archived successfully</div>
  <?php endif; ?>

  <!-- Permissions Table -->
  <div class="overflow-x-auto bg-white rounded-xl shadow-md">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Document Title</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">User/Role</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Permission Type</th>
          <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php if ($permissions->num_rows > 0): ?>
          <?php while($row = $permissions->fetch_assoc()): ?>
            <tr>
              <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['title']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['user_role']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['permission_type']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-700 flex gap-2">
                <?php if(in_array($row['permission_type'], ['View','Download','Edit'])): ?>
                  <button onclick="openPreview('<?= $row['filepath'] ?>','<?= $row['filename'] ?>')"
                          class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">View</button>
                <?php endif; ?>
                <?php if(in_array($row['permission_type'], ['Download','Edit'])): ?>
                  <a href="<?= $row['filepath'] ?>" download
                     class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">Download</a>
                <?php endif; ?>
                <?php if($row['permission_type'] === 'Edit'): ?>
                  <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">Edit</button>
                <?php endif; ?>
               <a href="Document_Access_Permissions.php?archive=<?= $row['doc_id'] ?>" 
   onclick="return confirm('Archive this document?')"
   class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-xs">Archive</a>


                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this permission?')"
                  class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Remove</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4" class="text-center py-6 text-gray-500">No permissions assigned yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Permission Modal -->
<div id="permissionModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[999]">
  <div class="bg-white w-[95%] max-w-lg rounded-2xl shadow-xl p-8 animate-fadeIn">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Add Document Permission</h2>
    <form method="POST" class="space-y-5">
      <select name="docId" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5">
        <option value="" disabled selected>Select Document</option>
        <?php while($doc = $documents->fetch_assoc()): ?>
          <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['title']) ?></option>
        <?php endwhile; ?>
      </select>

      <input type="text" name="userRole" placeholder="User or Role (e.g., Admin, Student)" 
        class="w-full border border-gray-300 rounded-lg px-4 py-2.5" required>

      <select name="permissionType" class="w-full border border-gray-300 rounded-lg px-4 py-2.5" required>
        <option value="" disabled selected>Select Permission Type</option>
        <option value="View">View</option>
        <option value="Download">Download</option>
        <option value="Edit">Edit</option>
        <option value="Archive">Archive</option>
      </select>

      <div class="flex justify-end gap-4 pt-5">
        <button type="button" onclick="document.getElementById('permissionModal').classList.add('hidden')"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg">Cancel</button>
        <button type="submit" name="addPermission"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">Add Permission</button>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
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

<script>
function openPreview(src, name) {
  const ext = name.split('.').pop().toLowerCase();
  const viewModal = document.getElementById("viewModal");
  const viewContent = document.getElementById("viewContent");
  const viewTitle = document.getElementById("viewTitle");

  viewTitle.textContent = name;
  const fullURL = window.location.origin + "/" + src;

  if(["png","jpg","jpeg"].includes(ext)){
    viewContent.innerHTML = `<img src="${fullURL}" class="w-full h-auto object-contain">`;
  } else if(ext === "pdf"){
    viewContent.innerHTML = `<iframe src="${fullURL}" class="w-full h-[70vh]" frameborder="0"></iframe>`;
  } else if(["doc","docx","xls","xlsx","ppt","pptx"].includes(ext)){
    viewContent.innerHTML = `<iframe src="https://docs.google.com/gview?url=${fullURL}&embedded=true" class="w-full h-[70vh]" frameborder="0"></iframe>`;
  } else {
    viewContent.innerHTML = `<p class="text-gray-700">Preview not supported for this file type. Please download it.</p>`;
  }

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