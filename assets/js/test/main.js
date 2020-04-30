import '../main';
import { CACHE_KEY } from '../unlock';

const SUBSCRIBERS = self.SWG[0];

describe('main', () => {
  let metaEl;
  let articleEl;
  let contributeButtonEl;
  let contributeLinkEl;
  let subscribeButtonEl;
  let subscribeLinkEl;
  let subscribeButtonElWithoutPlayOffersDefined;
  let subscriptions;

  beforeEach(() => {
    global.fetch = jest.fn(() => Promise.resolve({
      json: () => ({
        entitlements: [{
          products: ['premium']
        }],
      })
    }));
    delete global.localStorage[CACHE_KEY];

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

    subscribeButtonElWithoutPlayOffersDefined = document.createElement('div');
    subscribeButtonElWithoutPlayOffersDefined.classList.add('swg-subscribe-button');
    document.body.appendChild(subscribeButtonElWithoutPlayOffersDefined);
  });

  afterEach(() => {
    metaEl.remove();
    articleEl.remove();
  });

  it('handles missing meta element', async () => {
    metaEl.remove();

    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg-entitled')).toBeFalsy();
  });

  it('handles mismatched product in meta element', async () => {
    metaEl.setAttribute('content', 'exclusive');

    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg-entitled')).toBeFalsy();
  });

  it('handles missing article element', async () => {
    articleEl.remove();

    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg-entitled')).toBeFalsy();
  });

  it('marks article as entitled', async () => {
    await SUBSCRIBERS(subscriptions);
    expect(articleEl.classList.contains('swg--page-is-unlocked')).toBeTruthy();
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
});
