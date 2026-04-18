<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Academic\TimeSchedule\TimeScheduleStoreRequest;
use App\Http\Requests\Academic\TimeSchedule\TimeScheduleUpdateRequest;
use App\Interfaces\Academic\TimeScheduleInterface;
use Illuminate\Support\Facades\Schema;

class TimeScheduleController extends Controller
{
    private $timeRepo;

    function __construct(TimeScheduleInterface $timeRepo)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->timeRepo       = $timeRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['time_schedule'] = $this->timeRepo->getAll();
        $data['title'] = ___('academic.time_schedule');
        if ($request->expectsJson()) return response()->json(['data' => $data['time_schedule'], 'meta' => ['title' => $data['title']]]);
        return view('backend.academic.time-schedule.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('academic.create_time_schedule');
        if ($request->expectsJson()) return response()->json(['meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/time-schedules/create'));
    }

    public function store(TimeScheduleStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->timeRepo->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('time_schedule.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['time_schedule']        = $this->timeRepo->show($id);
        $data['title']        = ___('academic.edit_time_schedule');
        if ($request->expectsJson()) return response()->json(['data' => $data['time_schedule'], 'meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/time-schedules/'.$id.'/edit'));
    }

    public function update(TimeScheduleUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->timeRepo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('time_schedule.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->timeRepo->destroy($id);
        if($result['status']):
            $success[0] = $result['message'];
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
            return response()->json($success);
        else:
            $success[0] = $result['message'];
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            return response()->json($success);
        endif;
    }
}
