// import jest
import { describe, expect, it } from '@jest/globals'
import { MatchTree } from '../MatchTree'
import { Team } from '../Team'
import { setTeams } from './SetTeams'

describe('SetTeams', () => {
  it('should set the teams', () => {
    const matches = [
      [
        {
          id: 9,
          roundIndex: 0,
          matchIndex: 0,
        },
        {
          id: 10,
          roundIndex: 0,
          matchIndex: 1,
        },
      ],
      [{ roundIndex: 1, matchIndex: 1 }],
    ]

    const matchTree = MatchTree.deserialize({ rounds: matches })
    setTeams(matchTree, [
      new Team('Team 1', 1),
      new Team('Team 2', 2),
      new Team('Team 3', 3),
      new Team('Team 4', 4),
    ])
    expect(matchTree.serialize()).toMatchSnapshot()
  })
  it('should throw error if wrong number of teams', () => {
    const matches = [
      [
        {
          id: 9,
          roundIndex: 0,
          matchIndex: 0,
        },
        {
          id: 10,
          roundIndex: 0,
          matchIndex: 1,
        },
      ],
      [{ roundIndex: 1, matchIndex: 1 }],
    ]

    const matchTree = MatchTree.deserialize({ rounds: matches })
    expect(() =>
      setTeams(matchTree, [
        new Team('Team 1', 1),
        new Team('Team 2', 2),
        new Team('Team 3', 3),
      ])
    ).toThrowErrorMatchingSnapshot()
  })
})
