# WINNER production deployment checklist

## Before deployment

- Use a server whose document root is the Laravel `public` directory.
- Configure `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, a unique `APP_KEY`, production MySQL credentials and `SESSION_SECURE_COOKIE=true`.
- Keep `.env`, database exports, logs, backups and source-control metadata outside the public web root.
- Configure SMTP or a transactional mail provider and keep all credentials only in environment configuration.
- Keep `QUEUE_CONNECTION=database` and supervise at least one queue worker.
- Back up MySQL, `storage/app/public` uploads and environment secrets to protected storage. Test restoring each backup separately.

## Release commands

```sh
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan winner:migrate-product-images --dry-run
php artisan winner:migrate-product-images
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Run the database migration only after a verified database backup. Never use `migrate:fresh` or `db:wipe` in production.

## Operations

- Supervise `php artisan queue:work --tries=3 --backoff=60 --timeout=90` and restart it on releases.
- Monitor `php artisan queue:failed`, application logs, disk usage, database capacity and mail delivery failures.
- Terminate HTTPS at the web server or trusted proxy and redirect HTTP to HTTPS there.
- Restrict filesystem permissions: the web process needs write access only to `storage` and `bootstrap/cache`.
- Confirm scheduled database and upload backups complete, are encrypted where appropriate, and are not web-accessible.

## Product image storage

- Product uploads live under `storage/app/public/products`; database values are relative paths such as `products/example.jpg`.
- `resources/images` remains for source and design assets and is not authoritative for new product uploads.
- The migration command copies first, verifies the destination, is idempotent, and never deletes source files. Review its unmatched report before changing anything manually.
- Git does not transfer uploaded files. Copy `storage/app/public/products` to production storage separately and run `php artisan storage:link`.
- Verify every migrated product before removing legacy resource product files. Keep logos, heroes, category artwork and editorial assets.

## Smoke test

- Load the homepage, catalogue, one category and one product.
- Register, verify email when required, log in and log out.
- Add, update and remove a cart item.
- Place a Cash on Delivery test order and verify stock, totals, order history and queued email.
- Confirm customer order ownership and admin-route protection.
- Check 403, 404, 419 and 500 presentation with debug disabled.
