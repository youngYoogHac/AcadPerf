@extends('layouts.app')

@section('title', 'Дашборд — AcadPerf')

@section('content')
    <section class="container">
        <h1 class="page-title">Добро пожаловать!</h1>
        
        <div class="placeholder-card" style="margin-bottom: 30px;">
            <h2>Вы вошли как: <strong>{{ auth()->user()->name }}</strong></h2>
            <p>Ваша текущая роль в системе: <span class="role-badge">{{ auth()->user()->role?->name }}</span></p>
        </div>

        @if (auth()->user()->role?->name === 'admin')
            <h3>Быстрые действия (Администратор):</h3>
            <div style="display: flex; gap: 15px; margin-top: 15px;">
                <a href="{{ route('groups.index') }}" class="btn">Управление группами</a>
                <a href="{{ route('students.index') }}" class="btn">Управление студентами</a>
                <a href="{{ route('schedules.index') }}" class="btn">Расписание</a>
            </div>
        @endif

        @if (auth()->user()->role?->name === 'teacher')
            <h3>Быстрые действия (Преподаватель):</h3>
            <div style="display: flex; gap: 15px; margin-top: 15px;">
                <a href="{{ route('teacher.schedule') }}" class="btn">Перейти к моим парам</a>
            </div>
        @endif
    </section>
@endsection