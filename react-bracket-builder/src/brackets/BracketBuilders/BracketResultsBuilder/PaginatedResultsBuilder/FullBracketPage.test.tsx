import React from 'react'
import { render } from '@testing-library/react'
import { FullBracketPage } from './FullBracketPage'
import { fourTeamMatchTree } from '../../../shared/models/MatchTreeFakeFactory'

describe('FullBracketPage', () => {
  test('renders page correctly', () => {
    const noop = () => {}
    const { asFragment } = render(
      <FullBracketPage
        matchTree={fourTeamMatchTree()}
        handleUpdatePicks={noop}
        onEditClick={noop}
      />
    )
    expect(asFragment()).toMatchSnapshot()
  })
})
