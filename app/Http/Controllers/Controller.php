<?php

namespace App\Http\Controllers;

use App\Models\Akademik;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\Roles;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\Ruangan;
use App\Models\UnitKemahasiswaan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    function setLog($caused, $performed, $log, $properties = null, $content)
    {
        activity()
            ->causedBy($caused)
            ->performedOn($performed)
            ->withProperties($properties)
            ->useLog($content)
            ->log($log);
    }

    public function getJabatanOption()
    {
        $data = [];
        $data = Jabatan::query()
            ->toBase()
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->name,
                ];
            });

        return $data;
    }

    public function getAkademikOption()
    {
        $data = [];
        $data = Akademik::where('status', true)
            ->toBase()
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->name,
                ];
            });

        return $data;
    }

    function getJurusanOption()
    {
        $data = [];
        $data = Jurusan::where('status', true)
            ->toBase()
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->name,
                ];
            });

        return $data;
    }

    function getUserableOption()
    {
        $data = [];

        $dataUnit = UnitKemahasiswaan::where('status', true)
            // ->whereDoesntHave('user')
            ->get()
            ->toBase()
            ->map(function ($item) {
                return [
                    'value' => $item->id . '|Unit',
                    'label' => $item->name,
                ];
            });

        $dataDosen = Dosen::where('status', true)
            // ->whereDoesntHave('user')
            // ->orWhereHas('user', function ($query){

            // })
            ->toBase()
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id . '|Dosen',
                    'label' => $item->name,
                ];
            });
        $data = $dataUnit->concat($dataDosen)->sortBy('label')->values();
        return $data;
    }

    function getOrganisasiOption()
    {
        $data = [];

        $data = UnitKemahasiswaan::where('status', true)
            ->whereHas('user')
            ->get()
            ->toBase()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->name,
                ];
            });

        return $data;
    }

    function getDosenOption($jurusan = null)
    {
        $data = [];

        $data = Dosen::where('status', true)
            ->when($jurusan != null, function ($query) use ($jurusan) {
                $query->where('jurusan_id', $jurusan);
            })
            // ->whereHas('user')
            ->toBase()
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->name,
                ];
            });

        return $data;
    }

    function getMahasiswaOption($jurusan = null)
    {
        $data = [];

        $data = Mahasiswa::where('status', true)
            ->when($jurusan != null, function ($query) use ($jurusan) {
                $query->where('jurusan_id', $jurusan);
            })
            ->toBase()
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->npm . ' | ' . $item->name,
                ];
            });

        return $data;
    }

    // function getRuanganOption()
    // {
    //     $data = [];

    //     $data = Ruangan::where('status', true)
    //         ->get()
    //         ->map(function ($item) {
    //             return [
    //                 'value' => $item->id,
    //                 'label' => $item->name,
    //             ];
    //         });

    //     return $data;
    // }

    function getKaprodi($jurusanId)
    {
        $kaprodi = Jurusan::select('ketua_id')
            ->whereHas('ketua', function ($q) {
                $q->where('status', true);
            })
            ->where('id', $jurusanId)
            ->first();

        return $kaprodi->ketua ?? null;
    }

    function getLayananMahasiswa()
    {
        $layananMahasiswa = Akademik::where('id', 1) // id layanan
            ->where('status', true)
            ->first();

        return $layananMahasiswa;
    }

    function getKepalaBagianMinatBakat()
    {
        $kepalaBagianMinatBakat = Akademik::where('id', 2) // id minat bakat
            ->select('ketua_id')
            ->where('status', true)
            ->whereHas('ketua', function ($q) {
                $q->where('status', true);
            })
            ->first();

        return $kepalaBagianMinatBakat->ketua;
    }

    function getMinatBakat()
    {
        $minatBakat = Akademik::where('id', 2) // id minat bakat
            ->where('status', true)
            ->first();

        return $minatBakat;
    }

    function getWakilRektor1()
    {
        $wakilRektor = Dosen::where('jabatan_id', 3) // id jabatan wakil rektor 1
            ->where('status', true)
            ->first();

        return $wakilRektor;
    }

    function getRoleOption()
    {
        $data = [];
        $data = Roles::get()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->name,
            ];
        });

        return $data;
    }

    function storageStore(UploadedFile $file, $basePath)
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $timestamp = now()->format('Ymd_Hisu');
        $extension = $file->getClientOriginalExtension();
        $fileName = $originalName . '_' . $timestamp . '.' . $extension;
        $imagePath = Storage::putFileAs($basePath, $file, $fileName);

        return $imagePath;
    }

    function storageDelete($path)
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    function getRomawi($bulan)
    {
        switch (intval($bulan)) {
            case 1:
                return "I";
                break;
            case 2:
                return "II";
                break;
            case 3:
                return "III";
                break;
            case 4:
                return "IV";
                break;
            case 5:
                return "V";
                break;
            case 6:
                return "VI";
                break;
            case 7:
                return "VII";
                break;
            case 8:
                return "VIII";
                break;
            case 9:
                return "IX";
                break;
            case 10:
                return "X";
                break;
            case 11:
                return "XI";
                break;
            case 12:
                return "XII";
                break;
        }
    }

    function getErrorMessage($e)
    {
        $message = '';
        if (app()->environment('local')) {
            $message = $e->getMessage() . ' Line: ' . $e->getLine() . ' on ' . $e->getFile();
        } else {
            $message = $e->getMessage();
        }
        return $message;
    }

    function storeLog($data, $description, $content = 'default')
    {
        $this->setLog(Auth::user(), $data, $description, [
            'changed' => $data,
        ], $content);
    }

    // automatic save and set log
    function updateLog($data, $description, $content = 'default')
    {
        if ($data->isDirty()) {
            $old = $data->getOriginal();
            $data->save();
            $change = $data->getChanges();
            $oldField = array_intersect_key(
                $old,
                array_flip(array_keys($change))
            );
            $this->setLog(Auth::user(), $data, $description, [
                'old' => $oldField,
                'changed' => $change,
            ], $content);
        }
    }

    function deleteLog($data, $description, $content = 'default')
    {
        $old = $data->getOriginal();
        $this->setLog(Auth::user(), $data, $description, [
            'old' => $old
        ], $content);
    }

    function getStoreSuccessMessage()
    {
        return 'Data Berhasil Disimpan';
    }
    function getUpdateSuccessMessage()
    {
        return 'Data Berhasil Diperbarui';
    }
    function getDeleteSuccessMessage()
    {
        return 'Data Berhasil Dihapus';
    }
}
