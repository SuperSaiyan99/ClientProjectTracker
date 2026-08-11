# Reflection Notes - Client Project Tracker

### 1. Why I Chose This Tech Stack (Laravel + Vue.js + DaisyUI)
I chose Laravel with Vue 3 and Inertia.js because it makes building full-stack apps very fast and clean. Inertia connects Laravel to Vue components directly, so we don't need complex frontend state managers or separate API routers. Using DaisyUI with TailwindCSS helped build clean UI components like modals, badges, and search inputs with automatic dark mode support.

### 2. Architectural Tradeoffs
I attached every project to a specific user (`user_id`). In the backend, every query is scoped to `$request->user()->projects()`. This guarantees that users can never see or touch another user's data. The tradeoff is that projects are private to each user account instead of being shared across team members.

### 3. Design Decisions
I built the dashboard inside Laravel's main layout so it includes a sidebar, header breadcrumbs, and user profile menu. I used colored badges for Status and Priority so users can quickly see project progress (Planning, In Progress, On Hold, Completed). I also added a 1.5-second delay when typing in the search box so it doesn't overload the server while typing.

### 4. Assumptions Made in This Project
- **User Ownership**: We assume each project belongs to the user who created it. Each logged-in user only sees their own project records.
- **Database**: SQLite is used by default for simple setup, but the backend is built so it can easily switch to MySQL or PostgreSQL.
- **Seed Data**: We assumed the 12 sample projects from `test_data.json` should automatically attach to the main demo account (`admin@kodakollectiv.com`).
- **Date Rules**: Start dates and due dates are required, and the due date must always be on or after the start date (`due_date >= start_date`).

### 5. Known Edge Cases Handled vs Deferred
- **Handled**: 
  - Users cannot view, edit, or delete another user's project (returns a 404 Not Found error).
  - Clear validation error messages appear under form inputs if dates or required fields are wrong.
  - Dark mode support across all cards, modals, tables, and loading states.
- **Deferred**: 
  - Project sharing or team collaboration roles.
  - File attachments and PDF export features.

### 6. What I Would Add With More Time
If I had more time, I would add team workspace sharing, email notifications when a project due date is close, and automated end-to-end browser tests.

### 7. AI Tooling Note
To be completely transparent: I used AI coding assistants (like Gemini) to help accelerate frontend UI design styling, component layouts, and minor backend helpers. The overall architecture, database relationships, security scoping, and code quality reflect my own engineering decisions and code as per requirements given.
