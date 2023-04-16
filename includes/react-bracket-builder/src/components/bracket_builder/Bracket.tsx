import React, { useState } from 'react';
import { Container, Row, Col, Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';

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

const BracketCol = (props) => {
	return (
		<div style={{ display: 'flex', border: '1px solid black', flexGrow: '1', flexDirection: 'column', justifyContent: 'center' }}>
			{props.children}
		</div>
	)
}

const MatchBox = ({ grow = '1', ...props }) => {
	const node1: Node = props.node1
	const node2: Node = props.node2
	// const height: number = props.height
	// This component renders the lines connecting two nodes representing a "game"
	// These should be evenly spaced in the column and grow according to the number of other matches in the round
	return (
		<div style={{ height: props.height, border: '1px solid black', marginBottom: props.marginBottom }}>

		</div>
	)

}

const Spacer = ({ grow = '1' }) => {
	return (
		<div style={{ flexGrow: grow }} />
	)
}

const FinalRound = (props) => {
	const round: Round = props.round;
	return (
		<BracketCol>
			<div style={{ position: 'absolute' }}>
				{round.depth}<br />
				{round.name}
			</div>
			<Spacer grow='2' />
			<MatchBox grow='1' />
			<Spacer grow='2' />
		</BracketCol>
	)

}

const RoundComponent = (props) => {
	const round: Round = props.round;
	const direction: Direction = props.direction;
	const numDirections: number = props.numDirections;
	const matchHeight: number = props.matchHeight;

	console.log(matchHeight)

	// For a given round and it's depth, we know that the number of nodes in this round will be 2^depth
	// For example, a round with depth 1 has 2 nodes and a round at depth 3 can have up to 8 nodes
	// The number of matches in a round is the number of nodes / 2
	// However, each round component only renders the match in a given direction. So for a bracket with 2 directions, 
	// the number of matches is split in half

	// const buildMatches = () => {
	// 	const numMatches = 2 ** round.depth / 2 / numDirections
	// 	// return an array of MatchBoxes separated by Spacers
	// 	const matches = [<Spacer />]
	// 	for (let i = 0; i < numMatches; i++) {
	// 		matches.push(<MatchBox />)
	// 		matches.push(<Spacer />)
	// 	}
	// 	return matches
	// }
	const buildMatches = () => {
		const numMatches = 2 ** round.depth / 2 / numDirections
		const matches = Array.from(Array(numMatches).keys()).map((i) => {
			// const node1 = round.nodes[i * 2]
			// const node2 = round.nodes[i * 2 + 1]
			return (
				<MatchBox height={matchHeight} marginBottom={i + 1 < numMatches ? matchHeight : 0} />
			)
		})
		return matches

	}


	return (
		<BracketCol>
			<div style={{ position: 'absolute' }}>
				{round.depth}<br />
				{round.name}
			</div>
			{buildMatches()}
			{/* <Spacer />
			<MatchBox grow='1' />
			<Spacer /> */}
		</BracketCol>
	)
}

export const Bracket = () => {
	const [rounds, setRounds] = useState([

		// new Round(1, 'Round 3', 1, []),
		// new Round(2, 'Round 2', 2, []),
		// new Round(3, 'Round 1', 3, []),

		// new Round(0, 'Round 4', 1, []),
		// new Round(2, 'Round 3', 2, []),
		// new Round(3, 'Round 2', 3, []),
		// new Round(4, 'Round 1', 4, []),

		new Round(1, 'Round 6', 1, []),
		new Round(2, 'Round 5', 2, []),
		new Round(3, 'Round 4', 3, []),
		new Round(4, 'Round 3', 4, []),
		new Round(5, 'Round 2', 5, []),
		new Round(6, 'Round 1', 6, []),
	]);
	const bracketHeight = 600;
	// The number of rounds sets the initial height of each match
	const firstRoundMatchHeight = bracketHeight / rounds.length;
	/**
	 * Build rounds in two directions, left to right and right to left
	 */
	const buildRounds2 = (rounds: Round[]) => {
		// Assume rounds are sorted by depth
		// Rendering from left to right, sort by depth descending
		const reversed = rounds.slice(1).reverse()
		const numDirections = 2

		return [
			...rounds.slice(1).reverse().map((round, idx) => <RoundComponent round={round} direction={Direction.TopLeft} numDirections={numDirections} matchHeight={2 ** idx * firstRoundMatchHeight / 2} />),
			// handle final round differently
			<FinalRound round={rounds[0]} />,
			...rounds.slice(1).map((round, idx, arr) => <RoundComponent round={round} direction={Direction.TopRight} numDirections={numDirections} matchHeight={2 ** (arr.length - 1 - idx) * firstRoundMatchHeight / 2} />)
		]
	}

	return (
		<div style={{ height: bracketHeight, display: 'flex' }}>
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

	return (
		<Modal show={show} onHide={handleCancel} size='xl' centered={true} style={{ zIndex: '99999999', position: 'relative' }}>
			<Modal.Header closeButton style={{ borderBottom: '0' }}>
				<Modal.Title>Create Bracket</Modal.Title>
			</Modal.Header >
			<Modal.Body><Bracket /></Modal.Body>
			<Modal.Footer style={{ borderTop: '0' }}>
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
