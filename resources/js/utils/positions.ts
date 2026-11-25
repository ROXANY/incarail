export const positions = [
    { value: 'Goalkeeper', title: 'Portero' },
    { value: 'Defence', title: 'Defensa' },
    { value: 'Midfield', title: 'Mediocampista' },
    { value: 'Offence', title: 'Delantero' },
];

export function positionLabel(position: string | null): string {
    const found = positions.find((item) => item.value === position);

    return found ? found.title : 'Sin posición';
}
