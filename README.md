# My Vue SPA with Laravel Starter

This package provides a **starter SPA setup** built on top of a fresh Laravel project. It automates the installation of
Vue 2, Tailwind CSS, and Sanctum authentication so you can get a fully functional SPA login page without repetitive
setup.

---

## Features

- **Empty Laravel Project Scaffold**  
  Start with a clean Laravel installation (`composer create-project laravel/laravel your-project`) and run the SPA setup
  command.

- **Automatic Vue 2 Setup**  
  Vue 2 is installed with a ready-to-use `resources/js/app.js`, `bootstrap.js`, and `App.vue` entry point.

- **Tailwind CSS Preconfigured**  
  `resources/css/app.css` is created automatically with Tailwind directives. A `tailwind.config.js` file is generated
  with proper content paths for Blade and Vue files.

- **Sanctum Authentication Ready**  
  SPA cookie-based authentication is ready out-of-the-box.

- **Login Page**  
  A fully functional login view is included and wired up with the Vue router.

- **Optional Product Module**  
  Add `--with-product` when running the SPA command to generate a full CRUD with:
    - Automatic slug generation
    - Grid-refilling pagination
    - PHP 8.2+ optimized code

- **Artisan SPA Install Command**  
  Run `php artisan spa:install` to scaffold everything automatically in seconds.

---

## Setup Instructions

1. **Install Dependencies**

```bash
composer install
npm install
```

2. **Run the SPA Installer**

```bash
php artisan spa:install
```

This will automatically:

- Create `resources/js/app.js`, `bootstrap.js`, and Vue components
- Create `resources/css/app.css` with Tailwind directives
- Create `tailwind.config.js` if it doesn't exist
- Set up Laravel Mix with Vue 2 and Tailwind
- Configure all scripts in `package.json` for `npm run dev`, `watch`, `production`, etc.

3. **Configure Database**

Edit your `.env` file with your database credentials.

4. **Run Migrations**

```bash
php artisan migrate
```

5. **Seed a Test User**

```bash
php artisan db:seed --class=UserSeeder
```

- Default credentials:
    - **Email:** `test@example.com`
    - **Password:** `password`

6. **Compile Assets**

```bash
npm run dev
# or
npm run watch
```

7. **Serve the Application**

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` to see the login page.

---

## Notes

- **Sessions Table:** If using `SESSION_DRIVER=database`, make sure to run `php artisan session:table` and migrate to
  avoid errors.
- **Vue Version:** Vue 2 is used to match the SPA setup; Vue 3 is not included.
- **Tailwind CSS:** The installer ensures `tailwind.config.js` is properly created and configured for Blade and Vue
  components.
- **Artisan Command:** The SPA command automates setup so you don’t need to repeat scaffolding every time you start a
  new project.

---

## Troubleshooting

- If `npm run watch` fails, make sure your Node version is **14 or higher**.
- If Tailwind compilation fails, ensure `resources/css/app.css` exists with the correct directives:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

- Make sure Laravel Mix is installed in your `package.json`:

```json
"devDependencies": {
"laravel-mix": "^6.0.0",
"vue": "^2.7.0",
"vue-template-compiler": "^2.7.0",
"tailwindcss": "^3.3.0",
"autoprefixer": "^10.0.0",
"postcss": "^8.0.0"
}
```

---

This setup gives you a **ready-to-use SPA project** with authentication and a login page, saving hours of repetitive
configuration.
