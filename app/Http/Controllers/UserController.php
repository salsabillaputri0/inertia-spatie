<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Menentukan middleware yang digunakan untuk kontrol akses berdasarkan izin.
     * Setiap metode di controller ini memiliki middleware yang memastikan hanya pengguna dengan izin tertentu yang dapat mengaksesnya.
     */
    public static function middleware()
    {
        return [
            // Middleware untuk metode 'index' (daftar pengguna)
            new Middleware('permission:users index', only: ['index']),
            // Middleware untuk metode 'create' dan 'store' (membuat dan menyimpan pengguna baru)
            new Middleware('permission:users create', only: ['create', 'store']),
            // Middleware untuk metode 'edit' dan 'update' (mengedit dan memperbarui pengguna)
            new Middleware('permission:users edit', only: ['edit', 'update']),
            // Middleware untuk metode 'destroy' (menghapus pengguna)
            new Middleware('permission:users delete', only: ['destroy']),
        ];
    }

    /**
     * Menampilkan daftar pengguna dengan relasi roles dan fitur pencarian.
     * Menggunakan paginasi untuk menampilkan 6 pengguna per halaman.
     */
    public function index(Request $request)
    {
        // Mengambil data pengguna beserta roles yang terkait
        $users = User::with('roles')
            // Jika ada query pencarian, menyaring berdasarkan nama pengguna
            ->when(request('search'), fn($query) => $query->where('name', 'like', '%'.request('search').'%'))
            // Mengurutkan berdasarkan pengguna terbaru
            ->latest()
            // Menampilkan hasil dalam bentuk halaman dengan 6 data per halaman
            ->paginate(6);

        // Mengembalikan tampilan dengan daftar pengguna dan filter pencarian
        return inertia('Users/Index', ['users' => $users, 'filters' => $request->only(['search'])]);
    }

    /**
     * Menampilkan form untuk membuat pengguna baru.
     * Juga mengambil daftar roles yang tersedia untuk dipilih.
     */
    public function create()
    {
        // Mengambil daftar roles yang ada
        $roles = Role::latest()->get();
        
        // Mengembalikan tampilan form pembuatan pengguna dengan daftar roles
        return inertia('Users/Create', ['roles' => $roles]);
    }

    /**
     * Menyimpan pengguna baru ke dalam database.
     * Memvalidasi input, membuat pengguna, dan memberikan roles yang dipilih.
     */
    public function store(Request $request)
    {
        // Memvalidasi input dari pengguna
        $request->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:4',
            'selectedRoles' => 'required|array|min:1', // Memastikan bahwa ada setidaknya satu role yang dipilih
        ]);

        // Membuat pengguna baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Enkripsi password
        ]);

        // Memberikan roles yang dipilih oleh pengguna
        $user->assignRole($request->selectedRoles);

        // Mengarahkan pengguna kembali ke halaman daftar pengguna setelah berhasil membuat pengguna
        return to_route('users.index');
    }

    /**
     * Menampilkan detail pengguna berdasarkan ID.
     * Metode ini belum digunakan dalam kode ini, tetapi bisa dikembangkan di masa depan.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Menampilkan form untuk mengedit pengguna yang sudah ada.
     * Mengambil data roles dan memuat roles yang sudah dimiliki pengguna.
     */
    public function edit(User $user)
    {
        // Mengambil daftar roles yang ada, kecuali role 'super-admin'
        $roles = Role::where('name', '!=', 'super-admin')->get();

        // Memuat roles yang dimiliki pengguna
        $user->load('roles');

        // Mengembalikan tampilan form edit pengguna dengan data pengguna dan daftar roles
        return inertia('Users/Edit', ['user' => $user, 'roles' => $roles]);
    }

    /**
     * Memperbarui data pengguna yang sudah ada.
     * Mengupdate informasi nama, email, dan roles yang dimiliki pengguna.
     */
    public function update(Request $request, User $user)
    {
        // Memvalidasi input yang diberikan oleh pengguna
        $request->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id, // Memastikan email tidak sama dengan email pengguna yang sedang diedit
            'selectedRoles' => 'required|array|min:1', // Memastikan setidaknya satu role dipilih
        ]);

        // Memperbarui nama dan email pengguna
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Menyinkronkan roles yang dipilih dengan pengguna
        $user->syncRoles($request->selectedRoles);

        // Mengarahkan kembali ke halaman daftar pengguna setelah berhasil memperbarui pengguna
        return to_route('users.index');
    }

    /**
     * Menghapus pengguna dari database.
     * Setelah penghapusan, pengguna akan diarahkan kembali ke halaman sebelumnya.
     */
    public function destroy(User $user)
    {
        // Menghapus pengguna dari database
        $user->delete();

        // Mengarahkan kembali ke halaman sebelumnya setelah pengguna dihapus
        return back();
    }
}
