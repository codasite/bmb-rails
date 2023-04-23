import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Button, InputGroup } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { Form } from 'react-bootstrap';

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
}


class MatchNode {
	leftTeam: Team | null = null;
	rightTeam: Team | null = null;
	result: Team | null = null;
	left: MatchNode | null = null;
	right: MatchNode | null = null;
	parent: MatchNode | null = null;
	depth: number;

	constructor(parent: MatchNode | null, depth: number,) {
		this.depth = depth;
		this.parent = parent;
	}
}

class Round {
	id: number;
	name: string;
	depth: number;
	roundNum: number;
	matches: (MatchNode | null)[];
	constructor(id: number, name: string, depth: number, roundNum: number) {
		this.id = id;
		this.name = name;
		this.depth = depth;
		this.roundNum = roundNum;
		this.matches = [];
	}
}

class WildcardRange {
	min: number
	max: number

	constructor(min: number, max: number) {
		this.min = min
		this.max = max
	}

	toString(): string {
		return `${this.min}-${this.max}`
	}
}

class MatchTree {
	root: MatchNode | null
	rounds: Round[]

	constructor(numRounds: number, numWildcards: number) {
		this.rounds = this.buildRounds(numRounds, numWildcards)
	}

	buildRounds(numRounds: number, numWildcards: number): Round[] {
		// The number of matches in a round is equal to 2^depth unless it's the first round
		// and there are wildcards. In that case, the number of matches equals the number of wildcards
		const rootMatch = new MatchNode(null, 0)
		const finalRound = new Round(1, 'Round 1', 0, numRounds)
		finalRound.matches = [rootMatch]
		const rounds = [finalRound]

		for (let i = 1; i < numRounds; i++) {

			let ranges: WildcardRange[] = []

			if (i === numRounds - 1 && numWildcards > 0) {
				// const placement = WildcardPlacement.Top
				const placement = WildcardPlacement.Bottom
				const maxNodes = 2 ** i
				console.log('max nodes', maxNodes)
				const range1 = this.getWildcardRange(0, maxNodes / 2, numWildcards / 2, placement)
				const range2 = this.getWildcardRange(maxNodes / 2, maxNodes, numWildcards / 2, placement)
				ranges = [...range1, ...range2]
				console.log('ranges', ranges)
			}

			const round = new Round(i + 1, `Round ${numRounds - i}`, i, numRounds - i);
			const numMatches = i === numRounds - 1 && numWildcards > 0 ? numWildcards : 2 ** i;
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
				const parentIndex = Math.floor(x / 2)
				const parent = rounds[i - 1].matches[parentIndex]
				const match = new MatchNode(parent, i)
				if (parent) {
					// If x is even, match is the left child of parent, otherwise right child
					if (x % 2 === 0) {
						parent.left = match
					} else {
						parent.right = match
					}
				}
				matches[x] = match
			}
			round.matches = matches
			rounds[i] = round
		};
		rounds.forEach(round => {
			console.log(round.matches)
		})
		return rounds
	}

	// return the list of ranges that define where empty nodes should be placed based on the wildcard placement pattern selected by the user
	// getWildcardRange (maxNodes: number, emptyNodes: number, directions: number, placement: WildcardPlacement): WildcardRange[] {
	// 	const nodesPerDirection = maxNodes / directions
	// 	switch (placement) {
	// 		case WildcardPlacement.Top:


	// 		case WildcardPlacement.Bottom:
	// 		case WildcardPlacement.Center:
	// 		case WildcardPlacement.Split:
	// }}

	getWildcardRange(start: number, end: number, count: number, placement: WildcardPlacement): WildcardRange[] {
		switch (placement) {
			case WildcardPlacement.Top:
				return [new WildcardRange(start, start + count)]
			case WildcardPlacement.Bottom:
				return [new WildcardRange(end - count, end)]
			case WildcardPlacement.Center:
				const total = end - start
				console.log('total', total)
				console.log('count', count)
				const offset = (total - count) / 2
				console.log('offset', offset)
				const min = Math.ceil(start + offset)
				console.log('min', min)
				const max = Math.ceil(end - offset)
				console.log('max', max)
				return [new WildcardRange(min, max)]

			// // Split the range into 2 and call this function once for each half
			// const start1 = start
			// const end1 = end - start / 2
			// const start2 = end1
			// const end2 = end
			// const range1 = this.getWildcardRange(start1, end1, count / 2, WildcardPlacement.Bottom)
			// const range2 = this.getWildcardRange(start2, end2, count / 2, WildcardPlacement.Top)
			// return [...range1, ...range2]
			case WildcardPlacement.Split:
				// Split the range into 2 and call this function once for each half
				const start3 = start
				const end3 = end - start / 2
				const start4 = end3
				const end4 = end
				const range3 = this.getWildcardRange(start3, end3, count / 2, WildcardPlacement.Top)
				const range4 = this.getWildcardRange(start4, end4, count / 2, WildcardPlacement.Bottom)
				// return [...range3, ...range4]
				return [new WildcardRange(1, 3)]
		}
	}
}

