<?php

namespace App\Http\Controllers\UnitKemahasiswaan;

use Illuminate\Http\Request;
use App\Models\UnitKemahasiswaan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Helper\CrudController;
use Twilio\TwiML\Voice\Stop;

class UnitKemahasiswaanController extends Controller
{
    public function index()
    {
        $optionJurusan = $this->getJurusanOption();
        return view('Pages.UnitKemahasiswaan.index', compact('optionJurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            // 'jurusan_id' => 'required',
            'image' => ['sometimes', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024)]
        ]);

        try {
            $imagePath = '';
            if ($request->file('image')) {
                $imagePath = $this->storageStore($request->file('image'), 'unit_kemahasiswaan');
            }

            $dataField = [
                'name' => $request->name,
                'jurusan_id' => $request->jurusan_id,
                'image' => $imagePath,
                'status' => $request->boolean('status'),
            ];
            DB::beginTransaction();

            $Crud = new CrudController(UnitKemahasiswaan::class, dataField: $dataField, description: 'Menambah Unit Kemahasiswaan', content: 'Unit Kemahasiswaan');
            $action =  $Crud->insertWithReturnJson();

            DB::commit();
            return $action;
        } catch (\Throwable $e) {
            $this->storageDelete($imagePath);
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
            // 'jurusan_id' => 'required',
        ]);

        try {
            $imagePath = null;
            $imageOld = UnitKemahasiswaan::select('image')->where('id', decrypt($request->id))->first();

            $dataField = [
                'name' => $request->name,
                // 'jurusan_id' => $request->jurusan_id,
                'status' => $request->boolean('status'),
            ];

            if ($request->file('image')) {
                $imagePath = $this->storageStore($request->file('image'), 'unit_kemahasiswaan');
                $dataField['image'] = $imagePath;
            }
            DB::beginTransaction();

            $Crud = new CrudController(UnitKemahasiswaan::class, id: decrypt($request->id), dataField: $dataField, description: 'Merubah Unit Kemahasiswaan', content: 'Unit Kemahasiswaan');
            $action = $Crud->updateWithReturnJson();

            if ($request->file('image')) {
                $this->storageDelete($imageOld->image);
            }

            DB::commit();
            return $action;
        } catch (\Throwable $e) {
            $this->storageDelete($imagePath);
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
        $data = UnitKemahasiswaan::with(['jurusan'])->select('name', 'no_hp', 'image', 'status', 'id', 'jurusan_id')
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhereRelation('jurusan', 'name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->getCollection()->transform(function ($item) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'no_hp' => $item->no_hp,
                'jurusan' => $item->jurusan->name ?? '-',
                'image' => $item->image ? Storage::temporaryUrl($item->image, now()->addMinutes(5)) : null,
                'jurusan_id' => $item->jurusan_id,
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
