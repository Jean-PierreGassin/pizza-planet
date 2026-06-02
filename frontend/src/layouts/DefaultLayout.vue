<script setup lang="ts">
defineProps<{
  eyebrow?: string
  title: string
}>()
</script>

<template>
  <main class="min-h-screen bg-[#fff8ed] text-ink">
    <section class="mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-8 sm:px-8 lg:px-10">
      <header class="flex items-center justify-between border-b border-ink/10 pb-5">
        <RouterLink
          to="/"
          class="text-base font-bold tracking-normal text-sauce"
        >
          Pizza Planet
        </RouterLink>
      </header>

      <section class="grid flex-1 items-center gap-10 py-10 lg:grid-cols-[1fr_0.85fr]">
        <div>
          <p
            v-if="eyebrow"
            class="mb-3 text-sm font-semibold uppercase tracking-normal text-basil"
          >
            {{ eyebrow }}
          </p>
          <h1 class="max-w-3xl text-4xl font-black leading-tight tracking-normal text-ink sm:text-5xl">
            {{ title }}
          </h1>
          <div class="mt-6 max-w-2xl text-lg leading-8 text-ink/75">
            <slot />
          </div>
        </div>

        <aside class="rounded-lg border border-ink/10 bg-white p-6 shadow-sm">
          <slot name="aside" />
        </aside>
      </section>
    </section>
  </main>
</template>
