# 🏗️ **Complete Facility Status Workflow Guide**

## 📊 **Status Flow Overview**

```
[NEW FACILITY] → [PENDING] → [APPROVED/REJECTED] → [UNDER MAINTENANCE] → [BACK TO APPROVED]
     ↓              ↓              ↓                      ↓                    ↓
Reserve_Room → Approval_Requests → Reservation_Calendar → Facilities_Maintenance → Reservation_Calendar
```

## 🎯 **File Responsibilities & Status Management**

### **1. Reserve_Room.php** - *Facility Creation Hub*
**Purpose**: Create and manage all facilities
**Statuses Displayed**: ALL (Pending, Approved, Rejected, Under Maintenance)
**Actions Available**:
- ✅ **Create New Facility** → Always starts as "Pending"
- ✅ **Edit Existing Facility** → Maintains current status
- ✅ **Delete Facility** → Permanent removal
- ✅ **View All Facilities** → Color-coded status badges

**Features**:
- Success messages when facilities are submitted
- Workflow guide showing the 3-step process
- Status badges: 🟡 Pending, 🟢 Approved, 🔴 Rejected, 🟠 Under Maintenance

---

### **2. Approval_Rejection_Requests.php** - *Approval Gateway*
**Purpose**: Review and process pending facilities
**Statuses Displayed**: Only "Pending"
**Actions Available**:
- ✅ **Approve** → Changes status to "Approved"
- ✅ **Reject** → Changes status to "Rejected"

**Features**:
- Clean approval interface
- Only shows facilities awaiting decision
- Immediate status updates
- Confirmation dialogs for all actions

---

### **3. Reservation_Calendar.php** - *Active Facility Management*
**Purpose**: Manage approved and rejected facilities
**Statuses Displayed**: "Approved" and "Rejected"
**Actions Available**:

**For Approved Facilities**:
- 🔧 **Set to Maintenance** → Changes status to "Under Maintenance"
- 🗑️ **Delete Permanently** → Complete removal

**For Rejected Facilities**:
- 🔄 **Reconsider for Approval** → Changes status back to "Pending"
- 🗑️ **Delete Permanently** → Complete removal

**Features**:
- Split view: Approved vs Rejected
- Detailed facility information
- Action buttons with icons
- Confirmation dialogs

---

### **4. Facilities_Maintenance.php** - *Maintenance Center*
**Purpose**: Manage facilities under maintenance
**Statuses Displayed**: Only "Under Maintenance"
**Actions Available**:
- ✅ **Return to Service** → Changes status back to "Approved"

**Features**:
- Dedicated maintenance view
- Success messages for actions
- Return to service functionality
- Clean maintenance interface

---

## 🔄 **Complete Status Lifecycle**

### **Phase 1: Creation**
1. User goes to **Reserve_Room.php**
2. Clicks "+ Add New Facility"
3. Fills out form (status automatically set to "Pending")
4. Facility appears in pending list with yellow badge

### **Phase 2: Approval**
1. Admin goes to **Approval_Rejection_Requests.php**
2. Reviews pending facilities
3. Clicks "Approve" or "Reject"
4. Facility moves to next phase

### **Phase 3: Management**
1. Approved/Rejected facilities appear in **Reservation_Calendar.php**
2. **Approved facilities** can be:
   - Set to maintenance → Goes to Facilities_Maintenance.php
   - Deleted permanently
3. **Rejected facilities** can be:
   - Reconsidered → Goes back to Approval_Rejection_Requests.php
   - Deleted permanently

### **Phase 4: Maintenance**
1. Facilities under maintenance appear in **Facilities_Maintenance.php**
2. Can be returned to service → Goes back to Reservation_Calendar.php as "Approved"

---

## 🎨 **Status Color Coding**

| Status | Color | Badge | Location |
|--------|-------|-------|----------|
| **Pending** | 🟡 Yellow | `bg-yellow-100 text-yellow-700` | Reserve_Room, Approval_Requests |
| **Approved** | 🟢 Green | `bg-green-100 text-green-700` | Reserve_Room, Reservation_Calendar |
| **Rejected** | 🔴 Red | `bg-red-100 text-red-700` | Reserve_Room, Reservation_Calendar |
| **Under Maintenance** | 🟠 Orange | `bg-yellow-100 text-yellow-700` | Reserve_Room, Facilities_Maintenance |

---

## 🚀 **Key Benefits of This Workflow**

✅ **Clear Separation**: Each file has a specific purpose
✅ **Logical Flow**: Status progression makes sense
✅ **Reversible Actions**: Rejected facilities can be reconsidered
✅ **Maintenance Support**: Facilities can be temporarily taken offline
✅ **Visual Feedback**: Color-coded statuses everywhere
✅ **User-Friendly**: Confirmation dialogs and success messages
✅ **Admin Control**: Centralized approval system
✅ **Data Integrity**: Proper status transitions

---

## 📋 **Quick Reference Actions**

### **Reserve_Room.php**
- Create → Pending
- Edit → Keep Status
- Delete → Remove

### **Approval_Rejection_Requests.php**
- Approve → Approved
- Reject → Rejected

### **Reservation_Calendar.php**
- Maintenance → Under Maintenance
- Reconsider → Pending
- Delete → Remove

### **Facilities_Maintenance.php**
- Return to Service → Approved

---

## 🔔 **Notification Integration**

The notification system shows:
- **Pending facilities** count in bell icon
- **Direct links** to approval pages
- **Real-time updates** across all pages

This creates a seamless workflow where admins are immediately notified of facilities needing attention!

---

**🎉 Your facility management system now has a complete, logical workflow that handles all facility statuses efficiently!**
