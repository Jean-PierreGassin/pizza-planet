import { mount } from '@vue/test-utils'
import HomePage from '@/pages/HomePage.vue'
import { createTestRouter } from '../support/router'

describe('HomePage', () => {
  it('renders the scaffold readiness state', async () => {
    const wrapper = mount(HomePage, {
      global: {
        plugins: [await createTestRouter()]
      }
    })

    expect(wrapper.text()).toContain('Pizza Planet is ready for its first real screen.')
    expect(wrapper.text()).toContain('Vue 3 app bootstrapped through Vite')
  })
})
