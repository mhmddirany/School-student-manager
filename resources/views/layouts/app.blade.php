<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Student Manager' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .stat-card .stat-value { font-size: 2rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { color: #6c757d; margin-top: 4px; }
        th.sortable { cursor: pointer; user-select: none; }
        th.sortable .sort-arrow { opacity: 0.35; font-size: 0.85em; margin-left: 4px; }
        th.sortable.active .sort-arrow { opacity: 1; }
    </style>
    @livewireStyles
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="{{ route('dashboard') }}">Student Manager</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('students.index') }}">Students</a></li>
            @can('manage-users')
                <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Users</a></li>
            @endcan
            @can('view-logs')
                <li class="nav-item"><a class="nav-link" href="{{ route('logs.index') }}">Activity Logs</a></li>
            @endcan
        </ul>
    </div>
    @auth
        <span class="text-light me-3">
            {{ auth()->user()->name }}
            <span class="badge bg-secondary text-uppercase">{{ auth()->user()->role }}</span>
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-light btn-sm" type="submit">Logout</button>
        </form>
    @endauth
</nav>

<div class="container-fluid px-4 pb-5 mt-3">
    {{-- Each page's Livewire view renders its own <x-breadcrumbs> at the top,
         since the trail differs per page (e.g. Dashboard > Students > Edit). --}}

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif

    {{ $slot }}
</div>

@livewireScripts
</body>
</html>
