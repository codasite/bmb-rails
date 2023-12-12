// MyComponent.test.js
import React from 'react'
import { render } from '@testing-library/react'
import PlayBracketPage from './PlayBracketPage'
import { MatchTree } from '../../shared/models/MatchTree'
import { MatchTreeStorage } from './MatchTreeStorage'

describe('PlayBracketPage', () => {
  test('renders PlayBracketPage correctly', () => {
    const { asFragment } = render(
      <PlayBracketPage matchTree={MatchTree.fromNumTeams(10)} />
    )
    expect(asFragment()).toMatchSnapshot()
  })
  test('renders PlayBracketPage from sessionStorage', () => {
    const matchTreeStorage = new MatchTreeStorage(
      'loadStoredPicks',
      'wpbb_play_data_'
    )
    matchTreeStorage.storeMatchTree(MatchTree.fromNumTeams(10), 1)
    expect(window.location.search).toContain('loadStoredPicks=true')
    expect(sessionStorage.getItem('wpbb_play_data_1')).toBeTruthy()
    const { asFragment: asFragmentSession } = render(
      <PlayBracketPage bracket={{ id: 1 }} />
    )
    const { asFragment } = render(
      <PlayBracketPage matchTree={MatchTree.fromNumTeams(10)} />
    )
    expect(asFragmentSession()).toEqual(asFragment())
  })
})
