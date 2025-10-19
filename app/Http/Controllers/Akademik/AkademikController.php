<?php

namespace App\Http\Controllers\Akademik;

use App\Models\Akademik;
use Illuminate\Http\Request;
use App\Traits\DosenValidation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AkademikController extends Controller
{
    use DosenValidation;

    public function index(Request $request)
    {
        $dataDosen = $this->getDosenOption();
        return view('Pages.Akademik.index', compact('dataDosen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:akademik,name',
            'no_hp' => 'required|numeric|regex:/^08[0-9]{8,15}$/',
        ]);

        try {
            DB::beginTransaction();

            if ($request->ketua_id) {
                $this->validateDosenIsActive($request->ketua_id);
            }
            $data = Akademik::create([
                'name' => $request->name,
                'no_hp' => $request->no_hp,
                'ketua_id' => $request->ketua_id,
                'status' => $request->boolean('status'),
            ]);
            $this->storeLog($data, 'Menambah Akademik', 'Akademik');

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
        $id = decrypt($request->id);
        $request->validate([
            'name' => [
                'required',
                Rule::unique('akademik', 'name')->ignore($id),
            ],
            'no_hp' => 'required|numeric|regex:/^08[0-9]{8,15}$/',
            'ketua_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $data = Akademik::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingDataReturnException($data);

            if ($request->ketua_id != $data->ketua_id) {
                $this->validateDosenIsActive($request->ketua_id);
            }

            $data->fill([
                'name' => $request->name,
                'no_hp' => $request->no_hp,
                'ketua_id' => $request->ketua_id,
                'status' => $request->boolean('status'),
            ]);
            $this->updateLog($data, 'Merubah Akademik', 'Akademik');

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
        $data = Akademik::with([
            'ketua' => function ($item) {
                $item->select('id', 'name', 'status');
            },
        ])
            ->select('name', 'no_hp', 'status', 'id', 'ketua_id')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('no_hp', 'like', '%' . $request->search . '%')
                    ->orWhereRelation('ketua', 'name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->map(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'no_hp' => $item->no_hp,
                'ketua_id' => $item->ketua_id,
                'ketua' => ($item->ketua?->name ?? '-') . ($item->ketua && $item->ketua->status == false ? ' (inactive)' : ''),
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
