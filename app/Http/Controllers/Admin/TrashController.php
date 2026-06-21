<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Application;
use App\Models\CrmTasks;
use App\Models\Document;
use App\Models\Emails;
use App\Models\StudentNote;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    protected array $models = [
        'student'            => ['class' => \App\Models\Student::class,            'label' => 'Students',        'icon' => 'fa-graduation-cap',    'title' => 'full_name'],
        'application'        => ['class' => \App\Models\Application::class,        'label' => 'Applications',     'icon' => 'fa-file-alt',          'title' => 'id'],
        'document'           => ['class' => \App\Models\Document::class,           'label' => 'Documents',        'icon' => 'fa-file',              'title' => 'name'],
        'course'             => ['class' => \App\Models\Course::class,             'label' => 'Courses',          'icon' => 'fa-book',              'title' => 'name'],
        'university'         => ['class' => \App\Models\University::class,         'label' => 'Universities',     'icon' => 'fa-university',        'title' => 'name'],
        'activity'           => ['class' => \App\Models\Activity::class,           'label' => 'Activities',       'icon' => 'fa-history',           'title' => 'description'],
        'application_status' => ['class' => \App\Models\ApplicationStatus::class,  'label' => 'Application Status','icon' => 'fa-tasks',             'title' => 'status'],
        'chat_message'       => ['class' => \App\Models\ChatMessage::class,        'label' => 'Chat Messages',    'icon' => 'fa-comments',          'title' => 'message'],
        'crm_task'           => ['class' => \App\Models\CrmTasks::class,           'label' => 'CRM Tasks',        'icon' => 'fa-tasks',             'title' => 'title'],
        'email'              => ['class' => \App\Models\Emails::class,             'label' => 'Emails',           'icon' => 'fa-envelope',          'title' => 'subject'],
        'student_note'       => ['class' => \App\Models\StudentNote::class,        'label' => 'Student Notes',    'icon' => 'fa-sticky-note',        'title' => 'note'],
        'student_stage'      => ['class' => \App\Models\StudentStage::class,       'label' => 'Student Stages',   'icon' => 'fa-layer-group',       'title' => 'name'],
    ];

    protected array $studentRelations = [
        'application'  => 'student',
        'document'     => 'student',
        'crm_task'     => 'student',
        'student_note' => 'student',
        'activity'     => 'student',
        'email'        => 'student',
    ];

    public function index()
    {
        $groups = [];
        foreach ($this->models as $type => $cfg) {
            $model = $cfg['class'];
            $query = $model::onlyTrashed()->orderBy('deleted_at', 'desc');

            // Eager-load student relationship where available
            if (isset($this->studentRelations[$type])) {
                $query->with($this->studentRelations[$type]);
            }

            $records = $query->get();
            if ($records->isNotEmpty()) {
                $groups[$type] = [
                    'label'   => $cfg['label'],
                    'icon'    => $cfg['icon'],
                    'records' => $records,
                    'title'   => $cfg['title'],
                    'count'   => $records->count(),
                    'has_student' => isset($this->studentRelations[$type]),
                ];
            }
        }

        return view('admin.trash.index', compact('groups'));
    }

    public function restore($modelType, $id)
    {
        $cfg = $this->models[$modelType] ?? abort(404);
        $cfg['class']::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.trash.index')
            ->with('success', 'Record restored successfully.');
    }

    public function forceDelete($modelType, $id)
    {
        $cfg = $this->models[$modelType] ?? abort(404);
        $cfg['class']::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.trash.index')
            ->with('success', 'Record permanently deleted.');
    }

    public function restoreAll($modelType)
    {
        $cfg = $this->models[$modelType] ?? abort(404);
        $count = $cfg['class']::onlyTrashed()->count();
        $cfg['class']::onlyTrashed()->restore();

        return redirect()->route('admin.trash.index')
            ->with('success', "{$count} " . strtolower($cfg['label']) . ' restored successfully.');
    }

    public function emptyTrash($modelType)
    {
        $cfg = $this->models[$modelType] ?? abort(404);
        $count = $cfg['class']::onlyTrashed()->count();
        $cfg['class']::onlyTrashed()->forceDelete();

        return redirect()->route('admin.trash.index')
            ->with('success', "{$count} " . strtolower($cfg['label']) . ' permanently deleted.');
    }
}
