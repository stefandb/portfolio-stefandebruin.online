<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
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
    { title: 'Berichten', href: '/admin/posts' },
    { title: props.post.title, href: `/admin/posts/${props.post.id}/edit` },
];

setLayoutProps({
    primaryButton: {
        label: 'Bericht Bijwerken',
        onClick: () => postForm.value?.submit(),
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
