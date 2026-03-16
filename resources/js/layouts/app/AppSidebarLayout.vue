<script setup lang="ts">
import { useLayoutProps } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';
import 'vue-sonner/style.css';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const layout = useLayoutProps({
    primaryButton: {
        label: '',
        onClick: () => {},
    },
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader
                :breadcrumbs="breadcrumbs"
                :layout-props="layout"
            />
            <div>
                <slot />
            </div>
            <Toaster :position="'bottom-right'"/>
        </AppContent>
    </AppShell>
</template>
