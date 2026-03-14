<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import TagController from '@/actions/App/Http/Controllers/Admin/TagController';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Tag {
    id: number;
    name: { en: string };
    type: string | null;
}

interface Props {
    tag?: Tag;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.tag?.name.en ?? '',
    type: props.tag?.type ?? '',
});

const submit = () => {
    if (props.tag) {
        form.patch(TagController.update.url({ tag: props.tag.id }));
    } else {
        form.post(TagController.store.url(), {
            onSuccess: () => form.reset(),
        });
    }
};

defineExpose({ submit });
</script>

<template>
    <form @submit.prevent="submit">
        <Card>
            <CardContent class="space-y-6 pt-6">
                <div class="space-y-2">
                    <Label for="name">Naam</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Tag naam"
                        autofocus
                    />
                    <div v-if="form.errors.name" class="text-sm text-destructive">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="type">Type <span class="text-muted-foreground">(optioneel)</span></Label>
                    <Input
                        id="type"
                        v-model="form.type"
                        placeholder="Bijv. technologie, taal..."
                    />
                    <div v-if="form.errors.type" class="text-sm text-destructive">
                        {{ form.errors.type }}
                    </div>
                </div>
            </CardContent>
        </Card>
    </form>
</template>
