<?php

namespace App\Http\Controllers\Features\Admin\Positions;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Position $position)
    {
        return response()->json($position->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Position $position)
    {
        $newPosition = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'required|numeric|unique',
            'per_party_count' => 'required|numeric',
            'choose_max_count' => 'required|numeric',
        ]);

        $newPosition = $position->create($newPosition);

        return response()->json(['message' => 'Position created.', 'position' => $newPosition], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function show(Position $position)
    {
        return response()->json($position);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Position $position)
    {
        $newPostion = $request->validate([
            'name' => 'required|string|max:255',
            'order' => ['required', 'numeric', 'digits:3', Rule::unique('positions')->ignore($position->id)],
            'per_party_count' => 'required|numeric|digits:3',
            'choose_max_count' => 'required|numeric|digits:3',
        ]);

        $status = $position->update($newPostion);

        return response()->json(['message' => 'Position updated', 'success' => $status]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function destroy(Position $position)
    {
        $status = $position->delete();

        return response()->json(['message' => 'Position deleted.', 'success' => $status]);
    }
}
