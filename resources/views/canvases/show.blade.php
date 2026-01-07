@extends('layouts.app')

@section('title', 'Editor: ' . $canvas->name)

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col">
    <!-- Editor Header -->
    <div class="mb-4 flex justify-between items-center bg-gray-800 p-3 border-4 border-white shadow-[4px_4px_0_0_rgba(0,0,0,0.5)]">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.show', $canvas->project_id) }}" class="text-gray-400 hover:text-white font-vt323 text-xl">< BACK</a>
            <h2 class="text-green-400 font-vt323 text-2xl">
                FILE: {{ strtoupper($canvas->name) }} [{{ $canvas->width }}x{{ $canvas->height }}]
            </h2>
        </div>
        
        <div class="flex gap-3">
            <div id="connection-status" class="flex items-center gap-2 px-3 py-1 bg-gray-900 border-2 border-gray-600">
                <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                <span class="text-[10px] text-gray-400 font-mono uppercase">Offline</span>
            </div>
            <x-atoms.button class="py-1 px-4 text-xs bg-blue-600 border-blue-800 hover:bg-blue-500">SAVE_SYSTEM</x-atoms.button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex gap-4 overflow-hidden">
        <!-- Tools Palette (L) -->
        <div class="w-16 bg-gray-800 border-4 border-white p-2 flex flex-col gap-2 shadow-[4px_4px_0_0_rgba(0,0,0,0.5)]">
            <button class="w-full aspect-square bg-gray-700 border-2 border-white flex items-center justify-center hover:bg-gray-600 active:translate-y-0.5" title="Pencil">✏️</button>
            <button class="w-full aspect-square bg-gray-700 border-2 border-transparent flex items-center justify-center hover:bg-gray-600" title="Eraser">🧹</button>
            <button class="w-full aspect-square bg-gray-700 border-2 border-transparent flex items-center justify-center hover:bg-gray-600" title="Fill">🪣</button>
            <div class="mt-auto border-t-2 border-gray-600 pt-2">
                <div class="w-full aspect-square bg-white border-2 border-black" id="active-color"></div>
            </div>
        </div>

        <!-- Canvas Area (C) -->
        <div class="flex-1 bg-gray-900 border-4 border-white shadow-inner flex items-center justify-center p-8 overflow-auto relative">
            <div id="app">
                 <pixel-canvas 
                    :project-id="{{ $canvas->project_id }}" 
                    :canvas-id="{{ $canvas->id }}"
                    :width="{{ $canvas->width }}" 
                    :height="{{ $canvas->height }}"
                    bg-color="{{ $canvas->background_color }}"
                 ></pixel-canvas>
            </div>
        </div>

        <!-- Layers/Info (R) -->
        <div class="w-64 bg-gray-800 border-4 border-white p-4 flex flex-col gap-4 shadow-[4px_4px_0_0_rgba(0,0,0,0.5)]">
            <h4 class="text-sm font-vt323 text-yellow-400 border-b-2 border-gray-600 pb-1">LAYERS</h4>
            <div class="flex-1 overflow-y-auto space-y-2">
                <div class="bg-gray-700 p-2 border-2 border-blue-400 flex justify-between items-center">
                    <span class="text-xs">Background</span>
                    <span class="text-[10px]">👁️</span>
                </div>
            </div>
            
            <div class="mt-auto border-t-2 border-gray-600 pt-4">
                <h4 class="text-xs text-gray-500 mb-2">PARTICIPANTS</h4>
                <div class="flex -space-x-2">
                    <div class="w-6 h-6 bg-green-500 border-2 border-white rounded-sm flex items-center justify-center text-[10px]" title="{{ auth()->user()->name }}">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
