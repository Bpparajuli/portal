<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Application;
use App\Models\University;
use App\Models\User;
use App\Models\Course;

class ExportController extends Controller
{
    public function index()
    {
        return view('admin.exports');
    }

    public function export(Request $request)
    {
        $columns = $request->input('columns', []);
        if (empty($columns)) return back()->with('error', 'Please select at least one data type with columns.');

        $csv = '';
        $headerWritten = false;

        foreach ($columns as $type => $selectedCols) {
            if (empty($selectedCols)) continue;
            $rows = $this->getData($type, $selectedCols);
            if (!$headerWritten && !empty($rows)) {
                $csv .= $this->arrayToCsv(array_keys($rows[0]));
                $headerWritten = true;
            }
            foreach ($rows as $row) {
                $csv .= $this->arrayToCsv(array_values($row));
            }
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export_' . now()->format('Y-m-d_His') . '.csv"',
        ]);
    }

    private function getData(string $type, array $selectedCols): array
    {
        $data = [];

        switch ($type) {
            case 'students':
                $students = Student::with('agent')->get();
                foreach ($students as $s) {
                    $row = [];
                    foreach ($selectedCols as $col) {
                        $row[$col] = match ($col) {
                            'ID' => $s->id,
                            'First Name' => $s->first_name,
                            'Last Name' => $s->last_name,
                            'Full Name' => $s->full_name,
                            'Email' => $s->email ?? '',
                            'Phone' => $s->phone_number ?? '',
                            'Gender' => $s->gender ?? '',
                            'DOB' => $s->dob?->format('Y-m-d') ?? '',
                            'Nationality' => $s->nationality ?? '',
                            'Passport Number' => $s->passport_number ?? '',
                            'Passport Expiry' => $s->passport_expiry?->format('Y-m-d') ?? '',
                            'Marital Status' => $s->marital_status ?? '',
                            'Qualification' => $s->qualification ?? '',
                            'Passed Year' => (string)($s->passed_year ?? ''),
                            'Last Grades' => $s->last_grades ?? '',
                            'Education Board' => $s->education_board ?? '',
                            'Gap (years)' => (string)($s->gap ?? '0'),
                            'Preferred Country' => $s->preferred_country ?? '',
                            'Preferred City' => $s->preferred_city ?? '',
                            'Preferred Course' => $s->preferred_course ?? '',
                            'Preferred University' => $s->preferred_university ?? '',
                            'Agent' => $s->agent?->business_name ?? $s->agent?->name ?? '',
                            'Agent Email' => $s->agent?->email ?? '',
                            'Expected Revenue' => number_format((float)($s->expected_revenue ?? 0), 2),
                            'Received Revenue' => number_format((float)($s->received_revenue ?? 0), 2),
                            'Created Date' => $s->created_at?->format('Y-m-d') ?? '',
                            default => '',
                        };
                    }
                    $data[] = $row;
                }
                break;

            case 'applications':
                $apps = Application::with(['student', 'university', 'course', 'status', 'agent'])->get();
                foreach ($apps as $a) {
                    $row = [];
                    foreach ($selectedCols as $col) {
                        $row[$col] = match ($col) {
                            'ID' => $a->id,
                            'Application #' => $a->application_number ?? '',
                            'Student ID' => $a->student?->id ?? '',
                            'Student Name' => $a->student?->first_name . ' ' . $a->student?->last_name,
                            'Student Email' => $a->student?->email ?? '',
                            'Student Phone' => $a->student?->phone_number ?? '',
                            'University' => $a->university?->name ?? '',
                            'University Country' => $a->university?->country ?? '',
                            'University City' => $a->university?->city ?? '',
                            'Course' => $a->course?->title ?? '',
                            'Course Duration' => $a->course?->duration ?? '',
                            'Course Fee' => $a->course?->fee ?? '',
                            'Status' => $a->status?->name ?? '',
                            'Agent' => $a->agent?->business_name ?? $a->agent?->name ?? '',
                            'Agent Email' => $a->agent?->email ?? '',
                            'Created Date' => $a->created_at?->format('Y-m-d') ?? '',
                            'Updated Date' => $a->updated_at?->format('Y-m-d') ?? '',
                            default => '',
                        };
                    }
                    $data[] = $row;
                }
                break;

            case 'universities':
                $unis = University::all();
                foreach ($unis as $u) {
                    $row = [];
                    foreach ($selectedCols as $col) {
                        $row[$col] = match ($col) {
                            'ID' => $u->id,
                            'Name' => $u->name,
                            'Short Name' => $u->short_name ?? '',
                            'Country' => $u->country ?? '',
                            'City' => $u->city ?? '',
                            'Website' => $u->website ?? '',
                            'Email' => $u->email ?? '',
                            'Phone' => $u->phone ?? '',
                            'Description' => $u->description ?? '',
                            'Course Count' => (string)($u->courses_count ?? $u->courses()->count()),
                            'Created Date' => $u->created_at?->format('Y-m-d') ?? '',
                            default => '',
                        };
                    }
                    $data[] = $row;
                }
                break;

            case 'agents':
                $agents = User::where('role', 'agent')->get();
                foreach ($agents as $u) {
                    $row = [];
                    foreach ($selectedCols as $col) {
                        $row[$col] = match ($col) {
                            'ID' => $u->id,
                            'Name' => $u->name,
                            'Email' => $u->email,
                            'Business Name' => $u->business_name ?? '',
                            'Phone' => $u->phone ?? '',
                            'Status' => $u->active ? 'Active' : 'Inactive',
                            'Agreement Status' => $u->agreement_status ?? '',
                            'Student Count' => (string)($u->students_count ?? $u->students()->count()),
                            'Application Count' => (string)($u->applications_count ?? $u->applications()->count()),
                            'Created Date' => $u->created_at?->format('Y-m-d') ?? '',
                            default => '',
                        };
                    }
                    $data[] = $row;
                }
                break;

            case 'users':
                $users = User::all();
                foreach ($users as $u) {
                    $row = [];
                    foreach ($selectedCols as $col) {
                        $row[$col] = match ($col) {
                            'ID' => $u->id,
                            'Name' => $u->name,
                            'Email' => $u->email,
                            'Role' => $u->role,
                            'Business Name' => $u->business_name ?? '',
                            'Phone' => $u->phone ?? '',
                            'Status' => $u->active ? 'Active' : 'Inactive',
                            'Agreement Status' => $u->agreement_status ?? '',
                            'Student Count' => (string)($u->students_count ?? $u->students()->count()),
                            'Application Count' => (string)($u->applications_count ?? $u->applications()->count()),
                            'Created Date' => $u->created_at?->format('Y-m-d') ?? '',
                            default => '',
                        };
                    }
                    $data[] = $row;
                }
                break;

            case 'courses':
                $courses = Course::with('university')->get();
                foreach ($courses as $c) {
                    $row = [];
                    foreach ($selectedCols as $col) {
                        $row[$col] = match ($col) {
                            'ID' => $c->id,
                            'Title' => $c->title,
                            'University' => $c->university?->name ?? '',
                            'Level' => $c->level ?? '',
                            'Duration' => $c->duration ?? '',
                            'Fee' => $c->fee ?? '',
                            'Currency' => $c->currency ?? '',
                            'Intake' => $c->intake ?? '',
                            'Category' => $c->category ?? '',
                            'Type' => $c->type ?? '',
                            'Description' => $c->description ?? '',
                            'Created Date' => $c->created_at?->format('Y-m-d') ?? '',
                            default => '',
                        };
                    }
                    $data[] = $row;
                }
                break;
        }

        return $data;
    }

    private function arrayToCsv(array $fields): string
    {
        $escaped = array_map(function ($f) {
            $f = str_replace('"', '""', $f ?? '');
            if (str_contains($f, ',') || str_contains($f, '"') || str_contains($f, "\n")) {
                $f = '"' . $f . '"';
            }
            return $f;
        }, $fields);
        return implode(',', $escaped) . "\r\n";
    }
}
