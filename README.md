# Hostel Mess Management System - Home & Search Module

## Module Overview
This module provides a user-friendly home screen with a search bar that allows users to search for students by their account number and view their complete mess-related details including photo, personal information, hostel details, and food preferences.

## Features
✅ **Search Functionality** - Search students by account number  
✅ **Student Profile Display** - View photo and all relevant student details  
✅ **Responsive Design** - Works seamlessly on desktop and mobile devices  
✅ **Data Security** - SQL injection prevention with prepared statements  
✅ **Beautiful UI** - Modern gradient design with smooth animations  

## File Structure
```
ms1/
├── index.html           (Main HTML page)
├── styles.css           (CSS styling)
├── script.js            (JavaScript functionality)
├── search.php           (Backend search logic)
├── config.php           (Database configuration)
├── database.sql         (Database schema)
├── README.md            (This file)
└── assets/
    └── images/
        └── default-profile.png (Default profile picture)
```

## Setup Instructions

### 1. Database Setup
- Open phpMyAdmin (http://localhost/phpmyadmin)
- Create a new database named `hostel_mess`
- Import the `database.sql` file or run the SQL commands directly

**SQL to create database and table:**
```sql
CREATE DATABASE IF NOT EXISTS hostel_mess;
USE hostel_mess;

CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_number VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    photo_path VARCHAR(255),
    hostel_name VARCHAR(100) NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    food_preference ENUM('veg', 'non-veg') NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

CREATE INDEX idx_account_number ON students(account_number);
```

### 2. Insert Sample Data
```sql
INSERT INTO students (account_number, full_name, email, phone, photo_path, hostel_name, room_number, food_preference) 
VALUES 
('ACC001', 'Rajesh Kumar', 'rajesh@example.com', '9876543210', 'assets/images/default-profile.png', 'Hostel A', 'A-101', 'veg'),
('ACC002', 'Priya Singh', 'priya@example.com', '9876543211', 'assets/images/default-profile.png', 'Hostel B', 'B-205', 'non-veg'),
('ACC003', 'Amit Patel', 'amit@example.com', '9876543212', 'assets/images/default-profile.png', 'Hostel C', 'C-312', 'veg');
```

### 3. File Placement
- Place all files in your XAMPP htdocs directory: `C:\xampp\htdocs\php\ms1\`
- Create the `assets/images/` folder structure

### 4. Configuration
- The `config.php` file contains database connection settings
- Default credentials:
  - Host: `localhost`
  - User: `root`
  - Password: `` (empty)
  - Database: `hostel_mess`
- Update these if your XAMPP configuration differs

### 5. Access the Module
- Open your browser and navigate to: `http://localhost/php/ms1/index.html`
- Enter a student account number (e.g., ACC001, ACC002, ACC003)
- Click "Search" to view student details

## How to Use

1. **Search for a Student:**
   - Enter the student's account number in the search bar
   - Click the "Search" button
   - The system will display the student's photo and all details

2. **View Student Information:**
   - Student's full name
   - Account number
   - Email and phone number
   - Hostel name and room number
   - Food preference (veg/non-veg)
   - Registration date

3. **Action Buttons:**
   - **View Meal History** - Future module to track meal usage
   - **View Bill Status** - Future module to check billing information
   - **New Search** - Clear the search and start a new search

## Customization

### Styling
- Modify `styles.css` to change colors, fonts, or layout
- Current color scheme: Purple gradient (#667eea to #764ba2)

### Adding More Student Fields
1. Add columns to the `students` table in database
2. Update the PHP search query to include new fields
3. Add new HTML elements in the student-details card
4. Update JavaScript to populate the new fields

### Profile Pictures
- Upload student photos to `assets/images/` folder
- Update the `photo_path` in the database with the image filename
- Default image will be used if photo_path is invalid

## Technologies Used
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 7+
- **Database:** MySQL/MariaDB
- **Server:** Apache (XAMPP)

## Browser Compatibility
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Future Enhancements
- Student photo upload functionality
- Advanced search filters (by hostel, food preference, etc.)
- Batch import of student data (CSV/Excel)
- QR code scanning for quick access
- Integration with ID card system

## Troubleshooting

### Database Connection Error
- Check if MySQL service is running
- Verify credentials in `config.php`
- Ensure database and table are created

### Student Not Found
- Verify the account number exists in the database
- Check that student status is set to 'active'
- Ensure account number matches exactly (case-sensitive)

### Photo Not Displaying
- Check if image file exists in `assets/images/` folder
- Verify the photo_path in database is correct
- Ensure proper file permissions

### XAMPP Issues
- Start Apache and MySQL services
- Check XAMPP Control Panel
- Ensure port 80 is available

## Support
For issues or feature requests, please contact the development team.

---
**Version:** 1.0  
**Last Updated:** December 2025  
**Module:** Home & Search
