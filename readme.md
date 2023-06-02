OMTBIZ

1. php artisan migrate:fresh
2. php artisan db:seed
3. php artisan passport:install
4. Add to env
    ```
    PROXY_OAUTH_CLIENT_ID=2
    PROXY_OAUTH_CLIENT_SECRET={client-id-2-secret}
    PROXY_OAUTH_GRANT_TYPE=password
    ```
5. If you generate new keys then added to env
   ```
   PASSPORT_PRIVATE_KEY=
   PASSPORT_PUBLIC_KEY=
   ```
Ibrave
