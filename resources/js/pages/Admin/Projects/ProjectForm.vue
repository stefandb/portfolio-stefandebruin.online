<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/Admin/ProjectController';
import ProjectTagsInput from '@/components/ProjectTagsInput.vue';
import TinyEditor from '@/components/TinyEditor.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')     // Replace spaces with -
        .replace(/[^\w-]+/g, '')  // Remove all non-word chars
        .replace(/--+/g, '-');    // Replace multiple - with single -
};

interface Props {
    project?: any;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.project?.title ?? '',
    slug: props.project?.slug ?? '',
    content: props.project?.content ?? '',
    excerpt: props.project?.excerpt ?? '',
    tags: props.project?.tags?.map((t: any) => t.name.nl) ?? [],
    images: [] as File[],
    company: props.project?.company ?? '',
    role: props.project?.role ?? '',
    year: props.project?.year ?? new Date().getFullYear(),
    status: props.project?.status ?? 'draft',
    github_url: props.project?.github_url ?? '',
    demo_url: props.project?.demo_url ?? '',
});

// Auto-slugify title
watch(() => form.title, (newTitle) => {
    if (!props.project) {
        form.slug = slugify(newTitle);
    }
});

const submit = () => {
    if (props.project) {
        form.patch(ProjectController.update.url({ project: props.project.id }), {
            onSuccess: () => form.reset('images'),
            forceFormData: true,
        });
    } else {
        form.post(ProjectController.store.url(), {
            onSuccess: () => form.reset(),
            forceFormData: true,
        });
    }
};

defineExpose({
    submit,
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        form.images = Array.from(target.files);
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Card 1: title, slug, description (geen CardTitle) -->
        <Card>
            <CardContent class="pt-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="title">Titel</Label>
                        <Input id="title" v-model="form.title" placeholder="Project Titel" />
                        <div v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label for="slug">Slug</Label>
                        <Input id="slug" v-model="form.slug" placeholder="project-slug" />
                        <div v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</div>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>Beschrijving</Label>
                    <TinyEditor v-model="form.content" />
                    <div v-if="form.errors.content" class="text-sm text-destructive">{{ form.errors.content }}</div>
                </div>
            </CardContent>
        </Card>

        <!-- Card 2: Excerpt (wel CardTitle) -->
        <Card>
            <CardHeader>
                <CardTitle>Samenvatting (Excerpt)</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-2">
                    <Textarea id="excerpt" v-model="form.excerpt" placeholder="Korte beschrijving van het project" rows="3" />
                    <div v-if="form.errors.excerpt" class="text-sm text-destructive">{{ form.errors.excerpt }}</div>
                </div>
            </CardContent>
        </Card>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Card 3: Tags (wel CardTitle) -->
            <Card>
                <CardHeader>
                    <CardTitle>Tags</CardTitle>
                </CardHeader>
                <CardContent>
                    <ProjectTagsInput v-model="form.tags" :error="form.errors.tags" />
                </CardContent>
            </Card>

            <!-- Card 4: Images (wel CardTitle) -->
            <Card>
                <CardHeader>
                    <CardTitle>Afbeeldingen</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <Label for="images">Afbeeldingen</Label>
                        <Input id="images" type="file" multiple @change="handleFileChange" accept="image/*" />
                        <div v-if="form.errors.images" class="text-sm text-destructive">{{ form.errors.images }}</div>

                        <div v-if="props.project?.media?.length" class="mt-4 grid grid-cols-2 gap-2">
                            <div v-for="media in props.project.media" :key="media.id" class="relative aspect-video overflow-hidden rounded-md border">
                                <img :src="media.original_url" class="h-full w-full object-cover" />
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <Label for="company">Bedrijf</Label>
                        <Input id="company" v-model="form.company" placeholder="Bedrijfsnaam" />
                        <div v-if="form.errors.company" class="text-sm text-destructive">{{ form.errors.company }}</div>
                    </div>
                    <div class="space-y-2">
                        <Label for="role">Rol</Label>
                        <Input id="role" v-model="form.role" placeholder="Bijv. Lead Developer" />
                        <div v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</div>
                    </div>
                    <div class="space-y-2">
                        <Label for="year">Jaar</Label>
                        <Input id="year" type="number" v-model="form.year" />
                        <div v-if="form.errors.year" class="text-sm text-destructive">{{ form.errors.year }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div class="space-y-2">
                        <Label for="github_url">GitHub URL</Label>
                        <Input id="github_url" v-model="form.github_url" placeholder="https://github.com/..." />
                        <div v-if="form.errors.github_url" class="text-sm text-destructive">{{ form.errors.github_url }}</div>
                    </div>
                    <div class="space-y-2">
                        <Label for="demo_url">Demo URL</Label>
                        <Input id="demo_url" v-model="form.demo_url" placeholder="https://..." />
                        <div v-if="form.errors.demo_url" class="text-sm text-destructive">{{ form.errors.demo_url }}</div>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="status">Status</Label>
                    <select id="status" v-model="form.status" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="draft">Concept</option>
                        <option value="published">Gepubliceerd</option>
                    </select>
                    <div v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</div>
                </div>
            </CardContent>
        </Card>
    </form>
</template>
