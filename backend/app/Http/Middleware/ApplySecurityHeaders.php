public function handle($request, Closure $next)
{
    $response = $next($request);

    $response->headers->set('Content-Security-Policy', 
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' cdn.pusher.com; " .
        "style-src 'self' 'unsafe-inline'; " .
        "img-src 'self' data:; " .
        "connect-src 'self' ws://localhost:6001");
    
    $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');

    return $response;
}