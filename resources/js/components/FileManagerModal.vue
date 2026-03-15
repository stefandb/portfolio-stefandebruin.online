<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { useIntersectionObserver } from '@vueuse/core';
import {
    DialogClose,
    DialogContent,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
    CollapsibleContent,
    CollapsibleRoot,
    CollapsibleTrigger,
} from 'reka-ui';
import {
    ChevronDown,
    ChevronRight,
    File,
    FileText,
    Image,
    Loader2,
    Music,
    Trash2,
    Upload,
    Video,
    X,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { Separator } from '@/components/ui/separator';
import type { FileItem, FileCursorPage } from '@/types/files';

interface Props {
    open: boolean;
    multiple?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    multiple: false,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [files: FileItem[]];
}>();

// State
const files = ref<FileItem[]>([]);
const selectedFiles = ref<FileItem[]>([]);
const isLoading = ref(false);
const isUploading = ref(false);
const isDragOver = ref(false);
const nextCursor = ref<string | null>(null);
const hasMore = ref(true);
const expandedUuids = ref<Set<string>>(new Set());
const altTexts = ref<Record<string, string>>({});

// Refs
const sentinelRef = ref<HTMLElement | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const gridScrollRef = ref<HTMLElement | null>(null);

// Computed
const lastSelectedFile = computed(() => selectedFiles.value[selectedFiles.value.length - 1] ?? null);

// ─── API helpers ──────────────────────────────────────────────────────────────

function getCsrfToken(): string {
    return decodeURIComponent(
        document.cookie
            .split('; ')
            .find((r) => r.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? '',
    );
}

async function loadFiles(cursor?: string): Promise<void> {
    if (isLoading.value) {
        return;
    }

    isLoading.value = true;

    try {
        const url = new URL('/admin/files', window.location.origin);
        if (cursor) {
            url.searchParams.set('cursor', cursor);
        }

        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
        });

        const json: FileCursorPage = await response.json();

        if (cursor) {
            files.value.push(...json.data);
        } else {
            files.value = json.data;
        }

        nextCursor.value = json.meta.next_cursor;
        hasMore.value = json.links.next !== null;
    } finally {
        isLoading.value = false;
    }
}

async function uploadFiles(fileList: FileList): Promise<void> {
    isUploading.value = true;

    const formData = new FormData();
    Array.from(fileList).forEach((file) => formData.append('files[]', file));

    try {
        const response = await fetch('/admin/files', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: formData,
        });

        const uploaded: FileItem[] = await response.json();
        files.value.unshift(...uploaded);
    } finally {
        isUploading.value = false;
    }
}

async function deleteFile(uuid: string): Promise<void> {
    await fetch(`/admin/files/${uuid}`, {
        method: 'DELETE',
        headers: {
            'X-XSRF-TOKEN': getCsrfToken(),
            Accept: 'application/json',
        },
    });

    files.value = files.value.filter((f) => f.uuid !== uuid);
    deselectFile(uuid);
}

// ─── Selection ────────────────────────────────────────────────────────────────

function toggleFile(file: FileItem): void {
    const index = selectedFiles.value.findIndex((f) => f.uuid === file.uuid);

    if (index >= 0) {
        selectedFiles.value.splice(index, 1);
        expandedUuids.value.delete(file.uuid);
    } else {
        if (!props.multiple) {
            selectedFiles.value = [file];
            expandedUuids.value = new Set([file.uuid]);
        } else {
            selectedFiles.value.push(file);
            expandedUuids.value.add(file.uuid);
        }
    }
}

function isSelected(file: FileItem): boolean {
    return selectedFiles.value.some((f) => f.uuid === file.uuid);
}

function deselectFile(uuid: string): void {
    selectedFiles.value = selectedFiles.value.filter((f) => f.uuid !== uuid);
    expandedUuids.value.delete(uuid);
    delete altTexts.value[uuid];
}

function toggleExpanded(uuid: string): void {
    if (expandedUuids.value.has(uuid)) {
        expandedUuids.value.delete(uuid);
    } else {
        expandedUuids.value.add(uuid);
    }
}

// ─── Event handlers ───────────────────────────────────────────────────────────

