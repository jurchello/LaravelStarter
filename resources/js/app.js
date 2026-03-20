import './bootstrap'
import { connectModules } from '@/core/connect'

document.addEventListener('DOMContentLoaded', () => {
    void connectModules()
})
