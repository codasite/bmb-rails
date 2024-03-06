import React from 'react'
import BracketResultsBuilder from './BracketResultsBuilder'
import { fourTeamMatchTree } from '../../shared/models/MatchTreeFakeFactory'
import { render } from '@testing-library/react'

describe('BracketResultsBuilder', () => {
  test('renders page correctly', () => {
    const noop = () => {}
    const { asFragment } = render(
      <BracketResultsBuilder
        matchTree={fourTeamMatchTree()}
        setMatchTree={noop}
        bracket={{
          numTeams: 4,
          matches: [],
          results: [],
          id: 0,
        }}
        setBracketMeta={noop}
        darkMode={false}
        setDarkMode={noop}
      />
    )
    expect(asFragment()).toMatchSnapshot()
  })
})
