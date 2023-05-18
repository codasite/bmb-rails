import { Nullable } from '../../utils/types';
import {
	BracketRes,
	BracketReq,
	RoundReq,
	MatchReq,
	TeamReq,
	UserBracketReq,
	UserMatchReq,
	UserRoundReq,
	UserTeamReq,
	RoundRes,
} from '../../api/types/bracket';

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

	toUserRequest(): Nullable<UserTeamReq> {
		return this.id === null ? null : { id: this.id };
	}
}


export class MatchNode {
	team1: Nullable<Team> = null;
	team2: Nullable<Team> = null;
	result: Nullable<Team> = null;
	left: Nullable<MatchNode> = null;
	right: Nullable<MatchNode> = null;
	parent: Nullable<MatchNode> = null;
	depth: number;

	constructor(parent: Nullable<MatchNode>, depth: number) {
		this.depth = depth;
		this.parent = parent;
	}

	clone(): MatchNode {
		const match = this;
		const clone = new MatchNode(null, match.depth);

		clone.team1 = match.team1 ? match.team1.clone() : null;
		clone.team2 = match.team2 ? match.team2.clone() : null;

		if (match.result) {
			if (match.result === match.team1) {
				clone.result = clone.team1;
			} else if (match.result === match.team2) {
				clone.result = clone.team2;
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

	toUserRequest(): UserMatchReq {
		return {
			result: this.result ? this.result.toUserRequest() : null,
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

	toUserRequest(): UserRoundReq {
		const round = this;
		const matches = round.matches.map((match, i) => {
			if (match === null) {
				return null;
			}
			return match.toUserRequest();
		});
		return {
			matches: matches,
		}
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
	// numRounds: number
	// numWildcards: number
	// wildcardsPlacement: WildcardPlacement

	// constructor(numRounds: number, numWildcards: number, wildcardsPlacement: WildcardPlacement) {
	// 	this.rounds = this.buildRounds(numRounds, numWildcards, wildcardsPlacement)
	// 	this.numRounds = numRounds
	// 	this.numWildcards = numWildcards
	// 	this.wildcardsPlacement = wildcardsPlacement
	// }
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
				const match = new MatchNode(parent, i)
				this.assignMatchToParent(x, match, parent)
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
		const tree = this
		const newTree = new MatchTree()
		// First, create the new rounds.
		newTree.rounds = tree.rounds.map((round) => {
			const newRound = new Round(round.id, round.name, round.depth);
			return newRound;
		});
		// Then, iterate over the new rounds to create the matches and update their parent relationships.
		newTree.rounds.forEach((round, roundIndex) => {
			round.matches = tree.rounds[roundIndex].matches.map((match, matchIndex) => {
				if (match === null) {
					return null;
				}
				const newMatch = match.clone();
				const parent = MatchTree.getParent(matchIndex, roundIndex, newTree.rounds);
				newMatch.parent = parent;
				MatchTree.assignMatchToParent(matchIndex, newMatch, parent);
				return newMatch;
			});
		});
		return newTree;
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
				const newMatch = new MatchNode(null, roundIndex);
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

	toUserRequest(name: string, bracketId: number): UserBracketReq {
		const tree = this;
		const rounds = tree.rounds.map((round) => {
			return round.toUserRequest();
		});
		return {
			rounds: rounds,
			name: name,
			bracketId: bracketId,
		}
	}

	advanceTeam = (depth: number, matchIndex: number, left: boolean) => {
		console.log('advanceTeam', depth, matchIndex, left)
		const match = this.rounds[depth].matches[matchIndex]
		if (!match) {
			console.log('no match')
			return
		}
		const team = left ? match.team1 : match.team2
		if (!team) {
			console.log('no team')
			return
		}
		match.result = team
		console.log('match', match)
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