<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Label from '@/components/ui/label/Label.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { UserContext, type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const props = defineProps<{
    name?: string;
    userData: UserContext | null;
    journals: any;
    timetable?: any; // Optional timetable prop
}>();

const tahvelCookie = ref<string>();

const saveTahvelCookie = () => {
    router.put('/save-tahvel-cookie', {
        tahvel_cookie: tahvelCookie.value,
    });
};

// Computed properties for stats
const taskStats = computed(() => {
    if (!props.journals?.tasks) return { total: 0, completed: 0, pending: 0, tests: 0 };

    const tasks = props.journals.tasks;
    return {
        total: tasks.length,
        completed: tasks.filter(task => task.isDone).length,
        pending: tasks.filter(task => !task.isDone).length,
        tests: tasks.filter(task => task.isTest).length,
    };
});

// Timetable stats - safe handling
const timetableStats = computed(() => {
    if (!props.timetable?.timetableEvents) return { todayEvents: 0, weekEvents: 0 };

    const events = props.timetable.timetableEvents;
    const today = new Date().toDateString();

    return {
        todayEvents: events.filter(event => new Date(event.date).toDateString() === today).length,
        weekEvents: events.length,
    };
});

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatTime = (dateTimeString: string) => {
    if (!dateTimeString) return 'N/A';
    return new Date(dateTimeString).toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
};

const getTaskTypeColor = (entryType: string, isTest: boolean) => {
    if (isTest) return 'text-red-600 bg-red-50 border-red-200';
    if (entryType === 'SISSEKANNE_H') return 'text-orange-600 bg-orange-50 border-orange-200';
    if (entryType === 'SISSEKANNE_T') return 'text-blue-600 bg-blue-50 border-blue-200';
    return 'text-gray-600 bg-gray-50 border-gray-200';
};

const getStatusColor = (isDone: boolean) => {
    return isDone ? 'text-green-600 bg-green-50 border-green-200' : 'text-yellow-600 bg-yellow-50 border-yellow-200';
};

