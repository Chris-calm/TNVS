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
    $picture = $_FILES['facilityPicture']['name'] ?? null;

    if ($picture) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        move_uploaded_file($_FILES['facilityPicture']['tmp_name'], $targetDir . $picture);
    }

    if ($id) {
        // **FIXED:** Edit facility - Using WHERE facility_id=?
        if ($picture) {
            $stmt = $conn->prepare("UPDATE facilities SET name=?, capacity=?, location=?, available_date=?, available_time=?, picture=?, status=? WHERE facility_id=?");
            $stmt->bind_param("sisssssi", $name, $capacity, $location, $date, $time, $picture, $status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE facilities SET name=?, capacity=?, location=?, available_date=?, available_time=?, status=? WHERE facility_id=?");
            $stmt->bind_param("sissssi", $name, $capacity, $location, $date, $time, $status, $id);
        }
        $stmt->execute();
    } else {
        // Add facility - Always set status to 'Pending' for approval workflow
        $stmt = $conn->prepare("INSERT INTO facilities (name, capacity, location, available_date, available_time, picture, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("sissss", $name, $capacity, $location, $date, $time, $picture);
        $stmt->execute();
        
        // Set success message for new facility
        $_SESSION['facility_success'] = "Facility '$name' has been submitted and is pending approval.";
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
    
    // Original redundant/misplaced APPROVE/REJECT logic has been removed from this file.

    // This block handles a submission from a separate form (likely user reservation), 
    // which is being left alone since it uses different POST variables ('name', 'capacity', etc.)
    if (isset($_POST['name'])) { 
        $name = $_POST['name'];
        $capacity = $_POST['capacity'];
        $location = $_POST['location'];
        $date = $_POST['available_date'];
        $time = $_POST['available_time'];
        $picture = $_FILES['picture']['name'] ?? null;

        if ($picture) {
            $targetDir = "../uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            move_uploaded_file($_FILES['picture']['tmp_name'], $targetDir . $picture);
        }

        // Insert with status = Pending
        $stmt = $conn->prepare("INSERT INTO facilities (name, capacity, location, available_date, available_time, picture, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("sissss", $name, $capacity, $location, $date, $time, $picture);
        $stmt->execute();
        $stmt->close();

        header("Location: Approval_Rejection_Requests.php"); 
        exit();
    }
}


// --- 3. FETCH FACILITIES ---
// Fetch all facilities from DB for display
$sql = "SELECT * FROM facilities ORDER BY created_at DESC";
$result = $conn->query($sql);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/crud.css">
    <title>TNVS Dashboard</title>

        <?php include 'partials/styles.php'; ?>

</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <?php include 'partials/sidebar.php'; ?>
    <section id="content">
        <?php include 'partials/header.php'; ?>
        <main>
            
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Facility Reservation</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50">

  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Minimalist Header -->
    <div class="flex justify-between items-center mb-12">
      <div>
        <h1 class="text-2xl font-light text-gray-900">Facilities</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your facility inventory</p>
      </div>
      <button id="openModal" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
        Add Facility
      </button>
    </div>



    <!-- Minimalist Facility Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition-colors group">
            <div class="aspect-video overflow-hidden rounded-t-lg">
              <img src="../uploads/<?php echo htmlspecialchars($row['picture']); ?>" alt="Facility" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            </div>
            <div class="p-4">
              <div class="flex items-start justify-between mb-2">
                <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></h3>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                  <?php 
                    switch($row['status']) {
                      case 'Approved': echo 'bg-green-50 text-green-700'; break;
                      case 'Rejected': echo 'bg-red-50 text-red-700'; break;
                      case 'Pending': echo 'bg-yellow-50 text-yellow-700'; break;
                      default: echo 'bg-gray-50 text-gray-700';
                    }
                  ?>">
                  <?php echo htmlspecialchars($row['status']); ?>
                </span>
              </div>
              <div class="space-y-1 text-sm text-gray-500 mb-4">
                <p><?php echo $row['capacity']; ?> people • <?php echo htmlspecialchars($row['location']); ?></p>
                <p><?php echo $row['available_date']; ?> at <?php echo $row['available_time']; ?></p>
              </div>
              <div class="flex gap-2">
                <button
                    class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-xs font-medium transition-colors editFacility"
                    data-id="<?= $row['facility_id'] ?>" 
                    data-name="<?= htmlspecialchars($row['name']) ?>"
                    data-capacity="<?= $row['capacity'] ?>"
                    data-location="<?= htmlspecialchars($row['location']) ?>"
                    data-status="<?= htmlspecialchars($row['status']) ?>"
                    data-date="<?= $row['available_date'] ?>"
                    data-time="<?= $row['available_time'] ?>"
                    data-picture="../uploads/<?= htmlspecialchars($row['picture'] ?? '') ?>">
                    Edit
                </button>

                <form method="POST" onsubmit="return confirm('Delete this facility?');" class="flex-1">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="facility_id_to_delete" value="<?= $row['facility_id'] ?>">
                    <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-md text-xs font-medium transition-colors">
                        Delete
                    </button>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-gray-500">No facilities added yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Minimalist Modal -->
  <div id="facilityModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-[999] flex items-center justify-center">
    <div class="bg-white w-[95%] max-w-md rounded-xl shadow-2xl p-6 relative animate-fadeIn">
      <h2 class="text-xl font-medium text-gray-900 mb-6">Add Facility</h2>
      <form id="facilityForm" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="facilityId" id="facilityId">
        
        <!-- Image Upload -->
        <div class="text-center">
          <div class="w-32 h-20 mx-auto mb-3 rounded-lg border-2 border-dashed border-gray-200 overflow-hidden">
            <img id="previewImage" src="https://via.placeholder.com/128x80?text=Image" alt="Preview" class="w-full h-full object-cover">
          </div>
          <input type="file" name="facilityPicture" id="facilityPicture" accept="image/*" class="text-xs text-gray-500">
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
            
            // Set the image preview, default if no picture is set or path is 'null'
            const picturePath = btn.dataset.picture;
            previewImage.src = picturePath && picturePath.endsWith('null') === false 
                ? picturePath 
                : "https://via.placeholder.com/250x150";

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
        previewImage.src = "https://via.placeholder.com/250x150";
        modal.classList.remove("hidden");
    });

    // Modal closing logic
    closeModalBtn.addEventListener("click", () => modal.classList.add("hidden"));
    modal.addEventListener("click", (e) => { if (e.target === modal) modal.classList.add("hidden"); });

    // Image preview for file input
    pictureInput.addEventListener("change", () => {
      if (pictureInput.files && pictureInput.files[0]) {
        previewImage.src = URL.createObjectURL(pictureInput.files[0]);
      }
    });

    // Show success modal if there's a success message
    <?php if (isset($_SESSION['facility_success'])): ?>
      showSuccessModal('Facility Added!', '<?= addslashes($_SESSION['facility_success']) ?>');
      <?php unset($_SESSION['facility_success']); ?>
    <?php endif; ?>

  </script>

  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
    .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
  </style>
</body>
</html>
        </main>
    </section>



    <script src="../JS/script.js"></script>
    <script src="../JS/modal.js"></script>
</body>
</html>