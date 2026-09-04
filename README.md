# Task Manager - Drag & Drop Priority Ordering

A modern task management application built with Laravel featuring **drag-and-drop priority reordering** for tasks using a midpoint-based priority algorithm.

---

## Tech Stack

| Layer      | Technology                          |
| ---------- | ----------------------------------- |
| Backend    | Laravel 13.x, PHP 8.3               |
| Frontend   | Vanilla JS, Tailwind CSS v4, Vite 8 |
| DnD Engine | SortableJS 1.15                     |
| Database   | MySQL                               |
| Testing    | Pest PHP 4.7                        |

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
# Extract the zip file and open the project folder
cd job-task

# Install PHP dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database — create the database first, then update .env with your MySQL credentials
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=task_management_db
# DB_USERNAME=root
# DB_PASSWORD=

php artisan migrate

# Seed sample data (10 projects, 110 tasks)
php artisan db:seed

# Frontend assets
npm install
npm run build
```

### Running the App

```sh
composer run dev
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

#### Step 3: Resolving Neighbor Priorities

The backend receives `taskId`, `previousTaskId`, and `nextTaskId`. Each defaults to `0` if not provided (dropped at boundary). It then resolves actual priority values — and for edge cases (top/bottom of list), queries the database for the nearest neighbor:

```php
$prevPriority = $validated['previousTaskId']
    ? Task::where('id', $validated['previousTaskId'])->value('priority')
    : 0;

$nextPriority = $validated['nextTaskId']
    ? Task::where('id', $validated['nextTaskId'])->value('priority')
    : 0;

// Dropped at the very top: find the nearest task below the next one
if ($prevPriority === 0 && $nextPriority !== 0) {
    $prevPriority = Task::where('priority', '<', $nextPriority)
        ->orderByDesc('priority')
        ->value('priority') ?? 1;
}

// Dropped at the very bottom: find the nearest task above the previous one
elseif ($nextPriority === 0 && $prevPriority !== 0) {
    $nextPriority = Task::where('priority', '>', $prevPriority)
        ->orderBy('priority')
        ->value('priority') ?? Task::max('priority') + 1000;
}
```

**Three scenarios:**

| Position          | prevPriority | nextPriority | Resolution                                           |
| ----------------- | ------------ | ------------ | ---------------------------------------------------- |
| Between two tasks | set          | set          | Use both directly                                    |
| End of list       | set          | 0            | Query for nearest task above `prev` to use as `next` |
| Beginning of list | 0            | set          | Query for nearest task below `next` to use as `prev` |

After resolution, both values are always valid and we call `findMidpoint()`.

#### Step 4: Finding the Midpoint

Every case — including start/end — funnels into the same method:

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

Drag Task A to the bottom (after Task D):
  previousTaskId = D (priority 1500)
  nextTaskId = null (0)

  // Edge case: query for nearest task above D's priority
  // nextPriority = Task::where('priority', '>', 1500)->min('priority') = 2000
  midpoint = (1500 + 2000) / 2 = 1750
  Task A gets priority 1750

After drag:
  Task B (2000)
  Task C (3000)
  Task D (1500)
  Task A (1750)  ← moved here
```

### Why This Works Well

- **Single-row update** — Only the dragged task's row is updated, regardless of list size
- **No race conditions** — No bulk updates means no conflicting writes
- **Unified calculation** — Every case (top, middle, bottom) funnels into `findMidpoint()`, keeping logic simple
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
