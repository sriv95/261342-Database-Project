{{-- Top-down badminton court, doubles, net at centre. Decorative. Props: $class, $arc (draw the shuttle arc) --}}
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1340 610" preserveAspectRatio="xMidYMid slice" aria-hidden="true" class="{{ $class ?? '' }}" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <g stroke-width="3" opacity=".22">
        <rect x="1.5" y="1.5" width="1337" height="607"/>
        <line x1="0" y1="46" x2="1340" y2="46"/>
        <line x1="0" y1="564" x2="1340" y2="564"/>
        <line x1="76" y1="0" x2="76" y2="610"/>
        <line x1="1264" y1="0" x2="1264" y2="610"/>
        <line x1="472" y1="46" x2="472" y2="564"/>
        <line x1="868" y1="46" x2="868" y2="564"/>
        <line x1="0" y1="305" x2="472" y2="305"/>
        <line x1="868" y1="305" x2="1340" y2="305"/>
    </g>
    <line x1="670" y1="0" x2="670" y2="610" stroke-width="5" opacity=".24"/>
    @if (! empty($arc))
        <path class="arc" pathLength="1" d="M700 440 C 800 -40, 1100 -40, 1140 424" stroke-width="3.5" opacity=".8"/>
        <g class="arc-shuttle" transform="translate(1140 424) rotate(-4)" fill="currentColor" stroke="none">
            <circle cx="0" cy="0" r="11"/>
            <path d="M-8 -6 L-22 -40 L22 -40 L8 -6 Z" opacity=".9"/>
        </g>
    @endif
</svg>
