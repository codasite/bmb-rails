import {
	MatchTree,
	Round,
	Team,
	MatchNode,
	getNumRounds,
	getNullMatches,
	getMatchRepr,
	fillInEmptyMatches,
	WildcardPlacement,
	getWildcardRange,
	getFirstRoundMatches,
	matchReprFromNumTeams,
	linkNodes,
	// linkTeams,
} from './MatchTree';
import {
	MatchPicksRes,
	MatchRes,
	MatchReq
} from '../api/types/bracket';

import { describe, test, expect, it } from '@jest/globals';

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
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
			],
			[
				{ roundIndex: 1, matchIndex: 1 },
			],
		]

		const matchTree = MatchTree.deserialize(matches)

		expect(matchTree).not.toBeNull()
		const rounds = matchTree?.rounds
		expect(rounds).not.toBeNull()
		expect(rounds?.length).toBe(2)
		const round1 = rounds?.[0]
		expect(round1).not.toBeNull()
		expect(round1?.matches.length).toBe(2)
		expect(round1?.matches[0]?.getTeam1()?.name).toBe("Team 1")
		expect(round1?.matches[0]?.getTeam2()?.name).toBe("Team 2")
		expect(round1?.matches[1]?.getTeam1()?.name).toBe("Team 3")
		expect(round1?.matches[1]?.getTeam2()?.name).toBe("Team 4")
		const round2 = rounds?.[1]
		expect(round2).not.toBeNull()
		expect(round2?.matches.length).toBe(1)
		expect(round2?.matches[0]?.getTeam1()).toBeNull()
		expect(round2?.matches[0]?.getTeam2()).toBeNull()
	})


	test('testing create match tree from a flat array of matches', () => {
		const matches = [
			{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
			{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
			{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
			{ id: 12, roundIndex: 0, matchIndex: 3, team1: { id: 23, name: "Team 7" }, team2: { id: 24, name: "Team 8" } },
			{ id: 13, roundIndex: 0, matchIndex: 4, team1: { id: 25, name: "Team 9" }, team2: { id: 26, name: "Team 10" } },
			{ id: 14, roundIndex: 0, matchIndex: 5, team1: { id: 27, name: "Team 11" }, team2: { id: 28, name: "Team 12" } },
			{ id: 15, roundIndex: 0, matchIndex: 6, team1: { id: 29, name: "Team 13" }, team2: { id: 30, name: "Team 14" } },
			{ id: 16, roundIndex: 0, matchIndex: 7, team1: { id: 31, name: "Team 15" }, team2: { id: 32, name: "Team 16" } },
		]
		const matchTree = MatchTree.fromMatchRes(16, matches)

		expect(matchTree).not.toBeNull()
		expect(matchTree?.rounds.length).toBe(4)
		expect(matchTree?.rounds[0].matches.length).toBe(8)
		expect(matchTree?.rounds[1].matches.length).toBe(4)
		expect(matchTree?.rounds[2].matches.length).toBe(2)
		expect(matchTree?.rounds[3].matches.length).toBe(1)
		expect(matchTree?.rounds[0].matches[0]?.getTeam1()?.name).toBe("Team 1")
		expect(matchTree?.rounds[0].matches[0]?.getTeam2()?.name).toBe("Team 2")
		expect(matchTree?.rounds[0].matches[1]?.getTeam1()?.name).toBe("Team 3")
		expect(matchTree?.rounds[0].matches[1]?.getTeam2()?.name).toBe("Team 4")
		expect(matchTree?.rounds[0].matches[2]?.getTeam1()?.name).toBe("Team 5")
		expect(matchTree?.rounds[0].matches[2]?.getTeam2()?.name).toBe("Team 6")
		expect(matchTree?.rounds[0].matches[3]?.getTeam1()?.name).toBe("Team 7")
		expect(matchTree?.rounds[0].matches[3]?.getTeam2()?.name).toBe("Team 8")
		expect(matchTree?.rounds[0].matches[4]?.getTeam1()?.name).toBe("Team 9")
		expect(matchTree?.rounds[0].matches[4]?.getTeam2()?.name).toBe("Team 10")
		expect(matchTree?.rounds[0].matches[5]?.getTeam1()?.name).toBe("Team 11")
		expect(matchTree?.rounds[0].matches[5]?.getTeam2()?.name).toBe("Team 12")
		expect(matchTree?.rounds[0].matches[6]?.getTeam1()?.name).toBe("Team 13")
		expect(matchTree?.rounds[0].matches[6]?.getTeam2()?.name).toBe("Team 14")
		expect(matchTree?.rounds[0].matches[7]?.getTeam1()?.name).toBe("Team 15")
		expect(matchTree?.rounds[0].matches[7]?.getTeam2()?.name).toBe("Team 16")
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

	test('testing isComplete true', () => {
		const matches = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" }, team1Wins: true },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" }, team1Wins: true },
			],
			[
				{ roundIndex: 1, matchIndex: 1, team2Wins: true },
			],
		]

		const matchTree = MatchTree.deserialize(matches)
		expect(matchTree?.isComplete()).toBe(true)
	})

	test('testing isComplete false', () => {
		const matches = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" }, team1Wins: true },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" }, team1Wins: true },
			],
			[
				{ roundIndex: 1, matchIndex: 1 },
			],
		]

		const matchTree = MatchTree.deserialize(matches)
		expect(matchTree?.isComplete()).toBe(false)
	})

	test('testing advanceTeam', () => {
		const matches = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } }
			],
			[
				{ roundIndex: 1, matchIndex: 1 },
			],
		]

		const matchTree = MatchTree.deserialize(matches)
		matchTree?.advanceTeam(0, 1, true)
		expect(matchTree?.rounds[0].matches[1]?.getWinner()?.id).toBe(19)
		expect(matchTree?.rounds[1].matches[0]?.getTeam2()?.id).toBe(19)
	})

	test('testing MatchTree serialize', () => {
		const team1 = new Team("Team 1")
		const team2 = new Team("Team 2")
		const team3 = new Team("Team 3")
		const team4 = new Team("Team 4")
		const team5 = new Team("Team 5")
		const team6 = new Team("Team 6")
		const team7 = new Team("Team 7")
		const team8 = new Team("Team 8")

		const rounds = [
			new Round(0, 2, [
				new MatchNode({ roundIndex: 0, matchIndex: 0, depth: 2, team1: team1, team2: team2, team1Wins: true }),
				new MatchNode({ roundIndex: 0, matchIndex: 1, depth: 2, team1: team3, team2: team4, team2Wins: true }),
				new MatchNode({ roundIndex: 0, matchIndex: 2, depth: 2, team1: team5, team2: team6, team1Wins: true }),
				new MatchNode({ roundIndex: 0, matchIndex: 3, depth: 2, team1: team7, team2: team8, team2Wins: true }),

			]),
			new Round(1, 1, [
				new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 1, team1Wins: true }),
				new MatchNode({ roundIndex: 1, matchIndex: 1, depth: 1, team2Wins: true }),
			]),
			new Round(2, 0, [
				new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0, team1Wins: true }),
			])
		]

		linkNodes(rounds)

		const expected = [
			[
				{ roundIndex: 0, matchIndex: 0, team1: { name: "Team 1" }, team2: { name: "Team 2" }, team1Wins: true },
				{ roundIndex: 0, matchIndex: 1, team1: { name: "Team 3" }, team2: { name: "Team 4" }, team2Wins: true },
				{ roundIndex: 0, matchIndex: 2, team1: { name: "Team 5" }, team2: { name: "Team 6" }, team1Wins: true },
				{ roundIndex: 0, matchIndex: 3, team1: { name: "Team 7" }, team2: { name: "Team 8" }, team2Wins: true },
			],
			[
				{ roundIndex: 1, matchIndex: 0, team1Wins: true },
				{ roundIndex: 1, matchIndex: 1, team2Wins: true },
			],
			[
				{ roundIndex: 2, matchIndex: 0, team1Wins: true },
			]
		]

		const tree = new MatchTree()
		tree.rounds = rounds
		const serialized = tree?.serialize()
		expect(serialized).toEqual(expected)
	})

	test('testing from match picks', () => {
		const matches = [
			{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
			{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
			{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
			{ id: 12, roundIndex: 0, matchIndex: 3, team1: { id: 23, name: "Team 7" }, team2: { id: 24, name: "Team 8" } },
		]
		const picks: MatchPicksRes[] = [
			{ roundIndex: 0, matchIndex: 0, winningTeamId: 17 },
			{ roundIndex: 0, matchIndex: 1, winningTeamId: 20 },
			{ roundIndex: 0, matchIndex: 2, winningTeamId: 21 },
			{ roundIndex: 0, matchIndex: 3, winningTeamId: 24 },
			{ roundIndex: 1, matchIndex: 0, winningTeamId: 17 },
			{ roundIndex: 1, matchIndex: 1, winningTeamId: 21 },
			{ roundIndex: 2, matchIndex: 0, winningTeamId: 17 },
		]

		const matchTree = MatchTree.fromPicks(8, matches, picks)

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

	test('testing to match res', () => {
		const team1 = new Team("Team 1")
		const team2 = new Team("Team 2")
		const team3 = new Team("Team 3")
		const team4 = new Team("Team 4")
		const team5 = new Team("Team 5")
		const team6 = new Team("Team 6")
		const team7 = new Team("Team 7")
		const team8 = new Team("Team 8")

		const rounds = [
			new Round(0, 2, [
				new MatchNode({ roundIndex: 0, matchIndex: 0, depth: 2, team1: team1, team2: team2 }),
				new MatchNode({ roundIndex: 0, matchIndex: 1, depth: 2, team1: team3, team2: team4 }),
				new MatchNode({ roundIndex: 0, matchIndex: 2, depth: 2, team1: team5, team2: team6 }),
				new MatchNode({ roundIndex: 0, matchIndex: 3, depth: 2, team1: team7, team2: team8 }),

			]),
			new Round(1, 1, [
				new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 1 }),
				new MatchNode({ roundIndex: 1, matchIndex: 1, depth: 1 }),
			]),
			new Round(2, 0, [
				new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0 }),
			])
		]

		linkNodes(rounds)

		const expected: MatchReq[] = [
			{ roundIndex: 0, matchIndex: 0, team1: { name: "Team 1" }, team2: { name: "Team 2" } },
			{ roundIndex: 0, matchIndex: 1, team1: { name: "Team 3" }, team2: { name: "Team 4" } },
			{ roundIndex: 0, matchIndex: 2, team1: { name: "Team 5" }, team2: { name: "Team 6" } },
			{ roundIndex: 0, matchIndex: 3, team1: { name: "Team 7" }, team2: { name: "Team 8" } },
		]

		const tree = new MatchTree()
		tree.rounds = rounds
		const req = tree?.toMatchReq()

		expect(req).toEqual(expected)
	})

	test('testing to match req with wildcards', () => {
		const team1 = new Team("Team 1")
		const team2 = new Team("Team 2")
		const team3 = new Team("Team 3")
		const team4 = new Team("Team 4")
		const team5 = new Team("Team 5")
		const team6 = new Team("Team 6")

		const rounds = [
			new Round(0, 2, [
				new MatchNode({ roundIndex: 0, matchIndex: 0, depth: 2, team1: team1, team2: team2 }),
				null,
				new MatchNode({ roundIndex: 0, matchIndex: 2, depth: 2, team1: team3, team2: team4 }),
				null,
			]),
			new Round(1, 1, [
				new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 1, team2: team5 }),
				new MatchNode({ roundIndex: 1, matchIndex: 1, depth: 1, team2: team6 }),
			]),
			new Round(2, 0, [
				new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0 }),
			])
		]

		linkNodes(rounds)

		const expected: MatchReq[] = [
			{ roundIndex: 1, matchIndex: 0, team2: { name: "Team 5" } },
			{ roundIndex: 1, matchIndex: 1, team2: { name: "Team 6" } },
			{ roundIndex: 0, matchIndex: 0, team1: { name: "Team 1" }, team2: { name: "Team 2" } },
			{ roundIndex: 0, matchIndex: 2, team1: { name: "Team 3" }, team2: { name: "Team 4" } },
		]

		const tree = new MatchTree()
		tree.rounds = rounds
		const req = tree?.toMatchReq()

		expect(req).toEqual(expected)
	})
});

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

		const expected = [
			[null, null, null, null, null, null, null, null],
			[null, null, null, null],
			[null, null],
			[null],
		]

		const nullMatches = getNullMatches(numRounds)
		expect(nullMatches).toEqual(expected)
	})

	test('testing getMatchRepr', () => {
		const numRounds = 2
		const matches = [
			{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
			{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
		]

		const expected = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
			],
			[
				{ roundIndex: 1, matchIndex: 0 },
			]
		]

		const matchRepr = getMatchRepr(numRounds, matches)
		expect(matchRepr).toEqual(expected)
	})

	test('testing getMatchRepr invalid round index', () => {
		const numRounds = 2
		const matches = [
			{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
			{ id: 10, roundIndex: 4, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
		]

		const t = () => {
			const matchRepr = getMatchRepr(numRounds, matches)
		}
		expect(t).toThrowError('Invalid round index 4 for match 10')
	})

	test('testing getMatchRepr invalid match index', () => {
		const numRounds = 2
		const matches = [
			{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
			{ id: 10, roundIndex: 1, matchIndex: 8, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
		]

		const t = () => {
			const matchRepr = getMatchRepr(numRounds, matches)
		}
		expect(t).toThrowError('Invalid match index 8 for match 10')
	})

	test('testing fillInEmptyMatches', () => {
		const matches = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
				{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
				{ id: 12, roundIndex: 0, matchIndex: 3, team1: { id: 23, name: "Team 7" }, team2: { id: 24, name: "Team 8" } },
			],
			[null, null],
			[null],
		]

		const expected = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
				{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
				{ id: 12, roundIndex: 0, matchIndex: 3, team1: { id: 23, name: "Team 7" }, team2: { id: 24, name: "Team 8" } },
			],
			[
				{ roundIndex: 1, matchIndex: 0 },
				{ roundIndex: 1, matchIndex: 1 },
			],
			[
				{ roundIndex: 2, matchIndex: 0 },
			]
		]

		const filled = fillInEmptyMatches(matches)
		expect(filled).toEqual(expected)
	})

	test('testing fillInEmptyMatches', () => {
		const matches =
			[[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
				{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
				{ id: 12, roundIndex: 0, matchIndex: 3, team1: { id: 23, name: "Team 7" }, team2: { id: 24, name: "Team 8" } },
			],
			[null, null],
			[null],
			]

		const expected = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
				{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
				{ id: 12, roundIndex: 0, matchIndex: 3, team1: { id: 23, name: "Team 7" }, team2: { id: 24, name: "Team 8" } },
			],
			[
				{ roundIndex: 1, matchIndex: 0 },
				{ roundIndex: 1, matchIndex: 1 },
			],
			[
				{ roundIndex: 2, matchIndex: 0 },
			]
		]

		const filled = fillInEmptyMatches(matches)
		expect(filled).toEqual(expected)
	})
	test('testing fillInEmptyMatches wildcards', () => {
		const matches =
			[[
				null,
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
				{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
				null,
			],
			[null, null],
			[null],
			]

		const expected = [
			[
				null,
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
				{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
				null,
			],
			[
				{ roundIndex: 1, matchIndex: 0 },
				{ roundIndex: 1, matchIndex: 1 },
			],
			[
				{ roundIndex: 2, matchIndex: 0 },
			]
		]

		const filled = fillInEmptyMatches(matches)
		expect(filled).toEqual(expected)
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
			])
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
		const team1 = new Team("Team 1")
		const team2 = new Team("Team 2")
		const team3 = new Team("Team 3")
		const team4 = new Team("Team 4")
		const team5 = new Team("Team 5")
		const team6 = new Team("Team 6")
		const team7 = new Team("Team 7")
		const team8 = new Team("Team 8")

		const rounds = [
			new Round(0, 2, [
				new MatchNode({ roundIndex: 0, matchIndex: 0, depth: 2, team1: team1, team2: team2, team1Wins: true }),
				new MatchNode({ roundIndex: 0, matchIndex: 1, depth: 2, team1: team3, team2: team4, team2Wins: true }),
				new MatchNode({ roundIndex: 0, matchIndex: 2, depth: 2, team1: team5, team2: team6, team1Wins: true }),
				new MatchNode({ roundIndex: 0, matchIndex: 3, depth: 2, team1: team7, team2: team8, team2Wins: true }),

			]),
			new Round(1, 1, [
				new MatchNode({ roundIndex: 1, matchIndex: 0, depth: 1, team1Wins: true }),
				new MatchNode({ roundIndex: 1, matchIndex: 1, depth: 1, team2Wins: true }),
			]),
			new Round(2, 0, [
				new MatchNode({ roundIndex: 2, matchIndex: 0, depth: 0, team1Wins: true }),
			])
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

		const expected = [{
			min: 0,
			max: 4,
		}]

		const range = getWildcardRange(start, end, count, wildcardPlacement)
		expect(range).toEqual(expected)
	})

	test('testing getWildcardRange bottom', () => {
		const start = 0
		const end = 8
		const wildcardPlacement = WildcardPlacement.Bottom
		const count = 4

		const expected = [{
			min: 4,
			max: 8,
		}]

		const range = getWildcardRange(start, end, count, wildcardPlacement)
		expect(range).toEqual(expected)
	})

	test('testing getWildcardRange center', () => {
		const start = 0
		const end = 8
		const wildcardPlacement = WildcardPlacement.Center
		const count = 4

		const expected = [{
			min: 2,
			max: 6,
		}]

		const range = getWildcardRange(start, end, count, wildcardPlacement)
		expect(range).toEqual(expected)
	})

	test('testing getWildcardRange split', () => {
		const start = 0
		const end = 8
		const wildcardPlacement = WildcardPlacement.Split
		const count = 4

		const expected = [{
			min: 0,
			max: 2,
		}, {
			min: 6,
			max: 8,
		}]

		const range = getWildcardRange(start, end, count, wildcardPlacement)
		expect(range).toEqual(expected)
	})

	test('testing getFirstRoundMatches 8 teams', () => {
		const numTeams = 8

		const expected = [
			{ roundIndex: 0, matchIndex: 0 },
			{ roundIndex: 0, matchIndex: 1 },
			{ roundIndex: 0, matchIndex: 2 },
			{ roundIndex: 0, matchIndex: 3 },
		]

		const matches = getFirstRoundMatches(numTeams)
		expect(matches).toEqual(expected)
	})

	test('testing getFirstRoundMatches 12 teams top', () => {
		const numTeams = 12
		const wildcardPlacement = WildcardPlacement.Top

		const expected = [
			{ roundIndex: 0, matchIndex: 0 },
			{ roundIndex: 0, matchIndex: 1 },
			null,
			null,
			{ roundIndex: 0, matchIndex: 4 },
			{ roundIndex: 0, matchIndex: 5 },
			null,
			null,
		]

		const matches = getFirstRoundMatches(numTeams, wildcardPlacement)
		expect(matches).toEqual(expected)
	})

	test('testing getFirstRoundMatches 12 teams bottom', () => {
		const numTeams = 12
		const wildcardPlacement = WildcardPlacement.Bottom

		const expected = [
			null,
			null,
			{ roundIndex: 0, matchIndex: 2 },
			{ roundIndex: 0, matchIndex: 3 },
			null,
			null,
			{ roundIndex: 0, matchIndex: 6 },
			{ roundIndex: 0, matchIndex: 7 },
		]

		const matches = getFirstRoundMatches(numTeams, wildcardPlacement)
		expect(matches).toEqual(expected)
	})

	test('testing getFirstRoundMatches 12 teams center', () => {
		const numTeams = 12
		const wildcardPlacement = WildcardPlacement.Center

		const expected = [
			null,
			{ roundIndex: 0, matchIndex: 1 },
			{ roundIndex: 0, matchIndex: 2 },
			null,
			null,
			{ roundIndex: 0, matchIndex: 5 },
			{ roundIndex: 0, matchIndex: 6 },
			null,
		]

		const matches = getFirstRoundMatches(numTeams, wildcardPlacement)
		expect(matches).toEqual(expected)
	})

	test('testing getFirstRoundMatches 12 teams split', () => {
		const numTeams = 12
		const wildcardPlacement = WildcardPlacement.Split

		const expected = [
			{ roundIndex: 0, matchIndex: 0 },
			null,
			null,
			{ roundIndex: 0, matchIndex: 3 },
			{ roundIndex: 0, matchIndex: 4 },
			null,
			null,
			{ roundIndex: 0, matchIndex: 7 },
		]

		const matches = getFirstRoundMatches(numTeams, wildcardPlacement)
		expect(matches).toEqual(expected)
	})

	test('testing getMatchReprFromNumTeams 8 teams', () => {
		const numTeams = 8

		const expected = [
			[
				{ roundIndex: 0, matchIndex: 0 },
				{ roundIndex: 0, matchIndex: 1 },
				{ roundIndex: 0, matchIndex: 2 },
				{ roundIndex: 0, matchIndex: 3 },
			],
			[
				{ roundIndex: 1, matchIndex: 0 },
				{ roundIndex: 1, matchIndex: 1 },
			],
			[
				{ roundIndex: 2, matchIndex: 0 },
			],
		]

		const matches = matchReprFromNumTeams(numTeams)
		expect(matches).toEqual(expected)
	})

	test('testing getMatchReprFromNumTeams 12 teams top', () => {
		const numTeams = 12
		const wildcardPlacement = WildcardPlacement.Top

		const expected = [
			[
				{ roundIndex: 0, matchIndex: 0 },
				{ roundIndex: 0, matchIndex: 1 },
				null,
				null,
				{ roundIndex: 0, matchIndex: 4 },
				{ roundIndex: 0, matchIndex: 5 },
				null,
				null,
			],
			[
				{ roundIndex: 1, matchIndex: 0 },
				{ roundIndex: 1, matchIndex: 1 },
				{ roundIndex: 1, matchIndex: 2 },
				{ roundIndex: 1, matchIndex: 3 },
			],
			[
				{ roundIndex: 2, matchIndex: 0 },
				{ roundIndex: 2, matchIndex: 1 },
			],
			[
				{ roundIndex: 3, matchIndex: 0 },
			],
		]

		const matches = matchReprFromNumTeams(numTeams, wildcardPlacement)
		expect(matches).toEqual(expected)
	})
})