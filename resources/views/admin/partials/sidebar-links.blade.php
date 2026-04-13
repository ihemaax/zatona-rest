@foreach($links as $link)
    <a href="{{ $link['url'] }}" class="sb-sublink {{ !empty($link['active']) ? 'active' : '' }}">
        <span class="sb-sublink-dot"></span>
        <span>{{ $link['label'] }}</span>
        @if(array_key_exists('badge', $link))
            <span class="sb-badge" @if(!empty($link['badge_id'])) id="{{ $link['badge_id'] }}" @endif>
                {{ $link['badge'] }}
            </span>
        @endif
    </a>
@endforeach
