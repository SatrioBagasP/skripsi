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
use App\Traits\UserValidation;

class UserController extends Controller
{
    use UserValidation;
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
            'selected_role' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();
            list($id, $type) = explode('|', $request->user_id,);
            $type = $type == 'Unit' ? UnitKemahasiswaan::class : ($type == 'Dosen' ? Dosen::class : null);
            $this->validateUserAlreadyHasAccount($id, $type);
            $this->validateUserAbleIsActive($id, $type);

            $data = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'userable_id' => $id,
                'userable_type' => $type,
                'password' => Hash::make($request->password),
                'status' => $request->boolean('status'),
            ]);
            $data->roles()->attach($request->selected_role);
            $this->updateLog($data, 'Menambah User', 'User');
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
            'email' => 'required',
            'user_id' => 'required',
            'selected_role' => 'required|array|min:1',
        ]);
        try {
            DB::beginTransaction();

            list($id, $type) = explode('|', $request->user_id,);
            $type = $type == 'Unit' ? UnitKemahasiswaan::class : ($type == 'Dosen' ? Dosen::class : null);

            $data = User::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingDataReturnException($data);

            // jika dia mengganti user ablenya
            if (($data->userable_id != $id && $data->userable_type != $type)) {
                $this->validateUserAlreadyHasAccount($id, $type);
                $this->validateUserAbleIsActive($id, $type);
            }
            $data->fill([
                'name' => $request->name,
                'email' => $request->email,
                'userable_id' => $id,
                'userable_type' => $type,
                'password' => Hash::make($request->password),
                'status' => $request->boolean('status'),
            ]);
            $data->roles()->sync($request->selected_role);
            $this->updateLog($data, 'Merubah User', 'User');
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
        $data = User::with(['roles', 'userable'])
            ->select('name', 'email', 'id', 'userable_type', 'userable_id')
            ->where('id', '!=', '1')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('npm', 'like', '%' . $request->search . '%')
                        ->orWhereRelation('jurusan', 'name', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->map(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'email' => $item->email,
                'user_type' => $item->userable_type == UnitKemahasiswaan::class ? 'Unit Kemahasiswaan' : ($item->userable_type == Dosen::class ? 'Dosen' : 'None'),
                'user_id' => $item->userable_type == UnitKemahasiswaan::class ?  $item->userable_id . '|Unit' : ($item->userable_type == Dosen::class ? $item->userable_id . '|Dosen' : 'None'),
                'status' => $item->userable?->status ?? 1,
                'selected_role' => $item->roles->map(function ($item) {
                    return $item->id;
                })->toArray(),
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
