# 🌐 Speak - Multi-User Blog Platform

A powerful, multilingual blogging ecosystem built with **Symfony 7** and **Vanilla JS**. 
Designed for expression, connection, and seamless user experience.

![Project Banner](https://img.shields.io/badge/Symfony-7.2-black?logo=symfony) ![License](https://img.shields.io/badge/License-MIT-blue) ![Status](https://img.shields.io/badge/Status-Active-success)

## ✨ Key Features

### 🌍 Multilingual Support
- **Full Localization**: Seamlessly switch between **English 🇬🇧**, **Français 🇫🇷**, and **Arabic 🇲🇦** (RTL support included).
- **Localized Content**: Dates, charts, and interfaces adapt to your selected language.

### 🚀 Social & Interactive
- **Multi-User System**: Secure registration and profile management.
- **Follow System**: "Instagram-style" follow/unfollow with real-time "Inspired" & "Inspiring" counters.
- **Smart Comments**: 
    - Auto-moderation with `SensitiveWordFilter`.
    - Mention system (`@username`).

### 📊 Powerful Admin Dashboard
- **Real-Time Statistics**: Track platform growth, user engagement, and content metrics.
- **Interactive Charts**: Powered by **Chart.js** (Line, Bar, and Pie charts).
- **Deep Insights**: Visual analytics for comments status (Approved/Pending/Rejected).

### 🎨 Modern UI/UX
- **Premium Design**: Dark mode aesthetic with Glassmorphism effects.
- **Global Animations**: Smooth `AOS` (Animate On Scroll) transitions.
- **Typewriter Effects**: Dynamic text animations for a lively landing page.

---

## 🛠️ Tech Stack

- **Backend**: Symfony 7.2 (PHP 8.2+)
- **Frontend**: Twig, Vanilla JS, CSS3 (Variables & Animations)
- **Assets**: NPM (Chart.js, AOS, Bootstrap 5)
- **Database**: MySQL / MariaDB

---

## 🚀 Installation Guide

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL

### Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Only-Lord-AS/Plateforme-du-blog-Multi-Utilisateurs.git
   cd Plateforme-du-blog-Multi-Utilisateurs
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend Assets**
   ```bash
   npm install
   ```

4. **Configure Database**
   Edit `.env` to match your database credentials:
   ```env
   DATABASE_URL="mysql://root:@127.0.0.1:3306/amine?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
   ```

5. **Setup Database & Migrations**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

6. **Start the Server**
   ```bash
   symfony server:start
   ```
   Visit `http://127.0.0.1:8000`

---

## 🤝 Contribution
Contributions are welcome! Please fork the repository and submit a pull request.

---

## 📜 License
This project is licensed under the MIT License.

---
*Developed with ❤️ by A.S (Ouhaddine MED Amine)*
*Special Thanks to @aymenmarjan for the support 💪🏽*
