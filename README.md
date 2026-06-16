# Gourmet Bites — QR Code Digital Menu System

A PHP-based restaurant digital menu system where customers scan a QR code at their table, browse the menu, and place orders directly from their phone. The admin panel lets staff manage the menu, track orders, and view analytics.

---

## Features

- **QR Table System** — Each table gets a unique QR code. Scanning it loads the menu pre-loaded with the table identity.
- **Customer Menu** — Browse items by category, search, view item details, add to cart.
- **Cart & Checkout** — Persistent cart (localStorage), checkout with name entry and table ID lookup.
- **Payment Flow** — Pay Now (bank transfer with 30-min countdown) or Pay on Receipt (cash).
- **Order Tracking** — Customers track their order status via a tracking ID (`ORD-XXXXXXXX`).
- **Admin Panel** — Manage menu items, categories, tables, orders, settings, and analytics.
- **Dark Mode** — Toggle on desktop.

---

## Requirements

- PHP 8.0+
- MySQL / MariaDB 10.4+
- Apache with `mod_rewrite` enabled (XAMPP works out of the box)

---

## Local Setup (XAMPP)

### 1. Copy project files

Place the project folder inside your XAMPP `htdocs` directory:

```
C:/xampp/htdocs/php-menu/        (Windows)
/opt/lampp/htdocs/php-menu/      (Linux)
```

### 2. Create the database

1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **Import**
3. Choose the file: `database/restaurant_menu.sql`
4. Click **Go**

This creates all tables and seeds the default settings.

### 3. Configure the database connection

Open `config/db.php` and set your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // empty by default on XAMPP
define('DB_NAME', 'restaurant_menu');
```

### 4. Start the app

1. Start **Apache** and **MySQL** in the XAMPP Control Panel
2. Visit: `http://localhost/php-menu/`

---

## Admin Panel

Go to: `http://localhost/php-menu/admin/login.php`

| Username | Password  |
|----------|-----------|
| admin    | admin123  |

> **Change your password** after first login via Settings.

---

## Project Structure

```
php-menu/
├── admin/              # Admin panel (dashboard, orders, menu, QR, analytics)
├── config/
│   └── db.php          # Database connection & helpers
├── css/
│   └── style.css       # Custom styles (cart bubble, dark mode, etc.)
├── database/
│   └── restaurant_menu_update.sql  # DB schema + seed data
├── images/             # Menu item and category images
├── js/
│   └── app.js          # Cart, table session, category filter, dark mode
├── pages/              # Customer-facing pages
│   ├── checkout.php
│   ├── item.php
│   ├── order.php       # JSON API for order creation
│   ├── payment.php
│   └── track.php
└── index.php           # Main menu page
```

---

## Production Deployment (InfinityFree / cPanel)

1. Upload all files via FTP or File Manager.
2. Import `database/restaurant_menu_update.sql` through phpMyAdmin.
3. Update `config/db.php` with your hosting database credentials.
4. Ensure your `.htaccess` does **not** redirect `.php` requests to `index.html`.

---

## License

Private project — Gourmet Bites Restaurant.
