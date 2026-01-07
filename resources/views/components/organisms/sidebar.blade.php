<aside class="w-64 bg-gray-800 border-r-4 border-white flex flex-col pt-6 pb-6 hidden md:flex shadow-[4px_0_0_0_rgba(0,0,0,0.5)]">
    <!-- Logo -->
    <div class="px-6 mb-8 text-center">
        <h1 class="text-2xl text-yellow-400 drop-shadow-[2px_2px_0_rgba(0,0,0,1)] leading-tight" style="font-family: 'Press Start 2P', cursive;">
            PIXEL<br>BOARD
        </h1>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 space-y-2">
        <h3 class="px-2 text-xs text-gray-400 font-bold mb-2 uppercase tracking-wider">Main Menu</h3>
        
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-gray-700 hover:text-green-400 cursor-pointer border-2 border-transparent hover:border-gray-500 transition-all group {{ request()->routeIs('dashboard') ? 'bg-gray-700 border-gray-500 text-green-400' : '' }}">
            <span class="text-xl group-hover:scale-110 transition-transform">📊</span>
            <span class="font-vt323 text-xl">DASHBOARD</span>
        </a>

        <a href="#" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-gray-700 hover:text-blue-400 cursor-pointer border-2 border-transparent hover:border-gray-500 transition-all group">
            <span class="text-xl group-hover:scale-110 transition-transform">🎨</span>
            <span class="font-vt323 text-xl">PROJECTS</span>
        </a>

        <a href="#" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-gray-700 hover:text-yellow-400 cursor-pointer border-2 border-transparent hover:border-gray-500 transition-all group">
            <span class="text-xl group-hover:scale-110 transition-transform">👥</span>
            <span class="font-vt323 text-xl">COMMUNITY</span>
        </a>
    </nav>

    <!-- User Info -->
    <div class="px-4 mt-auto">
        <div class="bg-gray-900 border-2 border-gray-600 p-4">
            <div class="text-xs text-gray-400 mb-1">LOGGED IN AS:</div>
            <div class="font-vt323 text-green-400 text-xl truncate">{{ Auth::user()->name ?? 'Guest' }}</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full text-left text-xs text-red-400 hover:text-red-300 hover:underline">
                    > LOGOUT_SYSTEM
                </button>
            </form>
        </div>
    </div>
</aside>
