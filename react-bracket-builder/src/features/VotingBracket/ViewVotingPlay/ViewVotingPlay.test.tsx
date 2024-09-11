import { fireEvent, render, screen } from '@testing-library/react'
import { PlayRes } from '../../../brackets/shared'
import ViewVotingPlay from './ViewVotingPlay'
import { BracketMetaContext } from '../../../brackets/shared/context/context'
import WrappedViewPlayPage from '../../../brackets/BracketBuilders/ViewPlayPage/ViewPlayRouter'
import userEvent from '@testing-library/user-event'

describe('ViewVotingPlay', () => {
  it('renders correctly', () => {
    const play: PlayRes = {
      id: 553,
      title: 'Chip Flavors',
      author: 10,
      status: 'publish',
      publishedDate: {
        date: '2024-09-11 19:39:43.000000',
        timezoneType: 1,
        timezone: '+00:00',
      },
      authorDisplayName: 'test teset',
      thumbnailUrl: '',
      url: 'http://localhost:10003/plays/fifxuj2b/',
      bracketId: 547,
      isBustable: false,
      bracket: {
        id: 547,
        title: 'Chip Flavors',
        author: 1,
        status: 'publish',
        publishedDate: {
          date: '2024-09-11 18:34:39.000000',
          timezoneType: 1,
          timezone: '+00:00',
        },
        authorDisplayName: 'Karl Molina',
        thumbnailUrl: '',
        url: 'http://localhost:10003/brackets/2ewxlajq/',
        numTeams: 11,
        wildcardPlacement: 0,
        month: '',
        year: '',
        matches: [
          {
            id: 221,
            roundIndex: 0,
            matchIndex: 0,
            team1: {
              id: 435,
              name: 'Classic',
            },
            team2: {
              id: 436,
              name: 'doritos',
            },
          },
          {
            id: 222,
            roundIndex: 0,
            matchIndex: 1,
            team1: {
              id: 437,
              name: 'cheetos',
            },
            team2: {
              id: 438,
              name: 'takis',
            },
          },
          {
            id: 223,
            roundIndex: 0,
            matchIndex: 4,
            team1: {
              id: 439,
              name: 'jalapeno',
            },
            team2: {
              id: 440,
              name: 'chili lime',
            },
          },
          {
            id: 224,
            roundIndex: 1,
            matchIndex: 1,
            team1: {
              id: 441,
              name: 'cheddar cheese',
            },
            team2: {
              id: 442,
              name: 'honey mustard',
            },
          },
          {
            id: 225,
            roundIndex: 1,
            matchIndex: 2,
            team2: {
              id: 443,
              name: 'sour cream/onion',
            },
          },
          {
            id: 226,
            roundIndex: 1,
            matchIndex: 3,
            team1: {
              id: 444,
              name: 'bbq',
            },
            team2: {
              id: 445,
              name: 'salt and Vinegar',
            },
          },
        ],
        mostPopularPicks: [
          {
            roundIndex: 0,
            matchIndex: 0,
            winningTeamId: 435,
            popularity: 0.6667,
          },
          {
            roundIndex: 0,
            matchIndex: 1,
            winningTeamId: 437,
            popularity: 0.6667,
          },
          {
            roundIndex: 0,
            matchIndex: 4,
            winningTeamId: 439,
            popularity: 0.6667,
          },
          {
            roundIndex: 1,
            matchIndex: 0,
            winningTeamId: 435,
            popularity: 0.6667,
          },
          {
            roundIndex: 1,
            matchIndex: 1,
            winningTeamId: 441,
            popularity: 0.6667,
          },
          {
            roundIndex: 1,
            matchIndex: 2,
            winningTeamId: 439,
            popularity: 0.6667,
          },
          {
            roundIndex: 1,
            matchIndex: 3,
            winningTeamId: 444,
            popularity: 0.6667,
          },
          {
            roundIndex: 2,
            matchIndex: 0,
            winningTeamId: 435,
            popularity: 1,
          },
          {
            roundIndex: 2,
            matchIndex: 1,
            winningTeamId: 439,
            popularity: 1,
          },
          {
            roundIndex: 3,
            matchIndex: 0,
            winningTeamId: 439,
            popularity: 0.5,
          },
        ],
        isOpen: true,
        isPrintable: false,
        fee: 0,
        isVoting: true,
        liveRoundIndex: 3,
      },
      picks: [
        {
          roundIndex: 2,
          matchIndex: 0,
          winningTeamId: 435,
        },
        {
          roundIndex: 2,
          matchIndex: 1,
          winningTeamId: 439,
        },
        {
          roundIndex: 3,
          matchIndex: 0,
          winningTeamId: 435,
        },
      ],
    }

    const { asFragment } = render(<WrappedViewPlayPage bracketPlay={play} />)
    expect(asFragment()).toMatchSnapshot()
    fireEvent.click(screen.getByTitle('Show popular picks'))
    expect(asFragment()).toMatchSnapshot()
  })
})
