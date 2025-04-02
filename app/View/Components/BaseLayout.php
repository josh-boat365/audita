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
        $pending_exception_count =   session('pending_exception_count');
        $employeeId = ExceptionController::getLoggedInUserInformation()->id;


        return view('layouts.base', compact('pending_exception_count', 'employeeId'));
    }
}
