@extends('layouts.app')

@section('page-title', $title ?? 'Admin Dashboard')
@section('title', 'Admin | ' . ($title ?? 'Dashboard'))

@section('content')
    @yield('admin-content')
@endsection

@push('scripts')
<script>
tinymce.init({
    selector: '.tinymce, textarea.wysiwyg, .editor-textarea',
    height: 300,
    menubar: false,
    plugins: 'lists link image preview code table emoticons',
    toolbar: 'undo redo | bold italic underline strikethrough | forecolor backcolor | bullist numlist | outdent indent | alignleft aligncenter alignright | link image | table emoticons | code',
    branding: false,
    promotion: false,
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; }',
    relative_urls: false,
    remove_script_host: false,
    setup: function (editor) {
        editor.on('change', function () { editor.save(); });
    }
});
</script>
@endpush
