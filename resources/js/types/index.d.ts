import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;


interface School {
    id: number;
    basic: boolean;
    secondary: boolean;
    vocational: boolean;
    higher: boolean;
    doctoral: boolean;
    letterGrades: boolean;
    withoutEkis: boolean;
    hasPinalCertificateNr: boolean;
    hmodules: boolean;
    ehisSchool: string;
    timetableType: string;
    logo: string;
    studentGroupTeacherJournal: boolean;
    notAbsence: boolean;
    loadEap: boolean;
    pinal: boolean;
  }

  interface User {
    id: number;
    schoolCode: string;
    role: string;
    nameEt: string;
    nameEn: string;
    studentName: string;
    studentGroup: string;
    default: null;
  }

  interface UserContext {
    name: string;
    user: number;
    person: number;
    student: number;
    teacher: number | null;
    teacherUuid: string | null;
    studentGroupId: number;
    roleCode: string;
    school: School;
    basic: boolean;
    secondary: boolean;
    vocational: boolean;
    higher: boolean;
    doctoral: boolean;
    fullname: string;
    type: string;
    authorizedRoles: string[];
    users: User[];
    loginMethod: string;
    sessionTimeoutInSeconds: number;
    teacherGroupIds: number[] | null;
    studentIds: number[] | null;
    isCurriculumTeacher: boolean | null;
    committees: any[];
    curriculums: any | null;
    hasSchoolRole: boolean;
    inApplicationCommittee: boolean | null;
    mustAgreeWithToS: boolean;
    curriculumVersion: number;
    mustAnswerPoll: boolean;
    isAdult: boolean;
    color: string | null;
    readOnly: boolean;
    allowedFileTypes: string[];
    allowedFileExtensions: string[];
    isSecondaryTeacher: boolean | null;
    isVocationalTeacher: boolean | null;
    isHigherTeacher: boolean | null;
  }