<?php

namespace App\Http\Controllers\Jurusan;

use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper\CrudController;

class JurusanController extends Controller
{
    public function index()
    {
        return view('Pages.Jurusan.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'kode' => 'required',
        ]);

        try {
            $dataField = [
                'name' => $request->name,
                'kode' => $request->kode,
                'status' => $request->boolean('status'),
            ];

            return DB::transaction(function () use ($dataField) {
                $Crud = new CrudController(Jurusan::class, dataField: $dataField, description: 'Menambah Jurusan', content: 'Jurusan');
                return $Crud->insertWithReturnJson();
            });

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage() . ' Line: ' . $e->getLine() . ' on ' . $e->getFile(),
            ],400);
        }
    }
}
