/**
 * Returns products the user owns.
 * @param {*} subscriptions SwG API
 * @return {Promise<Set<string>>} Products the user owns.
 */
export function getOwnedProducts(subscriptions) {
  return subscriptions.getEntitlements().then(response => {
    const products = new Set();
    for (const entitlement of response.entitlements) {
      for (const product of entitlement.products) {
        products.add(product);
      }
    }
    return products;
  });
}
