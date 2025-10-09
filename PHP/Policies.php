<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

include 'db_connect.php';
include 'partials/functions.php'; // Assuming this contains getPendingItems

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// --- ADD or EDIT Policy ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['savePolicy'])) {
    // Check permissions for add/edit policies
    $id = $_POST['policyId'] ?? null;
    if ($id) {
        RBACMiddleware::requirePermission('edit_policies');
    } else {
        RBACMiddleware::requirePermission('add_policies');
    }
    $id = $_POST['policyId'] ?? null;
    $title = $_POST['title'];
    $role = $_POST['role'];
    $short = $_POST['short'];
    $details = $_POST['details'];
    $policy_name = $title; // Use for success message

    if ($id) {
        $stmt = $conn->prepare("UPDATE policies SET title=?, role=?, short_desc=?, full_policy=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $role, $short, $details, $id);
        $stmt->execute();
        $_SESSION['policy_success'] = "Policy '{$policy_name}' has been updated successfully.";
    } else {
        $stmt = $conn->prepare("INSERT INTO policies (title, role, short_desc, full_policy) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $role, $short, $details);
        $stmt->execute();
        $_SESSION['policy_success'] = "New policy '{$policy_name}' has been added successfully.";
    }

    header("Location: Policies.php");
    exit;
}

// --- DELETE Policy ---
if (isset($_GET['delete'])) {
    // Check delete permission
    RBACMiddleware::requirePermission('delete_policies');
    $id = intval($_GET['delete']);
    
    // Fetch title before deleting
    $stmt_fetch = $conn->prepare("SELECT title FROM policies WHERE id=?");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    $policy_to_delete = $result_fetch->fetch_assoc();
    $title_deleted = $policy_to_delete['title'] ?? 'Policy';

    $stmt = $conn->prepare("DELETE FROM policies WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $_SESSION['policy_success'] = "Policy '{$title_deleted}' has been deleted successfully.";
    header("Location: Policies.php");
    exit;
}

// --- GET Policies ---
$result = $conn->query("SELECT * FROM policies ORDER BY id DESC");
$policies = $result->fetch_all(MYSQLI_ASSOC);
$policies_json = json_encode($policies);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Policies Management | TNVS Dashboard</title>
    
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
                        <h1 class="text-2xl font-light text-gray-900">Policies</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage and publish internal rules and guidelines</p>
                    </div>
                    <?php if (RBACMiddleware::hasPermission('add_policies')): ?>
                    <button id="btnAdd" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Add Policy
                    </button>
                    <?php endif; ?>
                </div>

                <div class="flex flex-wrap gap-3 mb-6">
                    <button data-role="all" class="roleBtn px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-gray-900 text-white">All</button>
                    <button data-role="administrative" class="roleBtn px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">Administrative</button>
                    <button data-role="student" class="roleBtn px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">Student</button>
                    <button data-role="admin-only" class="roleBtn px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">Admin-Only</button>
                    <div class="flex-grow max-w-xs">
                        <input type="text" id="searchInput" placeholder="Search policies..." class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors">
                    </div>
                </div>
            </div>

            <div id="policiesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            </div>
                
            <div id="emptyState" class="hidden text-center py-10 text-slate-500">No matching policies found.</div>
            
            <template id="cardTemplate">
                <article class="bg-white rounded-lg border border-gray-100 p-4 hover:border-gray-200 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-grow min-w-0">
                            <h3 class="policyTitle font-medium text-gray-900 truncate"></h3>
                            <p class="text-sm mt-1 text-slate-600 policyShortDesc"></p>
                        </div>
                        <div class="flex gap-2 items-center ml-4 flex-shrink-0">
                             <span class="policyRole text-xs font-medium rounded-full px-2 py-1 inline-block"></span>
                            <button class="btnEdit p-2 text-gray-400 hover:text-green-600 transition-colors" title="Edit">
                                <i class='bx bx-edit text-lg'></i>
                            </button>
                            <a href="#" class="btnDelete p-2 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                <i class='bx bx-trash text-lg'></i>
                            </a>
                        </div>
                    </div>
                </article>
            </template>


            <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-[999]">
              <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6 animate-fadeIn">
                <header class="flex items-center justify-between mb-4">
                  <h2 id="modalTitle" class="text-lg font-medium">Add Policy</h2>
                  <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
                </header>

                <form id="policyForm" method="POST" class="space-y-4">
                  <input type="hidden" name="policyId" id="policyId">
                  <div>
                    <label class="block text-sm font-medium mb-1">Title</label>
                    <input id="title" name="title" required class="w-full border rounded-md px-3 py-2" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1">Role</label>
                    <select id="role" name="role" class="w-full border rounded-md px-3 py-2">
                      <option value="administrative">Administrative</option>
                      <option value="student">Student</option>
                      <option value="admin-only">Admin-only</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1">Short Description</label>
                    <input id="short" name="short" class="w-full border rounded-md px-3 py-2" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1">Full Policy (details)</label>
                    <textarea id="details" name="details" rows="6" class="w-full border rounded-md px-3 py-2"></textarea>
                  </div>
                  <div class="flex justify-end gap-2">
                    <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
                    <button type="submit" name="savePolicy" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Policy</button>
                  </div>
                </form>
              </div>
            </div>
            
        </main>
    </section>

    <?php include 'partials/success_modal.php'; ?>

    <script>
        const policies = <?= $policies_json ?>; // Load PHP data into JS variable

        const modal = document.getElementById('modal');
        const btnAdd = document.getElementById('btnAdd');
        const modalClose = document.getElementById('modalClose');
        const cancelBtn = document.getElementById('cancelBtn');
        const modalTitle = document.getElementById('modalTitle');
        const policiesGrid = document.getElementById('policiesGrid');
        const cardTemplate = document.getElementById('cardTemplate');
        const emptyState = document.getElementById('emptyState');
        const searchInput = document.getElementById('searchInput');

        const idInput = document.getElementById('policyId');
        const titleInput = document.getElementById('title');
        const roleInput = document.getElementById('role');
        const shortInput = document.getElementById('short');
        const detailsInput = document.getElementById('details');
        const policyForm = document.getElementById('policyForm');

        var activeRoleFilter = 'all';

        function getRoleClasses(role) {
            role = role.toLowerCase();
            if (role === 'admin-only') {
                return { text: 'ADMIN-ONLY', class: 'bg-rose-100 text-rose-700' };
            } else if (role === 'administrative') {
                return { text: 'ADMINISTRATIVE', class: 'bg-amber-100 text-amber-700' };
            } else { // student
                return { text: 'STUDENT', class: 'bg-sky-100 text-sky-700' };
            }
        }
        
        // --- Rendering and Filtering Logic ---
        function render() {
            var q = searchInput.value.trim().toLowerCase();
            var filtered = policies.filter(function(p) {
                var matchesQ = (p.title + ' ' + p.short_desc + ' ' + p.full_policy).toLowerCase().indexOf(q) !== -1;
                var matchesRole = activeRoleFilter === 'all' ? true : p.role.toLowerCase() === activeRoleFilter;
                return matchesQ && matchesRole;
            });

            policiesGrid.innerHTML = '';

            if (filtered.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }

            filtered.forEach(function(p) {
                var node = cardTemplate.content.cloneNode(true);
                node.querySelector('.policyTitle').textContent = p.title;
                node.querySelector('.policyShortDesc').textContent = p.short_desc;
                
                var roleData = getRoleClasses(p.role);
                var roleEl = node.querySelector('.policyRole');
                roleEl.textContent = roleData.text;
                roleEl.classList.add(...roleData.class.split(' '));

                // Set up button actions
                node.querySelector('.btnEdit').addEventListener('click', function() {
                    editPolicy(p.id, p.title, p.role, p.short_desc, p.full_policy);
                });

                // Set up delete link URL
                var deleteLink = node.querySelector('.btnDelete');
                // The URL is set up to trigger a PHP delete action via GET, which handles the redirect.
                deleteLink.href = "?delete=" + p.id; 
                deleteLink.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete the policy: ' + p.title + '?')) {
                        e.preventDefault();
                    }
                });

                policiesGrid.appendChild(node);
            });
        }

        // --- Modal Logic (Retained from original) ---
        function openModal(mode) {
            modalTitle.textContent = mode;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('policyForm').reset();
            idInput.value = '';
        }

        function editPolicy(id, title, role, short, details) {
            idInput.value = id;
            titleInput.value = title;
            roleInput.value = role;
            shortInput.value = short;
            detailsInput.value = details;
            openModal('Edit Policy');
        }
        
        // --- Event Listeners ---
        btnAdd.addEventListener('click', () => {
            policyForm.reset();
            idInput.value = '';
            openModal('Add Policy');
        });
        modalClose.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        searchInput.addEventListener('input', function(){ render(); });

        var roleBtns = document.querySelectorAll('.roleBtn');
        roleBtns.forEach(function(btn){
            btn.addEventListener('click', function(){
                // Reset styles
                roleBtns.forEach(function(b){ b.classList.remove('bg-gray-900', 'text-white'); b.classList.add('bg-gray-100', 'text-gray-700'); });
                
                // Set active style
                btn.classList.add('bg-gray-900', 'text-white');
                btn.classList.remove('bg-gray-100', 'text-gray-700');

                activeRoleFilter = btn.getAttribute('data-role');
                render();
            });
        });
        
        // Initial setup
        document.querySelector('.roleBtn[data-role="all"]').classList.add('bg-gray-900', 'text-white');
        document.querySelector('.roleBtn[data-role="all"]').classList.remove('bg-gray-100', 'text-gray-700');
        render();

    </script>
    <?php include 'partials/success_modal.php'; ?>
    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['policy_success'])): ?>
            showSuccessModal('Success!', '<?= addslashes($_SESSION['policy_success']) ?>');
            <?php unset($_SESSION['policy_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>