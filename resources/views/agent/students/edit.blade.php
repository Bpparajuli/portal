@extends('layouts.agent')

@section('agent-content')
<div class="create-form-wrapper">
    <h2 class="form-title">📝 Edit Student</h2>

    <form method="POST" action="{{ route('agent.students.update', $student) }}" enctype="multipart/form-data" class="card p-4 shadow-sm mt-3">
        @csrf
        @method('PUT')
        @include('agent.students.form')

        <div class="form-actions mt-3">
            <button type="submit" class="btn btn-success">💾 Save Student</button>
            <a href="{{ route('agent.students.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
