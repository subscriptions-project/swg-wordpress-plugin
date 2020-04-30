import { $ } from "./utils/dom";


/** Local storage key where SwG entitlements are cached. */
const CACHE_KEY = 'subscribewithgoogle-entitlements-cache';

/** Unlocks current page, if possible. */
export async function unlockPageMaybe() {
  const $article = $('article');
  if (!$article) {
    return;
  }

  const product = getProduct();
  if (!product) {
    return;
  }

  const entitled = await userIsEntitledToProduct(product);
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
 * @return {boolean}
 */
async function userIsEntitledToProduct(product) {
  if (cacheEntitlesUserToProduct(product)) {
    return true;
  }

  // Fetch and cache entitlements.
  const response = await fetchEntitlements();
  const products = extractProductsFromEntitlementsResponse(response);
  updateCache(products);
  return products.indexOf(product) > -1
}

/**
 * Returns true if the cache entitles user to a given product via cache.
 * @param {string} product
 * @return {boolean}
 */
function cacheEntitlesUserToProduct(product) {
  try {
    const cache = JSON.parse(localStorage[CACHE_KEY]);
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
    localStorage[CACHE_KEY] = JSON.stringify(cache);
  } catch (err) {
    // Sometimes privacy is more important than convenience.
  }
}

/**
 * Returns entitlements response.
 * @return {Promise<*>}
 */
async function fetchEntitlements() {
  console.log('📡 Fetching entitlements');
  const url =
          SubscribeWithGoogleWpGlobals.API_BASE_URL +
          '/entitlements';
  return fetch(url)
    .then(response => response.json());
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
    for (const product of entitlement.products) {
      products.push(product);
    }
  }
  return products;
}
