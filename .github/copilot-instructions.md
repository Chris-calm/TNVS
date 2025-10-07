# Copilot Instructions for TNVS Project

## Project Overview
- This is a PHP-based web application for facility, document, legal, and visitor management, organized under the "TNVS" dashboard.
- The codebase is structured by feature: each major module (e.g., Contracts, Policies, Facilities, Visitor Logs) has its own PHP file in the `PHP/` directory, with shared CSS in `CSS/` and JavaScript in `JS/`.
- The UI is built with server-rendered PHP, using HTML/CSS and some JavaScript for interactivity. Tailwind CSS and Boxicons are used via CDN.

## Architecture & Data Flow
- Each PHP file is a self-contained page/module, often including `db_connect.php` for database access.
- Navigation is sidebar-driven, with dropdowns for major feature groups (e.g., Legal Management, Statistics, Visitor Management).
- Data is typically loaded and rendered server-side, with some modules using JavaScript for dynamic UI (e.g., modals, search, filtering).
- File uploads are handled via the `Upload_Document.php` module, with files stored in the `uploads/` directory.

## Key Conventions
- All database access is via `db_connect.php` (MySQL, procedural style).
- CSS is modular: use `CSS/index.css` for shared styles, and feature-specific CSS files as needed.
- JavaScript is modular: use `JS/script.js` for general scripts, and feature-specific JS files as needed.
- Use relative paths for all asset references (images, CSS, JS).
- UI components (cards, modals, forms) are often defined inline in each PHP file, not as reusable includes.

## Developer Workflows
- No build step: edit PHP, CSS, JS directly and refresh in browser (served via XAMPP/Apache).
- No formal test suite; manual testing via browser is standard.
- Debugging: use `echo`, `var_dump`, or browser dev tools for JS/CSS.
- To add a new module: create a new PHP file in `PHP/`, add navigation links in the sidebar of all relevant PHP files, and create any needed CSS/JS.

## Integration Points
- External dependencies: Tailwind CSS and Boxicons via CDN; no package manager.
- All uploads are stored in `uploads/` (with subfolders for organization).
- Images for branding/logos are in `PICTURES/`.

## Examples
- See `PHP/Contracts.php` and `PHP/Policies.php` for typical module structure (sidebar, main content, modals, JS interactivity).
- See `PHP/Dashboard.php` for the main entry point and navigation structure.

## Special Notes
- Keep navigation consistent across all modules (update sidebar in each PHP file).
- Do not introduce frameworks or build tools unless explicitly requested.
- Use procedural PHP, not OOP, to match existing code style.
