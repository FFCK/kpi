// Shim à injecter via browser_evaluate AVANT toute connexion.
// Réécrit, côté navigateur uniquement, les URLs HTTPS servies par Traefik
// (certificat auto-signé que Playwright rejette) vers le port HTTP exposé par
// kpi_php, accepté sans souci.
//
//   - API   : https://kpi.localhost/api2 -> http://localhost:8003/api2  (via fetch)
//   - Images: https://kpi.localhost/img  -> http://localhost:8003/img   (via src)
//
// Usage (Playwright MCP) :
//   browser_navigate http://localhost:3004/admin2/login
//   browser_evaluate <contenu de ce fichier>
//   ... puis login normal.
() => {
  const HOST = 'https://kpi.localhost'
  const TO = 'http://localhost:8003'

  // 1. API calls (fetch)
  const orig = window.fetch
  window.fetch = (input, init) => {
    try {
      if (typeof input === 'string' && input.startsWith(HOST + '/api2')) {
        input = input.replace(HOST, TO)
      } else if (input && input.url && input.url.startsWith(HOST + '/api2')) {
        input = new Request(input.url.replace(HOST, TO), input)
      }
    } catch { /* noop */ }
    return orig(input, init)
  }

  // 2. Images (<img src>), including those added dynamically after render.
  const fixImg = (img) => {
    const src = img.getAttribute('src')
    if (src && src.startsWith(HOST)) img.setAttribute('src', src.replace(HOST, TO))
  }
  document.querySelectorAll('img').forEach(fixImg)
  new MutationObserver((muts) => {
    for (const m of muts) {
      for (const node of m.addedNodes) {
        if (node.nodeType !== 1) continue
        if (node.tagName === 'IMG') fixImg(node)
        node.querySelectorAll?.('img').forEach(fixImg)
      }
      if (m.type === 'attributes' && m.target.tagName === 'IMG') fixImg(m.target)
    }
  }).observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] })

  return 'fetch + image shim installed'
}
