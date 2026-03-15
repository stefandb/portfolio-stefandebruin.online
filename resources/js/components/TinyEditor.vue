<script setup lang="ts">
import Editor from '@tinymce/tinymce-vue';
import { ref, watch } from 'vue';
import FileManagerModal from '@/components/FileManagerModal.vue';
import type { FileItem } from '@/types/files';

interface Props {
    modelValue?: string;
    id?: string;
    name?: string;
    placeholder?: string;
    disabled?: boolean;
    height?: number | string;
    plugins?: string | string[];
    toolbar?: string | string[];
    options?: Record<string, any>;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    id: () => `tiny-editor-${Math.random().toString(36).substring(2, 9)}`,
    height: 400,
    plugins: () => [
        'advlist',
        'autolink',
        'lists',
        'link',
        'image',
        'charmap',
        'preview',
        'anchor',
        'searchreplace',
        'visualblocks',
        'code',
        'fullscreen',
        'insertdatetime',
        'media',
        'table',
        'help',
        'wordcount',
    ],
    toolbar:
        'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
    options: () => ({}),
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'change', value: string): void;
    (e: 'blur', event: any): void;
    (e: 'focus', event: any): void;
}>();

const editorValue = ref(props.modelValue);

watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue !== editorValue.value) {
            editorValue.value = newValue;
        }
    },
);

watch(editorValue, (newValue) => {
    emit('update:modelValue', newValue);
});

// ─── File manager integration ─────────────────────────────────────────────────

const isFileManagerOpen = ref(false);
const filePickerCallback = ref<((url: string, meta?: Record<string, string>) => void) | null>(null);

function openFileManager(callback: (url: string, meta?: Record<string, string>) => void): void {
    filePickerCallback.value = callback;
    isFileManagerOpen.value = true;
}

function handleFileSelected(files: FileItem[]): void {
    if (filePickerCallback.value && files.length > 0) {
        const file = files[0];
        filePickerCallback.value(file.url, { title: file.name });
    }
    filePickerCallback.value = null;
}

const initOptions = {
    height: props.height,
    menubar: true,
    plugins: props.plugins,
    toolbar: props.toolbar,
    branding: false,
    promotion: false,
    skin: 'oxide',
    content_css: 'default',
    file_picker_types: 'image media file',
    file_picker_callback: (
        callback: (url: string, meta?: Record<string, string>) => void,
    ) => {
        openFileManager(callback);
    },
    ...props.options,
};
</script>

<template>
    <div class="tinymce-container">
        <Editor
            :id="id"
            api-key="p03ipftpmcy1trpjawqo4a7i23xwggdgftl0tkwp7n8a7b6a"
            v-model="editorValue"
            :init="initOptions"
            :disabled="disabled"
            @blur="emit('blur', $event)"
            @focus="emit('focus', $event)"
            @change="emit('change', editorValue)"
        />

        <FileManagerModal
            v-model:open="isFileManagerOpen"
            :multiple="false"
            @confirm="handleFileSelected"
        />
    </div>
</template>

<style scoped>
.tinymce-container :deep(.tox-tinymce) {
    border-radius: 0.5rem;
}
</style>
