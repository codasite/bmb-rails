import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Button, InputGroup } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { Form } from 'react-bootstrap';
import { bracketApi } from '../../api/bracketApi';
import { Nullable } from '../../types';
import { BracketResponse } from '../../api/bracketApi';

enum WildcardPlacement {
	Top = 0,
	Bottom = 1,
	Center = 2,
	Split = 3,
}

// Direction enum
enum Direction {
	TopLeft = 0,
	TopRight = 1,
	BottomLeft = 2,
	BottomRight = 3,
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

	clone(): Team {
		return new Team(this.name);
	}
}


class MatchNode {
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

}

class Round {
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
}

class WildcardRange {
	constructor(public min: number, public max: number) { }

	toString(): string {
		return `${this.min}-${this.max}`;
	}
}

class MatchTree {
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
		console.log('cloning tree')
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

	static fromBracketResponse(bracket: BracketResponse): MatchTree {
		const tree = new MatchTree()
		tree.rounds = bracket.rounds.map((round) => {
			const newRound = new Round(round.id, round.name, round.depth);
			return newRound;
		});
		// Then, iterate over the new rounds to create the matches and update their parent relationships.
		tree.rounds.forEach((round, roundIndex) => {
			round.matches = bracket.rounds[roundIndex].matches.map((match, matchIndex) => {
				if (match === null) {
					return null;
				}
				const newMatch = new MatchNode(null, roundIndex);
				newMatch.team1 = match.team1 ? new Team(match.team1.name) : null;
				newMatch.team2 = match.team2 ? new Team(match.team2.name) : null;
				newMatch.result = match.result ? new Team(match.result.name) : null;
				const parent = this.getParent(matchIndex, roundIndex, tree.rounds);
				newMatch.parent = parent;
				this.assignMatchToParent(matchIndex, newMatch, parent);
				return newMatch;
			});
		});
		return tree;
	}


