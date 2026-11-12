# R.A.S

Sistema de gestión (Laravel 12) con identidad visual basada en el escudo R.A.S:
azul marino `#0f1742`, rojo `#e83222` y granate `#60282b`.

## Entorno local (XAMPP)

```bash
composer install
npm install && npm run build
cp .env.example .env          # luego ajustar DB_* (MySQL, base `ras`)
php artisan key:generate
php artisan migrate --seed
php artisan serve             # http://localhost:8000
```

Requisitos: PHP 8.2+, Node 18+, MySQL (XAMPP).
