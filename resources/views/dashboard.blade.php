@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-gray-800 border-4 border-white p-6 shadow-[8px_8px_0_0_rgba(0,0,0,0.5)]">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl text-blue-400 font-vt323">> DASHBOARD_SYSTEM</h2>
        <div class="text-xs text-gray-500">v1.0.0</div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- New Project Card (Trigger Modal) -->
        <div onclick="openCreateModal()" class="bg-gray-700 border-2 border-dashed border-gray-500 p-6 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-600 hover:border-white transition-all group h-64">
            <div class="text-6xl text-gray-400 group-hover:text-yellow-400 mb-4 font-vt323">+</div>
            <span class="font-vt323 text-2xl text-gray-300 group-hover:text-white">NEW CANVAS</span>
        </div>

        <!-- Project List -->
        @foreach($projects as $project)
        <a href="{{ route('projects.show', $project) }}" class="bg-gray-900 border-2 border-gray-600 p-4 relative group hover:border-blue-400 transition-all flex flex-col h-64">
            <div class="w-full h-40 bg-gray-800 mb-4 flex items-center justify-center text-gray-600 font-pixel text-xs border border-gray-700">
                @if($project->thumbnail_path)
                    <img src="{{ $project->thumbnail_path }}" class="w-full h-full object-cover pixelated">
                @else
                    [NO_PREVIEW]
                @endif
            </div>
            <h3 class="font-bold text-lg text-white truncate mb-1">{{ $project->name }}</h3>
            <div class="mt-auto flex justify-between items-end">
                <span class="text-xs text-gray-500">{{ $project->updated_at->diffForHumans() }}</span>
                <span class="text-xs bg-green-900 text-green-400 px-2 py-1">{{ $project->width }}x{{ $project->height }}</span>
            </div>
        </a>
        @endforeach
    </div>
</div>

<!-- Create Project Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50">
    <div class="bg-gray-800 border-4 border-white p-6 w-full max-w-md shadow-2xl relative">
        <h3 class="text-xl text-yellow-400 font-vt323 mb-4">> CREATE_NEW_PROJECT</h3>
        
        <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-400 mb-1">PROJECT NAME</label>
                <input type="text" name="name" class="w-full bg-gray-900 border-2 border-gray-600 p-2 text-white font-vt323 text-lg" placeholder="My Pixel Workspace" required>
            </div>
            
            <div>
                <label class="inline-flex items-center cursor-pointer mt-2">
                    <input type="checkbox" name="is_public" value="1" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-xs font-medium text-gray-400">PUBLIC PROJECT?</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-xs bg-red-500 hover:bg-red-400 text-white font-bold border-b-4 border-red-700 active:border-b-0 active:translate-y-1">CANCEL</button>
                <button type="submit" class="px-4 py-2 text-xs bg-green-500 hover:bg-green-400 text-white font-bold border-b-4 border-green-700 active:border-b-0 active:translate-y-1">CREATE WORKSPACE</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }
</script>
@endsection
