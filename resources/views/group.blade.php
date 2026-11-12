@extends('layouts.app')

@section('content')
<div class="container">
    <a href="{{ route('dashboard') }}">← Назад к группам</a>
    <h1>Группа: {{ $group->name }}</h1>

    {{-- Форма создания нового журнала (предмета) --}}
    <form action="{{ route('subjects.store', $group->id) }}" method="POST" style="margin-bottom: 30px; display: flex; gap: 10px;">
        @csrf
        <input type="text" name="name" placeholder="Название предмета (например, Математика)" required style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
        <button type="submit" class="btn">Добавить журнал</button>
    </form>

    <h3>Существующие журналы:</h3>
    <div class="category-grid">
        @forelse($subjects as $subject)
            <div class="category-card" style="background: #f0fdf4; display: flex; flex-direction: column; justify-content: space-between;">
                <a href="{{ route('journal.grid', [$group->id, $subject->id]) }}" style="text-decoration: none; color: inherit;">
                    <span class="category-name">{{ $subject->name }}</span>
                    <span class="category-description" style="display: block; margin-top: 10px;">Открыть журнал</span>
                </a>

                {{-- Кнопка удаления журнала --}}
                <form action="{{ route('subjects.destroy', [$group->id, $subject->id]) }}" method="POST" 
                      onsubmit="return confirm('Удалить журнал по предмету {{ $subject->name }}? Все оценки за этот предмет будут стерты.');" 
                      style="margin-top: 15px; border-top: 1px solid #bbf7d0; padding-top: 10px; text-align: right;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 13px; font-weight: bold;">
                        🗑 Удалить журнал
                    </button>
                </form>
            </div>
        @empty
            <p>Журналов пока нет. Создайте первый!</p>
        @endforelse
    </div>
</div>
@endsection