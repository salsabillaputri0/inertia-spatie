<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    /**
     * Menentukan middleware untuk controller ini.
     * Setiap middleware bertanggung jawab untuk memastikan pengguna memiliki izin yang sesuai
     * untuk tindakan tertentu di controller ini.
     */
    public static function middleware()
    {
        return [
            // Middleware untuk metode 'index' (tampilan daftar permission)
            new Middleware('permission:permissions index', only: ['index']),
            // Middleware untuk metode 'create' dan 'store' (tindakan create dan store permission)
            new Middleware('permission:permissions create', only: ['create', 'store']),
            // Middleware untuk metode 'edit' dan 'update' (tindakan edit dan update permission)
            new Middleware('permission:permissions edit', only: ['edit', 'update']),
            // Middleware untuk metode 'destroy' (tindakan delete permission)
            new Middleware('permission:permissions delete', only: ['destroy']),
        ];
    }

    /**
     * Menampilkan daftar resource (permission).
     * Metode ini menangani penyaringan dan paginasi dari permissions.
     */
    public function index(Request $request)
    {
        // Mengambil data permissions dari database, dengan penyaringan jika disediakan
        $permissions = Permission::select('id', 'name')
            // Menerapkan filter pencarian berdasarkan kolom 'name', jika query pencarian ada
            ->when($request->search, fn($search) => $search->where('name', 'like', '%'.$request->search.'%'))
            // Mengurutkan permissions berdasarkan yang terbaru
            ->latest()
            // Menerapkan paginasi hasil dan mempertahankan query string filter pada URL
            ->paginate(6)->withQueryString();

        // Mengembalikan tampilan dengan daftar permissions dan filter yang aktif
        return inertia('Permissions/Index', ['permissions' => $permissions, 'filters' => $request->only(['search'])]);
    }

    /**
     * Menampilkan form untuk membuat resource (permission) baru.
     */
    public function create()
    {
        // Mengembalikan tampilan untuk membuat permission baru
        return inertia('Permissions/Create');
    }

    /**
     * Menyimpan resource (permission) yang baru dibuat ke dalam database.
     * Memvalidasi data yang masuk untuk memastikan 'name' unik.
     */
    public function store(Request $request)
    {
        // Memvalidasi bahwa kolom 'name' wajib ada, unik, dan memiliki panjang antara 3 hingga 255 karakter
        $request->validate(['name' => 'required|min:3|max:255|unique:permissions']);

        // Membuat record permission baru di database
        Permission::create(['name' => $request->name]);

        // Mengarahkan kembali pengguna ke halaman index permissions setelah permission baru dibuat
        return to_route('permissions.index');
    }

    /**
     * Menampilkan form untuk mengedit resource (permission) yang ada.
     */
    public function edit(Permission $permission)
    {
        // Mengembalikan tampilan untuk mengedit permission yang ditentukan
        return inertia('Permissions/Edit', ['permission' => $permission]);
    }

    /**
     * Memperbarui resource (permission) yang ditentukan di database.
     * Kolom 'name' divalidasi untuk memastikan keunikannya, kecuali untuk permission yang sedang diedit.
     */
    public function update(Request $request, Permission $permission)
    {
        // Memvalidasi bahwa kolom 'name' wajib ada, unik, dan memiliki panjang antara 3 hingga 255 karakter
        // Mengecualikan ID permission yang sedang diedit agar dapat memperbarui nama tanpa konflik
        $request->validate(['name' => 'required|min:3|max:255|unique:permissions,name,'.$permission->id]);

        // Memperbarui nama permission di database
        $permission->update(['name' => $request->name]);

        // Mengarahkan kembali pengguna ke halaman index permissions setelah update berhasil
        return to_route('permissions.index');
    }

    /**
     * Menghapus resource (permission) yang ditentukan dari database.
     * Menghapus record permission secara permanen.
     */
    public function destroy(Permission $permission)
    {
        // Menghapus permission yang ditentukan dari database
        $permission->delete();

        // Kembali ke halaman sebelumnya (biasanya halaman index permissions)
        return back();
    }
}
