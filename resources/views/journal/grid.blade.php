@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 100%; overflow-x: auto;">
        
        {{-- ПАНЕЛЬ УПРАВЛЕНИЯ --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0;">Журнал: {{ $group->name }} — {{ $subject->name }}</h1>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('journal.template', [$group->id, $subject->id]) }}?{{ request()->getQueryString() }}" class="btn btn-small" style="background: #3b82f6;">
                    📄 Скачать шаблон
                </a>
                
                <a href="{{ route('journal.export', [$group->id, $subject->id]) }}?{{ request()->getQueryString() }}" class="btn btn-small" style="background: #059669;">
                    📥 Скачать с оценками
                </a>

                <form action="{{ route('journal.import', [$group->id, $subject->id]) }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center; background: #f8fafc; padding: 10px 15px; border-radius: 8px; border: 1px solid #e5e7eb; margin: 0;">
                    @csrf
                    <label for="excel_file" style="margin: 0; font-weight: bold; font-size: 0.9rem;">Импорт:</label>
                    <input type="file" name="excel_file" id="excel_file" required style="font-size: 0.9rem;">
                    <button type="submit" class="btn btn-small btn-secondary" style="margin: 0;">Загрузить</button>
                </form>

                {{-- КНОПКИ РЕЖИМА РЕДАКТИРОВАНИЯ --}}
                <div style="margin-left: 20px; display: flex; gap: 10px; padding-left: 20px; border-left: 2px solid #e5e7eb;">
                    <button type="button" id="btn-enable-edit" class="btn" style="background: #f59e0b; color: white;">✏️ Редактировать</button>
                    <button type="submit" id="btn-submit-edit" form="journal-form" class="btn" style="display: none; background: #059669; color: white;">💾 Сохранить</button>
                    <a href="{{ request()->fullUrl() }}" id="btn-cancel-edit" class="btn btn-secondary" style="display: none;">Отмена</a>
                </div>
            </div>
        </div>

        {{-- ФИЛЬТРЫ И СОРТИРОВКА --}}
        <div style="margin-bottom: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
            <form method="GET" action="{{ route('journal.grid', [$group->id, $subject->id]) }}" style="display: flex; gap: 15px; align-items: flex-end; margin: 0; flex-wrap: wrap;">
                
                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Поиск студента:</label>
                    <input type="text" name="search_student" value="{{ request('search_student') }}" placeholder="Иванов..." style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 150px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Подгруппа:</label>
                    <select name="filter_subgroup" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                        <option value="">Все</option>
                        @foreach($subgroups ?? [] as $subgroup)
                            <option value="{{ $subgroup }}" {{ request('filter_subgroup') == $subgroup ? 'selected' : '' }}>{{ $subgroup }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Сортировка:</label>
                    <select name="sort" onchange="this.form.submit()" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>По алфавиту (А-Я)</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>По алфавиту (Я-А)</option>
                        <option value="id_asc" {{ request('sort') == 'id_asc' ? 'selected' : '' }}>Сначала старые</option>
                        <option value="id_desc" {{ request('sort') == 'id_desc' ? 'selected' : '' }}>Сначала новые</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Фильтр по дате:</label>
                    <select name="filter_type" id="filter_type" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                        <option value="all" {{ request('filter_type') == 'all' ? 'selected' : '' }}>Все даты</option>
                        <option value="single" {{ request('filter_type') == 'single' ? 'selected' : '' }}>Одна дата</option>
                        <option value="range" {{ request('filter_type') == 'range' ? 'selected' : '' }}>Диапазон</option>
                    </select>
                </div>

                <div id="single_date_wrap" style="display: none;">
                    <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Дата:</label>
                    <input type="date" name="filter_date" value="{{ request('filter_date') }}" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div id="range_date_wrap" style="display: none; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">С:</label>
                        <input type="date" name="filter_date_from" value="{{ request('filter_date_from') }}" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">По:</label>
                        <input type="date" name="filter_date_to" value="{{ request('filter_date_to') }}" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <button type="submit" class="btn btn-small">Применить</button>
                @if(request()->hasAny(['search_student', 'filter_subgroup', 'filter_type', 'filter_date', 'filter_date_from', 'filter_date_to', 'sort']))
                    <a href="{{ route('journal.grid', [$group->id, $subject->id]) }}" style="font-size: 13px; color: #dc2626; text-decoration: underline; margin-bottom: 8px;">Сбросить</a>
                @endif
            </form>
        </div>

        {{-- ОСНОВНАЯ ФОРМА С ТАБЛИЦЕЙ --}}
        <form id="journal-form" action="{{ route('journal.update-grid') }}" method="POST">
            @csrf
            <table class="excel-table" id="journal-table">
                <thead>
                    <tr>
                        <th class="student-header" style="text-align: left; min-width: 250px;">Студент</th>
                        <th style="text-align: center; min-width: 80px;">Подгр.</th>
                        
                        @foreach($schedules as $schedule)
                            <th class="schedule-col" 
                                data-schedule-id="{{ $schedule->id }}" 
                                data-date="{{ \Carbon\Carbon::parse($schedule->date)->format('Y-m-d') }}" 
                                data-pair-number="{{ $schedule->pair_number }}"
                                style="min-width: 100px; position: relative; vertical-align: top; padding: 5px;">
                                {{ \Carbon\Carbon::parse($schedule->date)->format('d.m') }}<br>
                                <small>{{ $schedule->pair_number }} пара</small><br>
                                
                                {{-- Текст для режима просмотра --}}
                                <small class="schedule-type-text" style="color: #666; font-size: 10px;">
                                    {{ $schedule->lessonType->name ?? '' }}
                                </small>

                                {{-- Выпадающий список для режима редактирования --}}
                                <select class="schedule-type-select" data-schedule-id="{{ $schedule->id }}" style="display: none; width: 100%; font-size: 10px; border: 1px solid #ccc; margin-top: 2px;">
                                    @foreach($lessonTypes as $type)
                                        <option value="{{ $type->id }}" @selected($schedule->lesson_type_id == $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>

                                {{-- Кнопка удаления скрыта по умолчанию --}}
                                <button type="button" class="delete-schedule-btn" data-schedule-id="{{ $schedule->id }}" title="Удалить пару" style="display: none; position: absolute; top: 2px; right: 2px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 10px; padding: 2px 5px; opacity: 0.6; transition: 0.2s;">✖</button>
                            </th>
                        @endforeach
                        
                        {{-- N+1 Колонка (скрыта по умолчанию) --}}
                        <th id="new-schedule-header" style="display: none; min-width: 160px; width: 160px; background: #e2e8f0; vertical-align: top; padding: 5px; box-sizing: border-box;">
                            <div style="display: flex; flex-direction: column; gap: 5px; width: 100%;">
                                <input type="date" id="new-schedule-date" style="width: 100%; font-size: 11px; border: 1px solid #ccc; padding: 3px; box-sizing: border-box;">
                                <select id="new-schedule-pair" style="width: 100%; font-size: 11px; border: 1px solid #ccc; padding: 3px; box-sizing: border-box;">
                                    <option value="1">1 пара</option>
                                    <option value="2">2 пара</option>
                                    <option value="3">3 пара</option>
                                    <option value="4">4 пара</option>
                                    <option value="5">5 пара</option>
                                    <option value="6">6 пара</option>
                                </select>
                                {{-- Выбор типа пары --}}
                                <select id="new-schedule-type" style="width: 100%; font-size: 11px; border: 1px solid #ccc; padding: 3px; box-sizing: border-box;">
                                    @foreach($lessonTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" id="btn-add-schedule" style="width: 100%; padding: 4px; font-size: 11px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; box-sizing: border-box;">+ Добавить</button>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody id="students-tbody">
                    @foreach($students as $student)
                        <tr data-student-id="{{ $student->id }}">
                            {{-- ФИО --}}
                            <td class="student-name" style="padding: 0;">
                                <div style="display: flex; align-items: center; width: 100%;">
                                    <input type="text" class="student-name-input" data-student-id="{{ $student->id }}" value="{{ trim($student->last_name . ' ' . $student->first_name . ' ' . $student->middle_name) }}" readonly style="flex-grow: 1; border: none; background: transparent; padding: 6px; font-weight: bold; outline: none; width: 100%; cursor: default;">
                                    <button type="button" class="delete-student-btn" data-student-id="{{ $student->id }}" title="Удалить" style="display: none; background: #fee2e2; color: #dc2626; border: none; padding: 4px 8px; cursor: pointer; border-radius: 4px; margin-right: 4px; opacity: 0.6; transition: 0.2s;">✖</button>
                                </div>
                            </td>

                            {{-- Подгруппа --}}
                            <td style="text-align: center; padding: 0;">
                                <input type="text" name="subgroups[{{ $student->id }}]" value="{{ $student->subgroup }}" readonly style="width: 100%; height: 100%; border: none; text-align: center; font-size: 14px; background: transparent; outline: none; cursor: default;" placeholder="-">
                            </td>
                            
                            {{-- Оценки --}}
                            @foreach($schedules as $schedule)
                                @php 
                                    $markName = $attendances[$student->id][$schedule->id]->mark->name ?? ''; 
                                @endphp
                                <td data-schedule-id="{{ $schedule->id }}">
                                    <input type="text" name="marks[{{ $student->id }}][{{ $schedule->id }}]" value="{{ $markName }}" readonly style="width: 100%; border: none; text-align: center; font-size: 14px; font-weight: bold; background: transparent; outline: none; cursor: default;" placeholder="-">
                                </td>
                            @endforeach
                            <td style="background: #f1f5f9;"></td> 
                        </tr>
                    @endforeach
                    
                    {{-- N+1 Строка (скрыта по умолчанию) --}}
                    <tr id="new-student-row" style="display: none; background: #f8fafc;">
                        <td class="student-name" style="padding: 0;">
                            <input type="text" id="new-student-input" placeholder="+ Введите ФИО и нажмите Enter..." style="width: 100%; height: 100%; border: none; padding: 8px; background: transparent; font-weight: bold; outline: none;">
                        </td>
                        <td style="background: #f1f5f9;"></td>
                        @foreach($schedules as $schedule)
                            <td data-schedule-id="{{ $schedule->id }}" style="background: #f1f5f9;"></td>
                        @endforeach
                        <td style="background: #f1f5f9;"></td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = '{{ csrf_token() }}';

    // ==========================================
    // ЛОГИКА РЕЖИМА РЕДАКТИРОВАНИЯ
    // ==========================================
    const btnEnableEdit = document.getElementById('btn-enable-edit');
    const btnSubmitEdit = document.getElementById('btn-submit-edit');
    const btnCancelEdit = document.getElementById('btn-cancel-edit');

    btnEnableEdit.addEventListener('click', function() {
        // Меняем кнопки
        btnEnableEdit.style.display = 'none';
        btnSubmitEdit.style.display = 'inline-flex';
        btnCancelEdit.style.display = 'inline-flex';

        // Разблокируем все текстовые поля (убираем readonly)
        document.querySelectorAll('input[type="text"]').forEach(input => {
            input.removeAttribute('readonly');
            input.style.cursor = 'text';
            input.style.backgroundColor = '#fff';
        });

        // Показываем N+1 колонку и строку
        document.getElementById('new-schedule-header').style.display = 'table-cell';
        document.getElementById('new-student-row').style.display = 'table-row';

        // Показываем кнопки удаления
        document.querySelectorAll('.delete-student-btn').forEach(btn => {
            btn.style.display = 'inline-block';
        });
        document.querySelectorAll('.delete-schedule-btn').forEach(btn => {
            btn.style.display = 'inline-block';
        });

        // Показываем выпадающие списки типов пар в заголовках
        document.querySelectorAll('.schedule-type-text').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.schedule-type-select').forEach(el => el.style.display = 'inline-block');
    });

    // ==========================================
    // 1. ЛОГИКА ПЕРЕКЛЮЧЕНИЯ ФИЛЬТРОВ ДАТЫ
    // ==========================================
    const filterType = document.getElementById('filter_type');
    const singleDateWrap = document.getElementById('single_date_wrap');
    const rangeDateWrap = document.getElementById('range_date_wrap');

    function toggleDateFilters() {
        if (filterType.value === 'single') {
            singleDateWrap.style.display = 'block';
            rangeDateWrap.style.display = 'none';
        } else if (filterType.value === 'range') {
            singleDateWrap.style.display = 'none';
            rangeDateWrap.style.display = 'flex';
        } else {
            singleDateWrap.style.display = 'none';
            rangeDateWrap.style.display = 'none';
        }
    }
    filterType.addEventListener('change', toggleDateFilters);
    toggleDateFilters(); 

    // ==========================================
    // 2. СОХРАНЕНИЕ ИМЕНИ СТУДЕНТА
    // ==========================================
    function bindNameInput(input) {
        let originalValue = input.value;
        const saveName = () => {
            if (input.hasAttribute('readonly')) return; 

            const newValue = input.value.trim();
            const studentId = input.dataset.studentId;
            if (newValue === originalValue || !newValue) return;

            fetch(`/journal/student/${studentId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ full_name: newValue })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    originalValue = newValue;
                    input.style.backgroundColor = '#dcfce7';
                    setTimeout(() => {
                        input.style.backgroundColor = input.hasAttribute('readonly') ? 'transparent' : '#fff';
                    }, 500);
                }
            })
            .catch(() => alert('Ошибка при сохранении имени'));
        };
        input.addEventListener('blur', saveName);
        input.addEventListener('keypress', (e) => { if (e.key === 'Enter') { e.preventDefault(); input.blur(); }});
    }
    document.querySelectorAll('.student-name-input').forEach(bindNameInput);

    // ==========================================
    // 3. УДАЛЕНИЕ СТУДЕНТА
    // ==========================================
    function bindDeleteStudentBtn(btn) {
        btn.addEventListener('mouseenter', () => btn.style.opacity = '1');
        btn.addEventListener('mouseleave', () => btn.style.opacity = '0.6');
        btn.addEventListener('click', function() {
            const studentId = this.dataset.studentId;
            const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
            const studentName = row.querySelector('.student-name-input').value;

            if (!confirm(`Удалить студента ${studentName} и все его оценки?`)) return;

            fetch(`/journal/student/${studentId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => { if (data.success) row.remove(); })
            .catch(() => alert('Ошибка при удалении студента'));
        });
    }
    document.querySelectorAll('.delete-student-btn').forEach(bindDeleteStudentBtn);

    // ==========================================
    // 4. УДАЛЕНИЕ КОЛОНКИ (ПАРЫ)
    // ==========================================
    function bindDeleteScheduleBtn(btn) {
        btn.addEventListener('mouseenter', () => btn.style.opacity = '1');
        btn.addEventListener('mouseleave', () => btn.style.opacity = '0.6');
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const scheduleId = this.dataset.scheduleId;

            if (!confirm('Удалить эту дату (пару) и все оценки за нее?')) return;

            fetch(`/journal/schedule/${scheduleId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const th = document.querySelector(`th[data-schedule-id="${scheduleId}"]`);
                    if (th) th.remove();
                    document.querySelectorAll(`td[data-schedule-id="${scheduleId}"]`).forEach(td => td.remove());
                }
            })
            .catch(() => alert('Ошибка при удалении пары'));
        });
    }
    document.querySelectorAll('.delete-schedule-btn').forEach(bindDeleteScheduleBtn);

    // ==========================================
    // 5. ДОБАВЛЕНИЕ КОЛОНКИ (ПАРЫ)
    // ==========================================
    document.getElementById('btn-add-schedule').addEventListener('click', function() {
        const dateInput = document.getElementById('new-schedule-date');
        const pairInput = document.getElementById('new-schedule-pair');
        const typeInput = document.getElementById('new-schedule-type'); 
        
        const date = dateInput.value;
        const pair = pairInput.value;
        const typeId = typeInput.value;
        
        if (!date) { alert('Выберите дату!'); return; }

        this.disabled = true;
        this.innerText = '...';

        fetch(`/journal/{{ $group->id }}/{{ $subject->id }}/schedule`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({ date: date, pair_number: pair, lesson_type_id: typeId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const s = data.schedule;
                const sDate = s.date.split('T')[0]; 
                
                const dateObj = new Date(s.date);
                const dateStr = String(dateObj.getDate()).padStart(2, '0') + '.' + String(dateObj.getMonth() + 1).padStart(2, '0');
                
                const typeName = s.lesson_type ? s.lesson_type.name : '';

                const existingThs = Array.from(document.querySelectorAll('th.schedule-col'));
                let insertBeforeTh = document.getElementById('new-schedule-header');

                // УМНАЯ СОРТИРОВКА (Дата + Номер пары)
                for (let th of existingThs) {
                    const thDate = th.dataset.date;
                    const thPair = parseInt(th.dataset.pairNumber || 0);
                    
                    if (sDate < thDate || (sDate === thDate && parseInt(pair) < thPair)) {
                        insertBeforeTh = th;
                        break;
                    }
                }

                const th = document.createElement('th');
                th.className = 'schedule-col';
                th.dataset.scheduleId = s.id;
                th.dataset.date = sDate;
                th.dataset.pairNumber = s.pair_number; 
                th.style.minWidth = '100px';
                th.style.position = 'relative';
                th.style.verticalAlign = 'top';
                th.style.padding = '5px';
                th.innerHTML = `
                    ${dateStr}<br><small>${s.pair_number} пара</small><br>
                    <small class="schedule-type-text" style="color: #666; font-size: 10px;">${typeName}</small>
                    <select class="schedule-type-select" data-schedule-id="${s.id}" style="display: inline-block; width: 100%; font-size: 10px; border: 1px solid #ccc; margin-top: 2px;">
                        @foreach($lessonTypes as $type)
                            <option value="{{ $type->id }}" ${s.lesson_type_id == {{ $type->id }} ? 'selected' : ''}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="delete-schedule-btn" data-schedule-id="${s.id}" title="Удалить пару" style="display: inline-block; position: absolute; top: 2px; right: 2px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 10px; padding: 2px 5px; opacity: 0.6;">✖</button>
                `;
                
                insertBeforeTh.parentNode.insertBefore(th, insertBeforeTh);
                bindDeleteScheduleBtn(th.querySelector('.delete-schedule-btn'));
                
                // Привязываем событие изменения типа для новой колонки
                bindScheduleTypeSelect(th.querySelector('.schedule-type-select'));

                const colIndex = Array.from(insertBeforeTh.parentNode.children).indexOf(insertBeforeTh);

                document.querySelectorAll('#students-tbody tr[data-student-id]').forEach(tr => {
                    const studentId = tr.dataset.studentId;
                    const td = document.createElement('td');
                    td.dataset.scheduleId = s.id;
                    td.innerHTML = `<input type="text" name="marks[${studentId}][${s.id}]" style="width: 100%; border: none; text-align: center; font-size: 14px; font-weight: bold; background: #fff; outline: none;" placeholder="-">`;
                    tr.insertBefore(td, tr.children[colIndex]);
                });

                const nPlusOneRow = document.getElementById('new-student-row');
                const nPlusOneTd = document.createElement('td');
                nPlusOneTd.dataset.scheduleId = s.id;
                nPlusOneTd.style.background = '#f1f5f9';
                nPlusOneRow.insertBefore(nPlusOneTd, nPlusOneRow.children[colIndex]);

                dateInput.value = '';
            }
        })
        .catch(error => {
            console.error(error);
            alert('Ошибка при добавлении пары');
        })
        .finally(() => { this.disabled = false; this.innerText = '+ Добавить'; });
    });

    // ==========================================
    // 6. СОХРАНЕНИЕ ТИПА ПАРЫ (ЛЕКЦИЯ / ПРАКТИКА)
    // ==========================================
    function bindScheduleTypeSelect(select) {
        select.addEventListener('change', function() {
            const scheduleId = this.dataset.scheduleId;
            const typeId = this.value;

            fetch(`/journal/schedule/${scheduleId}/type`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ lesson_type_id: typeId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.style.backgroundColor = '#dcfce7';
                    setTimeout(() => this.style.backgroundColor = '', 500);
                    // Обновляем текст рядом, чтобы при отмене редактирования было правильное значение
                    const textEl = this.parentElement.querySelector('.schedule-type-text');
                    if (textEl) textEl.textContent = this.options[this.selectedIndex].text;
                }
            })
            .catch(() => alert('Ошибка при сохранении типа пары'));
        });
    }
    document.querySelectorAll('.schedule-type-select').forEach(bindScheduleTypeSelect);

    // ==========================================
    // 7. ДОБАВЛЕНИЕ СТУДЕНТА (С УЧЕТОМ СОРТИРОВКИ)
    // ==========================================
    const newStudentInput = document.getElementById('new-student-input');
    newStudentInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const fullName = this.value.trim();
            if (!fullName) return;

            this.disabled = true;
            this.placeholder = 'Добавление...';

            fetch('{{ route("journal.student.store", $group->id) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ full_name: fullName })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const s = data.student;
                    const tr = document.createElement('tr');
                    tr.dataset.studentId = s.id;

                    let html = `<td class="student-name" style="padding: 0;">
                        <div style="display: flex; align-items: center; width: 100%;">
                            <input type="text" class="student-name-input" data-student-id="${s.id}" value="${s.last_name} ${s.first_name} ${s.middle_name || ''}" style="flex-grow: 1; border: none; background: #fff; padding: 6px; font-weight: bold; outline: none; width: 100%;">
                            <button type="button" class="delete-student-btn" data-student-id="${s.id}" style="display: inline-block; background: #fee2e2; color: #dc2626; border: none; padding: 4px 8px; cursor: pointer; border-radius: 4px; margin-right: 4px; opacity: 0.6;">✖</button>
                        </div>
                    </td>
                    <td style="text-align: center; padding: 0;">
                        <input type="text" name="subgroups[${s.id}]" value="${s.subgroup || ''}" style="width: 100%; border: none; text-align: center; background: #fff;" placeholder="-">
                    </td>`;

                    document.querySelectorAll('th.schedule-col').forEach(th => {
                        const schedId = th.dataset.scheduleId;
                        html += `<td data-schedule-id="${schedId}"><input type="text" name="marks[${s.id}][${schedId}]" style="width: 100%; border: none; text-align: center; font-size: 14px; font-weight: bold; background: #fff; outline: none;" placeholder="-"></td>`;
                    });

                    html += `<td style="background: #f1f5f9;"></td>`;
                    tr.innerHTML = html;

                    bindNameInput(tr.querySelector('.student-name-input'));
                    bindDeleteStudentBtn(tr.querySelector('.delete-student-btn'));

                    const currentSort = '{{ request("sort", "name_asc") }}';
                    const nPlusOneRow = document.getElementById('new-student-row');
                    
                    if (currentSort === 'name_asc' || currentSort === 'name_desc') {
                        const newNameStr = `${s.last_name} ${s.first_name}`.toLowerCase();
                        const existingRows = Array.from(document.querySelectorAll('#students-tbody tr[data-student-id]'));
                        let inserted = false;

                        for (let row of existingRows) {
                            const rowNameStr = row.querySelector('.student-name-input').value.toLowerCase();
                            const isGreater = newNameStr.localeCompare(rowNameStr) === 1;
                            const isLess = newNameStr.localeCompare(rowNameStr) === -1;
                            
                            if ((currentSort === 'name_asc' && isLess) || (currentSort === 'name_desc' && isGreater)) {
                                row.parentNode.insertBefore(tr, row);
                                inserted = true;
                                break;
                            }
                        }
                        if (!inserted) nPlusOneRow.parentNode.insertBefore(tr, nPlusOneRow);

                    } else if (currentSort === 'id_desc') {
                        document.getElementById('students-tbody').prepend(tr);
                    } else {
                        nPlusOneRow.parentNode.insertBefore(tr, nPlusOneRow);
                    }

                    this.value = '';
                }
            })
            .catch(() => alert('Ошибка при добавлении студента'))
            .finally(() => { 
                this.disabled = false; 
                this.placeholder = '+ Введите ФИО и нажмите Enter...';
            });
        }
    });
});
</script>
@endsection