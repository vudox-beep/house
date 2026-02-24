# House for Rent System (Africa + Europe Ready)

A modern, scalable House for Rent Web System built with PHP and MySQL.

## Features

- **Public Listing:** Search properties by location, type, price.
- **Dealer Dashboard:** Manage properties, subscription, and profile.
- **Admin Dashboard:** View users, properties, and reports.
- **Subscription System:** Mock Lenco payment integration (K20/month).
- **Responsive Design:** Works on mobile and desktop.
- **Secure Authentication:** User and Dealer login/registration.

## Installation

1.  **Requirements:**
    - XAMPP or any PHP/MySQL server.
    - PHP 7.4 or higher.

2.  **Setup:**
    - Place the project folder in `htdocs` (e.g., `C:\xampp\htdocs\house`).
    - Create a database named `house_rent_db` in phpMyAdmin.
    - Import the `database.sql` file into the database.
    - **OR** run `http://localhost/house/install.php` to automatically set up the database.

3.  **Configuration:**
    - Edit `config/config.php` if your database credentials differ from default (`root`, empty password).

4.  **Usage:**
    - Visit `http://localhost/house/` to see the public site.
    - Register as a **Dealer** to post properties.
    - Login as **Admin** (you need to manually set `role='admin'` in the `users` table for an account) to access the Admin Panel.

## Directory Structure

- `assets/`: CSS, JS, Images
- `config/`: Database configuration
- `controllers/`: Logic (mostly inline or helper functions)
- `includes/`: Auth checks and helpers
- `models/`: Database models (User, Property)
- `views/`: View templates (mostly inline for simplicity)
- `dealer/`: Dealer dashboard files
- `admin/`: Admin dashboard files
- `uploads/`: Property images

## Mock Payment

To simulate a subscription payment:
1. Register as a Dealer.
2. Go to Dashboard -> Subscribe.
3. Click "Pay Now". It will simulate a successful transaction.

## License

Open Source.
