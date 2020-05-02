/**
 * Handle clicks on Contribute buttons.
 * @param {*} subscriptions SwG API
 */
export function handleContributeClicks(subscriptions) {
  const contributeButtons = new Set([].concat(
    Array.from(document.querySelectorAll('.swg-contribute-button')),
    Array.from(document.querySelectorAll('a[href="#swg-contribute"]'))
  ));
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

/** Handle clicks on Signin buttons. */
export function handleSignInClicks() {
  const signinButtons = new Set([].concat(
    Array.from(document.querySelectorAll('.swg-signin-button')),
    Array.from(document.querySelectorAll('a[href="#swg-signin"]'))
  ));
  for (const signinButton of signinButtons) {
    signinButton.addEventListener('click', (e) => {
      e.preventDefault();

      gapi.load('auth2', async () => {
        const { code } = await gapi.auth2.init().grantOfflineAccess();
        const url =
          SubscribeWithGoogleWpGlobals.API_BASE_URL +
          '/create-1p-cookie';
        await window.fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            gsi_auth_code: code,
          }),
        });
        location.reload();
      });
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
