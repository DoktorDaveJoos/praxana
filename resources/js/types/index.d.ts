import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    practice: Practice
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Practice {
    id: number;
    name: string;
    email: string;
    phone: string;
    address: string;
    city: string;
    postal_code: string;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Patient {
    id: string;
    first_name: string;
    last_name: string;
    name: string;
    birth_date: string;
    gender: string;
    email: string;
    phone: string;
    address: string;
    city: string;
    postal_code: string;
    occupation: string;
    insurance_type: string;
    insurance_number: string;
    insurance_name: string;
    emergency_contact: string;
}

export interface ResourceCollection<T> {
    data: T[];
}

export interface Resource<T> {
    data: T;
}

