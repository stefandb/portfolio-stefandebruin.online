<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { ref, watch, nextTick, computed } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';

interface Props {
    modelValue: string;
    description: string;
    maxLength?: number;
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    maxLength: 200,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editMode = ref(false);
const excerpt = ref(props.modelValue ?? '');
const http = useHttp({ text: props.description });

const textareaRef = ref<HTMLTextAreaElement | null>(null);

watch(excerpt, (val) => {
    emit('update:modelValue', val);
});

watch(editMode, async (val) => {
    if (val) {
        await nextTick();
        textareaRef.value?.focus();
    }
});

async function generateExcerpt() {
    if (!props.description) return;

    try {
        const response = (await http.post('/ai/excerpt')) as { excerpt: string };
        excerpt.value = response.excerpt;
        editMode.value = true;
    } catch {
        toast.error('Kon geen samenvatting genereren', {
            description: 'Er is een fout opgetreden. Probeer het opnieuw.',
        });
    }
}

function startEditing() {
    editMode.value = true;
}

const aiLabel = computed(() =>
    excerpt.value ? 'Genereer opnieuw' : 'Genereer AI',
);
</script>

<template>
    <!-- preview mode -->
    <div
        v-if="!editMode"
        class="cursor-text rounded-md border border-input p-3 text-sm"
        @click="startEditing"
    >
        <span v-if="!excerpt" class="text-muted-foreground italic">
            Klik om een samenvatting te typen of genereer er één met AI.
        </span>

        <span v-else>
            {{ excerpt }}
        </span>
    </div>

    <!-- edit mode -->
    <div v-else class="space-y-2">
        <Textarea
            ref="textareaRef"
            v-model="excerpt"
            :maxlength="maxLength"
            rows="4"
        />

        <div
            class="flex items-center justify-between text-xs text-muted-foreground"
        >
            <span>{{ excerpt.length }} / {{ maxLength }}</span>

            <Button
                type="button"
                variant="ghost"
                size="sm"
                :disabled="http.processing"
                @click="generateExcerpt"
            >
                ✨ {{ aiLabel }}
            </Button>
        </div>
    </div>
</template>
