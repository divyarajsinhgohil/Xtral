# X-TRAL — Project Features Documentation

**Brand:** X-Tral — Premium Bathware & Sanitary Ware  
**Project Duration:** 25-05-2026 to 24-08-2026  
**Server:** Hostinger (Live Production)  
**Database:** `u401719003_xtral`

---

## 📌 Project Summary — 10 Key Points

1. **Dynamic Homepage & Banner Slider** — Dynamic hero carousel, category grid, new arrivals, featured products and catalogue CTA fetched live from MySQL.
2. **PDF Catalogue & Inline Reader** — Downloadable product catalogue PDF with one click and interactive inline browser viewer.
3. **Complete Product Catalogue & Live Search** — Browse by category, series and sub-category with instant live search (by name, code, series) and product detail pages.
4. **Contact & Dealer Enquiry System** — AJAX contact form with instant email alerts delivered to company inbox via Gmail SMTP.
5. **Admin Command Dashboard** — Real-time telemetry cards (total products, active count, categories, series, banners, admin users) and quick action shortcuts.
6. **Complete Admin Panel CRUD** — Full management for products, categories, series, sub-categories, banners, colours and features with multi-image upload.
7. **Bulk Import & Export Tools** — One-click catalogue export to CSV/Excel and bulk product import with spreadsheet template validation.
8. **REST API Architecture** — Dedicated PHP REST API layer (`xadmin/api/webapi/`) serving JSON data to keep frontend decoupled.
9. **Security & SEO Optimisation** — Session timeout, password hashing, role checks, PDO prepared statements, CSRF protection, `sitemap.xml` and `robots.txt`.
10. **Live Deployment on Hostinger** — Production environment auto-detection for database and base URL settings, deployed live on Hostinger server.

---

## 🌐 Frontend Features

- **Dynamic Hero Banner Carousel** — Full-width slider with custom title, subtitle, background image, and CTA button link.
- **Category Grid** — Responsive cards for Sanitary Ware, Bath Fittings, Kitchen Sinks, Wellness, and PTMT Faucets.
- **New Arrivals Slider** — Dynamic showcase carousel featuring the latest product collections.
- **Signature Picks / Featured Products** — Handpicked signature bathware collection grid on the homepage.
- **PDF Catalogue Access** — Direct PDF catalogue download button and interactive inline page-by-page catalogue viewer.
- **Contact & Dealer Enquiry Form** — Form fields for Name, Phone, Email, Subject dropdown, and Message with AJAX handling.
- **Instant Email Notifications** — Automatic email alerts sent via Gmail SMTP directly to the company inbox on form submission.
- **Product Detail View** — Complete product page featuring multi-image gallery, size variants, finish colours, and specs.
- **Real-Time Live Search** — Instant client-side search bar for finding products by name, product code, or series.
- **Multi-Level Navigation** — Filter products seamlessly by Main Category, Series, and Sub-category.
- **Quick-Contact Info Cards** — One-touch access to company phone, email, WhatsApp, and office address.
- **Dedicated Brand Pages** — About Us, Company Profile page, and International Export Enquiry landing page.
- **Fully Responsive Layout** — Optimized fluid design for seamless performance on mobile, tablet, and desktop screens.
- **SEO Ready Setup** — Optimized meta title tags, meta descriptions, semantic HTML structure, `sitemap.xml`, and `robots.txt`.
- **API-Driven Frontend** — Dynamically fetches all banners, categories, products, and configuration settings via PHP REST APIs.

---

## 🛠️ Admin Side Features

