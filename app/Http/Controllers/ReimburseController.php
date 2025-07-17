<?php

namespace App\Http\Controllers;

use App\Models\Reimburse;
use Illuminate\Http\Request;

class ReimburseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reimburses = Reimburse::get();

        return response()->json($reimburses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reimburse $reimburse)
    {
        //        Gate::authorize('view', $reimburse);

        return response()->json($reimburse);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reimburse $reimburse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reimburse $reimburse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reimburse $reimburse)
    {
        //
    }
}
