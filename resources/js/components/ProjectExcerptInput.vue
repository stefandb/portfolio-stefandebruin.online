<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { Wand2, RefreshCcw } from 'lucide-vue-next';
import { ref, watch, nextTick, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
    modelValue: string;
    description: string;
    maxLength?: number;
}

const props = withDefaults(defineProps<Props>(), {
    maxLength: 200,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editMode = ref(false);
const excerpt = ref(props.modelValue ?? '');
const http = useHttp({
    text: '',
});

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

    const response = await http.post('/ai/excerpt', {
        text: props.description,
    });

    excerpt.value = response.excerpt;
    editMode.value = true;
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
        class="border-input border cursor-text rounded-md p-3 text-sm"
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
            <span> {{ excerpt.length }} / {{ maxLength }} </span>

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
