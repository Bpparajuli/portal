<?php
namespace App\DTOs;

class ApplicationListDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $applicationNumber,
        public readonly string $studentName,
        public readonly string $university,
        public readonly ?string $course,
        public readonly string $status,
        public readonly string $statusColor,
        public readonly string $createdAt,
    ) {}
}
