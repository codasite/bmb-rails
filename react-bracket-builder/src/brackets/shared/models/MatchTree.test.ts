import { MatchTree } from './MatchTree'
import { MatchPick } from '../api/types/bracket'
import { describe, expect, test } from '@jest/globals'
import { WildcardPlacement } from './WildcardPlacement'
import { Team } from './Team'
import { MatchNode } from './operations/MatchNode'
import { Round } from './Round'
import { linkNodes } from './operations/LinkNodes'

describe('MatchTree', () => {
  test('testing create match tree from a number of teams and wildcard placement', () => {
    const numTeams = 8
    const wildcardPlacement = WildcardPlacement.Top
    const matchTree = MatchTree.fromNumTeams(numTeams, wildcardPlacement)

    expect(matchTree).not.toBeNull()
    const rounds = matchTree?.rounds
    expect(rounds).not.toBeNull()
    expect(rounds?.length).toBe(3)
    const round1 = rounds?.[0]
    expect(round1).not.toBeNull()
    expect(round1?.matches.length).toBe(4)
    expect(round1?.matches[0]).not.toBeNull()
    expect(round1?.matches[1]).not.toBeNull()
    expect(round1?.matches[2]).not.toBeNull()
    expect(round1?.matches[3]).not.toBeNull()
    const round2 = rounds?.[1]
    expect(round2).not.toBeNull()
    expect(round2?.matches.length).toBe(2)
    expect(round2?.matches[0]).not.toBeNull()
    expect(round2?.matches[1]).not.toBeNull()
    const round3 = rounds?.[2]
    expect(round3).not.toBeNull()
    expect(round3?.matches.length).toBe(1)
    expect(round3?.matches[0]).not.toBeNull()
  })

  test('testing create match tree from nested array of matches', () => {
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

    expect(matchTree).not.toBeNull()
    const rounds = matchTree?.rounds
    expect(rounds).not.toBeNull()
    expect(rounds?.length).toBe(2)
    const round1 = rounds?.[0]
    expect(round1).not.toBeNull()
    expect(round1?.matches.length).toBe(2)
    expect(round1?.matches[0]?.getTeam1()?.name).toBe('Team 1')
    expect(round1?.matches[0]?.getTeam2()?.name).toBe('Team 2')
    expect(round1?.matches[1]?.getTeam1()?.name).toBe('Team 3')
    expect(round1?.matches[1]?.getTeam2()?.name).toBe('Team 4')
    const round2 = rounds?.[1]
    expect(round2).not.toBeNull()
    expect(round2?.matches.length).toBe(1)
    expect(round2?.matches[0]?.getTeam1()).toBeNull()
    expect(round2?.matches[0]?.getTeam2()).toBeNull()
  })

  test('testing create match tree from a flat array of matches', () => {
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
      {
        id: 13,
        roundIndex: 0,
        matchIndex: 4,
        team1: { id: 25, name: 'Team 9' },
        team2: { id: 26, name: 'Team 10' },
      },
      {
        id: 14,
        roundIndex: 0,
        matchIndex: 5,
        team1: { id: 27, name: 'Team 11' },
        team2: { id: 28, name: 'Team 12' },
      },
      {
        id: 15,
        roundIndex: 0,
        matchIndex: 6,
        team1: { id: 29, name: 'Team 13' },
        team2: { id: 30, name: 'Team 14' },
      },
      {
        id: 16,
        roundIndex: 0,
        matchIndex: 7,
        team1: { id: 31, name: 'Team 15' },
        team2: { id: 32, name: 'Team 16' },
      },
    ]
    const matchTree = MatchTree.fromMatchRes({ numTeams: 16, matches })

    expect(matchTree).not.toBeNull()
    expect(matchTree?.rounds.length).toBe(4)
    expect(matchTree?.rounds[0].matches.length).toBe(8)
    expect(matchTree?.rounds[1].matches.length).toBe(4)
    expect(matchTree?.rounds[2].matches.length).toBe(2)
    expect(matchTree?.rounds[3].matches.length).toBe(1)
    expect(matchTree?.rounds[0].matches[0]?.getTeam1()?.name).toBe('Team 1')
    expect(matchTree?.rounds[0].matches[0]?.getTeam2()?.name).toBe('Team 2')
    expect(matchTree?.rounds[0].matches[1]?.getTeam1()?.name).toBe('Team 3')
    expect(matchTree?.rounds[0].matches[1]?.getTeam2()?.name).toBe('Team 4')
    expect(matchTree?.rounds[0].matches[2]?.getTeam1()?.name).toBe('Team 5')
    expect(matchTree?.rounds[0].matches[2]?.getTeam2()?.name).toBe('Team 6')
    expect(matchTree?.rounds[0].matches[3]?.getTeam1()?.name).toBe('Team 7')
    expect(matchTree?.rounds[0].matches[3]?.getTeam2()?.name).toBe('Team 8')
    expect(matchTree?.rounds[0].matches[4]?.getTeam1()?.name).toBe('Team 9')
    expect(matchTree?.rounds[0].matches[4]?.getTeam2()?.name).toBe('Team 10')
    expect(matchTree?.rounds[0].matches[5]?.getTeam1()?.name).toBe('Team 11')
    expect(matchTree?.rounds[0].matches[5]?.getTeam2()?.name).toBe('Team 12')
    expect(matchTree?.rounds[0].matches[6]?.getTeam1()?.name).toBe('Team 13')
    expect(matchTree?.rounds[0].matches[6]?.getTeam2()?.name).toBe('Team 14')
    expect(matchTree?.rounds[0].matches[7]?.getTeam1()?.name).toBe('Team 15')
    expect(matchTree?.rounds[0].matches[7]?.getTeam2()?.name).toBe('Team 16')
    expect(matchTree?.rounds[1].matches[0]?.getTeam1()).toBeNull()
    expect(matchTree?.rounds[1].matches[0]?.getTeam2()).toBeNull()
    expect(matchTree?.rounds[1].matches[1]?.getTeam1()).toBeNull()
    expect(matchTree?.rounds[1].matches[1]?.getTeam2()).toBeNull()
    expect(matchTree?.rounds[1].matches[2]?.getTeam1()).toBeNull()
    expect(matchTree?.rounds[1].matches[2]?.getTeam2()).toBeNull()
    expect(matchTree?.rounds[1].matches[3]?.getTeam1()).toBeNull()
    expect(matchTree?.rounds[1].matches[3]?.getTeam2()).toBeNull()
    expect(matchTree?.rounds[2].matches[0]?.getTeam1()).toBeNull()
    expect(matchTree?.rounds[2].matches[0]?.getTeam2()).toBeNull()
    expect(matchTree?.rounds[2].matches[1]?.getTeam1()).toBeNull()
    expect(matchTree?.rounds[2].matches[1]?.getTeam2()).toBeNull()
    expect(matchTree?.rounds[3].matches[0]?.getTeam1()).toBeNull()
    expect(matchTree?.rounds[3].matches[0]?.getTeam2()).toBeNull()
  })

  test('testing allPicked true', () => {
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
    expect(matchTree?.allPicked()).toBe(true)
  })

  test('testing allPicked false', () => {
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
      [{ roundIndex: 1, matchIndex: 1 }],
    ]

    const matchTree = MatchTree.deserialize({ rounds: matches })
    expect(matchTree?.allPicked()).toBe(false)
  })

  test('testing advanceTeam', () => {
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
    matchTree?.advanceTeam(0, 1, true)
    expect(matchTree?.rounds[0].matches[1]?.getWinner()?.id).toBe(19)
    expect(matchTree?.rounds[1].matches[0]?.getTeam2()?.id).toBe(19)
  })
  test('testing advanceTeam with picked tree', () => {
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
          team2Wins: true,
        },
      ],
      [{ roundIndex: 1, matchIndex: 1, team2Wins: true }],
    ]

    const matchTree = MatchTree.deserialize({ rounds: matches })
    expect(matchTree?.rounds[1].matches[0]?.getWinner()?.id).toBe(20)

    matchTree?.advanceTeam(0, 1, true)

    expect(matchTree?.rounds[0].matches[1]?.team1Wins).toBe(true)
    expect(matchTree?.rounds[0].matches[1]?.team2Wins).toBe(false)
    expect(matchTree?.rounds[0].matches[1]?.getWinner()?.id).toBe(19)
  })

  test('testing MatchTree serialize', () => {
    const team1 = new Team('Team 1')
    const team2 = new Team('Team 2')
    const team3 = new Team('Team 3')
    const team4 = new Team('Team 4')
    const team5 = new Team('Team 5')
    const team6 = new Team('Team 6')
    const team7 = new Team('Team 7')
    const team8 = new Team('Team 8')

    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 2,
          team1: team1,
          team2: team2,
          team1Wins: true,
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 1,
          depth: 2,
          team1: team3,
          team2: team4,
          team2Wins: true,
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 2,
          depth: 2,
          team1: team5,
          team2: team6,
          team1Wins: true,
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 3,
          depth: 2,
          team1: team7,
          team2: team8,
          team2Wins: true,
        }),
      ]),
      new Round(1, 1, [
        new MatchNode({
          roundIndex: 1,
          matchIndex: 0,
          depth: 1,
          team1Wins: true,
        }),
        new MatchNode({
          roundIndex: 1,
          matchIndex: 1,
          depth: 1,
          team2Wins: true,
        }),
      ]),
      new Round(2, 0, [
        new MatchNode({
          roundIndex: 2,
          matchIndex: 0,
          depth: 0,
          team1Wins: true,
        }),
      ]),
    ]

    const tree = new MatchTree(rounds, WildcardPlacement.Top)
    const serialized = tree?.serialize()
    expect(serialized).toMatchSnapshot()
  })

  test('testing from match picks', () => {
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
    ]
    const picks: MatchPick[] = [
      { roundIndex: 0, matchIndex: 0, winningTeamId: 17 },
      { roundIndex: 0, matchIndex: 1, winningTeamId: 20 },
      { roundIndex: 0, matchIndex: 2, winningTeamId: 21 },
      { roundIndex: 0, matchIndex: 3, winningTeamId: 24 },
      { roundIndex: 1, matchIndex: 0, winningTeamId: 17 },
      { roundIndex: 1, matchIndex: 1, winningTeamId: 21 },
      { roundIndex: 2, matchIndex: 0, winningTeamId: 17 },
    ]

    const matchTree = MatchTree.fromPicks({ numTeams: 8, matches }, picks)

    expect(matchTree).not.toBeNull()
    expect(matchTree?.rounds.length).toBe(3)
    const round1 = matchTree?.rounds[0]
    expect(round1).not.toBeNull()
    expect(round1?.matches.length).toBe(4)
    expect(round1?.matches[0]?.getTeam1()?.id).toBe(17)
    expect(round1?.matches[0]?.getTeam2()?.id).toBe(18)
    expect(round1?.matches[1]?.getTeam1()?.id).toBe(19)
    expect(round1?.matches[1]?.getTeam2()?.id).toBe(20)
    expect(round1?.matches[2]?.getTeam1()?.id).toBe(21)
    expect(round1?.matches[2]?.getTeam2()?.id).toBe(22)
    expect(round1?.matches[3]?.getTeam1()?.id).toBe(23)
    expect(round1?.matches[3]?.getTeam2()?.id).toBe(24)
    const round2 = matchTree?.rounds[1]
    expect(round2).not.toBeNull()
    expect(round2?.matches.length).toBe(2)
    expect(round2?.matches[0]?.getTeam1()?.id).toBe(17)
    expect(round2?.matches[0]?.getTeam2()?.id).toBe(20)
    expect(round2?.matches[1]?.getTeam1()?.id).toBe(21)
    expect(round2?.matches[1]?.getTeam2()?.id).toBe(24)
    const round3 = matchTree?.rounds[2]
    expect(round3).not.toBeNull()
    expect(round3?.matches.length).toBe(1)
    expect(round3?.matches[0]?.getTeam1()?.id).toBe(17)
    expect(round3?.matches[0]?.getTeam2()?.id).toBe(21)
    expect(round3?.matches[0]?.getWinner()?.id).toBe(17)
  })

  test('testing to match req', () => {
    const team1 = new Team('Team 1')
    const team2 = new Team('Team 2')
    const team3 = new Team('Team 3')
    const team4 = new Team('Team 4')
    const team5 = new Team('Team 5')
    const team6 = new Team('Team 6')
    const team7 = new Team('Team 7')
    const team8 = new Team('Team 8')

    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 2,
          team1: team1,
          team2: team2,
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 1,
          depth: 2,
          team1: team3,
          team2: team4,
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 2,
          depth: 2,
          team1: team5,
          team2: team6,
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 3,
          depth: 2,
          team1: team7,
          team2: team8,
        }),
      ]),
      new Round(1, 1, [
        new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 1 }),
        new MatchNode({ roundIndex: 1, matchIndex: 1, depth: 1 }),
      ]),
      new Round(2, 0, [
        new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds
    const req = tree?.toMatchReq()

    expect(req).toMatchSnapshot()
  })

  test('testing to match req with wildcards', () => {
    const team1 = new Team('Team 1')
    const team2 = new Team('Team 2')
    const team3 = new Team('Team 3')
    const team4 = new Team('Team 4')
    const team5 = new Team('Team 5')
    const team6 = new Team('Team 6')

    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 2,
          team1: team1,
          team2: team2,
        }),
        null,
        new MatchNode({
          roundIndex: 0,
          matchIndex: 2,
          depth: 2,
          team1: team3,
          team2: team4,
        }),
        null,
      ]),
      new Round(1, 1, [
        new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 1, team2: team5 }),
        new MatchNode({ roundIndex: 1, matchIndex: 1, depth: 1, team2: team6 }),
      ]),
      new Round(2, 0, [
        new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds
    const req = tree?.toMatchReq()

    expect(req).toMatchSnapshot()
  })

  test('testing to match picks', () => {
    const team1 = new Team('Team 1', 1)
    const team2 = new Team('Team 2', 2)
    const team3 = new Team('Team 3', 3)
    const team4 = new Team('Team 4', 4)
    const team5 = new Team('Team 5', 5)
    const team6 = new Team('Team 6', 6)

    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 2,
          team1Wins: true,
          team1: team1,
          team2: team2,
        }),
        null,
        new MatchNode({
          roundIndex: 0,
          matchIndex: 2,
          depth: 2,
          team2Wins: true,
          team1: team3,
          team2: team4,
        }),
        null,
      ]),
      new Round(1, 1, [
        new MatchNode({
          roundIndex: 1,
          matchIndex: 0,
          depth: 1,
          team1Wins: true,
          team2: team5,
        }),
        new MatchNode({
          roundIndex: 1,
          matchIndex: 1,
          depth: 1,
          team1Wins: true,
          team2: team6,
        }),
      ]),
      new Round(2, 0, [
        new MatchNode({
          roundIndex: 2,
          matchIndex: 0,
          depth: 0,
          team1Wins: true,
        }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds
    const picks = tree?.toMatchPicks()

    expect(picks).toMatchSnapshot()
  })

  test('testing getNumTeams', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 1,
          team1: new Team('Team 1'),
          team2: new Team('Team 2'),
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 1,
          depth: 1,
          team1: new Team('Team 3'),
          team2: new Team('Team 4'),
        }),
      ]),
      new Round(1, 1, [
        new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds

    expect(tree?.getNumTeams()).toBe(4)
  })

  test('testing getNumTeams with null teams', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({ roundIndex: 0, matchIndex: 0, depth: 1 }),
        new MatchNode({ roundIndex: 0, matchIndex: 1, depth: 1 }),
      ]),
      new Round(1, 1, [
        new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds

    expect(tree?.getNumTeams()).toBe(4)
  })

  test('testing getNumTeams with wildcards', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 2,
          team1: new Team('Team 1'),
          team2: new Team('Team 2'),
        }),
        null,
        new MatchNode({
          roundIndex: 0,
          matchIndex: 2,
          depth: 2,
          team1: new Team('Team 3'),
          team2: new Team('Team 4'),
        }),
        null,
      ]),
      new Round(1, 1, [
        new MatchNode({
          roundIndex: 1,
          matchIndex: 0,
          depth: 1,
          team2: new Team('Team 5'),
        }),
        new MatchNode({
          roundIndex: 1,
          matchIndex: 1,
          depth: 1,
          team2: new Team('Team 6'),
        }),
      ]),
      new Round(2, 0, [
        new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds
    expect(tree?.getNumTeams()).toBe(6)
  })

  test('testing getNumTeams from fromNumTeams', () => {
    expect(MatchTree.fromNumTeams(2)?.getNumTeams()).toBe(2)
    expect(MatchTree.fromNumTeams(4)?.getNumTeams()).toBe(4)
    expect(MatchTree.fromNumTeams(8)?.getNumTeams()).toBe(8)
    expect(MatchTree.fromNumTeams(12)?.getNumTeams()).toBe(12)
    expect(MatchTree.fromNumTeams(20)?.getNumTeams()).toBe(20)
    expect(MatchTree.fromNumTeams(36)?.getNumTeams()).toBe(36)
    expect(MatchTree.fromNumTeams(64)?.getNumTeams()).toBe(64)
  })

  test('testing allTeamsAdded true', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 1,
          team1: new Team('Team 1'),
          team2: new Team('Team 2'),
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 1,
          depth: 1,
          team1: new Team('Team 3'),
          team2: new Team('Team 4'),
        }),
      ]),
      new Round(1, 1, [
        new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds

    expect(tree?.allTeamsAdded()).toBe(true)
  })

  test('testing allTeamsAdded false', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 1,
          team1: new Team('Team 1'),
          team2: new Team('Team 2'),
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 1,
          depth: 1,
          team1: new Team('Team 3'),
        }),
      ]),
      new Round(1, 1, [
        new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds

    expect(tree?.allTeamsAdded()).toBe(false)
  })

  test('testing allTeamsAdded with wildcards true', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 2,
          team1: new Team('Team 1'),
          team2: new Team('Team 2'),
        }),
        null,
        new MatchNode({
          roundIndex: 0,
          matchIndex: 2,
          depth: 2,
          team1: new Team('Team 3'),
          team2: new Team('Team 4'),
        }),
        null,
      ]),
      new Round(1, 1, [
        new MatchNode({
          roundIndex: 1,
          matchIndex: 0,
          depth: 1,
          team2: new Team('Team 5'),
        }),
        new MatchNode({
          roundIndex: 1,
          matchIndex: 1,
          depth: 1,
          team2: new Team('Team 6'),
        }),
      ]),
      new Round(2, 0, [
        new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds
    expect(tree?.allTeamsAdded()).toBe(true)
  })

  test('testing allTeamsAdded with wildcards false', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 2,
          team1: new Team('Team 1'),
          team2: new Team('Team 2'),
        }),
        null,
        new MatchNode({
          roundIndex: 0,
          matchIndex: 2,
          depth: 2,
          team1: new Team('Team 3'),
        }),
        null,
      ]),
      new Round(1, 1, [
        new MatchNode({
          roundIndex: 1,
          matchIndex: 0,
          depth: 1,
          team2: new Team('Team 5'),
        }),
        new MatchNode({
          roundIndex: 1,
          matchIndex: 1,
          depth: 1,
          team2: new Team('Team 6'),
        }),
      ]),
      new Round(2, 0, [
        new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds
    expect(tree?.allTeamsAdded()).toBe(false)
  })

  test('testing allTeamsAdded with empty team names', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({
          roundIndex: 0,
          matchIndex: 0,
          depth: 1,
          team1: new Team(''),
          team2: new Team('Team 2'),
        }),
        new MatchNode({
          roundIndex: 0,
          matchIndex: 1,
          depth: 1,
          team1: new Team('Team 3'),
          team2: new Team('Team 4'),
        }),
      ]),
      new Round(1, 1, [
        new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 0 }),
      ]),
    ]

    linkNodes(rounds)

    const tree = new MatchTree()
    tree.rounds = rounds

    expect(tree?.allTeamsAdded()).toBe(false)
  })

  test('testing getWildcardPlacement', () => {
    expect(
      MatchTree.fromNumTeams(
        8,
        WildcardPlacement.Bottom
      )?.getWildcardPlacement()
    ).toBe(WildcardPlacement.Bottom)
    expect(
      MatchTree.fromNumTeams(8, WildcardPlacement.Top)?.getWildcardPlacement()
    ).toBe(WildcardPlacement.Top)
    expect(
      MatchTree.fromNumTeams(
        8,
        WildcardPlacement.Center
      )?.getWildcardPlacement()
    ).toBe(WildcardPlacement.Center)
    expect(
      MatchTree.fromNumTeams(8, WildcardPlacement.Split)?.getWildcardPlacement()
    ).toBe(WildcardPlacement.Split)
  })

  test('testing matchTree equals true', () => {
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
    const matchTree2 = MatchTree.deserialize({ rounds: matches })

    expect(matchTree?.equals(matchTree2)).toBe(true)
  })

  test('testing matchTree equals false', () => {
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
      [{ roundIndex: 1, matchIndex: 1 }],
    ]

    const matchTree = MatchTree.deserialize({ rounds: matches })
    const matchTree2 = MatchTree.deserialize({ rounds: matches })

    matchTree2?.advanceTeam(0, 1, true)

    expect(matchTree?.equals(matchTree2)).toBe(false)
  })
})
