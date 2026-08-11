# Technical Reflection - Client Project Tracker

### 1. Why this stack (Laravel + Vue.js + DaisyUI) vs alternatives?
Laravel with Vue 3 and Inertia.js provides an unmatched developer experience for modern full-stack web applications. Inertia allows us to leverage Vue 3's reactive, componentized UI without the overhead of client-side routing state duplication or OAuth token ceremony, while consuming a clean RESTful Laravel backend API (`/api/projects`). Combining DaisyUI component primitives with Lucide Vue icons and the Laravel starter kit layout produces an executive-grade dashboard UI with instant dark/light appearance toggles out of the box.

### 2. One architectural tradeoff made and why
I implemented per-user foreign key scoping (`user_id`) at both the Eloquent database model and API controller layer. The query builder restricts all project queries to `$request->user()->projects()`. This tradeoff ensures strict multi-tenant data boundaries while keeping API endpoints clean and consistent across session requests.

### 3. One design decision explaining why the UI looks the way it does
I moved the dashboard inside the Laravel starter kit's `AppLayout` with a collapsible sidebar, breadcrumb navigation, and user avatar menu. The design combines DaisyUI component primitives (badges, modals, selects, skeleton loaders) with Lucide Vue icons and elevated metric cards. Semantic badge colors (`badge-info`, `badge-warning`, `badge-neutral`, `badge-success`) provide clear visual hierarchy for statuses and priorities.

### 4. What I'd add with more time
With additional time, I would containerize the application using **Docker & Docker Compose** (packaging PHP-FPM, Nginx, Node, and SQLite/PostgreSQL into single-command setup) and expand the Pest PHP test suite to include E2E Cypress/Playwright browser automation testing for interactive Vue form submissions and modal transitions.

### 5. Known edge cases handled vs deliberately deferred
- **Handled**: Per-user project isolation (User A cannot view, update, or delete User B's projects, returning HTTP 404), as well as strict date sequencing validation (`due_date >= start_date`) with inline 422 field error display.
- **Deferred**: Server-side pagination. Given the initial 12 seed records per user, all filtered records are returned in a single JSON payload. For datasets exceeding 1,000 projects, server-side pagination with infinite scrolling would be implemented.
