<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Coming Soon" />

    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-background px-6"
    >
        <!-- Subtle grid background -->
        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,var(--color-border)_1px,transparent_1px),linear-gradient(to_bottom,var(--color-border)_1px,transparent_1px)] bg-[size:64px_64px] opacity-50 dark:opacity-30"
        />

        <!-- Radial fade overlay -->
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_60%_60%_at_50%_50%,transparent_40%,var(--color-background)_100%)]"
        />

        <main class="relative flex flex-col items-center gap-8 text-center">
            <!-- Status indicator -->
            <div
                class="flex items-center gap-2 rounded-full border border-border bg-muted px-4 py-1.5"
            >
                <span class="relative flex size-2">
                    <span
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-foreground opacity-50"
                    />
                    <span
                        class="relative inline-flex size-2 rounded-full bg-foreground"
                    />
                </span>
                <span
                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    Under Construction
                </span>
            </div>

            <!-- Heading -->
            <div class="flex flex-col gap-3">
                <h1
                    class="text-5xl font-semibold tracking-tight text-foreground sm:text-6xl lg:text-7xl"
                >
                    Something's<br />being crafted.
                </h1>
                <p class="max-w-md text-base text-muted-foreground sm:text-lg">
                    A new portfolio is on its way. Check back soon.
                </p>
            </div>
        </main>
    </div>
</template>
