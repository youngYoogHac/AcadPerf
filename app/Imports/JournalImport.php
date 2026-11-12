<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\Import; // ← ДОБАВИТЬ ЭТУ СТРОКУ

// ← ДОБАВИТЬ Import в конце
class JournalImport implements WithMultipleSheets, SkipsUnknownSheets, Import 
{
    protected $group;
    protected $subject;
    
    public $savedCount = 0;
    public $skippedStudents = [];
    public $skippedDates = [];
    public $skippedMarks = [];
    public $sheetFound = false;

    public function __construct($group, $subject)
    {
        $this->group = $group;
        $this->subject = $subject;
    }

    public function sheets(): array
    {
        $sheetName = trim($this->group->name);
        return [
            $sheetName => new JournalSheetImport($this->group, $this->subject, $this)
        ];
    }

    public function onUnknownSheet(string|int $sheetName): void
    {
        // Игнорируем остальные листы
    }
}