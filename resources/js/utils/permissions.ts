const labels: Record<string, string> = {
    'competitions.view': 'Ver competiciones',
    'teams.view': 'Ver equipos',
    'players.view': 'Ver jugadores',
    'users.view': 'Ver usuarios',
    'users.manage': 'Gestionar usuarios',
    'roles.manage': 'Gestionar roles',
};

export function permissionLabel(permission: string): string {
    return labels[permission] ?? permission;
}
