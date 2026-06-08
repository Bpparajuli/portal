<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;

class EnquiryController extends Controller
{
    public function create()
    {
        return view('guest.enquiry');
    }

    public function store(StoreEnquiryRequest $request)
    {
        Enquiry::create($request->validated());

        return redirect()->back()->with('success', 'Your enquiry has been submitted successfully. We will get back to you shortly.');
    }
}
