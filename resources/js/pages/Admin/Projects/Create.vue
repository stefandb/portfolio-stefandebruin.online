<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import ProjectForm from './ProjectForm.vue';

const projectForm = ref<InstanceType<typeof ProjectForm> | null>(null);

interface Props {
    availableTags: string[];
}

const props = defineProps<Props>();

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

setLayoutProps({
    primaryButton: {
        label: 'Project Opslaan',
        onClick: () => {
            projectForm.value?.submit();
        },
    },
});
</script>

<template>
    <Head title="Project Toevoegen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <ProjectForm ref="projectForm" :available-tags="props.availableTags" />
        </div>
    </AppLayout>
</template>
