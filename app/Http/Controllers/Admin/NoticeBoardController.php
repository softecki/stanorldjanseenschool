<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoticeBoard\NoticeBoardStoreRequest;
use App\Repositories\LanguageRepository;
use App\Repositories\NoticeBoard\NoticeBoardRepository;
use App\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoticeBoardController extends Controller
{
    private $repo;

    private $roleRepo;

    private $lang_repo;

    public function __construct(NoticeBoardRepository $repo, RoleRepository $roleRepo, LanguageRepository $lang_repo)
    {
        $this->repo = $repo;
        $this->roleRepo = $roleRepo;
        $this->lang_repo = $lang_repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.notice_board');
        $data['notice-boards'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['notice-boards'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(spa_url('communication/notice-board'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.notice_board_create');
        $data['roles'] = $this->roleRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('communication/notice-board/create'));
    }

    public function store(NoticeBoardStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('notice-board.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['notice-board'] = $this->repo->show($id);
        $data['title'] = ___('common.notice_board_edit');
        $data['roles'] = $this->roleRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('communication/notice-board/'.$id.'/edit'));
    }

    public function update(NoticeBoardStoreRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('notice-board.index')->with('success', $result['message']);
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

            return redirect()->route('notice-board.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function translate(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['notice_board'] = $this->repo->show($id);
        $data['translates'] = $this->repo->translates($id);
        $data['languages'] = $this->lang_repo->all();
        $data['title'] = ___('website.Edit Notice Board');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('communication/notice-board/'.$id.'/translate'));
    }

    public function translateUpdate(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->translateUpdate($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('notice-board.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }
}
