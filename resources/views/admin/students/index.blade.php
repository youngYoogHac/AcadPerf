@extends('layouts.app')

@section('title', 'Студенты — AcadPerf')

@section('content')
    <section class="container">
        <h1 class="page-title">Управление студентами</h1>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div>
        @endif

        <form class="news-form" action="{{ route('students.store') }}" method="POST" style="margin-bottom: 30px;">
            @csrf
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label>Группа</label>
                    <select name="group_id" required>
                        <option value="">Выберите группу</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label>Фамилия</label>
                    <input name="last_name" type="text" value="{{ old('last_name') }}" required>
                </div>
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label>Имя</label>
                    <input name="first_name" type="text" value="{{ old('first_name') }}" required>
                </div>
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label>Отчество (опц.)</label>
                    <input name="middle_name" type="text" value="{{ old('middle_name') }}">
                </div>
            </div>
            <button type="submit" class="btn">Добавить студента</button>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Группа</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $student->last_name }} {{ $student->first_name }} {{ $student->middle_name }}</td>
                        <td>{{ $student->group->name }}</td>
                        <td>
                            <form action="{{ route('students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Удалить студента?');">
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