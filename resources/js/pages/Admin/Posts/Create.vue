<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
import PostController from '@/actions/App/Http/Controllers/Admin/PostController';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PostSerie } from '@/types';
import PostForm from './PostForm.vue';

const postForm = ref<InstanceType<typeof PostForm> | null>(null);

interface Props {
    availableSeries: Pick<PostSerie, 'id' | 'title'>[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Berichten', href: PostController.index.url() },
    { title: 'Bericht Toevoegen', href: PostController.create.url() },
];

setLayoutProps({
    primaryButton: {
        label: 'Bericht Opslaan',
        onClick: () => postForm.value?.submit(),
    },
});
</script>

<template>
    <Head title="Bericht Toevoegen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PostForm ref="postForm" :available-series="props.availableSeries" />
        </div>
    </AppLayout>
</template>
