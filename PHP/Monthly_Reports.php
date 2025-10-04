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
  <title>Monthly Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen p-4">
  <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">Monthly Reports</h1>

    <!-- Selectors -->
    <div class="flex flex-wrap gap-4 mb-4">
      <select id="yearSelect" class="border p-2 rounded">
        <option value="2023">2023</option>
        <option value="2024">2024</option>
        <option value="2025">2025</option>
      </select>
      <select id="monthSelect" class="border p-2 rounded">
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
      <button id="loadBtn" onclick="loadMonth()" class="bg-blue-500 text-white px-4 py-2 rounded">Load Month</button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
      <div class="bg-blue-100 p-3 rounded text-center">
        <p class="text-sm">Total Rides</p>
        <h2 id="totalRides" class="text-lg font-bold">0</h2>
      </div>
      <div class="bg-green-100 p-3 rounded text-center">
        <p class="text-sm">Total Visitors</p>
        <h2 id="totalVisitors" class="text-lg font-bold">0</h2>
      </div>
      <div class="bg-yellow-100 p-3 rounded text-center">
        <p class="text-sm">Pass Requests</p>
        <h2 id="totalPass" class="text-lg font-bold">0</h2>
      </div>
      <div class="bg-red-100 p-3 rounded text-center">
        <p class="text-sm">Cases</p>
        <h2 id="totalCases" class="text-lg font-bold">0</h2>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid md:grid-cols-2 gap-4 mb-6">
      <div class="bg-white border rounded p-2">
        <canvas id="barChart" class="!h-48"></canvas>
      </div>
      <div class="bg-white border rounded p-2">
        <canvas id="lineChart" class="!h-48"></canvas>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto mb-4">
      <table class="w-full border text-sm" id="monthTable">
        <thead>
          <tr class="bg-gray-200">
            <th class="border px-2 py-1">Day</th>
            <th class="border px-2 py-1">Rides</th>
            <th class="border px-2 py-1">Visitors</th>
            <th class="border px-2 py-1">Pass Requests</th>
            <th class="border px-2 py-1">Cases</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div class="flex gap-2">
      <button onclick="updateCharts()" class="bg-green-500 text-white px-3 py-1 rounded">Update Charts</button>
      <button onclick="exportCSV()" class="bg-gray-700 text-white px-3 py-1 rounded">Export CSV</button>
    </div>
  </div>

  <script>
    let barChart, lineChart;

    function loadMonth() {
      const tbody = document.querySelector('#monthTable tbody');
      const loadBtn = document.getElementById('loadBtn');

      // If table already has rows, clear it (toggle off)
      if (tbody.innerHTML.trim() !== '') {
        tbody.innerHTML = '';
        loadBtn.innerText = "Load Month";
        return;
      }

      const year = document.getElementById('yearSelect').value;
      const month = document.getElementById('monthSelect').value;
      const daysInMonth = new Date(year, parseInt(month) + 1, 0).getDate();

      tbody.innerHTML = '';
      for (let d = 1; d <= daysInMonth; d++) {
        tbody.innerHTML += `
          <tr>
            <td class="border px-2 py-1 text-center">${d}</td>
            <td class="border px-2 py-1"><input type="number" class="w-16 border rounded p-1"></td>
            <td class="border px-2 py-1"><input type="number" class="w-16 border rounded p-1"></td>
            <td class="border px-2 py-1"><input type="number" class="w-16 border rounded p-1"></td>
            <td class="border px-2 py-1"><input type="number" class="w-16 border rounded p-1"></td>
          </tr>`;
      }
      loadBtn.innerText = "Close Month";
    }

    function updateCharts() {
      const rows = document.querySelectorAll('#monthTable tbody tr');
      const labels = [];
      const rides = [], visitors = [], passReq = [], cases = [];
      let tRides=0,tVisitors=0,tPass=0,tCases=0;

      rows.forEach((row,i) => {
        labels.push(i+1);
        const inputs = row.querySelectorAll('input');
        const r = parseInt(inputs[0].value)||0;
        const v = parseInt(inputs[1].value)||0;
        const p = parseInt(inputs[2].value)||0;
        const c = parseInt(inputs[3].value)||0;
        rides.push(r); visitors.push(v); passReq.push(p); cases.push(c);
        tRides+=r; tVisitors+=v; tPass+=p; tCases+=c;
      });

      document.getElementById('totalRides').innerText = tRides;
      document.getElementById('totalVisitors').innerText = tVisitors;
      document.getElementById('totalPass').innerText = tPass;
      document.getElementById('totalCases').innerText = tCases;

      if (barChart) barChart.destroy();
      if (lineChart) lineChart.destroy();

      const ctxBar = document.getElementById('barChart').getContext('2d');
      barChart = new Chart(ctxBar, {
        type: 'bar',
        data: { labels, datasets: [{ label:'Rides', data: rides, backgroundColor: 'rgba(59,130,246,0.6)'}]},
        options: {responsive:true, maintainAspectRatio:false}
      });

      const ctxLine = document.getElementById('lineChart').getContext('2d');
      lineChart = new Chart(ctxLine, {
        type: 'line',
        data: { labels, datasets: [
          {label:'Rides', data: rides, borderColor:'blue', fill:false},
          {label:'Visitors', data: visitors, borderColor:'green', fill:false}
        ]},
        options: {responsive:true, maintainAspectRatio:false}
      });
    }

    function exportCSV() {
      const rows = document.querySelectorAll('#monthTable tr');
      let csv = [];
      rows.forEach(row => {
        const cols = row.querySelectorAll('th,td');
        let rowData = [];
        cols.forEach(col => {
          const input = col.querySelector('input');
          rowData.push(input ? input.value : col.innerText);
        });
        csv.push(rowData.join(","));
      });
      const blob = new Blob([csv.join("\\n")], {type:'text/csv'});
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'monthly_report.csv';
      link.click();
    }
  </script>
</body>
</html>

        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>