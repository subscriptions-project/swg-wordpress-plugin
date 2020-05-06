import { $ } from "./utils/dom";


/** Local storage key where SwG entitlements are cached. */
export const CACHE_KEY = 'subscribewithgoogle-entitlements-cache';

/**
 * Unlocks current page, if possible.
 * @param {*} swg SwG API
 */
export async function unlockPageMaybe(swg) {
  const $article = $('article');
  if (!$article) {
    return;
  }

  const product = getProduct();
  if (!product) {
    return;
  }

  const entitled = await userIsEntitledToProduct(product, swg);
  if (entitled) {
    $article.classList.add('swg--page-is-unlocked');
  } else {
    $article.classList.add('swg--page-is-locked');
  }
}

/**
 * Returns product for current page.
 * @return {string}
 */
function getProduct() {
  const metaEl = $('meta[name=subscriptions-product-id]');
  if (!metaEl) {
    return null;
  }
  const product = metaEl.getAttribute('content');
  return product;
}

/**
 * Returns true if user is entitled to a given product. 
 * @param {string} product
 * @param {*} swg SwG API
 * @return {boolean}
 */
async function userIsEntitledToProduct(product, swg) {
  if (cacheEntitlesUserToProduct(product)) {
    return true;
  }

  // Fetch and cache entitlements.
  const products = await fetchEntitlements(swg);
  updateCache(products);
  return products.indexOf(product) > -1;
}

/**
 * Returns true if the cache entitles user to a given product via cache.
 * @param {string} product
 * @return {boolean}
 */
function cacheEntitlesUserToProduct(product) {
  if (location.hash.includes('swg.wp.experiments=disablecache')) {
    console.log('👷 Disabling cache');
    return false;
  }

  try {
    const cache = JSON.parse(window.localStorage[CACHE_KEY]);
    if (cache.expiration < Date.now()) {
      console.log('⌛ Cache expired');
      return false;
    }
    const entitled = cache.products.indexOf(product) > -1;
    console.log(entitled ? '🎉 Cache unlocked page' : '😭 Cache did not unlock page');
    return entitled;
  } catch (err) {
    return false;
  }
}

/**
 * Updates caches with products the user is entitled to.
 * @param {string[]} products 
 */
function updateCache(products) {
  try {
    // 6 hours.
    const expiration = Date.now() + 1000 * 60 * 60 * 6;
    const cache = {
      expiration,
      products,
    };
    window.localStorage[CACHE_KEY] = JSON.stringify(cache);
  } catch (err) {
    // Sometimes privacy is more important than convenience.
  }
}

/**
 * Returns entitlements response.
 * @param {*} swg SwG API
 * @return {Promise<!Array<string>>}
 */
async function fetchEntitlements(swg) {
  return new Promise((resolve, reject) => {
    const resolveOnlyWithProducts = (products) => {
      if (products.length >= 1) {
        resolve(products);
      }
    };

    const productsWith1pCookie = fetchEntitledProductsWith1pCookie()
      .then(resolveOnlyWithProducts);
    const productsWith3pCookie = fetchEntitledProductsWith3pCookie(swg)
      .then(resolveOnlyWithProducts);

    // Return empty list by default.
    Promise
      .all([productsWith1pCookie, productsWith3pCookie])
      .then(() => void resolve([]));
  });
}

/**
 * Returns entitlements response using a 1st party cookie.
 * @return {Promise<!Array<string>>}
 */
async function fetchEntitledProductsWith1pCookie() {
  console.log('📡 Fetching entitlements with 1p cookie');
  const url =
    SubscribeWithGoogleWpGlobals.API_BASE_URL +
    '/entitlements';
  return window.fetch(url)
    .then(response => response.json())
    .catch(() => ({}))
    .then(extractProductsFromEntitlementsResponse);
}

/**
 * Returns entitlements response using a 3rd party cookie.
 * @param {*} swg SwG API
 * @return {Promise<!Array<string>>}
 */
async function fetchEntitledProductsWith3pCookie(swg) {
  console.log('📡 Fetching entitlements with 3p cookie');
  return swg.getEntitlements()
    .catch(() => ({}))
    .then(extractProductsFromEntitlementsResponse);
}

/**
 * Returns set of products from an entitlements response.
 * @param {*} response with entitlements
 * @return {!Array<string>} Products the user owns.
 */
function extractProductsFromEntitlementsResponse(response) {
  const products = [];
  const entitlements = response.entitlements || [];
  for (const entitlement of entitlements) {
    products.push(...entitlement.products);
  }
  return products;
}
