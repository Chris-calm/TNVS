<?php
include 'db_connect.php';
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
  <title>Daily Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">📅 Daily Reports</h2>

    <!-- Date Selector -->
    <div class="flex flex-wrap gap-4 mb-4">
      <input type="date" id="dateSelect" class="border rounded p-2">
      <button onclick="loadDay()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Load Day</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full border text-sm" id="dayTable">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-2 py-1">Date</th>
            <th class="border px-2 py-1">Rides</th>
            <th class="border px-2 py-1">Visitors</th>
            <th class="border px-2 py-1">Pass Requests</th>
            <th class="border px-2 py-1">Cases</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-4 mt-4">
      <button onclick="updateCharts()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Update Charts</button>
      <button onclick="exportCSV()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Export CSV</button>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
      <canvas id="barChart" class="w-full h-64"></canvas>
      <canvas id="pieChart" class="w-full h-64"></canvas>
    </div>
  </div>

  <script>
    let barChart, pieChart;

    function loadDay() {
      const date = document.getElementById('dateSelect').value;
      const tbody = document.querySelector('#dayTable tbody');

      // Toggle: clear table if already loaded
      if (tbody.innerHTML.trim() !== '') {
        tbody.innerHTML = '';
        return;
      }

      if (!date) {
        alert("Please select a date.");
        return;
      }

      tbody.innerHTML = `
        <tr>
          <td class="border px-2 py-1 text-center">${date}</td>
          <td class="border px-2 py-1"><input type="number" class="w-20 border rounded p-1"></td>
          <td class="border px-2 py-1"><input type="number" class="w-20 border rounded p-1"></td>
          <td class="border px-2 py-1"><input type="number" class="w-20 border rounded p-1"></td>
          <td class="border px-2 py-1"><input type="number" class="w-20 border rounded p-1"></td>
        </tr>`;
    }

    function updateCharts() {
      const row = document.querySelector('#dayTable tbody tr');
      if (!row) return;

      const labels = ["Rides", "Visitors", "Pass Requests", "Cases"];
      const values = [
        parseInt(row.cells[1].querySelector('input').value) || 0,
        parseInt(row.cells[2].querySelector('input').value) || 0,
        parseInt(row.cells[3].querySelector('input').value) || 0,
        parseInt(row.cells[4].querySelector('input').value) || 0
      ];

      const ctxBar = document.getElementById('barChart').getContext('2d');
      const ctxPie = document.getElementById('pieChart').getContext('2d');

      if (barChart) barChart.destroy();
      if (pieChart) pieChart.destroy();

      barChart = new Chart(ctxBar, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Daily Report', data: values, backgroundColor: ['#3b82f6','#22c55e','#f59e0b','#ef4444'] }] },
        options: { responsive: true, maintainAspectRatio: false }
      });

      pieChart = new Chart(ctxPie, {
        type: 'pie',
        data: { labels, datasets: [{ data: values, backgroundColor: ['#3b82f6','#22c55e','#f59e0b','#ef4444'] }] },
        options: { responsive: true, maintainAspectRatio: false }
      });
    }

    function exportCSV() {
      const rows = document.querySelectorAll('#dayTable tr');
      let csv = [];
      rows.forEach(row => {
        let cols = Array.from(row.querySelectorAll('th,td')).map(col => col.innerText || col.querySelector('input')?.value || '');
        csv.push(cols.join(','));
      });

      const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'daily_report.csv';
      a.click();
      window.URL.revokeObjectURL(url);
    }
  </script>
</body>
</html>

        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>