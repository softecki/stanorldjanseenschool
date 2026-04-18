<?php

namespace App\Http\Controllers\Attendance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Repositories\RoleRepository;
use App\Models\StudentAbsentNotification;
use App\Http\Requests\Attendance\StudentAbsentNotificationUpdate;
use App\Http\Requests\Attendance\StudentAbsentettingUpdateRequest;
use App\Repositories\Attendance\StudentAbsenceNotificationRepository;
use App\Http\Requests\Attendance\StudentAbsentNotificationUpdateRequest;
use App\Repositories\Academic\ShiftRepository;

class AttendaceNotificationController extends Controller
{
    protected $role_repo ;
    protected $student_absense_noti_repo ;
    protected $shift_repo;

    public function __construct(RoleRepository $role_repo , StudentAbsenceNotificationRepository $student_absense_noti_repo , ShiftRepository $shift_repo)
    {
        $this->role_repo = $role_repo;
        $this->student_absense_noti_repo = $student_absense_noti_repo;
        $this->shift_repo = $shift_repo;
    }


    public function notification(Request $request): JsonResponse|RedirectResponse{
        $data = [];
        $data['pt'] = ___('settings.absent_notification_setup');
        $data['roles'] = $this->role_repo->all()->whereIn('id',[6,7]) ;
        $data['setting'] = $this->student_absense_noti_repo->setting();
        $data['shifts'] = $this->shift_repo->getAll();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(url('/app/attendance/notification'));
    }


    public function settinngUpdate(StudentAbsentettingUpdateRequest $request): JsonResponse|RedirectResponse{
     
        $result =  $this->student_absense_noti_repo->update($request);

        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->back()->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }
}
