<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterQcKpi;

class MasterQcKpiController extends Controller
{
    public function index()
    {
        $kpis = MasterQcKpi::query()->orderBy('category')->orderBy('instruction')->get();
        return view('admin.master-qc.index', compact('kpis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'instruction' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        MasterQcKpi::create($validated);

        return redirect()->route('master-qc.index')->with('success', 'KPI berhasil ditambahkan');
    }

    public function update(Request $request, MasterQcKpi $master_qc)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'instruction' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $master_qc->update($validated);

        return redirect()->route('master-qc.index')->with('success', 'KPI berhasil diperbarui');
    }

    public function destroy(MasterQcKpi $master_qc)
    {
        $master_qc->delete();
        return redirect()->route('master-qc.index')->with('success', 'KPI berhasil dihapus');
    }
}
