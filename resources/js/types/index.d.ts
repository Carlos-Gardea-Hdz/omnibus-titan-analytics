import type { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface Auth {
    user: {
        id: string;
        name: string;
        email: string;
    } | null;
}

export interface Flash {
    success?: string;
    error?: string;
}

// Module augmentation: typed shared props for every `usePage()` call.
declare module '@inertiajs/react' {
    interface PageProps extends InertiaPageProps {
        auth: Auth;
        flash: Flash;
    }
}

// Point-in-time aggregated series row returned by the AggregateQueryAction DTO.
// The authoritative shape is generated from PHP DTOs into ./generated.d.ts
// via `php artisan typescript:transform` — never hand-redeclare server payloads there.
export interface TimeBucket {
    bucket: string;
    count: number;
}
