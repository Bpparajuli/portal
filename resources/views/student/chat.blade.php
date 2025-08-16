@extends('layout.app')

@section('content')
<div class="p-2">
    <h3>Chat for Application #{{ $application->id }} - {{ $application->university->name }}</h3>

    <div class="border p-2 mb-2" style="height:300px; overflow-y:auto;">
        @foreach($application->chats as $chat)
        <p><strong>{{ $chat->user->name }}:</strong> {{ $chat->message }} <small>{{ $chat->created_at }}</small></p>
        @endforeach
    </div>

    <form action="{{ route('application.chat.store', $application->id) }}" method="POST">
        @csrf
        <input type="text" name="message" class="form-control mb-2" placeholder="Type message..." required>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
@endsection
