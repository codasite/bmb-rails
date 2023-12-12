import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import PlayBracketPage from './PlayBracketPage'
import { MatchTree } from '../../shared/models/MatchTree'
import '@testing-library/jest-dom/jest-globals'
import { MatchTreeStorage } from './MatchTreeStorage'

global.fetch = jest.fn(() =>
  Promise.resolve({
    json: () => Promise.resolve(),
    ok: true,
  } as Response)
)
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
  test('click add to apparel button', async () => {
    const matches = [
      [
        {
          id: 9,
          roundIndex: 0,
          matchIndex: 0,
          team1: { id: 17, name: 'Team 1' },
          team2: { id: 18, name: 'Team 2' },
          team1Wins: true,
        },
        {
          id: 10,
          roundIndex: 0,
          matchIndex: 1,
          team1: { id: 19, name: 'Team 3' },
          team2: { id: 20, name: 'Team 4' },
          team1Wins: true,
        },
      ],
      [{ roundIndex: 1, matchIndex: 1, team2Wins: true }],
    ]
    const matchTree = MatchTree.deserialize({ rounds: matches })
    const matchTreeStorage = new MatchTreeStorage(
      'loadStoredPicks',
      'wpbb_play_data_'
    )
    matchTreeStorage.storeMatchTree(matchTree, 1)
    const { asFragment } = render(<PlayBracketPage bracket={{ id: 1 }} />)
    expect(screen.getByText('Add to Apparel')).toBeEnabled()
    const location = window.location
    delete window.location
    window.location = { assign: jest.fn() as any } as Location
    await userEvent.click(screen.getByText('Add to Apparel'))
    window.location = location
    expect(
      screen.getByText('Just a moment while we generate your bracket.')
    ).toBeVisible()
  })
})
