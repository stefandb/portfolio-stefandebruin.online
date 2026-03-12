<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { provide, ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import ProjectForm from './ProjectForm.vue';

const projectForm = ref<InstanceType<typeof ProjectForm> | null>(null);

interface Props {
    project: any;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projecten',
        href: '/admin/projects',
    },
    {
        title: props.project.title,
        href: `/admin/projects/${props.project.id}/edit`,
    },
];

provide('layoutActions', {
    triggerCreate: () => {
        projectForm.value?.submit();
    },
});
</script>

<template>
    <Head :title="`Bewerk Project: ${props.project.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <ProjectForm ref="projectForm" :project="props.project" />
        </div>
    </AppLayout>
</template>
