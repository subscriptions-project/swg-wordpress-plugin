import { removePaywallsForProductIds } from '../subscribers';

describe('paywalls', () => {
	const PRODUCT_IDS = new Set(['basic', 'premium']);

	let metaEl;
	let articleEl;

	beforeEach(() => {
		metaEl = document.createElement('meta');
		metaEl.setAttribute('name', 'subscriptions-product-id');
		metaEl.setAttribute('content', 'premium');
		document.body.appendChild(metaEl);

		articleEl = document.createElement('article');
		document.body.appendChild(articleEl);
	});

	afterEach(() => {
		metaEl.remove();
		articleEl.remove();
	});

	it('handles missing meta element', () => {
		metaEl.remove();
		removePaywallsForProductIds(PRODUCT_IDS);
	});

	it('handles mismatched product in meta element', () => {
		metaEl.setAttribute('content', 'exclusive');
		removePaywallsForProductIds(PRODUCT_IDS);
	});

	it('handles missing article element', () => {
		articleEl.remove();
		removePaywallsForProductIds(PRODUCT_IDS);
	});

	it('marks article as entitled', () => {
		removePaywallsForProductIds(PRODUCT_IDS);
		expect(articleEl.classList.contains('swg-entitled')).toBeTruthy();
	});
});
