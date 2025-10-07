# 🔔 Universal Notification System Implementation Guide

## ✅ Files Already Updated with Notifications:
- Dashboard.php ✅
- Approval_Rejection_Requests.php ✅
- Reservation_Calendar.php ✅

## 📋 Files That Need Notification System:

### **Main Pages:**
- Statistics.php
- Reserve_Room.php
- Blacklist_Watchlist.php
- Case_Records.php
- Contracts.php
- Document_Access_Permissions.php
- Facilities_Maintenance.php
- Monthly_Reports.php
- Policies.php
- Settings.php
- Upload_Document.php
- View_Records.php
- Visitor_Logs.php
- Visitor_Pre_Registration.php

## 🔧 **Step-by-Step Conversion Process**

### **Step 1: Update PHP Header Section**

**FIND THIS at the top of each file:**
```php
<?php
include 'db_connect.php';
// or
<?php
session_start();
include 'db_connect.php';
```

**REPLACE WITH:**
```php
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// Your existing page logic continues here...
```

### **Step 2: Update HTML Head Section**

**ADD THIS** after your existing CSS links:
```php
<?php include 'partials/styles.php'; ?>
```

**Example:**
```html
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Your Page Title</title>
    
    <?php include 'partials/styles.php'; ?>  <!-- ADD THIS -->
</head>
```

### **Step 3: Replace Sidebar and Header**

**FIND AND DELETE** the entire sidebar section:
```html
<section id="sidebar">
    <!-- All sidebar content -->
</section>
```

**REPLACE WITH:**
```php
<?php include 'partials/sidebar.php'; ?>
```

**FIND THIS:**
```html
<section id="content">
    <nav>
        <!-- Navigation content -->
    </nav>
```

**REPLACE WITH:**
```php
<section id="content">
    <?php include 'partials/header.php'; ?>
```

### **Step 4: Clean Up JavaScript**

**REMOVE** any duplicate JavaScript for notifications/profile popups at the bottom of files.

**KEEP ONLY:**
```html
<script src="../JS/script.js"></script>
```

## 🎯 **Quick Template for New Files**

```php
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// ===== YOUR PAGE-SPECIFIC CODE HERE =====
// Example: Handle forms, fetch data, etc.

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
    
    <style>
        /* Your custom styles */
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Your Page Title</h1>
                </div>
            </div>

            <!-- YOUR PAGE CONTENT -->
            
        </main>
    </section>
    
    <script src="../JS/script.js"></script>
</body>
</html>
```

## 🔔 **What the Notification System Provides:**

### **Automatic Notifications For:**
1. **Pending Facility Reservations** - Shows when facilities need approval
2. **New Facility Requests** - Shows when new facilities are submitted
3. **Visitor Pre-Registrations** - Shows when visitors need approval

### **Features:**
- ✅ **Real-time notification count** in the bell icon
- ✅ **Dropdown with details** showing type, name, and date
- ✅ **Direct links** to approval pages
- ✅ **Profile dropdown** with settings and logout
- ✅ **Responsive design** works on all devices
- ✅ **Automatic updates** across all pages

## 📊 **Notification Data Sources:**

The system pulls from these database tables:
- `reservations` table (status = 'Pending')
- `facilities` table (status = 'Pending')  
- `visitors` table (request_status = 'pending')

## 🚀 **Benefits After Implementation:**

✅ **Consistent Experience** - Same navigation and notifications everywhere
✅ **Real-time Updates** - Users see pending items immediately
✅ **Better Workflow** - Direct links to approval pages
✅ **Professional Look** - Modern notification system
✅ **Easy Maintenance** - Update once, applies everywhere
✅ **Session Security** - Login protection on all pages

## ⚡ **Quick Conversion Checklist:**

For each file:
- [ ] Add session check and includes at top
- [ ] Add `$pendingApprovals = getPendingItems($conn);`
- [ ] Include `partials/styles.php` in head
- [ ] Replace sidebar with `<?php include 'partials/sidebar.php'; ?>`
- [ ] Replace nav with `<?php include 'partials/header.php'; ?>`
- [ ] Remove duplicate JavaScript
- [ ] Test the page

## 🎯 **Priority Order for Updates:**

1. **High Priority** (User-facing pages):
   - Reserve_Room.php
   - Visitor_Pre_Registration.php
   - Upload_Document.php
   - View_Records.php

2. **Medium Priority** (Admin pages):
   - Facilities_Maintenance.php
   - Case_Records.php
   - Visitor_Logs.php
   - Statistics.php

3. **Low Priority** (Settings/Reports):
   - Settings.php
   - Monthly_Reports.php
   - Policies.php
   - Contracts.php

Start with high priority files first for maximum user impact!
