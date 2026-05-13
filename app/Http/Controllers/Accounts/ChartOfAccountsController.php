<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Repositories\Accounts\AccountingAccountRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountsController extends Controller
{
    public function __construct(
        protected AccountingAccountRepository $repo
    ) {}

    public function index(Request $request): JsonResponse|View
    {
        $data['title'] = __('Chart of Accounts');
        $data['accounts'] = $this->repo->getAll($request);
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['accounts'],
                'meta' => [
                    'title' => $data['title'],
                    'filters' => [
                        'q' => (string) $request->input('q', ''),
                        'type' => (string) $request->input('type', ''),
                        'status' => (string) $request->input('status', ''),
                    ],
                ],
            ]);
        }
        return redirect()->to(url('/app/chart-of-accounts'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = __('Add Account');
        $data['parents'] = $this->repo->getTree();
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title'], 'parents' => $data['parents']]]);
        }
        return redirect()->to(url('/app/chart-of-accounts/create'));
    }

    public function show(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['title'] = __('Account Details');
        $data['account'] = $this->repo->show($id)->load(['parent', 'children']);
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['account'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(url('/app/chart-of-accounts/'.$id));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,asset,liability',
            'code' => 'nullable|string|max:50|unique:accounting_accounts,code',
            'parent_id' => 'nullable|integer|exists:accounting_accounts,id',
            'status' => 'nullable|integer|in:0,1',
            'description' => 'nullable|string',
        ]);
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('chart-of-accounts.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['title'] = __('Edit Account');
        $data['account'] = $this->repo->show($id);
        $data['parents'] = $this->repo->getTree();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['account'],
                'meta' => ['title' => $data['title'], 'parents' => $data['parents']],
            ]);
        }
        return redirect()->to(url('/app/chart-of-accounts/'.$id.'/edit'));
    }

    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,asset,liability',
            'code' => ['nullable', 'string', 'max:50', Rule::unique('accounting_accounts', 'code')->ignore($id)],
            'parent_id' => ['nullable', 'integer', 'exists:accounting_accounts,id', Rule::notIn([(int) $id])],
            'status' => 'nullable|integer|in:0,1',
            'description' => 'nullable|string',
        ]);
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('chart-of-accounts.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function delete($id)
    {
        $result = $this->repo->destroy($id);
        if ($result['status']) {
            return response()->json([$result['message'], 'success', ___('alert.deleted'), ___('alert.OK')]);
        }
        return response()->json([$result['message'], 'error', ___('alert.oops')]);
    }
}
