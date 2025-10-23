<?php

namespace App\View\Components;

use App\Http\Controllers\ExceptionManipulationController;
use Illuminate\View\View;
use Illuminate\View\Component;

class BaseLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        if(session('api_token') !== null){


            $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;
            // dd($employeeId);
            $employeeRoleId = ExceptionManipulationController::getLoggedInUserInformation()->empRoleId;
            $employeeDepartmentId = ExceptionManipulationController::getLoggedInUserInformation()->departmentId;

            $response = ExceptionManipulationController::getPendingExceptions($employeeId);
            $pending_exception_count =   collect($response)->count();

            // top managers
            // 1 - Managing Director
            // 2 - Head of Internal Audit
            // 4 - Head of Internal Control & Compliance
            $topManagers = [1, 2, 4];
            $auditorDepartments = [7, 8];


            return view('layouts.base', compact('pending_exception_count', 'employeeId', 'employeeRoleId', 'topManagers', 'auditorDepartments', 'employeeDepartmentId'));
        }else{
            return view('auth.auth-login')->with('toast_warning', 'Session expired. Please login again.');

        }



    }
}
