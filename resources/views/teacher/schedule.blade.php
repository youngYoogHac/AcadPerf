@extends('layouts.app')

@section('title', 'Мои пары — AcadPerf')

@section('content')
    <section class="container">
        <h1 class="page-title">Мои журналы</h1>

        <div class="placeholder-card" style="margin-bottom: 40px; background: #f8fafc;">
            <h2 style="margin-top: 0;">Сводные журналы</h2>
            <p style="color: #6b7280; margin-bottom: 20px;">Выберите предмет и группу, чтобы открыть таблицу для выставления оценок за весь семестр.</p>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                @foreach($uniqueJournals as $journal)
                    <a href="{{ route('journal.grid', ['group' => $journal->group_id, 'subject' => $journal->subject_id]) }}" 
                       class="btn btn-secondary" style="display: flex; flex-direction: column; align-items: flex-start; padding: 15px 20px; text-align: left; border: 1px solid #e5e7eb;">
                        <strong style="font-size: 1.1rem; color: #111827;">{{ $journal->subject->name }}</strong>
                        <span style="font-size: 0.9rem; color: #6b7280;">Группа: {{ $journal->group->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        
    </section>
@endsection