<?php

namespace App\Http\Controllers\Jurusan;

use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper\CrudController;
use App\Traits\CommonValidation;

class JurusanController extends Controller
{

    use CommonValidation;

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
            DB::beginTransaction();

            $data = Jurusan::create([
                'name' => $request->name,
                'kode' => $request->kode,
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
        ]);

        try {
            DB::beginTransaction();

            $data = Jurusan::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingData($data);

            $data->fill([
                'name' => $request->name,
                'kode' => $request->kode,
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
        $data = Jurusan::select('name', 'kode', 'status', 'id')
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
