<script setup lang="ts">
import type { LoginCredentials } from '../types'

defineProps<{
  errorMessage: string
  isLoggingIn: boolean
}>()

const credentials = defineModel<LoginCredentials>({
  required: true
})

defineEmits<{
  submit: []
}>()
</script>

<template>
  <section class="grid flex-1 items-center gap-10 py-10 lg:grid-cols-[1fr_420px]">
    <div>
      <p class="mb-3 text-sm font-semibold uppercase tracking-normal text-basil">
        Crew login
      </p>
      <h1 class="max-w-3xl text-4xl font-black leading-tight tracking-normal text-ink sm:text-5xl">
        Sign in to move orders through the kitchen.
      </h1>
      <p class="mt-6 max-w-2xl text-lg leading-8 text-ink/75">
        Orders stay locked until a crew member signs in. Once authenticated,
        the demo starts every item at the beginning so each webhook transition is visible.
      </p>
    </div>

    <form
      class="rounded-lg border border-ink/10 bg-white p-6 shadow-sm"
      @submit.prevent="$emit('submit')"
    >
      <h2 class="text-xl font-bold text-ink">
        Login
      </h2>

      <label
        class="mt-6 block text-sm font-semibold text-ink"
        for="email"
      >
        Email
      </label>
      <input
        id="email"
        v-model="credentials.email"
        class="mt-2 w-full rounded-md border border-ink/15 px-3 py-2 text-base outline-none transition focus:border-sauce focus:ring-2 focus:ring-sauce/20"
        name="email"
        autocomplete="username"
        type="email"
        required
      />

      <label
        class="mt-5 block text-sm font-semibold text-ink"
        for="password"
      >
        Password
      </label>
      <input
        id="password"
        v-model="credentials.password"
        class="mt-2 w-full rounded-md border border-ink/15 px-3 py-2 text-base outline-none transition focus:border-sauce focus:ring-2 focus:ring-sauce/20"
        name="password"
        autocomplete="current-password"
        type="password"
        required
      />

      <p
        v-if="errorMessage"
        class="mt-4 rounded-md border border-sauce/25 bg-sauce/5 px-3 py-2 text-sm font-semibold text-sauce"
        role="alert"
      >
        {{ errorMessage }}
      </p>

      <button
        type="submit"
        class="mt-6 w-full rounded-md bg-sauce px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-sauce/90 disabled:cursor-not-allowed disabled:bg-ink/30"
        :disabled="isLoggingIn"
      >
        {{ isLoggingIn ? 'Signing in...' : 'Sign in' }}
      </button>
    </form>
  </section>
</template>
