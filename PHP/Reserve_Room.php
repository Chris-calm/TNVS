<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// --- 1. HANDLE ADD/EDIT FACILITY (POST Request for saveFacility) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['saveFacility'])) {
    // NOTE: Changed 'id' to 'facility_id' in logic to match DB schema.
    $id = $_POST['facilityId'] ?? null; // facilityId comes from the hidden input
    $name = $_POST['facilityName'];
    $capacity = $_POST['facilityCapacity'];
    $location = $_POST['facilityLocation'];
    $status = $_POST['facilityStatus'];
    $date = $_POST['facilityDate'];
    $time = $_POST['facilityTime'];
    $picture = null;
    $upload_error = null;

    // Handle file upload with proper validation
    if (isset($_FILES['facilityPicture']) && $_FILES['facilityPicture']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['facilityPicture'];
        $originalName = $uploadedFile['name'];
        $tmpName = $uploadedFile['tmp_name'];
        $fileSize = $uploadedFile['size'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($tmpName);
        
        if (!in_array($fileType, $allowedTypes)) {
            $upload_error = "Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.";
        } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            $upload_error = "File size too large. Maximum 5MB allowed.";
        } else {
            // Generate unique filename to prevent conflicts
            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
            $picture = time() . '_' . uniqid() . '.' . $fileExtension;
            
            // Use absolute path for upload directory
            $targetDir = dirname(__DIR__) . "/uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            $targetPath = $targetDir . $picture;
            
            if (!move_uploaded_file($tmpName, $targetPath)) {
                $upload_error = "Failed to upload file. Please try again.";
                $picture = null;
            }
        }
    } elseif (isset($_FILES['facilityPicture']) && $_FILES['facilityPicture']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        switch ($_FILES['facilityPicture']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $upload_error = "File size exceeds maximum allowed size.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $upload_error = "File upload was interrupted. Please try again.";
                break;
            default:
                $upload_error = "File upload failed. Please try again.";
        }
    }

    // Only proceed if there's no upload error
    if ($upload_error) {
        $_SESSION['facility_error'] = $upload_error;
    } else {
        if ($id) {
            // **FIXED:** Edit facility - Using WHERE facility_id=?
            if ($picture) {
                $stmt = $conn->prepare("UPDATE facilities SET name=?, capacity=?, location=?, available_date=?, available_time=?, picture=?, status=? WHERE facility_id=?");
                $stmt->bind_param("sisssssi", $name, $capacity, $location, $date, $time, $picture, $status, $id);
            } else {
                $stmt = $conn->prepare("UPDATE facilities SET name=?, capacity=?, location=?, available_date=?, available_time=?, status=? WHERE facility_id=?");
                $stmt->bind_param("sissssi", $name, $capacity, $location, $date, $time, $status, $id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['facility_success'] = "Facility '$name' has been updated successfully.";
            } else {
                $_SESSION['facility_error'] = "Failed to update facility. Please try again.";
            }
        } else {
            // Add facility - Always set status to 'Pending' for approval workflow
            $stmt = $conn->prepare("INSERT INTO facilities (name, capacity, location, available_date, available_time, picture, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("sissss", $name, $capacity, $location, $date, $time, $picture);
            
            if ($stmt->execute()) {
                $_SESSION['facility_success'] = "Facility '$name' has been submitted and is pending approval.";
            } else {
                $_SESSION['facility_error'] = "Failed to add facility. Please try again.";
            }
        }
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- 2. HANDLE DELETE FACILITY (POST Request for delete action) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check for the delete action specific to this file's list
    if (isset($_POST['action']) && $_POST['action'] === "delete" && isset($_POST['facility_id_to_delete'])) {
        $facility_id = intval($_POST['facility_id_to_delete']);

        // **FIXED:** Delete facility using facility_id
        $stmt = $conn->prepare("DELETE FROM facilities WHERE facility_id = ?");
        $stmt->bind_param("i", $facility_id);
        $stmt->execute();
        
        if (isset($stmt)) {
            $stmt->close();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Removed duplicate form handler that was causing conflicts
}

// --- 3. FETCH FACILITIES ---
// Fetch all facilities from DB for display
$sql = "SELECT * FROM facilities ORDER BY created_at DESC";
$result = $conn->query($sql);

// Debug: Check if query executed successfully and count results
if (!$result) {
    die("Query failed: " . $conn->error);
}
$facility_count = $result->num_rows;
// Debug: Uncomment the line below for debugging
echo "<!-- Debug: Found $facility_count facilities -->";

// Additional debug: Show actual data
if ($facility_count > 0) {
    echo "<!-- Debug: Facilities exist in result set -->";
} else {
    echo "<!-- Debug: No facilities in result set -->";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/crud.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Facility Reservation | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    
    <style> 
        body { font-family: 'Inter', sans-serif; } 
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
    </style>
</head>
<body class="flex h-screen overflow-hidden" style="background-color: #eeeeee;">
    <?php include 'partials/sidebar.php'; ?>
    <section id="content">
        <?php include 'partials/header.php'; ?>
        <main class="min-h-full" style="background-color: #eeeeee;">

  <div class="w-full px-6 py-6 min-h-screen" style="background-color: #eeeeee;">
    <!-- Minimalist Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-light text-gray-900">Facilities</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your facility inventory</p>
      </div>
      <button id="openModal" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
        Add Facility
      </button>
    </div>



    <!-- Facility Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
      <!-- DEBUG: Facility count = <?= $facility_count ?> -->
      
      <?php 
      // Fresh query for display to avoid any pointer issues
      $display_sql = "SELECT * FROM facilities ORDER BY created_at DESC";
      $display_result = $conn->query($display_sql);
      ?>
      
      <?php if ($display_result && $display_result->num_rows > 0): ?>
        <!-- DEBUG: Entering facility loop with fresh query -->
        <?php $count = 0; while($row = $display_result->fetch_assoc()): $count++; ?>
          <!-- DEBUG: Processing facility #<?= $count ?> - ID <?= $row['facility_id'] ?? 'UNKNOWN' ?> - Name: <?= $row['name'] ?? 'NO_NAME' ?> -->
          <div class="bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition-colors group">
            <div class="aspect-video overflow-hidden rounded-t-lg">
              <?php 
                $picture = $row['picture'] ?? '';
                // Use absolute path for file existence check
                $absoluteImagePath = dirname(__DIR__) . "/uploads/" . $picture;
                // Use relative path for web display
                $webImagePath = "../uploads/" . htmlspecialchars($picture);
                $imageExists = !empty($picture) && file_exists($absoluteImagePath);
              ?>
              <img src="<?= $imageExists ? $webImagePath : 'https://via.placeholder.com/400x200?text=No+Image' ?>" 
                   alt="Facility" 
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                   onerror="this.src='https://via.placeholder.com/400x200?text=Image+Not+Found'"
                   loading="lazy">
            </div>
            <div class="p-4">
              <div class="flex items-start justify-between mb-2">
                <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($row['name'] ?? 'Unnamed Facility'); ?></h3>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                  <?php 
                    $status = $row['status'] ?? 'Unknown';
                    switch($status) {
                      case 'Approved': echo 'bg-green-50 text-green-700'; break;
                      case 'Rejected': echo 'bg-red-50 text-red-700'; break;
                      case 'Pending': echo 'bg-yellow-50 text-yellow-700'; break;
                      default: echo 'bg-gray-50 text-gray-700';
                    }
                  ?>">
                  <?php echo htmlspecialchars($status); ?>
                </span>
              </div>
              <div class="space-y-1 text-sm text-gray-500 mb-4">
                <p><?php echo ($row['capacity'] ?? 0); ?> people • <?php echo htmlspecialchars($row['location'] ?? 'No location'); ?></p>
                <p><?php echo ($row['available_date'] ?? 'No date'); ?> at <?php echo ($row['available_time'] ?? 'No time'); ?></p>
              </div>
              <div class="flex gap-2">
                <button
                    class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-xs font-medium transition-colors editFacility"
                    data-id="<?= $row['facility_id'] ?? 0 ?>" 
                    data-name="<?= htmlspecialchars($row['name'] ?? '') ?>"
                    data-capacity="<?= $row['capacity'] ?? 0 ?>"
                    data-location="<?= htmlspecialchars($row['location'] ?? '') ?>"
                    data-status="<?= htmlspecialchars($row['status'] ?? '') ?>"
                    data-date="<?= $row['available_date'] ?? '' ?>"
                    data-time="<?= $row['available_time'] ?? '' ?>"
                    data-picture="<?= !empty($row['picture']) ? '../uploads/' . htmlspecialchars($row['picture']) : '' ?>">
                    Edit
                </button>

                <form method="POST" onsubmit="return confirm('Delete this facility?');" class="flex-1">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="facility_id_to_delete" value="<?= $row['facility_id'] ?? 0 ?>">
                    <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-md text-xs font-medium transition-colors">
                        Delete
                    </button>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
        <!-- DEBUG: Finished facility loop -->
      <?php else: ?>
        <!-- DEBUG: No facilities found - showing else condition -->
        <div class="col-span-full text-center py-6">
          <p class="text-gray-500 text-lg">No facilities added yet.</p>
          <p class="text-sm text-gray-400 mt-2">Click "Add Facility" to get started.</p>
        </div>
      <?php endif; ?>
      
    </div>
  </div>

  <!-- Minimalist Modal -->
  <div id="facilityModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-[999] flex items-center justify-center">
    <div class="bg-white w-[95%] max-w-lg rounded-xl shadow-2xl p-6 relative animate-fadeIn">
      <h2 class="text-xl font-medium text-gray-900 mb-6">Add Facility</h2>
      <form id="facilityForm" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="facilityId" id="facilityId">
        
        <!-- Image Upload -->
        <div class="text-center">
          <div id="previewContainer" class="w-80 h-52 mx-auto mb-4 rounded-lg border-2 border-dashed border-gray-200 overflow-hidden hidden shadow-sm">
            <img id="previewImage" src="" alt="Preview" class="w-full h-full object-cover">
          </div>
          <div class="relative">
            <input type="file" name="facilityPicture" id="facilityPicture" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            <div class="bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg px-4 py-2 text-sm text-gray-600 transition-colors cursor-pointer flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              <span id="fileButtonText">Choose File</span>
            </div>
          </div>
        </div>

        <!-- Form Fields -->
        <div class="space-y-4">
          <div>
            <input type="text" name="facilityName" required placeholder="Facility name"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors">
          </div>

          <div class="grid grid-cols-2 gap-3">
            <input type="number" name="facilityCapacity" required placeholder="Capacity"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors">
            <input type="text" name="facilityLocation" required placeholder="Location"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors">
          </div>

          <div class="grid grid-cols-2 gap-3">
            <input type="date" name="facilityDate" required
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors">
            <input type="time" name="facilityTime" required
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors">
          </div>
        </div>

        <!-- Status is automatically set to 'Pending' for new facilities -->
        <input type="hidden" name="facilityStatus" value="Pending">

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4">
          <button type="button" id="closeModal" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">
            Cancel
          </button>
          <button type="submit" name="saveFacility" id="saveFacilityBtn" class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2 rounded-lg text-sm font-medium transition-colors">
            Save
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Include Success Modal -->
  <?php include 'partials/success_modal.php'; ?>
  
  <!-- Include Error Modal -->
  <?php include 'partials/error_modal.php'; ?>
  
  <script>
    
    // --- Facility Modal (Add/Edit) Logic ---
    const modal = document.getElementById("facilityModal");
    const openModalBtn = document.getElementById("openModal");
    const closeModalBtn = document.getElementById("closeModal");
    const pictureInput = document.getElementById("facilityPicture");

    const editButtons = document.querySelectorAll(".editFacility");
    const facilityForm = document.getElementById("facilityForm");
    const facilityIdInput = document.getElementById("facilityId"); 
    const facilityNameInput = document.querySelector("input[name='facilityName']");
    const facilityCapacityInput = document.querySelector("input[name='facilityCapacity']");
    const facilityLocationInput = document.querySelector("input[name='facilityLocation']");
    const facilityStatusInput = document.querySelector("input[name='facilityStatus']");
    const facilityDateInput = document.querySelector("input[name='facilityDate']");
    const facilityTimeInput = document.querySelector("input[name='facilityTime']");
    const previewImage = document.getElementById("previewImage");
    const saveButton = document.getElementById("saveFacilityBtn");


    // Handle Edit button click
    editButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            // Set form appearance for EDIT
            document.querySelector("#facilityModal h2").textContent = "Edit Facility";
            saveButton.textContent = "Update";
            
            // Populate form fields with data attributes
            facilityIdInput.value = btn.dataset.id; // Correctly sets the ID for the PHP update
            facilityNameInput.value = btn.dataset.name;
            facilityCapacityInput.value = btn.dataset.capacity;
            facilityLocationInput.value = btn.dataset.location;
            facilityStatusInput.value = btn.dataset.status;
            facilityDateInput.value = btn.dataset.date;
            facilityTimeInput.value = btn.dataset.time;
            
            // Set the image preview, show only if there's an actual image
            const picturePath = btn.dataset.picture;
            if (picturePath && picturePath.endsWith('null') === false && picturePath !== '../uploads/') {
                previewImage.src = picturePath;
                document.getElementById("previewContainer").classList.remove("hidden");
                document.getElementById("fileButtonText").textContent = "Change File";
            } else {
                document.getElementById("previewContainer").classList.add("hidden");
                document.getElementById("fileButtonText").textContent = "Choose File";
            }

            // Open the modal
            modal.classList.remove("hidden");
        });
    });

    // Handle Add Facility button click (resets form)
    openModalBtn.addEventListener("click", () => {
        document.querySelector("#facilityModal h2").textContent = "Add a Facility";
        saveButton.textContent = "Save";
        facilityForm.reset();
        facilityIdInput.value = ''; // Clear the ID for a new record
        document.getElementById("previewContainer").classList.add("hidden");
        document.getElementById("fileButtonText").textContent = "Choose File";
        modal.classList.remove("hidden");
    });

    // Modal closing logic
    closeModalBtn.addEventListener("click", () => modal.classList.add("hidden"));
    modal.addEventListener("click", (e) => { if (e.target === modal) modal.classList.add("hidden"); });

    // Image preview for file input
    pictureInput.addEventListener("change", () => {
      if (pictureInput.files && pictureInput.files[0]) {
        const file = pictureInput.files[0];
        previewImage.src = URL.createObjectURL(file);
        document.getElementById("previewContainer").classList.remove("hidden");
        document.getElementById("fileButtonText").textContent = file.name;
      }
    });

    // Show success modal if there's a success message
    <?php if (isset($_SESSION['facility_success'])): ?>
      showSuccessModal('Success!', '<?= addslashes($_SESSION['facility_success']) ?>');
      <?php unset($_SESSION['facility_success']); ?>
    <?php endif; ?>
    
    // Show error modal if there's an error message
    <?php if (isset($_SESSION['facility_error'])): ?>
      showErrorModal('Upload Error', '<?= addslashes($_SESSION['facility_error']) ?>');
      <?php unset($_SESSION['facility_error']); ?>
    <?php endif; ?>

  </script>

  </div>

        </main>
    </section>

    <script src="../JS/script.js"></script>
    <script src="../JS/modal.js"></script>
</body>
</html>
