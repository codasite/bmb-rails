import {
	MatchTree,
	getNumRounds,
	getNullMatches,
	getMatchRepr,
	fillInEmptyMatches,
} from './MatchTree';
import { describe, test, expect, it } from '@jest/globals';

describe('MatchTree', () => {

	test('test create match tree from nested array of matches', () => {
		const matches = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
			],
			[
				{ roundIndex: 1, matchIndex: 1 },
			],
		]

		const matchTree = MatchTree.fromMatchRepr(matches)

		expect(matchTree).not.toBeNull()
		const rounds = matchTree?.rounds
		expect(rounds).not.toBeNull()
		expect(rounds?.length).toBe(2)
		const round1 = rounds?.[0]
		expect(round1).not.toBeNull()
		expect(round1?.matches.length).toBe(2)
		expect(round1?.matches[0]?.team1?.name).toBe("Team 1")
		expect(round1?.matches[0]?.team2?.name).toBe("Team 2")
		expect(round1?.matches[1]?.team1?.name).toBe("Team 3")
		expect(round1?.matches[1]?.team2?.name).toBe("Team 4")
		const round2 = rounds?.[1]
		expect(round2).not.toBeNull()
		expect(round2?.matches.length).toBe(1)
		expect(round2?.matches[0]?.team1).toBeNull()
		expect(round2?.matches[0]?.team2).toBeNull()
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
		expect(matchTree?.rounds[0].matches[0]?.team1?.name).toBe("Team 1")
		expect(matchTree?.rounds[0].matches[0]?.team2?.name).toBe("Team 2")
		expect(matchTree?.rounds[0].matches[1]?.team1?.name).toBe("Team 3")
		expect(matchTree?.rounds[0].matches[1]?.team2?.name).toBe("Team 4")
		expect(matchTree?.rounds[0].matches[2]?.team1?.name).toBe("Team 5")
		expect(matchTree?.rounds[0].matches[2]?.team2?.name).toBe("Team 6")
		expect(matchTree?.rounds[0].matches[3]?.team1?.name).toBe("Team 7")
		expect(matchTree?.rounds[0].matches[3]?.team2?.name).toBe("Team 8")
		expect(matchTree?.rounds[0].matches[4]?.team1?.name).toBe("Team 9")
		expect(matchTree?.rounds[0].matches[4]?.team2?.name).toBe("Team 10")
		expect(matchTree?.rounds[0].matches[5]?.team1?.name).toBe("Team 11")
		expect(matchTree?.rounds[0].matches[5]?.team2?.name).toBe("Team 12")
		expect(matchTree?.rounds[0].matches[6]?.team1?.name).toBe("Team 13")
		expect(matchTree?.rounds[0].matches[6]?.team2?.name).toBe("Team 14")
		expect(matchTree?.rounds[0].matches[7]?.team1?.name).toBe("Team 15")
		expect(matchTree?.rounds[0].matches[7]?.team2?.name).toBe("Team 16")
		expect(matchTree?.rounds[1].matches[0]?.team1).toBeNull()
		expect(matchTree?.rounds[1].matches[0]?.team2).toBeNull()
		expect(matchTree?.rounds[1].matches[1]?.team1).toBeNull()
		expect(matchTree?.rounds[1].matches[1]?.team2).toBeNull()
		expect(matchTree?.rounds[1].matches[2]?.team1).toBeNull()
		expect(matchTree?.rounds[1].matches[2]?.team2).toBeNull()
		expect(matchTree?.rounds[1].matches[3]?.team1).toBeNull()
		expect(matchTree?.rounds[1].matches[3]?.team2).toBeNull()
		expect(matchTree?.rounds[2].matches[0]?.team1).toBeNull()
		expect(matchTree?.rounds[2].matches[0]?.team2).toBeNull()
		expect(matchTree?.rounds[2].matches[1]?.team1).toBeNull()
		expect(matchTree?.rounds[2].matches[1]?.team2).toBeNull()
		expect(matchTree?.rounds[3].matches[0]?.team1).toBeNull()
		expect(matchTree?.rounds[3].matches[0]?.team2).toBeNull()
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

	test('testing get match parent', () => {
		const matchTree = MatchTree.fromMatchRes(8, [
			{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
			{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
			{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
			{ id: 12, roundIndex: 0, matchIndex: 3, team1: { id: 23, name: "Team 7" }, team2: { id: 24, name: "Team 8" } },
		])
		console.log('tree')
	})
})