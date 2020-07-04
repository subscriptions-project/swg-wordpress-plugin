import { $, $$ } from "./utils/dom";
import { experimentIsOn } from "./experiments";


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

  $article.classList.add('swg--page-is-locked');
  getFullPostIfUserIsEntitled($article);

}

/**
 * Fetches the full HTML content of the unlocked page and replaces the current content with it
 * 
 * @param {*} $article The page's Article element
 */
async function getFullPostIfUserIsEntitled($article) {
  let articleHtmlUrl = '/wp-json/wp/v2/posts/' + SubscribeWithGoogleWpGlobals.POST_ID;

  fetch(articleHtmlUrl)
    .then(response => {
      if (!response.ok) {
        throw Error(response.statusText);
      }
      return response;
    })
    .then(response => response.json())
    .then((data) => {
      let contentHtml = data;
      $('.entry-content').innerHTML = contentHtml;
      $article.classList.add('swg--entitlements-have-loaded')
    })
    .catch((err) => {
      $article.classList.add('swg--entitlements-have-loaded')
    })
}