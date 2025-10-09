<?php
/**
 * Role-Based Access Control (RBAC) Configuration
 * Defines permissions for each role in the TNVS system
 */

class RBACConfig {
    
    // Define all available permissions
    const PERMISSIONS = [
        // Dashboard & Analytics
        'view_dashboard',
        'view_statistics',
        'view_reports',
        
        // Document Management
        'view_documents',
        'upload_documents',
        'delete_documents',
        'manage_document_permissions',
        
        // Visitor Management
        'view_visitors',
        'add_visitors',
        'edit_visitors',
        'delete_visitors',
        'view_visitor_logs',
        
        // Contract Management
        'view_contracts',
        'add_contracts',
        'edit_contracts',
        'delete_contracts',
        
        // Case Records
        'view_cases',
        'add_cases',
        'edit_cases',
        'delete_cases',
        
        // Facility Management
        'view_facilities',
        'add_facilities',
        'edit_facilities',
        'delete_facilities',
        'approve_facilities',
        'reserve_rooms',
        'view_reservations',
        
        // Policy Management
        'view_policies',
        'add_policies',
        'edit_policies',
        'delete_policies',
        
        // User & System Management
        'manage_users',
        'view_settings',
        'edit_settings',
        'view_approvals',
        'approve_requests',
        'reject_requests',
        
        // System Administration
        'system_admin',
        'backup_system',
        'view_logs'
    ];
    
    // Define role permissions
    private static $rolePermissions = [
        'super_admin' => [
            // Super Admin has ALL permissions
            'view_dashboard',
            'view_statistics',
            'view_reports',
            'view_documents',
            'upload_documents',
            'delete_documents',
            'manage_document_permissions',
            'view_visitors',
            'add_visitors',
            'edit_visitors',
            'delete_visitors',
            'view_visitor_logs',
            'view_contracts',
            'add_contracts',
            'edit_contracts',
            'delete_contracts',
            'view_cases',
            'add_cases',
            'edit_cases',
            'delete_cases',
            'view_facilities',
            'add_facilities',
            'edit_facilities',
            'delete_facilities',
            'approve_facilities',
            'reserve_rooms',
            'view_reservations',
            'view_policies',
            'add_policies',
            'edit_policies',
            'delete_policies',
            'manage_users',
            'view_settings',
            'edit_settings',
            'view_approvals',
            'approve_requests',
            'reject_requests',
            'system_admin',
            'backup_system',
            'view_logs'
        ],
        
        'admin' => [
            // Admin has most permissions except system administration
            'view_dashboard',
            'view_statistics',
            'view_reports',
            'view_documents',
            'upload_documents',
            'manage_document_permissions',
            'view_visitors',
            'add_visitors',
            'edit_visitors',
            'view_visitor_logs',
            'view_contracts',
            'add_contracts',
            'edit_contracts',
            'view_cases',
            'add_cases',
            'edit_cases',
            'view_facilities',
            'add_facilities',
            'edit_facilities',
            'approve_facilities',
            'reserve_rooms',
            'view_reservations',
            'view_policies',
            'add_policies',
            'edit_policies',
            'view_settings',
            'view_approvals',
            'approve_requests',
            'reject_requests'
        ],
        
        'employee' => [
            // Employee has limited permissions - only specific modules
            'view_dashboard',
            'view_documents',           // Can view documents (View_Records.php)
            'view_visitor_logs',        // Can view visitor logs (Visitor_Logs.php)
            'reserve_rooms',            // Can reserve rooms (Reserve_Room.php)
            'view_reservations'         // Can view reservations (Reservation_Calendar.php)
        ]
    ];
    
    // Define page access mapping
    private static $pagePermissions = [
        'Dashboard.php' => ['view_dashboard'],
        'Statistics.php' => ['view_statistics'],
        'Monthly_Reports.php' => ['view_reports'],
        
        'View_Records.php' => ['view_documents'],
        'Upload_Document.php' => ['upload_documents'],
        'Document_Access_Permissions.php' => ['manage_document_permissions'],
        
        'Visitor_Logs.php' => ['view_visitor_logs'],
        'Visitor_Pre_Registration.php' => ['add_visitors', 'edit_visitors'],
        
        'Contracts.php' => ['view_contracts', 'add_contracts', 'edit_contracts'],
        
        'Case_Records.php' => ['view_cases', 'add_cases', 'edit_cases'],
        
        'Facilities_Maintenance.php' => ['view_facilities', 'add_facilities', 'edit_facilities'],
        'Reserve_Room.php' => ['reserve_rooms'],
        'Reservation_Calendar.php' => ['view_reservations'],
        
        'Policies.php' => ['view_policies', 'add_policies', 'edit_policies'],
        
        'Settings.php' => ['system_admin'],
        'Approval_Rejection_Requests.php' => ['view_approvals', 'approve_requests']
    ];
    
    /**
     * Check if a role has a specific permission
     */
    public static function hasPermission($role, $permission) {
        if (!isset(self::$rolePermissions[$role])) {
            return false;
        }
        
        return in_array($permission, self::$rolePermissions[$role]);
    }
    
    /**
     * Get all permissions for a role
     */
    public static function getRolePermissions($role) {
        return self::$rolePermissions[$role] ?? [];
    }
    
    /**
     * Check if a role can access a specific page
     */
    public static function canAccessPage($role, $page) {
        // Remove path if present, keep only filename
        $page = basename($page);
        
        if (!isset(self::$pagePermissions[$page])) {
            // If page is not in the mapping, allow access (for backward compatibility)
            return true;
        }
        
        $requiredPermissions = self::$pagePermissions[$page];
        
        // Check if user has ANY of the required permissions
        foreach ($requiredPermissions as $permission) {
            if (self::hasPermission($role, $permission)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get required permissions for a page
     */
    public static function getPagePermissions($page) {
        $page = basename($page);
        return self::$pagePermissions[$page] ?? [];
    }
    
    /**
     * Check if user can perform a specific action on a page
     */
    public static function canPerformAction($role, $action) {
        return self::hasPermission($role, $action);
    }
    
    /**
     * Get all roles
     */
    public static function getAllRoles() {
        return array_keys(self::$rolePermissions);
    }
    
    /**
     * Get role hierarchy (for future use)
     */
    public static function getRoleHierarchy() {
        return [
            'super_admin' => 3,
            'admin' => 2,
            'employee' => 1
        ];
    }
}
?>
