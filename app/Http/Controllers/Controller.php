<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Roles;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\UnitKemahasiswaan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    function setLog($caused, $performed, $log, $properties = null, $content = 'default')
    {
        activity()
            ->causedBy($caused)
            ->performedOn($performed)
            ->withProperties($properties)
            ->useLog($content)
            ->log($log);
    }

    function getJurusanOption()
    {
        $data = [];
        $data = Jurusan::get()->map(function ($item) {
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

        $dataUnit = UnitKemahasiswaan::where('status', true)->get()->map(function ($item) {
            return [
                'value' => $item->id . '|Unit',
                'label' => $item->name,
            ];
        });

        $dataDosen = Dosen::where('status', true)->get()->map(function ($item) {
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

        $data = UnitKemahasiswaan::where('status', true)->whereHas('user')->get()->map(function ($item) {
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
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->name,
                ];
            });

        return $data;
    }

    function getKaprodi($jurusanId)
    {
        $kaprodi = User::where('role_id', 4) // role user untuk kaprodi
            ->whereHas('userable', function ($query) use ($jurusanId) {
                $query->where('jurusan_id', $jurusanId);
            })
            ->first();

        return $kaprodi;
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
        $timestamp = now()->format('Ymd_His');
        $extension = $file->getClientOriginalExtension();
        $fileName = $originalName . '_' . $timestamp . '.' . $extension;
        $imagePath = Storage::putFileAs($basePath, $file, $fileName);

        return $imagePath;
    }

    function storageDelete($path)
    {
        if (Storage::exists($path)) {
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
}
