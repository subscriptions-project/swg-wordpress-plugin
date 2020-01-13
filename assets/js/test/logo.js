/**
 * Internal dependencies
 */
import Logo from '../logo';

describe( 'Logo', () => {
	it( 'renders correctly', () => {
		const result = Logo();
		expect( result ).toStrictEqual( '123.87' );
	} );
} );
