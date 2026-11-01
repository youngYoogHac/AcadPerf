@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 100%; overflow-x: auto;">
                {{-- ПАНЕЛЬ УПРАВЛЕНИЯ --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0;">Журнал: {{ $group->name }} — {{ $subject->name }}</h1>

            <div style="display: flex; gap: 10px; align-items: center;">
                {{-- КНОПКА ЭКСПОРТА --}}
                <a href="{{ route('journal.export', [$group->id, $subject->id]) }}" class="btn btn-small" style="background: #059669;">
                    📥 Скачать шаблон
                </a>

                {{-- ФОРМА ИМПОРТА --}}
                <form action="{{ route('journal.import', [$group->id, $subject->id]) }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center; background: #f8fafc; padding: 10px 15px; border-radius: 8px; border: 1px solid #e5e7eb; margin: 0;">
                    @csrf
                    <label for="excel_file" style="margin: 0; font-weight: bold; font-size: 0.9rem;">Импорт:</label>
                    <input type="file" name="excel_file" id="excel_file" required style="font-size: 0.9rem;">
                    <button type="submit" class="btn btn-small btn-secondary" style="margin: 0;">Загрузить</button>
                </form>
            </div>
        </div>

        <form action="{{ route('journal.update-grid') }}" method="POST">
            @csrf
            <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        {{-- Фиксируем ширину первой колонки и прижимаем текст влево --}}
                        <th style="width: 250px; text-align: left; position: sticky; left: 0; background: #fff; z-index: 1;">
                            Студент
                        </th>
                        
                        {{-- Выводим даты как столбцы. Центрируем текст --}}
                        @foreach($schedules as $schedule)
                            <th style="text-align: center; min-width: 100px; border-left: 1px solid #eee;">
                                {{ \Carbon\Carbon::parse($schedule->date)->format('d.m') }}<br>
                                <small style="color: #888;">{{ $schedule->pair_number }} пара</small>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            {{-- Фиксируем ФИО студента --}}
                            <td style="text-align: left; position: sticky; left: 0; background: #fff; font-weight: bold; border-right: 1px solid #ddd;">
                                {{ $student->last_name }} {{ $student->first_name }}
                            </td>
                            
                            {{-- Для каждого студента выводим ячейки для каждой даты --}}
                            @foreach($schedules as $schedule)
                                @php 
                                    $markId = $attendances[$student->id][$schedule->id]->mark_id ?? null; 
                                @endphp
                                
                                {{-- Центрируем select внутри ячейки --}}
                                <td style="text-align: center; border-left: 1px solid #eee; padding: 8px;">
                                    <select name="marks[{{ $student->id }}][{{ $schedule->id }}]" 
                                            style="width: 80px; padding: 6px; border: 1px solid #ccc; border-radius: 6px; text-align: center; margin: 0 auto; display: block;">
                                        <option value="">—</option>
                                        @foreach($marks as $mark)
                                            <option value="{{ $mark->id }}" @selected($markId == $mark->id)>
                                                {{ $mark->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="margin-top: 30px;">
                <button type="submit" class="btn">Сохранить ведомость</button>
            </div>
        </form>
    </div>
@endsection