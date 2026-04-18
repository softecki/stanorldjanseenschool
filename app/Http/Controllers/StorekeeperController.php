<?php

namespace App\Http\Controllers;

use App\Models\Storekeeper;
use App\Http\Requests\StoreStorekeeperRequest;
use App\Http\Requests\UpdateStorekeeperRequest;
use App\Models\StockOverview;
use App\Models\Stock;
use App\Models\StocksIn;
use App\Models\StocksOut;
use App\Models\CashBeposit;
use App\Models\CashDeposit;
use App\Models\CashCollectedHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\Staff\DepartmentInterface;


class StorekeeperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

         private $repo;

          function __construct(DepartmentInterface $repo)
    {
        $this->repo       = $repo; 
    }
    public function index()
    {
        $data['bloodGroup'] = DB::table('stocks')
        ->join('products','products.id','=','stocks.name')
        ->join('unit','unit.id','=','stocks.unit')
        ->paginate(10);
        $data['products'] = DB::table('shop_products')
        ->paginate(10);
        $data['title'] = 'Stock';
        return view('backend.stock_overview.index', compact('data'));
    }

    public function vehiclesindex(){
        $data['drivers'] = DB::table('drivers')
        ->paginate(100);
         $data['title']              = 'Drivers';
        return view('backend.vehicles.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['title']       = 'Create Stock';
        $data['products'] = DB::table('products')->get();
        $data['units'] = DB::table('unit')->get();
        return view('backend.stock_overview.create', compact('data'));
    }

     public function vehicles_create()
    {
        $data['title']       = 'Register Driver';
        return view('backend.vehicles.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorekeeperRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $stock = Stock::where('name', $request->name)->first();

            if ($stock) {
                // Update existing stock
                $stock->price    = $request->price;
                $stock->cost     = $request->price * $request->quantity;
                $stock->quantity = $stock->quantity + $request->quantity;
                $stock->unit     = $request->unit;
            } else {
                // Create new stock
                $stock = new Stock();
                $stock->name     = $request->name;
                $stock->price    = $request->price;
                $stock->cost     = $request->price * $request->quantity;
                $stock->quantity = $request->quantity;
                $stock->unit     = $request->unit;
            }

            $stock->save();


            $StockOverView              = new StocksIn();
            $StockOverView->item_id        = $request->name;
            $StockOverView->user_id      = Auth::user()->id;
            $StockOverView->total_cost        = $request->price * $request->quantity;
            $StockOverView->quantity      = $request->quantity;
            $StockOverView->price      = $request->price;
            $StockOverView->unit      = $request->unit;
            $StockOverView->save();

            return redirect()->route('storekeeper.index')->with('success', ___('alert.created_successfully'));

        } catch (\Throwable $th) {
            dd($th);
            return back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }
    }

    public function vehicles_store(Request $request){
        try {
            $driver_name = $request->name;
            $liters = $request->liters;
            $plate_number = $request->plate_number;
            
            // Check for duplicate vehicle by plate_number
            $duplicate = DB::table('drivers')->where('plate_number', $plate_number)->first();
            if ($duplicate) {
                return back()->with('danger', 'A vehicle with this plate number already exists.');
            }

            DB::insert('INSERT INTO drivers (driver_name,liters,plate_number) VALUES (?,?,?)',
                [$driver_name, $liters, $plate_number]);

            return redirect()->route('vehicles.index')->with('success', ___('alert.created_successfully'));

        } catch (\Throwable $th) {

            return back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Storekeeper  $storekeeper
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $data['title']       = 'Create Stock';
        $data['products'] = DB::table('products')->get();
        $data['units'] = DB::table('unit')->get();
        $data['stock'] = Stock::where('id', $id)->first();
        return view('backend.shop_products.shopproduct', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Storekeeper  $storekeeper
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $data['title']       = 'Create Stock';
        $data['products'] = DB::table('products')->get();
        $data['units'] = DB::table('unit')->get();
        $data['stock'] = Stock::where('id', $id)->first();
        return view('backend.stock_overview.edit', compact('data'));
    }

    public function sell($id)
    {
        //
        $data['title']       = 'Sells';
        $data['products'] = DB::table('products')->get();
        $data['units'] = DB::table('unit')->get();
        $data['stock'] = Stock::where('id', $id)->first();
        return view('backend.stock_overview.sell', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorekeeperRequest  $request
     * @param  \App\Models\Storekeeper  $storekeeper
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        //
    }

    public function sellupdate(Request $request,$id)
    {
        try {

            $quantity = !empty($request->quantity) ? $request->quantity : 1;
            $stock = Stock::where('id', $id)->first();
            $stock->quantity = $stock->quantity - $quantity;
            $stock->save();

            $StockOverView              = new StocksOut();
            $StockOverView->item_id     = $request->name;
            $StockOverView->user_id     = Auth::user()->id;
            $StockOverView->quantity    = $quantity;
            $StockOverView->price       = $request->price * $quantity;
            $StockOverView->save();

            $cashCollectedHistory              = new CashCollectedHistory();
            $cashCollectedHistory->source     = $StockOverView->id;
            $cashCollectedHistory->user_id     = Auth::user()->id;
            $cashCollectedHistory->amount_collected       = $request->price * $quantity;
            $cashCollectedHistory->save();

            $cashDeposit = CashDeposit::where('user_id', Auth::user()->id)->first();

            if ($cashDeposit) {
                $cashDeposit->total_amount_collected       = $cashDeposit->total_amount_collected + $request->price * $quantity;
                $cashDeposit->total_amount_remained       = $cashDeposit->total_amount_remained  + ($request->price * $quantity);
            }else{
                $cashDeposit              = new CashDeposit();
                $cashDeposit->user_id     = Auth::user()->id;
                $cashDeposit->total_amount_collected       = $request->price * $quantity;
                $cashDeposit->total_amount_remained       = $request->price * $quantity;
            }
          
            $cashDeposit->save();

            return redirect()->route('storekeeper.index')->with('success', ___('alert.created_successfully'));

        } catch (\Throwable $th) {
            dd($th);
            return back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Storekeeper  $storekeeper
     * @return \Illuminate\Http\Response
     */
    public function destroy(Storekeeper $storekeeper)
    {
        //
    }
}
