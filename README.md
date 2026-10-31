# wisp-jwt

JWT (HS256) authentication plugin for the [Wisp](https://github.com/tlek-tech/wisp) PHP micro-framework.

Adds `\Wisp\Jwt::encode()` / `::decode()`, a `JwtMiddleware` for protecting routes with
`Authorization: Bearer <token>`, and a `$req->user()` macro that exposes the decoded
claims on any request that passed through the middleware.

## Requirements

Your Wisp core must support the plugin extension points this relies on:
- `Request::macro()` and `Request::setAttribute()` / `getAttribute()`
- The `plugins/` auto-loader in `public/index.php` (loads `plugins/<name>/<name>.php`)

## Install

1. Copy the `plugins/jwt/` folder from this repo into your Wisp project's `plugins/` folder:
   ```
   your-wisp-project/
   └── plugins/
       └── jwt/
           ├── jwt.php
           └── JwtMiddleware.php
   ```
2. Add the settings from this repo's `.env.example` to your project's `.env`:
   ```dotenv
   JWT_SECRET=            # php -r "echo bin2hex(random_bytes(32));"
   JWT_TTL=3600
   ```
3. That's it — Wisp's plugin loader picks up `plugins/jwt/jwt.php` automatically on boot.

## Usage

```php
// Issue a token
$token = \Wisp\Jwt::encode(['sub' => $userId]);

// Protect a route
$app->get("/me", JwtMiddleware::class, function ($req) {
    return Response::json(['user' => $req->user()]);
});
```

See `routes/jwt.php` in this repo for a runnable example (copy it into your project's
`routes/` folder if you want the `/jwt-demo/*` endpoints).

## License

MIT
