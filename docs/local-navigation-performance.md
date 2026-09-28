# Local navigation performance

The navigation loader is registered in `router.js` before authorization. This
keeps the existing fresh server identity check on every navigation while showing
feedback during that request and lazy page imports. Page-specific data loaders
take over after the route mounts. The main layout no longer waits for an outgoing
fade before mounting the next page.

## Local measurements (28 September 2026)

On the Windows checkout mounted into the local Docker containers:

- Nginx health response: approximately 24 ms.
- Vite Notes page module response: approximately 39 ms.
- Laravel profile endpoint, unauthenticated JSON response: 4.2–5.3 seconds.
- Separate CLI measurement: Laravel bootstrap took 5.6 seconds including autoload;
  a subsequent database connection and `SELECT 1` took 322 ms.
- After optimizing Composer autoload and caching Laravel configuration and routes,
  the same unauthenticated profile request took approximately 1.9 seconds.

These are individual local diagnostic samples, not an authenticated page-load
benchmark or a production performance guarantee. Laravel startup is a significant
part of the measured delay. Windows bind-mounted PHP dependencies are a candidate
for further profiling; moving dependencies to Linux storage has not been tested.

## Cache maintenance

The following commands were applied to the running local environment:

```powershell
docker exec local-php composer dump-autoload --optimize --no-scripts --no-interaction
docker exec local-php php artisan config:cache
docker exec local-php php artisan route:cache
```

Configuration and route caches are generated, ignored files. After editing `.env`,
configuration, or routes, clear the relevant cache or rebuild it with the commands
above. Clear both before running PHP tests so cached local configuration does not
override the test database settings:

```powershell
docker exec local-php php artisan config:clear
docker exec local-php php artisan route:clear
```

No production configuration was changed. Vite development mode remains enabled.
