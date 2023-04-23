import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Button, InputGroup } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { Form } from 'react-bootstrap';

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
	numMatches: number;
	matches: MatchNode[];
	constructor(id: number, name: string, depth: number, roundNum: number, numMatches: number) {
		this.id = id;
		this.name = name;
		this.depth = depth;
		this.roundNum = roundNum;
		this.numMatches = numMatches;
		this.matches = [];
	}
}

class MatchTree {
	root: MatchNode | null
	rounds: Round[]

	constructor(numRounds: number, numWildcards: number) {
		this.rounds = this.buildRounds(numRounds, numWildcards)
		this.root = this.buildMatch(null, 0)
	}

	buildRounds(numRounds: number, numWildcards: number): Round[] {
		// The number of matches in a round is equal to 2^depth unless it's the first round
		// and there are wildcards. In that case, the number of matches equals the number of wildcards
		return Array.from({ length: numRounds }, (_, i) => {
			const numMatches = i === numRounds - 1 && numWildcards > 0 ? numWildcards : 2 ** i;
			return new Round(i + 1, `Round ${numRounds - i}`, i, numRounds - i, numMatches);
		});
	}

	buildMatch(parent: MatchNode | null, depth: number): MatchNode | null {
		if (depth >= this.rounds.length) {
			return null;
		}

		const match = new MatchNode(parent, depth)

		// Give the round at this depth a reference to the match node
		// Matches are ordered left to right 
		const round = this.rounds[depth]
		round.matches.push(match)

		match.left = this.buildMatch(match, depth + 1)
		match.right = this.buildMatch(match, depth + 1)
		return match
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
	const node1: Node = props.node1
	const node2: Node = props.node2

	const empty: boolean = props.empty
	const direction: Direction = props.direction
	const upperOuter: boolean = props.upperOuter
	const lowerOuter: boolean = props.lowerOuter
	const height: number = props.height
	const spacing: number = props.spacing

	if (empty) {
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
			{/* {round.depth}<br /> */}
			{/* {round.name} */}
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


const RoundComponent = (props) => {
	const round: Round = props.round;
	const direction: Direction = props.direction;
	const numDirections: number = props.numDirections;
	const matchHeight: number = props.matchHeight;
	const updateRoundName = props.updateRoundName;


	// For a given round and it's depth, we know that the number of nodes in this round will be 2^depth
	// For example, a round with depth 1 has 2 nodes and a round at depth 3 can have up to 8 nodes
	// The number of matches in a round is the number of nodes / 2
	// However, each round component only renders the match in a given direction. So for a bracket with 2 directions, 
	// the number of matches is split in half

	const buildMatches = () => {
		// const numMatches = 2 ** round.depth / 2 / numDirections
		// Get the number of matches in a single direction (left or right)
		const numMatches = round.numMatches / numDirections
		// Get the difference between the specified number of matches and how many there could possibly be
		// This is to account for wildcard rounds where there are less than the maximum number of matches
		const maxMatches = 2 ** (round.depth + 1) / 2 / numDirections
		const emptyMatches = maxMatches - numMatches

		// console.log('round numMatches', roundNumMatches)

		// Whether there are any matches below this round
		// Used to determine whether to truncate the match box border so that it does not extend past the team slot
		// const outerRound = round.roundNum === 1
		let upperOuter = false
		let lowerOuter = false
		if (round.roundNum === 1) {
			upperOuter = true
			lowerOuter = true
		}


		const matches = Array.from(Array(maxMatches).keys()).map((i) => {
			return (
				// <MatchBox className={className} style={{ height: matchHeight, marginBottom: (i + 1 < numMatches ? matchHeight : 0) }} />
				<MatchBox
					empty={i < emptyMatches}
					direction={direction}
					upperOuter={upperOuter}
					lowerOuter={lowerOuter}
					height={matchHeight}
					spacing={i + 1 < maxMatches ? matchHeight : 0} // Do not add spacing to the last match in the round column
				/>

			)
		})
		return matches

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

	const targetHeight = 700;

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
			...rounds.slice(1).reverse().map((round, idx) =>
				<RoundComponent
					round={round} direction={Direction.TopLeft}
					numDirections={numDirections}
					matchHeight={2 ** idx * firstRoundMatchHeight}
					updateRoundName={updateRoundName}
				/>
			),
			// handle final round differently
			<FinalRound round={rounds[0]} updateRoundName={updateRoundName} />,
			...rounds.slice(1).map((round, idx, arr) =>
				<RoundComponent round={round}
					direction={Direction.TopRight}
					numDirections={numDirections}
					matchHeight={2 ** (arr.length - 1 - idx) * firstRoundMatchHeight}
					updateRoundName={updateRoundName}
				/>
			)
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
