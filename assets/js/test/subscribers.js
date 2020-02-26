import '../subscribers';

const SUBSCRIBERS = self.SWG[0];

describe('subscribers', () => {
	let metaEl;
	let articleEl;
	let contributeButtonEl;
	let subscribeButtonEl;
	let subscriptions;

	beforeEach(() => {
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
			showContributions: jest.fn(),
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

		subscribeButtonEl = document.createElement('div');
		subscribeButtonEl.classList.add('swg-button');
		subscribeButtonEl.dataset.playOffers = 'basic, premium';
		document.body.appendChild(subscribeButtonEl);
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
		expect(articleEl.classList.contains('swg-entitled')).toBeTruthy();
	});

	it('handles subscribe button clicks', async () => {
		await SUBSCRIBERS(subscriptions);

		subscribeButtonEl.click();

		expect(subscriptions.showOffers.mock.calls).toEqual([[{
			isClosable: true,
			skus: ['basic', 'premium'],
		}]]);
	});

	it('handles contribute button clicks', async () => {
		await SUBSCRIBERS(subscriptions);

		contributeButtonEl.click();

		expect(subscriptions.showContributions.mock.calls).toEqual([[{
			isClosable: true,
			skus: ['basic', 'premium'],
		}]]);
	});
});
