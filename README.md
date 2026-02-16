# Codenames Card Generator — Helper Engine

A Laravel-based utility for generating **print-ready Codenames-style cards** from custom image assets.

---

![Generated PDF](/docs/index.png)


## 🖼 Result & Examples

### Generated PDF (A4, double-sided, 3×4 grid)
![Generated PDF](/docs/result-pdf.png)

### Example Card Images
![Example cards](/docs/ExmapleImage4.png)

> The output is a **well-formatted, two-sided A4 PDF**, optimized for clean cutting and gameplay.


## 📦 Sample Data

Example generated cards and demo assets are available here:

[Example cards →](/docs/sample_data)

---

## 🖼 Image Optimization & Resize Command

To normalize dimensions and reduce file size before PDF generation, run:

```bash
php artisan app:resize-code-names-images
```

The command includes an interactive menu for selecting resize mode and output format.

**Interactive menu preview:**

![Resize menu](/docs/resize.png)

**Processing progress:**

![Resize progress](/docs/resize_progress.png)

All supported formats, resolution rules, and technical resize constraints are documented in: 🖼 Image Size Requirements

---

## 🎨 Image Style & Prompt Rules (Style Lock)

All card images **must** follow these rules to ensure visual consistency and print quality:

- **Style:** Flat board-game illustration  
- **Shapes:** Bold, clean, distinct  
- **Shading:** Minimal, flat tones only  
- **Color palette:** **Black, white, grayscale only**  
- **Background:** **Solid white** (preferred) or printer-safe gray  
- **Tone:** Whimsical, surreal  
- **Subject:** Exactly **one composite character per image**  
- **Exclusions:** No text, letters, numbers, symbols, logos, or brands  
- **Readability:** Instantly readable at small card size  
- **Aspect ratio:** Square **1:1 (4×4)**

This style is **locked** and must be applied to all images.

---

## 🖼 Image Size Requirements

Each image is printed at **60×60 mm** (~2.36 inches) on the PDF.

| Criteria        | Size (px)   | DPI | Notes                                      |
|-----------------|-------------|-----|--------------------------------------------|
| **Optimal**     | 709×709     | 300 | Best print quality, sharp at any distance  |
| **Recommended** | 512×512     | 216 | Good quality, no visible pixelation        |
| **Minimum**     | 354×354     | 150 | Acceptable quality, may look soft up close |

> Images below 354×354 px will appear noticeably blurry when printed.
> Non-square images will be stretched to fit the 1:1 cell.

---

## 🖨 Printing Recommendations

- Use **double-sided printing**
- Confirm your printer supports the selected PPI before printing

---

## 🧠 How the Generator Works

1. Loads images from `storage/code_names`
2. Randomly selects and arranges them
3. Generates a **two-sided A4 PDF**
4. Outputs a file ready for cutting and play

---

## 🚀 Tech Stack

- **Framework:** Laravel 12  
- **Runtime:** Docker (Laravel Sail–compatible)  
- **Output:** A4 PDF (print-ready)  
- **Assets:** Local storage (`storage/code_names`)  

---

## 🛠 Installation & Setup

### Prerequisites
- Docker Desktop
- Docker Compose
- Git

> Composer is installed inside the container — no local Composer required.

### Clone the Repository
```bash
git clone git@github.com:your-username/codenames-project.git
cd codenames-project
````

### Start the Environment

Laravel Sail is not preconfigured yet, so use Docker directly:

```bash
docker compose up -d
docker compose run --rm laravel.test composer install
```

### Application Setup

```bash
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
```

(Optional) Enter the container shell:

```bash
docker compose exec laravel.test bash
```

---

## 🎮 Card Generator Command

Run inside the container:

```bash
php artisan app:generate-code-names-pdf
```

**Description:**
Generate a printable **A4 PDF** with Codenames images arranged in a **3×4 grid**.

![CLI command example](/docs/cli-command-run.png)

---

## 📌 Notes

* Focused on **print quality**, not gameplay logic
* Perfect for custom decks, prototypes, or gifts
* Image sets can be swapped without code changes


```
Have fun generating and printing your own Codenames decks 🎲🃏
```
