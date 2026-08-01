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
