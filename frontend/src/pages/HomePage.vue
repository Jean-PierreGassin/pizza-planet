<script setup lang="ts">
import DefaultLayout from '@/layouts/DefaultLayout.vue'

const readinessItems = [
  'Vue 3 app bootstrapped through Vite',
  'TypeScript and vue-tsc ready for strict checks',
  'Tailwind CSS loaded from the shared style entrypoint',
  'Laravel API boundary reserved under shared/api'
] as const
</script>

<template>
  <DefaultLayout
    eyebrow="Frontend scaffold"
    title="Pizza Planet is ready for its first real screen."
  >
    <p>
      The frontend app is separated from Laravel, wired for local development,
      and shaped around the project architecture before product features land.
    </p>

    <template #aside>
      <h2 class="text-lg font-bold text-ink">
        Stack check
      </h2>
      <ul class="mt-5 space-y-3">
        <li
          v-for="item in readinessItems"
          :key="item"
          class="flex gap-3 text-sm leading-6 text-ink/75"
        >
          <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-basil" />
          <span>{{ item }}</span>
        </li>
      </ul>
    </template>
  </DefaultLayout>
</template>
