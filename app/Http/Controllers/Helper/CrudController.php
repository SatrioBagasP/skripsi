<?php

namespace App\Http\Controllers\Helper;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CrudController extends Controller
{
    protected $field;
    protected $model;
    protected $id;
    protected $answer;
    protected $user;
    protected $description;
    protected $dataField;
    protected $content;


    function __construct($model, ?int $id = null, ?string $field = null, $answer = null, $user = null, $description = null, $dataField = [], $content = 'default')
    {
        $this->model = new $model;
        $this->id = $id;
        $this->field = $field;
        $this->answer = $answer;
        $this->user = $user;
        $this->description = $description;
        $this->dataField  = $dataField;
        $this->content = $content;
    }


    public function insertWithReturnData()
    {
        $data = $this->model->create($this->dataField);
        $this->setLog($this->user, $data, $this->description, [
            'changed' => $data,
        ], $this->content);
        return $data;
    }

    public function insertWithReturnJson()
    {
        $data = $this->model->create($this->dataField);
        $this->setLog($this->user, $data, $this->description, [
            'changed' => $data,
        ], $this->content);
        return response()->json([
            'status' => 200,
            'message' => 'Data Berhasil Ditambahkan',
        ], 200);
    }
}
