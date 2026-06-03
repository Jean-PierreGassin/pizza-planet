export function applyCsrfHeader (headers: Headers, method: string | undefined): void {
  if (isReadMethod(method) || headers.has('X-XSRF-TOKEN')) {
    return
  }

  const token = readCookie('XSRF-TOKEN')

  if (token !== null) {
    headers.set('X-XSRF-TOKEN', token)
  }
}

function isReadMethod (method: string | undefined): boolean {
  return ['GET', 'HEAD', 'OPTIONS'].includes((method ?? 'GET').toUpperCase())
}

function readCookie (name: string): string | null {
  const cookie = document.cookie
    .split('; ')
    .find((cookie) => cookie.startsWith(`${name}=`))

  if (cookie === undefined) {
    return null
  }

  return decodeURIComponent(cookie.slice(name.length + 1))
}
