import { Nullable } from '../../../utils/types';
import {
	MatchRes,
	MatchPicks,
	MatchRepr,
	TeamRepr,
	MatchReq,
	TeamReq,
} from '../api/types/bracket';

export enum WildcardPlacement {
	Top = 0,
	Bottom = 1,
	Center = 2,
	Split = 3,
}

export class Team {
	name: string;
	id: number | null;

	constructor(name: string, id: number | null = null) {
		this.name = name;
		this.id = id;
	}

	clone(): Team {
		return new Team(this.name, this.id);
	}

	serialize(): TeamRepr {
		return {
			name: this.name,
			id: this.id ? this.id : undefined,
		}
	}

	toTeamReq(): TeamReq {
		return {
			name: this.name,
		}
	}
}

export interface MatchNodeArgs {
	id?: number;
	matchIndex: number;
	roundIndex: number;
	team1?: Nullable<Team>;
	team2?: Nullable<Team>;
	team1Wins?: boolean;
	team2Wins?: boolean;
	left?: Nullable<MatchNode>;
	right?: Nullable<MatchNode>;
	parent?: Nullable<MatchNode>;
	depth: number;
}

export class MatchNode {
	id?: number;
	matchIndex: number;
	roundIndex: number;
	private team1: Nullable<Team> = null;
	private team2: Nullable<Team> = null;
	team1Wins?: boolean = false;
	team2Wins?: boolean = false;
	left: Nullable<MatchNode> = null;
	right: Nullable<MatchNode> = null;
	parent: Nullable<MatchNode> = null;
	depth: number;

	// constructor(matchIndex, roundIndex, id: number | null, depth: number, parent: Nullable<MatchNode> = null) {
	constructor(args: MatchNodeArgs) {
		const {
			id,
			matchIndex,
			roundIndex,
			team1,
			team2,
			team1Wins,
			team2Wins,
			left,
			right,
			parent,
			depth,
		} = args

		this.matchIndex = matchIndex;
		this.roundIndex = roundIndex;
		this.depth = depth;
		this.id = id;
		this.team1Wins = team1Wins;
		this.team2Wins = team2Wins;
		this.left = left ? left : null;
		this.right = right ? right : null;
		this.parent = parent ? parent : null;
		this.team1 = team1 ? team1 : null;
		this.team2 = team2 ? team2 : null;
	}

	serialize(): MatchRepr {
		const {
			id,
			matchIndex,
			roundIndex,
			team1,
			team2,
			team1Wins,
			team2Wins,
		} = this

		return {
			id,
			matchIndex,
			roundIndex,
			team1: team1 ? team1.serialize() : undefined,
			team2: team2 ? team2.serialize() : undefined,
			team1Wins,
			team2Wins,
		}
	}

	getWinner(): Nullable<Team> {
		if (this.team1Wins) {
			return this.getTeam1();
		} else if (this.team2Wins) {
			return this.getTeam2();
		}
		return null;
	}

	isLeftChild(): boolean {
		return this.parent !== null && this.parent.left === this;
	}

	getTeam1(): Nullable<Team> {
		return this.left ? this.left.getWinner() : this.team1;
	}

	getTeam2(): Nullable<Team> {
		return this.right ? this.right.getWinner() : this.team2;
	}
}

export class Round {
	index: number;
	depth: number;
	matches: Array<Nullable<MatchNode>>;

	constructor(index: number, depth: number, matches: Array<Nullable<MatchNode>> = []) {
		this.index = index;
		this.depth = depth;
		this.matches = matches;
	}

	isComplete(): boolean {
		return this.matches.every((match) => {
			if (match === null) {
				return true;
			}
			return match.getWinner() !== null;
		});
	}

	serialize(): Nullable<MatchRepr>[] {
		return this.matches.map((match, i) => {
			if (match === null) {
				return null;
			}
			return match.serialize();
		})
	}
}

