import React, { useState } from 'react';
import { Container, Row, Col, Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { Form } from 'react-bootstrap';

// Direction enum
enum Direction {
	TopLeft = 0,
	TopRight = 1,
	BottomLeft = 2,
	BottomRight = 3,
}

class Node {
	id: number;
	name: string;
	left: number;
	right: number;
	in_order: number;
	depth: number;
	parent_id: number;
	constructor(id: number, name: string, left: number, right: number, depth: number, parent_id: number) {
		this.id = id;
		this.name = name;
		this.left = left;
		this.right = right;
		this.depth = depth;
		this.parent_id = parent_id;
	}
}

class Round {
	id: number;
	name: string;
	depth: number;
	nodes: Node[];
	constructor(id: number, name: string, depth: number, nodes: Node[]) {
		this.id = id;
		this.name = name;
		this.depth = depth;
		this.nodes = nodes;
	}
}

const MatchBox = ({ ...props }) => {
	const node1: Node = props.node1
	const node2: Node = props.node2
	// This component renders the lines connecting two nodes representing a "game"
	// These should be evenly spaced in the column and grow according to the number of other matches in the round
	return (
		<div className={props.className} style={{ ...props.style }}>
		</div>
	)

}

const Spacer = ({ grow = '1' }) => {
	return (
		<div style={{ flexGrow: grow }} />
	)
}

const RoundHeader = (props) => {
	const round: Round = props.round;
	return (
		<div className='wpbb-round__header'>
			{/* {round.depth}<br /> */}
			{/* {round.name} */}
			<Form.Control type='text' value={round.name} />
		</div>
	)
}

const FinalRound = (props) => {
	const round: Round = props.round;
	return (
		<div className='wpbb-round'>
			<RoundHeader round={round} />
			<div className='wpbb-round__body'>
				<Spacer grow='2' />
				<MatchBox className='wpbb-final-match' />
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


	// For a given round and it's depth, we know that the number of nodes in this round will be 2^depth
	// For example, a round with depth 1 has 2 nodes and a round at depth 3 can have up to 8 nodes
	// The number of matches in a round is the number of nodes / 2
	// However, each round component only renders the match in a given direction. So for a bracket with 2 directions, 
	// the number of matches is split in half

	const buildMatches = () => {
		const numMatches = 2 ** round.depth / 2 / numDirections
		const className = direction === Direction.TopLeft || direction === Direction.BottomLeft ? 'wpbb-match-box-left' : 'wpbb-match-box-right'
		const matches = Array.from(Array(numMatches).keys()).map((i) => {
			return (
				<MatchBox className={className} style={{ height: matchHeight, marginBottom: (i + 1 < numMatches ? matchHeight : 0) }} />
			)
		})
		return matches

	}
	return (
		<div className='wpbb-round'>
			<RoundHeader round={round} />
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
		console.log(num)
		setNumRounds(parseInt(num))
	}

	return (
		<form className='wpbb-options-form'>
			<label>
				Number of Rounds:
			</label>
			<select value={numRounds} onChange={handleChange}>
				{options}
			</select>
		</form>
	)
}

export const Bracket = (props) => {
	const { numRounds } = props

	const rounds = Array.from(Array(numRounds).keys()).map((i) => {
		return new Round(i + 1, `Round ${numRounds - i}`, i + 1, [])
	})

	const targetHeight = 600;

	// The number of rounds sets the initial height of each match
	// const firstRoundMatchHeight = targetHeight / rounds.length / 2;
	const firstRoundMatchHeight = targetHeight / 2 ** (rounds.length - 2) / 2;

	/**
	 * Build rounds in two directions, left to right and right to left
	 */
	const buildRounds2 = (rounds: Round[]) => {
		// Assume rounds are sorted by depth
		// Rendering from left to right, sort by depth descending
		const reversed = rounds.slice(1).reverse()
		const numDirections = 2

		return [
			...rounds.slice(1).reverse().map((round, idx) => <RoundComponent round={round} direction={Direction.TopLeft} numDirections={numDirections} matchHeight={2 ** idx * firstRoundMatchHeight} />),
			// handle final round differently
			<FinalRound round={rounds[0]} />,
			...rounds.slice(1).map((round, idx, arr) => <RoundComponent round={round} direction={Direction.TopRight} numDirections={numDirections} matchHeight={2 ** (arr.length - 1 - idx) * firstRoundMatchHeight} />)
		]
	}

	return (
		<div className='wpbb-bracket'>
			{buildRounds2(rounds)}
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

	return (
		<Modal className='wpbb-bracket-modal' show={show} onHide={handleCancel} size='xl' centered={true}>
			<Modal.Header className='wpbb-bracket-modal__header' closeButton>
				<Modal.Title>Create Bracket</Modal.Title>
				<NumRoundsSelector numRounds={numRounds} setNumRounds={setNumRounds} />
			</Modal.Header >
			<Modal.Body className='pt-0'><Bracket numRounds={numRounds} /></Modal.Body>
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