const TeamSlot = (props) => {
	return (
		<div className={props.className}>
			<span className='wpbb-team-name'>Michigan State</span>
		</div>
	)
}

const MatchBox = ({ ...props }) => {
	const match: MatchNode | null = props.match
	const direction: Direction = props.direction
	const height: number = props.height
	const spacing: number = props.spacing


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
			<TeamSlot className='wpbb-team1' />
			<TeamSlot className='wpbb-team2' />
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

	const buildMatches = () => {
		const matchBoxes = matches.map((match, i) => {
			return (
				<MatchBox
					match={match}
					direction={direction}
					height={matchHeight}
					spacing={i + 1 < matches.length ? matchHeight : 0} // Do not add spacing to the last match in the round column
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

export const Bracket = (props) => {
	const { numRounds, numWildcards } = props
	const [rounds, setRounds] = useState<Round[]>([])

	const updateRoundName = (roundId: number, name: string) => {
		const newRounds = rounds.map((round) => {
			if (round.id === roundId) {
				round.name = name
			}
			return round
		})
		setRounds(newRounds)
	}

	useEffect(() => {
		const matchTree = new MatchTree(numRounds, numWildcards)
		setRounds(matchTree.rounds)
		// setRounds(buildRounds(numRounds, numWildcards))
	}, [numRounds, numWildcards])

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

export const BracketModal = (props) => {
	const {
		show,
		handleCancel,
		handleSave,
	} = props;
	const [numRounds, setNumRounds] = useState(4);
	const [numWildcards, setNumWildcards] = useState(0);
	// The max number of wildcards is 2 less than the possible number of matches in the first round
	// (2^numRounds - 2)
	const maxWildcards = 2 ** (numRounds - 1) - 2;

	return (
		<Modal className='wpbb-bracket-modal' show={show} onHide={handleCancel} size='xl' centered={true}>
			<Modal.Header className='wpbb-bracket-modal__header' closeButton>
				<Modal.Title>Create Bracket</Modal.Title>
				<form className='wpbb-options-form'>
					<NumRoundsSelector numRounds={numRounds} setNumRounds={setNumRounds} />
					<NumWildcardsSelector numWildcards={numWildcards} setNumWildcards={setNumWildcards} maxWildcards={maxWildcards} />
				</form>
			</Modal.Header >
			<Modal.Body className='pt-0'><Bracket numRounds={numRounds} numWildcards={numWildcards} /></Modal.Body>
			<Modal.Footer className='wpbb-bracket-modal__footer'>
				<Button variant="secondary" onClick={handleCancel}>
					Close
				</Button>
				<Button variant="primary" onClick={handleSave}>
					Save Changes
				</Button>
			</Modal.Footer>
		</Modal>
	)
}
