// Обёртка над fetch: всегда возвращает {ok, status, data}, исключений наружу не бросает.
export async function request(url, { method = 'GET', body, signal } = {}) {
    try {
        const response = await fetch(url, { method, body, signal, credentials: 'same-origin' });
        let data = null;
        try { data = await response.json(); } catch { /* ответ без JSON */ }
        return { ok: response.ok && data?.ok !== false, status: response.status, data };
    } catch (error) {
        return { ok: false, status: 0, data: null, aborted: error?.name === 'AbortError' };
    }
}

export const postForm = (url, fields) => request(url, { method: 'POST', body: new URLSearchParams(fields) });
