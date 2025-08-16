@extends('layout.app')

@section('content')
<div class="p-2">
    <h3>Student Details: {{ $student->first_name }} {{ $student->last_name }}</h3>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <ul class="nav nav-tabs mb-3" id="studentTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab">Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="applications-tab" data-bs-toggle="tab" href="#applications" role="tab">Applications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="documents-tab" data-bs-toggle="tab" href="#documents" role="tab">Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="embassy-tab" data-bs-toggle="tab" href="#embassy" role="tab">Embassy Docs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="chat-tab" data-bs-toggle="tab" href="#chat" role="tab">Chat</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="notes-tab" data-bs-toggle="tab" href="#notes" role="tab">Notes</a>
        </li>
    </ul>

    <div class="tab-content" id="studentTabsContent">
        <!-- Overview -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <table class="table table-bordered">
                <tr>
                    <th>DOB</th>
                    <td>{{ $student->dob }}</td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td>{{ $student->gender }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $student->email }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $student->phone_number }}</td>
                </tr>
                <tr>
                    <th>Preferred Country</th>
                    <td>{{ $student->preferred_country }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ ucfirst($student->student_status) }}</td>
                </tr>
            </table>
            <a href="{{ route('student.edit', $student->id) }}" class="btn btn-warning">Edit Student</a>
            <a href="{{ route('student.apply', $student->id) }}" class="btn btn-success">Apply Now</a>
        </div>

        <!-- Applications -->
        <div class="tab-pane fade" id="applications" role="tabpanel">
            <h5>Applications</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>University</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Submitted On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->applications as $app)
                    <tr>
                        <td>{{ $app->university->name }}</td>
                        <td>{{ $app->course->title }}</td>
                        <td>{{ ucfirst($app->application_status) }}</td>
                        <td>{{ $app->created_at }}</td>
                        <td>
                            <a href="{{ route('application.documents', $app->id) }}" class="btn btn-sm btn-info">Documents</a>
                            <a href="{{ route('application.chat', $app->id) }}" class="btn btn-sm btn-secondary">Chat</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Documents -->
        <div class="tab-pane fade" id="documents" role="tabpanel">
            <h5>Uploaded Documents (All Applications)</h5>
            @foreach($student->applications as $app)
            <h6>{{ $app->university->name }} - {{ $app->course->title }}</h6>
            <ul>
                @foreach($app->documents as $doc)
                <li>
                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank">{{ $doc->file_name }}</a>
                    uploaded by {{ $doc->uploaded_by_user->name ?? 'N/A' }}
                </li>
                @endforeach
            </ul>
            @endforeach
        </div>

        <!-- Embassy Docs -->
        <div class="tab-pane fade" id="embassy" role="tabpanel">
            <h5>Embassy Documents (After Acceptance)</h5>
            @foreach($student->applications as $app)
            @if($app->application_status == 'approved')
            <h6>{{ $app->university->name }} - {{ $app->course->title }}</h6>
            <ul>
                @foreach($app->embassy_documents as $doc)
                <li><a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank">{{ $doc->file_name }}</a></li>
                @endforeach
            </ul>
            @endif
            @endforeach
        </div>

        <!-- Chat -->
        <div class="tab-pane fade" id="chat" role="tabpanel">
            <h5>Application Chat</h5>
            @foreach($student->applications as $app)
            <h6>{{ $app->university->name }} - {{ $app->course->title }}</h6>
            <div class="border p-2 mb-2" style="height:200px; overflow-y:auto;">
                @foreach($app->chats as $chat)
                <p><strong>{{ $chat->user->name }}:</strong> {{ $chat->message }} <small>{{ $chat->created_at }}</small></p>
                @endforeach
            </div>
            <form action="{{ route('application.chat.store', $app->id) }}" method="POST">
                @csrf
                <input type="text" name="message" class="form-control mb-2" placeholder="Type message...">
                <button type="submit" class="btn btn-sm btn-primary">Send</button>
            </form>
            @endforeach
        </div>

        <!-- Notes -->
        <div class="tab-pane fade" id="notes" role="tabpanel">
            <h5>Notes</h5>
            <ul>
                @foreach($student->notes as $note)
                <li>{{ $note->content }} - <small>{{ $note->created_at }}</small></li>
                @endforeach
            </ul>
        </div>

    </div>
</div>
@endsection
