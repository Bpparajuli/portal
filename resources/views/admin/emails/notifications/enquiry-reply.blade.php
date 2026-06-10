@component('mail::message')
# Hello {{ $enquiry->name }},

Thank you for reaching out to us. Here is our response to your enquiry:

**Subject:** {{ $enquiry->subject }}

**Your Message:**
> {{ $enquiry->message }}

**Our Reply:**
{{ $enquiry->reply_message }}

@component('mail::button', ['url' => config('app.url')])
Visit Our Portal
@endcomponent

Best regards,<br>
{{ config('app.name') }}
@endcomponent
