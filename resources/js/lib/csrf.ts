export function xsrfHeader(): Record<string, string> {
    const token = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1];

    return token ? { 'X-XSRF-TOKEN': decodeURIComponent(token) } : {};
}
