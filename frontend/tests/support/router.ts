import { createMemoryHistory, createRouter, type RouteRecordRaw, type Router } from 'vue-router'
import HomePage from '@/pages/HomePage.vue'

export const testRoutes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: HomePage
  }
]

export async function createTestRouter (initialPath = '/', routes = testRoutes): Promise<Router> {
  const router = createRouter({
    history: createMemoryHistory(),
    routes
  })

  await router.push(initialPath)
  await router.isReady()

  return router
}
