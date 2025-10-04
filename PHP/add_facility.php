<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name           = $_POST['facilityName'];
    $capacity       = $_POST['facilityCapacity'];
    $location       = $_POST['facilityLocation'];
    $status         = $_POST['facilityStatus'];
    $available_date = $_POST['facilityDate'];
    $available_time = $_POST['facilityTime'];

    // Handle picture upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // create folder if not exists
    }

    $picture = null;
    if (isset($_FILES['facilityPicture']) && $_FILES['facilityPicture']['error'] == 0) {
        $target_file = $target_dir . time() . "_" . basename($_FILES["facilityPicture"]["name"]);
        if (move_uploaded_file($_FILES["facilityPicture"]["tmp_name"], $target_file)) {
            $picture = $target_file;
        }
    }

    // Insert into DB
    $sql = "INSERT INTO facilities (name, capacity, location, status, picture, available_date, available_time)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssss", $name, $capacity, $location, $status, $picture, $available_date, $available_time);

    if ($stmt->execute()) {
        echo "<script>alert('Facility added successfully!'); window.location.href='Dashboard.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
