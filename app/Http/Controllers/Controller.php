<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
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

    function storageStore(UploadedFile $file, $basePath)
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $timestamp = now()->format('Ymd_His');
        $extension = $file->getClientOriginalExtension();
        $fileName = $originalName . '_' . $timestamp . '.' . $extension;
        $imagePath = Storage::putFileAs($basePath, $file, $fileName);

        return $imagePath;
    }
}
