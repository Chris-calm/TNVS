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
  <title>Weekly Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">📅 Weekly Reports</h2>

    <!-- Filters -->
    <div class="flex flex-wrap gap-4 mb-4">
      <select id="yearSelect" class="border rounded p-2">
        <option>2025</option>
        <option>2024</option>
        <option>2023</option>
      </select>
      <select id="monthSelect" class="border rounded p-2">
        <option value="0">January</option>
        <option value="1">February</option>
        <option value="2">March</option>
        <option value="3">April</option>
        <option value="4">May</option>
        <option value="5">June</option>
        <option value="6">July</option>
        <option value="7">August</option>
        <option value="8">September</option>
        <option value="9">October</option>
        <option value="10">November</option>
        <option value="11">December</option>
      </select>
      <select id="weekSelect" class="border rounded p-2">
        <option value="1">Week 1</option>
        <option value="2">Week 2</option>
        <option value="3">Week 3</option>
        <option value="4">Week 4</option>
        <option value="5">Week 5</option>
      </select>
      <button onclick="loadWeek()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Load Week</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full border text-sm" id="weekTable">
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
      <canvas id="lineChart" class="w-full h-64"></canvas>
    </div>
  </div>

  <script>
    let barChart, lineChart;

    function loadWeek() {
      const year = parseInt(document.getElementById('yearSelect').value);
      const month = parseInt(document.getElementById('monthSelect').value);
      const week = parseInt(document.getElementById('weekSelect').value);
      const tbody = document.querySelector('#weekTable tbody');

      // If table is loaded, clear it (toggle)
      if (tbody.innerHTML.trim() !== '') {
        tbody.innerHTML = '';
        return;
      }

      tbody.innerHTML = '';
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      // Week start and end
      const startDay = (week - 1) * 7 + 1;
      const endDay = Math.min(startDay + 6, daysInMonth);

      for (let d = startDay; d <= endDay; d++) {
        const dateObj = new Date(year, month, d);
        const options = { weekday: 'short', month: 'short', day: 'numeric' };
        const dateLabel = dateObj.toLocaleDateString('en-US', options);

        tbody.innerHTML += `
          <tr>
            <td class="border px-2 py-1 text-center">${dateLabel}</td>
            <td class="border px-2 py-1"><input type="number" class="w-16 border rounded p-1"></td>
            <td class="border px-2 py-1"><input type="number" class="w-16 border rounded p-1"></td>
            <td class="border px-2 py-1"><input type="number" class="w-16 border rounded p-1"></td>
            <td class="border px-2 py-1"><input type="number" class="w-16 border rounded p-1"></td>
          </tr>`;
      }
    }

    function updateCharts() {
      const rows = document.querySelectorAll('#weekTable tbody tr');
      const labels = [];
      const rides = [];
      const visitors = [];

      rows.forEach(row => {
        labels.push(row.cells[0].innerText);
        rides.push(parseInt(row.cells[1].querySelector('input').value) || 0);
        visitors.push(parseInt(row.cells[2].querySelector('input').value) || 0);
      });

      const ctxBar = document.getElementById('barChart').getContext('2d');
      const ctxLine = document.getElementById('lineChart').getContext('2d');

      if (barChart) barChart.destroy();
      if (lineChart) lineChart.destroy();

      barChart = new Chart(ctxBar, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Rides', data: rides, backgroundColor: 'rgba(59, 130, 246, 0.7)' }] }
      });

      lineChart = new Chart(ctxLine, {
        type: 'line',
        data: { labels, datasets: [
          { label: 'Rides', data: rides, borderColor: 'blue', fill: false },
          { label: 'Visitors', data: visitors, borderColor: 'green', fill: false }
        ]}
      });
    }

    function exportCSV() {
      const rows = document.querySelectorAll('#weekTable tr');
      let csv = [];
      rows.forEach(row => {
        let cols = Array.from(row.querySelectorAll('th,td')).map(col => col.innerText || col.querySelector('input')?.value || '');
        csv.push(cols.join(','));
      });

      const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'weekly_report.csv';
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