const getTaskTypeName = (entryType: string, isTest: boolean) => {
    if (isTest) return 'Test';
    if (entryType === 'SISSEKANNE_H') return 'Assessment';
    if (entryType === 'SISSEKANNE_T') return 'Lesson';
    return 'Unknown';
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-600">Welcome to your Tahvel overview, {{ userData?.fullname || 'Student' }}!</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-6 mb-6" v-if="userData">
                <div class="bg-white rounded-lg border p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Tasks</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ taskStats.total }}</p>
                </div>

                <div class="bg-white rounded-lg border p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Completed</h3>
                    <p class="text-2xl font-bold text-green-600">{{ taskStats.completed }}</p>
                </div>

                <div class="bg-white rounded-lg border p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Pending</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ taskStats.pending }}</p>
                </div>

                <div class="bg-white rounded-lg border p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Tests</h3>
                    <p class="text-2xl font-bold text-red-600">{{ taskStats.tests }}</p>
                </div>

                <div class="bg-white rounded-lg border p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Today's Classes</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ timetableStats.todayEvents }}</p>
                </div>

                <div class="bg-white rounded-lg border p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">This Week</h3>
                    <p class="text-2xl font-bold text-purple-600">{{ timetableStats.weekEvents }}</p>
                </div>
            </div>

            <!-- User Info & Cookie Configuration Row -->
            <div class="grid gap-6 lg:grid-cols-2 mb-6">
                <!-- User Information -->
                <div class="bg-white rounded-lg border p-6 shadow-sm" v-if="userData">
                    <h2 class="text-lg font-semibold mb-4">Student Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Name:</span>
                            <span class="text-sm text-gray-900">{{ userData.fullname }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Student Group:</span>
                            <span class="text-sm text-gray-900">{{ userData.users?.[0]?.studentGroup || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">School:</span>
                            <span class="text-sm text-gray-900">{{ userData.users?.[0]?.schoolCode || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Role:</span>
                            <span class="text-sm text-gray-900">{{ userData.users?.[0]?.nameEt || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Study Year:</span>
                            <span class="text-sm text-gray-900">
                                {{ formatDate(journals?.studyYearStartDate) }} - {{ formatDate(journals?.studyYearEndDate) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cookie Configuration -->
                <div class="bg-white rounded-lg border p-6 shadow-sm">
                    <h2 class="text-lg font-semibold mb-4">Tahvel Authentication</h2>
                    <div class="space-y-4">
                        <div>
                            <Label>Tahvel auth cookie</Label>
                            <Textarea
                                class="mt-1"
                                v-model="tahvelCookie"
                                placeholder="Paste your Tahvel authentication cookie here..."
                            />
                            <p class="text-xs text-red-500 mt-1" v-show="$page.props.errors.tahvel_cookie">
                                {{ $page.props.errors.tahvel_cookie }}
                            </p>
                        </div>
                        <Button @click="saveTahvelCookie" class="w-full">
                            Update Cookie
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Today's Timetable Preview -->
            <div class="bg-white rounded-lg border shadow-sm mb-6" v-if="timetable?.timetableEvents">
                <div class="p-6 border-b flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold">Today's Schedule</h2>
                        <p class="text-gray-600 text-sm">Your classes for today</p>
                    </div>
                    <a
                        href="/timetable"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                    >
                        View Full Timetable →
                    </a>
                </div>

                <div class="p-6">
                    <div
                        v-if="timetable.timetableEvents.filter(event => new Date(event.date).toDateString() === new Date().toDateString()).length > 0"
                        class="space-y-3"
                    >
                        <div
                            v-for="event in timetable.timetableEvents.filter(event => new Date(event.date).toDateString() === new Date().toDateString())"
                            :key="event.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ event.subjectName || event.name || 'Class' }}</h4>
                                <p class="text-sm text-gray-500">{{ event.rooms?.[0]?.name || 'No room specified' }}</p>
                                <p class="text-sm text-gray-500">{{ event.teachers?.[0]?.name || 'No teacher specified' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ formatTime(event.start) }} - {{ formatTime(event.end) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        <p>No classes scheduled for today</p>
                    </div>
                </div>
            </div>

            <!-- Tasks Table -->
            <div class="bg-white rounded-lg border shadow-sm" v-if="journals?.tasks && journals.tasks.length > 0">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Your Tasks & Assignments</h2>
                    <p class="text-gray-600 text-sm">Overview of all your coursework</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="task in journals.tasks" :key="task.entryId" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ task.journalName }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                    <div class="truncate" :title="task.taskContent">
                                        {{ task.taskContent || 'No description' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border"
                                        :class="getTaskTypeColor(task.entryType, task.isTest)"
                                    >
                                        {{ getTaskTypeName(task.entryType, task.isTest) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(task.date) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border"
                                        :class="getStatusColor(task.isDone)"
                                    >
                                        {{ task.isDone ? 'Completed' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ task.doneDate ? formatDate(task.doneDate) : '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div class="bg-white rounded-lg border p-12 text-center shadow-sm" v-else-if="!userData">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Tahvel Data</h3>
                <p class="text-gray-500">Add your authentication cookie to start syncing your data.</p>
            </div>

            <!-- Debug Section (for development) -->
            <details class="bg-gray-50 rounded-lg border p-4">
                <summary class="cursor-pointer font-medium text-gray-700">Debug Information (Development Only)</summary>
                <div class="mt-4 space-y-4">
                    <div>
                        <h3 class="font-semibold text-sm text-gray-700">User Info:</h3>
                        <pre class="bg-white p-3 rounded text-xs overflow-auto mt-1 border max-h-40">{{ JSON.stringify($page.props.auth.user, null, 2) }}</pre>
                    </div>

                    <div>
                        <h3 class="font-semibold text-sm text-gray-700">User Data:</h3>
                        <pre class="bg-white p-3 rounded text-xs overflow-auto mt-1 border max-h-40">{{ JSON.stringify(userData, null, 2) }}</pre>
                    </div>

                    <div>
                        <h3 class="font-semibold text-sm text-gray-700">Tasks Data:</h3>
                        <pre class="bg-white p-3 rounded text-xs overflow-auto mt-1 border max-h-40">{{ JSON.stringify(journals, null, 2) }}</pre>
                    </div>

                    <div>
                        <h3 class="font-semibold text-sm text-gray-700">Timetable Data:</h3>
                        <pre class="bg-white p-3 rounded text-xs overflow-auto mt-1 border max-h-40">{{ JSON.stringify(timetable, null, 2) }}</pre>
                    </div>

                    <div>
                        <h3 class="font-semibold text-sm text-gray-700">Tahvel Cookie:</h3>
                        <div class="bg-white p-3 rounded text-xs mt-1 border">{{ tahvelCookie || 'Not set' }}</div>
                    </div>
                </div>
            </details>
        </div>
    </AppLayout>
</template>