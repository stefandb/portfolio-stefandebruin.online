<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/Admin/ProjectController';
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
        href: ProjectController.index.url(),
    },
    {
        title: 'Project Toevoegen',
        href: ProjectController.create.url(),
    },
];

setLayoutProps({
    publishButtons: {
        currentStatus: null,
        onPublish: () => projectForm.value?.submit('published'),
        onSaveDraft: () => projectForm.value?.submit('draft'),
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
