<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\Expense\ExpenseStoreRequest;
use App\Http\Requests\Accounts\Expense\ExpenseUpdateRequest;
use App\Repositories\Accounts\AccountHeadRepository;
use App\Repositories\Accounts\ExpenseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Item;
use App\Models\Accounts\Income;
use App\Models\Accounts\AccountHead;
use Illuminate\Support\Facades\Log;
use App\Models\BankAccounts;
use App\Models\FloatBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    private $expenseRepo, $accountHeadRepository;

    function __construct(ExpenseRepository $expenseRepo, AccountHeadRepository $accountHeadRepository)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->expenseRepo                 = $expenseRepo; 
        $this->accountHeadRepository       = $accountHeadRepository; 
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['expense'] = $this->expenseRepo->getAll();
        $data['title'] = ___('account.expense');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['expense'], 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/app/expense'));
    }

    public function index_cash(Request $request): JsonResponse|RedirectResponse
    {
        $data['expense'] = $this->expenseRepo->getAll();
        $data['title'] = 'Cash Deposit';
         $data['account_number']           = BankAccounts::all();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['expense'], 'meta' => $data]);
        }
        return redirect()->to(url('/app/cash'));
    }

    public function index_product(Request $request): JsonResponse|RedirectResponse
    {
        $data['expense'] = $this->expenseRepo->getAllProduct();
        $data['title'] = 'Product';
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['expense'], 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/product'));
    }

    public function index_item(Request $request): JsonResponse|RedirectResponse
    {
        $data['expense'] = $this->expenseRepo->getAllItem();
        $data['title'] = 'Items';
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['expense'], 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/item'));
    }

     public function index_balance(Request $request): JsonResponse|RedirectResponse
    {
        $data['expense'] = $this->expenseRepo->getAllBalance();
        $data['title'] = 'Balance';
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['expense'], 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/balance'));
    }

    public function create(Request $request): JsonResponse|View
    {
        $data['title'] = ___('account.create_expense');
        $data['account_number'] = BankAccounts::all();
        $data['drivers'] = DB::select('select * from drivers');
        $data['heads'] = $this->accountHeadRepository->getExpenseHeads();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(url('/app/expense/create'));
    }

    public function create_product(Request $request): JsonResponse|RedirectResponse{
        $data['title']       = 'Create Product';
         $data['account_number']           = BankAccounts::all();
        $data['heads']       = $this->accountHeadRepository->getExpenseHeads();
        $data['items']   = DB::select('select * from items');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(url('/app/product/create'));
    }

     public function create_sell(Request $request): JsonResponse|RedirectResponse{
        $data['title']       = 'Sell Product';
         $data['account_number']           = BankAccounts::all();
        $data['heads']       = $this->accountHeadRepository->getExpenseHeads();
        $data['items']   = DB::select('select * from items');
        $data['products'] = Product::leftJoin('items', 'items.id', '=', 'products.name')
            ->select('products.*', 'products.name as item_id', 'items.name as item_name')
            ->orderBy('items.name')
            ->get();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(url('/app/product/sell'));
    }

     public function create_item(Request $request): JsonResponse|RedirectResponse{
        $data['title']       = 'Create Item';
        $data['heads']       = $this->accountHeadRepository->getExpenseHeads();
        $data['products']   = DB::select('select * from items');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(url('/app/item/create'));
    }

    public function store(ExpenseStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->expenseRepo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('expense.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function store_product(Request $request): JsonResponse|RedirectResponse{
        $request->validate([
            'name' => 'required',
            'quantity' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
           $existingProduct = Product::where('name', $request->name)->first();

            if ($existingProduct) {
                // Update the existing product
                $existingProduct->quantity += $request->quantity;
                $existingProduct->remained += $request->quantity;
                $existingProduct->price = $request->price; // Optional: update price
                $existingProduct->save();
            } else {
                // Create a new product
                $newProduct = new Product();
                $newProduct->name = $request->name;
                $newProduct->quantity = $request->quantity;
                $newProduct->remained = $request->quantity;
                $newProduct->price = $request->price;
                $newProduct->save();
            }


            DB::commit();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Successfully Registered']);
            }
            return redirect()->route('product.index')->with('success', 'Successfully Registered');
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
            }
            return back()->with('danger',___('alert.something_went_wrong_please_try_again'));
        }

    }

    public function edit_product(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['title'] = 'Edit Product';
        $data['items'] = DB::select('select * from items');
        $data['product'] = Product::findOrFail($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['product'], 'meta' => $data]);
        }
        return redirect()->to(url('/app/product/'.$id.'/edit'));
    }

    public function update_product(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'quantity' => 'required|numeric|min:0',
            'remained' => 'nullable|numeric|min:0',
            'itemout' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            $product->name = $request->name;
            $product->quantity = $request->quantity ?? 0;
            $product->remained = $request->remained ?? $request->quantity ?? 0;
            $product->itemout = $request->itemout ?? 0;
            $product->price = $request->price ?? 0;
            $product->save();

            DB::commit();
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.updated_successfully')]);
            }
            return redirect()->route('product.index')->with('success', ___('alert.updated_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
            }
            return back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }
    }

    public function delete_product($id)
    {
        try {
            Product::findOrFail($id)->delete();
            return response()->json([___('alert.deleted_successfully'), 'success', ___('alert.deleted'), ___('alert.OK')]);
        } catch (\Throwable $th) {
            return response()->json([___('alert.something_went_wrong_please_try_again'), 'error', ___('alert.oops')], 422);
        }
    }

    public function store_sell(Request $request): JsonResponse|RedirectResponse{
        $request->validate([
            'name' => 'required',
            'quantity' => 'required|numeric|min:1',
            'date' => 'nullable|date',
            'receipt' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
           $name = trim($request->name);
            $existingProduct = Product::where('name', $name)->first();
           Log::info($request->name);
           Log::info($existingProduct);
        if (!$existingProduct) {
            Log::warning('No product found for name: ' . $request->name);
            Log::info($existingProduct);
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Product with this name does not exists.'], 422);
            }
            return back()->with('danger', 'Product with this name does not exists.');
         }
         if ((float) $request->quantity > (float) ($existingProduct->remained ?? 0)) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sold quantity cannot exceed available stock.'], 422);
            }
            return back()->with('danger', 'Sold quantity cannot exceed available stock.');
         }
            if ($existingProduct) {
                // Update the existing product
                $existingProduct->remained -= $request->quantity;
                $existingProduct->itemout += $request->quantity;
                // $existingProduct->price = $request->price; // Optional: update price
                $existingProduct->save();
            } 
             $item = Item::where('id', $request->name)->first();
             $itemName = AccountHead::where('name', 'Store')->first() ?: AccountHead::where('type', \App\Enums\AccountHeadType::INCOME)->first();
             if (!$item || !$itemName) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Product item or income head is missing.'], 422);
                }
                return back()->with('danger', 'Product item or income head is missing.');
             }
              $incomeStore                   = new Income();
            $incomeStore->session_id       = setting('session'); 
            $incomeStore->name             = $item->name;
            $incomeStore->income_head      = $itemName->id;
            $incomeStore->date             = $request->date?? now()->toDateString();
            $incomeStore->amount           = $request->quantity * $existingProduct->price;
            // $incomeStore->bank_name             = $request->bank_name;
            $incomeStore->account_number           = "5";
            $incomeStore->invoice_number   = $request->receipt;
            // $incomeStore->upload_id        = $this->UploadImageCreate($request->document, 'backend/uploads/incomes');
            // $incomeStore->description      = $request->description;
            $incomeStore->save();

            //        $existingBalance = FloatBalance::where('account', $request->account_number)->first();
            // if ($existingBalance) {
            //     // Account exists – update the balance
            //     $existingBalance->balance_amount += $request->amount;
            //     $existingBalance->save();
            // } else {
            //     // Account doesn't exist – create a new record
            //     $FloatBalance = new FloatBalance();
            //     $FloatBalance->balance_amount = $request->amount;
            //     $FloatBalance->account = $request->account_number;
            //     $FloatBalance->save();
            // }

            DB::commit();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Successfully Registered']);
            }
            return redirect()->route('product.index')->with('success', 'Successfully Registered');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th);
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
            }
            return back()->with('danger',___('alert.something_went_wrong_please_try_again'));
        }

    }

    public function store_item(Request $request): JsonResponse|RedirectResponse{
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expenseStore                   = new Item();
            $expenseStore->name             = $request->name;
            $expenseStore->description             = $request->description;
            $expenseStore->save();

            DB::commit();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Successfully Registered']);
            }
            return redirect()->route('item.index')->with('success', 'Successfully Registered');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('store_item failed: '.$th->getMessage(), ['trace' => $th->getTraceAsString()]);
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
            }
            return back()->with('danger',___('alert.something_went_wrong_please_try_again'));
        }

    }

    public function edit_item(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['title'] = 'Edit Item';
        $data['item'] = Item::findOrFail($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['item'], 'meta' => $data]);
        }
        return redirect()->to(url('/app/item/'.$id.'/edit'));
    }

    public function update_item(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($id);
            $item->name = $request->name;
            $item->description = $request->description;
            $item->save();

            DB::commit();
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.updated_successfully')]);
            }
            return redirect()->route('item.index')->with('success', ___('alert.updated_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
            }
            return back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }
    }

    public function delete_item($id)
    {
        try {
            Item::findOrFail($id)->delete();
            return response()->json([___('alert.deleted_successfully'), 'success', ___('alert.deleted'), ___('alert.OK')]);
        } catch (\Throwable $th) {
            return response()->json([___('alert.something_went_wrong_please_try_again'), 'error', ___('alert.oops')], 422);
        }
    }

    public function edit(Request $request, $id): JsonResponse|View
    {
        $data['heads'] = $this->accountHeadRepository->getExpenseHeads();
        $data['expense'] = $this->expenseRepo->show($id);
        $data['status'] = DB::select('select * from expenses_status');
        $data['title'] = ___('account.edit_expense');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['expense'], 'meta' => $data]);
        }

        return redirect()->to(url('/app/expense/'.$id.'/edit'));
    }

    public function update(ExpenseUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->expenseRepo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('expense.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->expenseRepo->destroy($id);
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
