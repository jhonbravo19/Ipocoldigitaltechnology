<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules()
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'first_names' => 'required|string|max:255',
            'last_names' => 'required|string|max:255',
            'identification_type' => 'required|in:CC,CE,PA',
            'identification_number' => 'required|string|max:50',
            'identification_place' => 'required|string|max:255',
            'blood_type' => 'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'issue_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'has_drivers_license' => 'required|in:SI,NO',
            'drivers_license_category' => 'nullable|in:A1,A2,B1,B2,B3,C1,C2,C3',
        ];
    }
}
