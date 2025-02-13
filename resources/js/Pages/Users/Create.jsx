import React from "react"; // Mengimpor React
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout"; // Mengimpor layout untuk pengguna yang terautentikasi
import Container from "@/Components/Container"; // Mengimpor komponen Container untuk membungkus konten
import { Head, useForm, usePage } from "@inertiajs/react"; // Mengimpor komponen dan hook dari InertiaJS
import Input from "@/Components/Input"; // Mengimpor komponen Input untuk field form
import Button from "@/Components/Button"; // Mengimpor komponen Button
import Card from "@/Components/Card"; // Mengimpor komponen Card
import Select2 from "@/Components/Select2"; // Mengimpor komponen Select2 untuk dropdown pilihan
import Swal from "sweetalert2"; // Mengimpor SweetAlert untuk menampilkan notifikasi

export default function Create({ auth }) {
    // Mendestrukturisasi roles dari props yang diterima dari usePage
    const { roles } = usePage().props;

    // Mendefinisikan state dengan bantuan hook useForm dari Inertia
    const { data, setData, post, errors } = useForm({
        name: "",
        email: "",
        selectedRoles: [],
        password: "",
        password_confirmation: "",
    });

    // Mendefinisikan daftar roles yang diformat untuk Select2
    const formattedRoles = roles.map((role) => ({
        value: role.name,
        label: role.name,
    }));

    // Mendefinisikan metode untuk menangani perubahan pilihan roles
    const handleSelectedRoles = (selected) => {
        const selectedValues = selected.map((option) => option.value);
        setData("selectedRoles", selectedValues); // Mengupdate state selectedRoles
    };

    // Mendefinisikan metode untuk menangani penyimpanan data saat form disubmit
    const handleStoreData = async (e) => {
        e.preventDefault(); // Mencegah form untuk reload halaman saat submit

        // Mengirimkan data form menggunakan post request ke route 'users.store'
        post(route("users.store"), {
            onSuccess: () => {
                // Menampilkan notifikasi sukses dengan SweetAlert
                Swal.fire({
                    title: "Success!",
                    text: "Data created successfully!",
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
                    Create User
                </h2>
            } // Header untuk halaman ini
        >
            <Head title={"Create Users"} />{" "}
            {/* Mengatur judul halaman di tab browser */}
            <Container>
                {" "}
                {/* Membungkus konten dengan Container */}
                <Card title={"Create new user"}>
                    {" "}
                    {/* Menampilkan card dengan judul */}
                    {/* Formulir untuk membuat user baru */}
                    <form onSubmit={handleStoreData}>
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
                                Roles
                            </div>
                            {/* Komponen Select2 untuk memilih role, menggunakan handleSelectedRoles untuk meng-handle perubahan */}
                            <Select2
                                onChange={handleSelectedRoles}
                                options={formattedRoles}
                                placeholder="Pilih Role..."
                            />
                        </div>
                        <div className="mb-4">
                            {/* Input untuk password user */}
                            <Input
                                label={"Password"}
                                type={"password"}
                                value={data.password}
                                onChange={(e) =>
                                    setData("password", e.target.value)
                                }
                                errors={errors.password}
                                placeholder="Input password user.."
                            />
                        </div>
                        <div className="mb-4">
                            {/* Input untuk konfirmasi password */}
                            <Input
                                label={"Password Confirmation"}
                                type={"password"}
                                value={data.password_confirmation}
                                onChange={(e) =>
                                    setData(
                                        "password_confirmation",
                                        e.target.value
                                    )
                                }
                                errors={errors.password_confirmation}
                                placeholder="Input password confirmation..."
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
