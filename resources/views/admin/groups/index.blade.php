@extends('layouts.app')

@section('title', 'Группы — AcadPerf')

@section('content')
    <section class="container">
        <h1 class="page-title">Управление группами</h1>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div>
        @endif

        <form class="news-form" action="{{ route('groups.store') }}" method="POST" style="margin-bottom: 30px;">
            @csrf
            <div class="form-group">
                <label for="name">Название новой группы</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Например, ИС-23" required>
            </div>
            <button type="submit" class="btn">Добавить группу</button>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Студентов</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)
                    <tr>
                        <td>{{ $group->id }}</td>
                        <td>{{ $group->name }}</td>
                        <td>{{ $group->students_count }}</td>
                        <td>
                            <form action="{{ route('groups.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Удалить группу и всех её студентов?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-small btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection