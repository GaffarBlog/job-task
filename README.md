# Task Manager - Drag & Drop Priority Ordering

A modern task management application built with Laravel featuring **drag-and-drop priority reordering** for tasks using a midpoint-based priority algorithm.

---

## Tech Stack

| Layer       | Technology                                  |
|-------------|---------------------------------------------|
| Backend     | Laravel 13.x, PHP 8.3                      |
| Frontend    | Vanilla JS, Tailwind CSS v4, Vite 8        |
| DnD Engine  | SortableJS 1.15                            |
| Database    | SQLite                                     |
| Testing     | Pest PHP 4.7                              |

---

## Installation

### Prerequisites

- PHP >= 8.3
- Composer
- Node.js & npm

### Quick Setup

Run the one-liner setup script:

```sh
composer setup
```

This will:
1. Install PHP dependencies
2. Create `.env` file and generate app key
3. Run database migrations
4. Install npm dependencies
5. Build frontend assets

### Manual Setup

```sh
# Clone the repository
git clone <your-repo-url>
cd job-task

# Install PHP dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate

# Seed sample data (10 projects, 110 tasks)
php artisan db:seed

# Frontend assets
npm install
npm run build
```

### Running the App

```sh
composer dev
```

This starts the Laravel server, queue worker, and Vite dev server concurrently. Visit [http://localhost:8000](http://localhost:8000).

---

## Features

- **Dashboard** - Overview showing total tasks and projects count
- **Projects** - Full CRUD with paginated listing (name + description)
- **Tasks** - Full CRUD with project association, description, and priority
- **Drag & Drop Reordering** - Visually reorder task priorities by dragging

---

## How Drag & Drop Priority Ordering Works

This is the most interesting part of the project. The system uses a **midpoint-based priority algorithm** with gap spacing to avoid re-indexing all tasks on every reorder.

### The Problem

When a user drags a task to a new position, you need to update the `priority` column so the task list reflects the new order. A naive approach (renumbering all tasks sequentially) requires N database updates per drag. This is inefficient and creates race conditions.

### The Solution: Midpoint Insertion

Instead of sequential integers, tasks are assigned priorities with large gaps between them. When a task is moved, we calculate a new priority **between** its new neighbors — no need to touch any other records.

#### Step 1: Initial Spacing

When a new task is created, it gets the next available priority with a gap of **1000**:

```php
$maxPriority = Task::max('priority') ?? 0;
$validated['priority'] = $maxPriority + 1000;
```

This creates initial priorities like: `1000, 2000, 3000, 4000, ...`

#### Step 2: Detecting the Drop Position

When a user drops a task, the frontend captures the **previous sibling** and **next sibling** task IDs:

```javascript
onEnd: function (evt) {
    const item = evt.item;
    const taskId = item.dataset.taskId;
    const previousTaskId = item.previousElementSibling?.dataset.taskId ?? null;
    const nextTaskId = item.nextElementSibling?.dataset.taskId ?? null;

    fetch("/tasks/priority", {
        method: "PUT",
        body: JSON.stringify({ taskId, previousTaskId, nextTaskId }),
    });
}
```

#### Step 3: Calculating the New Priority

The backend receives `taskId`, `previousTaskId`, and `nextTaskId`, then determines the new priority:

```php
private function calculatePriority(?int $previousPriority, ?int $nextPriority): int
{
    // Between two tasks
    if ($previousPriority !== null && $nextPriority !== null) {
        return $this->findMidpoint($previousPriority, $nextPriority);
    }

    // End of list
    if ($previousPriority !== null) {
        return $this->getNextAvailablePriority($previousPriority + 1);
    }

    // Beginning of list
    if ($nextPriority !== null) {
        return $this->getPreviousAvailablePriority($nextPriority - 1);
    }

    return 1000; // Only task
}
```

**Three scenarios:**

| Position | Neighbors | Calculation |
|----------|-----------|-------------|
| Between two tasks | prev + next exist | `intdiv(prev + next, 2)`, then find next free slot |
| End of list | only prev | Increment from `prev + 1` until free |
| Beginning of list | only next | Decrement from `next - 1` until free |

#### Step 4: Finding the Midpoint

```php
private function findMidpoint(int $prev, int $next): int
{
    $candidate = intdiv($prev + $next, 2);

    // If midpoint is taken, increment until we find a free slot
    while (Task::where('priority', $candidate)->exists()) {
        $candidate++;
    }

    return $candidate;
}
```

### Visual Example

```
Before drag:
  Task A (priority: 1000)
  Task B (priority: 2000)
  Task C (priority: 3000)
  Task D (priority: 4000)

Drag Task D between Task A and Task B:
  previousTaskId = A (priority 1000)
  nextTaskId = B (priority 2000)

  midpoint = (1000 + 2000) / 2 = 1500
  Task D gets priority 1500

After drag:
  Task A (1000)
  Task D (1500)  ← moved here
  Task B (2000)
  Task C (3000)
```

### Why This Works Well

- **Single-row update** — Only the dragged task's row is updated, regardless of list size
- **No race conditions** — No bulk updates means no conflicting writes
- **Handles edge cases** — Moving to start, end, or between any two tasks
- **Collision resolution** — If the midpoint is taken, the algorithm finds the next available slot

### Limitation

The midpoint gap converges over many reorders between the same two tasks. After ~30 reorders between the same pair (depending on initial spacing), priorities may need a full re-index. The initial gap of 1000 provides ample room for typical usage.

---

## Database Structure

```
projects
  id              BIGINT (PK)
  name            STRING
  description     TEXT (nullable)
  created_at      TIMESTAMP
  updated_at      TIMESTAMP

tasks
  id              BIGINT (PK)
  name            STRING
  project_id      BIGINT (FK → projects.id, ON DELETE CASCADE)
  description     TEXT (nullable)
  priority        INTEGER (default: 0)
  created_at      TIMESTAMP
  updated_at      TIMESTAMP
```

**Relationships:** `Project` has many `Task`s. `Task` belongs to a `Project`.

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── DashboardController.php   # Dashboard stats
│   ├── ProjectController.php     # Project CRUD
│   └── TaskController.php        # Task CRUD + priority update
├── Models/
│   ├── Project.php
│   └── Task.php
resources/
├── views/
│   ├── layouts/main.blade.php    # Base layout
│   ├── dashboard/index.blade.php
│   ├── tasks/                    # Task views (index, create, edit, show)
│   └── projects/                 # Project views (index, create, edit, show)
└── js/app.js                     # SortableJS drag & drop logic
routes/
└── web.php                       # All routes
```

---

## License

MIT
