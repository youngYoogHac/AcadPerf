@extends('layouts.app')

@section('title', 'Расписание — AcadPerf')

@section('content')
    <section class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="page-title" style="margin: 0;">Расписание</h1>
            <a href="{{ route('schedules.create') }}" class="btn">Добавить пару</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Пара</th>
                    <th>Группа</th>
                    <th>Предмет</th>
                    <th>Тип</th>
                    <th>Учитель</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schedules as $schedule)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($schedule->date)->format('d.m.Y') }}</td>
                        <td>{{ $schedule->pair_number }}</td>
                        <td>{{ $schedule->group->name }}</td>
                        <td>{{ $schedule->subject->name }}</td>
                        <td>{{ $schedule->lessonType->name }}</td>
                        <td>{{ $schedule->teacher->name }}</td>
                        <td>
                            <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('Удалить пару из расписания?');">
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