<x-mail::message>
# {{ $email->subject }}

@if($isReply)
**Reply to: {{ $email->parent?->subject ?? 'previous message' }}**
@endif

{!! $email->body_html ?: nl2br(e($email->body)) !!}

---

Sent by **{{ $email->sender_name }}** ({{ $email->sender_email }})
</x-mail::message>
