<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ClassType;
use App\Models\MyClass;
use App\Models\Grade;
use Illuminate\Http\Request;

class LevelsController extends Controller
{
    public function index()
    {
        $levels = ClassType::orderBy('name')->withCount('myClasses')->get();
        return view('pages.super_admin.levels.index', compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = trim($request->name);
        $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 10)) ?: 'L' . (ClassType::max('id') + 1);
        $base = $code;
        $i = 0;
        while (ClassType::where('code', $code)->exists()) {
            $code = $base . (++$i);
        }

        ClassType::create([
            'name' => $name,
            'code' => $code,
        ]);

        return redirect()->route('levels.index')->with('flash_success', 'Level added successfully.');
    }

    public function update(Request $request, $level_id)
    {
        $level = ClassType::findOrFail($level_id);

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $level->update(['name' => trim($request->name)]);

        return redirect()->route('levels.index')->with('flash_success', 'Level updated successfully.');
    }

    public function destroy($level_id)
    {
        $level = ClassType::findOrFail($level_id);

        $classesCount = MyClass::where('class_type_id', $level_id)->count();
        $gradesCount = Grade::where('class_type_id', $level_id)->count();

        if ($classesCount > 0 || $gradesCount > 0) {
            return back()->with('flash_danger', 'This level cannot be deleted because it is in use (Classes: ' . $classesCount . ', Grades: ' . $gradesCount . '). Remove or reassign them first.');
        }

        $level->delete();
        return redirect()->route('levels.index')->with('flash_success', 'Level deleted successfully.');
    }
}
