<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';

import TagController from '@/actions/App/Http/Controllers/Admin/TagController';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import TagForm from './TagForm.vue';

const tagForm = ref<InstanceType<typeof TagForm> | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tags',
        href: TagController.index.url(),
    },
    {
        title: 'Tag Toevoegen',
        href: TagController.create.url(),
    },
];

setLayoutProps({
    primaryButton: {
        label: 'Tag Opslaan',
        onClick: () => tagForm.value?.submit(),
    },
});
</script>

<template>
    <Head title="Tag Toevoegen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <TagForm ref="tagForm" />
        </div>
    </AppLayout>
</template>
