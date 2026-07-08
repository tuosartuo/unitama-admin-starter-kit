<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
class userController extends Controller
{
     public function index()
    {
   return view('user.index', [
            'title' => 'User',
            'users' => User::latest()->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('user.create', [
            'title' => 'Tambah User',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
         $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:8',
        'passwordconfirm' => 'required|same:password',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:1048', // Opsional, jika upload file
        'role' => 'required|in:Superadmin,Admin',
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
        'role.in' => 'Role yang dipilih tidak valid.',
    ]);

    DB::beginTransaction();
    try {
        // Pindahkan proses upload ke DALAM try-catch dan SETELAH DB::beginTransaction 
        // agar jika query gagal, file yang terlanjur di-upload bisa di-handle atau tidak inkonsisten.


        if($request->file('avatar')){
            $validated['avatar'] = $request->file('avatar')->store('avatar','public');
        }
             $validated['email_verified_at']=now();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($request->password),
            'role' => $validated['role'],
            'avatar' => $validated['avatar'] ?? null,
            'email_verified_at' => now(),
        ]);
        DB::commit();

            return to_route('user.index')
                ->withSuccess('Data berhasil ditambahkan');

        } catch (\Exception $e) {

            DB::rollBack();

            return to_route('user.create')
                ->withError('Data gagal ditambahkan : ' . $e->getMessage());
        }
        }
    

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
          return view('user.show', [   
        'title' => 'Detail User',
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
         return view('user.edit', [
            'title' => 'Edit User',
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
         
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8',
        'passwordconfirm' => 'nullable|same:password',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:1048', // Opsional, jika upload file
        'role' => 'required|in:Superadmin,Admin',
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
        'role.in' => 'Role yang dipilih tidak valid.',
    ]);

    DB::beginTransaction();
    try {
        // Pindahkan proses upload ke DALAM try-catch dan SETELAH DB::beginTransaction 
        // agar jika query gagal, file yang terlanjur di-upload bisa di-handle atau tidak inkonsisten.

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
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
            'role' => $validated['role'],
            'avatar' => $validated['avatar'] ?? null,
        ]);

        DB::commit();

        return to_route('user.index')
            ->withSuccess('Data berhasil diubah');

    } catch (\Exception $e) {
        DB::rollBack();

        // Opsional: Hapus file avatar yang gagal disimpan di DB jika diperlukan menggunakan Storage::delete()

        return to_route('user.edit', $user)
            ->withError('Data gagal diubah : ' . $e->getMessage());
    }
}
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
         DB::beginTransaction();

    try {

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        DB::commit();

        return to_route('user.index')
            ->withSuccess('Data berhasil dihapus');

    } catch (\Exception $e) {

        DB::rollBack();

        return to_route('user.index')
            ->withError('Data gagal dihapus : '.$e->getMessage());
        }
    }
}