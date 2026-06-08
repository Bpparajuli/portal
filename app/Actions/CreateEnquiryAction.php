<?php

namespace App\Actions;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateEnquiryAction
{
    public function __construct(
        private LogActivityAction $logActivity,
    ) {}

    public function execute(Request $request): Enquiry
    {
        $enquiry = Enquiry::create($request->validated());

        $this->logActivity->execute(
            type: 'enquiry_created',
            description: "Enquiry created by {$enquiry->name} ({$enquiry->email})",
            user: Auth::check() ? Auth::user() : null,
            notifiableId: $enquiry->id,
        );

        return $enquiry;
    }
}
