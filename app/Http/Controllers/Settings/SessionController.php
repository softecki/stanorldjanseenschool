<?php

namespace App\Http\Controllers\Settings;

use App\Models\Session;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\SessionInterface;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\Session\SessionStoreRequest;
use App\Http\Requests\Session\SessionUpdateRequest;
use App\Repositories\LanguageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SessionController extends Controller
{
    private $session;
    private $lang_repo;

    function __construct(SessionInterface $session , LanguageRepository $lang_repo)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->session       = $session;
        $this->lang_repo       = $lang_repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['sessions'] = $this->session->getAll();
        $data['title'] = ___('settings.sessions');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['sessions'],
                'meta' => ['title' => $data['title']],
            ]);
        }
        return redirect()->to(spa_url('settings/sessions'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('settings.create_session');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(spa_url('settings/sessions/create'));
    }

    public function store(SessionStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->session->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('sessions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['session']        = $this->session->show($id);
        $data['title']       = ___('settings.edit_session');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('settings/sessions/'.$id.'/edit'));
    }

    public function update(SessionUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->session->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('sessions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->session->destroy($id);
        if($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('sessions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function changeSession(Request $request): JsonResponse|int
    {
        $setting = Setting::where('name', 'session')->update(
            ['value' => $request->id]
        );
        if($setting){
            return $request->expectsJson() ? response()->json(['message' => 'Session updated']) : 1;
        }
        return $request->expectsJson() ? response()->json(['message' => 'Unable to update session'], 422) : 0;

    }

    public function translate(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['session']        = $this->session->show($id);
        $data['translates']      = $this->session->translates($id);
        $data['languages']      = $this->lang_repo->all();
        $data['title']       = ___('academic.edit_section');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('settings/sessions/'.$id.'/translate'));
    }

    public function translateUpdate(Request $request, $id): JsonResponse|RedirectResponse{
        $result = $this->session->translateUpdate($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('sessions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }
}
