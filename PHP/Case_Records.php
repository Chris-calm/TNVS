<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// Handle AJAX CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        // Check permissions for add/edit
        if ($action === 'add') {
            RBACMiddleware::requirePermission('add_cases');
        } else {
            RBACMiddleware::requirePermission('edit_cases');
        }
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'];
        $complainant = $_POST['complainant'];
        $respondent = $_POST['respondent'];
        $status = $_POST['status'];
        $details = $_POST['details'];

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO case_records (title, complainant, respondent, status, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $complainant, $respondent, $status, $details);
            $stmt->execute();
            $_SESSION['case_success'] = "Case record '{$title}' has been added successfully.";
        } else {
            $stmt = $conn->prepare("UPDATE case_records SET title=?, complainant=?, respondent=?, status=?, details=? WHERE id=?");
            $stmt->bind_param("sssssi", $title, $complainant, $respondent, $status, $details, $id);
            $stmt->execute();
            $_SESSION['case_success'] = "Case record '{$title}' has been updated successfully.";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'delete') {
        // Check delete permission
        RBACMiddleware::requirePermission('delete_cases');
        $id = $_POST['id'];
        
        // Fetch the title of the record to be deleted for the success message
        $stmt_fetch = $conn->prepare("SELECT title FROM case_records WHERE id=?");
        $stmt_fetch->bind_param("i", $id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        $record_to_delete = $result_fetch->fetch_assoc();
        $title_deleted = $record_to_delete['title'] ?? 'Record';

        // Perform the deletion
        $stmt = $conn->prepare("DELETE FROM case_records WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $_SESSION['case_success'] = "Case record '{$title_deleted}' has been deleted successfully.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch all case records for display
$result = $conn->query("SELECT * FROM case_records ORDER BY id DESC");
$records = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$records_json = json_encode($records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Case Records | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
    #content main {
        background-color: transparent; /* Ensures main background is correct */
    }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>

    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-light text-gray-900">Case Records</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage legal case records and documentation</p>
                    </div>
                    <?php if (RBACMiddleware::hasPermission('add_cases')): ?>
                    <button id="btnAdd" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Add Case
                    </button>
                    <?php endif; ?>
                </div>

                <div class="flex flex-wrap gap-3 mb-6">
                    <button data-status="all" class="statusBtn px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">All</button>
                    <button data-status="Open" class="statusBtn px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">Open</button>
                    <button data-status="Closed" class="statusBtn px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">Closed</button>
                    <button data-status="In Progress" class="statusBtn px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">In Progress</button>
                    <div class="flex-grow max-w-xs">
                        <input type="text" id="searchInput" placeholder="Search cases..." class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors">
                    </div>
                </div>
            </div>

            <div id="casesGrid" class="space-y-4">
            </div>
                
            <div id="emptyState" class="hidden text-center py-10 text-slate-500">No matching case records found.</div>
            
            <template id="cardTemplate">
                <article class="bg-white rounded-lg border border-gray-100 p-4 hover:border-gray-200 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-grow min-w-0">
                            <h3 class="caseTitle font-medium text-gray-900 truncate"></h3>
                            <p class="caseComplainant text-sm text-gray-500 mt-1"></p>
                            <p class="caseRespondent text-sm text-gray-500"></p>
                        </div>
                        <div class="flex gap-2 items-center ml-4 flex-shrink-0">
                            <span class="caseStatus text-xs font-medium rounded-full px-2 py-1"></span>
                            <button class="btnView p-2 text-gray-400 hover:text-blue-600 transition-colors" title="View">
                                <i class='bx bx-show text-lg'></i>
                            </button>
                            <button class="btnEdit p-2 text-gray-400 hover:text-green-600 transition-colors" title="Edit">
                                <i class='bx bx-edit text-lg'></i>
                            </button>
                            <button class="btnDelete p-2 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                <i class='bx bx-trash text-lg'></i>
                            </button>
                        </div>
                    </div>
                </article>
            </template>


            <div id="modal" class="fixed inset-0 bg-black/20 backdrop-blur-sm hidden items-center justify-center p-4 z-[999]">
                <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl p-6 animate-fadeIn">
                    <div class="flex items-center justify-between mb-6">
                        <h2 id="modalTitle" class="text-xl font-medium text-gray-900">Add Case</h2>
                        <button id="modalClose" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>

                    <form id="caseForm" method="POST" class="space-y-4">
                        <input type="hidden" name="action" id="actionInput" value="add">
                        <input type="hidden" name="id" id="caseId">
                        
                        <div>
                            <input id="titleInput" name="title" required placeholder="Case title" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <input id="complainantInput" name="complainant" required placeholder="Complainant" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" />
                            <input id="respondentInput" name="respondent" required placeholder="Respondent" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" />
                        </div>
                        
                        <select id="statusInput" name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors">
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Closed">Closed</option>
                        </select>
                        
                        <textarea id="detailsInput" name="details" rows="4" placeholder="Case details..." 
                                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors resize-none"></textarea>
                        
                        <div class="flex gap-3 pt-4">
                            <button type="button" id="cancelBtn" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                                Save Case
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="deleteModal" class="fixed inset-0 bg-black/20 backdrop-blur-sm hidden items-center justify-center p-4 z-[1000]">
                <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl p-6 text-center animate-fadeIn">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class='bx bx-error text-red-600 text-3xl'></i>
                    </div>
                    <h2 class="text-xl font-medium text-gray-900 mb-2">Confirm Deletion</h2>
                    <p id="deleteMessage" class="text-sm text-gray-500 mb-6">Are you sure you want to delete this case record? This action cannot be undone.</p>
                    
                    <form id="deleteForm" method="POST" class="flex gap-3">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        
                        <button type="button" id="deleteCancelBtn" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                            Delete Case
                        </button>
                    </form>
                </div>
            </div>


            <script>
                const cases = <?= $records_json ?>; // Load PHP data into JS variable

                const modal = document.getElementById('modal');
                const deleteModal = document.getElementById('deleteModal'); // New Delete Modal
                const btnAdd = document.getElementById('btnAdd');
                const modalClose = document.getElementById('modalClose');
                const cancelBtn = document.getElementById('cancelBtn');
                const modalTitle = document.getElementById('modalTitle');
                const caseForm = document.getElementById('caseForm');
                const casesGrid = document.getElementById('casesGrid');
                const cardTemplate = document.getElementById('cardTemplate');
                const emptyState = document.getElementById('emptyState');
                const searchInput = document.getElementById('searchInput');

                const caseId = document.getElementById('caseId');
                const titleInput = document.getElementById('titleInput');
                const complainantInput = document.getElementById('complainantInput');
                const respondentInput = document.getElementById('respondentInput');
                const statusInput = document.getElementById('statusInput');
                const detailsInput = document.getElementById('detailsInput');
                const actionInput = document.getElementById('actionInput');
                
                // Delete Modal elements
                const deleteIdInput = document.getElementById('deleteId');
                const deleteCancelBtn = document.getElementById('deleteCancelBtn');
                const deleteForm = document.getElementById('deleteForm');

                var editingId = null;
                var activeStatusFilter = 'all';

                function render() {
                    var q = searchInput.value.trim().toLowerCase();
                    var filtered = cases.filter(function(c) {
                        var matchesQ = (c.title + ' ' + c.complainant + ' ' + c.respondent + ' ' + c.details).toLowerCase().indexOf(q) !== -1;
                        var matchesStatus = activeStatusFilter === 'all' ? true : c.status === activeStatusFilter;
                        return matchesQ && matchesStatus;
                    });

                    casesGrid.innerHTML = '';

                    if (filtered.length === 0) {
                        emptyState.classList.remove('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                    }

                    filtered.forEach(function(c) {
                        var node = cardTemplate.content.cloneNode(true);
                        node.querySelector('.caseTitle').textContent = c.title;
                        node.querySelector('.caseComplainant').textContent = 'Complainant: ' + c.complainant;
                        node.querySelector('.caseRespondent').textContent = 'Respondent: ' + c.respondent;
                        
                        var statusEl = node.querySelector('.caseStatus');
                        statusEl.textContent = c.status.toUpperCase();
                        statusEl.className = 'caseStatus text-xs font-medium rounded-full px-2 py-1 inline-block';
                        
                        if (c.status === 'Open') {
                            statusEl.classList.add('bg-green-100', 'text-green-700');
                        } else if (c.status === 'In Progress') {
                            statusEl.classList.add('bg-amber-100', 'text-amber-700');
                        } else { // Closed
                            statusEl.classList.add('bg-gray-100', 'text-gray-700');
                        }

                        node.querySelector('.btnView').addEventListener('click', function() {
                            alert('Case Title: ' + c.title + '\nComplainant: ' + c.complainant + '\nRespondent: ' + c.respondent + '\nStatus: ' + c.status + '\n\nDetails: ' + c.details);
                        });

                        node.querySelector('.btnEdit').addEventListener('click', function() {
                            openModal('Edit Case', c);
                        });

                        node.querySelector('.btnDelete').addEventListener('click', function() {
                            // *** MODIFIED: Open custom delete modal instead of browser confirm ***
                            openDeleteModal(c.id, c.title);
                        });

                        casesGrid.appendChild(node);
                    });
                }

                function openModal(mode, c) {
                    editingId = c ? c.id : null;
                    modalTitle.textContent = mode;
                    actionInput.value = c ? 'edit' : 'add';
                    caseId.value = c ? c.id : '';
                    
                    titleInput.value = c ? c.title : '';
                    complainantInput.value = c ? c.complainant : '';
                    respondentInput.value = c ? c.respondent : '';
                    statusInput.value = c ? c.status : 'Open';
                    detailsInput.value = c ? c.details : '';
                    
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    editingId = null;
                    caseForm.reset();
                }
                
                function openDeleteModal(id, title) {
                    deleteIdInput.value = id;
                    document.getElementById('deleteMessage').textContent = "Are you sure you want to permanently delete the case record titled '" + title + "'? This action cannot be undone.";
                    deleteModal.classList.remove('hidden');
                    deleteModal.classList.add('flex');
                }
                
                function closeDeleteModal() {
                    deleteModal.classList.add('hidden');
                    deleteModal.classList.remove('flex');
                    deleteIdInput.value = '';
                }

                btnAdd.addEventListener('click', function(){ openModal('Add Case'); });
                modalClose.addEventListener('click', closeModal);
                cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });
                
                deleteCancelBtn.addEventListener('click', closeDeleteModal); // Close delete modal

                searchInput.addEventListener('input', function(){ render(); });

                var statusBtns = document.querySelectorAll('.statusBtn');
                statusBtns.forEach(function(btn){
                    btn.addEventListener('click', function(){
                        // Removed the undefined 'bg-indigo-50','border-indigo-300' classes to prevent errors/unexpected styling
                        statusBtns.forEach(function(b){ b.classList.remove('bg-gray-900', 'text-white'); b.classList.add('bg-gray-100', 'text-gray-700'); }); 
                        
                        // Set the active button style (updated to use a sensible high-contrast style)
                        btn.classList.add('bg-gray-900', 'text-white');
                        btn.classList.remove('bg-gray-100', 'text-gray-700'); 

                        activeStatusFilter = btn.getAttribute('data-status');
                        render();
                    });
                });

                // Apply initial active style to 'All' button (Updated to new high-contrast style)
                document.querySelector('.statusBtn[data-status="all"]').classList.add('bg-gray-900','text-white');
                document.querySelector('.statusBtn[data-status="all"]').classList.remove('bg-gray-100', 'text-gray-700');
                
                render();
            </script>
        </main>
    </section>

    <?php include 'partials/success_modal.php'; ?>

    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['case_success'])): ?>
            showSuccessModal('Success!', '<?= addslashes($_SESSION['case_success']) ?>');
            <?php unset($_SESSION['case_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>