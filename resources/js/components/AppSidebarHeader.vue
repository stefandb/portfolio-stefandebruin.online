<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
        layoutProps: {
            primaryButton: {
                label: string;
                onClick: () => void;
            };
        };
    }>(),
    {
        breadcrumbs: () => [],
    },
);
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
                    v-if="layoutProps.primaryButton.label"
                    @click="layoutProps.primaryButton.onClick()"
                >
                    {{ layoutProps.primaryButton.label }}
                </Button>
            </div>
        </div>
    </header>
</template>
