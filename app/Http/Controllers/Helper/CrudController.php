<?php

namespace App\Http\Controllers\Helper;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\CommonValidation;

class CrudController extends Controller
{
    use CommonValidation;

    protected $field;
    protected $model;
    protected $data;
    protected $id;
    protected $answer;
    protected $user;
    protected $description;
    protected $dataField;
    protected $content;
    protected $withLog;


    function __construct($model, $data = null, ?int $id = null, ?string $field = null, $answer = null, $user = null, $description = null, $dataField = [], $content = 'default', $withLog = true)
    {
        $this->model = new $model;
        $this->id = $id;
        $this->field = $field;
        $this->answer = $answer;
        $this->user = $user;
        $this->description = $description;
        $this->dataField  = $dataField;
        $this->content = $content;
        $this->data = $data;
        $this->withLog = $withLog;
    }


    public function insertWithReturnData()
    {
        $data = $this->model->create($this->dataField);
        if ($this->withLog) {
            $this->setLog($this->user, $data, $this->description, [
                'changed' => $data,
            ], $this->content);
        }

        return $data;
    }

    public function insertWithReturnJson()
    {
        $data = $this->model->create($this->dataField);
        if ($this->withLog) {
            $this->setLog($this->user, $data, $this->description, [
                'changed' => $data,
            ], $this->content);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Data Berhasil Ditambahkan',
        ], 200);
    }

    public function updateWithReturnData()
    {
        if ($this->data == null) {
            $data = $this->model->where('id', $this->id)->lockForUpdate()->first();
            $this->validateExistingDataReturnException($data);
        } else {
            $data = $this->data;
        }

        $old = $data->getOriginal();
        $data->fill($this->dataField);

        if ($data->isDirty()) {
            $data->save();
            if ($this->withLog) {
                $change = $data->getChanges();
                $oldField = array_intersect_key(
                    $old,
                    array_flip(array_keys($change))
                );
                $this->setLog($this->user, $data, $this->description, [
                    'old' => $oldField,
                    'changed' => $change,
                ], $this->content);
            }
        }
        return $data;
    }

    public function updateWithReturnJson()
    {
        if ($this->data == null) {
            $data = $this->model->where('id', $this->id)->lockForUpdate()->first();
            $this->validateExistingDataReturnException($data);
        } else {
            $data = $this->data;
        }
        $old = $data->getOriginal();
        $data->fill($this->dataField);

        if ($data->isDirty()) {
            $data->save();
            if ($this->withLog) {
                $change = $data->getChanges();
                $oldField = array_intersect_key(
                    $old,
                    array_flip(array_keys($change))
                );
                $this->setLog($this->user, $data, $this->description, [
                    'old' => $oldField,
                    'changed' => $change,
                ], $this->content);
            }
        }
        return response()->json([
            'status' => 200,
            'message' => 'Data Berhasil Dirubah',
        ], 200);
    }

    public function deleteWithReturnData()
    {
        if ($this->data == null) {
            $data = $this->model->where('id', $this->id)->lockForUpdate()->first();
            $this->validateExistingDataReturnException($data);
        } else {
            $data = $this->data;
        }
        $old = $data->getOriginal();
        $data->delete();
        if ($this->withLog) {
            $this->setLog($this->user, $data, $this->description, [
                'old' => $old
            ], $this->content);
        }

        return $old;
    }

    public function deleteWithReturnJson()
    {
        if ($this->data == null) {
            $data = $this->model->where('id', $this->id)->lockForUpdate()->first();
            $this->validateExistingDataReturnException($data);
        } else {
            $data = $this->data;
        }
        $old = $data->getOriginal();
        $data->delete();
        if ($this->withLog) {
            $this->setLog($this->user, $data, $this->description, [
                'old' => $old
            ], $this->content);
        }


        return response()->json([
            'status' => 200,
            'message' => 'Data Berhasil Dihapus!',
        ], 200);
    }
}
