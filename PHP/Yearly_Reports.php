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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Yearly Reports - TNVS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen p-4">
  <div class="max-w-5xl mx-auto space-y-4">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <h1 class="text-lg font-bold">📊 Yearly Reports</h1>
      <select id="yearSelect" class="border rounded px-2 py-1 text-sm">
        <option value="2023">2023</option>
        <option value="2024">2024</option>
        <option value="2025" selected>2025</option>
      </select>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
      <div class="bg-white shadow rounded p-2 text-center">
        <h2 class="text-xs font-semibold text-gray-500">Total Rides</h2>
        <p id="totalRides" class="text-base font-bold">0</p>
      </div>
      <div class="bg-white shadow rounded p-2 text-center">
        <h2 class="text-xs font-semibold text-gray-500">Visitors</h2>
        <p id="totalVisitors" class="text-base font-bold">0</p>
      </div>
      <div class="bg-white shadow rounded p-2 text-center">
        <h2 class="text-xs font-semibold text-gray-500">Pass Requests</h2>
        <p id="totalPassRequests" class="text-base font-bold">0</p>
      </div>
      <div class="bg-white shadow rounded p-2 text-center">
        <h2 class="text-xs font-semibold text-gray-500">Cases</h2>
        <p id="totalCases" class="text-base font-bold">0</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
      <div class="bg-white p-2 shadow rounded">
        <canvas id="barChart" class="w-full h-48"></canvas>
      </div>
      <div class="bg-white p-2 shadow rounded">
        <canvas id="lineChart" class="w-full h-48"></canvas>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded p-2">
      <h2 class="text-sm font-semibold mb-2">Monthly Breakdown</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-xs border">
          <thead class="bg-gray-200">
            <tr>
              <th class="border px-2 py-1">Month</th>
              <th class="border px-2 py-1">Rides</th>
              <th class="border px-2 py-1">Visitors</th>
              <th class="border px-2 py-1">Pass Requests</th>
              <th class="border px-2 py-1">Cases</th>
            </tr>
          </thead>
          <tbody id="monthlyTable"></tbody>
        </table>
      </div>
    </div>

    <!-- Export -->
    <div class="flex justify-end">
      <button onclick="exportCSV()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Export CSV</button>
    </div>
  </div>

  <script>
    const yearlyData = {
      2023: { rides:[120,140,160,180,200,220,240,260,280,300,320,340], visitors:[60,70,80,90,100,110,120,130,140,150,160,170], passRequests:[20,25,30,35,40,45,50,55,60,65,70,75], cases:[5,6,7,8,9,10,11,12,13,14,15,16] },
      2024: { rides:[130,150,170,190,210,230,250,270,290,310,330,350], visitors:[65,75,85,95,105,115,125,135,145,155,165,175], passRequests:[22,27,32,37,42,47,52,57,62,67,72,77], cases:[6,7,8,9,10,11,12,13,14,15,16,17] },
      2025: { rides:[140,160,180,200,220,240,260,280,300,320,340,360], visitors:[70,80,90,100,110,120,130,140,150,160,170,180], passRequests:[24,29,34,39,44,49,54,59,64,69,74,79], cases:[7,8,9,10,11,12,13,14,15,16,17,18] },
    };

    const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const ctxBar = document.getElementById('barChart').getContext('2d');
    const ctxLine = document.getElementById('lineChart').getContext('2d');
    let barChart, lineChart;

    function updateReport(year){
      const data = yearlyData[year];
      const totalRides = data.rides.reduce((a,b)=>a+b,0);
      const totalVisitors = data.visitors.reduce((a,b)=>a+b,0);
      const totalPassRequests = data.passRequests.reduce((a,b)=>a+b,0);
      const totalCases = data.cases.reduce((a,b)=>a+b,0);

      document.getElementById('totalRides').innerText = totalRides;
      document.getElementById('totalVisitors').innerText = totalVisitors;
      document.getElementById('totalPassRequests').innerText = totalPassRequests;
      document.getElementById('totalCases').innerText = totalCases;

      if(barChart) barChart.destroy();
      if(lineChart) lineChart.destroy();

      barChart = new Chart(ctxBar, {
        type: 'bar',
        data: { labels: months, datasets: [{ label: 'Rides', data: data.rides, backgroundColor: 'rgba(59,130,246,0.7)' }] },
        options: { responsive:true, maintainAspectRatio:false }
      });

      lineChart = new Chart(ctxLine, {
        type: 'line',
        data: { labels: months, datasets: [
          { label:'Rides', data: data.rides, borderColor:'rgba(59,130,246,1)', fill:false },
          { label:'Visitors', data: data.visitors, borderColor:'rgba(234,179,8,1)', fill:false }
        ] },
        options: { responsive:true, maintainAspectRatio:false }
      });

      const tableBody = document.getElementById('monthlyTable');
      tableBody.innerHTML = '';
      months.forEach((m,i)=>{
        tableBody.innerHTML += `<tr>
          <td class='border px-2 py-1'>${m}</td>
          <td class='border px-2 py-1 text-center'>${data.rides[i]}</td>
          <td class='border px-2 py-1 text-center'>${data.visitors[i]}</td>
          <td class='border px-2 py-1 text-center'>${data.passRequests[i]}</td>
          <td class='border px-2 py-1 text-center'>${data.cases[i]}</td>
        </tr>`;
      });
    }

    function exportCSV(){
      const year = document.getElementById('yearSelect').value;
      const data = yearlyData[year];
      let csv = 'Month,Rides,Visitors,Pass Requests,Cases\n';
      months.forEach((m,i)=>{
        csv += `${m},${data.rides[i]},${data.visitors[i]},${data.passRequests[i]},${data.cases[i]}\n`;
      });
      const blob = new Blob([csv], {type:'text/csv'});
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `Yearly_Report_${year}.csv`;
      link.click();
    }

    document.getElementById('yearSelect').addEventListener('change', e => updateReport(e.target.value));
    updateReport('2025');
  </script>
</body>
</html>
        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>