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
use Illuminate\Support\Facades\DB;

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
        'student_note'       => ['class' => \App\Models\StudentNote::class,        'label' => 'Student Notes',    'icon' => 'fa-sticky-note',        'title' => 'content'],
        'student_stage'      => ['class' => \App\Models\StudentStage::class,       'label' => 'Student Stages',   'icon' => 'fa-layer-group',       'title' => 'name'],
    ];

    protected array $studentRelations = [
        'application'  => 'student',
        'document'     => 'student',
        'crm_task'     => 'student',
        'student_note' => 'student',
        'activity'     => 'student',
    ];

    protected array $deletedByMapping = [
        'student' => ['activity_type' => 'student_deleted', 'foreign_key' => 'notifiable_id'],
    ];

    public function index()
    {
        $groups = [];
        foreach ($this->models as $type => $cfg) {
            $model = $cfg['class'];
            $query = $model::onlyTrashed()->orderBy('deleted_at', 'desc');

            if (isset($this->studentRelations[$type])) {
                $query->with($this->studentRelations[$type]);
            }

            $records = $query->get();

            if ($records->isNotEmpty()) {
                if (isset($this->deletedByMapping[$type])) {
                    $map = $this->deletedByMapping[$type];
                    $ids = $records->pluck('id')->toArray();
                    $deletedByDebug = ['type' => $type, 'ids' => $ids, 'rows' => 0, 'nameMap' => []];
                    $nameMap = [];
                    try {
                        $rows = DB::table('activities')
                            ->where('type', $map['activity_type'])
                            ->whereIn($map['foreign_key'], $ids)
                            ->get();
                        $deletedByDebug['rows'] = $rows->count();
                        $userIds = $rows->pluck('user_id')->filter()->unique()->toArray();
                        $users = [];
                        if (!empty($userIds)) {
                            $userRows = DB::table('users')->whereIn('id', $userIds)->get();
                            foreach ($userRows as $u) {
                                $users[$u->id] = $u->name;
                            }
                        }
                        foreach ($rows as $r) {
                            $fk = $r->{$map['foreign_key']};
                            $nameMap[$fk] = $users[$r->user_id] ?? 'Unknown';
                            $deletedByDebug['nameMap'][$fk] = $nameMap[$fk];
                        }
                    } catch (\Exception $e) {
                        $deletedByDebug['error'] = $e->getMessage();
                    }
                    $groups['_debug'] = $deletedByDebug;
                    foreach ($records as $record) {
                        $record->setAttribute('deleted_by_name', $nameMap[$record->id] ?? '—');
                    }
                } else {
                    foreach ($records as $record) {
                        $record->setAttribute('deleted_by_name', '—');
                    }
                }

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

    public function bulkRestore(Request $request)
    {
        $ids = $request->input('ids', []);
        $modelType = $request->input('model_type');
        $cfg = $this->models[$modelType] ?? abort(404);

        if (empty($ids)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'No items selected.'], 400)
                : redirect()->back()->with('error', 'No items selected.');
        }

        $count = $cfg['class']::onlyTrashed()->whereIn('id', $ids)->restore();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "{$count} " . strtolower($cfg['label']) . ' restored.']);
        }
        return redirect()->route('admin.trash.index')->with('success', "{$count} " . strtolower($cfg['label']) . ' restored.');
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $modelType = $request->input('model_type');
        $cfg = $this->models[$modelType] ?? abort(404);

        if (empty($ids)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'No items selected.'], 400)
                : redirect()->back()->with('error', 'No items selected.');
        }

        $count = $cfg['class']::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "{$count} " . strtolower($cfg['label']) . ' permanently deleted.']);
        }
        return redirect()->route('admin.trash.index')->with('success', "{$count} " . strtolower($cfg['label']) . ' permanently deleted.');
    }
}
