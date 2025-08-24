<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper\CrudController;
use App\Traits\JurusanValidation;

class DosenController extends Controller
{

    use JurusanValidation;

    public function index()
    {
        $jabatanOption = $this->getJabatanOption();
        $optionJurusan = $this->getJurusanOption();
        $akademikOption = $this->getAkademikOption();
        return view('Pages.Dosen.index', compact('optionJurusan', 'jabatanOption', 'akademikOption'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nip' => 'required',
            'jurusan_id' => 'required',
            'jabatan_id' => 'required',
            'no_hp' => 'required|numeric|regex:/^08[0-9]{8,15}$/',
            'alamat' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $this->validateJurusanIsActive($request->jurusan_id);
            $data = Dosen::create([
                'name' => $request->name,
                'nip' => $request->nip,
                'jurusan_id' => $request->jurusan_id,
                'jabatan_id' => $request->jabatan_id,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'status' => $request->boolean('status'),
            ]);
            $data->akademik()->attach($request->akademik_id);
            $this->storeLog($data, 'Menambah Dosen', 'Dosen');

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
            'nip' => 'required',
            'jurusan_id' => 'required',
            'jabatan_id' => 'required',
            'no_hp' => 'required',
            'alamat' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $data = Dosen::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingData($data);

            if ($data->jurusan_id != $request->jurusan_id) {
                $this->validateJurusanIsActive($request->jurusan_id);
            }

            $data->fill([
                'name' => $request->name,
                'nip' => $request->nip,
                'jurusan_id' => $request->jurusan_id,
                'jabatan_id' => $request->jabatan_id,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'status' => $request->boolean('status'),
            ]);
            $data->akademik()->sync($request->akademik_id);
            $this->updateLog($data, 'Merubah Dosen', 'Dosen');

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
        $data = Dosen::with(['jurusan'])->select('name', 'nip', 'status', 'id', 'jurusan_id', 'no_hp', 'alamat', 'jabatan_id')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nip', 'like', '%' . $request->search . '%')
                    ->orWhereRelation('jurusan', 'name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->getCollection()->transform(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'nip' => $item->nip,
                'jurusan' => ($item->jurusan->name ?? '-') . ($item->jurusan && $item->jurusan->status == false ? ' (inactive)' : ''),
                'jurusan_id' => $item->jurusan_id,
                'jabatan_id' => $item->jabatan_id,
                'akademik_id' => $item->akademik->map(function ($item) {
                    return $item->id;
                }),
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
