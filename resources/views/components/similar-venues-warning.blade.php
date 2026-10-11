{{-- Soft warning shown when the venue being saved looks like an existing one. --}}
@props(['form'])
@if (session('similar_venues'))
<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-4 rounded" role="alert">
    <p class="font-semibold">This venue looks similar to {{ count(session('similar_venues')) === 1 ? 'an existing venue' : 'existing venues' }}:</p>
    <ul class="list-disc ml-5 mt-2">
        @foreach (session('similar_venues') as $match)
        <li>
            <a href="{{ route('venues.show', $match['id']) }}" target="_blank" class="underline">{{ $match['name'] }}</a>
            ({{ $match['city'] }}) &mdash; {{ implode('; ', $match['reasons']) }}
        </li>
        @endforeach
    </ul>
    <p class="mt-2 text-sm">
        Duplicate venues weaken the double-booking check, because the same room would appear free under two names.
        Different rooms in the same building should be separate venues with names that say which room
        (e.g. "Auromira &ndash; Main Hall").
    </p>
    <label class="flex items-start gap-2 mt-2">
        <input type="checkbox" name="confirm_not_duplicate" value="1" form="{{ $form }}" class="mt-1">
        <span>I have checked: this is a different venue / room. Save it anyway.</span>
    </label>
</div>
@endif
