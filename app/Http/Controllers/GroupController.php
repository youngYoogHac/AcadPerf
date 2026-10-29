<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View
    {
        $groups = Group::withCount('students')->get();
        return view('admin.groups.index', compact('groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:groups,name',
        ]);

        Group::create($validated);

        return back()->with('success', 'Группа успешно создана.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $group->delete();
        return back()->with('success', 'Группа удалена.');
    }
}