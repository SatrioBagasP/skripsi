<?php

namespace App\Http\Controllers\UnitKemahasiswaan;

use Illuminate\Http\Request;
use Twilio\TwiML\Voice\Stop;
use App\Models\UnitKemahasiswaan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use App\Traits\UnitKemahasiswaanValidation;
use App\Http\Controllers\Helper\CrudController;

class UnitKemahasiswaanController extends Controller
{
    use UnitKemahasiswaanValidation;

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
            DB::beginTransaction();

            $data = UnitKemahasiswaan::create([
                'name' => $request->name,
                'is_non_jurusan' => $request->boolean('is_non_jurusan'),
                'jurusan_id' =>  $request->boolean('is_non_jurusan') ? null : $request->jurusan_id,
                'image' => $imagePath,
                'status' => $request->boolean('status'),
            ]);
            $this->storeLog($data, 'Menambah Unit Kemahasiswaan', 'Unit Kemahasiswaan');

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $this->getStoreSuccessMessage(),
            ], 200);
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
            DB::beginTransaction();
            $data = UnitKemahasiswaan::with([
                'proposal' => function ($item) {
                    $item->lockForUpdate();
                }
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $imageOld = $data->image;

            $this->validateExistingDataReturnException($data);
            // jika dia merubah jurusan atau merubah non jursan maka cek pendingnya
            if ($data->is_non_jurusan != $request->boolean('is_non_jurusan') || $data->jurusan_id != $request->jurusan_id || $data->status != $request->status) {
                $this->validateUnitKemahasiswaanHasPendingProposal($data);
            }

            if ($request->file('image')) {
                $imagePath = $this->storageStore($request->file('image'), 'unit_kemahasiswaan');
            }
            $data->fill([
                'name' => $request->name,
                'jurusan_id' =>  $request->boolean('is_non_jurusan') ? null : $request->jurusan_id,
                'is_non_jurusan' => $request->boolean('is_non_jurusan'),
                'status' => $request->boolean('status'),
                'image' => $request->file('image') ? $imagePath : $imageOld,
            ]);
            $this->updateLog($data, 'Merubah Unit Kemahasiswaan', 'Unit Kemahasiswaan');

            if ($request->file('image')) {
                $this->storageDelete($imageOld);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $this->getUpdateSuccessMessage(),
            ], 200);
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
