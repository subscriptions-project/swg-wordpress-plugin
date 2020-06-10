/**
 * Returns true if a given experiment is on.
 * @param {string} experiment to check.
 * @return {boolean}
 */
export function experimentIsOn(experiment) {
	const regex =
		new RegExp('swg.wp.experiments=[\\w,^&]*' + experiment);
	return regex.test(location.hash);
}
