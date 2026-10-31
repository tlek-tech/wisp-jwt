<?php

class JwtMiddleware {
    public function handle($req, $next, $env) {
        $secret = $_ENV['JWT_SECRET'] ?? '';
        $auth = $req->header('Authorization') ?? '';

        if ($secret === '' || strpos($auth, 'Bearer ') !== 0) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        $payload = \Wisp\Jwt::decode(substr($auth, 7), $secret);
        if ($payload === null) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        $req->setAttribute('user', $payload);
        return $next($req);
    }
}
