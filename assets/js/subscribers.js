/**
 * Removes paywalls for given productIds.
 * @param {!Set<string>} productIds Set of productIDs to unlock.
 * @return {void}
 */
export function removePaywallsForProductIds(productIds) {
	const metaEl = document.querySelector('meta[name=subscriptions-product-id]');
	if (!metaEl) {
		return;
	}

	const productId = metaEl.getAttribute('content');
	if (!productIds.has(productId)) {
		return;
	}

	const articleEl = document.querySelector('article');
	if (!articleEl) {
		return;
	}

	articleEl.classList.add('swg-entitled');
}

// Wait for SwG library to become available.
(self.SWG = self.SWG || []).push(subscriptions => {

  // Handle payment responses.
  subscriptions.setOnPaymentResponse(response => {
    // 1. Handle subscription response.
    console.log('Payment response');

    // 2. Once subscription is processed (account created):
    response.complete().then(() => {
      // 3. The subscription is fully processed.
      console.log('response.complete');
    });
  });

  // Handle subscribe button clicks.
  const subscribeButtons = document.querySelectorAll('.swg-button');
  for (const subscribeButton of subscribeButtons) {
    subscribeButton.addEventListener('click', () => {
      let skus = [];

      if (subscribeButton.dataset.playOffers) {
        skus = subscribeButton.dataset.playOffers
          .trim()
          .split(',')
          .map(p => p.trim());
      }

      subscriptions.showOffers({ skus, isClosable: true });
    });
  }

  // Get entitlements, and remove paywalls accordingly.
  // TODO: Decrypt content.
  subscriptions.getEntitlements().then(response => {
    const productIds = new Set();
    for (const entitlement of response.entitlements) {
      for (const productId of entitlement.products) {
        productIds.add(productId);
      }
    }
    removePaywallsForProductIds(productIds);
  });

  console.log('📰 SwgPress has started.');
});
