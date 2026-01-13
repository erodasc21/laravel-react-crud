import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type UserIndexProps } from '@/types/user';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, PlusCircle, Trash2 } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Usuarios',
        href: '/catalogos/usuarios',
    },
];

export default function Index({ users }: UserIndexProps) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de eliminar este usuario?')) {
            router.delete(`/catalogos/usuarios/${id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Usuarios" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Lista de Usuarios</h1>
                    <button
                        onClick={() =>
                            router.visit(`/catalogos/usuarios/create`)
                        }
                        className="flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-600"
                    >
                        <PlusCircle className="h-4 w-4" />
                        Nuevo Usuario
                    </button>
                </div>

                {/* Tabla */}
                <div className="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="border-b border-sidebar-border/70 bg-sidebar/50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-sm font-medium">
                                        Nombre
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium">
                                        Email
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.map((user) => (
                                    <tr
                                        key={user.id}
                                        className="border-b border-sidebar-border/50 last:border-0 hover:bg-sidebar/30"
                                    >
                                        <td className="px-6 py-3">
                                            {user.name}
                                        </td>
                                        <td className="px-6 py-3">
                                            {user.email}
                                        </td>
                                        <td className="px-6 py-3">
                                            <div className="flex gap-2">
                                                <button
                                                    onClick={() =>
                                                        router.visit(
                                                            `/catalogos/usuarios/${user.id}/edit`,
                                                        )
                                                    }
                                                    className="flex items-center gap-2 rounded-md bg-yellow-500 px-3 py-1 text-sm text-white hover:bg-yellow-600"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                    Editar
                                                </button>

                                                <button
                                                    onClick={() =>
                                                        handleDelete(user.id)
                                                    }
                                                    className="flex items-center gap-2 rounded-md bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
