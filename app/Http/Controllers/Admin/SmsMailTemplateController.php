<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SmsMailTemplate\SmsMailTemplateStoreRequest;
use App\Repositories\SmsMailTemplate\SmsMailTemplateRepository;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsMailTemplateController extends Controller
{
    private $repo;

    public function __construct(SmsMailTemplateRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.SMS/Mail_template');
        $data['templates'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['templates'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(spa_url('communication/template'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.SMS/Mail_template_create');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('communication/template/create'));
    }

    public function store(SmsMailTemplateStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('template.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['template'] = $this->repo->show($id);
        $data['title'] = ___('common.SMS/Mail_template_edit');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('communication/template/'.$id.'/edit'));
    }

    public function update(SmsMailTemplateStoreRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('template.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function updateSmsTracking($results)
    {
        if (is_string($results)) {
            $results = json_decode($results, true);
        }

        if (is_array($results)) {
            foreach ($results as $result) {
                if (isset($result['status'])) {
                    $messageId = $result['messageId'];
                    $status_groupName = $result['status']['groupName'];
                    $status_name = $result['status']['name'];

                    DB::table('sms_tracking')
                        ->where('messageId', $messageId)
                        ->update([
                            'status_groupName' => $status_groupName,
                            'status_name' => $status_name,
                        ]);
                }
            }

            return true;
        }

        throw new \Exception('Invalid input: $results is not an array or valid JSON.');
    }

    public function delivery(Request $request): JsonResponse|RedirectResponse
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://messaging-service.co.tz/api/sms/v1/reports');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            'Authorization: Basic ZmlsYmVydG46RXVzYWJpdXMxNzEwLg==',
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $curlErr = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);

        if ($curlErr) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Remote request failed: '.$curlErr], 502);
            }

            return back()->with('danger', 'Remote request failed');
        }

        $data = json_decode($result, true);
        Log::info($data);
        $results = isset($data['results']) && ! empty($data['results']) ? $data['results'] : null;

        if ($results != null) {
            $status = $this->updateSmsTracking($results);
            $msg = $status ? 'Message Updated Successfully' : 'Failed to update status';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg, 'updated' => (bool) $status]);
            }

            return redirect()->route('smsmail.index')->with('success', $msg);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'No Reports', 'updated' => false]);
        }

        return redirect()->route('smsmail.index')->with('success', 'No Reports');
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->destroy($id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('template.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }
}