interface WildcardRange {
	min: number;
	max: number;
}

export class MatchTree {
	rounds: Round[]

	serialize(): Nullable<MatchRepr>[][] {
		const tree = this;
		const rounds = tree.rounds.map((round) => {
			return round.serialize()
		});
		return rounds
	}

	advanceTeam = (roundIndex: number, matchIndex: number, left: boolean, requireComplete: boolean = false) => {
		if (requireComplete && roundIndex > 0) {
			const prevRound = this.rounds[roundIndex - 1]
			if (prevRound && !prevRound.isComplete()) {
				return
			}
		}
		const round = this.rounds[roundIndex]
		const match = round.matches[matchIndex]
		if (!match) {
			return
		}
		if (left) {
			match.team1Wins = true
		} else {
			match.team2Wins = true
		}
	}

	isComplete = (): boolean => {
		const finalRound = this.rounds[this.rounds.length - 1]
		if (!finalRound) {
			return false
		}
		const finalMatch = finalRound.matches[0]
		if (!finalMatch) {
			return false
		}
		return finalMatch.getWinner() !== null
	}

	toMatchReq = (): MatchReq[] => {
		const root = this.rounds[this.rounds.length - 1].matches[0]
		if (!root) {
			return []
		}
		const matches: MatchReq[] = []
		const queue: MatchNode[] = [root]
		while (queue.length > 0) {
			const match = queue.shift()
			if (!match) {
				continue
			}
			const matchReq: MatchReq = {
				roundIndex: match.roundIndex,
				matchIndex: match.matchIndex,
			}
			if (!match.left) {
				matchReq.team1 = match.getTeam1()?.toTeamReq()
			} else {
				queue.push(match.left)
			}
			if (!match.right) {
				matchReq.team2 = match.getTeam2()?.toTeamReq()
			} else {
				queue.push(match.right)
			}
			if (matchReq.team1 || matchReq.team2) {
				matches.push(matchReq)
			}
		}
		console.log(matches)

		return matches
	}

	toMatchPicks = (): MatchPicks[] => {
		const picks: MatchPicks[] = []
		this.rounds.forEach((round, roundIndex) => {
			round.matches.forEach((match, matchIndex) => {
				if (!match) {
					return
				}
				const { team1Wins, team2Wins } = match
				const team1 = match.getTeam1()
				const team2 = match.getTeam2()
				const team1Winner = team1Wins && team1
				const team2Winner = team2Wins && team2

				if (team1Winner && team1.id) {
					picks.push({
						roundIndex,
						matchIndex,
						winningTeamId: team1.id
					})
				} else if (team2Winner && team2.id) {
					picks.push({
						roundIndex,
						matchIndex,
						winningTeamId: team2.id
					})
				}
			})
		})
		console.log('picks', picks)
		return picks
	}

	static fromNumTeams(numTeams: number, wildcardPlacement: WildcardPlacement = WildcardPlacement.Top): MatchTree {
		const matches = matchReprFromNumTeams(numTeams, wildcardPlacement)
		return MatchTree.deserialize(matches)
	}

	static fromMatchRes(numTeams: number, matches: MatchRes[]): MatchTree | null {
		const numRounds = getNumRounds(numTeams)

		try {
			const nestedMatches = getMatchRepr(numRounds, matches)
			return MatchTree.deserialize(nestedMatches)
		}
		catch (e) {
			console.log(e)
			return null
		}
	}

	static fromPicks(numTeams: number, matches: MatchRes[], picks: MatchPicks[]): MatchTree | null {
		const matchTree = MatchTree.fromMatchRes(numTeams, matches)
		if (!matchTree) {
			return null
		}
		for (const pick of picks) {
			const { roundIndex, matchIndex, winningTeamId } = pick
			const match = matchTree.rounds[roundIndex].matches[matchIndex]
			if (!match) {
				return null
			}
			const team1 = match.getTeam1()
			const team2 = match.getTeam2()
			if (!team1 || !team2) {
				return null
			}
			if (team1.id === winningTeamId) {
				match.team1Wins = true
			} else if (team2.id === winningTeamId) {
				match.team2Wins = true
			} else {
				return null
			}
		}
		return matchTree
	}

