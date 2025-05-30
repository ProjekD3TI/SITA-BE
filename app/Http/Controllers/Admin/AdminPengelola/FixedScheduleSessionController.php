<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminPengelola\FixedScheduleSession;

class FixedScheduleSessionController extends Controller
{
    public function getFixedScheduleSession(Request $request) {
        $query = FixedScheduleSession::query();

    }
}
