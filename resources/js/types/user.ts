export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    created_at?: string;
    updated_at?: string;
}

export interface UserIndexProps {
    users: User[];
}

export interface UserCreateProps {
    // Props vacías para crear
}

export interface UserEditProps {
    user: User;
}
