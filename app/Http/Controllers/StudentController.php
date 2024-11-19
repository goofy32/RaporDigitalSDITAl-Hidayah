<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\StudentImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Log;


class StudentController extends Controller
{
    public function index()
    {
        $students = Siswa::with('kelas')->paginate(10);
        return view('admin.student', compact('students'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        return view('data.add_student', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|unique:siswas',
            'nisn' => 'required|unique:siswas',
            'nama' => 'required',
            'kelas_id' => 'required|exists:kelas,id',
            // tambahkan validasi lainnya sesuai kebutuhan
        ]);

        Siswa::create($validated);
        return redirect()->route('student')->with('success', 'Data siswa berhasil ditambahkan!');
    }

    public function show($id)
    {
        $student = Siswa::findOrFail($id);
        return view('admin.student_show', compact('student'));
    }

    public function edit($id)
    {
        $student = Siswa::findOrFail($id);
        $kelas = Kelas::all();
        return view('admin.student_edit', compact('student', 'kelas'));
    }

    public function update(Request $request, $id)
    {
        $student = Siswa::findOrFail($id);
        $validated = $request->validate([
            'nis' => 'required|unique:siswas,nis,' . $id,
            'nisn' => 'required|unique:siswas,nisn,' . $id,
            'nama' => 'required',
            'kelas_id' => 'required|exists:kelas,id',
            // tambahkan validasi lainnya sesuai kebutuhan
        ]);

        $student->update($validated);
        return redirect()->route('student')->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $student = Siswa::findOrFail($id);
        $student->delete();
        return redirect()->route('student')->with('success', 'Data siswa berhasil dihapus!');
    }

    public function uploadPage()
    {
        return view('data.upload_student');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
    
        try {
            DB::beginTransaction();
    
            $import = new StudentImport();
            Excel::import($import, $request->file('file'));
    
            // Ambil error dari import
            $importErrors = $import->getErrors();
    
            if (!empty($importErrors)) {
                DB::rollBack();
                Log::error('Import Errors:', $importErrors);
                return back()->with('error', $importErrors);
            }
    
            DB::commit();
    
            return redirect()->route('student')
                ->with('success', 'Data siswa berhasil diimpor!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Exception: ' . $e->getMessage());
            Log::error('Import Trace: ' . $e->getTraceAsString());
    
            return back()->with('error', [
                'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()
            ]);
        }
    }



    public function downloadTemplate()
    {
        $filePath = public_path('templates/Student_Template_with_Data.xlsx');

        if (!file_exists($filePath)) {
            abort(404, 'File template tidak ditemukan.');
        }

        return response()->download($filePath, 'Student_Template.xlsx');
    }
}