<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper\CrudController;

class DosenController extends Controller
{
    public function index()
    {
        $optionJurusan = $this->getJurusanOption();
        return view('Pages.Dosen.index', compact('optionJurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nip' => 'required',
            'jurusan_id' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
        ]);

        try {
            $dataField = [
                'name' => $request->name,
                'nip' => $request->nip,
                'jurusan_id' => $request->jurusan_id,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'status' => $request->boolean('status'),
            ];

            // dd($dataField);

            return DB::transaction(function () use ($dataField) {
                $Crud = new CrudController(Dosen::class, dataField: $dataField, description: 'Menambah Dosen', content: 'Dosen');
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
            'nip' => 'required',
            'jurusan_id' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
        ]);

        try {
            $dataField = [
                'name' => $request->name,
                'nip' => $request->nip,
                'jurusan_id' => $request->jurusan_id,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'status' => $request->boolean('status'),
            ];

            return DB::transaction(function () use ($dataField, $request) {
                $Crud = new CrudController(Dosen::class, id: decrypt($request->id), dataField: $dataField, description: 'Merubah Dosen', content: 'Dosen');
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
        $data = Dosen::with(['jurusan'])->select('name', 'nip', 'status', 'id', 'jurusan_id', 'no_hp', 'alamat')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nip', 'like', '%' . $request->search . '%')
                    ->orWhereRelation('jurusan', 'name', $request->search);
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->getCollection()->transform(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'nip' => $item->nip,
                'jurusan' => $item->jurusan->name ?? '-',
                'jurusan_id' => $item->jurusan_id,
                'status' => $item->status,
                'no_hp' => $item->no_hp,
                'alamat' => $item->alamat,
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
