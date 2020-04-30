module.exports = {
	rootDir: '../../',
	testMatch: [
		'<rootDir>/assets/**/__tests__/**/*.js',
		'<rootDir>/assets/**/test/*.js',
		'<rootDir>/assets/**/?(*.)test.js',
	],
	collectCoverageFrom: [ 'assets/js/**/*.{js,jsx}' ],
	coveragePathIgnorePatterns: [ '/node_modules/', '<rootDir>/build/' ],
	coverageReporters: [ 'lcovonly' ],
	coverageDirectory: '<rootDir>/coverage/js',
	globals: {
		SubscribeWithGoogleWpGlobals: {
			API_BASE_URL: '/api',
		},
	},
};
