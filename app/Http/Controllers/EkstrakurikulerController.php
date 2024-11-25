<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $ekstrakurikulers = Ekstrakurikuler::paginate(10);
        return view('admin.ekstrakulikuler', compact('ekstrakurikulers'));
    }

    public function create()
    {
        return view('data.add_data_extracurriculer');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_ekstrakurikuler' => 'required|string|max:255',
                'pembina' => 'required|string|max:255',
            ], [
                'nama_ekstrakurikuler.required' => 'Nama ekstrakurikuler wajib diisi',
                'pembina.required' => 'Nama pembina wajib diisi',
            ]);

            Ekstrakurikuler::create($validated);

            return redirect()->route('ekstra.index')
                ->with('success', 'Data ekstrakurikuler berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating ekstrakurikuler: ' . $e->getMessage());
            return back()
                ->with('error', 'Terjadi kesalahan sistem')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $ekstrakurikuler = Ekstrakurikuler::findOrFail($id);
        return view('data.edit_data_extracurriculer', compact('ekstrakurikuler'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_ekstrakurikuler' => 'required|string|max:255',
                'pembina' => 'required|string|max:255',
            ], [
                'nama_ekstrakurikuler.required' => 'Nama ekstrakurikuler wajib diisi',
                'pembina.required' => 'Nama pembina wajib diisi',
            ]);

            $ekstrakurikuler = Ekstrakurikuler::findOrFail($id);
            $ekstrakurikuler->update($validated);

            return redirect()->route('ekstra.index')
                ->with('success', 'Data ekstrakurikuler berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating ekstrakurikuler: ' . $e->getMessage());
            return back()
                ->with('error', 'Terjadi kesalahan sistem')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $ekstrakurikuler = Ekstrakurikuler::findOrFail($id);
            $ekstrakurikuler->delete();

            return redirect()->route('ekstra.index')
                ->with('success', 'Data ekstrakurikuler berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting ekstrakurikuler: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem');
        }
    }
}