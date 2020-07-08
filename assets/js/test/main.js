import '../main';
import { CACHE_KEY } from '../unlock';

const SUBSCRIBERS = self.SWG[0];

// TODO: Refactor this into multiple files, or at least describe blocks.
describe('main', () => {
  let articleEl;
  let contributeButtonEl;
  let contributeLinkEl;
  let ldJsonEl;
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
      hash: '',
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
          grantOfflineAccess: () => Promise.resolve({ code: 1 }),
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

    ldJsonEl = document.createElement('script');
    ldJsonEl.setAttribute('type', 'application/ld+json');
    ldJsonEl.innerText = `
    {
			"@context": "http://schema.org",
			"@type": "NewsArticle",
			"isAccessibleForFree": false,
			"isPartOf": {
				"@type": ["CreativeWork", "Product"],
				"productID": "premium"
			}
    }
    `;
    document.head.appendChild(ldJsonEl);

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
    ldJsonEl.remove();
    articleEl.remove();
  });

  it('attempts to fetch the page content', async () => {
    global.SubscribeWithGoogleWpGlobals.POST_ID = 1;
    await SUBSCRIBERS(subscriptions);
    
    expect(fetch).toBeCalledWith('/wp-json/wp/v2/posts/1');
  });


  it('conditionally disables 3p entitlements', async () => {
    subscriptions.getEntitlements = jest.fn();
    location.hash = '#swg.wp.experiments=disable3p';
    await SUBSCRIBERS(subscriptions);
    expect(subscriptions.getEntitlements).not.toBeCalled();
  });

  it('marks article as locked when no products match', async () => {
    global.fetch = jest.fn(() => Promise.resolve({
      json: () => ({
        entitlements: [{
          products: []
        }],
      })
    }));
    subscriptions.getEntitlements = () => Promise.resolve({
      entitlements: [
        {
          products: [],
        },
      ],
    });
    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-locked')).toBeTruthy();
  });

  it('marks article as locked when requests fail', async () => {
    global.fetch = jest.fn(() => Promise.reject());
    subscriptions.getEntitlements = () => Promise.reject();
    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-locked')).toBeTruthy();
  });

  it('handles missing meta element', async () => {
    ldJsonEl.remove();

    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-unlocked')).toBeFalsy();
  });

  it('handles mismatched product in meta element', async () => {
    ldJsonEl.innerText = ldJsonEl.innerText.replace('premium', 'exclusive');

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
      '/api/create-1p-gsi-cookie',
      {
        "body": "{\"gsi_auth_code\":1}",
        "headers": { "Content-Type": "application/json" },
        "method": "POST"
      });
  });

  it('handles signin link clicks', async () => {
    await SUBSCRIBERS(subscriptions);

    await signinLinkEl.click();

    expect(gapi.load).toBeCalled();
    expect(fetch).toBeCalledWith(
      '/api/create-1p-gsi-cookie',
      {
        "body": "{\"gsi_auth_code\":1}",
        "headers": { "Content-Type": "application/json" },
        "method": "POST"
      });
  });
});
