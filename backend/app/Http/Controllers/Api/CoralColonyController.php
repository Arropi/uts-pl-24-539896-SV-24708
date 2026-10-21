<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoralColony;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoralColonyController extends Controller
{
    /**
     * Display a listing of coral colonies.
     */
    public function index()
    {
        $colonies = CoralColony::query()->latest()->get();

        return response()->json([
            'message' => 'Coral colonies retrieved successfully.',
            'data' => $colonies,
        ], 200);
    }

    /**
     * Store a newly created coral colony in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_fragmen' => 'required|string|max:50|unique:coral_colonies,kode_fragmen',
            'spesies_ilmiah' => 'required|string|max:255',
            'zona_terumbu' => 'required|string|max:255',
            'kedalaman_meter' => 'required|numeric|min:0',
            'persentase_hidup' => 'required|integer|between:0,100',
            'status_kesehatan' => 'required|string|max:100',
            'catatan_observasi' => 'nullable|string',
        ]);

        $colony = CoralColony::create($validated);

        return response()->json([
            'message' => 'Coral colony created successfully.',
            'data' => $colony,
        ], 201);
    }

    /**
     * Display the specified coral colony.
     */
    public function show(string $id)
    {
        $colony = CoralColony::find($id);

        if (! $colony) {
            return response()->json([
                'message' => 'Coral colony not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Coral colony retrieved successfully.',
            'data' => $colony,
        ], 200);
    }

    /**
     * Update the specified coral colony in storage.
     */
    public function update(Request $request, string $id)
    {
        $colony = CoralColony::find($id);

        if (! $colony) {
            return response()->json([
                'message' => 'Coral colony not found.',
            ], 404);
        }

        $validated = $request->validate([
            'kode_fragmen' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('coral_colonies', 'kode_fragmen')->ignore($colony->id),
            ],
            'spesies_ilmiah' => 'sometimes|required|string|max:255',
            'zona_terumbu' => 'sometimes|required|string|max:255',
            'kedalaman_meter' => 'sometimes|required|numeric|min:0',
            'persentase_hidup' => 'sometimes|required|integer|between:0,100',
            'status_kesehatan' => 'sometimes|required|string|max:100',
            'catatan_observasi' => 'nullable|string',
        ]);

        $colony->update($validated);

        return response()->json([
            'message' => 'Coral colony updated successfully.',
            'data' => $colony,
        ], 200);
    }

    /**
     * Remove the specified coral colony from storage.
     */
    public function destroy(string $id)
    {
        $colony = CoralColony::find($id);

        if (! $colony) {
            return response()->json([
                'message' => 'Coral colony not found.',
            ], 404);
        }

        $colony->delete();

        return response()->json([
            'message' => 'Coral colony deleted successfully.',
        ], 200);
    }
}
