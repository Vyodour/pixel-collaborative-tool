<button 
    {{ $attributes->merge(['type' => 'submit', 'class' => 'w-full bg-yellow-400 text-black font-bold py-3 px-4 border-b-4 border-yellow-600 hover:bg-yellow-300 active:border-b-0 active:translate-y-1 transition-all style="font-family: \'Press Start 2P\', cursive; font-size: 10px;"']) }}
>
    {{ $slot }}
</button>
