@props(['name' => 'o-home', 'class' => 'h-5 w-5'])

@switch($name)
    @case('o-home')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor" class="{{ $class }}">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 9.75L12 3l9 6.75v10.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 20.25V9.75z" />
        </svg>
        @break

    @case('o-cube')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor" class="{{ $class }}">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 2.25l9 5.25v9L12 21.75l-9-5.25v-9L12 2.25z" />
        </svg>
        @break

    @case('o-musical-note')
        🎵
        @break

    @case('o-cpu-chip')
        💻
        @break

    @case('o-clock')
        ⏰
        @break

    @case('o-user-group')
        👥
        @break

    @case('o-power')
        🔌
        @break

    @default
        ⚙️
@endswitch