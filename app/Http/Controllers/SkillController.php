<?php

namespace App\Http\Controllers;

use App\Models\UserSkill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    /**
     * Display a listing of the user's skills.
     */
    public function index()
    {
        $user = Auth::user();
        $skills = $user->userSkills()->orderBy('level', 'desc')->orderBy('name')->get();
        
        return view('skills.index', compact('skills'));
    }

    /**
     * Show the form for creating a new skill.
     */
    public function create()
    {
        return view('skills.create');
    }

    /**
     * Store a newly created skill.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:user_skills,name,NULL,id,user_id,' . Auth::id(),
            'level' => 'required|integer|min:1|max:5',
        ]);

        $skill = UserSkill::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'level' => $validated['level'],
        ]);

        return redirect()->route('skills.index')
            ->with('success', 'Skill added successfully!');
    }

    /**
     * Show the form for editing the specified skill.
     */
    public function edit($id)
    {
        $skill = UserSkill::where('user_id', Auth::id())->findOrFail($id);
        
        return view('skills.edit', compact('skill'));
    }

    /**
     * Update the specified skill.
     */
    public function update(Request $request, $id)
    {
        $skill = UserSkill::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:user_skills,name,' . $id . ',id,user_id,' . Auth::id(),
            'level' => 'required|integer|min:1|max:5',
        ]);

        $skill->update($validated);

        return redirect()->route('skills.index')
            ->with('success', 'Skill updated successfully!');
    }

    /**
     * Remove the specified skill.
     */
    public function destroy($id)
    {
        $skill = UserSkill::where('user_id', Auth::id())->findOrFail($id);
        $skill->delete();

        return redirect()->route('skills.index')
            ->with('success', 'Skill deleted successfully!');
    }

    /**
     * Get skills data for API endpoints.
     */
    public function apiIndex()
    {
        $user = Auth::user();
        $skills = $user->userSkills()
            ->orderBy('level', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'level']);

        return response()->json($skills);
    }

    /**
     * Search for skills by name.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $skills = UserSkill::where('name', 'like', '%' . $query . '%')
            ->with('user:id,name')
            ->orderBy('level', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'level', 'user_id']);

        return response()->json($skills);
    }

    /**
     * Get popular skills across all users.
     */
    public function popular()
    {
        $skills = UserSkill::select('name')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('AVG(level) as avg_level')
            ->groupBy('name')
            ->orderBy('count', 'desc')
            ->limit(20)
            ->get();

        return response()->json($skills);
    }
}
