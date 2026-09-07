<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTreeRequest;
use App\Models\Tree;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // GET /api/trees — solo árboles del usuario autenticado, nunca de otros.
    public function index(Request $request)
    {
        $trees = Tree::with('treeType')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($trees);
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
    public function store(StoreTreeRequest $request)
    {
        // Solo tree_type_id y nickname vienen del cliente (ver StoreTreeRequest).
        // Todo lo demás (status, level, health, planted_at, last_care_at,
        // last_deterioration_check_at) lo define el servidor o los defaults de la migración.
        $tree = Tree::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => 'ACTIVE',
            'level' => 1,
            'health' => 100,
        ]);

        return response()->json($tree->load('treeType'), 201);
    }

    /**
     * Display the specified resource.
     */
    // GET /api/trees/{tree} — protegido por TreePolicy@view vía authorize()
    public function show(Request $request, Tree $tree)
    {
        $this->authorize('view', $tree);

        return response()->json($tree->load('treeType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tree $tree)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tree $tree)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tree $tree)
    {
        //
    }
}
