# HomeLink - Smart Housing and Property Connection Platform

## Overview
HomeLink is a comprehensive web-based platform that connects renters, buyers, and sellers in an interactive system. It features a smart recommendation engine that suggests properties based on user preferences and search history.

## Features
✅ User Authentication (Registration, Login, Role-based access)  
✅ Property Listings (Upload, Browse, Search)  
✅ Smart Recommendation System (JavaScript-based matching)  
✅ Advanced Search & Filtering  
✅ Booking System for property viewings  
✅ Favorites/Wishlist functionality  
✅ Admin Dashboard for property management  
✅ Responsive Modern UI  

## Technologies Used
- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Libraries**: Font Awesome icons

## Installation

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   cd homelink
   ```

2. **Create the database**
   - Open phpMyAdmin or MySQL command line
   - Import `sql/homelink.sql` to create the database and sample data
   ```bash
   mysql -u root -p < sql/homelink.sql
   ```

3. **Configure database connection**
   - Edit `includes/db_connect.php`
   - Update credentials if needed:
   ```php
   $host = 'localhost';
   $dbname = 'homelink';
   $username = 'root';
   $password = '';
   ```

4. **Set up web server**
   - Point document root to the project directory
   - Ensure mod_rewrite is enabled (for Apache)
   - Set proper file permissions:
   ```bash
   chmod 755 assets/uploads
   ```

5. **Access the application**
   - Open browser: `http://localhost/homelink`
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`

## Project Structure

```
homelink/
├── index.php                 # Home page with property listings
├── login.php                 # User login
├── register.php              # User registration
├── property_details.php      # Property detail page
├── upload_property.php       # Upload new property (sellers)
├── profile.php               # User profile and favorites
├── search.php                # Advanced search page
├── logout.php                # Logout handler
│
├── /includes                 # PHP includes
│   ├── db_connect.php        # Database connection
│   ├── auth.php             # Authentication functions
│   ├── header.php           # Site header
│   └── footer.php           # Site footer
│
├── /admin                    # Admin panel
│   ├── dashboard.php        # Admin dashboard
│   ├── manage_properties.php # Property management
│   ├── manage_users.php     # User management
│   └── analytics.php        # Analytics page
│
├── /assets
│   ├── /css
│   │   └── style.css        # Main stylesheet
│   ├── /js
│   │   ├── main.js         # Main JavaScript
│   │   ├── search.js       # Search functionality
│   │   └── recommend.js    # Smart recommendations
│   ├── /images             # Property images
│   └── /uploads             # Uploaded property images
│
├── /api                     # API endpoints
│   ├── toggle_favorite.php  # Favorite toggle
│   ├── submit_booking.php   # Booking submission
│   └── get_properties.php   # Get properties (AJAX)
│
└── /sql
    └── homelink.sql        # Database schema
```

## Usage

### For Buyers/Renters
1. Register an account (select "Buy/Rent Properties")
2. Browse properties on the home page
3. Use search filters to find specific properties
4. Save favorite properties
5. Book viewing appointments
6. View booking history in profile

### For Sellers
1. Register an account (select "Sell/Rent Properties")
2. Login and go to "Upload Property"
3. Fill in property details
4. Upload images
5. Wait for admin approval
6. Manage inquiries and bookings

### For Admins
1. Login with admin credentials
2. Access admin dashboard
3. Approve/reject property listings
4. Manage users
5. View analytics and statistics

## Smart Recommendation System

The Smart Match feature uses JavaScript to analyze:
- Previous search queries
- Price range preferences
- Location preferences
- Property type preferences
- Search frequency patterns

Recommendations are displayed on the home page for logged-in users based on their search history.

## Database Schema

### Tables
- **users**: User accounts and profiles
- **properties**: Property listings
- **images**: Property images
- **amenities**: Property amenities
- **favorites**: User saved properties
- **bookings**: Viewing appointments
- **searches**: Search history for recommendations

### Relationships
- One seller can have many properties
- One property can have many images
- Many users can favorite many properties
- One buyer can have many bookings

## Security Features
- Password hashing (bcrypt)
- SQL injection prevention (PDO prepared statements)
- XSS protection (input sanitization)
- Session management
- Role-based access control
- File upload validation

## Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Contributing
This is a project demonstration. Feel free to extend with:
- Email notifications
- Payment integration
- Advanced filters
- Map integration
- Chat system
- Mobile app

## License
This project is created for educational purposes.

## Credits
Developed as a comprehensive web application demonstration featuring modern PHP, MySQL, HTML/CSS/JavaScript with smart recommendation algorithms.

---

For support or questions, contact: [Your Contact]

**Version**: 1.0.0  
**Last Updated**: 2024

# Home-Link
