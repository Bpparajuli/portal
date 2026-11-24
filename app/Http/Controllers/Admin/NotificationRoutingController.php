<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationRoutingController extends Controller
{
    private array $categories = [
        NotificationRouter::USERS,
        NotificationRouter::APPLICATIONS,
        NotificationRouter::STUDENTS,
        NotificationRouter::DOCUMENTS,
        NotificationRouter::ENQUIRIES,
        NotificationRouter::TASKS,
        NotificationRouter::REVENUE,
    ];

    public function index()
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $categories = $this->categories;

        // Staff belonging to an admin (or unparented staff).
        $staff = User::where('role', 'staff')
            ->where(function ($q) {
                $q->whereNull('parent_id')
                    ->orWhereHas('parent', fn ($p) => $p->whereIn('role', ['admin', 'superadmin']));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'business_name']);

        $routing = NotificationRouter::routing();

        // Lookup: [category][userId] => ['inapp'=>bool,'email'=>bool]
        $current = [];
        foreach ($routing as $category => $data) {
            foreach (($data['staff'] ?? []) as $row) {
                if (! empty($row['id'])) {
                    $current[$category][(int) $row['id']] = [
                        'inapp' => ! empty($row['inapp']),
                        'email' => ! empty($row['email']),
                    ];
                }
            }
        }

        return view('admin.notifications.routing', compact('categories', 'staff', 'current'));
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $staffInput = $request->input('staff', []);
        $routing = [];

        foreach ((array) $staffInput as $userId => $categories) {
            foreach ((array) $categories as $category => $channels) {
                if (! in_array($category, $this->categories, true)) {
                    continue;
                }

                $inapp = ! empty($channels['inapp']);
                $email = ! empty($channels['email']);

                if (! $inapp && ! $email) {
                    continue;
                }

                $routing[$category]['staff'][] = [
                    'id' => (int) $userId,
                    'inapp' => $inapp,
                    'email' => $email,
                ];
            }
        }

        Setting::setValue('notification_routing', $routing, 'email');

        return redirect()->route('admin.notifications.routing')->with('success', 'Notification routing saved.');
    }
}
