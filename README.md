
# 🚖 Vendor Cab and Driver Onboarding & Hierarchy Management System

## 📌 Project Overview

This is a web-based application built in **PHP and MySQL** that allows:

- 📥 Onboarding of **vendors**, **drivers**, and **vehicles**
- 🔁 Vendor **hierarchical management** (Super Vendor → Regional → City → Local)
- 🧾 **Driver document** upload and expiry tracking
- 🚗 **Vehicle management** with add/view
- 🔐 **Role-based access control** and permissions

---

## 🏗️ Features Implemented

- ✅ Vendor signup and login system with roles (`super`, `regional`, `city`, `local`)
- ✅ Hierarchical vendor relationship through `parent_id`
- ✅ Admin dashboard showing total drivers, vehicles, vendors, expired documents
- ✅ Driver module with:
  - Add driver
  - View driver list
  - Upload driver documents
- ✅ Fleet module:
  - Add vehicle
  - View vehicle list
- ✅ Delegation permissions:
  - Can add vehicles or upload driver docs only if permitted
- ✅ Expired document warning and tracking
- ✅ Secure authentication using password hashing

---

## 🗂️ Folder Structure

```
vendor-management/
│
├── auth/                  # Signup, Login, Logout
├── config/                # Database configuration
├── dashboard/             # Dashboard views
├── drivers/               # Driver CRUD + doc upload
├── fleet/                 # Vehicle management
├── public/                # Landing dashboard page
├── utils/                 # Authentication check
├── uploads/               # Uploaded document storage
├── sql/                   # Database schema (vendor_management_schema.sql)
└── README.md              # Project instructions
```

---

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Backend**: PHP 8+
- **Database**: MySQL
- **Security**: Password hashing, session-based auth

---

## 🔐 Roles and Permissions

| Role       | Can Add Vendor | Can Add Vehicle | Upload Docs | View Dashboard |
|------------|----------------|-----------------|-------------|----------------|
| Super      | ✅              | ✅               | ✅           | ✅              |
| Regional   | ❌              | ✅ *(if delegated)* | ✅ *(if delegated)* | ✅          |
| City/Local | ❌              | ✅ *(if delegated)* | ✅ *(if delegated)* | ✅          |

Delegations are stored in the `delegations` table.

---

## 💾 How to Setup

1. **Clone the repo** or download the zip
2. Create a MySQL database:  
   ```
   CREATE DATABASE vendor_management;
   ```
3. Import the provided schema:
   ```bash
   mysql -u root -p vendor_management < sql/vendor_management_schema.sql
   ```
4. Update database credentials in `config/db.php`
5. Run using XAMPP/WAMP server
6. Visit `http://localhost/vendor-management/public/dashboard.php` to open the project

---

## 🔑 Default Roles

- While signing up, you can select roles:
  - `super`
  - `regional`
  - `city`
  - `local`

Only **super** can view full dashboard and manage all vendors.

Demo Video link: https://drive.google.com/file/d/1JpteqMYoh_H1vRegDtr7yQnrcIbnhDLm/view?usp=drive_link


## 🙋‍♂️ Author

Built by Gunika as part of the **case study: Vendor Cab & Driver Onboarding with Hierarchy System**
