import React, { useState } from 'react';
import { Container, Row, Col, Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';

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


const RoundComponent = (props) => {
	const round: Round = props.round;

	return (
		<Col style={{ height: '100%', border: '1px solid black' }}>
			{round.depth}
		</Col>
	)
}

export const Bracket = () => {
	const [rounds, setRounds] = useState([
		new Round(1, 'Round 1', 0, []),
		new Round(2, 'Round 2', 1, []),
		new Round(3, 'Round 3', 2, []),
		new Round(4, 'Round 4', 3, []),
	]);

	const buildRounds = (rounds: Round[]) => {
		// Assume rounds are sorted by depth
		// Rendering from left to right, sort by depth descending
		const reversed = rounds.slice(1).reverse()
		return [
			...reversed.map(round => <RoundComponent round={round} />),
			...rounds.map(round => <RoundComponent round={round} />)
		]
	}

	return (
		<Container style={{ height: '600px' }}>
			<Row style={{ height: '100%' }}>
				{buildRounds(rounds)}
			</Row>
		</Container>
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
