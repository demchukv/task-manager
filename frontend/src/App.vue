<script setup lang="ts">
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'

const auth = useAuthStore()
const router = useRouter()

if (auth.isAuthenticated) {
  auth.startSessionWatcher()
}

const logout = async () => {
  auth.stopSessionWatcher()
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-background px-2 md:px-6 lg:px-10 xl:px-16 mx-auto">
    <header v-if="auth.isAuthenticated"
      class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div class="container flex h-14 items-center justify-between">
        <div class="flex items-center gap-4 md:gap-6">
          <RouterLink to="/projects" class="flex items-center space-x-2">
            <span class="inline-block font-bold text-primary text-xl">Projects & Task</span>
          </RouterLink>
        </div>
        <div class="flex items-center gap-4">
          <div class="hidden md:flex flex-col items-end">
            <span class="text-xs font-medium">{{ auth.user?.name }}</span>
            <span class="text-[10px] text-muted-foreground uppercase tracking-wider">{{ auth.user?.role }}</span>
          </div>
          <Button variant="outline" size="sm" @click="logout" class="h-8">
            Вийти
          </Button>
        </div>
      </div>
    </header>

    <main class="container">
      <RouterView />
    </main>
  </div>
</template>

<style>
@import "tailwindcss";
</style>
