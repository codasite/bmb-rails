// import jest
import { describe, expect, it } from '@jest/globals'
import { resetTeams, scrambleTeams } from './ScrambleTeams'
import { MatchTree } from '../MatchTree'

describe('ScrambleTeams', () => {
  it('should scramble the teams', () => {
    const matches = [
      [
        {
          id: 9,
          roundIndex: 0,
          matchIndex: 0,
          team1: { id: 17, name: 'Team 0' },
          team2: { id: 18, name: 'Team 1' },
        },
        {
          id: 10,
          roundIndex: 0,
          matchIndex: 1,
          team1: { id: 19, name: 'Team 2' },
          team2: { id: 20, name: 'Team 3' },
        },
      ],
      [{ roundIndex: 1, matchIndex: 1 }],
    ]

    const matchTree = MatchTree.deserialize({ rounds: matches })
    let indices = scrambleTeams(matchTree, [0, 1, 2, 3], () => 0.5)
    expect(matchTree.serialize()).toMatchSnapshot()
    expect(indices).toMatchSnapshot()
    indices = scrambleTeams(matchTree, indices, () => 0.7)
    expect(matchTree.serialize()).toMatchSnapshot()
    expect(indices).toMatchSnapshot()
    resetTeams(matchTree, indices)
    expect(matchTree.serialize()).toMatchSnapshot()
  })
  it('should throw an error if the number of teams does not match the number of scrambled indices', () => {
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
    expect(() =>
      scrambleTeams(matchTree, [], () => 0.5)
    ).toThrowErrorMatchingSnapshot()
  })
})
