<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Monthly Reports | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
    #content main {
        background-color: transparent;
    }
    </style>
</head>
<body style="background-color: #eeeeee;" class="bg-custom flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        
        <main>
            <div class="max-w-5xl mx-auto p-6">
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
                        <p class="text-sm">Total Documents</p>
                        <h2 id="totalDocuments" class="text-lg font-bold">0</h2>
                    </div>
                    <div class="bg-green-100 p-3 rounded text-center">
                        <p class="text-sm">Total Visitors</p>
                        <h2 id="totalVisitors" class="text-lg font-bold">0</h2>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded text-center">
                        <p class="text-sm">Reservations</p>
                        <h2 id="totalReservations" class="text-lg font-bold">0</h2>
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
                <!-- Table -->
                <div class="overflow-x-auto mb-4">
                    <table class="w-full border text-sm" id="monthTable">
                        <thead>
                            <tr class="bg-custom">
                                <th class="border px-2 py-1">Day</th>
                                <th class="border px-2 py-1">Documents</th>
                                <th class="border px-2 py-1">Visitors</th>
                                <th class="border px-2 py-1">Reservations</th>
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
                    const documents = [], visitors = [], reservations = [], cases = [];
                    let tDocuments=0,tVisitors=0,tReservations=0,tCases=0;

                    rows.forEach((row,i) => {
                        labels.push(i+1);
                        const inputs = row.querySelectorAll('input');
                        const d = parseInt(inputs[0].value)||0;
                        const v = parseInt(inputs[1].value)||0;
                        const r = parseInt(inputs[2].value)||0;
                        const c = parseInt(inputs[3].value)||0;
                        documents.push(d); visitors.push(v); reservations.push(r); cases.push(c);
                        tDocuments+=d; tVisitors+=v; tReservations+=r; tCases+=c;
                    });

                    document.getElementById('totalDocuments').innerText = tDocuments;
                    document.getElementById('totalVisitors').innerText = tVisitors;
                    document.getElementById('totalReservations').innerText = tReservations;
                    document.getElementById('totalCases').innerText = tCases;

                    if (barChart) barChart.destroy();
                    if (lineChart) lineChart.destroy();

                    const ctxBar = document.getElementById('barChart').getContext('2d');
                    barChart = new Chart(ctxBar, {
                        type: 'bar',
                        data: { labels, datasets: [{ label:'Documents', data: documents, backgroundColor: 'rgba(59,130,246,0.6)'}]},
                        options: {responsive:true, maintainAspectRatio:false}
                    });

                    const ctxLine = document.getElementById('lineChart').getContext('2d');
                    lineChart = new Chart(ctxLine, {
                        type: 'line',
                        data: { labels, datasets: [
                            {label:'Documents', data: documents, borderColor:'blue', fill:false},
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
                    const blob = new Blob([csv.join("\n")], {type:'text/csv'});
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'monthly_report.csv';
                    link.click();
                }
            </script>
            <script src="../JS/script.js"></script>
        </main>
    </section>
</body>
</html>