	// 	newTree.rounds = tree.rounds.map((round, i, rounds) => {
	// 		const newRound = new Round(round.id, round.name, round.depth, round.roundNum)
	// 		console.log('i', i)
	// 		console.log('rounds', rounds)
	// 		newRound.matches = round.matches.map((match, x, matches) => {
	// 			if (match === null) {
	// 				return null
	// 			}
	// 			const newMatch = match.clone()
	// 			console.log('x', x)
	// 			console.log('matches', matches)
	// 			const parent = this.getParent(x, i, newTree.rounds)
	// 			newMatch.parent = parent
	// 			this.assignMatchToParent(x, newMatch, parent)
	// 			return newMatch
	// 		})
	// 		return newRound
	// 	})
	// 	return newTree
	// }

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

const TeamSlot = (props) => {
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState('')
	const team: Team | null = props.team
	const updateTeam = props.updateTeam

	const handleUpdateTeam = (e) => {
		if (!team && textBuffer !== '' || team && textBuffer !== team.name) {
			updateTeam(textBuffer)
		}
		setEditing(false)
	}

	return (
		<div className={props.className} onClick={() => setEditing(true)}>
			{editing ?
				<input
					className='wpbb-team-name-input'
					autoFocus
					onFocus={(e) => e.target.select()}
					type='text'
					value={textBuffer}
					onChange={(e) => setTextBuffer(e.target.value)}
					onBlur={handleUpdateTeam}
					onKeyUp={(e) => {
						if (e.key === 'Enter') {
							handleUpdateTeam(e)
						}
					}}
				/>
				:
				<span className='wpbb-team-name'>{team ? team.name : ''}</span>
			}
		</div>
	)
}

const MatchBox = (props) => {
	const match: MatchNode | null = props.match
	const direction: Direction = props.direction
	const height: number = props.height
	const spacing: number = props.spacing
	// const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {
	const updateTeam = props.updateTeam

	// const updateTeam = (name: string, left: boolean) => {
	// 	console.log('updateTeam', name)
	// 	// updateTeam(match.roundId, match.matchIndex, left, name)
	// }

	if (match === null) {
		return (
			<div className='wpbb-match-box-empty' style={{ height: height + spacing }} />
		)
	}


	let className: string;

	if (direction === Direction.TopLeft || direction === Direction.BottomLeft) {
		// Left side of the bracket
		className = 'wpbb-match-box-left'
	} else {
		// Right side of the bracket
		className = 'wpbb-match-box-right'
	}

	const upperOuter = match.left === null
	const lowerOuter = match.right === null

	if (upperOuter && lowerOuter) {
		// First round
		className += '-outer'
	} else if (upperOuter) {
		// Upper bracket
		className += '-outer-upper'
	} else if (lowerOuter) {
		// Lower bracket
		className += '-outer-lower'
	}

	// This component renders the lines connecting two nodes representing a "game"
	// These should be evenly spaced in the column and grow according to the number of other matches in the round
	return (
		<div className={className} style={{ height: height, marginBottom: spacing }}>
			<TeamSlot className='wpbb-team1' team={match.team1} updateTeam={(name: string) => updateTeam(true, name)} />
			<TeamSlot className='wpbb-team2' team={match.team2} updateTeam={(name: string) => updateTeam(false, name)} />
		</div>
	)
}

const Spacer = ({ grow = '1' }) => {
	return (
		<div style={{ flexGrow: grow }} />
	)
}

const RoundHeader = (props) => {
	const [editRoundName, setEditRoundName] = useState(false);
	const [nameBuffer, setNameBuffer] = useState('');
	const round: Round = props.round;
	const updateRoundName = props.updateRoundName;

	useEffect(() => {
		setNameBuffer(props.round.name)
	}, [props.round.name])

	const doneEditing = () => {
		setEditRoundName(false)
		props.updateRoundName(props.round.id, nameBuffer)
	}

	return (
		<div className='wpbb-round__header'>
			{editRoundName ? <Form.Control type='text'
				value={nameBuffer}
				autoFocus
				onFocus={(e) => e.target.select()}
				onBlur={() => doneEditing()}
				onChange={(e) => setNameBuffer(e.target.value)}
				onKeyUp={(e) => {
					if (e.key === 'Enter') {
						doneEditing()
					}
				}}
			/>
				:
				<span onClick={() => setEditRoundName(true)}>{round.name}</span>
			}
		</div>
	)
}

const FinalRound = (props) => {
	const round: Round = props.round;
	const updateRoundName = props.updateRoundName;
	return (
		<div className='wpbb-round'>
			<RoundHeader round={round} updateRoundName={updateRoundName} />
			<div className='wpbb-round__body'>
				<Spacer grow='2' />
				<div className='wpbb-final-match'>
					<TeamSlot className='wpbb-team1' />
					<TeamSlot className='wpbb-team2' />
				</div>
				<Spacer grow='2' />
			</div>
		</div>
	)
}


const MatchColumn = (props) => {
	const round: Round = props.round;
	const matches: MatchNode[] = props.matches;
	const direction: Direction = props.direction;
	const matchHeight: number = props.matchHeight;
	const updateRoundName = props.updateRoundName;
	const updateTeam = props.updateTeam;
	// const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {

	const buildMatches = () => {
		const matchBoxes = matches.map((match, i) => {
			const matchIndex = direction === Direction.TopLeft || direction === Direction.BottomLeft ? i : i + matches.length
			return (
				<MatchBox
					match={match}
					direction={direction}
					height={matchHeight}
					spacing={i + 1 < matches.length ? matchHeight : 0} // Do not add spacing to the last match in the round column
					updateTeam={(left: boolean, name: string) => updateTeam(round.id, matchIndex, left, name)}
				/>
			)
		})
		return matchBoxes

	}
	return (
		<div className='wpbb-round'>
			<RoundHeader round={round} updateRoundName={updateRoundName} />
			<div className='wpbb-round__body'>
				{buildMatches()}
			</div>
		</div>
	)
}

const NumRoundsSelector = (props) => {
	const {
		numRounds,
		setNumRounds
	} = props

	const minRounds = 1;
	const maxRounds = 6;

	const options = Array.from(Array(maxRounds - minRounds + 1).keys()).map((i) => {
		return (
			<option value={i + minRounds}>{i + minRounds}</option>
		)
	})

	const handleChange = (event) => {
		const num = event.target.value
		setNumRounds(parseInt(num))
	}

	return (
		<div className='wpbb-option-group'>
			<label>
				Number of Rounds:
			</label>
			<select value={numRounds} onChange={handleChange}>
				{options}
			</select>
		</div>
	)
}

const NumWildcardsSelector = (props) => {
	const {
		numWildcards,
		setNumWildcards,
		maxWildcards,
	} = props

	const minWildcards = 0;

	// Number of wildcards must be an even number or 0
	let options = [
		<option value={0}>0</option>
	]
	options = [...options, ...Array.from(Array(maxWildcards / 2).keys()).reverse().map((i) => {
		return (
			<option value={(i + 1) * 2}>{(i + 1) * 2}</option>
		)
	})]

	const handleChange = (event) => {
		const num = event.target.value
		setNumWildcards(parseInt(num))
	}

	return (
		<div className='wpbb-option-group'>
			<label>
				Wildcard Games:
			</label>
			<select value={numWildcards} onChange={handleChange}>
				{options}
			</select>
		</div>
	)
}

const WildcardPlacementSelector = (props) => {
	const {
		wildcardPlacement,
		setWildcardPlacement,
	} = props

	const options = [
		<option value={WildcardPlacement.Bottom}>Bottom</option>,
		<option value={WildcardPlacement.Top}>Top</option>,
		<option value={WildcardPlacement.Split}>Split</option>,
		<option value={WildcardPlacement.Center}>Centered</option>,
	]

	const handleChange = (event) => {
		const num = event.target.value
		setWildcardPlacement(parseInt(num))
	}

	return (
		<div className='wpbb-option-group'>
			<label>
				Wildcard Placement:
			</label>
			<select value={wildcardPlacement} onChange={handleChange}>
				{options}
			</select>
		</div>
	)
}

const BracketTitle = (props) => {
	const {
		title,
		setTitle,
	} = props
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState(title)

	const handleUpdateTitle = (event) => {
		setTitle(textBuffer)
		setEditing(false)
	}

	return (
		<div className='wpbb-bracket-title' onClick={() => setEditing(true)}>
			{editing ?
				<input
					className='wpbb-bracket-title-input'
					autoFocus
					onFocus={(e) => e.target.select()}
					type='text'
					value={textBuffer}
					onChange={(e) => setTextBuffer(e.target.value)}
					onBlur={handleUpdateTitle}
					onKeyUp={(e) => {
						if (e.key === 'Enter') {
							handleUpdateTitle(e)
						}
					}}
				/>
				:
				<span className='wpbb-bracket-title-name'>{title}</span>
			}
		</div>
	)
}


interface BracketProps {
	matchTree: MatchTree
}

const Bracket = (props: BracketProps) => {
	// const { numRounds, numWildcards, wildcardPlacement } = props
	// const [matchTree, setMatchTree] = useState<MatchTree>(MatchTree.fromOptions(numRounds, numWildcards, wildcardPlacement))
	const matchTree = props.matchTree

	const rounds = matchTree.rounds

	const updateRoundName = (roundId: number, name: string) => {
		const newMatchTree = matchTree.clone();
		const roundToUpdate = newMatchTree.rounds.find((round) => round.id === roundId);
		if (roundToUpdate) {
			roundToUpdate.name = name;
			setMatchTree(newMatchTree);
		}
	};

	const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {
		const newMatchTree = matchTree.clone();
		const roundToUpdate = newMatchTree.rounds.find((round) => round.id === roundId);
		if (roundToUpdate) {
			const matchToUpdate = roundToUpdate.matches[matchIndex];
			if (matchToUpdate) {
				if (left) {
					const team = matchToUpdate.team1;
					if (team) {
						team.name = name;
					} else {
						matchToUpdate.team1 = new Team(name);
					}
				} else {
					const team = matchToUpdate.team2;
					if (team) {
						team.name = name;
					} else {
						matchToUpdate.team2 = new Team(name);
					}
				}
			}
			setMatchTree(newMatchTree);
		}
	}


	useEffect(() => {
		const matchTree = MatchTree.fromOptions(numRounds, numWildcards, wildcardPlacement)
		// setRounds(matchTree.rounds)
		setMatchTree(matchTree)
		// setRounds(buildRounds(numRounds, numWildcards))
	}, [numRounds, numWildcards, wildcardPlacement])

	const targetHeight = 800;

	// The number of rounds sets the initial height of each match
	// const firstRoundMatchHeight = targetHeight / rounds.length / 2;
	const firstRoundMatchHeight = targetHeight / 2 ** (rounds.length - 2) / 2;

	/**
	 * Build rounds in two directions, left to right and right to left
	 */
	const buildRounds2 = (rounds: Round[]) => {
		// Assume rounds are sorted by depth
		// Rendering from left to right, sort by depth descending
		const numDirections = 2

		return [
			...rounds.slice(1).reverse().map((round, idx) => {
				// Get the first half of matches for this column
				const colMatches = round.matches.slice(0, round.matches.length / 2)

				return <MatchColumn
					matches={colMatches}
					round={round} direction={Direction.TopLeft}
					numDirections={numDirections}
					matchHeight={2 ** idx * firstRoundMatchHeight}
					updateRoundName={updateRoundName}
					updateTeam={updateTeam}
				/>
			}),
			// handle final round differently
			<FinalRound round={rounds[0]} updateRoundName={updateRoundName} />,
			...rounds.slice(1).map((round, idx, arr) => {
				// Get the second half of matches for this column
				const colMatches = round.matches.slice(round.matches.length / 2)

				return <MatchColumn round={round}
					matches={colMatches}
					direction={Direction.TopRight}
					numDirections={numDirections}
					matchHeight={2 ** (arr.length - 1 - idx) * firstRoundMatchHeight}
					updateRoundName={updateRoundName}
					updateTeam={updateTeam}
				/>
			})
		]
	}

	return (
		<div className='wpbb-bracket'>
			{rounds.length > 0 && buildRounds2(rounds)}
		</div>
	)
}

const ViewBracketModal = (props) => {
	const {
		show,
		handleClose,
		bracketId
	} = props;

	useEffect(() => {
		bracketApi.getBracket(bracketId)
			.then((bracket) => {

				console.log('bracket', bracket)
			})
	})

	return (
		<Modal className='wpbb-bracket-modal' show={show} onHide={handleClose} size='xl' centered={true}>
			<Modal.Header className='wpbb-bracket-modal__header' closeButton>
				<Modal.Title>View Bracket {bracketId}</Modal.Title>
			</Modal.Header >
			<Modal.Body className='pt-0'><Bracket numRounds={4} numWildcards={0} wildcardPlacement={WildcardPlacement.Bottom} /></Modal.Body>
			<Modal.Footer className='wpbb-bracket-modal__footer'>
				<Button variant="secondary" onClick={handleClose}>
					Close
				</Button>
			</Modal.Footer>
		</Modal>
	)
}

const NewBracketModal = (props) => {
	const {
		show,
		handleClose,
		handleSave,
		bracketId
	} = props;
	const [numRounds, setNumRounds] = useState(4);
	const [numWildcards, setNumWildcards] = useState(0);
	const [wildcardPlacement, setWildcardPlacement] = useState(WildcardPlacement.Bottom);
	const [bracketName, setBracketName] = useState('New Bracket');
	const [matchTree, setMatchTree] = useState<MatchTree>(MatchTree.fromOptions(numRounds, numWildcards, wildcardPlacement))
	// The max number of wildcards is 2 less than the possible number of matches in the first round
	// (2^numRounds - 2)
	const maxWildcards = 2 ** (numRounds - 1) - 2;

	return (
		<Modal className='wpbb-bracket-modal' show={show} onHide={handleClose} size='xl' centered={true}>
			<Modal.Header className='wpbb-bracket-modal__header' closeButton>
				<Modal.Title><BracketTitle title={bracketName} setTitle={setBracketName} /></Modal.Title>
				<form className='wpbb-options-form'>
					<NumRoundsSelector numRounds={numRounds} setNumRounds={setNumRounds} />
					<NumWildcardsSelector numWildcards={numWildcards} setNumWildcards={setNumWildcards} maxWildcards={maxWildcards} />
					<WildcardPlacementSelector wildcardPlacement={wildcardPlacement} setWildcardPlacement={setWildcardPlacement} />
				</form>
			</Modal.Header >
			<Modal.Body className='pt-0'><Bracket matchTree={matchTree} /></Modal.Body>
			<Modal.Footer className='wpbb-bracket-modal__footer'>
				<Button variant="secondary" onClick={handleClose}>
					Close
				</Button>
				<Button variant="primary" onClick={handleSave}>
					Save Changes
				</Button>
			</Modal.Footer>
		</Modal>
	)
}

export const BracketModal = (props) => {
	const bracketId = props.bracketId;

	if (bracketId) {
		return <ViewBracketModal {...props} />
	} else {
		return <NewBracketModal {...props} />
	}
}
