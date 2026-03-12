<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, provide, inject } from 'vue';

import ProjectController from '@/actions/App/Http/Controllers/Admin/ProjectController';
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
import type { BreadcrumbItem, PaginatedResponse, Project } from '@/types';

const props = defineProps<{
    projects: PaginatedResponse<Project>;
    filters: {
        search?: string;
        status?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projecten',
        href: ProjectController.index.url(),
    },
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
            ProjectController.index.url(),
            {
                search: search.value,
                status: status.value === 'all' ? undefined : status.value,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300);
};

watch([search, status], () => {
    updateFilters();
});

const deleteProject = (project: Project) => {
    if (confirm('Weet je zeker dat je dit project wilt verwijderen?')) {
        router.delete(ProjectController.destroy.url({ project: project.id }));
    }
};

const realCreateLogoc = () => {
    alert('jaaaaa');
};

provide('layoutActions', {
    triggerCreate: realCreateLogoc,
});
</script>

<template>
    <Head title="Projecten" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex flex-1 items-center gap-4">
                    <div class="relative w-full max-w-sm items-center">
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Zoek projecten..."
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
                            <SelectItem value="published"
                                >Gepubliceerd</SelectItem
                            >
                            <SelectItem value="draft">Concept</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

<!--                <Button :as="Link" :href="ProjectController.create.url()">-->
<!--                    Project Toevoegen-->
<!--                </Button>-->
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Titel</TableHead>
                            <TableHead>Bedrijf</TableHead>
                            <TableHead>Jaar</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Links</TableHead>
                            <TableHead class="text-right">Acties</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="project in projects.data"
                            :key="project.id"
                        >
                            <TableCell class="font-medium">
                                {{ project.title }}
                            </TableCell>
                            <TableCell>{{ project.company || '-' }}</TableCell>
                            <TableCell>{{ project.year }}</TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        project.status === 'published'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        project.status === 'published'
                                            ? 'Gepubliceerd'
                                            : 'Concept'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <a
                                        v-if="project.github_url"
                                        :href="project.github_url"
                                        target="_blank"
                                        class="text-muted-foreground hover:text-foreground"
                                    >
                                        <Github class="size-4" />
                                    </a>
                                    <a
                                        v-if="project.demo_url"
                                        :href="project.demo_url"
                                        target="_blank"
                                        class="text-muted-foreground hover:text-foreground"
                                    >
                                        <ExternalLink class="size-4" />
                                    </a>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        :as="Link"
                                        :href="
                                            ProjectController.edit.url({
                                                project: project.id,
                                            })
                                        "
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        @click="deleteProject(project)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="projects.data.length === 0">
                            <TableCell colspan="6" class="h-24 text-center">
                                Geen projecten gevonden.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Paginatie -->
            <div class="flex items-center justify-between px-2 py-4">
                <div class="text-sm text-muted-foreground">
                    Totaal {{ projects.total }} projecten
                </div>
                <div class="flex items-center space-x-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!projects.prev_page_url"
                        @click="router.get(projects.prev_page_url!)"
                    >
                        <ChevronLeft class="mr-2 size-4" />
                        Vorige
                    </Button>
                    <div class="flex items-center gap-1">
                        <template
                            v-for="link in projects.links.slice(1, -1)"
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
                        :disabled="!projects.next_page_url"
                        @click="router.get(projects.next_page_url!)"
                    >
                        Volgende
                        <ChevronRight class="ml-2 size-4" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
