<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/Admin/ProjectController';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import ProjectForm from './ProjectForm.vue';

const projectForm = ref<InstanceType<typeof ProjectForm> | null>(null);

interface Props {
    project: any;
    availableTags: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projecten',
        href: ProjectController.index.url(),
    },
    {
        title: props.project.title,
        href: ProjectController.edit.url({ project: props.project.id }),
    },
];

setLayoutProps({
    publishButtons: {
        currentStatus: props.project.status,
        onPublish: () => projectForm.value?.submit('published'),
        onSaveDraft: () => projectForm.value?.submit('draft'),
    },
});
</script>

<template>
    <Head :title="`Bewerk Project: ${props.project.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <ProjectForm ref="projectForm" :project="props.project" :available-tags="props.availableTags" />
        </div>
    </AppLayout>
</template>
