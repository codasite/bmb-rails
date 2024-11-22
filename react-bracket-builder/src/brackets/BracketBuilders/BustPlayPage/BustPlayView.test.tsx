// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { render } from '@testing-library/react'
import { BustPlayView } from './BustPlayView'
import { fourTeamMatchTree } from '../../shared/models/MatchTreeFakeFactory'
import {
  MatchTreeContext1,
  MatchTreeContext2,
} from '../../shared/context/context'

describe('BustPlayView', () => {
  test('renders page correctly', () => {
    const noop = () => {}
    const { asFragment } = render(
      <MatchTreeContext1.Provider
        value={{ matchTree: fourTeamMatchTree(), setMatchTree: noop }}
      >
        <MatchTreeContext2.Provider value={{ matchTree: fourTeamMatchTree() }}>
          <BustPlayView
            busterTree={fourTeamMatchTree()}
            busteeDisplayName={'test name'}
            busteeThumbnail={'test'}
            buttonText={'button text'}
          />
        </MatchTreeContext2.Provider>
      </MatchTreeContext1.Provider>
    )
    expect(asFragment()).toMatchSnapshot()
  })
})
