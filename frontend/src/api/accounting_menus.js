import { fetchJson } from '../utils/api.js'

export function listAccountingMenus(showInactive = false) {
    const qs = new URLSearchParams({
        show_inactive: showInactive ? '1' : '0',
    }).toString()
    return fetchJson(`/api/accounting-menus?${qs}`)
}

export function listAccountingMenuParents(showInactive = false) {
    const qs = new URLSearchParams({
        show_inactive: showInactive ? '1' : '0',
    }).toString()
    return fetchJson(`/api/accounting-menus/parents?${qs}`)
}

export function createAccountingMenu(payload) {
    return fetchJson('/api/accounting-menus', { method: 'POST', body: payload })
}

export function updateAccountingMenu(id, payload) {
    return fetchJson(`/api/accounting-menus/${id}`, { method: 'PUT', body: payload })
}

export function deactivateAccountingMenu(id) {
    return fetchJson(`/api/accounting-menus/${id}`, { method: 'DELETE' })
}
