<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper\CrudController;

class MahasiswaController extends Controller
{
    public function index()
    {
        $optionJurusan = $this->getJurusanOption();
        return view('Pages.Mahasiswa.index', compact('optionJurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'npm' => 'required',
            'jurusan_id' => 'required',
        ]);

        try {
            $dataField = [
                'name' => $request->name,
                'npm' => $request->npm,
                'jurusan_id' => $request->jurusan_id,
                'status' => $request->boolean('status'),
            ];

            return DB::transaction(function () use ($dataField) {
                $Crud = new CrudController(Mahasiswa::class, dataField: $dataField, description: 'Menambah Mahasiswa', content: 'Mahasiswa');
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
            'npm' => 'required',
            'jurusan_id' => 'required',
        ]);

        try {
            $dataField = [
                'name' => $request->name,
                'npm' => $request->npm,
                'jurusan_id' => $request->jurusan_id,
                'status' => $request->boolean('status'),
            ];

            return DB::transaction(function () use ($dataField, $request) {
                $Crud = new CrudController(Mahasiswa::class, id: decrypt($request->id), dataField: $dataField, description: 'Merubah Mahasiswa', content: 'Mahasiswa');
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
        $data = Mahasiswa::with(['jurusan'])->select('name', 'npm', 'status', 'id', 'jurusan_id')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('npm', 'like', '%' . $request->search . '%')
                    ->orWhereRelation('jurusan', 'name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->getCollection()->transform(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'npm' => $item->npm,
                'jurusan' => $item->jurusan->name ?? '-',
                'jurusan_id' => $item->jurusan_id,
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
