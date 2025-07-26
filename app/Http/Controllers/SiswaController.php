<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Siswa as Model;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

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
    public function index(Request $request)
    {
        if ($request->filled('q')) {
            $models = Model::search($request->q)->paginate(50);
        } else {
            $models = Model::latest()->paginate(50);
        }

        return view('operator.' . $this->viewIndex, [
            'models' => $models,
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

    public function store(Request $request)
    {
        $requestData = $request->validate([
            'wali_id' => 'nullable',
            'nama' => 'required',
            'nisn' => 'required|unique:siswas',
            'jurusan' => 'required|nullable',
            'kelas' => 'required',
            'angkatan' => 'required',
            'foto'=> 'nullable|image|mimes:jpeg,png,jpg|max:5000',

        ]);

        if($request->hasFile('foto')) {
            $requestData['foto'] = $request->file('foto')->store('public');
        } 

        if($request->filled('wali_id')) {
            $requestData['wali_status'] = 'ok';
        } 

        $requestData['user_id'] = auth()->user()->id; //helper
        Model::create($requestData);
        flash('Data berhasil disimpan');
        return back();
        // return redirect()->route($this->routePrefix . '.index');
        // return redirect()->route('user.index');
    }

    
    public function show($id)
    {
        return view('operator.' .$this->viewShow, [
            'model' => Model::findOrFail($id),
            'title' => 'Detail Siswa'
        ]);
    }

    
    public function edit($id)
    {
        $data = [
            'model' => Model::findOrFail($id),
            'method' => 'PUT',
            'route' => [$this->routePrefix . '.update', $id],
            'button' => 'UPDATE',
            'title' => 'Form Data Siswa',
            'wali' => User::where('akses', 'wali')->pluck('name', 'id'),

        ];
        return view('operator.' . $this->viewEdit, $data);
    }

    public function update(Request $request, $id)
    {
        $requestData = $request->validate([
            'wali_id' => 'nullable',
            'nama' => 'required',
            'nisn' => 'required|unique:siswas,nisn,' . $id,
            'jurusan' => 'required|nullable',
            'kelas' => 'required',
            'angkatan' => 'required',
            'foto'=> 'nullable|image|mimes:jpeg,png,jpg|max:5000',
            
        ]);
        $model = Model::findOrFail($id);

        if($request->hasFile('foto')) {
            Storage::delete($model->foto); // Hapus foto lama jika ada
            $requestData['foto'] = $request->file('foto')->store('public');
        } 

        if($request->filled('wali_id')) {
            $requestData['wali_status'] = 'ok';
        } 

        $requestData['user_id'] = auth()->user()->id; 
        $model->fill($requestData);
        $model->save();
        flash('Data berhasil diubah');
        return redirect()->route($this->routePrefix . '.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = Model::findOrFail($id);

        if ($model->id == 4) {
            flash('Data tidak dapat dihapus')->error();
            return back();
        }

        $model->delete();
        flash('Data berhasil dihapus');
        return back();
    }
}
