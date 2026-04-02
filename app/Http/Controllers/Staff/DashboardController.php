<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $columns = [
            ['title' => 'New Leads', 'color' => '#4e73df', 'id' => 'new'],
            ['title' => 'Follow Up', 'color' => '#f6c23e', 'id' => 'follow_up'],
            ['title' => 'Documentation', 'color' => '#36b9cc', 'id' => 'docs'],
            ['title' => 'Visa Processing', 'color' => '#1cc88a', 'id' => 'visa'],
            ['title' => 'Completed', 'color' => '#858796', 'id' => 'done'],
        ];

        // 20+ Realistic Records
        $leads = [];
        $names = ['Arjun Patel', 'Sarah Jenkins', 'Michael Chen', 'Elena Rodriguez', 'Kofi Mensah', 'Priya Sharma', 'David O’Connor', 'Yuki Tanaka', 'Ahmed Ali', 'Maria Garcia', 'Liam Wilson', 'Fatima Zahra', 'Hans Schmidt', 'Chloe Bennett', 'Ivan Petrov', 'Sofia Rossi', 'Zainab Abbas', 'Lucas Silva', 'Emily White', 'Oscar Isaac', 'Amara Okafor', 'Noah Williams'];

        foreach ($names as $key => $name) {
            $colIndex = $key % 5;
            $leads[] = [
                'id' => 1000 + $key,
                'name' => $name,
                'visa_type' => ($key % 3 == 0) ? 'Student Visa' : (($key % 3 == 1) ? 'Visitor Visa' : 'Work Permit'),
                'country' => ['Australia', 'Canada', 'UK', 'USA', 'Germany'][$key % 5],
                'status' => $columns[$colIndex]['title'],
                'border_class' => ($key % 3 == 0) ? 'b-student' : (($key % 3 == 1) ? 'b-visitor' : 'b-work'),
                'updated_at' => rand(1, 24) . ' hours ago'
            ];
        }

        return view('staff.dashboard', compact('columns', 'leads'));
    }
}
