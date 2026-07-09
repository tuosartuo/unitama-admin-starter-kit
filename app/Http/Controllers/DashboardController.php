<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard.index',[
            'title' => 'Dashboar'
        ]);
    }

   

    /**
     * Display the specified resource.
     */
    public function show()
    {
        return view('dashboard.show', [
            'title' => 'Detail User',
            'user' => Auth::user(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
         return view('dashboard.edit', [
            'title' => 'Edit User',
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
         $user = Auth::user();
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8',
        'passwordconfirm' => 'nullable|same:password',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:1048', // Opsional, jika upload file
    ], [
        'name.required' => 'Nama tidak boleh kosong.',
        'name.max' => 'Nama tidak boleh lebih dari :max karakter.',
        
        'email.required' => 'Email tidak boleh kosong.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        
        'password.required' => 'Password tidak boleh kosong.',
        'password.min' => 'Password minimal harus :min karakter.',

        // --- PESAN ERROR TAMBAHAN UNTUK PASSWORD CONFIRM ---
        'passwordconfirm.required' => 'Konfirmasi password tidak boleh kosong.',
        'passwordconfirm.same' => 'Konfirmasi password harus sama dengan password.',
        
        // --- PESAN ERROR TAMBAHAN UNTUK AVATAR (OPSIONAL TAPI REKOMENDASI) ---
        'avatar.image' => 'File harus berupa gambar.',
        'avatar.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
        'avatar.max' => 'Ukuran gambar tidak boleh lebih dari 1 MB.',
        
        'role.required' => 'Role harus dipilih.',
    ]);

    DB::beginTransaction();
    try {
        // Pindahkan proses upload ke DALAM try-catch dan SETELAH DB::beginTransaction 
        // agar jika query gagal, file yang terlanjur di-upload bisa di-handle atau tidak inkonsisten.

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatar', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar' => $validated['avatar'] ?? null,
        ]);

        DB::commit();

        return to_route('user.index')
            ->withSuccess('Data berhasil diubah');

    } catch (\Exception $e) {
        DB::rollBack();

        // Opsional: Hapus file avatar yang gagal disimpan di DB jika diperlukan menggunakan Storage::delete()

        return to_route('dashboard.show')
            ->withError('Data gagal diubah : ' . $e->getMessage());
    }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}