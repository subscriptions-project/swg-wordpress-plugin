export function handleRightClicks() {
	let noContext = document.querySelector('.swg--paywall-prompt')

	if (noContext) {
		noContext.addEventListener('contextmenu', e => {
			e.preventDefault();
		});
	}
}