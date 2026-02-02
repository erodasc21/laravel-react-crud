import { Link, router } from '@inertiajs/react';
import { MoreHorizontal, Pencil, Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { usePermission } from '@/hooks/usePermission';

interface DataTableActionsProps {
    id: number;
    editRoute: string;
    deleteRoute: string;
    editPermission?: string;
    deletePermission?: string;
    onDelete?: (id: number) => void;
}

export function DataTableActions({
    id,
    editRoute,
    deleteRoute,
    editPermission,
    deletePermission,
    onDelete,
}: DataTableActionsProps) {
    const { can } = usePermission();

    const handleDelete = () => {
        if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
            if (onDelete) {
                onDelete(id);
            } else {
                router.delete(deleteRoute);
            }
        }
    };

    const canEdit = editPermission ? can(editPermission) : true;
    const canDelete = deletePermission ? can(deletePermission) : true;

    if (!canEdit && !canDelete) {
        return null;
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="h-8 w-8 p-0">
                    <span className="sr-only">Abrir menú</span>
                    <MoreHorizontal className="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Acciones</DropdownMenuLabel>
                <DropdownMenuSeparator />
                {canEdit && (
                    <DropdownMenuItem asChild>
                        <Link href={editRoute} className="flex items-center">
                            <Pencil className="mr-2 h-4 w-4" />
                            Editar
                        </Link>
                    </DropdownMenuItem>
                )}
                {canDelete && (
                    <DropdownMenuItem
                        onClick={handleDelete}
                        className="flex items-center text-destructive focus:text-destructive"
                    >
                        <Trash2 className="mr-2 h-4 w-4" />
                        Eliminar
                    </DropdownMenuItem>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
