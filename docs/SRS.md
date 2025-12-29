# Software Requirements Specification (SRS)

Project: TravelNext (Travel & Tour Management Website)
Version: 1.0
Date: 2025-12-10
Author: Team

## 1. Introduction
- Purpose: Define functional and non-functional requirements for a travel & tour site with packages, bookings, user accounts, an admin back office, a floating chatbot, and search suggestions.
- Scope: Public site (packages, details, contact), user features (auth, bookings, wishlist, reviews), admin (manage packages, itineraries, images, users, payments, reviews, settings), utility (chatbot widget, destination typeahead), and platform services (DB, security, file uploads).

## 2. Overall Description
- Users: Visitors, Registered Users, Admins.
- Environment: Web app (PHP 8+, MySQL 5.7+/8, Apache), modern browsers (desktop/mobile).
- Constraints: Secure coding (CSRF, password hashing), no heavy frontend frameworks required; pure HTML/CSS/JS and PHP.

## 3. System Features
### 3.1 Packages (Public)
- View packages with title, location, duration, type, price, hero image.
- Filter by destination, budget, and type.
- View package details with images and itineraries.

### 3.2 Search / Typeahead
- Destination input shows live suggestions from package `location` and `title`.
- Backend endpoint `GET /suggest.php?q=text` returns JSON array.
- Frontend vanilla JS renders dropdown and supports keyboard/mouse select.

### 3.3 Booking (Public/User)
- Start a booking from package page.
- Persist booking in DB with user relationship.

### 3.4 Authentication (User)
- Register: Full name, email, phone, address, password + confirm, validation.
- Login: Email + password, session-based.
- Passwords hashed (PHP password_hash).

### 3.5 Chatbot (Public)
- Floating button opens chat window.
- Suggested questions and scripted responses.
- Scrollable messages and suggestions lists.
- Works on mobile/desktop; close (X) button.

### 3.6 Admin: Packages
- Create/edit packages: title, slug, location, duration_days, type, overview, includes, excludes, price, seats, featured, status.
- Manage itineraries (add/delete).
- Manage photos: upload OR remote URL → always saved in `/uploads/packages/` (random filename), validated and stored in DB.

### 3.7 Admin: Users
- List users with stats and status (active/inactive).
- Activate/deactivate users (CSRF-protected action forms).

### 3.8 Admin: Other
- Bookings, Payments, Reviews, Messages, Settings basic CRUD (as implemented in codebase).

## 4. External Interface Requirements
- UI: HTML templates with vanilla CSS; responsive.
- JS: `assets/js/main.js` handles chatbot, typeahead, navbar toggler.
- API: `suggest.php` JSON; admin uses server-rendered forms.
- Filesystem: `/uploads/packages/` for package images; server-writable.

## 5. Database Requirements
- MySQL schema: `database.sql` contains tables `admins`, `users`, `packages`, `itineraries`, `package_images`, `bookings`, `payments`, `wishlist`, `reviews`, `notifications`, `messages`, `settings`.
- Keys/constraints defined; common FKs cascade on delete for content tables.

## 6. Security Requirements
- CSRF protection: hidden tokens on forms, server-side verification.
- Auth sessions: `$_SESSION` for user/admin.
- Password hashing: `password_hash`/`password_verify`.
- SQL safety: prepared statements where user input is persisted/searched.
- File upload safety: type/size validation, random filenames, dedicated upload dir.

## 7. Performance Requirements
- Lightweight vanilla JS/CSS, no heavyweight frameworks required.
- Image handling uses cURL with timeouts and size caps.
- Suggestion endpoint is index-backed (LIKE) with limit.

## 8. Reliability & Availability
- Graceful errors on invalid inputs and download failures.
- Defensive defaults for settings and images.

## 9. Maintainability
- Clear file structure: `includes/`, `admin/`, `assets/`, `uploads/`.
- Modular helpers in `includes/helpers.php` (csrf, redirect, settings, image helpers).

## 10. Assumptions & Dependencies
- PHP sessions enabled; server has write permissions to `/uploads/packages/`.
- cURL available (falls back to stream when not).
- Database credentials configured in `includes/config.php`.

## 11. Technology & Framework Usage
- Frontend UI: HTML, CSS, JavaScript (vanilla). No frameworks required.
- Chatbot: HTML/CSS/JS (vanilla), embedded in footer and driven by `assets/js/main.js`.
- Search Typeahead: Vanilla JS + PHP endpoint `suggest.php`.
- Backend: PHP (procedural), MySQLi for DB.
- Image handling: PHP filesystem + cURL (with fallback streams).
- Security: Native PHP sessions, CSRF tokens, password hashing.
- Optional libraries present in codebase: Font Awesome icons. (Bootstrap/AOS are not required; the system works without them.)

## 12. Detailed Feature Mapping (Language/Framework)
- Package CRUD, Itineraries, Images: PHP + MySQLi (server), HTML/CSS (views), JS (minor triggers).
- User Auth (Register/Login): PHP + MySQLi (server), HTML/CSS/JS (forms/UI).
- Chatbot Widget: HTML/CSS + Vanilla JS (UI and behavior).
- Search Suggestions: PHP (`suggest.php`) + Vanilla JS dropdown.
- Admin User Activation: PHP + MySQLi + CSRF token forms.

## 13. Non-Goals
- External AI integrations; real-time chat backends; payment gateway integrations beyond schema; SPA framework.

## 14. Acceptance Criteria
- Admin can create/edit packages; image upload or URL saves to `/uploads/packages/` and shows on details.
- Chatbot opens/closes; messages and suggestions are scrollable; works on mobile/desktop.
- Admin can activate/deactivate users without CSRF errors.
- Destination inputs show suggestions as the user types.

## 15. Future Enhancements
- Replace icon fonts with inline SVGs everywhere.
- Server-side full-text search for suggestions.
- Image thumbnailing and CDN.
- Role-based permissions.

---
End of SRS.
