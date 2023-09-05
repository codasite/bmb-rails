import { Nullable } from '../../../utils/types';
import {
	BracketRes,
	BracketReq,
	RoundReq,
	MatchReq,
	MatchRes,
	TeamReq,
	TeamRes,
	SubmissionReq,
	SubmissionMatchReq,
	SubmissionRoundReq,
	SubmissionTeamReq,
	RoundRes,
	MatchResV2,
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

	toRequest(): TeamReq {
		return {
			name: this.name,
		}
	}

	toSubmissionReq(): Nullable<SubmissionTeamReq> {
		return this.id === null ? null : { id: this.id };
	}

	toSerializable(): TeamRes {
		return {
			name: this.name,
			id: this.id,
		}
	}
}


export class MatchNode {
	id: number | null = null;
	team1: Nullable<Team> = null;
	team2: Nullable<Team> = null;
	result: Nullable<Team> = null;
	left: Nullable<MatchNode> = null;
	right: Nullable<MatchNode> = null;
	parent: Nullable<MatchNode> = null;
	depth: number;

	constructor(id: number | null, depth: number, parent: Nullable<MatchNode> = null) {
		this.id = id;
		this.depth = depth;
		this.parent = parent;
	}

	clone(): MatchNode {
		const match = this;
		const clone = new MatchNode(this.id, match.depth, match.parent);

		clone.team1 = match.team1 ? match.team1.clone() : null;
		clone.team2 = match.team2 ? match.team2.clone() : null;

		if (match.result) {
			if (match.result === match.team1) {
				clone.result = clone.team1;
			} else if (match.result === match.team2) {
				clone.result = clone.team2;
			} else {
			}
		}

		return clone;
	}

	toRequest(index: number): MatchReq {
		const match = this;
		return {
			index: index,
			team1: match.team1 ? match.team1.toRequest() : null,
			team2: match.team2 ? match.team2.toRequest() : null,
			result: match.result ? match.result.toRequest() : null,
		}
	}

	toSerializable(i: number): MatchRes {
		const match = this;
		return {
			id: match.id,
			index: i,
			team1: match.team1 ? match.team1.toSerializable() : null,
			team2: match.team2 ? match.team2.toSerializable() : null,
			result: match.result ? match.result.toSerializable() : null,
		}

	}

	toSubmissionReq(): SubmissionMatchReq {
		return {
			result: this.result ? this.result.toSubmissionReq() : null,
		}
	}
}

export class Round {
	id: number;
	name: string;
	depth: number;
	matches: Array<Nullable<MatchNode>>;

	constructor(id: number, name: string, depth: number,) {
		this.id = id;
		this.name = name;
		this.depth = depth;
		// this.matches = [];
	}

	toRequest(): RoundReq {
		const round = this;
		const matches = round.matches.map((match, i) => {
			if (match === null) {
				return null;
			}
			return match.toRequest(i);
		});
		return {
			name: round.name,
			depth: round.depth,
			matches: matches,
		}
	}

	toSubmissionReq(): SubmissionRoundReq {
		const round = this;
		const matches = round.matches.map((match, i) => {
			if (match === null) {
				return null;
			}
			return match.toSubmissionReq();
		});
		return {
			matches: matches,
		}
	}

	toSerializable(): RoundRes {
		const round = this;
		const matches = round.matches.map((match, i) => {
			if (match === null) {
				return null;
			}
			return match.toSerializable(i);
		});
		return {
			id: round.id,
			name: round.name,
			depth: round.depth,
			matches: matches,
		}
	}

	isComplete(): boolean {
		return this.matches.every((match) => {
			if (match === null) {
				return true;
			}
			return match.result !== null;
		});
	}

}

class WildcardRange {
	constructor(public min: number, public max: number) { }

	toString(): string {
		return `${this.min}-${this.max}`;
	}
}

export class MatchTree {
	root: MatchNode | null
	rounds: Round[]
	static fromOptions(numRounds: number, numWildcards: number, wildcardPlacement: WildcardPlacement): MatchTree {
		const tree = new MatchTree()
		tree.rounds = this.buildRounds(numRounds, numWildcards, wildcardPlacement)
		return tree
	}

