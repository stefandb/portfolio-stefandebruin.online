<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
import PostController from '@/actions/App/Http/Controllers/Admin/PostController';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PostSerie } from '@/types';
import PostForm from './PostForm.vue';

const postForm = ref<InstanceType<typeof PostForm> | null>(null);

interface Props {
    post: any;
    availableSeries: Pick<PostSerie, 'id' | 'title'>[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Berichten', href: PostController.index.url() },
    { title: props.post.title, href: PostController.edit.url({ post: props.post.id }) },
];

setLayoutProps({
    publishButtons: {
        currentStatus: props.post.status,
        onPublish: () => postForm.value?.submit('published'),
        onSaveDraft: () => postForm.value?.submit('draft'),
    },
});
</script>

<template>
    <Head :title="`Bewerk Bericht: ${props.post.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <PostForm
                ref="postForm"
                :post="props.post"
                :available-series="props.availableSeries"
            />
        </div>
    </AppLayout>
</template>
