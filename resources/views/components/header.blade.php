<header class="header">
    <div class="container header-inner">
        <a href="{{ route('dashboard') }}" class="logo">AcadPerf</a>

        <nav class="nav">
            @auth
                <a href="{{ route('dashboard') }}" class="nav-link">Дашборд</a>

                @if (auth()->user()->role?->name === 'admin')
                    <a href="{{ route('groups.index') }}" class="nav-link">Группы</a>
                    <a href="{{ route('students.index') }}" class="nav-link">Студенты</a>
                    <a href="{{ route('schedules.index') }}" class="nav-link">Расписание</a>
                    <a href="{{ route('users.index') }}" class="nav-link">Учителя</a>
                @endif

                @if (auth()->user()->role?->name === 'teacher')
                    <a href="{{ route('teacher.schedule') }}" class="nav-link">Мои журналы</a>
                @endif

                <div class="user-menu">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="role-badge">{{ auth()->user()->role?->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="margin:0">
                        @csrf
                        <button type="submit" class="nav-link" style="border:0;background:none;cursor:pointer">Выйти</button>
                    </form>
                </div>
            @endauth
        </nav>
    </div>
</header>