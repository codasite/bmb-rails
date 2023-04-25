type Nullable<T> = T | null;
enum WildcardPlacement {
	Top = 0,
	Bottom = 1,
	Center = 2,
	Split = 3,
}
class Team {
	name: string;
	// constructor(id: number, name: string) {
	// 	this.id = id;
	// 	this.name = name;
	// }
	constructor(name: string) {
		this.name = name;
	}
}

class MatchNode {
	leftTeam: Nullable<Team> = null;
	rightTeam: Nullable<Team> = null;
	result: Nullable<Team> = null;
	left: Nullable<MatchNode> = null;
	right: Nullable<MatchNode> = null;
	parent: Nullable<MatchNode> = null;
	depth: number;

	constructor(parent: Nullable<MatchNode>, depth: number) {
		this.depth = depth;
		this.parent = parent;
	}
}

class Round {
	id: number;
	name: string;
	depth: number;
	roundNum: number;
	matches: Array<Nullable<MatchNode>>;

	constructor(id: number, name: string, depth: number, roundNum: number) {
		this.id = id;
		this.name = name;
		this.depth = depth;
		this.roundNum = roundNum;
		this.matches = [];
	}
}

class WildcardRange {
	constructor(public min: number, public max: number) { }

	toString(): string {
		return `${this.min}-${this.max}`;
	}
}

class MatchTree {
	root: Nullable<MatchNode>;
	rounds: Round[];

	constructor(numRounds: number, numWildcards: number, wildcardsPlacement: WildcardPlacement) {
		this.rounds = this.buildRounds(numRounds, numWildcards, wildcardsPlacement);
	}

	buildRounds(numRounds: number, numWildcards: number, wildcardPlacement: WildcardPlacement): Round[] {
		const rounds: Round[] = [];
		const lastRound = numRounds - 1;

		for (let i = 0; i < numRounds; i++) {
			const depth = numRounds - 1 - i;
			console.log('depth', depth)
			const round = new Round(i + 1, `Round ${numRounds - i}`, depth, numRounds - i);
			const maxMatches = 2 ** depth;
			const matches: Array<Nullable<MatchNode>> = Array.from({ length: maxMatches }, (_, x) => {
				const parentIndex = Math.floor(x / 2);
				const parent = rounds[i - 1]?.matches[parentIndex] ?? null;
				if (parent && !this.isWildcardIndex(x, i, depth, numWildcards, wildcardPlacement)) {
					const match = new MatchNode(parent, depth);
					if (x % 2 === 0) {
						parent.left = match;
					} else {
						parent.right = match;
					}
					return match;
				}
				return null;
			});

			round.matches = matches;
			rounds.push(round);
		}

		return rounds;
	}

	isWildcardIndex(
		index: number,
		roundIndex: number,
		depth: number,
		numWildcards: number,
		wildcardPlacement: WildcardPlacement,
	): boolean {
		if (roundIndex !== depth || numWildcards === 0) {
			return false;
		}

		const maxNodes = 2 ** depth;
		const ranges = this.getWildcardRanges(maxNodes, numWildcards, wildcardPlacement);
		return ranges.some(range => index >= range.min && index < range.max);
	}

	getWildcardRanges(maxNodes: number, numWildcards: number, placement: WildcardPlacement): WildcardRange[] {
		const halfNodes = maxNodes / 2;
		const halfWildcards = numWildcards / 2;


		switch (placement) {
			case WildcardPlacement.Top:
				return [new WildcardRange(0, numWildcards)];
			case WildcardPlacement.Bottom:
				return [new WildcardRange(maxNodes - numWildcards, maxNodes)];
			case WildcardPlacement.Center:
				const offset = (maxNodes - numWildcards) / 2;
				return [new WildcardRange(offset, maxNodes - offset)];
			case WildcardPlacement.Split:
				return [
					new WildcardRange(0, halfWildcards),
					new WildcardRange(maxNodes - halfWildcards, maxNodes),
				];
		}
	}
}
