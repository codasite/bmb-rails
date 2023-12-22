import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import PlayBuilderPage from './PlayBuilderPage'
import { MatchTree } from '../../shared/models/MatchTree'
import '@testing-library/jest-dom/jest-globals'
import { PlayStorage } from '../../shared/storages/PlayStorage'
import { MatchPick, MatchRes } from '../../shared/api/types/bracket'
import { jest } from '@jest/globals'
import { bracketApi } from '../../shared/api/bracketApi'

jest.mock('react-lineto', () => {
  return {
    __esModule: true,
    default: () => {
      return <div />
    },
    SteppedLineTo: () => {
      return <div className={'mock-react-lineto'} />
    },
  }
})

jest.mock('../../shared/api/bracketApi')

describe('PlayBuilderPage', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })
  test('renders PlayBuilderPage correctly', () => {
    const { asFragment } = render(
      <PlayBuilderPage matchTree={MatchTree.fromNumTeams(10)} />
    )
    expect(asFragment()).toMatchSnapshot()
  })
  test('renders PlayBuilderPage from sessionStorage', () => {
    const playStorage = new PlayStorage('loadStoredPicks', 'wpbb_play_data_')
    const matches: MatchRes[] = [
      {
        id: 9,
        roundIndex: 0,
        matchIndex: 0,
        team1: { id: 17, name: 'Team 1' },
        team2: { id: 18, name: 'Team 2' },
      },
      {
        id: 10,
        roundIndex: 0,
        matchIndex: 1,
        team1: { id: 19, name: 'Team 3' },
        team2: { id: 20, name: 'Team 4' },
      },
      { roundIndex: 1, matchIndex: 1 },
    ]
    const picks: MatchPick[] = [
      { roundIndex: 0, matchIndex: 0, winningTeamId: 17 },
      { roundIndex: 0, matchIndex: 1, winningTeamId: 19 },
      { roundIndex: 1, matchIndex: 1, winningTeamId: 19 },
    ]

    playStorage.storePlay(
      {
        bracketId: 1,
        picks: picks,
      },
      1
    )
    expect(window.location.search).toContain('loadStoredPicks=true')
    expect(sessionStorage.getItem('wpbb_play_data_1')).toBeTruthy()
    const { asFragment: asFragmentSession } = render(
      <PlayBuilderPage bracket={{ id: 1, numTeams: 4, matches: matches }} />
    )
    const { asFragment } = render(
      <PlayBuilderPage matchTree={MatchTree.fromPicks(4, matches, picks)} />
    )
    expect(asFragmentSession()).toEqual(asFragment())
  })
  test('click add to apparel button', async () => {
    jest.mock('../../shared/storages/PlayStorage')
    PlayStorage.prototype.storePlay = jest.fn()
    const createPlayMock = bracketApi.createPlay as jest.MockedFunction<
      typeof bracketApi.createPlay
    >
    const playResMock = {
      bracketId: 1,
      id: 2,
      picks: [],
      title: 'title',
      url: 'url',
      status: 'status',
      author: 3,
      authorDisplayName: 'authorDisplayName',
      publishedDate: {
        date: 'date',
        timezone_type: 1,
        timezone: 'timezone',
      },
    }
    createPlayMock.mockResolvedValue(playResMock)

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
    const { asFragment } = render(
      <PlayBuilderPage
        matchTree={matchTree}
        bracket={{ id: 1 }}
        bracketProductArchiveUrl="#"
        myPlayHistoryUrl="#"
      />
    )
    expect(screen.getByText('Add to Apparel')).toBeEnabled()
    const location = window.location
    delete window.location
    window.location = { assign: jest.fn() as any } as Location
    await userEvent.click(screen.getByText('Add to Apparel'))
    window.location = location
    expect(screen.getByText('Generating your bracket...')).toBeVisible()
  })
})
