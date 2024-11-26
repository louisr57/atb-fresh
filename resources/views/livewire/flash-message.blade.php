<div>
    @if($show)
    <div class="mb-4 p-4 rounded {{ $type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700' }}"
        role="alert">
        {{ $message }}
    </div>
    @endif
</div>