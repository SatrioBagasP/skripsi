<?php

namespace App\Http\Controllers\Ruangan;

use Throwable;
use App\Models\Ruangan;
use App\Models\Akademik;
use Illuminate\Http\Request;
use App\Traits\CommonValidation;
use App\Traits\ProposalValidation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RuanganController extends Controller
{

    use CommonValidation, ProposalValidation;

    public function index(Request $request)
    {
        $dataDosen = $this->getDosenOption();
        return view('Pages.Ruangan.index', compact('dataDosen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $data = Ruangan::create([
                'name' => $request->name,
                'status' => $request->boolean('status'),
            ]);
            $this->storeLog($data, 'Menambah Ruangan', 'Ruangan');

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

            $data = Ruangan::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingDataReturnException($data);

            $data->fill([
                'name' => $request->name,
                'status' => $request->boolean('status'),
            ]);
            $this->updateLog($data, 'Merubah Ruangan', 'Ruangan');

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
        $data = Ruangan::select('name', 'status', 'id')
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


    public function getRuanganOption(Request $request)
    {
        try {
            [$startDate, $endDate] = $this->validateDate($request);

            $availableRuangan = Ruangan::where(function ($query) use ($startDate, $endDate) {
                $query->whereDoesntHave('proposal', function ($q) use ($startDate, $endDate) {
                    $q->where(function ($query) use ($startDate, $endDate) {
                        $query->whereNotIn('status', ['Draft', 'Rejected'])
                            ->where('start_date', '<=', $endDate)
                            ->where('end_date', '>=', $startDate);
                    });
                })->where('status', true);
            })
                // ->when($request->id != null, function ($query) use ($request) {
                //     $query->orWhereHas('proposal', function ($q) use ($request) {
                //         $q->where('proposal.id', decrypt($request->id));
                //     });
                // })
                ->get()
                ->map(function ($item) {
                    return [
                        'value' => $item->id,
                        'label' => $item->name,
                    ];
                });
            return response()->json([
                'status' => 200,
                'data' => $availableRuangan,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $this->getErrorMessage($e),
            ], 400);
        }
    }
}
