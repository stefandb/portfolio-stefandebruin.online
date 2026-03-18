<script setup lang="ts">
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
        layoutProps: {
            primaryButton?: {
                label: string;
                onClick: () => void;
            };
            publishButtons?: {
                currentStatus: string | null;
                onPublish: () => void;
                onSaveDraft: () => void;
            };
        };
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const statusLabel = computed(() => {
    switch (props.layoutProps.publishButtons?.currentStatus) {
        case 'published':
            return 'Gepubliceerd';
        case 'draft':
            return 'Concept';
        default:
            return null;
    }
});

const statusVariant = computed(() => {
    return props.layoutProps.publishButtons?.currentStatus === 'published' ? 'default' : 'secondary';
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex w-full items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <div class="flex w-full items-center justify-between">
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>

                <Button
                    v-if="layoutProps.primaryButton?.label"
                    @click="layoutProps.primaryButton!.onClick()"
                >
                    {{ layoutProps.primaryButton.label }}
                </Button>

                <div
                    v-else-if="layoutProps.publishButtons"
                    class="flex items-center gap-3"
                >
                    <Badge
                        v-if="statusLabel"
                        :variant="statusVariant"
                    >
                        {{ statusLabel }}
                    </Badge>
                    <Button
                        variant="outline"
                        @click="layoutProps.publishButtons.onSaveDraft()"
                    >
                        Concept opslaan
                    </Button>
                    <Button @click="layoutProps.publishButtons.onPublish()">
                        Publiceren
                    </Button>
                </div>
            </div>
        </div>
    </header>
</template>
