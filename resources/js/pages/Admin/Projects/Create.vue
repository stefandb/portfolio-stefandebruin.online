<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { provide, ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import ProjectForm from './ProjectForm.vue';

const projectForm = ref<InstanceType<typeof ProjectForm> | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projecten',
        href: '/admin/projects',
    },
    {
        title: 'Project Toevoegen',
        href: '/admin/projects/create',
    },
];

provide('layoutActions', {
    triggerCreate: () => {
        projectForm.value?.submit();
    },
});
</script>

<template>
    <Head title="Project Toevoegen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <ProjectForm ref="projectForm" />
        </div>
    </AppLayout>
</template>
