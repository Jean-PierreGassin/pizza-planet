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

const orderAlmostReady: Order[] = [
  {
    id: 20,
    reference: 'PP-MARS-002',
    fulfillment_type: 'delivery',
    status: 'in_progress',
    items: [
      {
        id: 60,
        name: 'Meteor Meatball Pizza',
        status: 'baking'
      },
      {
        id: 61,
        name: 'Rocket Salad',
        status: 'ready'
      }
    ]
  }
]

const finalizedOrder: Order[] = [
  {
    ...orderAlmostReady[0],
    status: 'ready_for_delivery',
    items: orderAlmostReady[0].items.map((item) => ({
      ...item,
      status: 'ready'
    }))
  }
]

function cloneOrders (orders: Order[]): Order[] {
  return orders.map((order) => ({
    ...order,
    items: order.items.map((item) => ({ ...item }))
  }))
}

describe('HomePage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(authApi.currentSession).mockResolvedValue({ user: mario })
    vi.mocked(authApi.csrfCookie).mockResolvedValue()
    vi.mocked(authApi.login).mockResolvedValue({ user: mario })
    vi.mocked(authApi.logout).mockResolvedValue()
    vi.mocked(ordersApi.list).mockResolvedValue({ orders: cloneOrders(seededOrders) })
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
    expect(wrapper.text()).toContain('Order status')
    expect(wrapper.text()).toContain('Items ready')
    expect(wrapper.text()).toContain('Item status')
  })

  it('keeps the user authenticated when initial order loading fails', async () => {
    vi.mocked(ordersApi.list).mockRejectedValue(new Error('Orders unavailable'))
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

    expect(wrapper.text()).toContain('Welcome back, Mario.')
    expect(wrapper.text()).toContain('Orders could not load. Check the API is running and refresh.')
    expect(wrapper.text()).not.toContain('Login failed.')
  })

  it('clears local auth state when server logout fails', async () => {
    vi.mocked(authApi.logout).mockRejectedValue(new Error('Session already expired'))
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
    await wrapper.findAll('button').find((button) => button.text() === 'Log out')?.trigger('click')
    await vi.dynamicImportSettled()

    expect(wrapper.text()).toContain('Sign in to move orders through the kitchen.')
    expect((wrapper.get('input[name="email"]').element as HTMLInputElement).value).toBe('')
    expect((wrapper.get('input[name="password"]').element as HTMLInputElement).value).toBe('')
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

  it('refreshes orders after an item move so final order status is visible', async () => {
    vi.mocked(ordersApi.list)
      .mockResolvedValueOnce({ orders: cloneOrders(orderAlmostReady) })
      .mockResolvedValueOnce({ orders: cloneOrders(finalizedOrder) })
    vi.mocked(ordersApi.updateItemStatus).mockResolvedValue({
      order_item_id: 60,
      status: 'ready'
    })

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
    await wrapper.findAll('button').find((button) => button.text() === 'Mark ready')?.trigger('click')
    await vi.dynamicImportSettled()

    expect(ordersApi.updateItemStatus).toHaveBeenCalledWith(20, 60, 'ready')
    expect(ordersApi.list).toHaveBeenCalledTimes(2)
    expect(wrapper.text()).toContain('Ready orders')
    expect(wrapper.text()).toContain('ready for delivery')
    expect(wrapper.text()).toContain('2 / 2')
  })
})
