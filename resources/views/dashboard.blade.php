@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Мои группы</h1>

    {{-- Форма создания группы --}}
    <form action="{{ route('groups.store') }}" method="POST" style="margin-bottom: 30px; display: flex; gap: 10px;">
        @csrf
        <input type="text" name="name" placeholder="Название группы (например, ИС-21)" required style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
        <button type="submit" class="btn">Создать группу</button>
    </form>

    {{-- Список групп --}}
        <div class="category-grid">
        @foreach($groups as $group)
            <div class="category-card" style="position: relative; display: flex; flex-direction: column; justify-content: space-between;">
                <a href="{{ route('groups.show', $group->id) }}" style="text-decoration: none; color: inherit; flex-grow: 1;">
                    <span class="category-name">{{ $group->name }}</span>
                    <span class="category-description" style="display: block; margin-top: 10px;">Студентов: {{ $group->students_count }}</span>
                </a>

                {{-- Кнопка удаления группы --}}
                <form action="{{ route('groups.destroy', $group->id) }}" method="POST" 
                      onsubmit="return confirm('Вы уверены, что хотите удалить группу {{ $group->name }} ВМЕСТЕ СО ВСЕМИ ОЦЕНКАМИ И СТУДЕНТАМИ? Это действие необратимо.');" 
                      style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; text-align: right;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 13px; font-weight: bold;">
                        🗑 Удалить группу
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection