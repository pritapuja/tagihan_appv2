<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Siswa as Model;
use App\Models\User;

class SiswaController extends Controller
{
    private $viewIndex = 'siswa_index';
    private $viewCreate = 'siswa_form';
    private $viewEdit = 'siswa_form';
    private $viewShow = 'siswa_show';
    private $routePrefix = 'siswa';


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('operator.' . $this->viewIndex, [
            'models' => Model::latest()
                ->paginate(50),
            'routePrefix' => $this->routePrefix,
            'title' => 'Data Siswa',
        ]);
    }
    
    public function create()
    {
        $data = [
            'model' => new Model(),
            'method' => 'POST',
            'route' => $this->routePrefix . '.store',
            'button' => 'SIMPAN',
            'title' => 'Form Data Siswa',
            'wali' => User::where('akses', 'wali')->pluck('name', 'id'),
        ];
        return view('operator.' . $this->viewCreate, $data);
    }
}
