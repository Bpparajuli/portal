<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function weeklyTrendData($userId = null): array
    {
        $labels = [];
        $applications = [];
        $students = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D, M d');

            $appQuery = Application::whereDate('created_at', $date);
            $stuQuery = Student::whereDate('created_at', $date);
            if ($userId) {
                $appQuery->where('agent_id', $userId);
                $stuQuery->where('agent_id', $userId);
            }
            $applications[] = $appQuery->count();
            $students[] = $stuQuery->count();
        }

        return compact('labels', 'applications', 'students');
    }

    public function applicationGrowth($userId = null): float
    {
        $now = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $currentMonthApps = $this->applicationQuery($userId)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $lastMonthApps = $this->applicationQuery($userId)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        if ($lastMonthApps == 0) {
            return $currentMonthApps > 0 ? 100 : 0;
        }

        return round((($currentMonthApps - $lastMonthApps) / $lastMonthApps) * 100, 1);
    }

    public function monthlyApplicationsChart($year = null, $userId = null): array
    {
        $year = $year ?: now()->year;

        $data = array_fill(0, 12, 0);
        $apps = $this->applicationQuery($userId)
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy(fn($app) => (int) $app->created_at->format('n'));

        foreach ($apps as $month => $items) {
            $data[$month - 1] = $items->count();
        }

        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [[
                'label' => 'Applications',
                'data' => $data,
                'borderColor' => '#820b5c',
                'backgroundColor' => 'rgba(130, 11, 92, 0.1)',
                'borderWidth' => 2,
                'pointBackgroundColor' => '#1a0262',
                'pointBorderColor' => '#fff',
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'tension' => 0.3,
                'fill' => true,
            ]],
        ];
    }

    public function applicationsByStatusChart($userId = null): array
    {
        $query = Application::join('application_statuses', 'applications.application_status_id', '=', 'application_statuses.id')
            ->select(
                'application_statuses.id',
                'application_statuses.name',
                'application_statuses.bg_color',
                'application_statuses.text_color',
                'application_statuses.sort_order',
                DB::raw('COUNT(applications.id) as count')
            )
            ->where('application_statuses.is_active', 1)
            ->groupBy(
                'application_statuses.id',
                'application_statuses.name',
                'application_statuses.bg_color',
                'application_statuses.text_color',
                'application_statuses.sort_order'
            )
            ->orderBy('application_statuses.sort_order');

        if ($userId) {
            $query->where('applications.agent_id', $userId);
        }

        $data = $query->get();

        $backgroundColors = $data->pluck('bg_color')->map(fn($color) => $color . 'cc')->toArray();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'datasets' => [[
                'label' => 'Applications',
                'data' => $data->pluck('count')->toArray(),
                'backgroundColor' => $backgroundColors,
                'borderColor' => '#fff',
                'borderWidth' => 2,
                'hoverOffset' => 8,
            ]],
            'statuses' => $data,
        ];
    }

    public function applicationsByUniversityChart($userId = null, $limit = 12): array
    {
        $query = DB::table('applications')
            ->join('universities', 'applications.university_id', '=', 'universities.id')
            ->select(
                'universities.short_name',
                'universities.name as full_name',
                DB::raw('COUNT(applications.id) as count')
            )
            ->groupBy('universities.short_name', 'universities.name')
            ->orderByDesc('count')
            ->limit($limit);

        if ($userId) {
            $query->where('applications.agent_id', $userId);
        }

        $data = $query->get();

        $labels = $data->pluck('short_name')->toArray();
        $counts = $data->pluck('count')->toArray();

        $baseColors = [
            '#1a0262', '#60a5fa', '#820b5c', '#facc15', '#22c55e',
            '#ef4444', '#8b5cf6', '#f97316', '#0ea5e9', '#16a34a',
            '#b91c1c', '#6b7280',
        ];

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Applications',
                'data' => $counts,
                'backgroundColor' => array_slice($baseColors, 0, count($labels)),
                'borderRadius' => 4,
                'barPercentage' => 0.7,
                'categoryPercentage' => 0.8,
            ]],
        ];
    }

    public function formatActivityDescription($activity, ?string $role = null): string
    {
        $role ??= request()->user()?->role ?? 'admin';
        $isAgent = $role === 'agent';
        $date = $activity->created_at ? $activity->created_at->format('M d, Y') : '';
        $agentName = $activity->user?->business_name ?? $activity->user?->name ?? 'System';

        $studentName = '';
        if ($activity->student_id) {
            $studentName = $activity->student?->full_name ?? $activity->student?->first_name ?? '';
        }
        if (!$studentName && $activity->notifiable_id) {
            if (in_array($activity->type, ['student_added','student_deleted','student_updated','document_uploaded','document_deleted','document_updated'])) {
                $studentName = \App\Models\Student::withTrashed()->find($activity->notifiable_id)?->full_name ?? '';
            } elseif (in_array($activity->type, ['application_submitted','application_withdrawn','application_status_changed','application_updated'])) {
                $app = \App\Models\Application::withTrashed()->with('student')->find($activity->notifiable_id);
                $studentName = $app?->student?->full_name ?? '';
            }
        }
        $docType = 'Document';
        if ($activity->document && $activity->document->document_type) {
            $docType = ucfirst(str_replace('_', ' ', $activity->document->document_type));
        } elseif (preg_match('/^(\w+)\s+uploaded\s+for\s+/i', $activity->description, $m)) {
            $docType = ucfirst(str_replace('_', ' ', $m[1]));
        } elseif (preg_match('/^(\w+)\s+deleted\s+for\s+/i', $activity->description, $m)) {
            $docType = ucfirst(str_replace('_', ' ', $m[1]));
        }

        return match ($activity->type) {
            'student_added' => $isAgent
                ? "New student <strong>{$studentName}</strong> added on {$date}"
                : "New student <strong>{$studentName}</strong> added by <strong>{$agentName}</strong> on {$date}",
            'student_updated' => $isAgent
                ? "Student <strong>{$studentName}</strong> updated on {$date}"
                : "Student <strong>{$studentName}</strong> updated by <strong>{$agentName}</strong> on {$date}",
            'student_deleted' => $isAgent
                ? "<strong>{$studentName}</strong> deleted on {$date}"
                : "<strong>{$studentName}</strong> deleted by <strong>{$agentName}</strong> on {$date}",
            'application_submitted' => $isAgent
                ? "New application for student <strong>{$studentName}</strong> added on {$date}"
                : "New application for student <strong>{$studentName}</strong> added by <strong>{$agentName}</strong> on {$date}",
            'application_updated' => $isAgent
                ? "Application for <strong>{$studentName}</strong> updated on {$date}"
                : "<strong>{$agentName}</strong> updated application for <strong>{$studentName}</strong> on {$date}",
            'application_status_changed' => $isAgent
                ? "Application status for <strong>{$studentName}</strong> changed from {$activity->old_value} to {$activity->new_value} on {$date}"
                : "<strong>{$agentName}</strong> changed application status for <strong>{$studentName}</strong> from {$activity->old_value} to {$activity->new_value} on {$date}",
            'application_withdrawn' => $isAgent
                ? "Application for <strong>{$studentName}</strong> withdrawn on {$date}"
                : "<strong>{$agentName}</strong> withdrew application for <strong>{$studentName}</strong> on {$date}",
            'document_uploaded' => $isAgent
                ? "{$docType} added for student <strong>{$studentName}</strong> on {$date}"
                : "{$docType} added for student <strong>{$studentName}</strong> by <strong>{$agentName}</strong> on {$date}",
            'document_updated' => $isAgent
                ? "{$docType} updated for <strong>{$studentName}</strong> on {$date}"
                : "{$docType} updated for <strong>{$studentName}</strong> by <strong>{$agentName}</strong> on {$date}",
            'document_deleted' => $isAgent
                ? "{$docType} deleted for <strong>{$studentName}</strong> on {$date}"
                : "{$docType} deleted for <strong>{$studentName}</strong> by <strong>{$agentName}</strong> on {$date}",
            default => $isAgent
                ? "Activity recorded on {$date}"
                : "<strong>{$agentName}</strong> performed {$activity->type} on {$date}",
        };
    }

    public function groupActivities($activities): \Illuminate\Support\Collection
    {
        $groups = collect();
        foreach ($activities as $act) {
            $dateKey = $act->created_at ? $act->created_at->format('Y-m-d') : 'unknown';
            $key = "{$act->type}|{$act->notifiable_id}|{$dateKey}";
            if ($groups->has($key)) {
                $existing = $groups->get($key);
                $existing['count']++;
                $existing['items'][] = $act;
                $existing['latest'] = $act->created_at;
            } else {
                $groups->put($key, [
                    'type' => $act->type,
                    'notifiable_id' => $act->notifiable_id,
                    'student' => $act->student,
                    'user' => $act->user,
                    'description' => $act->description,
                    'created_at' => $act->created_at,
                    'count' => 1,
                    'items' => [$act],
                    'latest' => $act->created_at,
                    'link' => $act->link ?? ($act['link'] ?? '#'),
                ]);
            }
        }
        return $groups->sortByDesc('latest')->values();
    }

    public function formatGroupedDescription(array $group, ?string $role = null): string
    {
        $role ??= request()->user()?->role ?? 'admin';
        $isAgent = $role === 'agent';
        $count = $group['count'];
        $date = $group['latest'] ? $group['latest']->format('M d, Y') : '';
        $agentName = $group['user']?->business_name ?? $group['user']?->name ?? 'System';

        $studentName = '';
        if ($group['student']) {
            $studentName = $group['student']->full_name ?? $group['student']->first_name ?? '';
        }
        if (!$studentName && $group['notifiable_id']) {
            if (in_array($group['type'], ['student_added','student_deleted','student_updated','document_uploaded','document_deleted','document_updated'])) {
                $studentName = \App\Models\Student::withTrashed()->find($group['notifiable_id'])?->full_name ?? '';
            } elseif (in_array($group['type'], ['application_submitted','application_withdrawn','application_status_changed','application_updated'])) {
                $app = \App\Models\Application::withTrashed()->with('student')->find($group['notifiable_id']);
                $studentName = $app?->student?->full_name ?? '';
            }
        }

        $desc = $group['description'] ?? '';
        $docType = 'Document';
        if (preg_match('/^(\w+)\s+uploaded\s+for\s+/i', $desc, $m)) {
            $docType = ucfirst(str_replace('_', ' ', $m[1]));
        } elseif (preg_match('/^(\w+)\s+deleted\s+for\s+/i', $desc, $m)) {
            $docType = ucfirst(str_replace('_', ' ', $m[1]));
        }

        $prefix = $count > 1 ? "{$count} " : '';

        return match ($group['type']) {
            'student_added' => $isAgent
                ? "{$prefix}New student <strong>{$studentName}</strong> added on {$date}"
                : "{$prefix}New student <strong>{$studentName}</strong> added by <strong>{$agentName}</strong> on {$date}",
            'student_updated' => $isAgent
                ? "{$prefix}Student <strong>{$studentName}</strong> updated on {$date}"
                : "{$prefix}Student <strong>{$studentName}</strong> updated by <strong>{$agentName}</strong> on {$date}",
            'student_deleted' => $isAgent
                ? "{$prefix}<strong>{$studentName}</strong> deleted on {$date}"
                : "{$prefix}<strong>{$studentName}</strong> deleted by <strong>{$agentName}</strong> on {$date}",
            'application_submitted' => $isAgent
                ? "{$prefix}New application for student <strong>{$studentName}</strong> added on {$date}"
                : "{$prefix}New application for student <strong>{$studentName}</strong> added by <strong>{$agentName}</strong> on {$date}",
            'application_updated' => $isAgent
                ? "{$prefix}Application for <strong>{$studentName}</strong> updated on {$date}"
                : "{$prefix}<strong>{$agentName}</strong> updated application for <strong>{$studentName}</strong> on {$date}",
            'application_status_changed' => $isAgent
                ? "{$prefix}Application status for <strong>{$studentName}</strong> changed on {$date}"
                : "{$prefix}<strong>{$agentName}</strong> changed application status for <strong>{$studentName}</strong> on {$date}",
            'application_withdrawn' => $isAgent
                ? "{$prefix}Application for <strong>{$studentName}</strong> withdrawn on {$date}"
                : "{$prefix}<strong>{$agentName}</strong> withdrew application for <strong>{$studentName}</strong> on {$date}",
            'document_uploaded' => $isAgent
                ? "{$prefix}New document uploaded for <strong>{$studentName}</strong> on {$date}"
                : "{$prefix}New document uploaded for <strong>{$studentName}</strong> by <strong>{$agentName}</strong> on {$date}",
            'document_deleted' => $isAgent
                ? "{$prefix}{$docType} deleted for <strong>{$studentName}</strong> on {$date}"
                : "{$prefix}{$docType} deleted for <strong>{$studentName}</strong> by <strong>{$agentName}</strong> on {$date}",
            default => $isAgent
                ? "{$prefix}Activity recorded on {$date}"
                : "{$prefix}<strong>{$agentName}</strong> performed activity on {$date}",
        };
    }

    private function applicationQuery($userId = null)
    {
        $query = Application::query();
        if ($userId) {
            $query->where('agent_id', $userId);
        }
        return $query;
    }
}
