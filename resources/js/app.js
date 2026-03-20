import './bootstrap'
import { connectModules } from '@/core/connect'
import { connectCurrentPage } from '@/pages/connect'
import {
    markAppBooting,
    markAppFinalReady,
} from '@/shared/runtime-state/module'

document.addEventListener('DOMContentLoaded', () => {
    markAppBooting()
    void connectModules().then(async () => {
        await connectCurrentPage()
        markAppFinalReady()
    })
})
