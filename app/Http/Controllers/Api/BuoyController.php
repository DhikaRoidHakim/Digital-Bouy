<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuoyController extends Controller
{
    public function index()
    {
        return Buoy::all();
    }

    public function store(Request $request)
    {
        return Buoy::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $buoy = Buoy::findOrFail($id);
        $buoy->update($request->all());

        return $buoy;
    }

    public function delete($id)
    {
        Buoy::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

