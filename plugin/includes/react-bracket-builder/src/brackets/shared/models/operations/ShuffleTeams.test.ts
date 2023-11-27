// import jest
import { describe, it } from '@jest/globals'
import { shuffleTeams } from './ShuffleTeams'
import { MatchTree } from '../MatchTree'

describe('ShuffleTeams', () => {
  it('should shuffle the teams', () => {
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
    shuffleTeams(matchTree)
  })
})
