<script setup lang="ts">
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { CheckCircle2Icon, XCircleIcon } from 'lucide-vue-next';
import ProjectSlugController from '@/actions/App/Http/Controllers/Admin/ProjectSlugController';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';

interface Props {
    modelValue: string;
    projectId?: number;
    error?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

type Status = 'idle' | 'checking' | 'valid' | 'taken';

const status = ref<Status>('idle');
const suggestedSlug = ref<string | null>(null);

const checkSlug = async (slug: string) => {
    if (!slug) {
        status.value = 'idle';
        suggestedSlug.value = null;
        return;
    }

    status.value = 'checking';

    try {
        const params = new URLSearchParams({ slug });

        if (props.projectId) {
            params.set('exclude_id', String(props.projectId));
        }

        const response = await fetch(`${ProjectSlugController.check.url()}?${params}`);
        const data = await response.json();

        if (data.available) {
            status.value = 'valid';
            suggestedSlug.value = null;
        } else {
            status.value = 'taken';
            suggestedSlug.value = data.suggested ?? null;
        }
    } catch {
        status.value = 'idle';
    }
};

const debouncedCheck = useDebounceFn(checkSlug, 400);

watch(
    () => props.modelValue,
    (value) => {
        debouncedCheck(value);
    },
);

const acceptSuggested = () => {
    if (!suggestedSlug.value) {
        return;
    }

    emit('update:modelValue', suggestedSlug.value);
    status.value = 'valid';
    suggestedSlug.value = null;
};
</script>

<template>
    <div class="space-y-1">
        <div class="relative">
            <Input
                :model-value="modelValue"
                :class="status !== 'idle' ? 'pr-9' : ''"
                placeholder="project-slug"
                @update:model-value="emit('update:modelValue', String($event))"
            />
            <div
                v-if="status !== 'idle'"
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
            >
                <Spinner v-if="status === 'checking'" class="text-muted-foreground" />
                <CheckCircle2Icon
                    v-else-if="status === 'valid'"
                    class="size-4 text-green-500"
                />
                <XCircleIcon
                    v-else-if="status === 'taken'"
                    class="size-4 text-destructive"
                />
            </div>
        </div>

        <p v-if="status === 'taken' && suggestedSlug" class="text-muted-foreground text-xs">
            Beschikbaar:
            <button
                type="button"
                class="text-foreground underline"
                @click="acceptSuggested"
            >{{ suggestedSlug }}</button>
        </p>

        <p v-if="error" class="text-destructive text-sm">{{ error }}</p>
    </div>
</template>
