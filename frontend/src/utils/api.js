const API_BASE = import.meta.env.VITE_API_BASE || 'http://127.0.0.1:8000'

export async function fetchJson(path, opts = {}) {
    const method = (opts.method || 'GET').toUpperCase()
    const headers = { ...(opts.headers || {}) }

    let body = opts.body
    if (body && typeof body === 'object' && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json'
        body = JSON.stringify(body)
    }

    const res = await fetch(`${API_BASE}${path}`, { method, headers, body })

    const text = await res.text()
    let data = null
    try {
        data = text ? JSON.parse(text) : null
    } catch {
        data = text
    }

    if (!res.ok) {
        const msg =
            (data && data.message) ||
            (typeof data === 'string' ? data : '') ||
            `HTTP ${res.status}`
        throw new Error(msg)
    }

    return data
}