function handleDrop(e: DragEvent): void {
    isDragOver.value = false;
    if (e.dataTransfer?.files.length) {
        uploadFiles(e.dataTransfer.files);
    }
}

function handleFileInput(e: Event): void {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        uploadFiles(input.files);
        input.value = '';
    }
}

function confirmSelection(): void {
    const filesWithAlt = selectedFiles.value.map((f) => ({
        ...f,
        alt: altTexts.value[f.uuid] ?? '',
    }));
    emit('confirm', filesWithAlt);
    emit('update:open', false);
}

function close(): void {
    emit('update:open', false);
}

// ─── Infinite scroll ──────────────────────────────────────────────────────────

useIntersectionObserver(sentinelRef, ([entry]) => {
    if (entry?.isIntersecting && hasMore.value && !isLoading.value && nextCursor.value) {
        loadFiles(nextCursor.value);
    }
});

// ─── Lifecycle ────────────────────────────────────────────────────────────────

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            files.value = [];
            selectedFiles.value = [];
            expandedUuids.value = new Set();
            altTexts.value = {};
            nextCursor.value = null;
            hasMore.value = true;
            await nextTick();
            loadFiles();
        }
    },
);

// ─── Formatting helpers ───────────────────────────────────────────────────────

function formatSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }
    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getFileIcon(mimeType: string) {
    if (mimeType.startsWith('image/')) {
        return Image;
    }
    if (mimeType.startsWith('video/')) {
        return Video;
    }
    if (mimeType.startsWith('audio/')) {
        return Music;
    }
    if (mimeType === 'application/pdf') {
        return FileText;
    }
    return File;
}
</script>

