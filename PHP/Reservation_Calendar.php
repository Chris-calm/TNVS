<?php
include 'db_connect.php'; // Ensure this file correctly establishes $conn

// --- 1. HANDLE DELETE ACTION (For Approved/Rejected Facilities Maintenance) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facility_id']) && $_POST['action'] === 'delete') {
    $facility_id = intval($_POST['facility_id']);

    // Delete the facility record
    $stmt = $conn->prepare("DELETE FROM facilities WHERE id=?");
    $stmt->bind_param("i", $facility_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to refresh the page
    header("Location: Facilities_Maintenance.php");
    exit();
}

// --- 2. FETCH ALL APPROVED AND REJECTED FACILITIES FOR DISPLAY ---
// We only fetch facilities that have been processed (Approved or Rejected)
$sql = "SELECT * FROM facilities WHERE status IN ('Approved', 'Rejected') ORDER BY name ASC";
$result = $conn->query($sql);

$approvedFacilities = [];
$rejectedFacilities = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'Approved') {
            $approvedFacilities[] = $row;
        } elseif ($row['status'] === 'Rejected') {
            $rejectedFacilities[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>TNVS Facility Maintenance</title>
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
            <div class="w-[95%] md:w-[90%] lg:w-[80%] mx-auto py-10">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-semibold text-gray-800">Facilities Maintenance</h1>
                    </div>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h2 class="text-xl font-semibold text-green-700 mb-4">✅ Approved Facilities (<?php echo count($approvedFacilities); ?>)</h2>
                        <div id="approvedGrid" class="grid sm:grid-cols-1 gap-6">
                            <?php if (empty($approvedFacilities)): ?>
                                <p class="text-gray-500">No approved facilities found.</p>
                            <?php else: ?>
                                <?php foreach ($approvedFacilities as $row): 
                                    $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                ?>
                                    <div class='bg-white rounded-xl shadow-md p-4 flex gap-4 items-center'>
                                        <img src='<?php echo $picturePath; ?>' alt='<?php echo htmlspecialchars($row['name']); ?>' class='w-20 h-20 object-cover rounded-lg border'>
                                        <div class="flex-grow">
                                            <h3 class='text-lg font-semibold'><?php echo htmlspecialchars($row['name']); ?></h3>
                                            <p class='text-sm text-gray-600'>Status: <span class="text-green-600 font-medium">Approved</span></p>
                                        </div>
                                        <form method='POST' onsubmit="return confirm('Are you sure you want to delete this facility?');">
                                            <input type='hidden' name='facility_id' value='<?php echo $row['id']; ?>'>
                                            <input type='hidden' name='action' value='delete'>
                                            <button type='submit' class='text-red-500 hover:text-red-700 p-2 rounded-full transition'>
                                                <i class='bx bx-trash text-xl'></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-red-700 mb-4">❌ Rejected Facilities (<?php echo count($rejectedFacilities); ?>)</h2>
                        <div id="rejectedGrid" class="grid sm:grid-cols-1 gap-6">
                            <?php if (empty($rejectedFacilities)): ?>
                                <p class="text-gray-500">No rejected facilities found.</p>
                            <?php else: ?>
                                <?php foreach ($rejectedFacilities as $row): 
                                    $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                ?>
                                    <div class='bg-white rounded-xl shadow-md p-4 flex gap-4 items-center'>
                                        <img src='<?php echo $picturePath; ?>' alt='<?php echo htmlspecialchars($row['name']); ?>' class='w-20 h-20 object-cover rounded-lg border'>
                                        <div class="flex-grow">
                                            <h3 class='text-lg font-semibold'><?php echo htmlspecialchars($row['name']); ?></h3>
                                            <p class='text-sm text-gray-600'>Status: <span class="text-red-600 font-medium">Rejected</span></p>
                                        </div>
                                        <form method='POST' onsubmit="return confirm('Are you sure you want to permanently delete this facility?');">
                                            <input type='hidden' name='facility_id' value='<?php echo $row['id']; ?>'>
                                            <input type='hidden' name='action' value='delete'>
                                            <button type='submit' class='text-red-500 hover:text-red-700 p-2 rounded-full transition'>
                                                <i class='bx bx-trash text-xl'></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            </main>
    </section>

    <script src="../JS/script.js"></script>
</body>
</html>