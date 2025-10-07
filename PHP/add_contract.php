<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['contractName'];
    $company = $_POST['contractCompany'];
    $position = $_POST['contractPosition'];
    $department = $_POST['contractDepartment'];
    $employee_id = $_POST['contractEmployeeID'];
    $age = $_POST['contractAge'];
    $type = $_POST['contractType'];
    $start = $_POST['contractStart'];
    $end = $_POST['contractEnd'];
    $status = $_POST['contractStatus'];

    // Handle Image Upload
    $picture = null;
    if (isset($_FILES['contractPicture']) && $_FILES['contractPicture']['error'] == 0) {
        $uploadDir = "../uploads/contracts/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $picture = uniqid() . "_" . basename($_FILES['contractPicture']['name']);
        move_uploaded_file($_FILES['contractPicture']['tmp_name'], $uploadDir . $picture);
    }

    $stmt = $conn->prepare("INSERT INTO contracts (name, company, position, department, employee_id, age, contract_type, start_date, end_date, status, picture) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissssss", $name, $company, $position, $department, $employee_id, $age, $type, $start, $end, $status, $picture);

    if ($stmt->execute()) {
        echo "<script>alert('Contract added successfully!'); window.location.href='Contracts.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
