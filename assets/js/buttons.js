/**
 * Handle clicks on Contribute buttons.
 * @param {*} subscriptions SwG API
 */
export function handleContributeClicks(subscriptions) {
  const contributeButtons = new Set([].concat(
    Array.from(document.querySelectorAll('.swg-contribute-button')),
    Array.from(document.querySelectorAll('a[href="#swg-contribute"]'))
  ));
  console.log(contributeButtons);
  for (const contributeButton of contributeButtons) {
    contributeButton.addEventListener('click', () => {
      const skus = getPlayOffersFromElement(contributeButton);
      subscriptions.showContributionOptions({ skus, isClosable: true });
    });
  }
}

/**
 * Handle clicks on Subscribe buttons.
 * @param {*} subscriptions SwG API
 */
export function handleSubscribeClicks(subscriptions) {
  const subscribeButtons = new Set([].concat(
    Array.from(document.querySelectorAll('.swg-subscribe-button')),
    Array.from(document.querySelectorAll('a[href="#swg-subscribe"]'))
  ));
  for (const subscribeButton of subscribeButtons) {
    subscribeButton.addEventListener('click', () => {
      const skus = getPlayOffersFromElement(subscribeButton);
      subscriptions.showOffers({ skus, isClosable: true });
    });
  }
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