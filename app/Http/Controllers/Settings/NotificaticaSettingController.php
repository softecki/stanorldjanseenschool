<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Repositories\SystemNotificationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificaticaSettingController extends Controller
{
    protected $system_notification_repo;

    public function __construct(SystemNotificationRepository $system_notification_repo)
    {
        $this->system_notification_repo = $system_notification_repo;
    }

    public function notificationSettings(Request $request): JsonResponse|View
    {
        $data = [];
        $data['pt'] = ___('settings.notification_setting');
        $data['notificationSettings'] = $this->system_notification_repo->setting();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['notificationSettings'],
                'meta' => [
                    'title' => $data['pt'],
                    'can_update' => hasPermission('general_settings_update'),
                ],
            ]);
        }

        return view('backend.settings.notification_setting', $data);
    }

    public function notificationEventModal(Request $request, $id, $key): JsonResponse|View
    {
        if ($request->expectsJson()) {
            try {
                $modal = $this->system_notification_repo->showSetting($id);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json(['message' => 'Not found'], 404);
            }

            if (! isset($modal->template[$key])) {
                return response()->json(['message' => 'Not found'], 404);
            }

            return response()->json([
                'data' => [
                    'id' => (int) $id,
                    'key' => $key,
                    'shortcode' => $modal->shortcode[$key] ?? '',
                    'subject' => $modal->subject[$key] ?? '',
                    'email_body' => $modal->template[$key]['Email'] ?? '',
                    'sms_body' => $modal->template[$key]['SMS'] ?? '',
                    'app_body' => $modal->template[$key]['App'] ?? '',
                    'web_body' => $modal->template[$key]['Web'] ?? '',
                ],
                'meta' => ['title' => ___('settings.notification_setting')],
            ]);
        }

        $modal = $this->system_notification_repo->showSetting($id);
        $data = [];
        if ($modal) {
            $data['id'] = $id;
            $data['key'] = $key;
            $data['shortcode'] = $modal->shortcode[$key];
            $data['subject'] = $modal->subject[$key];
            $data['emailBody'] = $modal->template[$key]['Email'];
            $data['smsBody'] = $modal->template[$key]['SMS'];
            $data['appBody'] = $modal->template[$key]['App'];
            $data['webBody'] = $modal->template[$key]['Web'];
        }

        return view('backend.settings.notification_setting_modal', $data);
    }

    public function viewNotification($id)
    {
        $result = $this->system_notification_repo->readNotification($id);
        if ($result && $result->url) {
            return redirect($result->url);
        }

        return redirect()->back()->with('success', 'Notification Viewed');
    }

    public function updateNotificationSetting(Request $request): JsonResponse
    {
        $type = $request->input('type');

        if ($type === 'destination') {
            $request->validate([
                'id' => 'required|integer|exists:notification_settings,id',
                'host' => 'required|string|max:100',
                'status' => 'required|in:0,1',
                'type' => 'required|in:destination',
            ]);
        } elseif ($type === 'recipient-status') {
            $request->validate([
                'id' => 'required|integer|exists:notification_settings,id',
                'reciever' => 'required|string|max:100',
                'status' => 'required|in:0,1',
                'type' => 'required|in:recipient-status',
            ]);
        } elseif ($type === 'recipient') {
            $request->validate([
                'id' => 'required|integer|exists:notification_settings,id',
                'key' => 'required|string|max:100',
                'subject' => 'nullable|string|max:500',
                'email_body' => 'nullable|string',
                'sms_body' => 'nullable|string',
                'app_body' => 'nullable|string',
                'web_body' => 'nullable|string',
                'type' => 'required|in:recipient',
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid request type.'], 422);
        }

        $result = $this->system_notification_repo->settingUpdate($request);

        if ($result) {
            return response()->json(['success' => true, 'message' => ___('alert.updated_successfully')]);
        }

        return response()->json([
            'success' => false,
            'message' => ___('alert.something_went_wrong_please_try_again'),
        ], 422);
    }
}
