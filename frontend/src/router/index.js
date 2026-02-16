import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    { path: '/', redirect: '/menu-builder' },

    {
        path: '/menu-builder',
        name: 'MenuBuilder',
        component: () => import('../pages/MenuBuilder.vue'),
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
