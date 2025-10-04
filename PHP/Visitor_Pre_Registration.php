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
            <!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TNVS — Visitor Pre-Registration</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-slate-800">
  <div class="max-w-6xl mx-auto p-6">
    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">Visitor Pre-Registration</h1>
      <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Visitor</button>
    </header>

    <!-- Search bar -->
    <div class="mb-4 flex items-center gap-2">
      <input id="search" type="search" placeholder="Search visitors..." class="border rounded-md px-3 py-2 w-80" />
    </div>

    <!-- Visitor grid -->
    <section id="visitorGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></section>

    <!-- Empty state -->
    <div id="emptyState" class="hidden text-center py-10 text-slate-500">
      No visitors pre-registered yet. Click "Add Visitor" to start.
    </div>

    <!-- Modal (Add/Edit) -->
    <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4">
      <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6">
        <header class="flex items-center justify-between mb-4">
          <h2 id="modalTitle" class="text-lg font-medium">Add Visitor</h2>
          <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
        </header>

        <form id="visitorForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Visitor Name</label>
            <input id="visitorName" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Contact Number</label>
            <input id="contactNumber" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Purpose of Visit</label>
            <input id="purpose" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Date of Visit</label>
            <input id="visitDate" type="date" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Time of Visit</label>
            <input id="visitTime" type="time" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Person to Visit</label>
            <input id="personToVisit" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Visitor</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <template id="visitorTemplate">
    <article class="visitorCard bg-white rounded-xl p-4 shadow hover:shadow-md transition">
      <div class="flex items-start justify-between">
        <div>
          <h3 class="visitorName text-lg font-semibold"></h3>
          <p class="visitorPurpose text-sm text-slate-600"></p>
        </div>
        <div class="flex gap-2">
          <button class="btnView text-sm px-2 py-1 border rounded">View</button>
          <button class="btnEdit text-sm px-2 py-1 border rounded">Edit</button>
          <button class="btnDelete text-sm px-2 py-1 border rounded text-red-600">Delete</button>
        </div>
      </div>
      <p class="visitorDetails text-sm mt-2 text-slate-600"></p>
    </article>
  </template>

  <script>
    var visitors = [];

    var visitorGrid = document.getElementById('visitorGrid');
    var emptyState = document.getElementById('emptyState');
    var searchInput = document.getElementById('search');
    var modal = document.getElementById('modal');
    var modalTitle = document.getElementById('modalTitle');
    var btnAdd = document.getElementById('btnAdd');
    var modalClose = document.getElementById('modalClose');
    var cancelBtn = document.getElementById('cancelBtn');
    var visitorForm = document.getElementById('visitorForm');
    var visitorNameInput = document.getElementById('visitorName');
    var contactNumberInput = document.getElementById('contactNumber');
    var purposeInput = document.getElementById('purpose');
    var visitDateInput = document.getElementById('visitDate');
    var visitTimeInput = document.getElementById('visitTime');
    var personToVisitInput = document.getElementById('personToVisit');
    var visitorTemplate = document.getElementById('visitorTemplate');

    var editingId = null;

    function render() {
      var q = searchInput.value.trim().toLowerCase();
      var filtered = visitors.filter(function(v) {
        var text = (v.name + ' ' + v.contact + ' ' + v.purpose + ' ' + v.person + ' ' + v.date + ' ' + v.time).toLowerCase();
        return text.indexOf(q) !== -1;
      });

      visitorGrid.innerHTML = '';
      if (filtered.length === 0) {
        emptyState.classList.remove('hidden');
      } else {
        emptyState.classList.add('hidden');
      }

      filtered.forEach(function(v) {
        var node = visitorTemplate.content.cloneNode(true);
        node.querySelector('.visitorName').textContent = v.name;
        node.querySelector('.visitorPurpose').textContent = v.purpose;
        node.querySelector('.visitorDetails').textContent = 'Visiting ' + v.person + ' on ' + v.date + ' at ' + v.time;

        node.querySelector('.btnView').addEventListener('click', function() {
          alert('Visitor: ' + v.name + '\nContact: ' + v.contact + '\nPurpose: ' + v.purpose + '\nVisit Date: ' + v.date + '\nVisit Time: ' + v.time + '\nPerson to Visit: ' + v.person);
        });

        node.querySelector('.btnEdit').addEventListener('click', function() {
          openModal('Edit Visitor', v);
        });

        node.querySelector('.btnDelete').addEventListener('click', function() {
          if (confirm('Delete this visitor?')) {
            visitors = visitors.filter(function(x){ return x.id !== v.id });
            render();
          }
        });

        visitorGrid.appendChild(node);
      });
    }

    function openModal(mode, v) {
      editingId = v ? v.id : null;
      modalTitle.textContent = mode;
      visitorNameInput.value = v ? v.name : '';
      contactNumberInput.value = v ? v.contact : '';
      purposeInput.value = v ? v.purpose : '';
      visitDateInput.value = v ? v.date : '';
      visitTimeInput.value = v ? v.time : '';
      personToVisitInput.value = v ? v.person : '';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      editingId = null;
    }

    btnAdd.addEventListener('click', function(){ openModal('Add Visitor'); });
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

    visitorForm.addEventListener('submit', function(e){
      e.preventDefault();
      var payload = {
        id: editingId || Date.now(),
        name: visitorNameInput.value.trim(),
        contact: contactNumberInput.value.trim(),
        purpose: purposeInput.value.trim(),
        date: visitDateInput.value,
        time: visitTimeInput.value,
        person: personToVisitInput.value.trim()
      };

      if (editingId) {
        visitors = visitors.map(function(v){ return v.id === editingId ? payload : v });
      } else {
        visitors.unshift(payload);
      }

      render();
      closeModal();
      visitorForm.reset();
    });

    searchInput.addEventListener('input', function(){ render(); });

    render();
  </script>
</body>
</html>

        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>