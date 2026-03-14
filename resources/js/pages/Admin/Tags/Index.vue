<script setup lang="ts">
import { Head, Link, router, setLayoutProps } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Pencil, Search, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

import TagController from '@/actions/App/Http/Controllers/Admin/TagController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PaginatedResponse } from '@/types';

interface Tag {
    id: number;
    name: { en: string };
    slug: { en: string };
    type: string | null;
}

const props = defineProps<{
    tags: PaginatedResponse<Tag>;
    filters: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tags',
        href: TagController.index.url(),
    },
];

const search = ref(props.filters.search ?? '');

let timeoutId: ReturnType<typeof setTimeout> | null = null;

watch(search, () => {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(() => {
        router.get(
            TagController.index.url(),
            { search: search.value || undefined },
            { preserveState: true, replace: true },
        );
    }, 300);
});

const deleteTag = (tag: Tag) => {
    if (confirm(`Weet je zeker dat je de tag "${tag.name.en}" wilt verwijderen?`)) {
        router.delete(TagController.destroy.url({ tag: tag.id }));
    }
};

setLayoutProps({
    primaryButton: {
        label: 'Tag Toevoegen',
        onClick: () => router.visit(TagController.create.url()),
    },
});
</script>

<template>
    <Head title="Tags" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <div class="relative w-full max-w-sm">
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Zoek tags..."
                        class="pl-10"
                    />
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">
                        <Search class="size-4" />
                    </span>
                </div>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Naam</TableHead>
                            <TableHead>Slug</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead class="text-right">Acties</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="tag in tags.data" :key="tag.id">
                            <TableCell class="font-medium">{{ tag.name.en }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ tag.slug.en }}</TableCell>
                            <TableCell>{{ tag.type ?? '-' }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        :as="Link"
                                        :href="TagController.edit.url({ tag: tag.id })"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        @click="deleteTag(tag)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="tags.data.length === 0">
                            <TableCell colspan="4" class="h-24 text-center">
                                Geen tags gevonden.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex items-center justify-between px-2 py-4">
                <div class="text-sm text-muted-foreground">
                    Totaal {{ tags.total }} tags
                </div>
                <div class="flex items-center space-x-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!tags.prev_page_url"
                        @click="router.get(tags.prev_page_url!)"
                    >
                        <ChevronLeft class="mr-2 size-4" />
                        Vorige
                    </Button>
                    <div class="flex items-center gap-1">
                        <template v-for="link in tags.links.slice(1, -1)" :key="link.label">
                            <Button
                                variant="outline"
                                size="sm"
                                :class="{ 'bg-primary text-primary-foreground': link.active }"
                                :disabled="!link.url"
                                @click="link.url && router.get(link.url)"
                            >
                                {{ link.label }}
                            </Button>
                        </template>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!tags.next_page_url"
                        @click="router.get(tags.next_page_url!)"
                    >
                        Volgende
                        <ChevronRight class="ml-2 size-4" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
