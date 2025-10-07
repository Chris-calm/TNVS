<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// Handle maintenance actions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facility_id']) && isset($_POST['action'])) {
    $facility_id = intval($_POST['facility_id']);
    $action = $_POST['action'];

    if ($action === 'return_to_service') {
        // Return facility to approved status
        $stmt = $conn->prepare("UPDATE facilities SET status='Approved' WHERE facility_id=?");
        $stmt->bind_param("i", $facility_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['maintenance_success'] = "Facility has been returned to service successfully.";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Facilities Maintenance | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* This style is for the content within the main section */
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        
        <main>
            <!-- Minimalist Header -->
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="mb-12">
                    <h1 class="text-2xl font-light text-gray-900">Maintenance Center</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage facilities under maintenance</p>
                </div>

                <!-- Facilities Grid -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php
                        // Fetch all facilities marked as Maintenance
                        $query = "SELECT * FROM facilities WHERE status = 'Maintenance' OR status = 'Under Maintenance'";
                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                                echo "
                                <div class='bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition-colors group'>
                                    <div class='aspect-video overflow-hidden rounded-t-lg'>
                                        <img src='{$picturePath}' alt='".htmlspecialchars($row['name'])."' class='w-full h-full object-cover group-hover:scale-105 transition-transform duration-300'>
                                    </div>
                                    <div class='p-4'>
                                        <div class='flex items-start justify-between mb-2'>
                                            <h3 class='font-medium text-gray-900'>".htmlspecialchars($row['name'])."</h3>
                                            <span class='px-2 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700'>Maintenance</span>
                                        </div>
                                        <div class='space-y-1 text-sm text-gray-500 mb-4'>
                                            <p>".htmlspecialchars($row['capacity'])." people • ".htmlspecialchars($row['location'])."</p>
                                        </div>
                                        <form method='POST' onsubmit=\"return confirm('Return to service?');\">
                                            <input type='hidden' name='facility_id' value='".htmlspecialchars($row['facility_id'])."'>
                                            <input type='hidden' name='action' value='return_to_service'>
                                            <button type='submit' class='w-full bg-green-50 hover:bg-green-100 text-green-700 px-3 py-2 rounded-md text-xs font-medium transition-colors'>
                                                Return to Service
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                ";
                            }
                        } else {
                            echo "
                            <div class='col-span-full text-center py-16'>
                                <div class='text-gray-400 mb-4'>
                                    <i class='bx bx-wrench text-4xl'></i>
                                </div>
                                <p class='text-gray-500'>No facilities under maintenance</p>
                            </div>
                            ";
                        }
                        ?>
                    </div>
                </div>
            </div>
            </main>
    </section>

    <!-- Include Success Modal -->
    <?php include 'partials/success_modal.php'; ?>

    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['maintenance_success'])): ?>
            showSuccessModal('Success!', '<?= addslashes($_SESSION['maintenance_success']) ?>');
            <?php unset($_SESSION['maintenance_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>