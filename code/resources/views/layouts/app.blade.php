<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <nav style="padding: 1rem; background: #f3f4f6; display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('rooms.index') }}">Rooms</a>
            <a href="{{ route('equipment.index') }}">Equipment</a>
            <a href="{{ route('reservations.index') }}">Reservations</a>
            <a href="{{ route('courses.index') }}">Courses</a>
            <a href="{{ route('assessments.index') }}">Assessments</a>
            <a href="{{ route('grades.index') }}">Grades</a>
            <a href="{{ route('staff-profiles.index') }}">Staff</a>
            <a href="{{ route('announcements.index') }}">Announcements</a>
            <a href="{{ route('messages.index') }}">Messages</a>
            <a href="{{ route('events.index') }}">Events</a>
            <form method="POST" action="{{ route('logout') }}" style="margin-left:auto;">
                @csrf
                <button>Logout</button>
            </form>
        </nav>
        <main style="padding: 1rem;">
            @if(session('success'))
                <div style="background:#dcfce7;padding:0.75rem;margin-bottom:1rem;border:1px solid #16a34a;">
                    {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
