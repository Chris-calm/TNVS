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
        // Add facility
        $stmt = $conn->prepare("INSERT INTO facilities (name, capacity, location, available_date, available_time, picture, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssss", $name, $capacity, $location, $date, $time, $picture, $status);
        $stmt->execute();
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

  <div class="w-[95%] md:w-[90%] lg:w-[80%] mx-auto py-10">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-semibold text-gray-800">Facilities</h1>
      <button id="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl shadow-md text-sm font-medium transition">
        + Add Facility
      </button>
    </div>

    <div id="facilityGrid" class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1 overflow-hidden">
            <img src="../uploads/<?php echo htmlspecialchars($row['picture']); ?>" alt="Facility" class="w-full h-48 object-cover">
            <div class="p-5">
              <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row['name']); ?></h3>
              <p class="text-sm text-gray-600 mt-1">Capacity: <?php echo $row['capacity']; ?></p>
              <p class="text-sm text-gray-600">Location: <?php echo htmlspecialchars($row['location']); ?></p>
              <p class="text-sm text-gray-600">Status: <?php echo htmlspecialchars($row['status']); ?></p>
              <p class="text-sm text-gray-600">Date: <?php echo $row['available_date']; ?></p>
              <p class="text-sm text-gray-600">Time: <?php echo $row['available_time']; ?></p>
              <div class="flex justify-between items-center mt-4 gap-10">
                <div class="flex gap-4">
<button
    class="bg-yellow-400 hover:bg-yellow-500 text-black px-3 py-1.5 rounded-lg text-xs font-medium transition editFacility"
    data-id="<?= $row['facility_id'] ?>" 
    data-name="<?= htmlspecialchars($row['name']) ?>"
    data-capacity="<?= $row['capacity'] ?>"
    data-location="<?= htmlspecialchars($row['location']) ?>"
    data-status="<?= htmlspecialchars($row['status']) ?>"
    data-date="<?= $row['available_date'] ?>"
    data-time="<?= $row['available_time'] ?>"
    data-picture="../uploads/<?= htmlspecialchars($row['picture'] ?? '') ?>"
>
    ✎ Edit
</button>

<form method="POST" onsubmit="return confirm('Are you sure you want to delete the facility: <?= htmlspecialchars($row['name']) ?>?');">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="facility_id_to_delete" value="<?= $row['facility_id'] ?>">
    <button type="submit"
        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">
        🗑 Delete
    </button>
</form>

</div>
</div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-gray-500">No facilities added yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <div id="facilityModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[999] flex items-center justify-center">
    <div class="bg-white w-[95%] max-w-lg rounded-2xl shadow-xl p-8 relative animate-fadeIn">
      <h2 class="text-2xl font-semibold text-gray-800 mb-6">Add a Facility</h2>
      <form id="facilityForm" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="facilityId" id="facilityId"> <div class="flex flex-col items-center">
          <img id="previewImage" src="https://via.placeholder.com/250x150" alt="Preview"
            class="w-48 h-32 object-cover rounded-lg border mb-3 shadow-sm">
          <input type="file" name="facilityPicture" id="facilityPicture" accept="image/*" class="text-sm">
        </div>

        <div>
          <label class="block text-sm mb-1 font-medium text-gray-700">Facility Name</label>
          <input type="text" name="facilityName" required placeholder="Ex: Conference Room"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
          <label class="block text-sm mb-1 font-medium text-gray-700">Capacity</label>
          <input type="number" name="facilityCapacity" required placeholder="Ex: 50"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
          <label class="block text-sm mb-1 font-medium text-gray-700">Location</label>
          <input type="text" name="facilityLocation" required placeholder="Ex: 2nd Floor, East Wing"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
<div>
  <label class="block text-sm mb-1 font-medium text-gray-700">Status</label>
  <select name="facilityStatus" required
    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    <option value="Approved">Approved</option>
    <option value="Rejected">Rejected</option>
    <option value="Pending">Pending</option>
    <option value="Under Maintenance">Under Maintenance</option>
  </select>
</div>

        <div>
          <label class="block text-sm mb-1 font-medium text-gray-700">Available Date</label>
          <input type="date" name="facilityDate" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
          <label class="block text-sm mb-1 font-medium text-gray-700">Available Time</label>
          <input type="time" name="facilityTime" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex justify-end gap-4 pt-5">
          <button type="button" id="closeModal"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm transition">Cancel</button>
          <button type="submit" name="saveFacility" id="saveFacilityBtn"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm transition">Save</button>
        </div>
      </form>
    </div>
  </div>
  
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
    const facilityStatusInput = document.querySelector("select[name='facilityStatus']");
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