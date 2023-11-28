// import jest
import { describe, expect, it } from '@jest/globals'
import { MatchTree } from '../MatchTree'

describe('GetTeams', () => {
  it('should get the teams', () => {
    const matches = [
      [
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
      ],
      [{ roundIndex: 1, matchIndex: 1 }],
    ]

    const matchTree = MatchTree.deserialize({ rounds: matches })
    expect(matchTree.teams).toMatchSnapshot()
  })
})
