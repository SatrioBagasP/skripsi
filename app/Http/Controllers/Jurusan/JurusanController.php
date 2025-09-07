<?php

namespace App\Http\Controllers\Jurusan;

use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\DosenValidation;
use App\Traits\JurusanValidation;

class JurusanController extends Controller
{

    use JurusanValidation;

    public function index()
    {
        $dataDosen = $this->getDosenOption();
        return view('Pages.Jurusan.index', compact('dataDosen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'kode' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if ($request->ketua_id) {
                $this->validateKetuaJurusan($request->ketua_id);
            }

            $data = Jurusan::create([
                'name' => $request->name,
                'kode' => $request->kode,
                'ketua_id' => $request->ketua_id,
                'status' => $request->boolean('status'),
            ]);

            $this->storeLog($data, 'Menambah Jurusan', 'Jurusan');

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $this->getStoreSuccessMessage(),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => $this->getErrorMessage($e),
            ], 400);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'kode' => 'required',
            'ketua_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $data = Jurusan::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingDataReturnException($data);

            if ($request->ketua_id != $data->ketua_id) {
                $this->validateKetuaJurusan($request->ketua_id);
            }
            $data->fill([
                'name' => $request->name,
                'kode' => $request->kode,
                'ketua_id' => $request->ketua_id,
                'status' => $request->boolean('status'),
            ]);
            $this->updateLog($data, 'Merubah Jurusan', 'Jurusan');

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $this->getUpdateSuccessMessage(),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => $this->getErrorMessage($e),
            ], 400);
        }
    }

    public function getData(Request $request)
    {
        $data = [];
        $data = Jurusan::with([
            'ketua' => function ($item) {
                $item->select('id', 'name', 'status', 'jurusan_id');
            },
        ])
            ->select('name', 'kode', 'status', 'id', 'ketua_id')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('kode', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->map(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'kode' => $item->kode,
                'ketua_id' => $item->ketua_id,
                'ketua' => ($item->ketua->name ?? '-')  . ($item->ketua && $item->ketua->status == false ? ' (inactive)' : ''),
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
