@php
    $toastPayloads = collect(\Illuminate\Support\Arr::wrap(session('toast')));

    if (session('status')) {
        $toastPayloads->push([
            'type' => 'success',
            'title' => 'Success',
            'message' => session('status') === 'verification-link-sent'
                ? 'A new verification link has been sent to the email address you provided during registration.'
                : (string) session('status'),
        ]);
    }

    if ($errors->any()) {
        $messages = $errors->all();

        $toastPayloads->push([
            'type' => 'danger',
            'title' => 'Action failed',
            'message' => $messages[0],
            'details' => count($messages) > 1 ? implode(' ', array_slice($messages, 1)) : null,
        ]);
    }
@endphp

{{-- @business-rule Transient site and admin feedback must be shown as toast notifications instead of inline status or error blocks. --}}
@if ($toastPayloads->isNotEmpty())
    <div hidden data-toast-payloads="@js($toastPayloads->values()->all(), JSON_THROW_ON_ERROR)"></div>
@endif
