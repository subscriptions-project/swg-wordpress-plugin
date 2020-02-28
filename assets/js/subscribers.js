/**
 * Removes paywalls for given productIds.
 * @param {!Set<string>} productIds Set of productIDs to unlock.
 */
function removePaywallsForProductIds(productIds) {
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

/**
 * Gets a list of Play Offers from a given Element.
 * @param {!Element} el
 * @return {string[]}
 */
function getPlayOffersFromElement(el) {
  if (!el.dataset.playOffers) {
    return [];
  }

  return el.dataset.playOffers
    .trim()
    .split(',')
    .map(p => p.trim());
}

// Wait for SwG library to become available.
(self.SWG = self.SWG || []).push(subscriptions => {
  subscriptions.setOnPaymentResponse(paymentResponse => {
    paymentResponse.then(response => {
      // TODO: Handle payment response.
      response.complete().then(() => {
        // TODO: Update page accordingly.
      });
    });
  });

  // Handle subscribe button clicks.
  const subscribeButtons = document.querySelectorAll('.swg-button');
  for (const subscribeButton of subscribeButtons) {
    subscribeButton.addEventListener('click', () => {
      const skus = getPlayOffersFromElement(subscribeButton);
      subscriptions.showOffers({ skus, isClosable: true });
    });
  }

  // Handle contribute button clicks.
  const contributeButtons = document.querySelectorAll('.swg-contribute-button');
  for (const contributeButton of contributeButtons) {
    contributeButton.addEventListener('click', () => {
      const skus = getPlayOffersFromElement(contributeButton);
      subscriptions.showContributionOptions({ skus, isClosable: true });
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
});
