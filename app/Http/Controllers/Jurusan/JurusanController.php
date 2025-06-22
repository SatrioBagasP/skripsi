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
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' Line: ' . $e->getLine() . ' on ' . $e->getFile();
            } else {
                $message = $e->getMessage();
            }
            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function update(Request $request)
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

            return DB::transaction(function () use ($dataField, $request) {
                $Crud = new CrudController(Jurusan::class, id: decrypt($request->id), dataField: $dataField, description: 'Merubah Jurusan', content: 'Jurusan');
                return $Crud->updateWithReturnJson();
            });
        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' Line: ' . $e->getLine() . ' on ' . $e->getFile();
            } else {
                $message = $e->getMessage();
            }
            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function getData(Request $request)
    {
        $data = [];
        $data = Jurusan::select('name', 'kode', 'status', 'id')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('kode', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);
        
        // dd($data);

        $dataFormated = $data->getCollection()->transform(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'kode' => $item->kode,
                'status' => $item->status,
            ];
        });

        return response()->json([
            'status' => '200',
            'data' => $dataFormated,
            'currentPage' => $data->currentPage(),
            'totalPage' => $data->lastPage(),
        ], 200);
    }
}
