import { MatchTree } from './MatchTree'

export function fourTeamMatchTree() {
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
  return MatchTree.deserialize({ rounds: matches })
}
