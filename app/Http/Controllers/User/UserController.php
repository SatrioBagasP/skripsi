<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Dosen;
use Illuminate\Http\Request;
use App\Models\UnitKemahasiswaan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper\CrudController;

class UserController extends Controller
{
    public function index()
    {
        $userAbleOption = $this->getUserableOption();
        $roleOption = $this->getRoleOption();
        return view('Pages.User.index', compact('userAbleOption', 'roleOption'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'user_id' => 'required',
            'role_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            list($id, $type) = explode('|', $request->user_id,);
            $type = $type == 'Unit' ? UnitKemahasiswaan::class : ($type == 'Dosen' ? Dosen::class : null);

            $dataField = [
                'name' => $request->name,
                'email' => $request->email,
                'userable_id' => $id,
                'userable_type' => $type,
                'password' => Hash::make($request->password),
                'status' => $request->boolean('status'),
                'role_id' => $request->role_id,
            ];
            $model = new $type;
            $userAble = $model->where('id', $id)->lockForUpdate()->first();
            if ($userAble->status == 0) {
                throw new \Exception('User Tidak Aktif');
            }
            $Crud = new CrudController(User::class, dataField: $dataField, description: 'Menambah User', content: 'User');
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
            'email' => 'required',
            'user_id' => 'required',
            'role_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            list($id, $type) = explode('|', $request->user_id,);
            $type = $type == 'Unit' ? UnitKemahasiswaan::class : ($type == 'Dosen' ? Dosen::class : null);

            $dataField = [
                'name' => $request->name,
                'email' => $request->email,
                'userable_id' => $id,
                'userable_type' => $type,
                'password' => Hash::make($request->password),
                'status' => $request->boolean('status'),
                'role_id' => $request->role_id,
            ];

            $Crud = new CrudController(User::class, id: decrypt($request->id), dataField: $dataField, description: 'Merubah User', content: 'User');
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
        $data = User::with(['roles', 'userable'])->select('name', 'email', 'id', 'role_id', 'userable_type', 'userable_id')
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
                'email' => $item->email,
                'role' => $item->roles->name ?? '-',
                'user_type' => $item->userable_type == UnitKemahasiswaan::class ? 'Unit Kemahasiswaan' : ($item->userable_type == Dosen::class ? 'Dosen' : 'None'),
                'user_id' => $item->userable_type == UnitKemahasiswaan::class ?  $item->userable_id . '|Unit' : ($item->userable_type == Dosen::class ? $item->userable_id . '|Dosen' : 'None'),
                'role_id' => $item->role_id,
                'status' => $item->userable?->status ?? 1,
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
