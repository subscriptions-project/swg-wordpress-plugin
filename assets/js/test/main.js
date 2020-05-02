import '../main';
import { CACHE_KEY } from '../unlock';

const SUBSCRIBERS = self.SWG[0];

// TODO: Refactor this into multiple files, or at least describe blocks.
describe('main', () => {
  let metaEl;
  let articleEl;
  let contributeButtonEl;
  let contributeLinkEl;
  let subscribeButtonEl;
  let subscribeLinkEl;
  let signinButtonEl;
  let signinLinkEl;
  let subscribeButtonElWithoutPlayOffersDefined;
  let subscriptions;

  beforeEach(() => {
    delete global.location;
    global.location = {
      reload: jest.fn(),
    };
    
    global.fetch = jest.fn(() => Promise.resolve({
      json: () => ({
        entitlements: [{
          products: ['premium']
        }],
      })
    }));
    delete global.localStorage[CACHE_KEY];

    global.gapi = {
      auth2: {
        init: () => ({
          grantOfflineAccess: () => Promise.resolve({code: 1}),
        }),
      },
      load: jest.fn((name, cb) => cb()),
    };

    subscriptions = {
      setOnPaymentResponse: callback => {
        const response = {
          complete: () => Promise.resolve(),
        };
        callback(Promise.resolve(response));
      },
      getEntitlements: () => Promise.resolve({
        entitlements: [
          {
            products: ['basic', 'premium'],
          },
        ],
      }),
      showContributionOptions: jest.fn(),
      showOffers: jest.fn(),
    };

    metaEl = document.createElement('meta');
    metaEl.setAttribute('name', 'subscriptions-product-id');
    metaEl.setAttribute('content', 'premium');
    document.body.appendChild(metaEl);

    articleEl = document.createElement('article');
    document.body.appendChild(articleEl);

    contributeButtonEl = document.createElement('div');
    contributeButtonEl.classList.add('swg-contribute-button');
    contributeButtonEl.dataset.playOffers = 'basic, premium';
    document.body.appendChild(contributeButtonEl);

    contributeLinkEl = document.createElement('a');
    contributeLinkEl.href = '#swg-contribute';
    contributeLinkEl.dataset.playOffers = 'basic, premium';
    document.body.appendChild(contributeLinkEl);

    subscribeButtonEl = document.createElement('div');
    subscribeButtonEl.classList.add('swg-subscribe-button');
    subscribeButtonEl.dataset.playOffers = 'basic, premium';
    document.body.appendChild(subscribeButtonEl);

    subscribeLinkEl = document.createElement('a');
    subscribeLinkEl.href = '#swg-subscribe';
    subscribeLinkEl.dataset.playOffers = 'basic, premium';
    document.body.appendChild(subscribeLinkEl);

    signinButtonEl = document.createElement('div');
    signinButtonEl.classList.add('swg-signin-button');
    signinButtonEl.dataset.playOffers = 'basic, premium';
    document.body.appendChild(signinButtonEl);

    signinLinkEl = document.createElement('a');
    signinLinkEl.href = '#swg-signin';
    signinLinkEl.dataset.playOffers = 'basic, premium';
    document.body.appendChild(signinLinkEl);

    subscribeButtonElWithoutPlayOffersDefined = document.createElement('div');
    subscribeButtonElWithoutPlayOffersDefined.classList.add('swg-subscribe-button');
    document.body.appendChild(subscribeButtonElWithoutPlayOffersDefined);
  });

  afterEach(() => {
    metaEl.remove();
    articleEl.remove();
  });

  it('fetches entitlements', async () => {
    await SUBSCRIBERS(subscriptions);
    expect(fetch).toBeCalledWith('/api/entitlements');
  });

  it('fetches entitlements if cache does not have the right product', async () => {
    global.localStorage[CACHE_KEY] = JSON.stringify({
      expiration: Date.now() * 2,
      products: [],
    });
    await SUBSCRIBERS(subscriptions);
    expect(fetch).toBeCalledWith('/api/entitlements');
  });

  it('fetches entitlements if cache is expired', async () => {
    global.localStorage[CACHE_KEY] = JSON.stringify({
      expiration: Date.now() / 2,
      products: ['premium'],
    });
    await SUBSCRIBERS(subscriptions);
    expect(fetch).toBeCalledWith('/api/entitlements');
  });

  it('does not fetch entitlements if cache entitles user', async () => {
    global.localStorage[CACHE_KEY] = JSON.stringify({
      expiration: Date.now() * 2,
      products: ['premium'],
    });
    await SUBSCRIBERS(subscriptions);
    expect(fetch).not.toBeCalled();
  });

  it('marks article as unlocked when a product matches', async () => {
    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-unlocked')).toBeTruthy();
  });

  it('marks article as locked when no products match', async () => {
    global.fetch = jest.fn(() => Promise.resolve({
      json: () => ({
        entitlements: [{
          products: []
        }],
      })
    }));
    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-locked')).toBeTruthy();
  });

  it('marks article as locked when request fails', async () => {
    global.fetch = jest.fn(() => Promise.reject());
    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-locked')).toBeTruthy();
  });

  it('handles missing meta element', async () => {
    metaEl.remove();

    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-unlocked')).toBeFalsy();
  });

  it('handles mismatched product in meta element', async () => {
    metaEl.setAttribute('content', 'exclusive');

    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-unlocked')).toBeFalsy();
  });

  it('handles missing article element', async () => {
    articleEl.remove();

    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-unlocked')).toBeFalsy();
  });

  it('handles subscribe button clicks', async () => {
    await SUBSCRIBERS(subscriptions);

    subscribeButtonEl.click();

    expect(subscriptions.showOffers.mock.calls).toEqual([[{
      isClosable: true,
      skus: ['basic', 'premium'],
    }]]);
  });

  it('handles subscribe link clicks', async () => {
    await SUBSCRIBERS(subscriptions);

    subscribeLinkEl.click();

    expect(subscriptions.showOffers.mock.calls).toEqual([[{
      isClosable: true,
      skus: ['basic', 'premium'],
    }]]);
  });

  it('handles subscribe button clicks when play offers are not defined', async () => {
    await SUBSCRIBERS(subscriptions);

    subscribeButtonElWithoutPlayOffersDefined.click();

    expect(subscriptions.showOffers.mock.calls).toEqual([[{
      isClosable: true,
      skus: [],
    }]]);
  });

  it('handles contribute button clicks', async () => {
    await SUBSCRIBERS(subscriptions);

    contributeButtonEl.click();

    expect(subscriptions.showContributionOptions.mock.calls).toEqual([[{
      isClosable: true,
      skus: ['basic', 'premium'],
    }]]);
  });

  it('handles contribute link clicks', async () => {
    await SUBSCRIBERS(subscriptions);

    contributeLinkEl.click();

    expect(subscriptions.showContributionOptions.mock.calls).toEqual([[{
      isClosable: true,
      skus: ['basic', 'premium'],
    }]]);
  });

  it('handles signin button clicks', async () => {
    await SUBSCRIBERS(subscriptions);
    global.fetch = jest.fn();

    await signinButtonEl.click();

    expect(gapi.load).toBeCalled();
    expect(fetch).toBeCalledWith(
      '/api/create-1p-cookie',
      {
        "body": "{\"gsi_auth_code\":1}",
        "headers": {"Content-Type": "application/json"},
        "method": "POST"
      });
  });

  it('handles signin link clicks', async () => {
    await SUBSCRIBERS(subscriptions);

    await signinLinkEl.click();

    expect(gapi.load).toBeCalled();
    expect(fetch).toBeCalledWith(
      '/api/create-1p-cookie',
      {
        "body": "{\"gsi_auth_code\":1}",
        "headers": {"Content-Type": "application/json"},
        "method": "POST"
      });
  });
});
