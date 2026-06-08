<?php
namespace App\ViewModels;

use App\DTOs\StudentDashboardDTO;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardViewModel
{
    public function __construct(
        public readonly User $user,
        public readonly StudentDashboardDTO $stats,
        public readonly Collection $recentActivities,
        public readonly array $monthlyData = [],
        public readonly ?string $welcomeMessage = null,
    ) {}

    public function isAdmin(): bool { return $this->user->is_admin; }
    public function isAgent(): bool { return $this->user->is_agent; }
    public function isStaff(): bool { return $this->user->is_staff; }
}
