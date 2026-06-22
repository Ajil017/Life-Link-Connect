# LifeLink Connect - Blood Donation Management System

![LifeLink Connect](https://img.shields.io/badge/LifeLink-Connect-red?style=for-the-badge&logo=heartbeat)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/Tailwind-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)

A comprehensive blood donation management platform that connects donors, hospitals, and recipients in a unified ecosystem. LifeLink Connect streamlines the blood donation process, making it easier to save lives through efficient blood management and emergency response systems.

## 🌟 Features

### Multi-Role Dashboard System
- **Admin Dashboard**: Oversee all operations, manage users, and view system-wide analytics
- **Hospital Portal**: Manage blood inventory, respond to requests, and organize donation camps
- **Donor Dashboard**: Schedule donations, track contribution history, and earn reward points
- **Receiver Dashboard**: Find available blood units and submit emergency requests
- **Ambulance Dashboard**: Locate and dispatch nearest available ambulances for emergencies

### Core Functionality
- **Blood Inventory Management**: Real-time tracking of blood units across partner hospitals
- **Donation Scheduling**: Book donation appointments at preferred hospitals
- **Emergency Blood Requests**: Priority system for urgent blood requirements
- **Blood Compatibility Checker**: Comprehensive compatibility chart for all blood types
- **Ambulance Tracking**: Quick dispatch system for emergency medical transport
- **Leaderboard System**: Gamification with reward points for active donors
- **Payment Integration**: Support for both free and paid donations
- **User Profiles**: Comprehensive profile management for all user types

### Additional Features
- Secure password hashing with PHP's built-in functions
- Session-based authentication system
- Responsive design with Tailwind CSS
- Blood type compatibility reference
- Real-time status updates for requests and donations

## 🛠️ Tech Stack

### Backend
- **PHP 8.0+**: Server-side logic and API endpoints
- **MySQL 8.0+**: Relational database management

### Frontend
- **HTML5**: Semantic markup
- **Tailwind CSS**: Utility-first CSS framework via CDN
- **Font Awesome 6.4.2**: Icon library
- **Google Fonts (Inter)**: Typography

### Development Tools
- **XAMPP/WAMP**: Local development server environment
- **phpMyAdmin**: Database management interface

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- PHP 8.0 or higher
- MySQL 8.0 or higher
- XAMPP, WAMP, or similar PHP development environment
- A web browser (Chrome, Firefox, Safari, or Edge)
- Git (for cloning the repository)

## 🚀 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/Ajil017/Life-Link-Connect.git
cd Life-Link-Connect
```

### Step 2: Set Up the Database

1. Start your XAMPP/WAMP server (Apache and MySQL)
2. Open phpMyAdmin at `http://localhost/phpmyadmin`
3. Create a new database named `blood_donation_db`
4. Import the database schema:
   - Click on the `blood_donation_db` database
   - Go to the "Import" tab
   - Select the `databaseschema.sql` file from the project directory
   - Click "Go" to import

### Step 3: Configure Database Connection

1. Open the `db_connect.php` file
2. Update the database credentials if needed:

```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');        // Your MySQL username
define('DB_PASSWORD', 'your_password'); // Your MySQL password
define('DB_NAME', 'blood_donation_db');
```

**Note**: If you're using XAMPP with default settings, the username is typically `root` and the password is empty.

### Step 4: Deploy to Local Server

1. Place the project folder in your XAMPP htdocs directory:
   - Windows: `C:\xampp\htdocs\Life-Link-Connect`
   - Mac/Linux: `/Applications/XAMPP/htdocs/Life-Link-Connect`

2. Start Apache and MySQL services in XAMPP

3. Access the application in your browser:
   ```
   http://localhost/Life-Link-Connect
   ```

### Step 5: Load Sample Data (Optional)

To populate the database with sample data for testing:

1. Open phpMyAdmin
2. Select the `blood_donation_db` database
3. Go to the "Import" tab
4. Select the `sampledata.sql` file
5. Click "Go" to import

## 📖 Usage Guide

### Registration

1. Navigate to the homepage
2. Click "Login / Register"
3. Choose your user type:
   - **Donor**: Register to donate blood
   - **Receiver**: Register to request blood
   - **Hospital Staff**: Register on behalf of a hospital
   - **Ambulance Driver**: Register to provide emergency transport

### Dashboard Access

After registration, you'll be automatically redirected to your respective dashboard based on your user type.

### Finding Blood

1. Log in as a receiver or donor
2. Use the "Find Blood" feature to search for available blood units
3. Filter by blood group and location
4. Contact hospitals directly or submit a request

### Donating Blood

1. Log in as a donor
2. Schedule a donation at a preferred hospital
3. Complete the donation process
4. Earn reward points for your contribution

### Emergency Requests

1. Submit emergency blood requests with priority status
2. Hospitals receive immediate notifications
3. Track request status in real-time

## 🏥 Database Schema

The system uses the following main tables:

- **users**: Stores all user information including donors, receivers, admins, hospital staff, and ambulance drivers
- **hospitals**: Hospital profiles and contact information
- **blood_units**: Blood inventory tracking for each hospital
- **blood_requests**: Blood request records with status tracking
- **donations**: Donation history and scheduling
- **payments**: Payment transaction records
- **ambulances**: Ambulance fleet management

## 👥 Team

- **Ajil P R** - Project Lead
- **DeviKrishna** - Project Assistant
- **Abhinav Manakkal** - Project Assistant

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- Blood donation organizations worldwide for their life-saving work
- Healthcare professionals who make blood donation programs possible
- Open-source community for the tools and libraries used

## 📞 Support

For support, email lifelinkconnect@example.com or open an issue in the GitHub repository.

## 🔒 Security Notes

- Passwords are hashed using PHP's built-in password hashing functions
- Session management is implemented for secure authentication
- SQL injection prevention using prepared statements
- Input validation and sanitization on all forms

**Important**: Remember to:
- Change default database credentials in production
- Use HTTPS in production environments
- Implement additional security measures for production deployment
- Regularly update dependencies

---

**LifeLink Connect** - Saving lives, one drop at a time. ❤️
