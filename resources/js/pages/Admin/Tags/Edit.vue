<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';

import TagController from '@/actions/App/Http/Controllers/Admin/TagController';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import TagForm from './TagForm.vue';

const tagForm = ref<InstanceType<typeof TagForm> | null>(null);

interface Tag {
    id: number;
    name: { en: string };
    type: string | null;
}

const props = defineProps<{ tag: Tag }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tags',
        href: TagController.index.url(),
    },
    {
        title: props.tag.name.en,
        href: TagController.edit.url({ tag: props.tag.id }),
    },
];

setLayoutProps({
    primaryButton: {
        label: 'Tag Bijwerken',
        onClick: () => tagForm.value?.submit(),
    },
});
</script>

<template>
    <Head :title="`Bewerk Tag: ${props.tag.name.en}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <TagForm ref="tagForm" :tag="props.tag" />
        </div>
    </AppLayout>
</template>
