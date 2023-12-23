import { describe, expect, test } from '@jest/globals'
import { getNumRounds } from './GetNumRounds'
import { getNullMatches } from './GetNullMatches'
import { matchReprFromRes } from './MatchReprFromRes'
import { fillInEmptyMatches } from './FillInEmptyMatches'
import { Round } from '../Round'
import { MatchNode } from './MatchNode'
import { linkNodes } from './LinkNodes'
import { Team } from '../Team'
import { WildcardPlacement } from '../WildcardPlacement'
import { getWildcardRange } from './GetWildcardRange'
import { getFirstRoundMatches } from './GetFirstRoundMatches'
import { matchReprFromNumTeams } from './MatchReprFromNumTeams'

describe('MatchTree Utils', () => {
  test('testing getNumRounds', () => {
    expect(getNumRounds(2)).toBe(1)
    expect(getNumRounds(3)).toBe(2)
    expect(getNumRounds(4)).toBe(2)
    expect(getNumRounds(5)).toBe(3)
    expect(getNumRounds(6)).toBe(3)
    expect(getNumRounds(7)).toBe(3)
    expect(getNumRounds(8)).toBe(3)
    expect(getNumRounds(9)).toBe(4)
    expect(getNumRounds(10)).toBe(4)
    expect(getNumRounds(11)).toBe(4)
    expect(getNumRounds(12)).toBe(4)
    expect(getNumRounds(13)).toBe(4)
    expect(getNumRounds(14)).toBe(4)
    expect(getNumRounds(15)).toBe(4)
    expect(getNumRounds(16)).toBe(4)
  })

  test('testing getNullMatches', () => {
    const numRounds = 4

    const nullMatches = getNullMatches(numRounds)
    expect(nullMatches).toMatchSnapshot()
  })

  test('testing matchReprFromRes', () => {
    const numRounds = 2
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
    ]

    const matchRepr = matchReprFromRes(numRounds, matches)
    expect(matchRepr).toMatchSnapshot()
  })

  test('testing matchReprFromRes invalid round index', () => {
    const numRounds = 2
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
        roundIndex: 4,
        matchIndex: 1,
        team1: { id: 19, name: 'Team 3' },
        team2: { id: 20, name: 'Team 4' },
      },
    ]

    const t = () => {
      matchReprFromRes(numRounds, matches)
    }
    expect(t).toThrowError('Invalid round index 4 for match 10')
  })

  test('testing matchReprFromRes invalid match index', () => {
    const numRounds = 2
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
        roundIndex: 1,
        matchIndex: 8,
        team1: { id: 19, name: 'Team 3' },
        team2: { id: 20, name: 'Team 4' },
      },
    ]

    const t = () => {
      matchReprFromRes(numRounds, matches)
    }
    expect(t).toThrowError('Invalid match index 8 for match 10')
  })

  test('testing matchReprFromRes wildcards', () => {
    const numRounds = getNumRounds(6)
    const matches = [
      {
        id: 23,
        roundIndex: 0,
        matchIndex: 0,
        team1: { id: 45, name: 'asefe' },
        team2: { id: 46, name: 'fefe' },
      },
      {
        id: 24,
        roundIndex: 0,
        matchIndex: 2,
        team1: { id: 47, name: 'asef' },
        team2: { id: 48, name: 'feef' },
      },
      {
        id: 25,
        roundIndex: 1,
        matchIndex: 0,
        team1: null,
        team2: { id: 49, name: 'asfe' },
      },
      {
        id: 26,
        roundIndex: 1,
        matchIndex: 1,
        team1: null,
        team2: { id: 50, name: 'afsef' },
      },
    ]

    const matchRepr = matchReprFromRes(numRounds, matches)
    expect(matchRepr).toMatchSnapshot()
  })

  test('testing fillInEmptyMatches', () => {
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
      [null, null],
      [null],
    ]

    const filled = fillInEmptyMatches(matches)
    expect(filled).toMatchSnapshot()
  })

  test('testing fillInEmptyMatches', () => {
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
      [null, null],
      [null],
    ]

    const filled = fillInEmptyMatches(matches)
    expect(filled).toMatchSnapshot()
  })
  test('testing fillInEmptyMatches wildcards', () => {
    const matches = [
      [
        null,
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
        null,
      ],
      [null, null],
      [null],
    ]

    const filled = fillInEmptyMatches(matches)
    expect(filled).toMatchSnapshot()
  })

  test('testing linkNodes', () => {
    const rounds = [
      new Round(0, 2, [
        new MatchNode({ roundIndex: 0, matchIndex: 0, depth: 2 }),
        new MatchNode({ roundIndex: 0, matchIndex: 1, depth: 2 }),
        new MatchNode({ roundIndex: 0, matchIndex: 2, depth: 2 }),
        new MatchNode({ roundIndex: 0, matchIndex: 3, depth: 2 }),
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

    const r1m1 = rounds?.[0].matches[0]
    const r1m2 = rounds?.[0].matches[1]
    const r1m3 = rounds?.[0].matches[2]
    const r1m4 = rounds?.[0].matches[3]
    const r2m1 = rounds?.[1].matches[0]
    const r2m2 = rounds?.[1].matches[1]
    const r3m1 = rounds?.[2].matches[0]

    // expect(r3m1?.parent).toBeNull()
    expect(r2m1?.parent).toBe(r3m1)
    expect(r2m2?.parent).toBe(r3m1)
    expect(r1m1?.parent).toBe(r2m1)
    expect(r1m2?.parent).toBe(r2m1)
    expect(r1m3?.parent).toBe(r2m2)
    expect(r1m4?.parent).toBe(r2m2)

    expect(r3m1?.left).toBe(r2m1)
    expect(r3m1?.right).toBe(r2m2)
    expect(r2m1?.left).toBe(r1m1)
    expect(r2m1?.right).toBe(r1m2)
    expect(r2m2?.left).toBe(r1m3)
    expect(r2m2?.right).toBe(r1m4)

    expect(r1m1?.left).toBeNull()
    expect(r1m1?.right).toBeNull()
    expect(r1m2?.left).toBeNull()
    expect(r1m2?.right).toBeNull()
    expect(r1m3?.left).toBeNull()
    expect(r1m3?.right).toBeNull()
    expect(r1m4?.left).toBeNull()
    expect(r1m4?.right).toBeNull()
  })

  test('testing teams are linked', () => {
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

    linkNodes(rounds)

    const r1m1 = rounds?.[0].matches[0]
    const r1m2 = rounds?.[0].matches[1]
    const r1m3 = rounds?.[0].matches[2]
    const r1m4 = rounds?.[0].matches[3]
    const r2m1 = rounds?.[1].matches[0]
    const r2m2 = rounds?.[1].matches[1]
    const r3m1 = rounds?.[2].matches[0]

    expect(r1m1?.getWinner()).toBe(team1)
    expect(r1m2?.getWinner()).toBe(team4)
    expect(r1m3?.getWinner()).toBe(team5)
    expect(r1m4?.getWinner()).toBe(team8)

    expect(r2m1?.getTeam1()).toBe(team1)
    expect(r2m1?.getTeam2()).toBe(team4)
    expect(r2m1?.getWinner()).toBe(team1)

    expect(r2m2?.getTeam1()).toBe(team5)
    expect(r2m2?.getTeam2()).toBe(team8)
    expect(r2m2?.getWinner()).toBe(team8)

    expect(r3m1?.getTeam1()).toBe(team1)
    expect(r3m1?.getTeam2()).toBe(team8)
    expect(r3m1?.getWinner()).toBe(team1)
  })

  test('testing getWildcardRange top', () => {
    const start = 0
    const end = 8
    const wildcardPlacement = WildcardPlacement.Top
    const count = 4

    const range = getWildcardRange(start, end, count, wildcardPlacement)
    expect(range).toMatchSnapshot()
  })

  test('testing getWildcardRange bottom', () => {
    const start = 0
    const end = 8
    const wildcardPlacement = WildcardPlacement.Bottom
    const count = 4

    const range = getWildcardRange(start, end, count, wildcardPlacement)
    expect(range).toMatchSnapshot()
  })

  test('testing getWildcardRange center', () => {
    const start = 0
    const end = 8
    const wildcardPlacement = WildcardPlacement.Center
    const count = 4

    const range = getWildcardRange(start, end, count, wildcardPlacement)
    expect(range).toMatchSnapshot()
  })

  test('testing getWildcardRange split', () => {
    const start = 0
    const end = 8
    const wildcardPlacement = WildcardPlacement.Split
    const count = 4

    const range = getWildcardRange(start, end, count, wildcardPlacement)
    expect(range).toMatchSnapshot()
  })

  test('testing getFirstRoundMatches 8 teams', () => {
    const numTeams = 8

    const matches = getFirstRoundMatches(numTeams)
    expect(matches).toMatchSnapshot()
  })

  test('testing getFirstRoundMatches 12 teams top', () => {
    const numTeams = 12
    const wildcardPlacement = WildcardPlacement.Top

    const matches = getFirstRoundMatches(numTeams, wildcardPlacement)
    expect(matches).toMatchSnapshot()
  })

  test('testing getFirstRoundMatches 12 teams bottom', () => {
    const numTeams = 12
    const wildcardPlacement = WildcardPlacement.Bottom

    const matches = getFirstRoundMatches(numTeams, wildcardPlacement)
    expect(matches).toMatchSnapshot()
  })

  test('testing getFirstRoundMatches 12 teams center', () => {
    const numTeams = 12
    const wildcardPlacement = WildcardPlacement.Center

    const matches = getFirstRoundMatches(numTeams, wildcardPlacement)
    expect(matches).toMatchSnapshot()
  })

  test('testing getFirstRoundMatches 12 teams split', () => {
    const numTeams = 12
    const wildcardPlacement = WildcardPlacement.Split

    const matches = getFirstRoundMatches(numTeams, wildcardPlacement)
    expect(matches).toMatchSnapshot()
  })

  test('testing getMatchReprFromNumTeams 8 teams', () => {
    const numTeams = 8

    const matches = matchReprFromNumTeams(numTeams)
    expect(matches).toMatchSnapshot()
  })

  test('testing getMatchReprFromNumTeams 12 teams top', () => {
    const numTeams = 12
    const wildcardPlacement = WildcardPlacement.Top

    const matches = matchReprFromNumTeams(numTeams, wildcardPlacement)
    expect(matches).toMatchSnapshot()
  })
})
