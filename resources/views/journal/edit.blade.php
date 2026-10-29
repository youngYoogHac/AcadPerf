@extends('layouts.app')

@section('title', 'Журнал — AcadPerf')

@section('content')
    <section class="container">
        <a href="{{ route('teacher.schedule') }}" class="back-link">← Назад к парам</a>
        
        <h1 class="page-title" style="margin-top: 15px;">Журнал пары</h1>
        
        <div class="placeholder-card" style="margin-bottom: 30px;">
            <p><strong>Предмет:</strong> {{ $schedule->subject->name }} ({{ $schedule->lessonType->name }})</p>
            <p><strong>Группа:</strong> {{ $schedule->group->name }} | <strong>Дата:</strong> {{ \Carbon\Carbon::parse($schedule->date)->format('d.m.Y') }} | <strong>Пара:</strong> {{ $schedule->pair_number }}</p>
        </div>

        <form action="{{ route('journal.update', $schedule->id) }}" method="POST">
            @csrf
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Студент</th>
                        <th style="width: 300px;">Оценка / Отметка</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>{{ $student->last_name }} {{ $student->first_name }}</td>
                        <td>
                            @php $currentMarkId = $attendances[$student->id]->mark_id ?? null; @endphp
                            
                            <select name="marks[{{ $student->id }}]" style="width: 100%; padding: 8px; border-radius: 6px;">
                                <option value="">— Нет отметки —</option>
                                @foreach($marks as $mark)
                                    <option value="{{ $mark->id }}" @selected($currentMarkId == $mark->id)>
                                        {{ $mark->name }} ({{ $mark->type == 'grade' ? 'Оценка' : 'Посещаемость' }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn">Сохранить журнал</button>
            </div>
        </form>
    </section>
@endsection