	static deserialize(matchRes: Nullable<MatchRepr>[][]) {
		const rounds = matchRes.map((round, roundIndex) => {
			const depth = matchRes.length - roundIndex - 1
			const newRound = new Round(roundIndex, depth)
			const matches = round.map((match, matchIndex) => {
				if (match === null) {
					return null;
				}
				const { id, team1: team1Repr, team2: team2Repr, team1Wins, team2Wins } = match
				const team1 = team1Repr ? new Team(team1Repr.name, team1Repr.id) : null;
				const team2 = team2Repr ? new Team(team2Repr.name, team2Repr.id) : null;
				// const newMatch = new MatchNode(roundIndex, matchIndex, depth, match.id, team1, team2, team1Wins, team2Wins)
				const newMatch = new MatchNode({
					id,
					matchIndex,
					roundIndex,
					team1,
					team2,
					team1Wins,
					team2Wins,
					left: null,
					right: null,
					parent: null,
					depth,
				})
				return newMatch;
			})
			newRound.matches = matches
			return newRound;
		})
		const tree = new MatchTree()
		linkNodes(rounds)
		// linkTeams(rounds)
		tree.rounds = rounds
		return tree
	}
}

// export const linkTeams = (rounds: Round[]) => {

// 	rounds.forEach((round, roundIndex) => {
// 		round.matches.forEach((match, matchIndex) => {
// 			if (!match) {
// 				return
// 			}
// 			const { team1Wins, team2Wins } = match
// 			const team1 = match.getTeam1()
// 			const team2 = match.getTeam2()
// 			const team1Winner = team1Wins && team1
// 			const team2Winner = team2Wins && team2

// 			if (team1Winner) {
// 				linkParentTeam(team1Winner, matchIndex, roundIndex, rounds)
// 			} else if (team2Winner) {
// 				linkParentTeam(team2Winner, matchIndex, roundIndex, rounds)
// 			}
// 		})
// 	})
// }

export const linkNodes = (rounds: Round[]) => {
	rounds.forEach((round, roundIndex) => {
		round.matches.forEach((match, matchIndex) => {
			if (!match) {
				return
			}
			const parent = getParent(matchIndex, roundIndex, rounds)
			match.parent = parent
			assignMatchToParent(matchIndex, match, parent)
		})
	})
}


export const getParent = (matchIndex: number, roundIndex: number, rounds: Round[]): MatchNode | null => {
	if (roundIndex === rounds.length - 1) {
		// last round does not have a parent
		return null
	}
	const parentIndex = Math.floor(matchIndex / 2)
	return rounds[roundIndex + 1].matches[parentIndex]
}

export const assignMatchToParent = (matchIndex: number, match: MatchNode, parent: MatchNode | null) => {
	if (parent === null) {
		return
	}
	if (matchIndex % 2 === 0) {
		parent.left = match
	} else {
		parent.right = match
	}
}

// export const linkParentTeam = (winningTeam: Team, matchIndex: number, roundIndex: number, rounds: Round[]) => {
// 	const parent = getParent(matchIndex, roundIndex, rounds);
// 	const leftChild = isLeftChild(matchIndex);
// 	if (parent) {
// 		if (leftChild) {
// 			parent.team1 = winningTeam;
// 		} else {
// 			parent.team2 = winningTeam;
// 		}
// 	}
// }

export const isLeftChild = (matchIndex: number): boolean => {
	return matchIndex % 2 === 0
}

export const getNumRounds = (numTeams: number): number => {
	return Math.ceil(Math.log2(numTeams))
}


