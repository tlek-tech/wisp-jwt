<?php
global $app;

// Example routes showing the JWT plugin end-to-end. Copy into your app's
// routes/ folder (or use as reference), gated behind APP_DEBUG the same way
// as Wisp's other demo routes.
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    $app->get("/jwt-demo/issue", function($req) {
        $token = \Wisp\Jwt::encode(['sub' => $req->query['sub'] ?? 'demo-user']);
        return Response::json(['token' => $token]);
    });

    $app->get("/jwt-demo/verify", JwtMiddleware::class, function($req) {
        return Response::json(['user' => $req->user()]);
    });
}
