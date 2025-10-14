<?php
/**
 * RBAC Middleware
 * Handles role-based access control for the TNVS system
 */

require_once 'rbac_config.php';

class RBACMiddleware {
    
    /**
     * Initialize RBAC - call this at the start of protected pages
     */
    public static function init() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
            self::redirectToLogin();
        }
    }
    
    /**
     * Check if current user can access the current page
     */
    public static function checkPageAccess($page = null) {
        self::init();
        
        if ($page === null) {
            $page = basename($_SERVER['PHP_SELF']);
        }
        
        $userRole = $_SESSION['role'];
        
        if (!RBACConfig::canAccessPage($userRole, $page)) {
            self::accessDenied($page);
        }
    }
    
    /**
     * Check if current user has a specific permission
     */
    public static function hasPermission($permission) {
        self::init();
        
        $userRole = $_SESSION['role'];
        return RBACConfig::hasPermission($userRole, $permission);
    }
    
    /**
     * Require specific permission - deny access if not present
     */
    public static function requirePermission($permission) {
        if (!self::hasPermission($permission)) {
            self::accessDenied(null, "You need '$permission' permission to perform this action.");
        }
    }
    
    /**
     * Require any of the specified permissions
     */
    public static function requireAnyPermission($permissions) {
        foreach ($permissions as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }
        
        $permissionList = implode(', ', $permissions);
        self::accessDenied(null, "You need one of these permissions: $permissionList");
    }
    
    /**
     * Check if user can perform action (for AJAX requests)
     */
    public static function checkAction($action) {
        self::init();
        
        $userRole = $_SESSION['role'];
        
        if (!RBACConfig::hasPermission($userRole, $action)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Access denied. You do not have permission to perform this action.',
                'required_permission' => $action
            ]);
            exit();
        }
    }
    
    /**
     * Get current user info
     */
    public static function getCurrentUser() {
        self::init();
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'permissions' => RBACConfig::getRolePermissions($_SESSION['role'])
        ];
    }
    
    /**
     * Check if current user is admin or higher
     */
    public static function isAdmin() {
        self::init();
        
        $role = $_SESSION['role'];
        return in_array($role, ['admin', 'super_admin']);
    }
    
    /**
     * Check if current user is super admin
     */
    public static function isSuperAdmin() {
        self::init();
        
        return $_SESSION['role'] === 'super_admin';
    }
    
    /**
     * Redirect to login page
     */
    private static function redirectToLogin() {
        header("Location: ../index.php");
        exit();
    }
    
    /**
     * Show access denied page
     */
    private static function accessDenied($page = null, $customMessage = null) {
        $message = $customMessage ?: "You do not have permission to access this page.";
        $userRole = $_SESSION['role'] ?? 'Unknown';
        
        // If it's an AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => $message,
                'redirect' => 'Dashboard.php'
            ]);
            exit();
        }
        
        // For regular requests, show error page
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied | TNVS</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        </head>
        <body style="background-color: #eeeeee;" class="bg-custom min-h-screen flex items-center justify-center">
            <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="mb-6">
                    <i class='bx bx-shield-x text-6xl text-red-500'></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Access Denied</h1>
                <p class="text-gray-600 mb-6"><?= htmlspecialchars($message) ?></p>
                <div class="text-sm text-gray-500 mb-6">
                    <p>Your role: <span class="font-semibold"><?= htmlspecialchars(str_replace('_', ' ', ucwords($userRole, '_'))) ?></span></p>
                    <?php if ($page): ?>
                    <p>Requested page: <span class="font-semibold"><?= htmlspecialchars($page) ?></span></p>
                    <?php endif; ?>
                </div>
                <div class="space-y-3">
                    <a href="Dashboard.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Go to Dashboard
                    </a>
                    <button onclick="history.back()" class="block w-full bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                        Go Back
                    </button>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    
    /**
     * Generate navigation menu based on user permissions
     */
    public static function getNavigationItems() {
        self::init();
        
        $userRole = $_SESSION['role'];
        $navigation = [];
        
        // Define navigation structure with required permissions
        $navItems = [
            [
                'title' => 'Dashboard',
                'url' => 'Dashboard.php',
                'icon' => 'bx-home',
                'permission' => 'view_dashboard'
            ],
            [
                'title' => 'Documents',
                'icon' => 'bx-folder',
                'children' => [
                    [
                        'title' => 'View Documents',
                        'url' => 'View_Records.php',
                        'permission' => 'view_documents'
                    ],
                    [
                        'title' => 'Upload Document',
                        'url' => 'Upload_Document.php',
                        'permission' => 'upload_documents'
                    ],
                    [
                        'title' => 'Document Permissions',
                        'url' => 'Document_Access_Permissions.php',
                        'permission' => 'manage_document_permissions'
                    ]
                ]
            ],
            [
                'title' => 'Visitors',
                'icon' => 'bx-user',
                'children' => [
                    [
                        'title' => 'Visitor Logs',
                        'url' => 'Visitor_Logs.php',
                        'permission' => 'view_visitor_logs'
                    ],
                    [
                        'title' => 'Pre-Registration',
                        'url' => 'Visitor_Pre_Registration.php',
                        'permission' => 'add_visitors'
                    ]
                ]
            ],
            [
                'title' => 'Contracts',
                'url' => 'Contracts.php',
                'icon' => 'bx-file',
                'permission' => 'view_contracts'
            ],
            [
                'title' => 'Case Records',
                'url' => 'Case_Records.php',
                'icon' => 'bx-clipboard',
                'permission' => 'view_cases'
            ],
            [
                'title' => 'Facilities',
                'icon' => 'bx-building',
                'children' => [
                    [
                        'title' => 'Maintenance',
                        'url' => 'Facilities_Maintenance.php',
                        'permission' => 'view_facilities'
                    ],
                    [
                        'title' => 'Reserve Room',
                        'url' => 'Reserve_Room.php',
                        'permission' => 'reserve_rooms'
                    ],
                    [
                        'title' => 'Calendar',
                        'url' => 'Reservation_Calendar.php',
                        'permission' => 'view_reservations'
                    ]
                ]
            ],
            [
                'title' => 'Policies',
                'url' => 'Policies.php',
                'icon' => 'bx-book',
                'permission' => 'view_policies'
            ],
            [
                'title' => 'Reports',
                'icon' => 'bx-chart',
                'children' => [
                    [
                        'title' => 'Statistics',
                        'url' => 'Statistics.php',
                        'permission' => 'view_statistics'
                    ],
                    [
                        'title' => 'Monthly Reports',
                        'url' => 'Monthly_Reports.php',
                        'permission' => 'view_reports'
                    ]
                ]
            ],
            [
                'title' => 'Administration',
                'icon' => 'bx-cog',
                'children' => [
                    [
                        'title' => 'Settings',
                        'url' => 'Settings.php',
                        'permission' => 'view_settings'
                    ],
                    [
                        'title' => 'Approvals',
                        'url' => 'Approval_Rejection_Requests.php',
                        'permission' => 'view_approvals'
                    ]
                ]
            ]
        ];
        
        // Filter navigation based on permissions
        foreach ($navItems as $item) {
            if (isset($item['permission'])) {
                if (RBACConfig::hasPermission($userRole, $item['permission'])) {
                    $navigation[] = $item;
                }
            } elseif (isset($item['children'])) {
                $filteredChildren = [];
                foreach ($item['children'] as $child) {
                    if (RBACConfig::hasPermission($userRole, $child['permission'])) {
                        $filteredChildren[] = $child;
                    }
                }
                if (!empty($filteredChildren)) {
                    $item['children'] = $filteredChildren;
                    $navigation[] = $item;
                }
            } else {
                // No permission required, add item
                $navigation[] = $item;
            }
        }
        
        return $navigation;
    }
}
?>
