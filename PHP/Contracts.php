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
<title>Contracts Dashboard</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50">

<!-- Container -->
<div class="w-[95%] md:w-[90%] lg:w-[80%] mx-auto py-10">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-semibold text-gray-800">Contracts</h1>
    <button id="openContractModal" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl shadow-md text-sm font-medium transition">
      + Add Contract
    </button>
  </div>

  <!-- Contracts Grid -->
  <div id="contractsGrid" class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
    <!-- Initial Example Contract -->
    
  </div>
</div>

<!-- Add/Edit Contract Modal -->
<div id="contractModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[999] flex items-center justify-center">
  <div class="bg-white w-[95%] max-w-lg rounded-2xl shadow-xl p-8 relative animate-fadeIn">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6" id="modalTitle">Add a Contract</h2>
    <form id="contractForm" class="space-y-5">
      <input type="hidden" id="editingIndex">
      
      <!-- Preview Image -->
      <div class="flex flex-col items-center">
        <img id="contractPreviewImage" src="https://via.placeholder.com/250x150" alt="Preview"
          class="w-48 h-32 object-cover rounded-lg border mb-3 shadow-sm">
        <input type="file" id="contractPicture" accept="image/*" class="text-sm">
      </div>

      <!-- Contract Fields -->
      <div>
        <label class="block text-sm mb-1 font-medium text-gray-700">Name</label>
        <input type="text" id="contractName" placeholder="Ex: John Doe"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>

      <div>
        <label class="block text-sm mb-1 font-medium text-gray-700">Company</label>
        <input type="text" id="contractCompany" placeholder="Ex: ABC Corp"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>

      <div class="flex gap-4">
        <div class="w-1/2">
          <label class="block text-sm mb-1 font-medium text-gray-700">Position</label>
          <input type="text" id="contractPosition" placeholder="Ex: Software Engineer"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div class="w-1/2">
          <label class="block text-sm mb-1 font-medium text-gray-700">Department</label>
          <input type="text" id="contractDepartment" placeholder="Ex: IT"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
      </div>

      <div class="flex gap-4">
        <div class="w-1/2">
          <label class="block text-sm mb-1 font-medium text-gray-700">Employee ID</label>
          <input type="text" id="contractEmployeeID" placeholder="Ex: 12345"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div class="w-1/2">
          <label class="block text-sm mb-1 font-medium text-gray-700">Age</label>
          <input type="number" id="contractAge" placeholder="Ex: 30"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
      </div>

      <div>
        <label class="block text-sm mb-1 font-medium text-gray-700">Contract Type</label>
        <input type="text" id="contractType" placeholder="Ex: COE"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>

      <div class="flex gap-4">
        <div class="w-1/2">
          <label class="block text-sm mb-1 font-medium text-gray-700">Start Date</label>
          <input type="date" id="contractStart" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div class="w-1/2">
          <label class="block text-sm mb-1 font-medium text-gray-700">End Date</label>
          <input type="date" id="contractEnd" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
      </div>

      <div>
        <label class="block text-sm mb-1 font-medium text-gray-700">Status</label>
        <select id="contractStatus" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
          <option>Active</option>
          <option>Pending</option>
          <option>Expired</option>
        </select>
      </div>

      <div class="flex justify-end gap-4 pt-5">
        <button type="button" id="closeContractModal" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm transition">Cancel</button>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm transition">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->
<script>
const openContractBtn = document.getElementById("openContractModal");
const closeContractBtn = document.getElementById("closeContractModal");
const contractModal = document.getElementById("contractModal");
const contractForm = document.getElementById("contractForm");
const contractsGrid = document.getElementById("contractsGrid");
const contractPicture = document.getElementById("contractPicture");
const contractPreviewImage = document.getElementById("contractPreviewImage");
const editingIndex = document.getElementById("editingIndex");

// Open / Close Modal
openContractBtn.addEventListener("click", () => {
  contractModal.classList.remove("hidden");
  contractForm.reset();
  contractPreviewImage.src = "https://via.placeholder.com/250x150";
  editingIndex.value = '';
  document.getElementById("modalTitle").innerText = "Add a Contract";
});
closeContractBtn.addEventListener("click", () => contractModal.classList.add("hidden"));
contractModal.addEventListener("click", (e) => { if (e.target === contractModal) contractModal.classList.add("hidden"); });

// Image Preview
contractPicture.addEventListener("change", () => {
  if (contractPicture.files && contractPicture.files[0]) {
    contractPreviewImage.src = URL.createObjectURL(contractPicture.files[0]);
  }
});

