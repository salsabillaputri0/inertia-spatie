<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Menampilkan form profil pengguna untuk diedit.
     * Menyediakan status verifikasi email jika diperlukan.
     */
    public function edit(Request $request): Response
    {
        // Mengembalikan tampilan untuk mengedit profil pengguna
        return Inertia::render('Profile/Edit', [
            // Memeriksa apakah pengguna perlu memverifikasi email
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            // Mengambil status (jika ada) yang disimpan dalam session
            'status' => session('status'),
        ]);
    }

    /**
     * Memperbarui informasi profil pengguna.
     * Validasi data yang dikirim dari form update profil.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Mengisi data pengguna dengan data yang sudah divalidasi dari request
        $request->user()->fill($request->validated());

        // Jika email pengguna berubah, set 'email_verified_at' menjadi null
        // untuk menandakan bahwa email perlu diverifikasi ulang
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Menyimpan perubahan ke dalam database
        $request->user()->save();

        // Setelah update berhasil, mengarahkan pengguna kembali ke form edit profil
        return Redirect::route('profile.edit');
    }

    /**
     * Menghapus akun pengguna.
     * Pengguna harus mengkonfirmasi dengan memasukkan password mereka.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Memvalidasi bahwa pengguna telah memasukkan password yang benar
        $request->validate([
            'password' => ['required', 'current_password'], // Memeriksa apakah password yang dimasukkan sesuai dengan password saat ini
        ]);

        // Mengambil data pengguna yang ingin dihapus
        $user = $request->user();

        // Melakukan logout setelah pengguna menghapus akun mereka
        Auth::logout();

        // Menghapus akun pengguna dari database
        $user->delete();

        // Menghancurkan session dan meregenerasi token CSRF untuk keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Setelah penghapusan akun, mengarahkan pengguna ke halaman beranda
        return Redirect::to('/');
    }
}
