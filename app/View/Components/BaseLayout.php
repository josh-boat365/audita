<?php

namespace App\View\Components;

use App\Http\Controllers\ExceptionController;
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
        if(session('api_token') == null){
            return view('auth.auth-login')->with('toast_warning', 'Session expired. Please login again.');
        }
        $pending_exception_count =   session('pending_exception_count');
        $employeeId = ExceptionController::getLoggedInUserInformation()->id;
        $employeeRoleId = ExceptionController::getLoggedInUserInformation()->empRoleId;
        $employeeDepartmentId = ExceptionController::getLoggedInUserInformation()->departmentId;

        // top managers
        // 1 - Managing Director
        // 2 - Head of Internal Audit
        // 4 - Head of Internal Control & Compliance
        $topManagers = [1, 2, 4];
        $auditorDepartments = [7,8];


        return view('layouts.base', compact('pending_exception_count', 'employeeId', 'employeeRoleId', 'topManagers', 'auditorDepartments', 'employeeDepartmentId'));
    }
}
