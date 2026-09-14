<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Student Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; }
    </style>
    @livewireStyles
</head>
<body>
    <div class="login-wrap">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