	static buildRounds(numRounds: number, numWildcards: number, wildcardPlacement: WildcardPlacement): Round[] {
		// The number of matches in a round is equal to 2^depth unless it's the first round
		// and there are wildcards. In that case, the number of matches equals the number of wildcards
		const rootMatch = new MatchNode(null, 0)
		const finalRound = new Round(1, `Round ${numRounds}`, 0)
		finalRound.matches = [rootMatch]
		const rounds = [finalRound]

		for (let i = 1; i < numRounds; i++) {

			let ranges: WildcardRange[] = []

			if (i === numRounds - 1 && numWildcards > 0) {
				const placement = wildcardPlacement
				const maxNodes = 2 ** i
				const range1 = this.getWildcardRange(0, maxNodes / 2, numWildcards / 2, placement)
				const range2 = this.getWildcardRange(maxNodes / 2, maxNodes, numWildcards / 2, placement)
				ranges = [...range1, ...range2]
			}

			const round = new Round(i + 1, `Round ${numRounds - i}`, i);
			const maxMatches = 2 ** i
			const matches: (MatchNode | null)[] = []
			for (let x = 0; x < maxMatches; x++) {
				if (ranges.length > 0) {
					// check to see if x is in the range of any of the wildcard ranges
					const inRange = ranges.some(range => {
						return x >= range.min && x < range.max
					})
					if (!inRange) {
						matches[x] = null
						continue
					}
				}
				// const parentIndex = Math.floor(x / 2)
				// const parent = rounds[i - 1].matches[parentIndex]
				const parent = this.getParent(x, i, rounds)
				const match = new MatchNode(null, i, parent)
				MatchTree.assignMatchToParent(x, match, parent)
				matches[x] = match
			}
			round.matches = matches
			rounds[i] = round
		};
		return rounds
	}


	static getWildcardRange(start: number, end: number, count: number, placement: WildcardPlacement): WildcardRange[] {
		switch (placement) {
			case WildcardPlacement.Top:
				return [new WildcardRange(start, start + count)]
			case WildcardPlacement.Bottom:
				return [new WildcardRange(end - count, end)]
			case WildcardPlacement.Center:
				const offset = (end - start - count) / 2;
				return [new WildcardRange(start + offset, end - offset)];
			case WildcardPlacement.Split:
				return [new WildcardRange(start, start + count / 2), new WildcardRange(end - count / 2, end)]
		}
	}

	clone(): MatchTree {
		// State is now maintained by Redux and stored as a serializable object so we can just return this
		return this
		// const tree = this
		// const newTree = new MatchTree()
		// // First, create the new rounds.
		// newTree.rounds = tree.rounds.map((round) => {
		// 	const newRound = new Round(round.id, round.name, round.depth);
		// 	return newRound;
		// });
		// // Then, iterate over the new rounds to create the matches and update their parent relationships.
		// newTree.rounds.forEach((round, roundIndex) => {
		// 	round.matches = tree.rounds[roundIndex].matches.map((match, matchIndex) => {
		// 		if (match === null) {
		// 			return null;
		// 		}
		// 		const newMatch = match.clone();
		// 		const parent = MatchTree.getParent(matchIndex, roundIndex, newTree.rounds);
		// 		newMatch.parent = parent;
		// 		MatchTree.assignMatchToParent(matchIndex, newMatch, parent);
		// 		return newMatch;
		// 	});
		// });
		// return newTree;
	}

	static fromRounds(rounds: RoundRes[]): MatchTree {
		const tree = new MatchTree()
		tree.rounds = rounds.map((round) => {
			const newRound = new Round(round.id, round.name, round.depth);
			return newRound;
		});
		// Then, iterate over the new rounds to create the matches and update their parent relationships.
		tree.rounds.forEach((round, roundIndex) => {
			round.matches = rounds[roundIndex].matches.map((match, matchIndex) => {
				if (match === null) {
					return null;
				}
				const newMatch = new MatchNode(match.id, roundIndex);
				newMatch.team1 = match.team1 ? new Team(match.team1.name, match.team1.id) : null;
				newMatch.team2 = match.team2 ? new Team(match.team2.name, match.team2.id) : null;
				newMatch.result = match.result ? new Team(match.result.name, match.result.id) : null;
				const parent = this.getParent(matchIndex, roundIndex, tree.rounds);
				if (parent) {
					newMatch.parent = parent;
					this.assignMatchToParent(matchIndex, newMatch, parent);
				}
				return newMatch;
			});
		});
		return tree;
	}

