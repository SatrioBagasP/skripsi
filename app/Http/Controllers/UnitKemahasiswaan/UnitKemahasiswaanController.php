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
            'no_hp' => 'required',
            'jurusan_id' => 'required',
            'image' => ['sometimes', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024)]
        ]);

        try {
            $imagePath = $this->storageStore($request->file('image'), 'unit_kemahasiswaan');

            $dataField = [
                'name' => $request->name,
                'no_hp' => $request->no_hp,
                'jurusan_id' => $request->jurusan_id,
                'image' => $imagePath,
                'status' => $request->boolean('status'),
            ];

            return DB::transaction(function () use ($dataField) {
                $Crud = new CrudController(UnitKemahasiswaan::class, dataField: $dataField, description: 'Menambah Unit Kemahasiswaan', content: 'Unit Kemahasiswaan');
                return $Crud->insertWithReturnJson();
            });
        } catch (\Throwable $e) {
            Storage::delete($imagePath);

            if (app()->environment('local')) {
                $message = $e->getMessage() . ' Line: ' . $e->getLine() . ' on ' . $e->getFile();
            } else {
                $message = $e->getMessage();
            }
            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'no_hp' => 'required',
            'jurusan_id' => 'required',
        ]);

        try {
            $imagePath = null;
            $imageOld = UnitKemahasiswaan::select('image')->where('id', decrypt($request->id))->first();
    
            $dataField = [
                'name' => $request->name,
                'no_hp' => $request->no_hp,
                'jurusan_id' => $request->jurusan_id,
                'status' => $request->boolean('status'),
            ];
            
            if ($request->image) {
                $imagePath = $this->storageStore($request->file('image'), 'unit_kemahasiswaan');
                $dataField['image'] = $imagePath;
            }

            return DB::transaction(function () use ($dataField, $request, $imageOld) {
                $Crud = new CrudController(UnitKemahasiswaan::class, id: decrypt($request->id), dataField: $dataField, description: 'Merubah Unit Kemahasiswaan', content: 'Unit Kemahasiswaan');
                Storage::delete($imageOld->image);
                return $Crud->updateWithReturnJson();
            });
        } catch (\Throwable $e) {
            Storage::delete($imagePath);
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' Line: ' . $e->getLine() . ' on ' . $e->getFile();
            } else {
                $message = $e->getMessage();
            }
            return response()->json([
                'status' => 400,
                'message' => $message,
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
                'image' => Storage::temporaryUrl($item->image, now()->addMinutes(5)),
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
