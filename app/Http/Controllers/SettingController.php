<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('setting.index', [
            'title' => 'Setting',
            'setting' => Setting::first(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    
    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting)
    {
    $validated = $request->validate([
        'app_name'    => 'required|string|max:255',
        'copyright'   => 'required|string|max:255',
        'login_title' => 'required|string|max:255',
        'keywords'    => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'logo'        => 'nullable|image|mimes:jpeg,png,jpg|max:1048', // Maksimal 1MB
    ], [
        'app_name.required'    => 'Nama aplikasi tidak boleh kosong.',
        'app_name.max'         => 'Nama aplikasi tidak boleh lebih dari :max karakter.',
        
        'copyright.required'   => 'Hak cipta (copyright) tidak boleh kosong.',
        'copyright.max'        => 'Hak cipta tidak boleh lebih dari :max karakter.',
        
        'login_title.required' => 'Judul halaman login tidak boleh kosong.',
        'login_title.max'      => 'Judul halaman login tidak boleh lebih dari :max karakter.',
        
        'keywords.max'         => 'Keywords tidak boleh lebih dari :max karakter.',
        
        'logo.image'           => 'File harus berupa gambar.',
        'logo.mimes'           => 'Format logo harus jpeg, png, atau jpg.',
        'logo.max'             => 'Ukuran logo tidak boleh lebih dari 1 MB.',
    ]);

    DB::beginTransaction();

    try {

        $data = [
            'app_name' => $validated['app_name'],
            'copyright' => $validated['copyright'],
            'login_title' => $validated['login_title'],
            'keywords' => $validated['keywords'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('logo')) {

            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            $data['logo'] = $request->file('logo')->store('logo', 'public');
        }

        $setting->update($data);

        DB::commit();

        return to_route('setting.index')
            ->withSuccess('Data berhasil disimpan');

    } catch (\Exception $e) {

        DB::rollBack();

        return to_route('setting.index')
            ->withError('Data gagal disimpan : '.$e->getMessage());
    }

}
}