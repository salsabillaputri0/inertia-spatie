// Mengimpor komponen AuthenticatedLayout dan Head dari inertiajs
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

// Komponen utama Dashboard
export default function Dashboard() {
    return (
        // Menggunakan AuthenticatedLayout untuk membungkus konten dashboard
        <AuthenticatedLayout
            // Mendefinisikan header yang akan ditampilkan di bagian atas layout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            {/* Menetapkan judul halaman di tab browser */}
            <Head title="Dashboard" />

            <div className="py-12">
                {/* Kontainer utama untuk konten */}
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Membuat kartu putih dengan bayangan dan sudut yang membulat */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {/* Bagian ini menampilkan pesan yang mengonfirmasi bahwa pengguna telah berhasil login */}
                        <div className="p-6 text-gray-900">
                            Anda telah berhasil login!
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
