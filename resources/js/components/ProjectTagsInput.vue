<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverAnchor,
    PopoverContent,
} from '@/components/ui/popover';
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';

interface Props {
    modelValue: string[];
    error?: string;
    availableTags?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    availableTags: () => [],
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();

const tags = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const open = ref(false);
const searchTerm = ref('');

// Local list that merges server-provided tags with any newly created ones
const knownTags = ref<string[]>([...props.availableTags]);

watch(
    () => props.availableTags,
    (newTags) => {
        for (const tag of newTags) {
            if (!knownTags.value.includes(tag)) {
                knownTags.value.push(tag);
            }
        }
    },
);

const filteredTags = computed(() => {
    const search = searchTerm.value.toLowerCase();
    return knownTags.value.filter(
        (tag) => !tags.value.includes(tag) && tag.toLowerCase().includes(search),
    );
});

const addTag = (tag: string) => {
    if (!tags.value.includes(tag)) {
        const newTags = [...tags.value, tag];
        emit('update:modelValue', newTags);
        if (!knownTags.value.includes(tag)) {
            knownTags.value.push(tag);
        }
    }
    searchTerm.value = '';
    open.value = false;
};
</script>

<template>
    <div class="space-y-2">
        <Label for="tags">Tags</Label>
        <TagsInput v-model="tags">
            <div
                class="flex flex-wrap items-center gap-2 rounded-md bg-background px-3 py-1.5 text-sm"
            >
                <TagsInputItem v-for="item in tags" :key="item" :value="item">
                    <TagsInputItemText />
                    <TagsInputItemDelete />
                </TagsInputItem>

                <Popover v-model:open="open">
                    <PopoverAnchor as-child>
                        <TagsInputInput
                            placeholder="Typ of selecteer tags..."
                            class="flex-1 border-none bg-transparent p-0 focus-visible:ring-0"
                            @focus="open = true"
                            v-model="searchTerm"
                        />
                    </PopoverAnchor>
                    <PopoverContent
                        class="w-[300px] p-0"
                        align="start"
                        :auto-focus="false"
                        @open-autofocus.prevent
                    >
                        <Command v-model:search-term="searchTerm">
                            <CommandInput placeholder="Zoek tag..." />
                            <CommandList>
                                <CommandEmpty>Geen tags gevonden.</CommandEmpty>
                                <CommandGroup>
                                    <CommandItem
                                        v-for="tag in filteredTags"
                                        :key="tag"
                                        :value="tag"
                                        @select="addTag(tag)"
                                    >
                                        {{ tag }}
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
            </div>
        </TagsInput>
        <p class="text-xs text-muted-foreground">
            Selecteer een tag uit de lijst of druk op Enter om een nieuwe tag toe te voegen.
        </p>
        <div v-if="error" class="text-sm text-destructive">{{ error }}</div>
    </div>
</template>
