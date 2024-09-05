import { WildcardPlacement } from '../../models/WildcardPlacement'
import { BracketRes } from './bracket'

export const bracketResFactory = (
  override: Partial<BracketRes>
): BracketRes => {
  return {
    id: 1,
    title: 'Test Bracket',
    status: 'live',
    month: 'March',
    year: '2022',
    numTeams: 8,
    wildcardPlacement: WildcardPlacement.Top,
    author: 1,
    authorDisplayName: 'Test User',
    publishedDate: {
      date: '2024-08-28 16:46:09.000000',
      timezone_type: 1,
      timezone: '+00:00',
    },
    matches: [
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
      {
        id: 11,
        roundIndex: 0,
        matchIndex: 2,
        team1: { id: 21, name: 'Team 5' },
        team2: { id: 22, name: 'Team 6' },
      },
      {
        id: 12,
        roundIndex: 0,
        matchIndex: 3,
        team1: { id: 23, name: 'Team 7' },
        team2: { id: 24, name: 'Team 8' },
      },
    ],
    ...override,
  }
}
