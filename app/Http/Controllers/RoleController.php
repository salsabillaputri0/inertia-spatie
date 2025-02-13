<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller implements HasMiddleware // Implement Middleware Spatie
{
    /**
     * Menentukan middleware untuk controller ini.
     * Setiap middleware bertanggung jawab untuk memeriksa izin akses pengguna terhadap tindakan tertentu di controller ini.
     */
    public static function middleware()
    {
        return [
            // Middleware untuk metode 'index' (tampilan daftar role)
            new Middleware('permission:roles index', only: ['index']),
            // Middleware untuk metode 'create' dan 'store' (tindakan membuat dan menyimpan role baru)
            new Middleware('permission:roles create', only: ['create', 'store']),
            // Middleware untuk metode 'edit' dan 'update' (tindakan mengedit dan memperbarui role)
            new Middleware('permission:roles edit', only: ['edit', 'update']),
            // Middleware untuk metode 'destroy' (tindakan menghapus role)
            new Middleware('permission:roles delete', only: ['destroy']),
        ];
    }

    /**
     * Menampilkan daftar resource (role).
     * Metode ini mengambil data role dengan permissions yang terkait dan menerapkan penyaringan serta paginasi.
     */
    public function index(Request $request)
    {
        // Mengambil daftar role dengan relasi permissions
        $roles = Role::select('id', 'name')
            // Memuat permissions terkait setiap role
            ->with('permissions:id,name')
            // Menyaring berdasarkan pencarian nama role jika ada query 'search'
            ->when($request->search, fn($search) => $search->where('name', 'like', '%'.$request->search.'%'))
            // Mengurutkan berdasarkan yang terbaru
            ->latest()
            // Menerapkan paginasi
            ->paginate(6);

        // Mengembalikan tampilan dengan daftar role dan filter pencarian
        return inertia('Roles/Index', ['roles' => $roles, 'filters' => $request->only(['search'])]);
    }

    /**
     * Menampilkan form untuk membuat resource (role) baru.
     * Metode ini juga mengambil data permissions yang dikelompokkan berdasarkan kata pertama.
     */
    public function create()
    {
        // Mengambil data permissions dan mengelompokkan berdasarkan kata pertama
        $data = Permission::orderBy('name')->pluck('name', 'id');
        $collection = collect($data);
        $permissions = $collection->groupBy(function ($item, $key) {
            // Memecah nama permission menjadi array kata dan mengambil kata pertama
            $words = explode(' ', $item);
            return $words[0];
        });

        // Mengembalikan tampilan form untuk membuat role baru dengan daftar permission
        return inertia('Roles/Create', ['permissions' => $permissions]);
    }

    /**
     * Menyimpan resource (role) yang baru dibuat ke dalam database.
     * Setelah role dibuat, memberi permissions yang dipilih oleh pengguna.
     */
    public function store(Request $request)
    {
        // Memvalidasi bahwa 'name' role wajib ada dan unik, serta 'selectedPermissions' berisi array dengan minimal 1 permission
        $request->validate([
            'name' => 'required|min:3|max:255|unique:roles',
            'selectedPermissions' => 'required|array|min:1',
        ]);

        // Membuat role baru dengan nama yang diberikan
        $role = Role::create(['name' => $request->name]);

        // Memberikan permissions yang dipilih ke role yang baru dibuat
        $role->givePermissionTo($request->selectedPermissions);

        // Setelah berhasil disimpan, mengarahkan kembali ke halaman index role
        return to_route('roles.index');
    }

    /**
     * Menampilkan detail resource (role) yang ditentukan.
     * Metode ini belum digunakan dalam kode ini, bisa ditambahkan nanti.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Menampilkan form untuk mengedit resource (role) yang ada.
     * Memuat data role dan permissions yang ada, kemudian menampilkan form edit.
     */
    public function edit(Role $role)
    {
        // Mengambil data permissions dan mengelompokkan berdasarkan kata pertama
        $data = Permission::orderBy('name')->pluck('name', 'id');
        $collection = collect($data);
        $permissions = $collection->groupBy(function ($item, $key) {
            // Memecah nama permission menjadi array kata dan mengambil kata pertama
            $words = explode(' ', $item);
            return $words[0];
        });

        // Memuat permissions yang sudah diberikan ke role
        $role->load('permissions');

        // Mengembalikan tampilan form edit untuk role yang sudah dipilih beserta permissions yang dikelompokkan
        return inertia('Roles/Edit', ['role' => $role, 'permissions' => $permissions]);
    }

    /**
     * Memperbarui resource (role) yang ditentukan di database.
     * Mengupdate nama role dan permissions yang diberikan sesuai pilihan pengguna.
     */
    public function update(Request $request, Role $role)
    {
        // Memvalidasi bahwa 'name' role wajib ada, unik, serta 'selectedPermissions' berisi array dengan minimal 1 permission
        $request->validate([
            'name' => 'required|min:3|max:255|unique:roles,name,'.$role->id,
            'selectedPermissions' => 'required|array|min:1',
        ]);

        // Memperbarui nama role yang ada
        $role->update(['name' => $request->name]);

        // Menyinkronkan permissions yang diberikan dengan role, menghapus yang lama dan menambahkan yang baru
        $role->syncPermissions($request->selectedPermissions);

        // Setelah update berhasil, mengarahkan kembali ke halaman index role
        return to_route('roles.index');
    }

    /**
     * Menghapus resource (role) yang ditentukan dari database.
     * Menghapus role yang dipilih.
     */
    public function destroy(Role $role)
    {
        // Menghapus role yang dipilih dari database
        $role->delete();

        // Setelah berhasil dihapus, mengarahkan kembali ke halaman sebelumnya (biasanya halaman index)
        return back();
    }
}
