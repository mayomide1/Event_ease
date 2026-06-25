# EventEase - Event Ticketing and Management System

## 📌 Project Overview

EventEase is a comprehensive web-based event ticketing and management system designed specifically for campus and small-scale events. The platform enables event organizers to create, manage, and promote events while allowing attendees to discover, book, and purchase tickets seamlessly.

**Key Benefits:**
- 🎫 Simplified event creation and management
- 🔍 Easy event discovery for attendees
- 💳 Secure ticket booking and confirmation
- 📱 Fully responsive design for all devices
- 🔒 Role-based access control (Organizer, User, Admin)

---

## 🚀 Features

### For Organizers
- **Dashboard**: Real-time statistics (events, bookings, tickets sold, revenue)
- **Event Management**: Create, Read, Update, Delete events
- **Booking Management**: View and confirm bookings
- **Event Categories**: Organize events by categories
- **Ticket Tracking**: Monitor ticket sales and availability

### For Users/Attendees
- **Event Discovery**: Browse and search events
- **Category Filtering**: Filter events by category
- **Event Details**: View full event information
- **Ticket Booking**: Book tickets with quantity selection
- **My Bookings**: View all booking history
- **My Tickets**: View and download confirmed tickets

### Technical Features
- 🔐 Secure authentication with password hashing
- 🛡️ PDO prepared statements for SQL injection prevention
- 📱 Responsive design with hamburger menu
- 🖨️ Downloadable tickets with QR codes
- 🎨 Modern, clean UI with Font Awesome icons

---

## 🛠️ Technology Stack

| Technology | Purpose |
|------------|---------|
| **Frontend** | HTML5, CSS3, JavaScript, Font Awesome 6 |
| **Backend** | PHP 8.x |
| **Database** | MySQL 5.7+ (with PDO) |
| **Server** | Apache (XAMPP/WAMP/MAMP) |

---

## 📋 Prerequisites

- XAMPP (or WAMP/MAMP) installed
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge, Safari)

---

## 📥 Installation Guide

### Step 1: Download/Clone the Project
```bash
git clone https://github.com/yourusername/eventease.git
```
Or download the ZIP file and extract it.

### Step 2: Copy to Web Server Root
Copy the `eventease` folder to your web server's document root:

- **XAMPP**: `C:\xampp\htdocs\eventease\`
- **WAMP**: `C:\wamp64\www\eventease\`
- **MAMP**: `/Applications/MAMP/htdocs/eventease/`

### Step 3: Start Web Server
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services

### Step 4: Create Database
1. Open `http://localhost/phpmyadmin`
2. Click **New** and create database: `eventease_db`
3. Choose **utf8_general_ci** as collation

### Step 5: Import Database
1. Select `eventease_db` in phpMyAdmin
2. Click **Import** tab
3. Choose `eventease_db.sql` from the project folder
4. Click **Go**

### Step 6: Configure Database Connection
The database configuration is in `config/database.php`. Default settings:
```php
$host = 'localhost';
$dbname = 'eventease_db';
$username = 'root';
$password = '';
```

### Step 7: Access the Application
Open your browser and navigate to:
```
http://localhost/eventease/public/
```

## 📁 Project Structure

```
eventease/
├── config/
│   └── database.php          # Database configuration
├── public/                   # Public directory (Document Root)
│   ├── assets/
│   │   ├── css/              # Stylesheets
│   │   ├── images/           # Images and event banners
│   ├── index.php             # Homepage
│   ├── login.php             # Login page
│   ├── signup.php            # Registration page
│   ├── dashboard.php         # Organizer dashboard
│   ├── events.php            # Public events page
│   ├── event-details.php     # Event details page
│   ├── my-events.php         # Organizer's events
│   ├── create-event.php      # Create event page
│   ├── edit-event.php        # Edit event page
│   ├── delete-event.php      # Delete event script
│   ├── bookings.php          # Organizer bookings
│   ├── my-bookings.php       # User bookings
│   ├── my-tickets.php        # User tickets
│   ├── book-ticket.php       # Booking script
│   ├── confirm-booking.php   # Confirm booking script
│   ├── delete-booking.php    # Delete booking script
│   ├── payment.php           # Payment page
│   ├── booking-confirmation.php # Confirmation page
│   ├── download-ticket.php   # Ticket download
│   ├── profile.php           # User profile
│   ├── change-password.php   # Password change
│   ├── logout.php            # Logout
│   ├── navbar.php            # Navigation component
│   ├── sidebar.php           # Sidebar component
│   ├── footer.php            # Footer component
└── eventease_db.sql          # Database installation script
```

---

## 👥 User Roles

### Organizer
- Create, edit, and delete events
- View event analytics (bookings, tickets sold, revenue)
- Manage bookings for their events
- Confirm or cancel bookings

### User/Attendee
- Browse and search events
- Book tickets for events
- View booking history
- Download and print tickets

### Admin
- Full system access (future enhancement)
- Manage users and events
- Platform-wide analytics

---

## 🎫 Ticket Generation

EventEase generates digital tickets with QR codes for easy entry verification:

- **QR Code**: Each ticket has a unique QR code containing the booking reference
- **Print-Friendly**: Tickets can be printed directly from the browser
- **Downloadable**: Save tickets as PDF for offline access

---

## 🔒 Security Features

| Feature | Implementation |
|---------|----------------|
| Password Protection | `password_hash()` and `password_verify()` |
| SQL Injection | PDO prepared statements |
| XSS Prevention | `htmlspecialchars()` for all output |
| Session Security | `session_start()` and role-based access |
| File Upload | File type validation and unique naming |

---

## 🧪 Testing

### Test Accounts
Use the default credentials above to test the system.

### Test Flow
1. **Login as Organizer** → Create an event → View it on My Events
2. **Login as User** → Browse events → Book tickets → Confirm booking
3. **Check My Tickets** → Download ticket → View QR code
4. **Login as Organizer** → View bookings → Confirm pending bookings

---

## 📱 Mobile Responsiveness

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

The navigation menu automatically converts to a hamburger menu on mobile devices.

---

## 🛠️ Troubleshooting

### Common Issues and Solutions

| Issue | Solution |
|-------|----------|
| **"Database connection failed"** | Check MySQL is running; verify credentials in `config/database.php` |
| **"404 Not Found"** | Ensure files are in `public/` directory; check `.htaccess` file |
| **"Cannot login"** | Use correct credentials; check password hash in database |
| **"Events not showing"** | Ensure event status is "published"; check date filters |
| **"Image upload fails"** | Check permissions on `assets/images/events/` folder |

### Enable Apache mod_rewrite
1. Open XAMPP Control Panel → Apache → Config → httpd.conf
2. Find `LoadModule rewrite_module modules/mod_rewrite.so` and uncomment it
3. Find `AllowOverride` and set to `All`
4. Restart Apache

---

## 📝 Future Enhancements

- [ ] Payment gateway integration (Paystack, Flutterwave)
- [ ] Email notification system
- [ ] Advanced search with filters
- [ ] Event calendar view
- [ ] Social media integration
- [ ] Mobile application (iOS/Android)
- [ ] Analytics dashboard
- [ ] Multi-language support

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 📞 Contact

**Developer**: [Mamukuyomi Ayomide Emmanuel]  
**Email**: [ayomidemamukuyomi5@gmail.com]  
**Project Link**: [https://github.com/mayomide1/Event_ease/]

---

## 🙏 Acknowledgments

- Font Awesome for icons
- PHP.net for documentation
- MySQL for database
- All contributors and testers


**© 2026 EventEase. All Rights Reserved.**