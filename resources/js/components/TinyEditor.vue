<script setup lang="ts">
import Editor from '@tinymce/tinymce-vue';
import { ref, watch } from 'vue';

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
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
    options: () => ({}),
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'change', value: string): void;
    (e: 'blur', event: any): void;
    (e: 'focus', event: any): void;
}>();

const editorValue = ref(props.modelValue);

watch(() => props.modelValue, (newValue) => {
    if (newValue !== editorValue.value) {
        editorValue.value = newValue;
    }
});

watch(editorValue, (newValue) => {
    emit('update:modelValue', newValue);
});

const initOptions = {
    height: props.height,
    menubar: true,
    plugins: props.plugins,
    toolbar: props.toolbar,
    branding: false,
    promotion: false,
    skin: 'oxide',
    content_css: 'default',
    ...props.options,
};


</script>

<template>
    <div class="tinymce-container">
        <Editor
            :id="id"
            v-model="editorValue"
            :init="initOptions"
            :disabled="disabled"
            @blur="emit('blur', $event)"
            @focus="emit('focus', $event)"
            @change="emit('change', editorValue)"
        />
    </div>
</template>

<style scoped>
.tinymce-container :deep(.tox-tinymce) {
    border-radius: 0.5rem;
}
</style>
