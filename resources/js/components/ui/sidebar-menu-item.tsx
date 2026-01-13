import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { type NavItem } from '@/types';
import { cn } from '@/lib/utils';

interface SidebarMenuItemProps {
    item: NavItem;
    level?: number;
}

export function SidebarMenuItem({ item, level = 0 }: SidebarMenuItemProps) {
    const [isOpen, setIsOpen] = useState(false);
    const hasChildren = item.children && item.children.length > 0;
    const Icon = item.icon;

    // Si tiene hijos, renderiza un botón expandible
    if (hasChildren) {
        return (
            <div className={cn('w-full', level > 0 && 'ml-4')}>
                <button
                    onClick={() => setIsOpen(!isOpen)}
                    className={cn(
                        'flex w-full items-center justify-between rounded-md px-3 py-2',
                        'text-sm font-medium transition-colors',
                        'hover:bg-accent hover:text-accent-foreground',
                        'focus-visible:outline-none focus-visible:ring-2'
                    )}
                >
                    <div className="flex items-center gap-3">
                        {Icon && <Icon className="h-4 w-4 shrink-0" />}
                        <span>{item.title}</span>
                    </div>
                    {isOpen ? (
                        <ChevronDown className="h-4 w-4 shrink-0" />
                    ) : (
                        <ChevronRight className="h-4 w-4 shrink-0" />
                    )}
                </button>

                {/* Renderiza submenús recursivamente */}
                {isOpen && (
                    <div className="mt-1 space-y-1">
                        {item.children.map((child, index) => (
                            <SidebarMenuItem
                                key={`${child.title}-${index}`}
                                item={child}
                                level={level + 1}
                            />
                        ))}
                    </div>
                )}
            </div>
        );
    }

    // Si no tiene hijos, renderiza un link normal
    return (
        <Link
            href={item.href}
            className={cn(
                'flex items-center gap-3 rounded-md px-3 py-2',
                'text-sm font-medium transition-colors',
                'hover:bg-accent hover:text-accent-foreground',
                'focus-visible:outline-none focus-visible:ring-2',
                level > 0 && 'ml-4'
            )}
        >
            {Icon && <Icon className="h-4 w-4 shrink-0" />}
            <span>{item.title}</span>
        </Link>
    );
}
