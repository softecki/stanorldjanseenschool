<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\Head\AccountHeadStoreRequest;
use App\Http\Requests\Accounts\Head\AccountHeadUpdateRequest;
use App\Repositories\Accounts\AccountHeadRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AccountHeadController extends Controller
{
    private $headRepo;

    function __construct(AccountHeadRepository $headRepo)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->headRepo       = $headRepo; 
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['account_head'] = $this->headRepo->getAll();
        $data['title'] = ___('account.account_head');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['account_head'], 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/account-heads'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('account.create_account_head');
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/account-heads/create'));
    }

    public function show(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['account_head'] = $this->headRepo->show($id);
        $data['title'] = __('Account Head Details');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['account_head'], 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/account-heads/'.$id));
    }

    public function store(AccountHeadStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->headRepo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('account_head.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['account_head']        = $this->headRepo->show($id);
        $data['title']       = ___('account.edit_account_head');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['account_head'], 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/account-heads/'.$id.'/edit'));
    }

    public function update(AccountHeadUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->headRepo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('account_head.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->headRepo->destroy($id);
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
