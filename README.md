# BuddyBites
Food-Delivery Platform
# 🍔 BuddyBites - Food Delivery Platform

A full-stack PHP/MySQL web application designed specifically for university students, featuring **budget-based filtering**, **custom meal builder**, and **group ordering** functionality.

---

## ✨ Key Features

- **Budget Filtering**: Filter meals by RM5, RM10, RM15, or view all
- **Custom Meal Builder**: 4-step meal customization (base → protein → toppings → sauce)
- **Group Ordering**: Create group rooms, share carts, and split delivery fees
- **4 User Roles**: Student, Restaurant, Rider, and Admin
- **Admin Dashboard**: Manage users, restaurants, orders, and generate reports
- **Real-time Order Tracking**: Track orders from preparation to delivery

---

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache (via XAMPP)
- **Version Control**: Git

---

## 🚀 How to Run This Project

### 1. Install XAMPP

Download and install [XAMPP](https://www.apachefriends.org/). Start the **Apache** and **MySQL** services.

### 2. Clone or Download the Project

```bash
git clone https://github.com/jjy77/-BuddyBites.git
```

Or download the ZIP file and extract it.

### 3. Move the Project to XAMPP's htdocs Folder

Copy the entire project folder to:

```
C:\xampp\htdocs\BuddyBites
```

### 4. Import the Database

1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click **"New"** on the left sidebar
3. Create a database named `buddybites_db` (select `utf8_general_ci` as the collation)
4. Click the **"Import"** tab at the top
5. Click **"Choose File"** and select the `buddybites_db.sql` file from the project root folder
6. Click **"Go"** to complete the import

### 5. Configure Database Connection

Open `includes/db.php` and verify the database credentials:

```php
$host = 'localhost';
$dbname = 'buddybites_db';
$username = 'root';
$password = '';
```

> ⚠️ If you set a password for MySQL in phpMyAdmin, update the `$password` field accordingly.

### 6. Launch the Application

Open your browser and go to:

```
http://localhost/BuddyBites/login.php
```

### 7. Test Login Credentials

You can use the following test accounts to log in:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@buddybites.com` | `admin123` |
| **Student** | `student@buddybites.com` | `student123` |
| **Restaurant** | `restaurant@buddybites.com` | `restaurant123` |
| **Rider** | `rider@buddybites.com` | `rider123` |

> 💡 You can also register a new account directly from the registration page.

---

## 👥 Development Team

| Member | Role / Contributions |
| :--- | :--- |
| **Marwa Saleem Bilal Elnour** | Landing page, Registration, Login, Order History |
| **Sherie Nuradzza binti Jailuddin** | Menu listing, Search & Filter, Cart management |
| **Noor Ainul Sufiyya Noor Mushar** | Group ordering, Custom meal builder, Checkout |
| **Ng Jing Yong** | Admin dashboard, Order management, Restaurant management |

---

## 📄 License

This project was developed as a coursework project for **Multimedia University (MMU)** and is intended for educational purposes only.
