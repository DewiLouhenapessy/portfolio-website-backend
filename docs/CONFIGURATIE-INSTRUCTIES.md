# Configuratie-stappen voor Sanctum SPA-authenticatie

We gebruiken Sanctum's "SPA authentication": geen tokens die je zelf in `localStorage` bewaart, maar httpOnly session-cookies. Dit werkt omdat `jouwdomein.nl` en `api.jouwdomein.nl` hetzelfde hoofddomein delen.

## 1. Installeren (eenmalig, lokaal draaien vóór het uploaden)

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## 2. `.env` (op de productieserver aanpassen, niet lokaal uploaden)

```env
APP_URL=https://api.jouwdomein.nl
FRONTEND_URL=https://jouwdomein.nl

SESSION_DRIVER=database
SESSION_DOMAIN=.jouwdomein.nl
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=jouwdomein.nl,www.jouwdomein.nl
```

Let op de punt vóór `jouwdomein.nl` bij `SESSION_DOMAIN` — die zorgt ervoor dat de cookie geldig is voor zowel `jouwdomein.nl` als `api.jouwdomein.nl`.

`SESSION_DRIVER=database` vereist de sessions-tabel:

```bash
php artisan session:table
php artisan migrate
```

## 3. `config/cors.php`

Pas aan naar:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
'allowed_methods' => ['*'],
'allowed_origins' => ['https://jouwdomein.nl'],
'allowed_headers' => ['*'],
'supports_credentials' => true,   // CRUCIAAL, anders werken cookies niet
```

## 4. Middleware registreren

**Laravel 11+** (`bootstrap/app.php`):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();
})
```

**Laravel 10 en ouder** (`app/Http/Kernel.php`), voeg toe bovenaan de `api` middleware group:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

## Lokale Kanban-ontwikkeling

De frontend op `http://localhost:3000` gebruikt de Laravel-backend op
`http://localhost:8000`. Configureer `FRONTEND_URL=http://localhost:3000`
voor CORS en voeg `localhost:3000` toe aan `SANCTUM_STATEFUL_DOMAINS`.
Laravel's `bootstrap/app.php` moet `$middleware->statefulApi()` registreren
zodat de API-sessiecookie wordt herkend.

Voer vanuit de backend uit:

```bash
php artisan migrate
php artisan serve --port=8000
```

De Kanban-migratie maakt het gedeelde bord en de eerste voorbeeldkolommen en
kaarten aan. Start vervolgens de frontend met
`NEXT_PUBLIC_API_URL=http://localhost:8000` in `.env.local`. Voer de Kanban-API-tests uit met:

```bash
php artisan test tests/Feature/KanbanApiTest.php
```

## 5. Eén admin-gebruiker aanmaken (je bent immers de enige gebruiker)

Via `php artisan tinker` op de server (als je shell-toegang hebt), of via een kleine seeder:

```bash
php artisan tinker
>>> App\Models\User::create([
...     'name' => 'Jouw naam',
...     'email' => 'jij@jouwdomein.nl',
...     'password' => bcrypt('kies-een-sterk-wachtwoord'),
... ]);
```

Geen shell-toegang op Hetzner shared hosting? Maak dan tijdelijk een eenmalige route aan die deze user aanmaakt, roep hem één keer aan via de browser, en verwijder de route daarna direct weer.
