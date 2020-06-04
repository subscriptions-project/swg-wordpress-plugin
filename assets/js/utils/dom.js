/**
 * Selects a single DOM element.
 * @param {string} selector
 * @return {Element}
 */
export const $ = selector => document.querySelector(selector);

/**
 * Selects multiple DOM elements.
 * @param {string} selector
 * @return {Element[]}
 */
export const $$ = selector => [...document.querySelectorAll(selector)];
