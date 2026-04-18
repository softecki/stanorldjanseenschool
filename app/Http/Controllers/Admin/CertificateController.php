<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificate\CertificateSearchRequest;
use App\Http\Requests\Certificate\CertificateStoreRequest;
use App\Http\Requests\Certificate\CertificateUpdateRequest;
use App\Models\StudentInfo\SessionClassStudent;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Certificate\CertificateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class CertificateController extends Controller
{
    private $repo;

    private $classSetupRepo;

    public function __construct(CertificateRepository $repo, ClassSetupRepository $classSetupRepo)
    {
        $this->repo = $repo;
        $this->classSetupRepo = $classSetupRepo;
    }

    /**
     * @param  \App\Models\Certificate  $cert
     */
    private function serializeCertificate($cert): array
    {
        $cert->loadMissing(['bgImage', 'leftSignature', 'rightSignature']);

        return [
            'id' => $cert->id,
            'title' => $cert->title,
            'top_text' => $cert->top_text,
            'description' => $cert->description,
            'logo' => (bool) $cert->logo,
            'name' => (bool) $cert->name,
            'bottom_left_text' => $cert->bottom_left_text,
            'bottom_right_text' => $cert->bottom_right_text,
            'bg_image_url' => $cert->bgImage ? globalAsset($cert->bgImage->path, '40X40.webp') : asset('backend/uploads/card-images/certificate_bg.png'),
            'bottom_left_signature_url' => $cert->leftSignature ? globalAsset($cert->leftSignature->path, '40X40.webp') : null,
            'bottom_right_signature_url' => $cert->rightSignature ? globalAsset($cert->rightSignature->path, '40X40.webp') : null,
            'school_logo_url' => $cert->logo ? globalAsset(setting('dark_logo'), '154X38.webp') : null,
        ];
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.certificate list');
        $data['certificates'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['certificates'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(spa_url('certificate'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.certificate create');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('certificate/create'));
    }

    public function store(CertificateStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('certificate.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['certificate'] = $this->repo->show($id);
        $data['title'] = ___('common.certificate edit');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('certificate/'.$id.'/edit'));
    }

    public function update(CertificateUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('certificate.index')->with('success', $result['message']);
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

            return redirect()->route('certificate.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function preview(Request $request): JsonResponse
    {
        $data['certificate'] = $this->repo->show($request->certificate_id);
        $data['view'] = view('backend.certificate.preview', compact('data'))->render();

        return response()->json($data);
    }

    public function generate(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.Generate certificate');
        $data['certificates'] = $this->repo->all();
        $data['classes'] = $this->classSetupRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('certificate/generate'));
    }

    /**
     * Students for class + section (dropdowns on generate page).
     */
    public function sessionStudents(Request $request): JsonResponse
    {
        $request->validate([
            'class' => 'required',
            'section' => 'required',
        ]);

        $rows = SessionClassStudent::query()
            ->where('session_id', setting('session'))
            ->where('classes_id', $request->class)
            ->where('section_id', $request->section)
            ->with(['student', 'class', 'section'])
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function generateSearch(CertificateSearchRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->generateSearch($request);
        if (! $result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']], 422);
            }

            return back()->with('danger', $result['message']);
        }

        $payload = $result['data'];
        $data['certificate'] = $payload['certificate']->loadMissing(['bgImage', 'leftSignature', 'rightSignature']);
        $data['students'] = $payload['students'];
        $data['session'] = $payload['session'];
        $data['title'] = ___('common.Generate certificate');
        $data['certificates'] = $this->repo->all();
        $data['classes'] = $this->classSetupRepo->all();

        $data['settings'] = [
            'application_name' => setting('application_name'),
            'address' => setting('address'),
            'dark_logo' => setting('dark_logo'),
        ];

        if ($request->expectsJson()) {
            $studentsOut = $data['students']->map(function ($row) {
                return [
                    'id' => $row->id,
                    'student' => $row->student,
                    'class' => $row->class,
                    'section' => $row->section,
                ];
            });

            return response()->json([
                'data' => [
                    'certificate' => $this->serializeCertificate($data['certificate']),
                    'students' => $studentsOut,
                    'session' => $data['session'],
                    'settings' => $data['settings'],
                    'certificates' => $data['certificates'],
                    'classes' => $data['classes'],
                ],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return view('backend.certificate.generate', compact('data'));
    }
}
