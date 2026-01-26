import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type UserEditProps } from '@/types/user';
import { FormEventHandler } from 'react';

export default function Edit({ user }: UserEditProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Inicio',
            href: dashboard().url,
        },
        {
            title: 'Usuarios',
            href: '/catalogos/usuarios',
        },
        {
            title: 'Editar',
            href: `/catalogos/usuarios/${user.id}/edit`,
        },
    ];

    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/catalogos/usuarios/${user.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Usuario" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Editar Usuario</h1>
                    <Link
                        href="/catalogos/usuarios"
                        className="text-sm text-muted-foreground hover:underline"
                    >
                        ← Volver
                    </Link>
                </div>

                <div className="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium mb-2">
                                Nombre
                            </label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2"
                            />
                            {errors.name && (
                                <span className="text-sm text-red-600">{errors.name}</span>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-2">
                                Email
                            </label>
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className="w-full rounded-md border border-input bg-background px-3 py-2"
                            />
                            {errors.email && (
                                <span className="text-sm text-red-600">{errors.email}</span>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-md bg-primary px-4 py-2 text-white hover:bg-primary/90 disabled:opacity-50"
                        >
                            Actualizar Usuario
                        </button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
