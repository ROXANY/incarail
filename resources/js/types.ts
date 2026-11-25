export interface Rule {
    action: string;
    subject: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    roles: string[];
    rules: Rule[];
}

export interface Competition {
    id: number;
    code: string | null;
    name: string;
    type: string | null;
    area_name: string | null;
    emblem: string | null;
    season_start: string | null;
    season_end: string | null;
    teams_synced_at: string | null;
    teams?: Team[];
}

export interface Team {
    id: number;
    name: string;
    short_name: string | null;
    tla: string | null;
    crest: string | null;
    venue: string | null;
    founded: number | null;
    club_colors: string | null;
    area_name: string | null;
    players_count?: number;
    competitions?: Competition[];
    players?: Player[];
}

export interface Player {
    id: number;
    name: string;
    position: string | null;
    shirt_number: number | null;
    date_of_birth: string | null;
    nationality: string | null;
    team?: Team;
}

export interface Paginated<T> {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface TableOptions {
    page: number;
    itemsPerPage: number;
}

export interface AdminUser {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    role: string | null;
    created_at: string | null;
}

export interface Role {
    id: number;
    name: string;
    permissions: string[];
    users_count?: number;
}
