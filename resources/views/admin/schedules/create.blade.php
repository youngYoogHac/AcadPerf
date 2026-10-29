@extends('layouts.app')

@section('title', 'Добавить пару — AcadPerf')

@section('content')
    <section class="form-section">
        <div class="container narrow-container">
            <h1 class="page-title">Добавить пару</h1>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                </div>
            @endif

            <form class="news-form" action="{{ route('schedules.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label>Группа</label>
                    <select name="group_id" required>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Учитель</label>
                    <select name="user_id" required>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('user_id') == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Предмет</label>
                    <select name="subject_id" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Тип пары</label>
                    <select name="lesson_type_id" required>
                        @foreach ($lessonTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('lesson_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Дата</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label>Номер пары (1-6)</label>
                    <input type="number" name="pair_number" min="1" max="6" value="{{ old('pair_number', 1) }}" required>
                </div>

                <button type="submit" class="btn">Сохранить</button>
                <a href="{{ route('schedules.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </section>
@endsection