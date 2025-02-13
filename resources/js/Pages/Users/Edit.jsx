import React, { useEffect, useState } from "react"; // Mengimpor React dan hooks useEffect, useState
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout"; // Mengimpor layout untuk pengguna yang terautentikasi
import Container from "@/Components/Container"; // Mengimpor komponen Container untuk membungkus konten
import { Head, useForm, usePage } from "@inertiajs/react"; // Mengimpor komponen dan hook dari InertiaJS
import Input from "@/Components/Input"; // Mengimpor komponen Input untuk field form
import Button from "@/Components/Button"; // Mengimpor komponen Button
import Card from "@/Components/Card"; // Mengimpor komponen Card
import Swal from "sweetalert2"; // Mengimpor SweetAlert untuk menampilkan notifikasi
import Select2 from "@/Components/Select2"; // Mengimpor komponen Select2 untuk dropdown pilihan

export default function Edit({ auth }) {
    // Mendestrukturisasi data 'user' dan 'roles' dari props yang diterima dari usePage
    const { user, roles } = usePage().props;

    // Mendefinisikan state dengan bantuan hook useForm dari Inertia
    const { data, setData, post, errors } = useForm({
        name: user.name, // Menetapkan nama user yang akan diedit
        email: user.email, // Menetapkan email user yang akan diedit
        selectedRoles: user.roles.map((role) => role.name), // Menetapkan roles yang dipilih berdasarkan data user
        filterRole: user.roles.map((role) => ({
            value: role.name, // Format roles yang sudah dipilih
            label: role.name,
        })),
        _method: "put", // Menetapkan metode HTTP 'put' untuk pembaruan data
    });

    // Format roles untuk dropdown pilihan (Select2)
    const formattedRoles = roles.map((role) => ({
        value: role.name, // Nilai dari role
        label: role.name, // Label yang ditampilkan untuk role
    }));

    // Mendefinisikan fungsi untuk menangani perubahan pilihan roles
    const handleSelectedRoles = (selected) => {
        // Mengambil nilai yang dipilih dan mengatur state selectedRoles
        const selectedValues = selected.map((option) => option.value);
        setData("selectedRoles", selectedValues);
    };

    // Mendefinisikan fungsi untuk menangani update data saat form disubmit
    const handleUpdateData = async (e) => {
        e.preventDefault(); // Mencegah reload halaman saat submit form

        // Mengirimkan data form menggunakan post request ke route 'users.update' dengan id user
        post(route("users.update", user.id), {
            onSuccess: () => {
                // Menampilkan notifikasi sukses dengan SweetAlert
                Swal.fire({
                    title: "Success!",
                    text: "Data updated successfully!",
                    icon: "success",
                    showConfirmButton: false,
                    timer: 1500,
                });
            },
        });
    };

    return (
        // Layout untuk pengguna yang sudah terautentikasi
        <AuthenticatedLayout
            user={auth.user} // Mengirimkan data user yang sedang login
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Edit User
                </h2>
            } // Header untuk halaman ini
        >
            <Head title={"Edit Users"} />{" "}
            {/* Mengatur judul halaman di tab browser */}
            <Container>
                {" "}
                {/* Membungkus konten dengan Container */}
                <Card title={"Edit user"}>
                    {" "}
                    {/* Menampilkan card dengan judul untuk mengedit user */}
                    {/* Formulir untuk mengedit user */}
                    <form onSubmit={handleUpdateData}>
                        <div className="mb-4">
                            {/* Input untuk nama user */}
                            <Input
                                label={"Name"}
                                type={"text"}
                                value={data.name}
                                onChange={(e) =>
                                    setData("name", e.target.value)
                                }
                                errors={errors.name}
                                placeholder="Input name user.."
                            />
                        </div>
                        <div className="mb-4">
                            {/* Input untuk email user */}
                            <Input
                                label={"Email"}
                                type={"email"}
                                value={data.email}
                                onChange={(e) =>
                                    setData("email", e.target.value)
                                }
                                errors={errors.email}
                                placeholder="Input email user.."
                            />
                        </div>
                        <div className="mb-4">
                            <div className="flex items-center gap-2 text-sm text-gray-700">
                                Roles {/* Label untuk bagian role */}
                            </div>
                            {/* Komponen Select2 untuk memilih roles, dengan opsi default berdasarkan user yang sedang diedit */}
                            <Select2
                                onChange={handleSelectedRoles} // Mengatur pilihan roles yang dipilih
                                defaultOptions={data.filterRole} // Opsi default berdasarkan roles yang sudah dipilih user
                                options={formattedRoles} // Opsi roles yang tersedia
                                placeholder="Pilih Role..." // Placeholder untuk dropdown
                            />
                        </div>
                        <div className="flex items-center gap-2">
                            {/* Tombol untuk submit form */}
                            <Button type={"submit"} />
                            {/* Tombol untuk membatalkan dan kembali ke halaman daftar user */}
                            <Button
                                type={"cancel"}
                                url={route("users.index")}
                            />
                        </div>
                    </form>
                </Card>
            </Container>
        </AuthenticatedLayout>
    );
}