- **Authentication & Security** — Admin login with password hashing, session guards, and role-based access control (`super_admin`).
- **Dashboard Command Center** — Real-time telemetry overview cards showing total products, active items, categories, banners, and users.
- **Recently Updated Products Panel** — Displays the latest modified product records with status indicators and direct edit links.
- **Quick Action Shortcuts** — Fast navigation buttons for immediate access to core admin tools and settings.
- **Product Management (CRUD)** — Create, edit, view, and delete products with image galleries, variants, finish colours, and features.
- **Visibility Status Toggles** — Instantly toggle Active/Inactive, Featured Product, and New Arrival tags per item.
- **Bulk Record Management** — Multi-select product list checkboxes for fast batch deletion of records.
- **Category Management** — Add, edit, and delete main categories with cover image uploads and display order control.
- **Series & Sub-Category Management** — Organise series collections linked to parent categories and manage nested sub-categories.
- **Homepage Banner Manager** — Add, edit, delete, and reorder hero slider banners with custom captions and button links.
- **Finish Colours & Specifications** — Define reusable product finishes (Chrome, Gold, Black) and bullet-point product technical features.
- **Catalogue Import & Export Tools** — Export full product database to CSV/Excel or import bulk items using formatted CSV templates.
- **Company Settings & Configuration** — Manage company logo, office address, contact numbers, email addresses, and social media URLs.
- **WhatsApp Template Manager** — Create and manage automated WhatsApp message templates for catalogue sharing.
- **Database Backup & Restore** — Generate on-demand full MySQL database SQL dumps, download backups, and restore system state.
- **Admin User Management** — List, create, edit, delete, and enable/disable admin user accounts.

---

## 📅 Development Timeline — Week Wise

### **Week 1 (30 June 2026 – 06 July 2026)**
- **Day 1 (30-06)** — Created the frontend UI design according to X-Tral brand aesthetics.
- **Day 2 (01-07)** — Started admin panel development; completed admin side, added Catalogue section, connected admin API to frontend.
- **Day 3-4 (02-07 to 03-07)** — Completed frontend implementation, tested functionality, added demo banners, checked and fixed mobile responsiveness.
- **Day 5 (04-07)** — Streamlined website structure based on client feedback into a clean informational showcase website.
- **Day 6 (06-07)** — Completed final checks, deployed live on Hostinger server, and shared live URL with client for review.

### **Week 2 (07 July 2026 – 13 July 2026)**
- **Day 7 (07-07)** — Added video support to the homepage hero banner.
- **Day 8 (08-07)** — Configured logo branding overlay on product images.
- **Day 9 (09-07)** — Added product video section on product detail page, added additional homepage imagery, fixed errors, received client approval.
- **Day 10 (10-07)** — Added filter section with category and price range filters; updated product photos.
- **Day 11 (11-07)** — Added product demonstration videos, extracted product images from catalogue, and added 4-5 core products.
- **Day 12 (13-07)** — Upgraded product photo resolutions to large format and improved product presentation layout.

### **Week 3 (14 July 2026 – 24 August 2026)**
- **Day 13 (14-07)** — Created all remaining product images; performed full end-to-end system testing.
- **Day 14 (24-08)** — Verified all live features, generated final project documentation, and finalized sign-off.

---

## 📆 Development Timeline — Date Wise

- **30 June 2026** — Designed and developed frontend layout according to X-Tral brand identity.
- **01 July 2026** — Initiated admin panel development, built core layout, dashboard, completed catalogue modules, connected REST APIs.
- **02 July 2026 – 03 July 2026** — Completed frontend pages, performed functional testing, configured banners, fixed mobile display issues.
- **04 July 2026** — Streamlined layout based on client review and configured website as a clean informational brand showcase.
- **06 July 2026** — Deployed website live on Hostinger production server, configured database settings, shared live URL with client.
- **07 July 2026** — Added video banner playback capabilities on website header.
- **08 July 2026** — Integrated brand logo watermarking / overlay on product display images.
- **09 July 2026** — Implemented product video player on product detail pages, expanded visual galleries, obtained client sign-off.
- **10 July 2026** — Implemented interactive category and price filter controls, uploaded high-resolution product photography.
- **11 July 2026** — Embedded product demo videos, digitized PDF catalogue assets, populated initial catalog set with specs.
- **13 July 2026** — Optimized product image dimensions for high-DPI displays, enhanced product detail card typography and zoom.
- **14 July 2026** — Completed catalog population, executed final full-system audit of frontend, API and admin features, confirmed stability.

---

**X-Tral — Premium Bathware & Sanitary Ware**  |  *Deployed on Hostinger*  |  *24-08-2026*
