import { act, fireEvent, render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import PlayBuilderPage from './PlayBuilderPage'
import { MatchTree } from '../../shared/models/MatchTree'
import '@testing-library/jest-dom/jest-globals'
import { PlayStorage } from '../../shared/storages/PlayStorage'
import { bracketApi, MatchPick, MatchRes, PlayRes } from '../../shared'
import { jest } from '@jest/globals'
import { bracketResFactory } from '../../shared/api/types/bracketFactory'
global.wpbb_app_obj = {
  bracketProductArchiveUrl: '#',
  myPlayHistoryUrl: '#',
}

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
  test('should render submit picks button when user is logged in', () => {
    const { asFragment } = render(
      <PlayBuilderPage
        bracket={bracketResFactory({ isPrintable: true, isOpen: true })}
        isUserLoggedIn={true}
        myPlayHistoryUrl=""
        loginUrl=""
        bracketProductArchiveUrl=""
      />
    )
    const fragment = asFragment()
    expect(fragment).toMatchSnapshot()
    expect(screen.getByText('Submit Picks')).toBeEnabled()
  })
  test('should render register modal when user is not logged in', () => {
    const { asFragment } = render(
      <PlayBuilderPage
        bracket={bracketResFactory({ isPrintable: true, isOpen: true })}
        isUserLoggedIn={false}
        myPlayHistoryUrl=""
        loginUrl=""
        bracketProductArchiveUrl=""
      />
    )
    expect(asFragment()).toMatchSnapshot()
    expect(
      screen.getByText('Sign in or register to submit your picks!')
    ).toBeVisible()
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
      { roundIndex: 1, matchIndex: 0 },
    ]
    const picks: MatchPick[] = [
      { roundIndex: 0, matchIndex: 0, winningTeamId: 17 },
      { roundIndex: 0, matchIndex: 1, winningTeamId: 19 },
      { roundIndex: 1, matchIndex: 0, winningTeamId: 19 },
    ]

    expect(window.location.search).not.toContain('loadStoredPicks=true')
    expect(sessionStorage.getItem('wpbb_play_data_1')).toBeFalsy()
    const { asFragment } = render(
      <PlayBuilderPage
        matchTree={MatchTree.fromPicks(
          { numTeams: 4, matches: matches },
          picks
        )}
        bracket={bracketResFactory({ numTeams: 4, matches: matches })}
        isUserLoggedIn={true}
        myPlayHistoryUrl=""
        loginUrl=""
        bracketProductArchiveUrl=""
      />
    )
    const fragment = asFragment()
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
      <PlayBuilderPage
        bracket={bracketResFactory({ numTeams: 4, matches: matches })}
        isUserLoggedIn={true}
        myPlayHistoryUrl=""
        loginUrl=""
        bracketProductArchiveUrl=""
      />
    )
    const sessionFragment = asFragmentSession()
    expect(fragment).toMatchSnapshot()
    expect(fragment).toEqual(sessionFragment)

    // Cleanup search params otherwise the following test fails for some reason
    const url = new URL(window.location.href)
    url.search = ''
    window.history.replaceState({}, document.title, url)
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
        timezoneType: 1,
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
    render(
      <PlayBuilderPage
        matchTree={matchTree}
        bracket={bracketResFactory({ id: 1, isPrintable: true })}
        bracketProductArchiveUrl="#"
        myPlayHistoryUrl="#"
        loginUrl=""
        isUserLoggedIn={true}
      />
    )
    expect(screen.getByText('Add to Apparel')).toBeEnabled()
    const location = window.location
    delete window.location
    window.location = { assign: jest.fn() as any } as Location
    await userEvent.click(screen.getByText('Add to Apparel'))
    window.location = location
    expect(screen.getByText('Generating your bracket...')).toBeVisible()
    expect(createPlayMock).toHaveBeenCalled()
  })

  test('should update existing play when bracket is voting and user submit picks for second round', () => {
    // Four team bracket is voting and live round is second round
    const matches = [
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
      { roundIndex: 1, matchIndex: 0 },
    ]
    const bracket = bracketResFactory({
      matches,
      numTeams: 4,
      isVoting: true,
      liveRoundIndex: 1,
    })

    // User's play picked Team 1 and Team 3 for first round
    const play: PlayRes = {
      id: 1,
      bracketId: bracket.id,
      picks: [
        { roundIndex: 0, matchIndex: 0, winningTeamId: 17 },
        { roundIndex: 0, matchIndex: 1, winningTeamId: 19 },
      ],
      author: 1,
      authorDisplayName: 'author',
      publishedDate: {
        date: 'date',
        timezoneType: 1,
        timezone: 'timezone',
      },
      title: 'Test Play',
      status: 'published',
    }
    // Results were actually Team 2 and Team 3
    bracket.results = [
      { roundIndex: 0, matchIndex: 0, winningTeamId: 18 },
      { roundIndex: 0, matchIndex: 1, winningTeamId: 19 },
    ]

    const { asFragment } = render(
      <PlayBuilderPage
        bracket={bracket}
        isUserLoggedIn={true}
        myPlayHistoryUrl=""
        loginUrl=""
        bracketProductArchiveUrl=""
        play={play}
      />
    )
    // Team 2 and Team 3 should be highlighted in the first round from results
    expect(asFragment()).toMatchSnapshot()
    // Submit picks button should be disabled
    expect(screen.getByRole('button', { name: 'Submit Picks' })).toBeDisabled()
    // I click on Team 3 to win second round
    expect(screen.getByTestId('team-slot-round-1-match-0-right'))
    fireEvent.click(screen.getByTestId('team-slot-round-1-match-0-right'))
    // Submit picks button should be enabled
    expect(screen.getByRole('button', { name: 'Submit Picks' })).toBeEnabled()
    // Click on submit picks
    fireEvent.click(screen.getByRole('button', { name: 'Submit Picks' }))
    // bracketApi.updatePicks should be called with the updated picks which is Team 3 for second round
    expect(bracketApi.updatePlay).toHaveBeenCalledWith(play.bracketId, {
      picks: [
        { matchIndex: 0, roundIndex: 0, winningTeamId: 18 },
        { matchIndex: 1, roundIndex: 0, winningTeamId: 19 },
        { matchIndex: 0, roundIndex: 1, winningTeamId: 19 },
      ],
    })
  })
})
