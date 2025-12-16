# Adding John's Student Record

## Quick Steps:

### Option 1: Automatic Insert (Recommended)
1. Navigate to: `http://localhost/php/ms1/add_john.php`
2. The script will automatically add John's record to the database
3. You'll see a confirmation message
4. Then you can search for account "541" to see his details

### Option 2: Manual SQL Insert
If you prefer to use phpMyAdmin:

```sql
INSERT INTO students (account_number, full_name, email, phone, photo_path, hostel_name, room_number, food_preference) 
VALUES ('541', 'John', 'john@example.com', '9876543213', 'images/p1.webp', 'Sacred Heart Hostel', '5', 'veg');
```

## John's Details:
- **Account Number**: 541
- **Full Name**: John
- **Room Number**: 5
- **Hostel Name**: Sacred Heart Hostel
- **Food Preference**: Veg (Vegetarian)
- **Photo**: images/p1.webp
- **Email**: john@example.com
- **Phone**: 9876543213

## How to Search for John:
1. Go to `http://localhost/php/ms1/index.php`
2. Enter account number: `541`
3. Click Search
4. John's complete profile will be displayed with his photo from p1.webp

## File Locations:
- Photo file: `c:\xampps\htdocs\php\ms1\images\p1.webp`
- Student record insertion script: `add_john.php`

---
**Status**: Ready to search! Enter account "541" to see John's profile.