export const getMatchRepr = (numRounds: number, matches: MatchRepr[]) => {
	const nullableMatches = getNullMatches(numRounds) as Nullable<MatchRepr>[][]
	for (const match of matches) {
		if (match.roundIndex >= nullableMatches.length) {
			throw new Error(`Invalid round index ${match.roundIndex} for match ${match.id}`)
		}
		if (match.matchIndex >= nullableMatches[match.roundIndex].length) {
			throw new Error(`Invalid match index ${match.matchIndex} for match ${match.id}`)
		}
		nullableMatches[match.roundIndex][match.matchIndex] = match
	}
	const filledMatches = fillInEmptyMatches(nullableMatches)

	return filledMatches
}

export const getNullMatches = (numRounds: number): null[][] => {
	let rounds: any[] = []
	for (let i = numRounds - 1; i >= 0; i--) {
		rounds.push(new Array(Math.pow(2, i)).fill(null))
	}
	return rounds
}

export const fillInEmptyMatches = (rounds: any[][], roundStart: number = 1): any[][] => {
	const newRounds = rounds.map((round, roundIndex) => {
		if (roundIndex < roundStart) {
			return round
		}
		const newRound = round.map((match, matchIndex) => {
			if (match !== null) {
				return match
			}
			return { roundIndex: roundIndex, matchIndex: matchIndex }
		})
		return newRound

	})
	return newRounds
}

export const getWildcardRange = (start: number, end: number, count: number, placement: WildcardPlacement): WildcardRange[] => {
	switch (placement) {
		case WildcardPlacement.Top:
			return [{ min: start, max: start + count }]
		case WildcardPlacement.Bottom:
			return [{ min: end - count, max: end }]
		case WildcardPlacement.Center:
			const offset = (end - start - count) / 2;
			return [{ min: start + offset, max: end - offset }]
		case WildcardPlacement.Split:
			return [{ min: start, max: start + count / 2 }, { min: end - count / 2, max: end }]
	}
}

export const getFirstRoundMatches = (numTeams: number, wildcardPlacement?: WildcardPlacement, maxMatches?: number): Nullable<MatchRepr>[] => {
	// This somehow works to get the number of matches that are not null in the first round
	if (!maxMatches) {
		maxMatches = 2 ** (getNumRounds(numTeams) - 1)
	}

	if (!wildcardPlacement) {
		wildcardPlacement = WildcardPlacement.Top
	}

	const matchCount = numTeams - maxMatches

	const leftSideCount = Math.ceil(matchCount / 2)
	const rightSideCount = Math.floor(matchCount / 2)

	const leftRange = getWildcardRange(0, maxMatches / 2, leftSideCount, wildcardPlacement)
	const rightRange = getWildcardRange(maxMatches / 2, maxMatches, rightSideCount, wildcardPlacement)

	const ranges = [...leftRange, ...rightRange]

	const matches = Array.from({ length: maxMatches }).map((match, matchIndex) => {
		const inRange = ranges.some(range => {
			return matchIndex >= range.min && matchIndex < range.max
		})
		if (!inRange) {
			return null
		}
		return { roundIndex: 0, matchIndex: matchIndex }

	})

	return matches
}

export const matchReprFromNumTeams = (numTeams: number, wildcardPlacement: WildcardPlacement = WildcardPlacement.Top): Nullable<MatchRepr>[][] => {
	const numRounds = getNumRounds(numTeams)

	const rounds = Array.from({ length: numRounds }).map((round, roundIndex) => {
		const depth = numRounds - roundIndex - 1
		const maxMatches = 2 ** depth
		if (roundIndex === 0) {
			const matches = getFirstRoundMatches(numTeams, wildcardPlacement, maxMatches)
			return matches
		} else {
			const matches = Array.from({ length: maxMatches }).map((match, matchIndex) => {
				return { roundIndex: roundIndex, matchIndex: matchIndex }
			})
			return matches
		}
	})
	return rounds
}