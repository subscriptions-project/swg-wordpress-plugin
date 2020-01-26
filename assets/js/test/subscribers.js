import '../subscribers';

const SUBSCRIBERS = self.SWG[0];

describe('subscribers', () => {
	let metaEl;
	let articleEl;
	let buttonEl;
	let subscriptions;

	beforeEach(() => {
		subscriptions = {
			setOnPaymentResponse: callback => {
				const response = {
					complete: () => Promise.resolve(),
				};
				callback(response);
			},
			getEntitlements: () => Promise.resolve({
				entitlements: [
					{
						products: ['basic', 'premium'],
					},
				],
			}),
			showOffers: jest.fn(),
		};

		metaEl = document.createElement('meta');
		metaEl.setAttribute('name', 'subscriptions-product-id');
		metaEl.setAttribute('content', 'premium');
		document.body.appendChild(metaEl);

		articleEl = document.createElement('article');
		document.body.appendChild(articleEl);

		buttonEl = document.createElement('div');
		buttonEl.classList.add('swg-button');
		document.body.appendChild(buttonEl);
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

	it('handles button clicks', async () => {
		await SUBSCRIBERS(subscriptions);

		buttonEl.click();

		expect(subscriptions.showOffers.mock.calls).toEqual([[{
			isClosable: true,
			skus: [],
		}]]);
	});
});
