import Logo from '../logo';
import React from 'react';
import renderer from 'react-test-renderer';

it( 'renders correctly', () => {
  const logo = renderer
    .create(<Logo />)
    .toJSON();
  expect( logo ).toMatchSnapshot();
} );
