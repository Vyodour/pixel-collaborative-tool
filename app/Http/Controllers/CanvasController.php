<?php

namespace App\Http\Controllers;

use App\Models\Canvas;
use App\Models\Project;
use Illuminate\Http\Request;

class CanvasController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'width' => 'required|integer|min:8|max:128',
            'height' => 'required|integer|min:8|max:128',
            'background_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $canvas = $project->canvases()->create([
            'name' => $validated['name'],
            'width' => $validated['width'],
            'height' => $validated['height'],
            'background_color' => $validated['background_color'],
            'data' => json_encode(['layers' => []]),
        ]);

        return redirect()->route('canvases.show', $canvas);
    }

    public function show(Canvas $canvas)
    {
        return view('canvases.show', compact('canvas'));
    }
}
