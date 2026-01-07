@extends('layouts.app')

@section('title', 'Workspace: ' . $project->name)

@section('content')
<div class="space-y-6">
    <!-- Project Header -->
    <div class="bg-gray-800 border-4 border-white p-6 shadow-[8px_8px_0_0_rgba(0,0,0,0.5)]">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-3xl text-yellow-400 font-vt323">{{ strtoupper($project->name) }}</h2>
                <p class="text-gray-400 text-xs mt-1">WORKSPACE_ID: #{{ $project->id }} | OWNER: {{ $project->user->name }}</p>
            </div>
            <div class="flex gap-2">
                <div class="bg-gray-900 border-2 border-gray-600 p-2 text-center min-w-[200px]">
                    <span class="block text-[10px] text-gray-500 mb-1">INVITE LINK</span>
                    <input type="text" readonly value="{{ route('projects.join', $project->invite_code) }}" 
                           class="bg-transparent text-green-400 font-mono text-[10px] w-full text-center focus:outline-none" 
                           onclick="this.select()">
                </div>
            </div>
        </div>
    </div>

    <!-- Canvases Section -->
    <div class="bg-gray-800 border-4 border-white p-6 shadow-[8px_8px_0_0_rgba(0,0,0,0.5)]">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl text-blue-400 font-vt323">> CANVASES</h3>
            <x-atoms.button onclick="openCanvasModal()" class="py-2 px-4 text-xs max-w-[200px]">ADD NEW CANVAS</x-atoms.button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($project->canvases as $canvas)
                <a href="{{ route('canvases.show', $canvas) }}" class="bg-gray-900 border-2 border-gray-600 p-4 relative group hover:border-yellow-400 transition-all flex flex-col h-56">
                    <div class="w-full h-32 bg-gray-800 mb-3 flex items-center justify-center text-gray-600 font-pixel text-xs border border-gray-700">
                        @if($canvas->thumbnail_path)
                            <img src="{{ $canvas->thumbnail_path }}" class="w-full h-full object-cover pixelated">
                        @else
                            [CANVAS_EMPTY]
                        @endif
                    </div>
                    <h4 class="font-bold text-lg text-white truncate">{{ $canvas->name }}</h4>
                    <span class="text-[10px] text-gray-500 uppercase">{{ $canvas->width }}x{{ $canvas->height }} PX</span>
                </a>
            @empty
                <div class="col-span-full py-12 text-center border-2 border-dashed border-gray-600 text-gray-500">
                    NO CANVASES FOUND IN THIS WORKSPACE
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Create Canvas Modal -->
<div id="canvasModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50">
    <div class="bg-gray-800 border-4 border-white p-6 w-full max-w-md shadow-2xl relative">
        <h3 class="text-xl text-yellow-400 font-vt323 mb-4">> INITIALIZE_NEW_CANVAS</h3>
        
        <form action="{{ route('canvases.store', $project) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-400 mb-1">CANVAS NAME</label>
                <input type="text" name="name" class="w-full bg-gray-900 border-2 border-gray-600 p-2 text-white font-vt323 text-lg" required placeholder="Untitled Layer">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">WIDTH (PX)</label>
                    <input type="number" name="width" value="32" min="8" max="128" class="w-full bg-gray-900 border-2 border-gray-600 p-2 text-white font-vt323 text-lg">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">HEIGHT (PX)</label>
                    <input type="number" name="height" value="32" min="8" max="128" class="w-full bg-gray-900 border-2 border-gray-600 p-2 text-white font-vt323 text-lg">
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-400 mb-1">BASE COLOR</label>
                <input type="color" name="background_color" value="#ffffff" class="w-full h-10 bg-gray-900 border-2 border-gray-600 p-1 cursor-pointer">
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeCanvasModal()" class="px-4 py-2 text-xs bg-red-500 hover:bg-red-400 text-white font-bold border-b-4 border-red-700 active:border-b-0 active:translate-y-1">ABORT</button>
                <button type="submit" class="px-4 py-2 text-xs bg-blue-500 hover:bg-blue-400 text-white font-bold border-b-4 border-blue-700 active:border-b-0 active:translate-y-1">INITIALIZE</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCanvasModal() {
        document.getElementById('canvasModal').classList.remove('hidden');
    }
    function closeCanvasModal() {
        document.getElementById('canvasModal').classList.add('hidden');
    }
</script>
@endsection
