<header class="header">
    <div class="container header-inner">
        <a href="{{ route('dashboard') }}" class="logo">AcadPerf</a>

        <nav class="nav">
            @auth
                {{-- Ссылка на главную страницу (Дашборд) --}}
                <a href="{{ route('dashboard') }}" class="nav-link">Мои группы</a>

                <div class="user-menu">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="margin:0">
                        @csrf
                        <button type="submit" class="nav-link" style="border:0;background:none;cursor:pointer">Выйти</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="nav-link">Войти</a>
            @endauth
        </nav>
    </div>
</header>