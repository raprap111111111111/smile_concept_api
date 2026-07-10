cat > README.md << 'EOF'
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Smile Concept API

Dental Practice Management System (DPMS) — Backend  
**Smile Concept – Multi-Branch Dental Clinic System**  
Version: 1.0 (July 2026)

## 🚀 Tech Stack
- **Framework:** Laravel 12
- **Auth:** Laravel Sanctum
- **RBAC:** Spatie Laravel Permission
- **Logging:** Spatie Activity Log
- **Database:** MySQL 8

## 📋 Overview

A full-featured dental clinic management system supporting multiple branches, role-based access, electronic health records, scheduling, billing, inventory, and patient self-service.

### Core Objectives:
- Eliminate double-bookings across branches
- Digitize patient records and dental charts
- Automate reminders and recalls to reduce no-shows
- Streamline billing and payments (including installments)
- Ensure legal compliance (clinical notes, consents, audit trail)

## 👥 User Roles & Permissions

| Role | Key Permissions |
|------|-----------------|
| **Patient** | View own appointments, treatment plans, invoices |
| **Receptionist/Staff** | Manage appointments, check-in, process payments |
| **Dentist** | Access patient charts, write clinical notes, prescribe |
| **Owner/Admin** | Full access, analytics, staff management, reports |

## 🛠️ Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve