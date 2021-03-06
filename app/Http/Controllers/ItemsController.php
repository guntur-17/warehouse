<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Office;
use App\Models\Region;
use App\Models\Type;
use App\Models\Employee;
use App\Models\History;
use App\Models\History_ownership;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Session;

class ItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Item::with('region','office','type')->select('items.*')->latest()->get();
            # Here 'items' is the name of table for Documents Model
            # And 'region' is the name of relation on Document Model.
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('type_str', function($row){
                    # 'name' is the field in table of Status Model
                    return $row->type->name;
               })
                ->addColumn('office_str', function($row){
                    # 'name' is the field in table of Status Model
                    return $row->office->name;
               })
                ->addColumn('region_str', function($row){
                      # 'name' is the field in table of Status Model
                      return $row->region->name;
                 })
                 ->addColumn('detail', function($row){
                    $detailBtn = '<a href="'.route('items.details',[$row->name,$row->id]).'" class="btn btn-primary d-flex justify-content-center">Detail</a>';
                    return $detailBtn;
                })
                ->addColumn('action', function($row){
                    $actionBtn = '<a href="'. route('items.edit', [$row->id]) .'" class="edit btn btn-success btn-sm">Edit</a> 
                    <form action="'.route('items.destroy',[$row->id]).'" method="POST" class="d-inline">'.method_field('delete') .csrf_field().'
                    <button class="delete btn btn-danger btn-sm" onclick="return confirm(\'Are You Sure?\')">Delete</button>
                    </form>';
                    return $actionBtn;
                })
                ->rawColumns(['detail','action'])
                ->make(true);
        }
        return view('pages.admin.Item.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.admin.Item.create', [
            'regions' => Region::all(),
            'types' => Type::all(),
            'offices' => Office::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // 'id' => 'required',
            'name' => 'required',
            'office_id' => 'required',
            'type_id' => 'required',
            'region_id' => 'required',
            'description' => 'required',
        ]);

        Item::create($validatedData);
        return redirect('/admin/items');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Item $items
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item, Request $request, $name,$id)
    { 

        
        $items = Item::where('id',$id)->firstOrFail();
        $office = Office::where('id',$items->office_id)->firstOrFail();
        $region = Region::where('id',$office->region_id)->firstOrFail();
        $history = History::where('item_id', $items->id)->get();

        
        if (request()->ajax()) {
            $data = History::with('employee', 'item')->select('histories.*')->where('item_id', $items->id)->latest()->get();
            # Here 'items' is the name of table for Documents Model
            # And 'region' is the name of relation on Document Model.
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('item_str', function ($row) {
                    # 'name' is the field in table of Status Model
                    return $row->item->name;
                })
                ->addColumn('employee_str', function ($row) {
                    # 'name' is the field in table of Status Model
                    return $row->employee->name;
                })
                ->addcolumn('end_date', function ($row) {
                    if ($row->end_date == null) {
                        $end_date = '-';
                    } else {
                        $end_date = $row->end_date;
                    }
                    return $end_date;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="'. route('histories.edit', [$row->id]) .'" class =" edit btn btn-success btn-sm">Edit</a> 
                    <form action="'.route('histories.destroy',[$row->id]).'" method="POST" class="d-inline">'.method_field('delete') .csrf_field().'
                    <button class="delete btn btn-danger btn-sm" onclick="return confirm(\'Are You Sure?\')">Delete</button>
                    </form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $fullUrl = $request->fullUrl();
        var_dump($fullUrl);
        // dd($fullUrl);
        if ($fullUrl != null) {
            $request->session()->put('backUrl', $fullUrl);
        }
        $request->session()->put('backUrl', $fullUrl);

        // Session::flash('backUrl', Request::fullUrl());    
        // Session::flash('backUrl', request()->fullUrl());
        
        // if($request->session()->has('backUrl')){
        //     dd('ada');
        // }
        // dd( $request->session()->flash('backUrl', $fullUrl));
        
        
        // $request->session()->flash('backUrl', $request->fullUrl());
        
        return view('pages.admin.Item.detail',[
            'item' => $items,
            'office' => $office,
            'region'  => $region,
            'history' => $history
        ]);

        // return $history;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Item  $items
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        // $item = Item::where('id',$id)->firstOrFail();
        $office = Office::where('id',$item->office_id);
    
        return view('pages.admin.Item.edit', [
            'item' => $item,
            'office' => $office,
            'offices' => Office::all(),
            'regions' => Region::all(),
            'types' => Type::all(),
            'offices' => Office::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item  $items
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Item $item)
    {
        $data = $request->all();

        // $item = Item::where('id',$id)->firstOrFail();

        $validatedData = $request->validate([
            // 'id' => 'required',
            'name' => 'required',
            // 'office_id' => 'required',
            'type_id' => 'required',
            'region_id' => 'required',
            'description' => 'required',
        ]);

        $item->update($data,$validatedData);

        return redirect()->route('items.list')->with('success','Edit Success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item  $items
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        // $item = Item::where('id',$id);
        // $history = History_ownership::where('item_id',$id);

        // $history->delete();
        $item->delete();

        return redirect()->route('items.list')->with('success','Delete Success');
    }
}
