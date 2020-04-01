/**
 * Unlocks content for one or more products.
 * @param {!Set<string>} products Products to unlock content for.
 */
export function unlockContent(products) {
  const metaEl = document.querySelector('meta[name=subscriptions-product-id]');
  if (!metaEl) {
    return;
  }

  const productId = metaEl.getAttribute('content');
  if (!products.has(productId)) {
    return;
  }

  const articleEl = document.querySelector('article');
  if (!articleEl) {
    return;
  }

  articleEl.classList.add('swg-entitled');
}
