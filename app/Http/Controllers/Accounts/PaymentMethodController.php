<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Repositories\Accounts\PaymentMethodRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct(
        protected PaymentMethodRepository $repo
    ) {}

    public function index(Request $request): JsonResponse|View
    {
        $data['title'] = __('Payment Methods');
        $data['methods'] = $this->repo->getAll();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['methods'], 'meta' => ['title' => $data['title']]]);
        }
        return view('backend.accounts.payment-methods.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = __('Add Payment Method');
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/accounts/payment-methods/create'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('payment-methods.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['title'] = __('Edit Payment Method');
        $data['method'] = $this->repo->show($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['method'], 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/accounts/payment-methods/'.$id.'/edit'));
    }

    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('payment-methods.index')->with('success', $result['message']);
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
