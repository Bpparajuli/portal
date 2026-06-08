<?php

namespace App\Actions;

use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationStatusUpdated;
use App\Contracts\FileUploadServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateApplicationAction
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
        private LogActivityAction $logActivity,
        private NotifyUserAction $notifyUser,
    ) {}

    public function execute(Application $application, Request $request): Application
    {
        return DB::transaction(function () use ($application, $request) {
            $originalStatusId = $application->application_status_id;

            $data = $request->validated();

            if ($request->hasFile('sop_file')) {
                $path = $this->fileUploadService->uploadStudentSOP(
                    $request->file('sop_file'),
                    $application->agent,
                    $application->student,
                    $application->sop_file
                );
                $data['sop_file'] = $path;
            }

            $application->update($data);

            if ($application->application_status_id !== $originalStatusId) {
                $this->logActivity->execute(
                    type: 'application_status_updated',
                    description: "Application {$application->application_number} status updated to {$application->status->name}",
                    user: Auth::user(),
                    notifiableId: $application->id,
                    link: route('applications.show', $application->id)
                );

                if ($application->agent) {
                    $this->notifyUser->notifyUser(
                        $application->agent,
                        new ApplicationStatusUpdated($application->fresh(), Auth::user())
                    );
                }
            }

            return $application->fresh();
        });
    }
}
