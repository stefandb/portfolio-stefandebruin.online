<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import PostController from '@/actions/App/Http/Controllers/Admin/PostController';
import PostSlugController from '@/actions/App/Http/Controllers/Admin/PostSlugController';
import FileManagerModal from '@/components/FileManagerModal.vue';
import ProjectExcerptInput from '@/components/ProjectExcerptInput.vue';
import SlugInput from '@/components/SlugInput.vue';
import TinyEditor from '@/components/TinyEditor.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PostSerie } from '@/types';
import type { FileItem } from '@/types/files';

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w-]+/g, '')
        .replace(/--+/g, '-');
};

interface Props {
    post?: any;
    availableSeries: Pick<PostSerie, 'id' | 'title'>[];
}

const props = defineProps<Props>();

const displayImages = ref<FileItem[]>(props.post?.files ?? []);

const form = useForm({
    title: props.post?.title ?? '',
    slug: props.post?.slug ?? '',
    content: props.post?.content ?? '',
    excerpt: props.post?.excerpt ?? '',
    status: props.post?.status ?? 'draft',
    published_at: props.post?.published_at ?? null,
    post_serie_id: props.post?.post_serie_id ?? null,
    image_uuids: displayImages.value.map((f) => f.uuid),
});

watch(
    () => form.title,
    (newTitle) => {
        if (!props.post) {
            form.slug = slugify(newTitle);
        }
    },
);

const submit = (status: 'draft' | 'published') => {
    form.status = status;

    if (status === 'published') {
        form.published_at = form.published_at ?? new Date().toISOString();
    } else {
        form.published_at = null;
    }

    if (props.post) {
        form.patch(PostController.update.url({ post: props.post.id }));
    } else {
        form.post(PostController.store.url(), {
            onSuccess: () => form.reset(),
        });
    }
};

defineExpose({ submit });

const fileManagerOpen = ref(false);

const handleFilesConfirmed = (files: FileItem[]) => {
    for (const file of files) {
        if (!displayImages.value.some((f) => f.uuid === file.uuid)) {
            displayImages.value.push(file);
        }
    }
    form.image_uuids = displayImages.value.map((f) => f.uuid);
};

const removeImage = (uuid: string) => {
    displayImages.value = displayImages.value.filter((f) => f.uuid !== uuid);
    form.image_uuids = displayImages.value.map((f) => f.uuid);
};
</script>

<template>
    <form class="space-y-6">
        <!-- Card 1: Titel, slug, beschrijving -->
        <Card>
            <CardContent class="space-y-6 pt-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="title">Titel</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            placeholder="Bericht Titel"
                        />
                        <div
                            v-if="form.errors.title"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.title }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="slug">Slug</Label>
                        <SlugInput
                            id="slug"
                            v-model="form.slug"
                            :check-url="PostSlugController.check.url()"
                            :exclude-id="props.post?.id"
                            :error="form.errors.slug"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>Inhoud</Label>
                    <TinyEditor v-model="form.content" />
                    <div
                        v-if="form.errors.content"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.content }}
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Card 2: Excerpt -->
        <Card>
            <CardHeader>
                <CardTitle>Samenvatting (Excerpt)</CardTitle>
            </CardHeader>
            <CardContent>
                <ProjectExcerptInput
                    v-model="form.excerpt"
                    :description="form.content"
                    :error="form.errors.excerpt"
                />
            </CardContent>
        </Card>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Card 3: Afbeeldingen -->
            <Card>
                <CardHeader>
                    <CardTitle>Afbeeldingen</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label>Afbeeldingen toevoegen</Label>
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full"
                                @click="fileManagerOpen = true"
                            >
                                Bestandsbeheer openen
                            </Button>
                            <div
                                v-if="form.errors.image_uuids"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.image_uuids }}
                            </div>
                        </div>

                        <div
                            v-if="displayImages.length"
                            class="grid grid-cols-2 gap-2"
                        >
                            <div
                                v-for="image in displayImages"
                                :key="image.uuid"
                                class="group relative aspect-video overflow-hidden rounded-md border"
                            >
                                <img
                                    :src="image.thumbnail_url ?? image.url"
                                    class="h-full w-full object-cover"
                                />
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="icon"
                                    class="absolute top-1 right-1 size-7 opacity-0 transition-opacity group-hover:opacity-100"
                                    @click="removeImage(image.uuid)"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Card 4: Metadata -->
            <Card>
                <CardHeader>
                    <CardTitle>Metadata</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="post_serie_id">Serie</Label>
                        <select
                            id="post_serie_id"
                            v-model="form.post_serie_id"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option :value="null">Geen serie</option>
                            <option
                                v-for="serie in props.availableSeries"
                                :key="serie.id"
                                :value="serie.id"
                            >
                                {{ serie.title }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.post_serie_id"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.post_serie_id }}
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </form>

    <FileManagerModal
        v-model:open="fileManagerOpen"
        multiple
        @confirm="handleFilesConfirmed"
    />
</template>
