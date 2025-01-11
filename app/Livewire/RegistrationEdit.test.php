<?php

use App\Livewire\RegistrationEdit;
use App\Models\Registration;
use Livewire\Livewire;


test('should successfully update the registration with valid end_status and comments', function () {
    $registration = Registration::factory()->create();

    Livewire::test(RegistrationEdit::class, ['registration' => $registration])
        ->set('end_status', 'Completed')
        ->set('comments', 'Test comment')
        ->call('saveChanges', $registration->id);

    $registration->refresh();

    expect($registration->end_status)->toBe('Completed');
    expect($registration->comments)->toBe('Test comment');
});

test('should set isEditing to false after successful update', function () {
    $registration = Registration::factory()->create();

    Livewire::test(RegistrationEdit::class, ['registration' => $registration])
        ->set('isEditing', true)
        ->set('end_status', 'Completed')
        ->set('comments', 'Test comment')
        ->call('saveChanges', $registration->id)
        ->assertSet('isEditing', false);
});

test('should dispatch registration-updated event after successful update', function () {
    $registration = Registration::factory()->create();

    Livewire::test(RegistrationEdit::class, ['registration' => $registration])
        ->set('end_status', 'Completed')
        ->set('comments', 'Test comment')
        ->call('saveChanges', $registration->id)
        ->assertDispatched('registration-updated');
});
