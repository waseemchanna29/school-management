<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 – Not Found</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:2rem;">
    <i class="fas fa-search" style="font-size:4rem;color:var(--accent);margin-bottom:1rem;"></i>
    <h1 style="font-family:var(--font-display);font-size:3rem;color:var(--primary);margin-bottom:0.5rem;">404</h1>
    <p style="color:var(--text-muted);font-size:1.05rem;margin-bottom:1.5rem;">The page you're looking for doesn't exist.</p>
    <a href="{{ url('/') }}" class="btn btn-primary"><i class="fas fa-home"></i> Go Home</a>
</div>
</body>
</html>