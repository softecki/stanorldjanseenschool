<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SmsStudentTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\SmsMailLog\SmsMailLogStoreRequest;
use App\Models\SmsMailTemplate;
use App\Models\User;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SmsMailLog\SmsMailLogRepository;
use App\Repositories\SmsMailTemplate\SmsMailTemplateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SmsMailLogController extends Controller
{
    private $templateRepo;

    private $repo;

    private $classSetupRepo;

    private $roleRepo;

    public function __construct(
        SmsMailLogRepository $repo,
        SmsMailTemplateRepository $templateRepo,
        ClassSetupRepository $classSetupRepo,
        RoleRepository $roleRepo,
    ) {
        $this->templateRepo = $templateRepo;
        $this->repo = $repo;
        $this->classSetupRepo = $classSetupRepo;
        $this->roleRepo = $roleRepo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.Sms/Mail');
        $data['smsmail'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['smsmail'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(spa_url('communication/smsmail'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.SMS/Mail_template_create');
        $data['templates'] = $this->templateRepo->all();
        $data['roles'] = $this->roleRepo->all();
        $data['classes'] = $this->classSetupRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('communication/smsmail/create'));
    }

    public function store(SmsMailLogStoreRequest $request): JsonResponse
    {
        $result = $this->repo->store($request);

        return response()->json($result, $result['status'] ? 200 : 422);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['smsmail'] = $this->repo->show($id);
        $data['title'] = ___('common.SMS/Mail_template_edit');
        $data['templates'] = $this->templateRepo->all();
        $data['roles'] = $this->roleRepo->all();
        $data['classes'] = $this->classSetupRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('communication/smsmail/'.$id.'/edit'));
    }

    public function update(SmsMailLogStoreRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('smsmail.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->destroy($id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('smsmail.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function users(Request $request)
    {
        $users = User::where('role_id', $request->role_id)->get();

        return response()->json($users);
    }

    public function template(Request $request)
    {
        $template = SmsMailTemplate::with('attachmentFile')->find($request->template_id);

        return response()->json($template);
    }

    /**
     * Preview Excel file and match student names to phone numbers
     */
    public function preview(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|mimes:xlsx,xls,csv',
                'sms_description' => 'required|string',
            ]);

            $smsDescription = $request->sms_description;

            $studentData = $this->repo->processExcelForSms($request);
            $students = [];

            foreach ($studentData as $student) {
                $studentName = $student['name'] ?? '';
                if (empty($studentName)) {
                    continue;
                }

                $messagePreview = $smsDescription;
                if (! empty($student['found'])) {
                    $displayName = $student['full_name'] ?? $studentName;
                    $messagePreview = str_replace('{name}', $displayName, $messagePreview);
                    $messagePreview = str_replace('{balance}', number_format($student['balance'] ?? 0, 0), $messagePreview);
                } else {
                    $messagePreview = str_replace('{name}', $studentName, $messagePreview);
                    $messagePreview = str_replace('{balance}', '0', $messagePreview);
                }

                $students[] = [
                    'name' => $studentName,
                    'phone' => $student['phone'] ?? null,
                    'balance' => $student['balance'] ?? 0,
                    'message_preview' => $messagePreview,
                    'found' => $student['found'] ?? false,
                    'full_name' => $student['full_name'] ?? $studentName,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Preview generated successfully',
                'data' => [
                    'students' => $students,
                    'message_template' => $smsDescription,
                    'total_students' => count($students),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Excel preview error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error processing Excel file: '.$e->getMessage(),
            ], 400);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new SmsStudentTemplateExport(), 'sms_student_template.xlsx');
    }
}
