<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBankAccountsRequest;
use App\Models\BankAccounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankAccountsController extends Controller
{
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $title = 'Banks Accounts';
        $rows = BankAccounts::query()->orderBy('id', 'desc')->get();
        if ($request->expectsJson()) {
            return response()->json(['data' => $rows, 'meta' => ['title' => $title]]);
        }

        return redirect()->to(spa_url('banks-accounts'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => 'Create bank account']]);
        }

        return redirect()->to(spa_url('banks-accounts/create'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number',
            'status' => 'required|boolean',
        ]);

        try {
            $bankAccounts = new BankAccounts();
            $bankAccounts->bank_name = $request->input('bank_name');
            $bankAccounts->account_name = $request->input('account_name');
            $bankAccounts->account_number = $request->input('account_number');
            $bankAccounts->status = $request->input('status');
            $bankAccounts->save();

            if ($request->expectsJson()) {
                return response()->json(['message' => __('alert.created_successfully')]);
            }

            return redirect()->route('banksAccounts.index')->with('success', __('alert.created_successfully'));
        } catch (\Throwable $th) {
            Log::error('Error creating bank account: ', ['error' => $th->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json(['message' => __('alert.something_went_wrong_please_try_again')], 500);
            }

            return redirect()->route('banksAccounts.index')->with('error', __('alert.something_went_wrong_please_try_again'));
        }
    }

    public function edit(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $row = BankAccounts::findOrFail($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $row, 'meta' => ['title' => 'Edit bank account']]);
        }

        return redirect()->to(spa_url('banks-accounts/'.$id.'/edit'));
    }

    public function update(UpdateBankAccountsRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $row = BankAccounts::findOrFail($id);
        $row->fill($request->validated());
        $row->save();

        if ($request->expectsJson()) {
            return response()->json(['message' => __('alert.updated_successfully')]);
        }

        return redirect()->route('banksAccounts.index')->with('success', __('alert.updated_successfully'));
    }

    public function delete(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $row = BankAccounts::findOrFail($id);
        $row->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => __('alert.deleted_successfully')]);
        }

        return redirect()->route('banksAccounts.index')->with('success', __('alert.deleted_successfully'));
    }

    public function translate(Request $request, int $id): RedirectResponse
    {
        return redirect()->to(spa_url('banks-accounts'));
    }

    public function translateUpdate(Request $request, int $id): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Not implemented'], 501);
        }

        return redirect()->to(spa_url('banks-accounts'));
    }
}
