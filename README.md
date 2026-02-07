Markdown

# Codenames Web Engine

A Laravel-based digital implementation of the Codenames board game using image assets.

## 🚀 Technical Stack

* **Framework:** Laravel 11
* **Environment:** Laravel Sail (Docker)
* **Frontend:** Tailwind CSS / Blade
* **Asset Management:** Local Storage for prepared image sets

## 🛠 Installation & Setup

### 1. Prerequisites

Ensure you have Docker Desktop and Composer installed.

### 2. Clone and Initialize

```bash
git clone git@github.com:your-username/codenames-project.git
cd codenames-project
3. Start Environment
Use Laravel Sail to bring up the containers:

```

./vendor/bin/sail up -d

4. Application Setup
   Bash

./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
🎮 Game Logic
Board Generation: The engine selects 25 random images from the storage/app/public/game_assets directory.

Role Mapping: A randomized 5x5 grid assigns teams (Red/Blue), neutral bystanders, and the assassin.

View Modes:

Spymaster: Full visibility of the color-coded grid.

Operative: Image-only view with interactive selection.

📁 Structure
app/Services/BoardGenerator.php: Logic for grid randomization.

resources/views/game/: Blade templates for game states.

public/images/prepared/: Pre-processed game assets.

⚠️ Known Constraints
Image Composite: (If applicable) Dynamic image generation requires libgd or imagick within the Sail container.

Persistence: Current version uses session-based state; multi-device sync requires Database/Redis caching.


---

Would you like me to write the `BoardGenerator` service class to handle the image-to-coordinate mapping logic?
