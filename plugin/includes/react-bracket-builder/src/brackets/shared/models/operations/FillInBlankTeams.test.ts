// import jest
import { describe, expect, it } from '@jest/globals'
import { MatchTree } from '../MatchTree'

describe('FillInBlankTeams', () => {
  it('should add the blank teams', () => {
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
    expect(matchTree.serialize()).toMatchSnapshot()
  })
})
