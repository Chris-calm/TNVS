<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
// Optionally include functions.php if any custom functions are used here
// include 'partials/functions.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <div class="head-title">
                <div class="left">
                    <h1>Facilities Maintenance</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="Dashboard.php">Dashboard</a>
                        </li>
                        <li><i class='bx bx-chevron-right' ></i></li>
                        <li>
                            <a class="active" href="Facilities_Maintenance.php">Facilities Maintenance</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="w-full mx-auto py-5">
                <div class="bg-yellow-600 text-white py-3 px-4 rounded-t-lg shadow-md">
                    <h2 class="text-xl font-semibold">🛠️ Facilities Currently Under Maintenance</h2>
                </div>
                
                <div class="p-6 bg-white rounded-b-lg shadow-lg">
                    
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                        <?php
                        // Fetch all facilities marked as Maintenance
                        $query = "SELECT * FROM facilities WHERE status = 'Maintenance' OR status = 'Under Maintenance'";
                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "
                                <div class='bg-white rounded-lg shadow-md p-4 border border-yellow-200 hover:shadow-xl transition-shadow'>
                                    ".(!empty($row['image_path']) ? "<img src='../uploads/{$row['image_path']}' alt='{$row['name']}' class='w-full h-40 object-cover rounded-lg mb-3'>" : "<div class='w-full h-40 bg-gray-200 flex items-center justify-center rounded-lg mb-3 text-gray-500'>No Image</div>")."
                                    <h3 class='text-lg font-semibold text-gray-800 mb-2'>".htmlspecialchars($row['name'])."</h3>
                                    <p class='text-sm text-gray-600 mb-3'>Facility ID: ".htmlspecialchars($row['facility_id'])."</p>
                                    <span class='inline-block px-3 py-1 text-sm font-medium rounded-full text-yellow-700 bg-yellow-100'>
                                        Under Maintenance
                                    </span>
                                </div>
                                ";
                            }
                        } else {
                            echo "
                            <div class='col-span-full text-center text-gray-600 text-lg py-10'>
                                <p class='text-2xl mb-2'>✅</p>
                                <p>No facilities are currently under maintenance.</p>
                            </div>
                            ";
                        }
                        ?>
                    </div>
                </div>
            </div>
            </main>
    </section>

    <script src="../JS/script.js"></script>
</body>
</html>