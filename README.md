# PixelBoard - Real-time Collaborative Pixel Art Studio

PixelBoard is a modern, web-based collaborative pixel art editor built with **Laravel 11**, **Vue.js 3**, and **Laravel Reverb** for real-time WebSocket communication. It allows teams to work together on pixel art canvases instantly, manage projects with role-based access control, and communicate via integrated team chat.

##  Technology Stack

-   **Backend**: Laravel 11 (PHP 8.2+)
-   **Frontend**: Vue.js 3 (Composition API), Tailwind CSS
-   **Real-time**: Laravel Reverb (WebSocket Server), Laravel Echo
-   **Database**: MySQL 8.0
-   **Cache/Queue**: Redis

---

##  Key Features & Core Implementation

### 1. Real-time Pixel Editor
The heart of the application. Allows multiple users to draw on the same canvas simultaneously.

-   **Component**: `resources/js/Components/PixelEditor.vue`
-   **Logic**:
    -   Uses HTML5 `<canvas>` API for rendering.
    -   **Tools**: Pencil, Eraser, Fill Bucket (Flood Fill algorithm), Color Picker, Shape Tools.
    -   **Real-time Sync**:
        -   **Frontend**: Listens to `.pixel.painted` event via Echo.
        -   **Backend**: `CanvasController::update` triggers `PixelPainted` event.
        -   **Optimistic UI**: Draws locally immediately, then broadcasts.
    -   **Export**: Built-in feature to scale up (20x) and download artwork as PNG/JPG.

```php
// App/Events/PixelPainted.php
class PixelPainted implements ShouldBroadcastNow {
    public function broadcastOn() {
        return new PresenceChannel('canvas.' . $this->canvasId);
    }
}
```

### 2. Role-Based Access Control (RBAC)
Granular permission system to manage team security.

-   **Roles**:
    -   **Owner**: Full access (Delete project, Manage RBAC, Edit everything).
    -   **Editor**: Create/Delete canvases, Invite members.
    -   **Member**: Draw on canvases, Chat.
    -   **Viewer**: Read-only access (Cannot draw, Cannot chat).
-   **Implementation**:
    -   **Database**: `project_users` pivot table with `role` ('owner', 'editor', 'member', 'viewer') and `status`.
    -   **Policy**: `App/Policies/ProjectPolicy.php` enforce rules using `authorize()`.
    -   **Middleware**: Custom logic in Controllers to check `hasRole($user, 'role')`.

### 3. Real-time Team Chat
Integrated chat for project collaboration, restricted by role.

-   **Component**: `resources/js/Components/ProjectChat.vue`
-   **Backend**: `App/Http/Controllers/ChatController.php`
-   **Features**:
    -   **Live Updates**: Messages appear instantly using `ShouldBroadcastNow`.
    -   **Security**: Viewers are blocked from sending or viewing chat history (403 Forbidden).
    -   **UI**: Smart interface that disables input for unauthorized users.
    -   **Axios**: Uses Axios for robust header handling (`X-Socket-ID`) to prevent self-echo.

### 4. Interactive Dashboard & Navigation
Modern UI with Shadcn-like aesthetics (Zinc/Emerald theme).

-   **Pages**:
    -   **Dashboard**: Overview of working projects.
    -   **Explore**: Public gallery of pixel art.
    -   **Teams**: Team management interface.
    -   **Assets**: Downloadable resources (Palettes, Tilesets).
-   **Sidebar**: Dynamic navigation with active state highlighting.

---

## 🛠️ Setup & Installation

### Prerequisites
-   Docker & Docker Compose
-   Node.js & NPM

### Installation Steps

1.  **Clone & Setup Environment**
    ```bash
    git clone <repo-url>
    cd pixel-board
    cp .env.example .env
    ```

2.  **Start Docker Containers**
    ```bash
    docker-compose up -d --build
    ```

3.  **Install Dependencies**
    ```bash
    docker exec -it pixel-board-app composer install
    docker exec -it pixel-board-app php artisan key:generate
    docker exec -it pixel-board-app php artisan migrate --seed
    npm install
    npm run build
    ```

4.  **Start Reverb Server (Critical for Real-time)**
    ```bash
    docker exec -it pixel-board-app php artisan reverb:start
    # OR if using separate container
    docker restart pixel-board-reverb
    ```

### Troubleshooting Real-time Issues
If changes (drawing/chat) don't appear for other users:
1.  Ensure `.env` has `BROADCAST_CONNECTION=reverb`.
2.  Ensure Events implement `ShouldBroadcastNow` (to bypass database queue).
3.  Check browser console for WebSocket connection errors.
4.  Restart Reverb: `php artisan reverb:restart`.

---

## 📂 Core Directory Structure

```
├── app/
│   ├── Events/           # Real-time events (PixelPainted, MessageSent)
│   ├── Http/Controllers/ # Logic for Canvas, Chat, Projects
│   ├── Models/           # Eloquent Models (Project, Canvas, Message)
│   └── Policies/         # Authorization Rules (RBAC)
├── resources/
│   ├── js/
│   │   ├── Components/   # Vue components (PixelEditor.vue, ProjectChat.vue)
│   │   └── Pages/        # Main route pages
│   └── views/            # Blade templates (layouts, static pages)
└── routes/
    ├── channels.php      # WebSocket Channel Authorization
    └── web.php           # App Routes
```

By **Roja Fadilah** (User) & **Antigravity** (AI Assistant).
