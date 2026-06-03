import { mount } from '@vue/test-utils'
import HomePage from '@/pages/HomePage.vue'
import { createTestRouter } from '../support/router'
import { authApi } from '@/features/auth/api/authApi'
import { ordersApi } from '@/features/orders/api/ordersApi'
import type { Order } from '@/features/orders/types'

vi.mock('@/features/auth/api/authApi', () => ({
  authApi: {
    currentSession: vi.fn(),
    csrfCookie: vi.fn(),
    login: vi.fn(),
    logout: vi.fn()
  }
}))

vi.mock('@/features/orders/api/ordersApi', () => ({
  ordersApi: {
    list: vi.fn(),
    updateItemStatus: vi.fn()
  }
}))

const mario = {
  id: 1,
  name: 'Mario',
  email: 'mario@pizzaplanet.test'
}

const seededOrders: Order[] = [
  {
    id: 10,
    reference: 'PP-MOON-001',
    fulfillment_type: 'delivery',
    status: 'pending',
    items: [
      {
        id: 50,
        name: 'Galactic Garlic Knots',
        status: 'pending'
      }
    ]
  }
]

describe('HomePage', () => {
  beforeEach(() => {
    vi.mocked(authApi.currentSession).mockResolvedValue({ user: mario })
    vi.mocked(authApi.csrfCookie).mockResolvedValue()
    vi.mocked(authApi.login).mockResolvedValue({ user: mario })
    vi.mocked(authApi.logout).mockResolvedValue()
    vi.mocked(ordersApi.list).mockResolvedValue({ orders: [...seededOrders] })
    vi.mocked(ordersApi.updateItemStatus).mockResolvedValue({
      order_item_id: 50,
      status: 'preparing'
    })
  })

  it('renders an empty login form without demo credentials prefilled', async () => {
    const wrapper = mount(HomePage, {
      global: {
        plugins: [await createTestRouter()]
      }
    })

    await vi.dynamicImportSettled()

    expect(wrapper.text()).toContain('Sign in to move orders through the kitchen.')
    expect((wrapper.get('input[name="email"]').element as HTMLInputElement).value).toBe('')
    expect((wrapper.get('input[name="password"]').element as HTMLInputElement).value).toBe('')
    expect(authApi.currentSession).not.toHaveBeenCalled()
  })

  it('logs in with typed credentials and renders seeded orders', async () => {
    const wrapper = mount(HomePage, {
      global: {
        plugins: [await createTestRouter()]
      }
    })

    await vi.dynamicImportSettled()
    await wrapper.get('input[name="email"]').setValue('mario@pizzaplanet.test')
    await wrapper.get('input[name="password"]').setValue('ilovepizza')
    await wrapper.get('form').trigger('submit')
    await vi.dynamicImportSettled()

    expect(authApi.login).toHaveBeenCalledWith({
      email: 'mario@pizzaplanet.test',
      password: 'ilovepizza'
    })
    expect(wrapper.text()).toContain('Welcome back, Mario.')
    expect(wrapper.text()).toContain('PP-MOON-001')
    expect(wrapper.text()).toContain('Galactic Garlic Knots')
  })

  it('moves an item to its next status', async () => {
    const wrapper = mount(HomePage, {
      global: {
        plugins: [await createTestRouter()]
      }
    })

    await vi.dynamicImportSettled()
    await wrapper.get('input[name="email"]').setValue('mario@pizzaplanet.test')
    await wrapper.get('input[name="password"]').setValue('ilovepizza')
    await wrapper.get('form').trigger('submit')
    await vi.dynamicImportSettled()
    await wrapper.findAll('button').find((button) => button.text() === 'Start preparing')?.trigger('click')
    await vi.dynamicImportSettled()

    expect(ordersApi.updateItemStatus).toHaveBeenCalledWith(10, 50, 'preparing')
    expect(wrapper.text()).toContain('preparing')
  })
})
