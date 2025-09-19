<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\JurusanValidation;

class MahasiswaController extends Controller
{
    use JurusanValidation;

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
            'no_hp' => 'required|numeric|regex:/^08[0-9]{8,15}$/',
            'jurusan_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $this->validateJurusanIsActive($request->jurusan_id);
            $data = Mahasiswa::create([
                'name' => $request->name,
                'npm' => $request->npm,
                'no_hp' => $request->no_hp,
                'jurusan_id' => $request->jurusan_id,
                'status' => $request->boolean('status'),
            ]);
            $this->storeLog($data, 'Menambah Mahasiswa', 'Mahasiswa');

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
            'npm' => 'required',
            'no_hp' => 'required',
            'jurusan_id' => 'required',
        ]);

        try {
            $dataField = [
                'name' => $request->name,
                'npm' => $request->npm,
                'no_hp' => $request->no_hp,
                'jurusan_id' => $request->jurusan_id,
                'status' => $request->boolean('status'),
            ];
            DB::beginTransaction();
            $data = Mahasiswa::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingDataReturnException($data);

            if ($data->jurusan_id != $request->jurusan_id) {
                $this->validateJurusanIsActive($request->jurusan_id);
            }

            $data->fill([
                'name' => $request->name,
                'npm' => $request->npm,
                'no_hp' => $request->no_hp,
                'jurusan_id' => $request->jurusan_id,
                'status' => $request->boolean('status'),
            ]);

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
        $data = Mahasiswa::with(['jurusan'])->select('name', 'npm', 'status', 'id', 'jurusan_id', 'no_hp')
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
                'jurusan' => ($item->jurusan->name ?? '-') . ($item->jurusan && $item->jurusan->status == false ? ' (inactive)' : ''),
                'jurusan_id' => $item->jurusan_id,
                'no_hp' => $item->no_hp,
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
