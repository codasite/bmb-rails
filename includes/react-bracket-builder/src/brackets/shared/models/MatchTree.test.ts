import {
	MatchTree,
	getNumRounds,
	getNullMatchRounds,
	fillInMatches,
	addEmptyMatches,
} from './MatchTree';
import { describe, test, expect, it } from '@jest/globals';

describe('MatchTree', () => {

	test('testing create match tree from matches', () => {
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
		const matchTree = MatchTree.fromMatches(16, matches)
		console.log('tree')
		console.log(matchTree)

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

	test('testing getNullMatchRounds', () => {
		const numRounds = 4

		const expected = [
			[null, null, null, null, null, null, null, null],
			[null, null, null, null],
			[null, null],
			[null],
		]

		const nullMatches = getNullMatchRounds(numRounds)
		expect(nullMatches).toEqual(expected)
	})

	test('testing fillInMatches', () => {
		const nulls = [
			[null, null, null, null, null, null, null, null],
			[null, null, null, null],
			[null, null],
			[null],
		]

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

		const expected = [
			[
				{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
				{ id: 10, roundIndex: 0, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
				{ id: 11, roundIndex: 0, matchIndex: 2, team1: { id: 21, name: "Team 5" }, team2: { id: 22, name: "Team 6" } },
				{ id: 12, roundIndex: 0, matchIndex: 3, team1: { id: 23, name: "Team 7" }, team2: { id: 24, name: "Team 8" } },
				{ id: 13, roundIndex: 0, matchIndex: 4, team1: { id: 25, name: "Team 9" }, team2: { id: 26, name: "Team 10" } },
				{ id: 14, roundIndex: 0, matchIndex: 5, team1: { id: 27, name: "Team 11" }, team2: { id: 28, name: "Team 12" } },
				{ id: 15, roundIndex: 0, matchIndex: 6, team1: { id: 29, name: "Team 13" }, team2: { id: 30, name: "Team 14" } },
				{ id: 16, roundIndex: 0, matchIndex: 7, team1: { id: 31, name: "Team 15" }, team2: { id: 32, name: "Team 16" } },
			],
			[null, null, null, null],
			[null, null],
			[null],
		]

		const filled = fillInMatches(nulls, matches)
		expect(filled).toEqual(expected)
	})

	test('testing fillInMatches invalid round index', () => {
		const nulls = [
			[null, null, null, null, null, null, null, null],
			[null, null, null, null],
			[null, null],
			[null],
		]
		const matches = [
			{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
			{ id: 10, roundIndex: 0, matchIndex: 8, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
		]

		const t = () => {
			const filled = fillInMatches(nulls, matches)
		}
		expect(t).toThrowError('Invalid match index 8 for match 10')
	})

	test('testing fillInMatches invalid match index', () => {
		const nulls = [
			[null, null, null, null, null, null, null, null],
			[null, null, null, null],
			[null, null],
			[null],
		]
		const matches = [
			{ id: 9, roundIndex: 0, matchIndex: 0, team1: { id: 17, name: "Team 1" }, team2: { id: 18, name: "Team 2" } },
			{ id: 10, roundIndex: 4, matchIndex: 1, team1: { id: 19, name: "Team 3" }, team2: { id: 20, name: "Team 4" } },
		]

		const t = () => {
			const filled = fillInMatches(nulls, matches)
		}
		expect(t).toThrowError('Invalid round index 4 for match 10')
	})

	test('testing addEmptyMatches', () => {
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
				{ id: null, roundIndex: 1, matchIndex: 0, team1: null, team2: null },
				{ id: null, roundIndex: 1, matchIndex: 1, team1: null, team2: null },
			],
			[
				{ id: null, roundIndex: 2, matchIndex: 0, team1: null, team2: null },
			]
		]

		const filled = addEmptyMatches(matches)
		expect(filled).toEqual(expected)
	})
	test('testing addEmptyMatches wildcards', () => {
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
				{ id: null, roundIndex: 1, matchIndex: 0, team1: null, team2: null },
				{ id: null, roundIndex: 1, matchIndex: 1, team1: null, team2: null },
			],
			[
				{ id: null, roundIndex: 2, matchIndex: 0, team1: null, team2: null },
			]
		]
	})

})