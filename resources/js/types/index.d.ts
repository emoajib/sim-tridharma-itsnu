export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    active_role?: string;
    role_list?: string[];
    permissions: string[];
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
