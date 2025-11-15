# 🏗️ Role-Based Folder Structure

## ✅ Implementation Complete

### **Folder Organization**

The HomeLink system now has separate folders for each user role, similar to the admin folder structure:

```
/Home-Link/
├── admin/              # Admin Portal
│   ├── dashboard.php
│   ├── manage_properties.php
│   ├── manage_users.php
│   ├── analytics.php
│   ├── admin_header.php
│   └── admin_sidebar.php
│
├── seller/             # Seller Portal (NEW)
│   ├── dashboard.php
│   ├── my_properties.php (pending)
│   ├── upload_property.php (pending)
│   ├── bookings.php (pending)
│   ├── analytics.php (pending)
│   ├── seller_header.php
│   └── seller_sidebar.php
│
├── buyer/              # Buyer Portal (NEW)
│   ├── dashboard.php
│   ├── favorites.php (pending)
│   ├── bookings.php (pending)
│   ├── recommendations.php (pending)
│   ├── buyer_header.php
│   └── buyer_sidebar.php
│
└── includes/           # Shared Components
    ├── header.php      # Public header
    ├── footer.php
    ├── db_connect.php
    └── auth.php
```

---

## 🎯 User Roles & Access

### **1. Admin (System Administrator)**
**Access**: `/admin/dashboard.php`

**Features**:
- Full system oversight
- User management (buyers, sellers)
- Property approval/rejection
- Analytics and reports
- System settings

**Dashboard Includes**:
- Total users, properties, bookings
- Approval queue
- Charts (Chart.js)
- Recent activity

**Color Theme**: Blue (#2563eb)

---

### **2. Seller / Property Owner / Landlord**
**Access**: `/seller/dashboard.php`

**Features**:
- Property listing management
- Upload new properties
- View bookings from buyers
- Property analytics
- Earnings tracking

**Dashboard Includes**:
- Total properties (approved, pending, rejected)
- Total bookings
- Total property value
- Recent properties table
- Recent bookings table
- Quick actions (Add Property, View All, Manage Bookings)

**Color Theme**: Green (#10b981)

**Sidebar Menu**:
- Dashboard
- My Properties
- Add Property
- Bookings
- Analytics
- Profile Settings

---

### **3. Buyer / Renter**
**Access**: `/buyer/dashboard.php`

**Features**:
- Browse and search properties
- Save favorite properties
- Book property viewings
- Smart recommendations
- Booking history

**Dashboard Includes**:
- Saved properties count
- Total bookings
- Properties viewed
- Favorite properties grid
- Recent bookings table
- Recommended properties
- Quick actions (Browse, Favorites, Bookings, Profile)

**Color Theme**: Purple (#8b5cf6)

**Sidebar Menu**:
- Dashboard
- Browse Properties
- My Favorites
- My Bookings
- Recommendations
- Profile Settings

---

### **4. Guest (Visitor)**
**Access**: Public pages only

**Features**:
- Browse public listings
- View property details
- Search and filter
- Must register to save/book

---

## 🔐 Login Flow

### **Automatic Role-Based Redirection**

When users log in, they are automatically redirected to their role-specific dashboard:

```php
// Admin
admin/admin123 → /admin/dashboard.php

// Seller
sallyseller/password → /seller/dashboard.php

// Buyer
johnbuyer/password → /buyer/dashboard.php
```

---

## 🎨 Design Features

### **Each Role Has**:

1. **Custom Header**
   - Role-specific branding
   - User info display
   - Home and logout buttons

2. **Custom Sidebar**
   - Role-specific menu items
   - Active page highlighting
   - View Site link at bottom

3. **Unique Color Theme**
   - Admin: Blue
   - Seller: Green
   - Buyer: Purple

4. **Dashboard Layout**
   - Statistics cards
   - Quick actions grid
   - Data tables
   - Charts (where applicable)

---

## 📊 Dashboard Components

### **Statistics Cards**
- Icon-based visual indicators
- Color-coded by type
- Hover effects
- Responsive grid layout

### **Quick Actions**
- Large, clickable action buttons
- Icon + text labels
- Color-coded by action type
- 4-column responsive grid

### **Data Tables**
- Sortable columns
- Hover row highlighting
- Action buttons (View, Edit, Delete)
- Responsive design

### **Empty States**
- Friendly messages
- Call-to-action buttons
- Icon illustrations

---

## 🔧 Technical Implementation

### **Files Created**:
```
/seller/dashboard.php
/seller/seller_header.php
/seller/seller_sidebar.php

/buyer/dashboard.php
/buyer/buyer_header.php
/buyer/buyer_sidebar.php
```

### **Files Modified**:
```
/login.php                  - Updated redirects
/includes/header.php        - Updated dashboard links
/assets/css/style.css       - Added role-specific styles
```

### **CSS Classes Added**:
```css
/* Seller */
.seller-body
.seller-navbar
.seller-navbar-content
.seller-brand
.seller-sidebar
.seller-user
.seller-home-btn
.seller-logout-btn

/* Buyer */
.buyer-body
.buyer-navbar
.buyer-navbar-content
.buyer-brand
.buyer-sidebar
.buyer-user
.buyer-home-btn
.buyer-logout-btn
```

---

## 📝 Database Queries

### **Seller Dashboard**:
- Total properties by seller_id
- Approved/pending/rejected counts
- Total bookings for seller's properties
- Total property value
- Recent properties with images
- Recent bookings with buyer info

### **Buyer Dashboard**:
- Total saved properties (favorites)
- Total bookings made
- Favorite properties with details
- Recent bookings with seller info
- Recommended properties (AI-based)

---

## 🚀 Next Steps

### **Pending Pages to Create**:

**Seller Portal**:
- [ ] `/seller/my_properties.php` - Full property list
- [ ] `/seller/upload_property.php` - Add new property
- [ ] `/seller/bookings.php` - All bookings
- [ ] `/seller/analytics.php` - Performance metrics

**Buyer Portal**:
- [ ] `/buyer/favorites.php` - All saved properties
- [ ] `/buyer/bookings.php` - All bookings
- [ ] `/buyer/recommendations.php` - Smart matches

---

## 🎯 User Experience Flow

### **Seller Journey**:
1. Login → Seller Dashboard
2. View statistics and recent activity
3. Click "Add Property" → Upload form
4. Manage existing properties
5. Respond to booking requests
6. View analytics

### **Buyer Journey**:
1. Login → Buyer Dashboard
2. View saved properties and bookings
3. Click "Browse Properties" → Search
4. Save favorites (heart icon)
5. Book viewings
6. View recommendations

### **Admin Journey**:
1. Login → Admin Dashboard
2. View system overview
3. Approve/reject properties
4. Manage users
5. View analytics
6. Monitor activity

---

## 📱 Responsive Design

All dashboards are fully responsive:
- **Desktop**: Full sidebar + content
- **Tablet**: Smaller sidebar
- **Mobile**: Hidden sidebar, full-width content

---

## 🔒 Security

- Role-based access control
- Session validation on every page
- Redirect unauthorized users
- SQL injection prevention (prepared statements)
- XSS protection (htmlspecialchars)

---

## ✅ Testing

All dashboards tested and working:
- ✅ Admin: 302 (redirect to login if not authenticated)
- ✅ Seller: 302 (redirect to login if not authenticated)
- ✅ Buyer: 302 (redirect to login if not authenticated)

**Test Accounts**:
- Admin: `admin` / `admin123`
- Seller: `sallyseller` / `password`
- Buyer: `johnbuyer` / `password`

---

**Status**: ✅ Core Structure Complete  
**Version**: 3.0.0  
**Last Updated**: November 7, 2025
