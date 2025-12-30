<?php

namespace App\Http\Controllers;

use App\Models\Buoy;
use Illuminate\Http\Request;

class BuoyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buoys = Buoy::all();
        return view('buoys.index', compact('buoys'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('buoys.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|unique:buoys',
            'name' => 'required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'required|integer',
            'status' => 'required'
        ]);

        Buoy::create($request->all());

        return redirect()->route('buoys.index')->with('success', 'Buoy created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Buoy $buoy)
    {
        return view('buoys.edit', compact('buoy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Buoy $buoy)
    {
        $request->validate([
            'device_id' => 'required|unique:buoys,device_id,' . $buoy->id,
            'name' => 'required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'required|integer',
            'status' => 'required'
        ]);

        $buoy->update($request->all());

        return redirect()->route('buoys.index')->with('success', 'Buoy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Buoy $buoy)
    {
        $buoy->delete();
        return redirect()->route('buoys.index')->with('success', 'Buoy deleted successfully.');
    }
}
