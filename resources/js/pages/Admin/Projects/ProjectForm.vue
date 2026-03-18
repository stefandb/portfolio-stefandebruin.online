<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/Admin/ProjectController';
import ProjectSlugController from '@/actions/App/Http/Controllers/Admin/ProjectSlugController';
import FileManagerModal from '@/components/FileManagerModal.vue';
import ProjectExcerptInput from '@/components/ProjectExcerptInput.vue';
import ProjectTagsInput from '@/components/ProjectTagsInput.vue';
import SlugInput from '@/components/SlugInput.vue';
import TinyEditor from '@/components/TinyEditor.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { FileItem } from '@/types/files';

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-') // Replace spaces with -
        .replace(/[^\w-]+/g, '') // Remove all non-word chars
        .replace(/--+/g, '-'); // Replace multiple - with single -
};

interface Props {
    project?: any;
    availableTags?: string[];
}

const props = defineProps<Props>();

const displayImages = ref<FileItem[]>(props.project?.files ?? []);

const form = useForm({
    title: props.project?.title ?? '',
    slug: props.project?.slug ?? '',
    content: props.project?.content ?? '',
    excerpt: props.project?.excerpt ?? '',
    tags: props.project?.tags?.map((t: any) => t.name.en) ?? [],
    image_uuids: displayImages.value.map((f) => f.uuid),
    company: props.project?.company ?? '',
    role: props.project?.role ?? '',
    year: props.project?.year ?? new Date().getFullYear(),
    status: props.project?.status ?? 'draft',
    published_at: props.project?.published_at ?? null,
    github_url: props.project?.github_url ?? '',
    demo_url: props.project?.demo_url ?? '',
});

// Auto-slugify title
watch(
    () => form.title,
    (newTitle) => {
        if (!props.project) {
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

    if (props.project) {
        form.patch(ProjectController.update.url({ project: props.project.id }));
    } else {
        form.post(ProjectController.store.url(), {
            onSuccess: () => form.reset(),
        });
    }
};

defineExpose({
    submit,
});

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
        <!-- Card 1: title, slug, description (geen CardTitle) -->
        <Card>
            <CardContent class="space-y-6 pt-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="title">Titel</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            placeholder="Project Titel"
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
                            :check-url="ProjectSlugController.check.url()"
                            :exclude-id="props.project?.id"
                            :error="form.errors.slug"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>Beschrijving</Label>
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

        <!-- Card 2: Excerpt (wel CardTitle) -->
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
            <!-- Card 3: Tags (wel CardTitle) -->
            <Card>
                <CardHeader>
                    <CardTitle>Tags</CardTitle>
                </CardHeader>
                <CardContent>
                    <ProjectTagsInput
                        v-model="form.tags"
                        :available-tags="props.availableTags ?? []"
                        :error="form.errors.tags"
                    />
                </CardContent>
            </Card>

            <!-- Card 4: Images (wel CardTitle) -->
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
        </div>

        <!-- Card 5: overige meta data velden (wel CardTitle) -->
        <Card>
            <CardHeader>
                <CardTitle>Overige metadata</CardTitle>
            </CardHeader>
            <CardContent class="space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="company">Bedrijf</Label>
                        <Input
                            id="company"
                            v-model="form.company"
                            placeholder="Bedrijfsnaam"
                        />
                        <div
                            v-if="form.errors.company"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.company }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="role">Rol</Label>
                        <Input
                            id="role"
                            v-model="form.role"
                            placeholder="Bijv. Lead Developer"
                        />
                        <div
                            v-if="form.errors.role"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.role }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="year">Jaar</Label>
                        <Input id="year" type="number" v-model="form.year" />
                        <div
                            v-if="form.errors.year"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.year }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="github_url">GitHub URL</Label>
                        <Input
                            id="github_url"
                            v-model="form.github_url"
                            placeholder="https://github.com/..."
                        />
                        <div
                            v-if="form.errors.github_url"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.github_url }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="demo_url">Demo URL</Label>
                        <Input
                            id="demo_url"
                            v-model="form.demo_url"
                            placeholder="https://..."
                        />
                        <div
                            v-if="form.errors.demo_url"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.demo_url }}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </form>

    <FileManagerModal
        v-model:open="fileManagerOpen"
        multiple
        @confirm="handleFilesConfirmed"
    />
</template>
