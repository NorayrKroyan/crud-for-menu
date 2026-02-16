import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// keep your old design CSS
import './styles/base.css'
import './styles/table.css'
import './styles/modal.css'

createApp(App).use(router).mount('#app')
