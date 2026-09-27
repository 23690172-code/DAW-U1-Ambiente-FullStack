import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ServicesView from '../views/ServicesView.vue'

const routes = [
  {
    path: '/',
    name: 'inicio',
    component: HomeView
  },
  {
    path: '/servicios',
    name: 'servicios',
    component: ServicesView
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router