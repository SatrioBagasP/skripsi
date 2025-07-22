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
            'jurusan_id' => 'required_if:is_non_jurusan,false',
            'image' => ['sometimes', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024)]
        ]);

        try {
            $imagePath = '';
            if ($request->file('image')) {
                $imagePath = $this->storageStore($request->file('image'), 'unit_kemahasiswaan');
            }

            $dataField = [
                'name' => $request->name,
                'is_non_jurusan' => false,
                'jurusan_id' =>  $request->boolean('is_non_jurusan') ? null : $request->jurusan_id,
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
            'jurusan_id' => 'required_if:is_non_jurusan,false',
        ]);

        try {
            $imagePath = null;
            $data = UnitKemahasiswaan::with('user.proposal')
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $imageOld = $data->image;

            // jika dia awalnya itu non jurusan lalu dirubah ke non jurusan atau sebalik nya, bakalan ada alert ga bisa krn ada proposal yang sudah dalam pengajuan 
            if (!$data) {
                throw new \Exception('Data unit kemahasiswaan tidak ada atau telah dihapus, silahkan refresh halaman ini');
            } elseif ($data->is_non_jurusan != $request->boolean('is_non_jurusan') && $data->user && $data->user->proposal->isNotEmpty()) {
                $data->user->proposal->each(function ($item) {
                    if (!in_array($item->status, ['Draft', 'Rejected', 'Accepted'])) {
                        throw new \Exception('Tidak bisa merubah unit kegiatan menjadi ke jurusan / non jurusan, dikarenakan ada proposal pada unit ini masih tahap pengecekan oleh verifikator');
                    }
                });
            }

            $dataField = [
                'name' => $request->name,
                'jurusan_id' =>  $request->boolean('is_non_jurusan') ? null : $request->jurusan_id,
                'is_non_jurusan' => $request->boolean('is_non_jurusan'),
                'status' => $request->boolean('status'),
            ];

            if ($request->file('image')) {
                $imagePath = $this->storageStore($request->file('image'), 'unit_kemahasiswaan');
                $dataField['image'] = $imagePath;
            }
            DB::beginTransaction();

            $Crud = new CrudController(UnitKemahasiswaan::class, data: $data, dataField: $dataField, description: 'Merubah Unit Kemahasiswaan', content: 'Unit Kemahasiswaan');
            $action = $Crud->updateWithReturnJson();

            if ($request->file('image')) {
                $this->storageDelete($imageOld);
            }

            DB::commit();
            return $action;
        } catch (\Throwable $e) {
            // $this->storageDelete($imagePath);
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
        $data = UnitKemahasiswaan::with(['jurusan'])->select('name', 'image', 'status', 'id', 'jurusan_id', 'is_non_jurusan')
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
                'is_non_jurusan' => $item->is_non_jurusan,
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
