<?php

namespace App\Listeners;

use App\Actions\LogActivityAction;
use App\Events\DocumentUploaded;
use Illuminate\Support\Facades\Auth;

class LogDocumentActivity
{
    public function handle(DocumentUploaded $event): void
    {
        app(LogActivityAction::class)->execute(
            'document_uploaded',
            "Document {$event->document->file_name} was uploaded for {$event->student->full_name}",
            Auth::user(),
            $event->student->id,
            route('admin.students.show', $event->student)
        );
    }
}