// Add/Edit Contract
contractForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const name = document.getElementById("contractName").value;
  const company = document.getElementById("contractCompany").value;
  const position = document.getElementById("contractPosition").value;
  const department = document.getElementById("contractDepartment").value;
  const employeeID = document.getElementById("contractEmployeeID").value;
  const age = document.getElementById("contractAge").value;
  const type = document.getElementById("contractType").value;
  const start = document.getElementById("contractStart").value;
  const end = document.getElementById("contractEnd").value;
  const status = document.getElementById("contractStatus").value;
  const imgSrc = contractPreviewImage.src;

  const index = editingIndex.value;

  let cardHTML = `
    <img src="${imgSrc}" alt="Contract" class="w-full h-48 object-cover">
    <div class="p-5">
      <h3 class="text-lg font-semibold text-gray-800">${name}</h3>
      <p class="text-sm text-gray-600 mt-1">Company: ${company}</p>
      <p class="text-sm text-gray-600">Position: ${position}</p>
      <p class="text-sm text-gray-600">Department: ${department}</p>
      <p class="text-sm text-gray-600">Employee ID: ${employeeID}</p>
      <p class="text-sm text-gray-600">Age: ${age}</p>
      <p class="text-sm text-gray-600">Contract Type: ${type}</p>
      <p class="text-sm text-gray-600">Start Date: ${start}</p>
      <p class="text-sm text-gray-600">End Date: ${end}</p>
      <p class="text-sm text-gray-600">Status: ${status}</p>
      <div class="flex justify-between items-center mt-4 gap-10">
        <div class="flex gap-4">
          <button class="viewBtn bg-blue-400 hover:bg-blue-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">View</button>
          <button class="editBtn bg-yellow-400 hover:bg-yellow-500 text-black px-3 py-1.5 rounded-lg text-xs font-medium transition">✎ Edit</button>
          <button class="deleteBtn bg-gray-400 hover:bg-gray-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">🗑 Delete</button>
        </div>
      </div>
    </div>
  `;

  if(index) {
    // Edit existing
    contractsGrid.children[index].innerHTML = cardHTML;
  } else {
    // Add new
    const card = document.createElement("div");
    card.className = "bg-white rounded-xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1 overflow-hidden";
    card.innerHTML = cardHTML;
    contractsGrid.appendChild(card);
  }

  contractForm.reset();
  contractPreviewImage.src = "https://via.placeholder.com/250x150";
  editingIndex.value = '';
  contractModal.classList.add("hidden");
});

// Delegated Event Listeners for View/Edit/Delete
contractsGrid.addEventListener("click", (e) => {
  const card = e.target.closest("div.bg-white");
  if(!card) return;
  const index = Array.from(contractsGrid.children).indexOf(card);

  // View
  if(e.target.classList.contains("viewBtn")) {
    alert(card.innerText.replace(/\n\s+/g,"\n"));
  }

  // Edit
  if(e.target.classList.contains("editBtn")) {
    const details = card.querySelectorAll("p");
    document.getElementById("contractName").value = card.querySelector("h3").innerText;
    document.getElementById("contractCompany").value = details[0].innerText.replace("Company: ","");
    document.getElementById("contractPosition").value = details[1].innerText.replace("Position: ","");
    document.getElementById("contractDepartment").value = details[2].innerText.replace("Department: ","");
    document.getElementById("contractEmployeeID").value = details[3].innerText.replace("Employee ID: ","");
    document.getElementById("contractAge").value = details[4].innerText.replace("Age: ","");
    document.getElementById("contractType").value = details[5].innerText.replace("Contract Type: ","");
    document.getElementById("contractStart").value = details[6].innerText.replace("Start Date: ","");
    document.getElementById("contractEnd").value = details[7].innerText.replace("End Date: ","");
    document.getElementById("contractStatus").value = details[8].innerText.replace("Status: ","");
    contractPreviewImage.src = card.querySelector("img").src;
    editingIndex.value = index;
    document.getElementById("modalTitle").innerText = "Edit Contract";
    contractModal.classList.remove("hidden");
  }

  // Delete
  if(e.target.classList.contains("deleteBtn")) {
    if(confirm("Are you sure you want to delete this contract?")) {
      card.remove();
    }
  }
});
</script>

<style>
@keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.animate-fadeIn { animation: fadeIn 0.25s ease-out; }
</style>

</body>
</html>

        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>