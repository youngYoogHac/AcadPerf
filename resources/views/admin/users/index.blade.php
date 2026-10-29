@extends('layouts.app')

@section('title', 'Учителя — AcadPerf')

@section('content')
    <section class="container">
        <h1 class="page-title">Управление учителями</h1>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div>
        @endif

        <form class="news-form" action="{{ route('users.store') }}" method="POST" style="margin-bottom: 30px;">
            @csrf
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1;">
                    <label>ФИО учителя</label>
                    <input name="name" type="text" value="{{ old('name') }}" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Логин</label>
                    <input name="login" type="text" value="{{ old('login') }}" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Пароль</label>
                    <input name="password" type="password" required>
                </div>
            </div>
            <button type="submit" class="btn">Добавить учителя</button>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Логин</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->login }}</td>
                        <td>{{ $user->role?->name }}</td>
                        <td>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Удалить учителя?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-small btn-danger">Удалить</button>
                                </form>
                            @else
                                <span class="muted-cell">Это вы</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection