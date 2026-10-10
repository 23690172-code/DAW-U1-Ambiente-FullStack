import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import DashboardView from '@/views/DashboardView.vue'
import ServicesView from '@/views/ServicesView.vue'
import { useAuth } from '@/services/auth'

const routes = [
  { path: '/', redirect: '/dashboard' },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { hideHeader: true }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },
  {
    path: '/servicios',
    name: 'servicios',
    component: ServicesView,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to) => {
  const { initialized, authenticated, checkSession } = useAuth()

  if (!initialized.value) {
    await checkSession()
  }

  if (to.meta.requiresAuth && !authenticated.value) {
    return {
      name: 'login',
      query: {
        redirect: to.fullPath
      }
    }
  }

  if (to.name === 'login' && authenticated.value) {
    return {
      name: 'dashboard'
    }
  }
})

export default router