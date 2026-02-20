# Codenames Card Generator — Helper Engine

A Laravel-based utility for generating **print-ready Codenames-style cards** from custom image assets.

---

![Generated PDF](/docs/index.png)

---

# 🖼 Result & Examples

## Generated PDF (A4, double-sided, 3×4 grid)

![Generated PDF](/docs/result-pdf.png)

### Example Card Images

![Example cards](/docs/ExmapleImage4.png)

> The output is a **ready-to-print, duplex-optimized A4 PDF** prepared for clean cutting and gameplay.

---

# 🖨 Ready-to-Print PDF (Duplex Logic Explained)

The generator produces a **two-sided A4 PDF** with layout logic optimized for real-world printing:

* Each page contains a fixed **3×4 grid**
* Images are centered with calculated margins
* TCPDF margins are fully removed (true edge alignment)

### Duplex Alignment Strategy

When printing double-sided:

* **Odd pages (front side)** have no outer borders
* This reduces visual cutting noise on the second side
* It minimizes edge trash and alignment mismatch

This makes the cards cleaner after cutting and improves duplex accuracy.

If you notice offset during printing — that is usually a printer duplex calibration issue, not a layout bug.

---

# 📦 Sample Data

Example generated cards and demo assets:

[Example cards →](/docs/sample_data)

---

# 🧠 Available Artisan Commands

You can list all project commands:

```bash
php artisan app:
```

Available commands:

### Generate PDF

```bash
php artisan app:generate-code-names-pdf
```

Generate a printable A4 PDF with images arranged in a 3×4 grid.

---

### Resize Images

```bash
php artisan app:resize-code-names-images
```

Resize Code Names images to selected target size for optimal print quality.

Includes interactive menu:

![Resize menu](/docs/resize.png)

Processing example:

![Resize progress](/docs/resize_progress.png)

---

### Convert Images to Grayscale

```bash
php artisan app:grayscale-code-names-images
```

Convert color images to grayscale (recommended for consistent deck style).

---

# 🖼 Image Size Requirements

Each image is printed at **60×60 mm (~2.36 inches)**.

| Criteria        | Size (px) | DPI | Notes                |
| --------------- | --------- | --- | -------------------- |
| **Optimal**     | 709×709   | 300 | Best print sharpness |
| **Recommended** | 512×512   | 216 | Good balance         |
| **Minimum**     | 354×354   | 150 | May appear soft      |

⚠ Images below 354×354 px will appear blurry when printed.
⚠ Non-square images are forced into 1:1 cells.

---

# 🎨 Image Style Rules (Locked)

All images must follow strict style rules:

* Flat board-game illustration
* Bold, clean shapes
* Minimal shading
* Black / white / grayscale only
* Solid white background
* One composite character per image
* No text, logos, numbers, symbols
* Square 1:1 format

This ensures visual consistency across decks.

---

# 🖨 Printing Recommendations

* Use **double-sided printing**
* Use 220–300 GSM paper for durability
* Confirm printer supports 300 DPI
* Enable duplex alignment correction if available

---

# 🚀 Tech Stack

* Laravel 12
* Docker (Sail-compatible runtime)
* TCPDF
* Local image storage

---

# 🛠 Installation & Setup

## Requirements

* Docker Desktop
* Docker Compose
* Git

---

## Clone

```bash
git clone git@github.com:your-username/codenames-project.git
cd codenames-project
```

---

## Start Environment

```bash
docker compose up -d
docker compose run --rm laravel.test composer install
```

---

## Setup Application

```bash
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
```

Optional shell:

```bash
docker compose exec laravel.test bash
```

---

## Generate Cards

```bash
php artisan app:generate-code-names-pdf
```

Output file:

```
storage/code_names_pdf/code_names.pdf
```

---

# 📌 Notes

* This is a **print-focused generator**, not a gameplay engine
* Image sets are fully swappable
* Layout is optimized for physical card cutting

---

Have fun generating and printing your own Codenames decks 🎲
