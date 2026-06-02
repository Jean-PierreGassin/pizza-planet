import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '@/pages/HomePage.vue'

describe('HomePage', () => {
  it('renders the scaffold readiness state', async () => {
    const router = createRouter({
      history: createWebHistory(),
      routes: [
        {
          path: '/',
          component: HomePage
        }
      ]
    })

    await router.push('/')
    await router.isReady()

    const wrapper = mount(HomePage, {
      global: {
        plugins: [router]
      }
    })

    expect(wrapper.text()).toContain('Pizza Planet is ready for its first real screen.')
    expect(wrapper.text()).toContain('Vue 3 app bootstrapped through Vite')
  })
})
