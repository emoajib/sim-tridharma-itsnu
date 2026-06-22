export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    active_role?: string;
    role_list?: string[];
    permissions: string[];
}

export interface Flash {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

export interface AppSettings {
    theme_mode?: string;
    theme_color?: string;
    chat_enabled?: boolean;
    layout_type?: string;
    dashboard_default_tab?: string;
    logo_path?: string | null;
    favicon_path?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash?: Flash;
    appSettings?: AppSettings;
};
