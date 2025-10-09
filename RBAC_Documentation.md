# RBAC (Role-Based Access Control) Implementation Guide

## Overview

This document describes the Role-Based Access Control (RBAC) system implemented for the TNVS (Transport Network Vehicle System). The RBAC system ensures that users can only access features and perform actions that are appropriate for their assigned role.

## System Architecture

### Core Components

1. **rbac_config.php** - Configuration file defining roles, permissions, and page access rules
2. **rbac_middleware.php** - Middleware class handling access control logic
3. **Role_Management.php** - Administrative interface for managing users and roles

### Role Hierarchy

The system implements three main roles with different permission levels:

#### 1. Super Admin
- **Full system access** - Can perform all operations
- **User management** - Can create, edit, and delete user accounts
- **System administration** - Access to system logs, backups, and configuration
- **All permissions** - Inherits all permissions from lower roles

#### 2. Admin
- **Management access** - Can manage most system features
- **Document management** - Upload, manage permissions, delete documents
- **Visitor management** - Full visitor and pre-registration management
- **Facility management** - Approve facilities, manage reservations
- **Reports and analytics** - Access to statistics and reports
- **Limited user management** - Cannot manage other admin accounts

#### 3. Employee
- **Basic access** - Limited to essential daily operations
- **View documents** - Can view but not upload or manage documents
- **Add visitors** - Can register new visitors and view logs
- **View cases** - Can view and add case records
- **Reserve facilities** - Can make facility reservations
- **No administrative access** - Cannot approve requests or manage users

## Implementation Details

### Permission System

The system uses granular permissions for fine-grained access control:

```php
// Document permissions
'view_documents', 'upload_documents', 'delete_documents', 'manage_document_permissions'

// Visitor permissions
'view_visitors', 'add_visitors', 'edit_visitors', 'delete_visitors', 'view_visitor_logs'

// Case management permissions
'view_cases', 'add_cases', 'edit_cases', 'delete_cases'

// Facility permissions
'view_facilities', 'add_facilities', 'edit_facilities', 'delete_facilities', 
'approve_facilities', 'reserve_rooms', 'view_reservations'

// Administrative permissions
'manage_users', 'view_settings', 'edit_settings', 'view_approvals', 
'approve_requests', 'reject_requests'
```

### Page Access Control

Each page is protected by the RBAC middleware:

```php
<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();
```

### Action-Level Permissions

Critical actions are protected with specific permission checks:

```php
// Check permission before performing action
RBACMiddleware::requirePermission('upload_documents');

// Check if user has permission (returns boolean)
if (RBACMiddleware::hasPermission('delete_documents')) {
    // Show delete button
}
```

### Navigation Control

The sidebar navigation is dynamically generated based on user permissions:

```php
<?php if (RBACMiddleware::hasPermission('view_documents')): ?>
<li><a href="View_Records.php">View Documents</a></li>
<?php endif; ?>
```

## Usage Examples

### Adding RBAC to a New Page

1. **Add page access check:**
```php
<?php
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();
```

2. **Add permission checks for actions:**
```php
if ($_POST['action'] === 'delete') {
    RBACMiddleware::requirePermission('delete_items');
    // Perform deletion
}
```

3. **Conditional UI elements:**
```php
<?php if (RBACMiddleware::hasPermission('add_items')): ?>
<button>Add New Item</button>
<?php endif; ?>
```

### Adding New Permissions

1. **Update rbac_config.php:**
```php
const PERMISSIONS = [
    // ... existing permissions
    'new_permission_name',
];

private static $rolePermissions = [
    'super_admin' => [
        // ... existing permissions
        'new_permission_name',
    ],
    // ... other roles
];
```

2. **Update page permissions mapping:**
```php
private static $pagePermissions = [
    'NewPage.php' => ['new_permission_name'],
    // ... other pages
];
```

### Creating New Roles

1. **Add role to rbac_config.php:**
```php
private static $rolePermissions = [
    // ... existing roles
    'new_role' => [
        'permission1',
        'permission2',
        // ... role permissions
    ],
];
```

2. **Update setup_users.php** to include the new role in user creation.

## Security Features

### Access Denial Handling

- **Graceful degradation** - Users see appropriate error messages
- **Automatic redirection** - Unauthorized users are redirected to safe pages
- **AJAX support** - API endpoints return proper HTTP status codes
- **Audit trail** - Access attempts are logged for security monitoring

### Session Management

- **Secure session handling** - All pages require valid authentication
- **Role validation** - User roles are verified on each request
- **Session timeout** - Automatic logout after inactivity

### Input Validation

- **Permission validation** - All permissions are validated against the configuration
- **Role validation** - User roles are checked against allowed values
- **SQL injection protection** - All database queries use prepared statements

## Administration

### User Management

Super Admins can access the Role Management interface at `/PHP/Role_Management.php` to:

- **Create new users** with specific roles
- **Update user roles** for existing accounts
- **Delete user accounts** (except their own)
- **View user activity** and role assignments

### Permission Auditing

The system provides tools for auditing permissions:

```php
// Get all permissions for a role
$permissions = RBACConfig::getRolePermissions('admin');

// Check if role can access a page
$canAccess = RBACConfig::canAccessPage('employee', 'Statistics.php');

// Get current user info with permissions
$user = RBACMiddleware::getCurrentUser();
```

## Troubleshooting

### Common Issues

1. **Access Denied Errors**
   - Check if user has required permissions
   - Verify role assignment in database
   - Ensure page is properly configured in rbac_config.php

2. **Navigation Items Missing**
   - Check permission requirements in sidebar.php
   - Verify user role has required permissions
   - Clear browser cache and refresh

3. **AJAX Requests Failing**
   - Ensure AJAX endpoints use RBACMiddleware::checkAction()
   - Check HTTP response codes (403 = Access Denied)
   - Verify permission names match configuration

### Debug Mode

Enable debug mode by adding this to your pages:

```php
// Show current user permissions (for debugging)
if (RBACMiddleware::isSuperAdmin()) {
    $user = RBACMiddleware::getCurrentUser();
    echo '<pre>' . print_r($user['permissions'], true) . '</pre>';
}
```

## Best Practices

1. **Principle of Least Privilege** - Give users only the minimum permissions needed
2. **Regular Audits** - Periodically review user roles and permissions
3. **Secure Defaults** - New features should be restricted by default
4. **Clear Documentation** - Document all custom permissions and roles
5. **Testing** - Test access control with different user roles

## Migration Notes

### Existing Pages

All existing pages have been updated with RBAC protection. If you encounter issues:

1. Check that the page includes `rbac_middleware.php`
2. Verify the page is listed in `$pagePermissions` array
3. Ensure UI elements have appropriate permission checks

### Database Changes

The RBAC system uses the existing `users` table with the `role` column. No additional database changes are required.

## Support

For questions or issues with the RBAC system:

1. Check this documentation first
2. Review the configuration in `rbac_config.php`
3. Test with different user roles
4. Check server logs for detailed error messages

---

**Last Updated:** October 2025  
**Version:** 1.0  
**Author:** TNVS Development Team
