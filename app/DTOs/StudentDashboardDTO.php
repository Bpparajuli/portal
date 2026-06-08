<?php
namespace App\DTOs;

class StudentDashboardDTO
{
    public function __construct(
        public readonly int $totalStudents,
        public readonly int $totalApplications,
        public readonly int $totalDocuments,
        public readonly float $completionRate,
        public readonly array $recentStudents = [],
        public readonly array $statusBreakdown = [],
    ) {}
}
