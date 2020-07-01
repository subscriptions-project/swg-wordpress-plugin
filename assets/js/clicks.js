export function handleRightClicks() {
	let noContext = document.querySelector('.swg--paywall-prompt')

	noContext.addEventListener('contextmenu', e => {
		e.preventDefault();
	});
}