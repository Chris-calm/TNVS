# TNVS Partials - Reusable Components

This folder contains reusable PHP components for the TNVS system.

## Files

### 1. **sidebar.php**
Contains the complete sidebar navigation menu.

**Usage:**
```php
<?php include 'partials/sidebar.php'; ?>
```

### 2. **header.php**
Contains the navigation bar with notification bell and profile dropdown.

**Requirements:**
- Session must be started
- `$pendingApprovals` array should be available
- Include after database connection

**Usage:**
```php
<?php
session_start();
include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending items
$pendingApprovals = getPendingItems($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Your head content -->
    <?php include 'partials/styles.php'; ?>
</head>
<body>
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        
        <main>
            <!-- Your page content -->
        </main>
    </section>
    
    <script src="../JS/script.js"></script>
</body>
</html>
```

### 3. **styles.php**
Contains CSS styles for notification and profile dropdowns.

**Usage:**
```php
<!-- Inside <head> tag -->
<?php include 'partials/styles.php'; ?>
```

### 4. **functions.php**
Contains common PHP functions used across the system.

**Functions:**
- `getTotalCount($conn, $table)` - Get count from any table
- `getRecentCaseRecords($conn, $limit)` - Get recent case records
- `getPendingItems($conn)` - Get all pending approvals
- `getStatusClass($status)` - Get CSS class for status badges

**Usage:**
```php
<?php
include 'partials/functions.php';

// Use the functions
$totalDocs = getTotalCount($conn, 'documents');
$pendingItems = getPendingItems($conn);
?>
```

## Complete Page Template

```php
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Include database connection and functions
include 'db_connect.php';
include 'partials/functions.php';

// Fetch data
$pendingApprovals = getPendingItems($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Your Page Title - TNVS</title>
    
    <?php include 'partials/styles.php'; ?>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        
        <main>
            <!-- Your page content goes here -->
            <div class="head-title">
                <div class="left">
                    <h1>Page Title</h1>
                </div>
            </div>
            
            <!-- Your content -->
            
        </main>
    </section>
    
    <script src="../JS/script.js"></script>
</body>
</html>
```

## Benefits

✅ **Consistency** - Same navigation across all pages
✅ **Easy Updates** - Change once, applies everywhere
✅ **Less Code** - No need to repeat HTML
✅ **Maintainability** - Easier to manage and debug
✅ **Notifications** - Automatic notification system on all pages
✅ **Profile Menu** - Consistent user profile access

## Notes

- Make sure to start the session before including header.php
- Database connection must be established before using functions.php
- The notification system automatically tracks pending items from the database
- All paths are relative to the PHP folder
