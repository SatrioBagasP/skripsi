<?php

namespace App\Http\Controllers\Akademik;

use App\Models\Akademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper\CrudController;

class AkademikController extends Controller
{
    public function index(Request $request)
    {
        $dataDosen = $this->getDosenOption();
        return view('Pages.Akademik.index', compact('dataDosen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'no_hp' => 'required|numeric|regex:/^08[0-9]{8,15}$/',
        ]);

        try {
            $dataField = [
                'name' => $request->name,
                'no_hp' => $request->no_hp,
                'ketua_id' => $request->ketua_id,
                'status' => $request->boolean('status'),
            ];
            DB::beginTransaction();

            $Crud = new CrudController(Akademik::class, dataField: $dataField, description: 'Menambah Akademik', content: 'Akademik');
            $action = $Crud->insertWithReturnJson();

            DB::commit();
            return $action;
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
            'no_hp' => 'required|numeric|regex:/^08[0-9]{8,15}$/',
        ]);

        try {
            $dataField = [
                'name' => $request->name,
                'no_hp' => $request->no_hp,
                'ketua_id' => $request->ketua_id,
                'status' => $request->boolean('status'),
            ];

            DB::beginTransaction();

            $Crud = new CrudController(Akademik::class, id: decrypt($request->id), dataField: $dataField, description: 'Merubah Akademik', content: 'Akademik');
            $action = $Crud->updateWithReturnJson();

            DB::commit();
            return $action;
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
        $data = Akademik::select('name', 'no_hp', 'status', 'id')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('no_hp', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        // dd($data);

        $dataFormated = $data->getCollection()->transform(function ($item) {
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
