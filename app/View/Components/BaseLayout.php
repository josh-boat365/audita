<?php

namespace App\View\Components;

use Illuminate\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Http;

class BaseLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        $accessToken = session('api_token');
        // Fetch user information
        $responseUser   = Http::withToken($accessToken)
            ->get('http://192.168.1.200:5124/HRMS/Employee/GetEmployeeInformation');

        // Handle responses
        $user = $responseUser->successful() ? $responseUser->object() : null;

        $responseDepartments = Http::withToken($accessToken)->get('http://192.168.1.200:5124/HRMS/Department');
        $responseRoles = Http::withToken($accessToken)->get('http://192.168.1.200:5124/HRMS/EmpRole');
        
        // dd($responseDepartments->object());

        $departments = collect($responseDepartments->object())->pluck('id')->toArray();
        $managers = collect($responseDepartments->object())->pluck('manager')->unique()->toArray();
        $roleManagers = collect($responseRoles->object())->pluck('manager')->unique()->toArray();
        // $roleManagers = dd(collect($responseDepartments->object()));
        // dd($managers, $roleManagers, collect($responseDepartments->object()) );
        // dd([
        //     'user'=>$user->id,
        //     'departments'=>$departments,
        //     'managers'=> $managers,
        //     'supervisors'=> $roleManagers,
        // ]);

        session([
            'user' => $user,
            'departments' => $departments,
            'managers' => $managers,
            'roleManagers' => $roleManagers,
        ]);




        return view('layouts.base', compact('user', 'departments', 'managers', 'roleManagers'));
    }
}
