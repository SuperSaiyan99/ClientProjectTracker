<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Layers, ArrowRight, ShieldCheck, CheckCircle, Sparkles, Building2, Calendar } from '@lucide/vue';
</script>

<template>
    <Head title="Client Project Tracker - Digital Agency Workspace">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background text-foreground flex flex-col font-sans selection:bg-primary selection:text-primary-content">
        <!-- Navigation Header -->
        <header class="border-b border-sidebar-border/70 dark:border-sidebar-border bg-card/60 backdrop-blur-md sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-primary p-2 text-primary-content shadow-xs">
                        <Layers class="h-5 w-5" />
                    </div>
                    <span class="font-bold text-lg tracking-tight">Client Project Tracker</span>
                </div>

                <nav class="flex items-center gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link
                            href="/dashboard"
                            class="btn btn-primary btn-sm gap-1.5 shadow-xs px-4"
                        >
                            <span>Go to Dashboard</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="btn btn-primary btn-sm gap-1.5 shadow-xs px-4"
                        >
                            <span>Sign in</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-1 flex flex-col items-center justify-center max-w-6xl mx-auto px-6 py-16 text-center space-y-12">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-primary/20 bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wider shadow-2xs">
                <Sparkles class="h-3.5 w-3.5" />
                <span>Modern Digital Agency Suite</span>
            </div>

            <!-- Title & Subtitle -->
            <div class="space-y-4 max-w-3xl">
                <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-foreground leading-[1.1]">
                    Track Client Projects with <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">Clarity & Elegance</span>
                </h1>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
                    A streamlined full-stack workspace built for agency teams to manage client deliverables, status workflows, priorities, and deadlines.
                </p>
            </div>

            <!-- Live Feature Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full pt-8 text-left">
                <div class="card bg-card border border-sidebar-border/70 p-6 shadow-xs rounded-2xl space-y-3">
                    <div class="rounded-xl bg-info/10 p-3 text-info w-fit">
                        <Building2 class="h-6 w-6" />
                    </div>
                    <h3 class="font-bold text-lg text-foreground">Per-User Workspaces</h3>
                    <p class="text-sm text-muted-foreground">
                        Each team member manages their own isolated project portfolio with full security and data boundaries.
                    </p>
                </div>

                <div class="card bg-card border border-sidebar-border/70 p-6 shadow-xs rounded-2xl space-y-3">
                    <div class="rounded-xl bg-warning/10 p-3 text-warning-800 dark:text-warning w-fit">
                        <Layers class="h-6 w-6" />
                    </div>
                    <h3 class="font-bold text-lg text-foreground">DaisyUI & Lucide Design</h3>
                    <p class="text-sm text-muted-foreground">
                        Restrained, modern typography and component design with dark & light theme modes.
                    </p>
                </div>

                <div class="card bg-card border border-sidebar-border/70 p-6 shadow-xs rounded-2xl space-y-3">
                    <div class="rounded-xl bg-success/10 p-3 text-success w-fit">
                        <Calendar class="h-6 w-6" />
                    </div>
                    <h3 class="font-bold text-lg text-foreground">Timeline Tracking</h3>
                    <p class="text-sm text-muted-foreground">
                        Real-time status badges, due date calculations, overdue alerts, and filter controls.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-sidebar-border/70 py-6 text-center text-xs text-muted-foreground">
            Client Project Tracker &bull; Built with Laravel 13 + Vue 3 + Inertia.js + DaisyUI
        </footer>
    </div>
</template>