	static fromMatches(numTeams: number, matches: MatchResV2[]): MatchTree {
		const tree = new MatchTree()
		const numRounds = getNumRounds(numTeams)
		const nullMatches = getNullMatchRounds(numRounds)
		try {
			const filledMatches = fillInMatches(nullMatches, matches)

		}
		console.log(numRounds)

		let rounds = []
		let roundIndex = 0
		let matchIndex = 0

		for (const match of matches) {
			if (match.roundIndex > roundIndex) {

			}

		}

		return tree
	}

	toRequest(name: string, active: boolean, numRounds: number, numWildcards: number, wildcardPlacement: WildcardPlacement): BracketReq {
		const tree = this;
		const rounds = tree.rounds.map((round) => {
			return round.toRequest();
		});
		return {
			rounds: rounds,
			name: name,
			active: active,
			numRounds: numRounds,
			numWildcards: numWildcards,
			wildcardPlacement: wildcardPlacement,
		}
	}

	toSubmissionReq(): SubmissionRoundReq[] {
		const tree = this;
		const rounds = tree.rounds.map((round) => {
			return round.toSubmissionReq();
		});
		return rounds
	}

	toSerializable(): RoundRes[] {
		const tree = this;
		const rounds = tree.rounds.map((round) => {
			return round.toSerializable();
		});
		return rounds
	}

	advanceTeam = (depth: number, matchIndex: number, left: boolean) => {
		const prevRound = this.rounds[depth + 1]
		// if (prevRound && !prevRound.isComplete()) {
		// 	return
		// }
		const round = this.rounds[depth]
		const match = round.matches[matchIndex]
		if (!match) {
			return
		}
		const team = left ? match.team1 : match.team2
		if (!team) {
			return
		}
		match.result = team
		const parent = match.parent
		if (!parent) {
			return
		}
		if (match === parent.left) {
			parent.team1 = team
		} else if (match === parent.right) {
			parent.team2 = team
		}
	}

	isComplete = (): boolean => {
		const finalRound = this.rounds[0]
		if (!finalRound) {
			return false
		}
		const finalMatch = finalRound.matches[0]
		if (!finalMatch) {
			return false
		}
		return finalMatch.result !== null
	}

	static getParent(matchIndex: number, roundIndex: number, rounds: Round[]): MatchNode | null {
		if (roundIndex === 0) {
			return null
		}
		const parentIndex = Math.floor(matchIndex / 2)
		return rounds[roundIndex - 1].matches[parentIndex]
	}

	static assignMatchToParent(matchIndex: number, match: MatchNode, parent: MatchNode | null) {
		if (parent === null) {
			return
		}
		if (matchIndex % 2 === 0) {
			parent.left = match
		} else {
			parent.right = match
		}
	}
}

export const getNumRounds = (numTeams: number): number => {
	return Math.ceil(Math.log2(numTeams))
}

export const getNullMatchRounds = (numRounds) => {
	let rounds: any[] = []
	for (let i = numRounds - 1; i >= 0; i--) {
		rounds.push(new Array(Math.pow(2, i)).fill(null))
	}
	return rounds
}

export const fillInMatches = (nullMatches: any[][], matches: MatchResV2[]) => {
	for (const match of matches) {
		if (match.roundIndex >= nullMatches.length) {
			throw new Error(`Invalid round index ${match.roundIndex} for match ${match.id}`)
		}
		if (match.matchIndex >= nullMatches[match.roundIndex].length) {
			throw new Error(`Invalid match index ${match.matchIndex} for match ${match.id}`)
		}
		nullMatches[match.roundIndex][match.matchIndex] = match
	}
	return nullMatches
}