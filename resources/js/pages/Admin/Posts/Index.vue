<script setup lang="ts">
import { Head, Link, router, setLayoutProps } from '@inertiajs/vue3';
import { Pencil, Search, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import PostController from '@/actions/App/Http/Controllers/Admin/PostController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PaginatedResponse, Post } from '@/types';

const props = defineProps<{
    posts: PaginatedResponse<Post>;
    filters: {
        search?: string;
        status?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Berichten', href: PostController.index.url() },
];

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');

let timeoutId: ReturnType<typeof setTimeout> | null = null;

const updateFilters = () => {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(() => {
        router.get(
            PostController.index.url(),
            {
                search: search.value,
                status: status.value === 'all' ? undefined : status.value,
            },
            { preserveState: true, replace: true },
        );
    }, 300);
};

watch([search, status], () => {
    updateFilters();
});

const deletePost = (post: Post) => {
    if (confirm('Weet je zeker dat je dit bericht wilt verwijderen?')) {
        router.delete(PostController.destroy.url({ post: post.id }));
    }
};

setLayoutProps({
    primaryButton: {
        label: 'Bericht Toevoegen',
        onClick: () => router.visit(PostController.create.url()),
    },
});
</script>

<template>
    <Head title="Berichten" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <div class="relative w-full max-w-sm items-center">
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Zoek berichten..."
                        class="pl-10"
                    />
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground"
                    >
                        <Search class="size-4" />
                    </span>
                </div>

                <Select v-model="status">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Alle Statussen</SelectItem>
                        <SelectItem value="published">Gepubliceerd</SelectItem>
                        <SelectItem value="draft">Concept</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Titel</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Aangemaakt</TableHead>
                            <TableHead class="text-right">Acties</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="post in posts.data"
                            :key="post.id"
                        >
                            <TableCell class="font-medium">
                                {{ post.title }}
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        post.status === 'published'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        post.status === 'published'
                                            ? 'Gepubliceerd'
                                            : 'Concept'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                {{ new Date(post.created_at).toLocaleDateString('nl-NL') }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        :as="Link"
                                        :href="
                                            PostController.edit.url({
                                                post: post.id,
                                            })
                                        "
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        @click="deletePost(post)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="posts.data.length === 0">
                            <TableCell colspan="4" class="h-24 text-center">
                                Geen berichten gevonden.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Paginatie -->
            <div class="flex items-center justify-between px-2 py-4">
                <div class="text-sm text-muted-foreground">
                    Totaal {{ posts.total }} berichten
                </div>
                <div class="flex items-center space-x-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!posts.prev_page_url"
                        @click="router.get(posts.prev_page_url!)"
                    >
                        Vorige
                    </Button>
                    <div class="flex items-center gap-1">
                        <template
                            v-for="link in posts.links.slice(1, -1)"
                            :key="link.label"
                        >
                            <Button
                                variant="outline"
                                size="sm"
                                :class="{
                                    'bg-primary text-primary-foreground':
                                        link.active,
                                }"
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
                        :disabled="!posts.next_page_url"
                        @click="router.get(posts.next_page_url!)"
                    >
                        Volgende
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
