<?php

namespace App\Events;

use App\Models\Document;
use App\Models\Student;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Document $document;
    public Student $student;

    public function __construct(Document $document, Student $student)
    {
        $this->document = $document;
        $this->student = $student;
    }
}
