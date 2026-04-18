<?php

namespace App\Http\Controllers\Examination;

use App\Http\Controllers\Controller;
use App\Repositories\Examination\ExaminationSettingsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExaminationSettingsController extends Controller
{
    private $repo;

    public function __construct(ExaminationSettingsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('settings.examination_settings');
        $data['average_pass_marks'] = examSetting('average_pass_marks');
        if ($request->expectsJson()) {
            return response()->json([
                'meta' => [
                    'title' => $data['title'],
                    'average_pass_marks' => $data['average_pass_marks'],
                ],
            ]);
        }

        return redirect()->to(spa_url('examination/settings'));
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->updateSetting($request);
        if ($result) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.updated_successfully')]);
            }

            return redirect()->back()->with('success', ___('alert.updated_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        }

        return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }
}
