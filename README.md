# Client Project Tracker

A full-stack web application to manage agency client projects. Built with Laravel 13, Vue 3, Inertia.js, TailwindCSS, and DaisyUI.

---

## Technologies Used

- Backend: Laravel 13 (REST API)
- Frontend: Vue 3 (Composition API) with Inertia.js
- Styling: TailwindCSS v4 with DaisyUI components
- Icons: Lucide Vue
- Authentication: Laravel Auth (Fortify / Session)
- Database: SQLite (default)

### Database Note
The app uses SQLite by default (`database/database.sqlite`). You can switch to MySQL or PostgreSQL by changing your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=client_project_tracker
DB_USERNAME=root
DB_PASSWORD=
```

---

## Demo Login Credentials

When you run the database seeder, a default user account is created and linked to all sample projects:

- Email: `admin@kodakollectiv.com`
- Password: `password`

Each user can only see, create, edit, and delete their own projects.

---

## How to Set Up and Run

### 1. Backend Setup

Open your terminal in the project folder and run:

```bash
# Install PHP packages
composer install

# Create .env file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations and seed sample projects
php artisan migrate:fresh --seed

# Start the Laravel server
php artisan serve
```

The server will start at `http://127.0.0.1:8000`.

### 2. Frontend Setup

In a new terminal tab or window, run:

```bash
# Install JavaScript packages
npm install

# Start Vite dev server for hot reloading
npm run dev
```

To build production assets:
```bash
npm run build
```

---

## Initial Sample Data

The project reads `test_data.json` from the project root and inserts 12 sample projects into the database. These sample projects cover all status values (Planning, In Progress, On Hold, Completed) and priority values (Low, Medium, High).

---

## REST API Endpoints

All API endpoints return JSON data and work for the currently logged-in user:

| Method | Endpoint | Description | Query Parameters / Payload |
| :--- | :--- | :--- | :--- |
| GET | `/projects` | Get all projects | Optional: `search`, `status`, `priority`, `sort`, `direction`, `page`, `per_page` |
| GET | `/projects/meta` | Get metadata & statistics | Returns status list, priority list, and summary counts |
| GET | `/projects/{id}` | Get single project | Returns project details or 404 error |
| POST | `/projects` | Create a new project | Requires `client_name`, `project_name`, `status`, `priority`, `start_date`, `due_date` |
| PUT | `/projects/{id}` | Update an existing project | Accepts updated project fields |
| DELETE | `/projects/{id}` | Delete a project | Deletes project and returns success message |

### Validation Rules

- `client_name`: Required, text, max 255 characters
- `project_name`: Required, text, max 255 characters
- `status`: Required, must be one of: `Planning`, `In Progress`, `On Hold`, `Completed`
- `priority`: Required, must be one of: `Low`, `Medium`, `High`
- `start_date`: Required, date (`YYYY-MM-DD`)
- `due_date`: Required, date, must be on or after `start_date`

---

## Running Tests

Run the PHP test suite:
```bash
php artisan test
```

Run TypeScript type checking:
```bash
npm run types:check
```

---

## Reflection

For my reflection and Assumptions Made, please refer to [REFLECTION.md](./REFLECTION.md).
