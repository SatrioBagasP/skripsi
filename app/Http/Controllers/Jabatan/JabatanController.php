<?php

namespace App\Http\Controllers\Jabatan;

use Illuminate\Http\Request;
use App\Traits\CommonValidation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Jabatan;

class JabatanController extends Controller
{
    use CommonValidation;

    public function index(Request $request)
    {
        return view('Pages.Jabatan.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $data = Jabatan::create([
                'name' => $request->name,
            ]);
            $this->storeLog($data, 'Menambah Jabatan', 'Jabatan');

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
        ]);

        try {
            DB::beginTransaction();

            $data = Jabatan::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingData($data);

            $data->fill([
                'name' => $request->name,
            ]);
            $this->updateLog($data, 'Merubah Jabatan', 'Jabatan');

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
        $data = Jabatan::select('name', 'id')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
                // ->orWhere('no_hp', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->map(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
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
