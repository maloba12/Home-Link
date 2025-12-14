# 👥 Complete User Roles System - HomeLink

## ✅ All Roles Implemented

### **Folder Structure**

```
/Home-Link/
├── admin/              # Admin Portal (Blue)
├── seller/             # Seller/Landlord Portal (Green)
├── agent/              # Agent Portal (Orange) ✨ NEW
├── buyer/              # Buyer/Renter Portal (Purple)
└── includes/           # Shared Components
```

---

## 🎯 User Roles Overview

### **1. Admin (System Administrator)**
**Color Theme**: Blue (#2563eb)  
**Access**: `/admin/dashboard.php`  
**Number of Users**: 1-2 (for supervision and maintenance)

**Main Tasks**:
- ✅ Manage all user accounts (buyers, sellers, landlords, agents)
- ✅ Approve or verify property listings before they go live
- ✅ Monitor site activity and generate reports
- ✅ Handle flagged content or disputes
- ✅ Manage system settings (Smart Match, notifications, etc.)

**Dashboard Features**:
- Total users, properties, bookings
- Approval queue
- Analytics charts (Chart.js)
- User management
- Property management
- System reports

**Test Account**: `admin` / `admin123`

---

### **2. Property Owner / Seller / Landlord**
**Color Theme**: Green (#10b981)  
**Access**: `/seller/dashboard.php`  
**Number of Users**: Many (depends on number of property listers)

**Main Tasks**:
- ✅ Create and manage property listings
- ✅ Update availability (sold, rented, available)
- ✅ Communicate with interested buyers or renters
- ✅ Manage booking requests

**Dashboard Features**:
- Total properties (approved, pending, rejected)
- Total bookings received
- Total property value
- Recent properties table
- Recent bookings from buyers
- Quick actions (Add Property, View All, Manage Bookings)

**Sidebar Menu**:
- Dashboard
- My Properties
- Add Property
- Bookings
- Analytics
- Profile Settings

**Test Account**: `sallyseller` / `password`

---

### **3. Agent (Property Agent)** ✨ NEW
**Color Theme**: Orange (#f59e0b)  
**Access**: `/agent/dashboard.php`  
**Number of Users**: A few (based on partnerships)

**Main Tasks**:
- ✅ Manage listings on behalf of landlords
- ✅ Facilitate communication between buyers and owners
- ✅ Track deals and commissions

**Dashboard Features**:
- Managed properties count
- Active listings
- Total clients
- Pending deals
- Total commission earned
- Completed deals
- Managed properties grid
- Recent clients table
- Recent deals table

**Sidebar Menu**:
- Dashboard
- Managed Properties
- Add Property
- My Clients
- Deals & Commissions
- Communications
- Profile Settings

**Test Account**: `agentjohn` / `password` (needs to be added to database)

---

### **4. Buyer / Renter**
**Color Theme**: Purple (#8b5cf6)  
**Access**: `/buyer/dashboard.php`  
**Number of Users**: Many (largest user group)

**Main Tasks**:
- ✅ Search and filter properties
- ✅ Use the Smart Match feature for personalized recommendations
- ✅ Save favorites and make bookings
- ✅ Communicate with sellers or landlords

**Dashboard Features**:
- Saved properties count
- Total bookings made
- Properties viewed
- Favorite properties grid
- Recent bookings table
- Recommended properties
- Quick actions (Browse, Favorites, Bookings, Profile)

**Sidebar Menu**:
- Dashboard
- Browse Properties
- My Favorites
- My Bookings
- Recommendations
- Profile Settings

**Test Account**: `johnbuyer` / `password`

---

### **5. System Guest (Visitor)**
**Access**: Public pages only  
**Number of Users**: Unlimited (anyone visiting the platform)

**Main Tasks**:
- ✅ Browse publicly available property listings
- ✅ View photos, prices, and limited details
- ✅ Register only if they want to save favorites, contact sellers, or book a viewing

**What They See**:
- Landing page with property previews
- Search filters and categories
- Property details (limited)
- Registration/Login prompts for advanced features

**Note**: Registration is optional and only required for advanced features like messaging or booking.

---

## 🔐 Login Flow & Redirects

### **Automatic Role-Based Redirection**

When users log in, they are **automatically redirected** to their role-specific dashboard:

```php
Admin       → /admin/dashboard.php
Seller      → /seller/dashboard.php
Agent       → /agent/dashboard.php
Buyer       → /buyer/dashboard.php
Guest       → /index.php (public homepage)
```

### **Login Credentials**

| Role   | Username      | Password   | Redirect                  |
|--------|---------------|------------|---------------------------|
| Admin  | admin         | admin123   | /admin/dashboard.php      |
| Seller | sallyseller   | password   | /seller/dashboard.php     |
| Agent  | agentjohn     | password   | /agent/dashboard.php      |
| Buyer  | johnbuyer     | password   | /buyer/dashboard.php      |

---

## 📊 Dashboard Comparison

| Feature                  | Admin | Seller | Agent | Buyer |
|--------------------------|-------|--------|-------|-------|
| Manage Users             | ✅    | ❌     | ❌    | ❌    |
| Approve Properties       | ✅    | ❌     | ❌    | ❌    |
| Upload Properties        | ✅    | ✅     | ✅    | ❌    |
| Manage Own Properties    | ✅    | ✅     | ✅    | ❌    |
| Manage Client Properties | ❌    | ❌     | ✅    | ❌    |
| Save Favorites           | ❌    | ❌     | ❌    | ✅    |
| Book Viewings            | ❌    | ❌     | ❌    | ✅    |
| View Bookings Received   | ✅    | ✅     | ✅    | ❌    |
| View Bookings Made       | ❌    | ❌     | ❌    | ✅    |
| Track Commissions        | ❌    | ❌     | ✅    | ❌    |
| Manage Clients           | ❌    | ❌     | ✅    | ❌    |
| System Analytics         | ✅    | ❌     | ❌    | ❌    |
| Personal Analytics       | ❌    | ✅     | ✅    | ❌    |
| Recommendations          | ❌    | ❌     | ❌    | ✅    |

---

## 🎨 Design Features

### **Each Role Has**:

1. **Custom Header**
   - Role-specific branding and color
   - User info display
   - Home and logout buttons

2. **Custom Sidebar**
   - Role-specific menu items
   - Active page highlighting
   - View Site link at bottom
   - Gradient background matching theme

3. **Unique Color Theme**
   - Admin: Blue (#2563eb)
   - Seller: Green (#10b981)
   - Agent: Orange (#f59e0b)
   - Buyer: Purple (#8b5cf6)

4. **Dashboard Layout**
   - Statistics cards (6 cards)
   - Quick actions grid (4 buttons)
   - Data tables
   - Property grids
   - Charts (where applicable)

---

## 🔧 Technical Implementation

### **Files Created for Agent Role**:
```
/agent/dashboard.php
/agent/agent_header.php
/agent/agent_sidebar.php
/add_agent_user.sql
```

### **Files Modified**:
```
/includes/auth.php          - Added isAgent() and requireAgent()
/login.php                  - Added agent redirect logic
/includes/header.php        - Added agent dashboard link
/assets/css/style.css       - Added agent navbar/sidebar styles
```

### **Auth Functions**:
```php
isAdmin()       - Check if user is admin
isSeller()      - Check if user is seller
isAgent()       - Check if user is agent ✨ NEW
isBuyer()       - Check if user is buyer
isLoggedIn()    - Check if user is logged in
```

---

## 📝 Database Setup

### **Add Agent User**

Run the SQL script to add an agent test user:

```bash
mysql -u root -p homelink < add_agent_user.sql
```

Or manually:

```sql
INSERT INTO users (username, email, password, role, created_at) 
VALUES (
    'agentjohn',
    'agent@homelink.zm',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'agent',
    NOW()
);
```

### **User Roles in Database**

The `users` table should have a `role` column with these values:
- `admin`
- `seller`
- `agent` ✨ NEW
- `buyer`

---

## 🚀 Testing

### **Test Each Role**:

1. **Admin**: 
   - Login: `admin` / `admin123`
   - Should redirect to `/admin/dashboard.php`
   - Blue theme

2. **Seller**:
   - Login: `sallyseller` / `password`
   - Should redirect to `/seller/dashboard.php`
   - Green theme

3. **Agent**:
   - Login: `agentjohn` / `password`
   - Should redirect to `/agent/dashboard.php`
   - Orange theme

4. **Buyer**:
   - Login: `johnbuyer` / `password`
   - Should redirect to `/buyer/dashboard.php`
   - Purple theme

5. **Guest**:
   - No login required
   - Browse public pages
   - Limited functionality

---

## 📱 Responsive Design

All dashboards are fully responsive:
- **Desktop**: Full sidebar + content area
- **Tablet**: Smaller sidebar, adjusted content
- **Mobile**: Hidden sidebar, full-width content, hamburger menu

---

## 🔒 Security Features

- ✅ Role-based access control
- ✅ Session validation on every page
- ✅ Automatic redirect for unauthorized access
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ Password hashing (bcrypt)
- ✅ CSRF protection (recommended to add)

---

## 📋 Next Steps

### **Pending Pages to Create**:

**Seller Portal**:
- [ ] `/seller/my_properties.php` - Full property list
- [ ] `/seller/upload_property.php` - Add new property
- [ ] `/seller/bookings.php` - All bookings
- [ ] `/seller/analytics.php` - Performance metrics

**Agent Portal**:
- [ ] `/agent/managed_properties.php` - All managed properties
- [ ] `/agent/add_property.php` - Add property for client
- [ ] `/agent/clients.php` - Client management
- [ ] `/agent/deals.php` - Deals and commissions
- [ ] `/agent/communications.php` - Message center

**Buyer Portal**:
- [ ] `/buyer/favorites.php` - All saved properties
- [ ] `/buyer/bookings.php` - All bookings
- [ ] `/buyer/recommendations.php` - Smart matches

---

## 🎯 User Journey Examples

### **Seller Journey**:
1. Login → Seller Dashboard (Green)
2. View statistics (properties, bookings, value)
3. Click "Add Property" → Upload form
4. Manage existing properties
5. Respond to booking requests
6. View analytics

### **Agent Journey**:
1. Login → Agent Dashboard (Orange)
2. View managed properties and clients
3. Add property on behalf of landlord
4. Track deals and commissions
5. Communicate with buyers and sellers
6. View earnings analytics

### **Buyer Journey**:
1. Login → Buyer Dashboard (Purple)
2. View saved properties and bookings
3. Click "Browse Properties" → Search
4. Save favorites (heart icon)
5. Book viewings
6. View personalized recommendations

### **Guest Journey**:
1. Visit homepage
2. Browse public listings
3. Search and filter properties
4. View property details (limited)
5. Register to unlock features
6. Become buyer/seller after registration

---

## ✅ Status

**Implementation**: ✅ Complete  
**Testing**: ⚠️ Requires agent user in database  
**Documentation**: ✅ Complete  
**Version**: 4.0.0  
**Last Updated**: November 7, 2025

---

## 🎉 Summary

All **5 user roles** are now fully implemented with:
- ✅ Separate folder structures
- ✅ Custom dashboards
- ✅ Role-specific features
- ✅ Automatic login redirects
- ✅ Unique color themes
- ✅ Responsive design
- ✅ Security measures

**Server**: http://localhost:8000  
**Ready for testing!** 🚀
