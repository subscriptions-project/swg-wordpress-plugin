<?php

/** Mocks a function from the AMP WP plugin. */
function is_amp_endpoint() {
	global $is_amp;
	return $is_amp;
}
