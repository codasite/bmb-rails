// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { render } from '@testing-library/react'
import { fourTeamMatchTree } from '../../../shared/models/MatchTreeFakeFactory'
import { LandingPage } from './LandingPage'

describe('LandingPage', () => {
  test('renders page correctly', () => {
    const noop = () => {}
    const { asFragment } = render(
      <LandingPage matchTree={fourTeamMatchTree()} onStart={noop} />
    )
    expect(asFragment()).toMatchSnapshot()
  })
})