<template>
    <DialogRoot :open="open" @update:open="emit('update:open', $event)">
        <DialogPortal>
            <DialogOverlay
                class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-[10000] bg-black/60"
            />
            <DialogContent
                class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 bg-background fixed top-1/2 left-1/2 z-[10001] flex h-[88vh] w-[95vw] max-w-7xl -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-xl border shadow-2xl duration-200 focus:outline-none"
                @pointer-down-outside.prevent
            >
                <!-- Header -->
                <div class="flex shrink-0 items-center justify-between border-b px-5 py-3.5">
                    <DialogTitle class="text-base font-semibold">File Manager</DialogTitle>
                    <div class="flex items-center gap-2">
                        <!-- Upload button -->
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="isUploading"
                            @click="fileInputRef?.click()"
                        >
                            <Loader2 v-if="isUploading" class="size-4 animate-spin" />
                            <Upload v-else class="size-4" />
                            {{ isUploading ? 'Uploading…' : 'Upload files' }}
                        </Button>
                        <input
                            ref="fileInputRef"
                            type="file"
                            multiple
                            class="hidden"
                            @change="handleFileInput"
                        />
                        <DialogClose as-child>
                            <Button variant="ghost" size="icon" class="size-8" @click="close">
                                <X class="size-4" />
                                <span class="sr-only">Close</span>
                            </Button>
                        </DialogClose>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex min-h-0 flex-1">
                    <!-- ── Left: file grid ── -->
                    <div
                        ref="gridScrollRef"
                        class="relative flex min-w-0 flex-1 flex-col overflow-y-auto"
                        @dragover.prevent="isDragOver = true"
                        @dragleave.prevent="isDragOver = false"
                        @drop.prevent="handleDrop"
                    >
                        <!-- Drag overlay -->
                        <Transition
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div
                                v-if="isDragOver"
                                class="pointer-events-none absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 rounded-none bg-primary/10 backdrop-blur-sm"
                            >
                                <Upload class="text-primary size-12" />
                                <p class="text-primary text-sm font-medium">Drop files to upload</p>
                            </div>
                        </Transition>

                        <!-- Empty / upload prompt when no files -->
                        <div
                            v-if="!isLoading && files.length === 0"
                            class="flex flex-1 flex-col items-center justify-center gap-4 p-10 text-center"
                        >
                            <div class="bg-muted rounded-full p-5">
                                <Upload class="text-muted-foreground size-8" />
                            </div>
                            <div>
                                <p class="font-medium">No files yet</p>
                                <p class="text-muted-foreground mt-1 text-sm">
                                    Drag &amp; drop files here, or click "Upload files"
                                </p>
                            </div>
                        </div>

                        <!-- File grid -->
                        <div
                            v-else
                            class="grid grid-cols-3 gap-3 p-4 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6"
                        >
                            <!-- Upload skeleton while uploading -->
                            <div
                                v-if="isUploading"
                                v-for="n in 3"
                                :key="`upload-skeleton-${n}`"
                                class="aspect-square overflow-hidden rounded-lg"
                            >
                                <Skeleton class="size-full" />
                            </div>

                            <!-- File items -->
                            <button
                                v-for="file in files"
                                :key="file.uuid"
                                type="button"
                                class="group relative aspect-square overflow-visible rounded-lg transition-all duration-150 focus:outline-none"
                                :class="
                                    isSelected(file)
                                        ? 'scale-105 z-10 ring-2 ring-primary ring-offset-2 ring-offset-background'
                                        : 'hover:ring-2 hover:ring-muted-foreground/30 hover:ring-offset-1 hover:ring-offset-background'
                                "
                                @click="toggleFile(file)"
                            >
                                <!-- Image thumbnail -->
                                <img
                                    v-if="file.is_image"
                                    :src="file.url"
                                    :alt="file.name"
                                    class="size-full rounded-lg object-cover"
                                    loading="lazy"
                                />
                                <!-- File type icon -->
                                <div
                                    v-else
                                    class="bg-muted flex size-full flex-col items-center justify-center gap-2 rounded-lg"
                                >
                                    <component
                                        :is="getFileIcon(file.mime_type)"
                                        class="text-muted-foreground size-8"
                                    />
                                    <span class="text-muted-foreground max-w-full truncate px-1 text-[10px]">
                                        {{ file.original_name }}
                                    </span>
                                </div>

                                <!-- Selected check badge -->
                                <span
                                    v-if="isSelected(file)"
                                    class="bg-primary text-primary-foreground absolute top-1.5 right-1.5 flex size-5 items-center justify-center rounded-full text-[10px] font-bold shadow"
                                >
                                    ✓
                                </span>

                                <!-- Delete button (hover) -->
                                <button
                                    type="button"
                                    class="bg-destructive text-destructive-foreground absolute bottom-1.5 right-1.5 hidden size-6 items-center justify-center rounded-full shadow transition-opacity group-hover:flex"
                                    @click.stop="deleteFile(file.uuid)"
                                >
                                    <Trash2 class="size-3" />
                                </button>
                            </button>

                            <!-- Initial loading skeletons -->
                            <template v-if="isLoading && files.length === 0">
                                <div
                                    v-for="n in 18"
                                    :key="`skeleton-${n}`"
                                    class="aspect-square overflow-hidden rounded-lg"
                                >
                                    <Skeleton class="size-full" />
                                </div>
                            </template>
                        </div>

                        <!-- Infinite scroll sentinel + load-more skeleton -->
                        <div ref="sentinelRef" class="shrink-0 px-4 pb-4">
                            <div
                                v-if="isLoading && files.length > 0"
                                class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6"
                            >
                                <div
                                    v-for="n in 6"
                                    :key="`more-skeleton-${n}`"
                                    class="aspect-square overflow-hidden rounded-lg"
                                >
                                    <Skeleton class="size-full" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Right: selected files sidebar ── -->
                    <div
                        v-if="selectedFiles.length > 0"
                        class="border-l flex w-72 shrink-0 flex-col overflow-hidden"
                    >
                        <div class="flex shrink-0 items-center justify-between px-4 py-3">
                            <span class="text-sm font-medium">
                                Selected
                                <span class="text-muted-foreground">({{ selectedFiles.length }})</span>
                            </span>
                            <button
                                type="button"
                                class="text-muted-foreground hover:text-foreground text-xs transition-colors"
                                @click="selectedFiles = []"
                            >
                                Clear all
                            </button>
                        </div>

                        <Separator />

                        <!-- Selected files list -->
                        <div class="min-h-0 flex-1 overflow-y-auto">
                            <CollapsibleRoot
                                v-for="file in selectedFiles"
                                :key="file.uuid"
                                :open="expandedUuids.has(file.uuid)"
                                class="border-b last:border-0"
                                @update:open="toggleExpanded(file.uuid)"
                            >
                                <CollapsibleTrigger
                                    class="hover:bg-muted/50 flex w-full items-center gap-2.5 px-3 py-2.5 text-left transition-colors"
                                >
                                    <!-- Thumbnail -->
                                    <div class="size-9 shrink-0 overflow-hidden rounded-md">
                                        <img
                                            v-if="file.is_image"
                                            :src="file.url"
                                            :alt="file.name"
                                            class="size-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="bg-muted flex size-full items-center justify-center"
                                        >
                                            <component
                                                :is="getFileIcon(file.mime_type)"
                                                class="text-muted-foreground size-4"
                                            />
                                        </div>
                                    </div>

                                    <!-- Name -->
                                    <span class="min-w-0 flex-1 truncate text-xs font-medium">
                                        {{ file.original_name }}
                                    </span>

                                    <!-- Actions -->
                                    <div class="flex shrink-0 items-center gap-1">
                                        <ChevronDown
                                            class="text-muted-foreground size-3.5 transition-transform"
                                            :class="{ 'rotate-180': expandedUuids.has(file.uuid) }"
                                        />
                                        <button
                                            type="button"
                                            class="text-muted-foreground hover:text-foreground ml-0.5 transition-colors"
                                            @click.stop="deselectFile(file.uuid)"
                                        >
                                            <X class="size-3.5" />
                                        </button>
                                    </div>
                                </CollapsibleTrigger>

                                <!-- Metadata panel -->
                                <CollapsibleContent
                                    class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 overflow-hidden transition-all"
                                >
                                    <div class="bg-muted/30 space-y-2 px-3 pb-3">
                                        <!-- Preview for images -->
                                        <div v-if="file.is_image" class="pt-2">
                                            <img
                                                :src="file.url"
                                                :alt="file.name"
                                                class="w-full rounded-md object-cover"
                                                style="max-height: 120px"
                                            />
                                        </div>

                                        <!-- Alt text (images only) -->
                                        <div v-if="file.is_image" class="pt-2">
                                            <label
                                                :for="`alt-${file.uuid}`"
                                                class="text-muted-foreground mb-1 block text-xs font-medium"
                                            >
                                                Alt text
                                            </label>
                                            <Input
                                                :id="`alt-${file.uuid}`"
                                                v-model="altTexts[file.uuid]"
                                                placeholder="Describe the image…"
                                                class="h-7 text-xs"
                                                @click.stop
                                                @keydown.stop
                                            />
                                        </div>

                                        <!-- Metadata rows -->
                                        <dl class="space-y-1.5 pt-2 text-xs">
                                            <div class="flex flex-col gap-0.5">
                                                <dt class="text-muted-foreground font-medium">Name</dt>
                                                <dd class="break-all font-medium">{{ file.original_name }}</dd>
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <dt class="text-muted-foreground font-medium">Type</dt>
                                                <dd>{{ file.mime_type }}</dd>
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <dt class="text-muted-foreground font-medium">Size</dt>
                                                <dd>{{ formatSize(file.size) }}</dd>
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <dt class="text-muted-foreground font-medium">Uploaded</dt>
                                                <dd>{{ formatDate(file.created_at) }}</dd>
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <dt class="text-muted-foreground font-medium">URL</dt>
                                                <dd>
                                                    <a
                                                        :href="file.url"
                                                        target="_blank"
                                                        class="text-primary truncate underline-offset-2 hover:underline"
                                                    >
                                                        Open file ↗
                                                    </a>
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </CollapsibleContent>
                            </CollapsibleRoot>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex shrink-0 items-center justify-between border-t px-5 py-3">
                    <p class="text-muted-foreground text-sm">
                        <template v-if="selectedFiles.length === 0">No files selected</template>
                        <template v-else>
                            {{ selectedFiles.length }}
                            {{ selectedFiles.length === 1 ? 'file' : 'files' }} selected
                        </template>
                    </p>
                    <div class="flex gap-2">
                        <Button variant="outline" size="sm" @click="close">Cancel</Button>
                        <Button
                            size="sm"
                            :disabled="selectedFiles.length === 0"
                            @click="confirmSelection"
                        >
                            Insert {{ selectedFiles.length > 0 ? `(${selectedFiles.length})` : '' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
