{{-- Usage: <x-breadcrumbs :items="['Dashboard' => route('dashboard'), 'Students' => route('students.index'), 'Edit' => '']" />
     The last entry's url may be '' — it always renders as the non-link "current page" item regardless. --}}
@props(['items' => []])

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        @php $keys = array_keys($items); $lastKey = end($keys); @endphp
        @foreach ($items as $label => $url)
            @if ($label === $lastKey || $url === '')
                <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
            @else
                <li class="breadcrumb-item"><a href="{{ $url }}" wire:navigate>{{ $label }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
