# Despliegue en Hostinger (vía Git)

Los assets ya compilados (`public/build`) van en el repo, así que no hace falta Node en el servidor.
`vendor/` no se sube: se instala con Composer por SSH.

## Primera vez
1. hPanel → **Avanzado → Git**: conecta `https://github.com/DekoCB/RAS.git`, rama `main`, y haz el deploy a una carpeta (p. ej. `domains/TU-DOMINIO/ras`).
2. Dominio: lo ideal es poner el **Document Root en `.../ras/public`**. Si no se puede, el `.htaccess` de la raíz redirige todo a `/public`.
3. Crea la base MySQL y su usuario en hPanel → **Bases de datos**.
4. Por SSH, en la carpeta del proyecto:
   ```bash
   cp .env.production.example .env
   nano .env                 # APP_URL, DB_*, MAIL_*
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force   # solo la primera vez (datos iniciales)
   php artisan storage:link
   ```
   Selecciona PHP 8.2+ en hPanel → Avanzado → Configuración PHP.
5. Cambia de inmediato las claves de los usuarios sembrados (p. ej. `admin@ras.edu.pe`).

## Actualizaciones
Haz push a `main`, pulsa **Deploy** en hPanel (o activa el webhook) y ejecuta `bash deploy.sh` por SSH.

## Notas
- Nunca subas `.env`; está en `.gitignore`.
- Si cambias CSS/JS de `resources/`, ejecuta `npm run build` en local y sube `public/build`.
- Cola (`QUEUE_CONNECTION=database`): si usas trabajos en cola, agrega un cron: `php artisan queue:work --stop-when-empty` cada minuto.
